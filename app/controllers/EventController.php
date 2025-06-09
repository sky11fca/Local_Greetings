<?php
require_once __DIR__ . '/../models/EventModel.php';

class EventController
{
    private $eventModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
    }

    public function joinEvent($eventId, $userId)
    {
        header('Content-Type: application/json');
        $result = $this->eventModel->joinEvent($eventId, $userId);
        echo json_encode($result);
    }

    public function leaveEvent($eventId, $userId)
    {
        header('Content-Type: application/json');
        $result = $this->eventModel->leaveEvent($eventId, $userId);
        echo json_encode($result);
    }

    // You can add more methods here for other event-related functionalities if needed, like listing events, creating events, etc.
    // For now, focusing on join/leave as requested.
} 