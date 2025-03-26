<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

try {
    $ranges = [
        [0, 1, '< 1h'],       
        [1, 3, '1-3h'],       
        [3, 6, '3-6h'],       
        [6, 12, '6-12h'],     
        [12, 24, '12-24h'],   
        [24, 48, '1-2 days'], 
        [48, 72, '2-3 days'], 
        [72, 120, '3-5 days'],
        [120, null, '5+ days']
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
                SELECT COUNT(DISTINCT p.player_id) as count
                FROM players p
                LEFT JOIN game_sessions gs ON p.player_id = gs.player_id
                GROUP BY p.player_id
                HAVING COALESCE(SUM(TIMESTAMPDIFF(MINUTE, gs.start_time, gs.end_time)), 0) >= ?
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$start]);
        } else {
            $end = $range[1] * 60; // convert to minutes
            $sql = "
                SELECT COUNT(DISTINCT p.player_id) as count
                FROM players p
                LEFT JOIN game_sessions gs ON p.player_id = gs.player_id
                GROUP BY p.player_id
                HAVING COALESCE(SUM(TIMESTAMPDIFF(MINUTE, gs.start_time, gs.end_time)), 0) >= ?
                AND COALESCE(SUM(TIMESTAMPDIFF(MINUTE, gs.start_time, gs.end_time)), 0) < ?
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
        'message' => 'Failed to fetch playtime distribution: ' . $e->getMessage()
    ]);
} 
