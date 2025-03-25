<?php
require_once '../functions.php';

header('Content-Type: application/json');

// 检查请求方法
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Only POST method is allowed'
    ]);
    exit;
}

// 检查必需参数
if (!isset($_POST['id']) || !isset($_POST['name'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required parameters'
    ]);
    exit;
}

$playerId = (int)$_POST['id'];
$playerName = trim($_POST['name']);
$farmName = isset($_POST['farm_name']) ? trim($_POST['farm_name']) : '';

// 验证数据
if (empty($playerName)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Player name cannot be empty'
    ]);
    exit;
}

// 更新玩家信息
try {
    global $pdo;
    
    $sql = "UPDATE players SET name = ?, farm_name = ? WHERE player_id = ?";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$playerName, $farmName, $playerId]);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Player information updated successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update player information'
        ]);
    }
} catch (PDOException $e) {
    error_log("Error updating player: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred'
    ]);
}