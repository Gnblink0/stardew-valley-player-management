<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT 
            HOUR(start_time) as hour,
            COUNT(*) as session_count
        FROM game_sessions
        WHERE start_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY HOUR(start_time)
        ORDER BY hour ASC
    ");
    
    echo json_encode($stmt->fetchAll());
} catch (PDOException $e) {
    error_log("Error getting session activity data: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
} 