<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$selectedWeek = $data['week'] ?? 'all';
$selectedPlayers = $data['players'] ?? [];

try {
    // Base query for all weeks or specific week
    $weekCondition = $selectedWeek !== 'all' 
        ? "AND DATE_FORMAT(gs.start_time, '%Y-%u') = :week" 
        : "";
    
    // Player condition
    $playerCondition = !empty($selectedPlayers) 
        ? "AND gs.player_id IN (" . implode(',', array_map('intval', $selectedPlayers)) . ")" 
        : "";
    
    // Query to get weekly playtime data
    $query = "
        SELECT 
            DATE_FORMAT(gs.start_time, '%Y-%u') as week_key,
            DATE_FORMAT(gs.start_time, '%Y Week %u') as week_label,
            p.player_id,
            p.name as player_name,
            COUNT(DISTINCT gs.session_id) as session_count,
            SUM(TIMESTAMPDIFF(MINUTE, gs.start_time, gs.end_time)) as total_playtime
        FROM game_sessions gs
        JOIN players p ON gs.player_id = p.player_id
        WHERE 1=1 
        $weekCondition
        $playerCondition
        GROUP BY week_key, p.player_id, p.name
        ORDER BY week_key DESC, total_playtime DESC
    ";
    
    $stmt = $pdo->prepare($query);
    if ($selectedWeek !== 'all') {
        $stmt->bindParam(':week', $selectedWeek);
    }
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    // Prepare data for chart and table
    $chartData = [
        'labels' => [],
        'datasets' => []
    ];
    
    $tableData = [];
    $playerData = [];
    $weeks = [];
    
    // Process results
    foreach ($results as $row) {
        if (!in_array($row['week_label'], $weeks)) {
            $weeks[] = $row['week_label'];
        }
        
        // Prepare table data
        $tableData[] = [
            'week' => $row['week_label'],
            'player_name' => $row['player_name'],
            'total_playtime' => $row['total_playtime'],
            'session_count' => $row['session_count'],
            'avg_session' => $row['total_playtime'] / $row['session_count']
        ];
        
        // Prepare chart data
        if (!isset($playerData[$row['player_name']])) {
            $playerData[$row['player_name']] = array_fill(0, count($weeks), 0);
        }
        $weekIndex = array_search($row['week_label'], $weeks);
        $playerData[$row['player_name']][$weekIndex] = round($row['total_playtime'] / 60, 1); // Convert to hours
    }
    
    // Build chart datasets
    $colors = [
        'rgba(255, 99, 132, 0.5)',   // Red
        'rgba(54, 162, 235, 0.5)',   // Blue
        'rgba(255, 206, 86, 0.5)',   // Yellow
        'rgba(75, 192, 192, 0.5)',   // Teal
        'rgba(153, 102, 255, 0.5)',  // Purple
        'rgba(255, 159, 64, 0.5)',   // Orange
        'rgba(76, 175, 80, 0.5)',    // Green
        'rgba(233, 30, 99, 0.5)',    // Pink
        'rgba(121, 85, 72, 0.5)',    // Brown
        'rgba(96, 125, 139, 0.5)'    // Blue Grey
    ];
    
    $i = 0;
    foreach ($playerData as $player => $data) {
        $chartData['datasets'][] = [
            'label' => $player,
            'data' => $data,
            'backgroundColor' => $colors[$i % count($colors)],
            'borderColor' => str_replace('0.5)', '1)', $colors[$i % count($colors)]),
            'borderWidth' => 1
        ];
        $i++;
    }
    
    $chartData['labels'] = $weeks;
    
    // Send response
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'chart_data' => $chartData,
        'table_data' => $tableData
    ]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch weekly playtime data: ' . $e->getMessage()
    ]);
} 