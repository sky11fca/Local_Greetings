<?php
require_once __DIR__ . '/../models/UserModel.php';

class UserController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function updateProfile() {
        // Check if user is logged in (simplified for now, actual authentication would be more robust)
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            return;
        }

        $updateSuccess = $this->userModel->updateProfile($userId, $data);

        header('Content-Type: application/json');
        if ($updateSuccess) {
            // Update session username if it was changed
            if (isset($data['username'])) {
                $_SESSION['username'] = $data['username'];
            }
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
        }
    }
} 