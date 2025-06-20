<?php

class EventModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAllEvents($searchQuery = null, $sportType = null)
    {
            $query = "SELECT 
        e.event_id,
        e.title,
        e.description,
        u.username as organizer_name,
        sf.name as field_name,
        sf.address,
        e.sport_type,
        e.end_time,
        e.max_participants,
        e.current_participants,
        e.status,
        e.created_at
        FROM Events e 
        JOIN Users u on e.organizer_id = u.user_id
        JOIN SportsFields sf ON e.field_id = sf.field_id
        WHERE 1=1";

            $params = [];

            if($searchQuery){
                $query .= " AND (e.title LIKE :search OR e.description LIKE :search)";
                $params['search'] = '%' . $searchQuery . '%';
            }

            if($sportType){
                $query .= " AND e.sport_type = :sport_type";
                $params['sport_type'] = $sportType;
            }

            $query .= " ORDER BY e.start_time ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function getJoinedEvents($userId, $searchQuery = null, $sportType = null)
    {
        $query = "SELECT 
        e.event_id,
        e.title,
        e.description,
        u.username as organizer_name,
        sf.name as field_name,
        sf.address,
        e.sport_type,
        e.end_time,
        e.max_participants,
        e.current_participants,
        e.status,
        e.created_at
    FROM Events e 
    JOIN Users u on e.organizer_id = u.user_id
    JOIN SportsFields sf ON e.field_id = sf.field_id
    WHERE e.event_id IN ( SELECT event_id FROM EventParticipants WHERE user_id = :user_id AND status = 'confirmed')";

        $params = ['user_id' => $userId];

        if($searchQuery){
            $query .= " AND (e.title LIKE :search OR e.description LIKE :search)";
            $params['search'] = '%' . $searchQuery . '%';
        }

        if($sportType){
            $query .= " AND e.sport_type = :sport_type";
            $params['sport_type'] = $sportType;
        }

        $query .= " ORDER BY e.start_time ASC";





        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCreatedEvents($userId, $searchQuery = null, $sportType = null){
        $query = "SELECT 
        e.event_id,
        e.title,
        e.description,
        u.username as organizer_name,
        sf.name as field_name,
        sf.address,
        e.sport_type,
        e.end_time,
        e.max_participants,
        e.current_participants,
        e.status,
        e.created_at
    FROM Events e 
    JOIN Users u on e.organizer_id = u.user_id
    JOIN SportsFields sf ON e.field_id = sf.field_id
    WHERE e.organizer_id = :user_id";

        $params = ['user_id' => $userId];

        if($searchQuery){
            $query .= " AND (e.title LIKE :search OR e.description LIKE :search)";
            $params['search'] = '%' . $searchQuery . '%';
        }

        if($sportType){
            $query .= " AND e.sport_type = :sport_type";
            $params['sport_type'] = $sportType;
        }

        $query .= " ORDER BY e.start_time ASC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

        public function isUserParticipant($eventId, $userId)
        {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM EventParticipants WHERE event_id = ? AND user_id = ?");
            $stmt->execute([$eventId, $userId]);
            return $stmt->fetchColumn() > 0;
        }
        public function getEventById($eventId)
        {
            $stmt = $this->db->prepare("SELECT * FROM Events WHERE event_id = ?");
            $stmt->execute([$eventId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function joinEvent($eventId, $userId)
        {
            try{
                $this->db->beginTransaction();

                // Add participant
                $stmt = $this->db->prepare("INSERT INTO EventParticipants (event_id, user_id, status) VALUES (?, ?, 'confirmed')");
                $stmt->execute([$eventId, $userId]);

                // Increment current_participants count in Events table
                $stmt = $this->db->prepare("UPDATE Events SET current_participants = current_participants + 1 WHERE event_id = ?");
                $stmt->execute([$eventId]);

                $this->db->commit();

                return ["success" => true, "message" => "Successfully joined event."];
            } catch (PDOException $e) {
                $this->db->rollBack();
                return ["success" => false, "message" => "Error joining event: " . $e->getMessage()];
            }

    }
    public function leaveEvent($eventId, $userId)
    {
        try {
            $this->db->beginTransaction();

            // Remove participant
            $stmt = $this->db->prepare("DELETE FROM EventParticipants WHERE event_id = ? AND user_id = ?");
            $stmt->execute([$eventId, $userId]);

            // Decrement current_participants count in Events table
            $stmt = $this->db->prepare("UPDATE Events SET current_participants = current_participants - 1 WHERE event_id = ? AND current_participants > 0");
            $stmt->execute([$eventId]);

            $this->db->commit();
            return ['success' => true, 'message' => 'Successfully left event.'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error leaving event: ' . $e->getMessage()];
        }
    }

    public function createEvent($organizerId, $title, $description, $fieldId, $fieldType, $endTime, $startTime, $maxParticipants )
    {
        try{
            $this->db->beginTransaction();

            if(strtotime($startTime) >= strtotime($endTime)){
                throw new Exception("Start time must be before end time.");
            }

            $stmt = $this->db->prepare("INSERT INTO Events (organizer_id, title, description, field_id, sport_type, end_time, start_time, max_participants) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([$organizerId, $title, $description, $fieldId, $fieldType, $endTime, $startTime, $maxParticipants]);

            $eventId = $this->db->lastInsertId();

            //You might want to add the event organizer as participant!

            $stmt = $this->db->prepare("INSERT INTO EventParticipants (event_id, user_id, status) VALUES (?, ?, 'confirmed')");
            $stmt->execute([$eventId, $organizerId]);

            //Also update your participant account!

            $stmt = $this->db->prepare("UPDATE Events SET current_participants = current_participants + 1 WHERE event_id = ?");

            $stmt->execute([$eventId]);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Successfully created event.',
                'event_id' => $eventId
            ];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error creating event: ' . $e->getMessage(),
            ];
        }catch(Exception $e2) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error creating event: ' . $e2->getMessage(),
            ];
        }
    }
}
