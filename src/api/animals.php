<?php
// 包含函数文件
require_once '../functions.php';

// 设置响应头为JSON
header('Content-Type: application/json');

$playerId = 1;

// 检查是否提供了玩家ID
if (!isset($_GET['player_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No player ID provided'
    ]);
    exit;
}

// 获取玩家ID
$playerId = (int)$_GET['player_id'];

// 使用functions.php中的getPlayerAnimals函数获取玩家动物数据
$animalsData = getPlayerAnimals($playerId);

if ($animalsData !== false) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Animal data retrieved successfully',
        'data' => $animalsData
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to retrieve animal data',
        'data' => []
    ]);
} 