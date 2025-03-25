<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Set headers for file download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="dashboard_data_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for proper Excel encoding
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

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
    // Export player statistics
    fputcsv($output, ['Player Statistics']);
    fputcsv($output, ['Player Name', 'Total Gold Earned', 'In-Game Days', 'Total Playtime (hours)', 'Achievements Completed']);
    
    $stmt = $pdo->query("
        SELECT 
            p.name,
            ps.total_gold_earned,
            ps.in_game_days,
            ROUND(SUM(TIMESTAMPDIFF(HOUR, gs.start_time, gs.end_time)), 1) as total_hours,
            COUNT(DISTINCT pa.achievement_id) as achievements_completed
        FROM players p
        JOIN player_statistics ps ON p.player_id = ps.player_id
        LEFT JOIN game_sessions gs ON p.player_id = gs.player_id
        LEFT JOIN player_achievements pa ON p.player_id = pa.player_id AND pa.status = 'Completed'
        " . getDateRangeCondition($timeRange) . "
        GROUP BY p.player_id, p.name, ps.total_gold_earned, ps.in_game_days
        ORDER BY ps.total_gold_earned DESC
    ");
    
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['name'],
            $row['total_gold_earned'],
            $row['in_game_days'],
            $row['total_hours'],
            $row['achievements_completed']
        ]);
    }
    
    // Add blank line between sections
    fputcsv($output, []);
    
    // Export farm statistics
    fputcsv($output, ['Farm Statistics']);
    fputcsv($output, ['Season', 'Number of Crops', 'Total Value']);
    
    $stmt = $pdo->query("
        SELECT 
            c.season,
            COUNT(*) as crop_count,
            SUM(c.quantity * i.base_value) as total_value
        FROM crops c
        JOIN items i ON c.item_id = i.item_id
        " . getDateRangeCondition($timeRange) . "
        GROUP BY c.season
        ORDER BY 
            CASE c.season
                WHEN 'Spring' THEN 1
                WHEN 'Summer' THEN 2
                WHEN 'Fall' THEN 3
                WHEN 'Winter' THEN 4
            END
    ");
    
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['season'],
            $row['crop_count'],
            $row['total_value']
        ]);
    }
    
    // Add blank line between sections
    fputcsv($output, []);
    
    // Export most valuable items
    fputcsv($output, ['Most Valuable Items']);
    fputcsv($output, ['Item Name', 'Type', 'Base Value', 'Total Quantity', 'Total Value']);
    
    $stmt = $pdo->query("
        SELECT 
            i.name,
            i.type,
            i.base_value,
            SUM(c.quantity) as total_quantity,
            SUM(c.quantity * i.base_value) as total_value
        FROM items i
        JOIN crops c ON i.item_id = c.item_id
        " . getDateRangeCondition($timeRange) . "
        GROUP BY i.item_id, i.name, i.type, i.base_value
        ORDER BY total_value DESC
        LIMIT 10
    ");
    
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['name'],
            $row['type'],
            $row['base_value'],
            $row['total_quantity'],
            $row['total_value']
        ]);
    }
    
    // Add blank line between sections
    fputcsv($output, []);
    
    // Export session analytics
    fputcsv($output, ['Session Analytics']);
    fputcsv($output, ['Date', 'Total Sessions', 'Average Duration (minutes)', 'Total Playtime (hours)', 'Achievements Earned']);
    
    $stmt = $pdo->query("
        SELECT 
            DATE(start_time) as date,
            COUNT(*) as total_sessions,
            ROUND(AVG(TIMESTAMPDIFF(MINUTE, start_time, end_time)), 1) as avg_duration,
            ROUND(SUM(TIMESTAMPDIFF(HOUR, start_time, end_time)), 1) as total_hours,
            COUNT(DISTINCT pa.achievement_id) as achievements_earned
        FROM game_sessions gs
        LEFT JOIN player_achievements pa ON gs.session_id = pa.session_id
        " . getDateRangeCondition($timeRange) . "
        GROUP BY DATE(start_time)
        ORDER BY date DESC
    ");
    
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['date'],
            $row['total_sessions'],
            $row['avg_duration'],
            $row['total_hours'],
            $row['achievements_earned']
        ]);
    }
    
    fclose($output);
} catch (PDOException $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
} 