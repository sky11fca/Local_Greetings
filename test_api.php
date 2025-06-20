<?php
// Simple test script to verify sports fields API
require_once 'api/config/Database.php';
require_once 'api/models/SportsFieldModel.php';

try {
    $database = new Database();
    $db = $database->connect();
    
    $model = new SportsFieldModel($db);
    
    // Test getting all fields
    $fields = $model->getAllFields(5, 0);
    $total = $model->countAllFields();
    
    echo "Total fields in database: " . $total . "\n";
    echo "First 5 fields:\n";
    
    foreach ($fields as $field) {
        echo "- " . $field['name'] . " (" . $field['type'] . ") at " . $field['address'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 