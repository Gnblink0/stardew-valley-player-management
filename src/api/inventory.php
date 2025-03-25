<?php
// 包含函数文件
require_once '../functions.php';

// 设置响应头为JSON
header('Content-Type: application/json');

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

// 使用functions.php中的getPlayerInventory函数获取玩家库存数据
$inventoryData = getPlayerInventory($playerId);

if ($inventoryData !== false) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Inventory data retrieved successfully',
        'data' => $inventoryData
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to retrieve inventory data',
        'data' => []
    ]);
} 