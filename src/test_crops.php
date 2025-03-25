<?php
// 开启错误显示
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 包含函数文件
require_once 'functions.php';

// 设置响应头为纯文本，方便查看
header('Content-Type: text/plain');

// 修改 getPlayerCrops 函数以显示错误
function testPlayerCrops($playerId) {
    global $pdo;
    
    try {
        $sql = "SELECT c.crop_id, c.name, c.season,
                       pch.harvested, pch.sold
                FROM crops c
                JOIN player_crops_harvested pch ON c.crop_id = pch.crop_id
                WHERE pch.player_id = ?";
        
        echo "Executing SQL: $sql\n";
        echo "With parameter: $playerId\n\n";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$playerId]);
        
        $crops = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $crops;
    } catch (PDOException $e) {
        echo "SQL Error: " . $e->getMessage() . "\n";
        return false;
    }
}

// 测试函数
$playerId = 1;
echo "Testing getPlayerCrops function for player ID: $playerId\n\n";

$crops = testPlayerCrops($playerId);

if ($crops === false) {
    echo "Error: Function returned false\n";
} elseif (empty($crops)) {
    echo "No crops found for this player\n";
} else {
    echo "Found " . count($crops) . " crops:\n\n";
    
    foreach ($crops as $crop) {
        print_r($crop);
        echo "\n";
    }
}

// 检查表结构
echo "\n\nChecking table structure:\n";
try {
    // 检查 crops 表
    $stmt = $pdo->query("SHOW COLUMNS FROM crops");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Crops table columns:\n";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    echo "\n";
    
    // 检查 player_crops_harvested 表
    $stmt = $pdo->query("SHOW COLUMNS FROM player_crops_harvested");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Player_crops_harvested table columns:\n";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
} catch (PDOException $e) {
    echo "Error checking table structure: " . $e->getMessage() . "\n";
}

// 检查数据
echo "\n\nChecking data in tables:\n";
try {
    // 检查 crops 表中的数据
    $stmt = $pdo->query("SELECT COUNT(*) FROM crops");
    $count = $stmt->fetchColumn();
    echo "Crops table has $count records\n";
    
    // 检查 player_crops_harvested 表中的数据
    $stmt = $pdo->query("SELECT COUNT(*) FROM player_crops_harvested WHERE player_id = $playerId");
    $count = $stmt->fetchColumn();
    echo "Player_crops_harvested table has $count records for player ID $playerId\n";
    
    // 显示一些示例数据
    echo "\nSample data from crops table:\n";
    $stmt = $pdo->query("SELECT * FROM crops LIMIT 3");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        print_r($row);
    }
    
    echo "\nSample data from player_crops_harvested table:\n";
    $stmt = $pdo->query("SELECT * FROM player_crops_harvested WHERE player_id = $playerId LIMIT 3");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        print_r($row);
    }
} catch (PDOException $e) {
    echo "Error checking data: " . $e->getMessage() . "\n";
}

// 检查 PDO 错误
if (isset($pdo)) {
    $errorInfo = $pdo->errorInfo();
    if ($errorInfo[0] !== '00000') {
        echo "\nDatabase Error: " . $errorInfo[2];
    }
}

// 显示 PDO 变量
echo "\n\nPDO variable status:\n";
echo "PDO is " . (isset($pdo) ? "defined" : "not defined") . "\n";
if (isset($pdo)) {
    echo "PDO is " . (is_object($pdo) ? "an object" : "not an object") . "\n";
    echo "PDO class: " . (is_object($pdo) ? get_class($pdo) : "N/A") . "\n";
} 