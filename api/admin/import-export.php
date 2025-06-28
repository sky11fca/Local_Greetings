<?php
require_once __DIR__ . '/AdminController.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/../models/SportsFieldModel.php';

class ImportExportController extends AdminController {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function handleRequest() {
        $user = $this->checkAdminAuth();
        if (!$user) {
            return; // Response already sent by checkAdminAuth
        }
        
        $action = $_GET['operation'] ?? '';
        $dataType = $_GET['type'] ?? '';
        $format = $_GET['format'] ?? 'json';
        
        switch ($action) {
            case 'export':
                $this->exportData($dataType, $format);
                break;
            case 'import':
                $this->importData($dataType, $format);
                break;
            default:
                $this->sendResponse(false, 'Invalid operation', null, 400);
        }
    }
    
    private function exportData($dataType, $format) {
        try {
            $data = [];
            
            switch ($dataType) {
                case 'users':
                    $data = $this->exportUsers();
                    break;
                case 'events':
                    $data = $this->exportEvents();
                    break;
                case 'fields':
                    $data = $this->exportFields();
                    break;
                case 'all':
                    $data = [
                        'users' => $this->exportUsers(),
                        'events' => $this->exportEvents(),
                        'fields' => $this->exportFields()
                    ];
                    break;
                default:
                    $this->sendResponse(false, 'Invalid data type', null, 400);
                    return;
            }
            
            if ($format === 'csv') {
                $this->exportAsCSV($data, $dataType);
            } else {
                $this->exportAsJSON($data, $dataType);
            }
            
            $this->logActivity('export', "Exported $dataType data in $format format");
            
        } catch (Exception $e) {
            $this->sendResponse(false, 'Export failed: ' . $e->getMessage(), null, 500);
        }
    }
    
    private function importData($dataType, $format) {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->sendResponse(false, 'POST method required for import', null, 405);
                return;
            }
            
            $input = file_get_contents('php://input');
            $data = null;
            
            if ($format === 'csv') {
                $data = $this->parseCSV($input);
            } else {
                $data = json_decode($input, true);
            }
            
            if (!$data) {
                $this->sendResponse(false, 'Invalid data format', null, 400);
                return;
            }
            
            $result = [];
            
            switch ($dataType) {
                case 'users':
                    $result = $this->importUsers($data);
                    break;
                case 'events':
                    $result = $this->importEvents($data);
                    break;
                case 'fields':
                    $result = $this->importFields($data);
                    break;
                default:
                    $this->sendResponse(false, 'Invalid data type', null, 400);
                    return;
            }
            
