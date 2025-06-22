-- Populate AdminLogs table with initial activity
-- This script adds sample admin activities to demonstrate the logging functionality

INSERT INTO AdminLogs (action, details, timestamp) VALUES 
('Admin Module Setup', 'Admin module initialized and configured', NOW()),
('Database Migration', 'AdminLogs table created successfully', NOW()),
('Admin User Created', 'Default admin user account created', NOW()),
('System Check', 'Admin panel accessibility verified', NOW()),
('Test Activity', 'Admin module test completed successfully', NOW()),
('User Management', 'Admin accessed user management section', NOW()),
('Event Management', 'Admin accessed event management section', NOW()),
('Field Management', 'Admin accessed sports field management section', NOW()),
('System Settings', 'Admin accessed system settings section', NOW()),
('Dashboard Access', 'Admin dashboard loaded successfully', NOW());

-- Verify the data was inserted
SELECT 'AdminLogs populated successfully' as status;
SELECT COUNT(*) as total_logs FROM AdminLogs;
SELECT action, details, timestamp FROM AdminLogs ORDER BY timestamp DESC LIMIT 5; 