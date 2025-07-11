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
        LEFT JOIN SportsFields sf ON e.field_id = sf.field_id
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
    WHERE e.event_id IN ( SELECT event_id FROM EventParticipants WHERE user_id = :user_id AND status = 'confirmed') AND e.organizer_id != :user_id";

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

                // Add participant with confirmed status
                $stmt = $this->db->prepare("INSERT INTO EventParticipants (event_id, user_id, status) VALUES (?, ?, 'confirmed')");
                $stmt->execute([$eventId, $userId]);

                // Increment current_participants count in Events table
                $stmt = $this->db->prepare("UPDATE Events SET current_participants = current_participants + 1 WHERE event_id = ?");
                $stmt->execute([$eventId]);

                $event = $this->getEventById($eventId);
                $isFull = ($event['max_participants'] !== null &&
                    $event['current_participants'] >= $event['max_participants']);

                $this->db->commit();

                return ["success" => true, "message" => "Successfully joined event.", 'event_id' => $eventId, 'is_full' => $isFull];
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
            $stmt = $this->db->prepare("DELETE FROM EventParticipants WHERE event_id = :event_id AND user_id = :user_id");
            $stmt->execute([':event_id' => $eventId, ':user_id' => $userId]);

            // Only decrement if a row was actually deleted
            if ($stmt->rowCount() > 0) {
                $stmt = $this->db->prepare("UPDATE Events SET current_participants = current_participants - 1 WHERE event_id = :event_id AND current_participants > 0");
                $stmt->execute([':event_id' => $eventId]);
            } else {
                // If no row was deleted, it means the user wasn't a participant.
                // We can choose to return an error or just succeed silently.
                // For a "leave" action, succeeding silently is often better UX.
            }

            $this->db->commit();
            return ['success' => true, 'message' => 'Successfully left event.'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error leaving event: ' . $e->getMessage()];
        }
    }

    public function createEvent($organizerId, $data)
    {
        try{
            $this->db->beginTransaction();

            if(strtotime($data['start_time']) >= strtotime($data['end_time'])){
                throw new Exception("Start time must be before end time.");
            }

            $stmt = $this->db->prepare(
                "INSERT INTO Events (organizer_id, title, description, field_id, sport_type, end_time, start_time, max_participants) 
                 VALUES (:organizer_id, :title, :description, :field_id, :sport_type, :end_time, :start_time, :max_participants)"
            );

            $stmt->execute([
                ':organizer_id' => $organizerId,
                ':title' => $data['title'],
                ':description' => $data['description'],
                ':field_id' => $data['field_id'],
                ':sport_type' => $data['sport_type'],
                ':end_time' => $data['end_time'],
                ':start_time' => $data['start_time'],
                ':max_participants' => $data['max_participants']
            ]);

            $eventId = $this->db->lastInsertId();

            //Add the event organizer as participant
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

    public function getPublicEvents($limit, $offset, $sportType = null, $search = null, $excludeUserId = null)
    {
        $query = "SELECT 
            e.event_id, e.title, e.description, e.organizer_id, e.start_time, e.end_time,
            e.max_participants, e.current_participants,
            u.username as organizer_name,
            sf.name as field_name, sf.address,
            e.sport_type
        FROM Events e
        JOIN Users u ON e.organizer_id = u.user_id
        LEFT JOIN SportsFields sf ON e.field_id = sf.field_id
        WHERE e.end_time > NOW()";

        $params = [];

        // Exclude events created by the current user (only if user is logged in)
        if ($excludeUserId) {
            $query .= " AND e.organizer_id != :exclude_user_id";
            $params[':exclude_user_id'] = $excludeUserId;
        }

        if ($sportType) {
            $query .= " AND e.sport_type = :sport_type";
            $params[':sport_type'] = $sportType;
        }

        if ($search) {
            $query .= " AND (e.title LIKE :search OR e.description LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        $query .= " ORDER BY e.start_time ASC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);

        if ($excludeUserId) {
            $stmt->bindValue(':exclude_user_id', $excludeUserId, PDO::PARAM_INT);
        }
        if ($sportType) {
            $stmt->bindValue(':sport_type', $sportType, PDO::PARAM_STR);
        }
        if ($search) {
            $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserParticipationStatus($eventIds, $userId)
    {
        if (empty($eventIds)) {
            return [];
        }
        
        $placeholders = str_repeat('?,', count($eventIds) - 1) . '?';
        $query = "SELECT event_id FROM EventParticipants WHERE event_id IN ($placeholders) AND user_id = ? AND status = 'confirmed'";
        
        $params = array_merge($eventIds, [$userId]);
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        $participatedEvents = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $result = [];
        
        foreach ($eventIds as $eventId) {
            $result[$eventId] = in_array($eventId, $participatedEvents);
        }
        
        return $result;
    }

    public function countPublicEvents($sportType = null, $search = null, $excludeUserId = null)
    {
        $query = "SELECT COUNT(*) FROM Events e WHERE e.end_time > NOW()";
        $params = [];

        // Exclude events created by the current user (only if user is logged in)
        if ($excludeUserId) {
            $query .= " AND e.organizer_id != :exclude_user_id";
            $params[':exclude_user_id'] = $excludeUserId;
        }

        if ($sportType) {
            $query .= " AND e.sport_type = :sport_type";
            $params[':sport_type'] = $sportType;
        }

        if ($search) {
            $query .= " AND (e.title LIKE :search OR e.description LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        $stmt = $this->db->prepare($query);
        
        if ($excludeUserId) {
            $stmt->bindValue(':exclude_user_id', $excludeUserId, PDO::PARAM_INT);
        }
        if ($sportType) {
            $stmt->bindValue(':sport_type', $sportType, PDO::PARAM_STR);
        }
        if ($search) {
            $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        }
        
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function updateEvent($eventId, $userId, $data)
    {
        $event = $this->getEventById($eventId);
        if (!$event) {
            return ['success' => false, 'message' => 'Event not found.'];
        }
        // Allow admin override (userId = 0) or if user is the organizer
        if ($userId !== 0 && $event['organizer_id'] != $userId) {
            return ['success' => false, 'message' => 'You are not authorized to edit this event.'];
        }

        // Validate start and end times
        if (strtotime($data['start_time']) >= strtotime($data['end_time'])) {
            return ['success' => false, 'message' => 'Start time must be before end time.'];
        }

        // Build SQL and params
        $sql = "UPDATE Events SET 
                    title = :title, 
                    description = :description, 
                    field_id = :field_id, 
                    start_time = :start_time, 
                    end_time = :end_time, 
                    max_participants = :max_participants
                WHERE event_id = :event_id";
        $params = [
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':field_id' => $data['field_id'],
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'],
            ':max_participants' => $data['max_participants'],
            ':event_id' => $eventId
        ];
        // If not admin, restrict to organizer
        if ($userId !== 0) {
            $sql .= " AND organizer_id = :organizer_id";
            $params[':organizer_id'] = $userId;
        }

        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $this->db->commit();
            return ['success' => true, 'message' => 'Event updated successfully.'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function deleteEvent($eventId, $userId)
    {
        // First, verify the user is the owner of the event OR if userId is 0 (admin override)
        $event = $this->getEventById($eventId);
        if (!$event) {
            return ['success' => false, 'message' => 'Event not found.'];
        }
        
        // Allow admin override (userId = 0) or if user is the organizer
        if ($userId !== 0 && $event['organizer_id'] != $userId) {
            return ['success' => false, 'message' => 'You are not authorized to delete this event.'];
        }

        $sql = "DELETE FROM Events WHERE event_id = :event_id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':event_id' => $eventId
            ]);
            
            // The database's ON DELETE CASCADE will handle removing participants
            return ['success' => true, 'message' => 'Event deleted successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function getPastEventsForUser($userId, $limit, $offset)
    {
        $query = "SELECT 
            e.event_id, e.title, e.description, e.start_time, e.end_time,
            e.sport_type, sf.name as field_name, sf.address
        FROM Events e
        JOIN SportsFields sf ON e.field_id = sf.field_id
        JOIN EventParticipants ep ON e.event_id = ep.event_id
        WHERE ep.user_id = :user_id AND e.end_time < NOW()
        ORDER BY e.end_time DESC
        LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countPastEventsForUser($userId)
    {
        $query = "SELECT COUNT(*)
        FROM Events e
        JOIN EventParticipants ep ON e.event_id = ep.event_id
        WHERE ep.user_id = :user_id AND e.end_time < NOW()";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getDb()
    {
        return $this->db;
    }

}
