<?php
require_once '../functions.php';

header('Content-Type: application/json');

// 获取查询参数
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
$criteria = isset($_GET['criteria']) ? $_GET['criteria'] : 'total_gold_earned';

// 验证并限制参数
if ($limit <= 0 || $limit > 20) {
    $limit = 5; // 默认值
}

// 获取排名数据
$topPlayers = getTopPlayers($limit, $criteria);

if ($topPlayers !== false) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Top players retrieved successfully',
        'data' => $topPlayers
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to retrieve top players',
        'data' => []
    ]);
} 