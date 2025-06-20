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

    public function listEvents()
    {
        header('Content-Type: application/json');
        try{
            $searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
            $sportType = isset($_GET['sport_type']) ? $_GET['sport_type'] : '';


            $result = $this->eventModel->getAllEvents($searchQuery, $sportType);

            echo json_encode([
                "success" => true,
                "events" => $result
            ]);
        } catch(Exception $e){
            http_response_code($e->getCode() ?: 400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

    }

    public function listJoinedEvents()
    {
        header('Content-Type: application/json');

        try{
            $data = json_decode(file_get_contents('php://input'), true);
            $searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
            $sportType = isset($_GET['sport_type']) ? $_GET['sport_type'] : '';
            if(empty($data['user_id'])){
                echo json_encode(['success' => false, 'message' => 'Invalid input']);
            }

            $result = $this->eventModel->getJoinedEvents($data['user_id'], $searchQuery, $sportType);

            if(!$result){
                echo json_encode(['success' => false, 'message' => 'Error fetching joined events.']);
            }
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "events" => $result
            ]);
        }catch(Exception $e){
            http_response_code($e->getCode() ?: 400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    public function listCreatedEvents()
    {
        header('Content-Type: application/json');

        try{
            $data = json_decode(file_get_contents('php://input'), true);
            $searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
            $sportType = isset($_GET['sport_type']) ? $_GET['sport_type'] : '';
            if(empty($data['user_id'])){
                echo json_encode(['success' => false, 'message' => 'Invalid input']);
            }

            $result = $this->eventModel->getCreatedEvents($data['user_id'], $searchQuery, $sportType);

            if(!$result){
                echo json_encode(['success' => false, 'message' => 'Error fetching joined events.']);
            }
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "events" => $result
            ]);
        }catch(Exception $e){
            http_response_code($e->getCode() ?: 400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function joinEvent()
    {
        header('Content-Type: application/json');
        try{


                $data = json_decode(file_get_contents('php://input'), true);

                if(empty($data['event_id'])){
                    echo json_encode(['success' => false, 'message' => 'Invalid input']);
                }

                $userData = json_decode($_COOKIE['userData'], true);
                $data['user_id'] = $userData['id'];


                $isParticipant = $this->eventModel->isUserParticipant($data['event_id'], $data['user_id']);

                if($isParticipant){
                    echo json_encode(['success' => false, 'message' => 'You have already joined this event.']);
                }


                $event = $this->eventModel->getEventById($data['event_id']);

                if (!$event) {
                    echo json_encode(['success' => false, 'message' => 'Event not found.']);
                }

                if ($event['max_participants'] !== null && $event['current_participants'] >= $event['max_participants']) {
                    echo json_encode(['success' => false, 'message' => 'Event is full.']);
                }

                $result = $this->eventModel->joinEvent($data['event_id'], $data['user_id']);

                if(!$result){
                    echo json_encode(['success' => false, 'message' => 'Error joining event.']);
                }

                echo json_encode([
                    "success" => true,
                    "message" => "Successfully joined event",
                    "event" => $result
                ]);
        } catch (Exception $e){
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function leaveEvent()
    {
        try{
            header('Content-Type: application/json');

            $data = json_decode(file_get_contents('php://input'), true);
            if(empty($data['event_id'])){
                echo json_encode(['success' => false, 'message' => 'Invalid input']);
            }

            $userData = json_decode($_COOKIE['userData'], true);
            $userId = $userData['id'];

            $result = $this->eventModel->leaveEvent($data['event_id'], $userId);
            if(!$result){
                echo json_encode(['success' => false, 'message' => 'Error leaving event.']);
            }
            echo json_encode([
                "success" => true,
                "message" => "Successfully left event",
            ]);
        } catch (Exception $e){
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function createEvent()
    {
        header('Content-Type: application/json');


        try{
            $data = json_decode(file_get_contents('php://input'), true);

            $userData = json_decode($_COOKIE['userData'], true);


            $organizerId = $userData['id'];
            $requiredFields = ['title', 'description', 'field_id', 'end_time', 'start_time', 'max_participants'];

            foreach($requiredFields as $field){
                if(empty($data[$field])){
                    throw new Exception('Missing required field: ' . $field);
                }
            }

            $result = $this->eventModel->createEvent(
                $organizerId,
                $data['title'],
                $data['description'],
                $data['field_id'],
                $data['field_type'],
                $data['end_time'],
                $data['start_time'],
                $data['max_participants']
            );

            if(!$result){
                throw new Exception('Error creating event');
            }
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Event created successfully',
                'event_id' => $result['event_id'],
            ]);
        }
        catch(Exception $e){
            http_response_code($e->getCode() ?: 400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }


} 