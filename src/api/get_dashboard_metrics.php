<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Get time range from request
$timeRange = isset($_GET['timeRange']) ? $_GET['timeRange'] : 'all';

// Helper function to get date range condition
function getDateRangeCondition($timeRange) {
    switch ($timeRange) {
        case '7days':
            return "WHERE start_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        case '30days':
            return "WHERE start_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        case 'year':
            return "WHERE start_time >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
        default:
            return "";
    }
}

try {
    // Get total players
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM players");
    $totalPlayers = $stmt->fetch()['total'];
    
    // Get total gold earned
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(total_gold_earned), 0) as total
        FROM player_statistics
    ");
    $totalGold = $stmt->fetch()['total'];
    
    // Get total playtime
    $stmt = $pdo->query("
        SELECT ROUND(SUM(TIMESTAMPDIFF(HOUR, start_time, end_time)), 1) as total
        FROM game_sessions
        " . getDateRangeCondition($timeRange)
    );
    $totalPlaytime = $stmt->fetch()['total'] ?? 0;
    
    // Get completed achievements
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT achievement_id) as total
        FROM player_achievements
        WHERE status = 'Completed'
        " . getDateRangeCondition($timeRange)
    );
    $achievementsCompleted = $stmt->fetch()['total'] ?? 0;
    
    // Return metrics as JSON
    echo json_encode([
        'total_players' => (int)$totalPlayers,
        'total_gold' => (int)$totalGold,
        'total_playtime' => (float)$totalPlaytime,
        'achievements_completed' => (int)$achievementsCompleted
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} 