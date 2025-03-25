<?php
// 包含函数文件
require_once '../functions.php';

// 设置响应头为JSON
header('Content-Type: application/json');

// 检查是否提供了玩家ID
if (!isset($_GET['id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => '未提供玩家ID'
    ]);
    exit;
}

// 获取玩家ID
$playerId = (int)$_GET['id'];

// 使用functions.php中的getPlayers函数获取玩家数据
$playerData = getPlayers($playerId);

if ($playerData) {
    // 获取玩家统计数据
    $playerStats = getPlayerStatistics($playerId);
    
    // 合并玩家数据和统计数据
    if ($playerStats) {
        $playerData = array_merge($playerData, $playerStats);
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => '玩家数据获取成功',
        'data' => $playerData
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => '找不到该玩家'
    ]);
} 