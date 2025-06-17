<?php

class EventModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAllEvents()
    {
        try
        {
            $query = "SELECT * FROM Events ORDER BY start_time ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
        }

    }
//        public function joinEvent($eventId, $userId)
//    {
//        try {
//            // Check if the user is already a participant
//            $stmt = $this->db->prepare("SELECT COUNT(*) FROM EventParticipants WHERE event_id = ? AND user_id = ?");
//            $stmt->execute([$eventId, $userId]);
//            if ($stmt->fetchColumn() > 0) {
//                return ['success' => false, 'message' => 'You have already joined this event.'];
//            }
//
//            // Check if the event exists and has available spots
//            $stmt = $this->db->prepare("SELECT max_participants, current_participants FROM Events WHERE event_id = ?");
//            $stmt->execute([$eventId]);
//            $event = $stmt->fetch(PDO::FETCH_ASSOC);
//
//            if (!$event) {
//                return ['success' => false, 'message' => 'Event not found.'];
//            }
//
//            if ($event['max_participants'] !== null && $event['current_participants'] >= $event['max_participants']) {
//                return ['success' => false, 'message' => 'Event is full.'];
//            }
//
//            $this->db->beginTransaction();
//
//            // Add participant
//            $stmt = $this->db->prepare("INSERT INTO EventParticipants (event_id, user_id, status) VALUES (?, ?, 'confirmed')");
//            $stmt->execute([$eventId, $userId]);
//
//            // Increment current_participants count in Events table
//            $stmt = $this->db->prepare("UPDATE Events SET current_participants = current_participants + 1 WHERE event_id = ?");
//            $stmt->execute([$eventId]);
//
//            $this->db->commit();
//            return ['success' => true, 'message' => 'Successfully joined event.'];
//        } catch (PDOException $e) {
//            $this->db->rollBack();
//            return ['success' => false, 'message' => 'Error joining event: ' . $e->getMessage()];
//        }
//    }
//
//    public function leaveEvent($eventId, $userId)
//    {
//        try {
//            $this->db->beginTransaction();
//
//            // Remove participant
//            $stmt = $this->db->prepare("DELETE FROM EventParticipants WHERE event_id = ? AND user_id = ?");
//            $stmt->execute([$eventId, $userId]);
//
//            // Decrement current_participants count in Events table
//            $stmt = $this->db->prepare("UPDATE Events SET current_participants = current_participants - 1 WHERE event_id = ? AND current_participants > 0");
//            $stmt->execute([$eventId]);
//
//            $this->db->commit();
//            return ['success' => true, 'message' => 'Successfully left event.'];
//        } catch (PDOException $e) {
//            $this->db->rollBack();
//            return ['success' => false, 'message' => 'Error leaving event: ' . $e->getMessage()];
//        }
//    }
//
//    public function getEventById($eventId)
//    {
//        $stmt = $this->db->prepare("SELECT * FROM Events WHERE event_id = ?");
//        $stmt->execute([$eventId]);
//        return $stmt->fetch(PDO::FETCH_ASSOC);
//    }
//
//    public function isUserParticipant($eventId, $userId)
//    {
//        $stmt = $this->db->prepare("SELECT COUNT(*) FROM EventParticipants WHERE event_id = ? AND user_id = ?");
//        $stmt->execute([$eventId, $userId]);
//        return $stmt->fetchColumn() > 0;
//    }
}

//    public function __construct()
//    {
//        try {
//            $this->db = new PDO(
//                'mysql:host=127.0.0.1;dbname=local_greeter',
//                'bobby',
//                'bobbydb3002',
//                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
//            );
//        } catch (PDOException $e) {
//            die("Database connection failed: " . $e->getMessage());
//        }
//    }
//
