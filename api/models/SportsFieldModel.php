<?php

class SportsFieldModel
{
    private $db;

    public function __construct($db)
    {
        if (!$db) {
            // If no database connection is provided, it's a critical error.
            // The controller is responsible for providing this.
            throw new \Exception("Database connection is not provided to SportsFieldModel.");
        }
        $this->db = $db;
    }

    public function getAllFields($limit, $offset)
    {
        $stmt = $this->db->prepare("SELECT field_id, name, address, ST_Y(location) AS latitude, ST_X(location) AS longitude, type, amenities, opening_hours, is_public FROM SportsFields ORDER BY field_id LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllFieldsSimple()
    {
        $stmt = $this->db->prepare("SELECT field_id, name, address FROM SportsFields ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchFields($searchQuery, $sportType, $limit, $offset)
    {
        $sql = "SELECT 
                    field_id, 
                    name, 
                    address, 
                    ST_Y(location) AS latitude, 
                    ST_X(location) AS longitude, 
                    type, 
                    amenities, 
                    opening_hours, 
                    is_public 
FROM SportsFields 
WHERE 1=1";
        $params = [];

        if ($searchQuery) {
            $sql .= " AND (name LIKE :search OR address LIKE :search)";
            $params[':search'] = '%' . $searchQuery . '%';
        }
        if ($sportType) {
            $sql .= " AND type = :sport_type";
            $params[':sport_type'] = $sportType;
        }

        $sql .= " ORDER BY field_id LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $this->db->prepare($sql);

        // Bind common params with their types
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        if ($searchQuery) {
             $stmt->bindValue(':search', '%' . $searchQuery . '%', PDO::PARAM_STR);
        }
        if ($sportType) {
            $stmt->bindValue(':sport_type', $sportType, PDO::PARAM_STR);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFieldById($fieldId)
    {
        $stmt = $this->db->prepare("SELECT field_id, name, address, ST_Y(location) AS latitude, ST_X(location) AS longitude, type, amenities, opening_hours, is_public FROM SportsFields WHERE field_id = ?");
        $stmt->execute([$fieldId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function countAllFields()
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM SportsFields");
        return $stmt->fetchColumn();
    }

    public function countSearchFields($searchQuery, $sportType)
    {
        $sql = "SELECT COUNT(*) FROM SportsFields WHERE 1=1";
        $params = [];

        if ($searchQuery) {
            $sql .= " AND (name LIKE ? OR address LIKE ?)";
            $params[] = '%' . $searchQuery . '%';
            $params[] = '%' . $searchQuery . '%';
        }
        if ($sportType) {
            $sql .= " AND type = ?";
            $params[] = $sportType;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
} 