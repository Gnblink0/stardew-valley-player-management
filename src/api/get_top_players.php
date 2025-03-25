<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT p.name, ps.total_gold_earned
        FROM players p
        JOIN player_statistics ps ON p.player_id = ps.player_id
        ORDER BY ps.total_gold_earned DESC
        LIMIT 5
    ");
    
    $players = $stmt->fetchAll();
    
    echo json_encode($players);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} 