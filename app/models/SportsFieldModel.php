<?php

class SportsFieldModel
{
    private $db;

    public function __construct()
    {
        try {
            $this->db = new PDO(
                'mysql:host=127.0.0.1;dbname=local_greeter',
                'bobby',
                'bobbydb3002',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function getAllFields($limit, $offset)
    {
        $stmt = $this->db->prepare("SELECT field_id, name, address, ST_Y(location) AS latitude, ST_X(location) AS longitude, type, amenities, opening_hours, is_public FROM SportsFields LIMIT ? OFFSET ?");
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->bindParam(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchFields($searchQuery, $sportType, $radius, $limit, $offset)
    {
        $sql = "SELECT field_id, name, address, ST_Y(location) AS latitude, ST_X(location) AS longitude, type, amenities, opening_hours, is_public FROM SportsFields WHERE 1=1";
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
        // Radius filtering would require more complex spatial queries and potentially user's current location
        // For simplicity, we'll skip radius filtering here unless specific coordinates are provided

        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
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

    public function countSearchFields($searchQuery, $sportType, $radius)
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
        // Radius filtering would require more complex spatial queries

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
} 