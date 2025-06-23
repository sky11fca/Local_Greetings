<?php
require_once __DIR__ . '/AdminController.php';

class AdminBackupController extends AdminController {
    public function createBackup() {
        $user = $this->checkAdminAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendResponse(false, 'Method not allowed', null, 405);
        }
        
        try {
            $backupDir = __DIR__ . '/../../backups/';
            
            // Create backup directory if it doesn't exist
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            $timestamp = date('Y-m-d_H-i-s');
            $backupFile = $backupDir . 'backup_' . $timestamp . '.sql';
            
            // Get database configuration
            $dbConfig = require __DIR__ . '/../config/Database.php';
            $host = $dbConfig['host'] ?? 'localhost';
            $dbname = $dbConfig['database'] ?? 'local_greeter';
            $username = $dbConfig['username'] ?? 'root';
            $password = $dbConfig['password'] ?? '';
            
            // Create backup using mysqldump
            $command = "mysqldump -h $host -u $username";
            if (!empty($password)) {
                $command .= " -p$password";
            }
            $command .= " $dbname > $backupFile";
            
            $output = [];
            $returnVar = 0;
            
            exec($command, $output, $returnVar);
            
            if ($returnVar === 0 && file_exists($backupFile)) {
                // Log the backup
                $this->logActivity('Backup created', "Database backup created: backup_$timestamp.sql");
                
                $this->sendResponse(true, 'Backup created successfully', [
                    'filename' => "backup_$timestamp.sql",
                    'size' => $this->formatBytes(filesize($backupFile)),
                    'timestamp' => $timestamp
                ]);
            } else {
                $this->sendResponse(false, 'Failed to create backup');
            }
            
        } catch (Exception $e) {
            $this->sendResponse(false, 'Error creating backup: ' . $e->getMessage());
        }
    }
    
    private function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

$controller = new AdminBackupController();
$controller->createBackup();
?> 