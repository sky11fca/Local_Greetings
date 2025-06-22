<?php
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'logs' => [
        [
            'time' => date('Y-m-d H:i:s'),
            'level' => 'info',
            'message' => 'System started.'
        ],
        [
            'time' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'level' => 'warning',
            'message' => 'Low disk space.'
        ],
        [
            'time' => date('Y-m-d H:i:s', strtotime('-2 hours')),
            'level' => 'error',
            'message' => 'Failed to send email.'
        ]
    ]
]); 