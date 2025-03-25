<?php
// 包含函数文件
require_once '../functions.php';

// 设置响应头为JSON
header('Content-Type: application/json');

// 使用functions.php中的getPlayers函数获取所有玩家
$players = getPlayers();

if ($players) {
    echo json_encode([
        'status' => 'success',
        'message' => '玩家列表获取成功',
        'data' => $players
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => '获取玩家列表失败',
        'data' => []
    ]);
} 