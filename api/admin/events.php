<?php
require_once __DIR__ . '/AdminController.php';
require_once __DIR__ . '/../models/EventModel.php';

class AdminEventsController extends AdminController {
    private $eventModel;
    
    public function __construct() {
        parent::__construct();
        $this->eventModel = new EventModel($this->db);
    }
    
    public function handleRequest() {
        try {
            $user = $this->checkAdminAuth();
        } catch (Exception $e) {
            $this->sendResponse(false, $e->getMessage(), null, $e->getCode());
            return;
        }
        
        $method = $_SERVER['REQUEST_METHOD'];
        
        // error_log('API endpoint: ' . $endpoint . ' | Method: ' . $_SERVER['REQUEST_METHOD']); // Removed undefined $endpoint
        
        switch ($method) {
            case 'GET':
                $this->getEvents();
                break;
            case 'POST':
                $this->createEvent();
                break;
            case 'PUT':
                $this->updateEvent();
                break;
            case 'DELETE':
                $this->deleteEvent();
                break;
            default:
                $this->sendResponse(false, 'Method not allowed', null, 405);
        }
    }
    
    private function getEvents() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        try {
            if ($id) {
                // Get single event
                $event = $this->eventModel->getEventById($id);
                if ($event) {
                    $this->sendResponse(true, 'Event retrieved successfully', ['event' => $event]);
                } else {
                    $this->sendResponse(false, 'Event not found', null, 404);
                }
            } else {
                // Get events with pagination and filters
                $itemsPerPage = 10;
                $offset = ($page - 1) * $itemsPerPage;
                
                $query = "SELECT 
                    e.event_id,
                    e.title,
                    e.description,
                    e.organizer_id,
                    u.username as organizer_name,
                    sf.name as field_name,
                    sf.address,
                    e.sport_type,
                    e.start_time,
                    e.end_time,
                    e.max_participants,
                    e.current_participants,
                    e.status,
                    e.created_at
                    FROM Events e 
                    JOIN Users u on e.organizer_id = u.user_id
                    LEFT JOIN SportsFields sf ON e.field_id = sf.field_id
                    WHERE 1=1";
                
                $params = [];
                
                if ($search) {
                    $query .= " AND (e.title LIKE ? OR e.description LIKE ?)";
                    $params[] = '%' . $search . '%';
                    $params[] = '%' . $search . '%';
                }
                
                if ($status) {
                    $query .= " AND e.status = ?";
                    $params[] = $status;
                }
                
                $query .= " ORDER BY e.created_at DESC LIMIT $itemsPerPage OFFSET $offset";
                $stmt = $this->db->prepare($query);
                $stmt->execute($params);
                $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Get total count
                $countQuery = "SELECT COUNT(*) as total FROM Events e WHERE 1=1";
                $countParams = [];
                
                if ($search) {
                    $countQuery .= " AND (e.title LIKE ? OR e.description LIKE ?)";
                    $countParams[] = '%' . $search . '%';
                    $countParams[] = '%' . $search . '%';
                }
                
                if ($status) {
                    $countQuery .= " AND e.status = ?";
                    $countParams[] = $status;
                }
                
                $stmt = $this->db->prepare($countQuery);
                $stmt->execute($countParams);
                $totalEvents = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                $pagination = $this->getPaginationInfo($page, $totalEvents, $itemsPerPage);
                
                $this->sendResponse(true, 'Events retrieved successfully', [
                    'events' => $events,
                    'pagination' => $pagination
                ]);
            }
            
        } catch (Exception $e) {
            $this->sendResponse(false, 'Error retrieving events: ' . $e->getMessage());
        }
    }
    
    private function createEvent() {
        try {
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $fieldId = $_POST['field_id'] ?? '';
            $sportType = $_POST['sport_type'] ?? '';
            $startTime = $_POST['start_time'] ?? '';
            $endTime = $_POST['end_time'] ?? '';
            $maxParticipants = $_POST['max_participants'] ?? '';
            
            if (empty($title) || empty($description) || empty($fieldId) || empty($sportType) || empty($startTime) || empty($endTime) || empty($maxParticipants)) {
                $this->sendResponse(false, 'All fields are required');
            }
            
            // Validate times
            if (strtotime($startTime) >= strtotime($endTime)) {
                $this->sendResponse(false, 'Start time must be before end time');
            }
            
            // Use admin user as organizer
            $adminUser = $this->checkAdminAuth();
            
            $eventData = [
                'title' => $title,
                'description' => $description,
                'field_id' => $fieldId,
                'sport_type' => $sportType,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'max_participants' => $maxParticipants,
                'status' => 'active'
            ];
            
            $result = $this->eventModel->createEvent($adminUser['user_id'], $eventData);
            
            if ($result['success']) {
                $this->logActivity('Event created', "Created event: $title");
                $this->sendResponse(true, 'Event created successfully');
            } else {
                $this->sendResponse(false, $result['message']);
            }
            
        } catch (Exception $e) {
            $this->sendResponse(false, 'Error creating event: ' . $e->getMessage());
        }
    }
    
    private function updateEvent() {
        try {
            $input = file_get_contents('php://input');
            parse_str($input, $data);
            
            $eventId = $data['id'] ?? null;
            $title = $data['title'] ?? '';
            $description = $data['description'] ?? '';
            $fieldId = $data['field_id'] ?? '';
            $sportType = $data['sport_type'] ?? '';
            $startTime = $data['start_time'] ?? '';
            $endTime = $data['end_time'] ?? '';
            $maxParticipants = $data['max_participants'] ?? '';
            
            if (!$eventId || empty($title) || empty($description) || empty($fieldId) || empty($sportType) || empty($startTime) || empty($endTime) || empty($maxParticipants)) {
                $this->sendResponse(false, 'All fields are required');
            }
            
            // Validate times
            if (strtotime($startTime) >= strtotime($endTime)) {
                $this->sendResponse(false, 'Start time must be before end time');
            }
            
            $eventData = [
                'title' => $title,
                'description' => $description,
                'field_id' => $fieldId,
                'sport_type' => $sportType,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'max_participants' => $maxParticipants
            ];
            
            $result = $this->eventModel->updateEvent($eventId, 0, $eventData); // 0 for admin override
            
            if ($result['success']) {
                $this->logActivity('Event updated', "Updated event: $title");
                $this->sendResponse(true, 'Event updated successfully');
            } else {
                $this->sendResponse(false, $result['message']);
            }
            
        } catch (Exception $e) {
            $this->sendResponse(false, 'Error updating event: ' . $e->getMessage());
        }
    }
    
    private function deleteEvent() {
        // Fix: Parse ID from query string for DELETE requests
        parse_str($_SERVER['QUERY_STRING'] ?? '', $query);
        $eventId = $query['id'] ?? null;

        if (!$eventId) {
            $this->sendResponse(false, 'Event ID is required');
        }

        // Get event info for logging
        $event = $this->eventModel->getEventById($eventId);
        if (!$event) {
            $this->sendResponse(false, 'Event not found', null, 404);
        }

        $result = $this->eventModel->deleteEvent($eventId, 0); // 0 for admin override

        if ($result['success']) {
            $this->logActivity('Event deleted', "Deleted event: {$event['title']}");
            $this->sendResponse(true, 'Event deleted successfully');
        } else {
            $this->sendResponse(false, $result['message']);
        }
    }
}

$controller = new AdminEventsController();
$controller->handleRequest();
?> 