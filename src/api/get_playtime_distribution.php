<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT 
            CASE 
                WHEN total_hours <= 50 THEN 1
                WHEN total_hours <= 100 THEN 2
                WHEN total_hours <= 200 THEN 3
                ELSE 4
            END as range_index,
            COUNT(*) as count
        FROM (
            SELECT 
                player_id,
                SUM(TIMESTAMPDIFF(HOUR, start_time, end_time)) as total_hours
            FROM game_sessions
            GROUP BY player_id
        ) as player_hours
        GROUP BY range_index
        ORDER BY range_index
    ");
    
    $distribution = $stmt->fetchAll();
    
    // Convert to simple array of counts
    $counts = array_map(function($row) {
        return (int)$row['count'];
    }, $distribution);
    
    echo json_encode($counts);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} 