<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    // Get total number of achievements
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM achievements");
    $total = $stmt->fetch()['total'];
    
    // Get count of achievements by status
    $stmt = $pdo->query("
        SELECT 
            status,
            COUNT(*) as count
        FROM player_achievements
        GROUP BY status
    ");
    $statusCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $response = [
        'total' => $total,
        'completed' => $statusCounts['Completed'] ?? 0,
        'in_progress' => $statusCounts['In Progress'] ?? 0,
        'not_started' => $total - (($statusCounts['Completed'] ?? 0) + ($statusCounts['In Progress'] ?? 0))
    ];
    
    echo json_encode($response);
} catch (PDOException $e) {
    error_log("Error getting achievement status distribution: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
} 