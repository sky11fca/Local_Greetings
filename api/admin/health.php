<?php
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'health' => [
        'database' => true,
        'email' => true,
        'diskSpace' => 42
    ]
]);
?> 