<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

try {
    // define session duration ranges (hours)
    $ranges = [
        [0, 0.5, '< 30min'],
        [0.5, 1, '30min-1h'],
        [1, 2, '1-2h'],
        [2, 3, '2-3h'],
        [3, 4, '3-4h'],
        [4, 6, '4-6h'],
        [6, 8, '6-8h'],
        [8, null, '8h+']
    ];

    $data = [
        'labels' => [],
        'values' => []
    ];

    // build query conditions for each range
    foreach ($ranges as $range) {
        $start = $range[0] * 60; // convert to minutes
        
        if ($range[1] === null) {
            // handle last range
            $sql = "
                SELECT COUNT(*) as count
                FROM game_sessions
                WHERE TIMESTAMPDIFF(MINUTE, start_time, end_time) >= ?
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$start]);
        } else {
            $end = $range[1] * 60; // convert to minutes
            $sql = "
                SELECT COUNT(*) as count
                FROM game_sessions
                WHERE TIMESTAMPDIFF(MINUTE, start_time, end_time) >= ?
                AND TIMESTAMPDIFF(MINUTE, start_time, end_time) < ?
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$start, $end]);
        }

        $count = $stmt->fetchColumn() ?: 0;
        
        $data['labels'][] = $range[2];
        $data['values'][] = (int)$count;
    }

    // send response
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'data' => $data
    ]);

} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch session analytics: ' . $e->getMessage()
    ]);
} 
