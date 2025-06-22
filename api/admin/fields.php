<?php
session_start();
require_once __DIR__ . '/AdminController.php';
require_once __DIR__ . '/../models/SportsFieldModel.php';

class AdminFieldsController extends AdminController {
    private $fieldModel;
    
    public function __construct() {
        parent::__construct();
        $this->fieldModel = new SportsFieldModel($this->db);
    }
    
    public function handleRequest() {
        $user = $this->checkAdminAuth();
        
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($method) {
            case 'GET':
                $this->getFields();
                break;
            case 'POST':
                $this->createField();
                break;
            case 'PUT':
                $this->updateField();
                break;
            case 'DELETE':
                $this->deleteField();
                break;
            default:
                $this->sendResponse(false, 'Method not allowed', null, 405);
        }
    }
    
    private function getFields() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        try {
            if ($id) {
                // Get single field
                $field = $this->fieldModel->getFieldById($id);
                if ($field) {
                    $this->sendResponse(true, 'Field retrieved successfully', ['field' => $field]);
                } else {
                    $this->sendResponse(false, 'Field not found', null, 404);
                }
            } else {
                // Get fields with pagination and filters
                $itemsPerPage = 10;
                $offset = ($page - 1) * $itemsPerPage;
                
                $query = "SELECT 
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
                
                if ($search) {
                    $query .= " AND (name LIKE ? OR address LIKE ?)";
                    $params[] = '%' . $search . '%';
                    $params[] = '%' . $search . '%';
                }
                
                if ($type) {
                    $query .= " AND type = ?";
                    $params[] = $type;
                }
                
                $query .= " ORDER BY name ASC LIMIT ? OFFSET ?";
                $params[] = $itemsPerPage;
                $params[] = $offset;
                
                $stmt = $this->db->prepare($query);
                $stmt->execute($params);
                $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Get total count
                $countQuery = "SELECT COUNT(*) as total FROM SportsFields WHERE 1=1";
                $countParams = [];
                
                if ($search) {
                    $countQuery .= " AND (name LIKE ? OR address LIKE ?)";
                    $countParams[] = '%' . $search . '%';
                    $countParams[] = '%' . $search . '%';
                }
                
                if ($type) {
                    $countQuery .= " AND type = ?";
                    $countParams[] = $type;
                }
                
                $stmt = $this->db->prepare($countQuery);
                $stmt->execute($countParams);
                $totalFields = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                $pagination = $this->getPaginationInfo($page, $totalFields, $itemsPerPage);
                
                $this->sendResponse(true, 'Fields retrieved successfully', [
                    'fields' => $fields,
                    'pagination' => $pagination
                ]);
            }
            
        } catch (Exception $e) {
            $this->sendResponse(false, 'Error retrieving fields: ' . $e->getMessage());
        }
    }
    
    private function createField() {
        try {
            $name = $_POST['name'] ?? '';
            $address = $_POST['address'] ?? '';
            $type = $_POST['type'] ?? '';
            $amenities = $_POST['amenities'] ?? '';
            $openingHours = $_POST['opening_hours'] ?? '';
            $isPublic = isset($_POST['is_public']) ? 1 : 0;
            
            if (empty($name) || empty($address) || empty($type)) {
                $this->sendResponse(false, 'Name, address, and type are required');
            }
            
            // Create field with default coordinates (can be updated later)
            $stmt = $this->db->prepare("INSERT INTO SportsFields (name, address, location, type, amenities, opening_hours, is_public) VALUES (?, ?, ST_GeomFromText('POINT(0 0)'), ?, ?, ?, ?)");
            $stmt->execute([$name, $address, $type, $amenities, $openingHours, $isPublic]);
            
            $this->logActivity('Field created', "Created field: $name");
            $this->sendResponse(true, 'Field created successfully');
            
        } catch (Exception $e) {
            $this->sendResponse(false, 'Error creating field: ' . $e->getMessage());
        }
    }
    
    private function updateField() {
        try {
            $input = file_get_contents('php://input');
            parse_str($input, $data);
            
            $fieldId = $data['id'] ?? null;
            $name = $data['name'] ?? '';
            $address = $data['address'] ?? '';
            $type = $data['type'] ?? '';
            $amenities = $data['amenities'] ?? '';
            $openingHours = $data['opening_hours'] ?? '';
            $isPublic = isset($data['is_public']) ? 1 : 0;
            
            if (!$fieldId || empty($name) || empty($address) || empty($type)) {
                $this->sendResponse(false, 'Field ID, name, address, and type are required');
            }
            
            $stmt = $this->db->prepare("UPDATE SportsFields SET name = ?, address = ?, type = ?, amenities = ?, opening_hours = ?, is_public = ? WHERE field_id = ?");
            $stmt->execute([$name, $address, $type, $amenities, $openingHours, $isPublic, $fieldId]);
            
            $this->logActivity('Field updated', "Updated field: $name");
            $this->sendResponse(true, 'Field updated successfully');
            
        } catch (Exception $e) {
            $this->sendResponse(false, 'Error updating field: ' . $e->getMessage());
        }
    }
    
    private function deleteField() {
        try {
            $fieldId = $_GET['id'] ?? null;
            
            if (!$fieldId) {
                $this->sendResponse(false, 'Field ID is required');
            }
            
            // Get field info for logging
            $field = $this->fieldModel->getFieldById($fieldId);
            if (!$field) {
                $this->sendResponse(false, 'Field not found', null, 404);
            }
            
            // Check if field has any events
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM Events WHERE field_id = ?");
            $stmt->execute([$fieldId]);
            $eventCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            if ($eventCount > 0) {
                $this->sendResponse(false, 'Cannot delete field with existing events');
            }
            
            // Delete field
            $stmt = $this->db->prepare("DELETE FROM SportsFields WHERE field_id = ?");
            $stmt->execute([$fieldId]);
            
            $this->logActivity('Field deleted', "Deleted field: {$field['name']}");
            $this->sendResponse(true, 'Field deleted successfully');
            
        } catch (Exception $e) {
            $this->sendResponse(false, 'Error deleting field: ' . $e->getMessage());
        }
    }
}

$controller = new AdminFieldsController();
$controller->handleRequest();
?> 