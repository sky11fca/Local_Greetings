<?php

require_once __DIR__ . '/../models/SportsFieldModel.php';

class SportsFieldController
{
    private $sportsFieldModel;
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db;
        $this->sportsFieldModel = new SportsFieldModel($db);
    }

    public function listFields()
    {
        header('Content-Type: application/json');

        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $searchQuery = $_GET['search'] ?? '';
        $sportType = $_GET['sport_type'] ?? '';

        if ($searchQuery || $sportType) {
            $fields = $this->sportsFieldModel->searchFields($searchQuery, $sportType, $limit, $offset);
            $total = $this->sportsFieldModel->countSearchFields($searchQuery, $sportType);
        } else {
            $fields = $this->sportsFieldModel->getAllFields($limit, $offset);
            $total = $this->sportsFieldModel->countAllFields();
        }

        echo json_encode([
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
            'fields' => $fields
        ]);
    }

    public function listAllFieldsSimple()
    {
        header('Content-Type: application/json');
        $fields = $this->sportsFieldModel->getAllFieldsSimple();
        echo json_encode(['success' => true, 'fields' => $fields]);
    }

    public function listAllFieldsWithCoordinates()
    {
        header('Content-Type: application/json');
        $fields = $this->sportsFieldModel->getAllFieldsWithCoordinates();
        echo json_encode(['success' => true, 'fields' => $fields]);
    }

    public function getField($fieldId)
    {
        header('Content-Type: application/json');

        $field = $this->sportsFieldModel->getFieldById($fieldId);
        if ($field) {
            echo json_encode(['success' => true, 'field' => $field]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Sports field not found.']);
        }
    }

    public function getFieldById()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $fieldId = $data['field_id'];

        $field = $this->sportsFieldModel->getFieldById($fieldId);
        if ($field) {
            echo json_encode([
                'success' => true,
                'fields' => $field
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Sports field not found.'
            ]);
        }
    }
} 