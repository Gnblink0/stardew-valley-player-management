<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    // Get data for the last 30 days
    $stmt = $pdo->query("
        SELECT 
            DATE(gs.start_time) as date,
            COUNT(DISTINCT gs.player_id) as active_players,
            COUNT(DISTINCT pa.achievement_id) as achievements_unlocked
        FROM game_sessions gs
        LEFT JOIN player_achievements pa ON DATE(gs.start_time) = DATE(pa.completion_date)
        WHERE gs.start_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(gs.start_time)
        ORDER BY date ASC
    ");
    
    echo json_encode($stmt->fetchAll());
} catch (PDOException $e) {
    error_log("Error getting player engagement data: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
} 