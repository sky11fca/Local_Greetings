<?php
require_once __DIR__ . '/AdminController.php';
require_once __DIR__ . '/../models/UserModel.php';

class AdminUsersController extends AdminController {
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new UserModel($this->db);
    }
    
    public function handleRequest() {
        $user = $this->checkAdminAuth();
        
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($method) {
            case 'GET':
                $this->getUsers();
                break;
            case 'POST':
                $this->createUser();
                break;
            case 'PUT':
                $this->updateUser();
                break;
            case 'DELETE':
                $this->deleteUser();
                break;
            default:
                $this->sendResponse(false, 'Method not allowed', null, 405);
        }
    }
    
    private function getUsers() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        try {
            if ($id) {
                // Get single user
                $user = $this->userModel->getUserById($id);
                if ($user) {
                    $this->sendResponse(true, 'User retrieved successfully', ['user' => $user]);
                } else {
                    $this->sendResponse(false, 'User not found', null, 404);
                }
            } else {
                // Get users with pagination and search
                $itemsPerPage = 10;
                $offset = ($page - 1) * $itemsPerPage;
                
                $query = "SELECT user_id, username, email, is_admin, created_at FROM Users WHERE 1=1";
                $params = [];
                
                if ($search) {
                    $query .= " AND (username LIKE ? OR email LIKE ?)";
                    $params[] = '%' . $search . '%';
                    $params[] = '%' . $search . '%';
                }
                
                $query .= " ORDER BY created_at DESC LIMIT $itemsPerPage OFFSET $offset";
                $stmt = $this->db->prepare($query);
                $stmt->execute($params);
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Get total count
                $countQuery = "SELECT COUNT(*) as total FROM Users WHERE 1=1";
                $countParams = [];
                
                if ($search) {
                    $countQuery .= " AND (username LIKE ? OR email LIKE ?)";
                    $countParams[] = '%' . $search . '%';
                    $countParams[] = '%' . $search . '%';
                }
                
                $stmt = $this->db->prepare($countQuery);
                $stmt->execute($countParams);
                $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                $pagination = $this->getPaginationInfo($page, $totalUsers, $itemsPerPage);
                
                $this->sendResponse(true, 'Users retrieved successfully', [
                    'users' => $users,
                    'pagination' => $pagination
                ]);
            }
            
        } catch (Exception $e) {
            $this->sendResponse(false, 'Error retrieving users: ' . $e->getMessage());
        }
    }
    
    private function createUser() {
        try {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'user';
            
            if (empty($username) || empty($email) || empty($password)) {
                $this->sendResponse(false, 'Username, email, and password are required');
            }
            
            // Check if email already exists
            $stmt = $this->db->prepare("SELECT user_id FROM Users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $this->sendResponse(false, 'Email already exists');
            }
            
            // Create user
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $isAdmin = ($role === 'admin') ? 1 : 0;
            
            $stmt = $this->db->prepare("INSERT INTO Users (username, email, password_hash, is_admin) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashedPassword, $isAdmin]);
            
            $this->logActivity('User created', "Created user: $username ($email)");
            $this->sendResponse(true, 'User created successfully');
            
        } catch (Exception $e) {
            $this->sendResponse(false, 'Error creating user: ' . $e->getMessage());
        }
    }
    
    private function updateUser() {
        try {
            $input = file_get_contents('php://input');
            parse_str($input, $data);
            
            $userId = $data['id'] ?? null;
            $username = $data['username'] ?? '';
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';
            $role = $data['role'] ?? 'user';
            
            if (!$userId || empty($username) || empty($email)) {
                $this->sendResponse(false, 'User ID, username, and email are required');
            }
            
            // Check if email already exists for other users
            $stmt = $this->db->prepare("SELECT user_id FROM Users WHERE email = ? AND user_id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                $this->sendResponse(false, 'Email already exists');
            }
            
            $isAdmin = ($role === 'admin') ? 1 : 0;
            
            if (!empty($password)) {
                // Update with password
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $this->db->prepare("UPDATE Users SET username = ?, email = ?, password_hash = ?, is_admin = ? WHERE user_id = ?");
                $stmt->execute([$username, $email, $hashedPassword, $isAdmin, $userId]);
            } else {
                // Update without password
                $stmt = $this->db->prepare("UPDATE Users SET username = ?, email = ?, is_admin = ? WHERE user_id = ?");
                $stmt->execute([$username, $email, $isAdmin, $userId]);
            }
            
            $this->logActivity('User updated', "Updated user: $username ($email)");
            $this->sendResponse(true, 'User updated successfully');
            
        } catch (Exception $e) {
            $this->sendResponse(false, 'Error updating user: ' . $e->getMessage());
        }
    }
    
    private function deleteUser() {
        try {
            $userId = $_GET['id'] ?? null;
            
            if (!$userId) {
                $this->sendResponse(false, 'User ID is required');
            }
            
            // Get user info for logging
            $user = $this->userModel->getUserById($userId);
            if (!$user) {
                $this->sendResponse(false, 'User not found', null, 404);
            }
            
            // Check if user has any events
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM Events WHERE organizer_id = ?");
            $stmt->execute([$userId]);
            $eventCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            if ($eventCount > 0) {
                $this->sendResponse(false, 'Cannot delete user with existing events');
            }
            
            // Delete user
            $stmt = $this->db->prepare("DELETE FROM Users WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            $this->logActivity('User deleted', "Deleted user: {$user['username']} ({$user['email']})");
            $this->sendResponse(true, 'User deleted successfully');
            
        } catch (Exception $e) {
            $this->sendResponse(false, 'Error deleting user: ' . $e->getMessage());
        }
    }
}

$controller = new AdminUsersController();
$controller->handleRequest();
?> 