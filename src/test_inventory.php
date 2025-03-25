<?php
// 开启错误显示
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 包含函数文件
require_once 'functions.php';

// 设置响应头为纯文本，方便查看
header('Content-Type: text/plain');

// 测试函数
function testPlayerInventory($playerId) {
    global $pdo;
    
    try {
        $sql = "SELECT i.item_id, i.name, i.type, i.value,
                       inv.quantity
                FROM items i
                JOIN inventory inv ON i.item_id = inv.item_id
                WHERE inv.player_id = ?";
        
        echo "Executing SQL: $sql\n";
        echo "With parameter: $playerId\n\n";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$playerId]);
        
        $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $inventory;
    } catch (PDOException $e) {
        echo "SQL Error: " . $e->getMessage() . "\n";
        return false;
    }
}

// 测试函数
$playerId = 1;
echo "Testing getPlayerInventory function for player ID: $playerId\n\n";

$inventory = testPlayerInventory($playerId);

if ($inventory === false) {
    echo "Error: Function returned false\n";
} elseif (empty($inventory)) {
    echo "No inventory items found for this player\n";
} else {
    echo "Found " . count($inventory) . " inventory items:\n\n";
    
    foreach ($inventory as $item) {
        print_r($item);
        echo "\n";
    }
}

// 检查表结构
echo "\n\nChecking table structure:\n";
try {
    // 检查 items 表
    $stmt = $pdo->query("SHOW COLUMNS FROM items");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Items table columns:\n";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    echo "\n";
    
    // 检查 inventory 表
    $stmt = $pdo->query("SHOW COLUMNS FROM inventory");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Inventory table columns:\n";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
} catch (PDOException $e) {
    echo "Error checking table structure: " . $e->getMessage() . "\n";
}

// 检查数据
echo "\n\nChecking data in tables:\n";
try {
    // 检查 items 表中的数据
    $stmt = $pdo->query("SELECT COUNT(*) FROM items");
    $count = $stmt->fetchColumn();
    echo "Items table has $count records\n";
    
    // 检查 inventory 表中的数据
    $stmt = $pdo->query("SELECT COUNT(*) FROM inventory WHERE player_id = $playerId");
    $count = $stmt->fetchColumn();
    echo "Inventory table has $count records for player ID $playerId\n";
    
    // 显示一些示例数据
    echo "\nSample data from items table:\n";
    $stmt = $pdo->query("SELECT * FROM items LIMIT 3");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        print_r($row);
    }
    
    echo "\nSample data from inventory table:\n";
    $stmt = $pdo->query("SELECT * FROM inventory WHERE player_id = $playerId LIMIT 3");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        print_r($row);
    }
} catch (PDOException $e) {
    echo "Error checking data: " . $e->getMessage() . "\n";
} 