            $this->sendResponse(true, 'Import completed successfully', $result);
            $this->logActivity('import', "Imported $dataType data in $format format");
            
        } catch (Exception $e) {
            $this->sendResponse(false, 'Import failed: ' . $e->getMessage(), null, 500);
        }
    }
    
    private function exportUsers() {
        $stmt = $this->db->prepare("
            SELECT user_id, username, email, is_admin, created_at, reputation_score
            FROM Users 
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function exportEvents() {
        $stmt = $this->db->prepare("
            SELECT e.event_id, e.title, e.description, e.sport_type, e.start_time, e.end_time, 
                   e.max_participants, e.current_participants, e.status, e.created_at,
                   f.name as field_name, u.username as creator_name
            FROM Events e
            LEFT JOIN SportsFields f ON e.field_id = f.field_id
            LEFT JOIN Users u ON e.organizer_id = u.user_id
            ORDER BY e.start_time DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function exportFields() {
        $stmt = $this->db->prepare("
            SELECT field_id, name, type as sport_type, address as location, 
                   amenities, opening_hours, is_public, 
                   ST_X(location) as latitude, ST_Y(location) as longitude
            FROM SportsFields 
            ORDER BY name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function exportAsCSV($data, $dataType) {
        $filename = $dataType . '_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Check if this is an AJAX request (JavaScript fetch)
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if (!$isAjax) {
            // Direct download - set headers for file download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        } else {
            // AJAX request - set JSON response headers
            header('Content-Type: application/json');
        }
        
        // Generate CSV content
        $csvContent = '';
        if (is_array($data) && !empty($data)) {
            $output = fopen('php://temp', 'w+');
            
            // Write headers
            fputcsv($output, array_keys($data[0]));
            
            // Write data
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
            
            rewind($output);
            $csvContent = stream_get_contents($output);
            fclose($output);
        }
        
        if ($isAjax) {
            // Return JSON response for AJAX requests
            $response = [
                'success' => true,
                'filename' => $filename,
                'content' => $csvContent,
                'contentType' => 'text/csv'
            ];
            echo json_encode($response);
        } else {
            // Direct output for file downloads
            echo $csvContent;
        }
        
        if (!$isAjax) {
            exit;
        }
    }
    
    private function exportAsJSON($data, $dataType) {
        $filename = $dataType . '_export_' . date('Y-m-d_H-i-s') . '.json';
        
        // Check if this is an AJAX request (JavaScript fetch)
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if (!$isAjax) {
            // Direct download - set headers for file download
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        } else {
            // AJAX request - set JSON response headers
            header('Content-Type: application/json');
        }
        
        // Generate JSON content
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT);
        
        if ($isAjax) {
            // Return JSON response for AJAX requests
            $response = [
                'success' => true,
                'filename' => $filename,
                'content' => $jsonContent,
                'contentType' => 'application/json'
            ];
            echo json_encode($response);
        } else {
            // Direct output for file downloads
            echo $jsonContent;
        }
        
        if (!$isAjax) {
            exit;
        }
    }
    
    private function parseCSV($csvData) {
        $lines = explode("\n", trim($csvData));
        if (empty($lines)) {
            return [];
        }
        
        $headers = str_getcsv(array_shift($lines));
        $data = [];
        
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            $row = str_getcsv($line);
            if (count($row) === count($headers)) {
                $data[] = array_combine($headers, $row);
            }
        }
        
        return $data;
    }
    
    private function importUsers($data) {
        $results = ['imported' => 0, 'skipped' => 0, 'errors' => []];
        
        foreach ($data as $row) {
            try {
                // Check if user already exists
                $stmt = $this->db->prepare("SELECT user_id FROM Users WHERE email = ?");
                $stmt->execute([$row['email']]);
                
                if ($stmt->fetch()) {
                    $results['skipped']++;
                    continue;
                }
                
                // Insert new user
                $stmt = $this->db->prepare("
                    INSERT INTO Users (username, email, password_hash, is_admin, reputation_score)
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                $passwordHash = password_hash('default_password_123', PASSWORD_DEFAULT);
                $isAdmin = isset($row['is_admin']) ? (int)$row['is_admin'] : 0;
                $reputationScore = isset($row['reputation_score']) ? (int)$row['reputation_score'] : 0;
                
                $stmt->execute([
                    $row['username'],
                    $row['email'],
                    $passwordHash,
                    $isAdmin,
                    $reputationScore
                ]);
                
                $results['imported']++;
                
            } catch (Exception $e) {
                $results['errors'][] = "User {$row['username']}: " . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    private function importEvents($data) {
        $results = ['imported' => 0, 'skipped' => 0, 'errors' => []];
        
        foreach ($data as $row) {
            try {
                // Check if event already exists
                $stmt = $this->db->prepare("SELECT event_id FROM Events WHERE title = ? AND start_time = ?");
                $stmt->execute([$row['title'], $row['start_time']]);
                
                if ($stmt->fetch()) {
                    $results['skipped']++;
                    continue;
                }
                
                // Get field_id
                $fieldId = null;
                if (isset($row['field_name'])) {
                    $stmt = $this->db->prepare("SELECT field_id FROM SportsFields WHERE name = ?");
                    $stmt->execute([$row['field_name']]);
                    $field = $stmt->fetch();
                    $fieldId = $field ? $field['field_id'] : null;
                }
                
                // Get organizer_id
                $organizerId = null;
                if (isset($row['creator_name'])) {
                    $stmt = $this->db->prepare("SELECT user_id FROM Users WHERE username = ?");
                    $stmt->execute([$row['creator_name']]);
                    $user = $stmt->fetch();
                    $organizerId = $user ? $user['user_id'] : null;
                }
                
                // Insert new event
                $stmt = $this->db->prepare("
                    INSERT INTO Events (title, description, organizer_id, field_id, sport_type, start_time, end_time, 
                                      max_participants, current_participants, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $row['title'],
                    $row['description'] ?? '',
                    $organizerId,
                    $fieldId,
                    $row['sport_type'],
                    $row['start_time'],
                    $row['end_time'],
                    $row['max_participants'] ?? 10,
                    $row['current_participants'] ?? 0,
                    $row['status'] ?? 'upcoming'
                ]);
                
                $results['imported']++;
                
            } catch (Exception $e) {
                $results['errors'][] = "Event {$row['title']}: " . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    private function importFields($data) {
        $results = ['imported' => 0, 'skipped' => 0, 'errors' => []];
        
        foreach ($data as $row) {
            try {
                // Check if field already exists
                $stmt = $this->db->prepare("SELECT field_id FROM SportsFields WHERE name = ?");
                $stmt->execute([$row['name']]);
                
                if ($stmt->fetch()) {
                    $results['skipped']++;
                    continue;
                }
                
                // Insert new field
                $stmt = $this->db->prepare("
                    INSERT INTO SportsFields (name, type, address, amenities, opening_hours, is_public, location)
                    VALUES (?, ?, ?, ?, ?, ?, POINT(?, ?))
                ");
                
                $stmt->execute([
                    $row['name'],
                    $row['sport_type'] ?? 'multi-sport',
                    $row['location'] ?? '',
                    $row['amenities'] ?? '{}',
                    $row['opening_hours'] ?? '{}',
                    isset($row['is_public']) ? (int)$row['is_public'] : 1,
                    $row['longitude'] ?? 0,
                    $row['latitude'] ?? 0
                ]);
                
                $results['imported']++;
                
            } catch (Exception $e) {
                $results['errors'][] = "Field {$row['name']}: " . $e->getMessage();
            }
        }
        
        return $results;
    }
}

// Handle the request
$controller = new ImportExportController();
$controller->handleRequest();
?> 