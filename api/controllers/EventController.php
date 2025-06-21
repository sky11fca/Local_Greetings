<?php
require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/../config/JWT.php';

class EventController
{
    private $eventModel;

    public function __construct($db)
    {
        $this->eventModel = new EventModel($db);
    }

    /**
     * A robust method to get the Authorization header from the request.
     * Works across different server environments (Apache, Nginx, etc.).
     */
    private function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { // Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    /**
     * Extracts the Bearer token from the Authorization header.
     */
    private function getBearerToken() {
        $headers = $this->getAuthorizationHeader();
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    public function listEvents()
    {
        header('Content-Type: application/json');
        
        // Sanitize and retrieve parameters
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $sportType = isset($_GET['sport_type']) ? htmlspecialchars(strip_tags($_GET['sport_type'])) : null;
        $search = isset($_GET['search']) ? htmlspecialchars(strip_tags($_GET['search'])) : null;

        // If sportType is an empty string, treat it as null
        if ($sportType === '') {
            $sportType = null;
        }

        try {
            // Get current user ID if logged in
            $currentUserId = null;
            $token = $this->getBearerToken();
            if ($token) {
                $payload = JWT::validate($token);
                if ($payload) {
                    $currentUserId = $payload['user_id'];
                }
            }

            $events = $this->eventModel->getPublicEvents($limit, $offset, $sportType, $search, $currentUserId);
            $totalEvents = $this->eventModel->countPublicEvents($sportType, $search, $currentUserId);

            // Get user participation status if user is logged in
            $participationStatus = [];
            if ($currentUserId && !empty($events)) {
                $eventIds = array_column($events, 'event_id');
                $participationStatus = $this->eventModel->getUserParticipationStatus($eventIds, $currentUserId);
            }

            // Add participation status to each event
            foreach ($events as &$event) {
                $event['is_participant'] = $participationStatus[$event['event_id']] ?? false;
            }

            echo json_encode([
                'total_events' => $totalEvents,
                'events' => $events
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'An internal server error occurred: ' . $e->getMessage()]);
        }
    }

    public function listJoinedEvents()
    {
        header('Content-Type: application/json');
        try {
            $token = $this->getBearerToken();
            if (!$token) {
                throw new Exception('Authorization token not found or invalid', 401);
            }

            $payload = JWT::validate($token);
            if (!$payload) {
                throw new Exception('Invalid or expired token', 401);
            }

            $userId = $payload['user_id'];
            $searchQuery = isset($_GET['search']) ? htmlspecialchars(strip_tags($_GET['search'])) : '';
            $sportType = isset($_GET['sport_type']) ? htmlspecialchars(strip_tags($_GET['sport_type'])) : '';

            $events = $this->eventModel->getJoinedEvents($userId, $searchQuery, $sportType);

            echo json_encode([
                "success" => true,
                "events" => $events
            ]);
        } catch (Exception $e) {
            $statusCode = is_int($e->getCode()) && $e->getCode() >= 400 ? $e->getCode() : 500;
            http_response_code($statusCode);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public function listCreatedEvents()
    {
        header('Content-Type: application/json');
        try {
            $token = $this->getBearerToken();
            if (!$token) {
                throw new Exception('Authorization token not found or invalid', 401);
            }

            $payload = JWT::validate($token);
            if (!$payload) {
                throw new Exception('Invalid or expired token', 401);
            }

            $userId = $payload['user_id'];
            $searchQuery = isset($_GET['search']) ? htmlspecialchars(strip_tags($_GET['search'])) : '';
            $sportType = isset($_GET['sport_type']) ? htmlspecialchars(strip_tags($_GET['sport_type'])) : '';
            
            $events = $this->eventModel->getCreatedEvents($userId, $searchQuery, $sportType);

            echo json_encode([
                "success" => true,
                "events" => $events
            ]);
        } catch (Exception $e) {
            $statusCode = is_int($e->getCode()) && $e->getCode() >= 400 ? $e->getCode() : 500;
            http_response_code($statusCode);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function joinEvent()
    {
        header('Content-Type: application/json');
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['event_id'])) {
                throw new Exception('Event ID is required.', 400);
            }

            // Use JWT for secure authentication
            $token = $this->getBearerToken();
            if (!$token) {
                throw new Exception('Authorization token not found or invalid', 401);
            }

            $payload = JWT::validate($token);
            if (!$payload) {
                throw new Exception('Invalid or expired token', 401);
            }

            $userId = $payload['user_id'];
            $eventId = $data['event_id'];

            // Check if user has already joined
            if ($this->eventModel->isUserParticipant($eventId, $userId)) {
                throw new Exception('You have already joined this event.', 409); // 409 Conflict
            }

            // Check if event is full
            $event = $this->eventModel->getEventById($eventId);
            if (!$event) {
                throw new Exception('Event not found.', 404);
            }
            if ($event['max_participants'] !== null && $event['current_participants'] >= $event['max_participants']) {
                throw new Exception('This event is full.', 409); // 409 Conflict
            }

            // Attempt to join the event
            $result = $this->eventModel->joinEvent($eventId, $userId);
            if (!$result['success']) {
                throw new Exception($result['message'] ?: 'Error joining event.', 500);
            }

            echo json_encode([
                "success" => true,
                "message" => "Successfully joined event."
            ]);
        } catch (Exception $e) {
            $statusCode = is_int($e->getCode()) && $e->getCode() >= 400 ? $e->getCode() : 500;
            http_response_code($statusCode);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function leaveEvent()
    {
        header('Content-Type: application/json');
        try {
            $token = $this->getBearerToken();
            if (!$token) {
                throw new Exception('Authorization token not found or invalid', 401);
            }

            $payload = JWT::validate($token);
            if (!$payload) {
                throw new Exception('Invalid or expired token', 401);
            }

            $userId = $payload['user_id'];
            $data = json_decode(file_get_contents('php://input'), true);
            $eventId = $data['event_id'] ?? 0;

            if (empty($eventId)) {
                throw new Exception('Event ID is required.', 400);
            }
            
            $result = $this->eventModel->leaveEvent($eventId, $userId);

            if (!$result['success']) {
                throw new Exception($result['message'], 500);
            }

            echo json_encode($result);

        } catch (Exception $e) {
            $statusCode = is_int($e->getCode()) && $e->getCode() >= 400 ? $e->getCode() : 500;
            http_response_code($statusCode);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function createEvent()
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // Use JWT for secure authentication
            $token = $this->getBearerToken();
            if (!$token) {
                throw new Exception('Authorization token not found or invalid', 401);
            }

            $payload = JWT::validate($token);
            if (!$payload) {
                throw new Exception('Invalid or expired token', 401);
            }

            $organizerId = $payload['user_id'];

            // Validate required fields
            $requiredFields = ['title', 'field_id', 'sport_type', 'end_time', 'start_time', 'max_participants'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    throw new Exception('Missing required field: ' . $field, 400);
                }
            }

            // Validate description (can be empty but must be present)
            if (!isset($data['description'])) {
                throw new Exception('Missing required field: description', 400);
            }

            // Pass the whole data array to the model
            $result = $this->eventModel->createEvent($organizerId, $data);

            if (!$result['success']) {
                throw new Exception($result['message'] ?: 'Error creating event', 500);
            }

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Event created successfully',
                'event_id' => $result['event_id'],
            ]);
        } catch (Exception $e) {
            $statusCode = is_int($e->getCode()) && $e->getCode() >= 400 ? $e->getCode() : 500;
            http_response_code($statusCode);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getEvent()
    {
        header('Content-Type: application/json');
        $eventId = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

        if ($eventId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid event ID.']);
            return;
        }

        $event = $this->eventModel->getEventById($eventId);

        if ($event) {
            echo json_encode(['success' => true, 'event' => $event]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Event not found.']);
        }
    }

    public function updateEvent()
    {
        header('Content-Type: application/json');
        try {
            $token = $this->getBearerToken();
            if (!$token) {
                throw new Exception('Authorization token not found or invalid', 401);
            }

            $payload = JWT::validate($token);
            if (!$payload) {
                throw new Exception('Invalid or expired token', 401);
            }

            $userId = $payload['user_id'];
            $data = json_decode(file_get_contents('php://input'), true);
            $eventId = $data['event_id'] ?? 0;

            if (empty($eventId)) {
                throw new Exception('Event ID is required.', 400);
            }
            
            // Pass validated data to the model
            $result = $this->eventModel->updateEvent($eventId, $userId, $data);

            if (!$result['success']) {
                throw new Exception($result['message'], 403); // 403 Forbidden for auth issues
            }

            echo json_encode($result);

        } catch (Exception $e) {
            $statusCode = is_int($e->getCode()) && $e->getCode() >= 400 ? $e->getCode() : 500;
            http_response_code($statusCode);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function deleteEvent()
    {
        header('Content-Type: application/json');
        try {
            $token = $this->getBearerToken();
            if (!$token) {
                throw new Exception('Authorization token not found or invalid', 401);
            }

            $payload = JWT::validate($token);
            if (!$payload) {
                throw new Exception('Invalid or expired token', 401);
            }

            $userId = $payload['user_id'];
            $data = json_decode(file_get_contents('php://input'), true);
            $eventId = $data['event_id'] ?? 0;

            if (empty($eventId)) {
                throw new Exception('Event ID is required.', 400);
            }
            
            $result = $this->eventModel->deleteEvent($eventId, $userId);

            if (!$result['success']) {
                throw new Exception($result['message'], 403);
            }

            echo json_encode($result);

        } catch (Exception $e) {
            $statusCode = is_int($e->getCode()) && $e->getCode() >= 400 ? $e->getCode() : 500;
            http_response_code($statusCode);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function listPastEvents()
    {
        header('Content-Type: application/json');
        try {
            $token = $this->getBearerToken();
            if (!$token) {
                throw new Exception('Authorization token not found or invalid', 401);
            }

            $payload = JWT::validate($token);
            if (!$payload) {
                throw new Exception('Invalid or expired token', 401);
            }

            $userId = $payload['user_id'];
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

            $events = $this->eventModel->getPastEventsForUser($userId, $limit, $offset);
            $totalEvents = $this->eventModel->countPastEventsForUser($userId);

            echo json_encode([
                'success' => true,
                'total_events' => $totalEvents,
                'events' => $events
            ]);

        } catch (Exception $e) {
            $statusCode = is_int($e->getCode()) && $e->getCode() >= 400 ? $e->getCode() : 500;
            http_response_code($statusCode);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function sendRssFeed()
    {
        // Implementation of sendRssFeed method
    }
} 