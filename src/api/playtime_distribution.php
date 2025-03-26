<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

try {
    // 定义更细致的游戏时间区间（小时）
    $ranges = [
        [0, 1, '< 1h'],        // 新手玩家
        [1, 3, '1-3h'],        // 初次体验
        [3, 6, '3-6h'],        // 短期游戏
        [6, 12, '6-12h'],      // 中等时长
        [12, 24, '12-24h'],    // 较长游戏
        [24, 48, '1-2 days'],  // 深度玩家
        [48, 72, '2-3 days'],  // 资深玩家
        [72, 120, '3-5 days'], // 专家玩家
        [120, null, '5+ days'] // 超级玩家
    ];

    $data = [
        'labels' => [],
        'values' => []
    ];

    // 为每个区间构建查询条件
    foreach ($ranges as $range) {
        $start = $range[0] * 60; // 转换为分钟
        
        if ($range[1] === null) {
            // 处理最后一个区间
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
            $end = $range[1] * 60; // 转换为分钟
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

    // 发送响应
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
