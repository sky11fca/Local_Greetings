<?php
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'activities' => [
        [
            'timestamp' => date('Y-m-d H:i:s'),
            'description' => 'Admin logged in.'
        ],
        [
            'timestamp' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'description' => 'User maria_sport registered.'
        ],
        [
            'timestamp' => date('Y-m-d H:i:s', strtotime('-2 hours')),
            'description' => 'Event "Football Match" created.'
        ]
    ]
]);
?> 