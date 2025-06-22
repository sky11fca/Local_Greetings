-- Admin Logs Table Migration
-- This table stores admin activities for audit purposes

CREATE TABLE IF NOT EXISTS AdminLogs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_timestamp (timestamp),
    INDEX idx_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add is_admin column to Users table if it doesn't exist
ALTER TABLE Users ADD COLUMN IF NOT EXISTS is_admin TINYINT(1) DEFAULT 0;

-- Create an admin user (password: admin123)
-- You should change this password in production
INSERT INTO Users (username, email, password_hash, is_admin, created_at) 
VALUES ('admin', 'admin@localgreeter.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW())
ON DUPLICATE KEY UPDATE is_admin = 1; 