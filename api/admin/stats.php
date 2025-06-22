<?php
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'stats' => [
        'totalUsers' => 5,
        'totalEvents' => 12,
        'totalFields' => 4,
        'activeEvents' => 3
    ]
]);
?> 