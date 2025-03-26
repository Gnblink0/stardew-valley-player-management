<?php
require_once '../functions.php';

header('Content-Type: application/json');

if (!isset($_GET['player_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No player ID provided'
    ]);
    exit;
}

if (!isset($_GET['player_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No player ID provided'
    ]);
    exit;
}

$playerId = (int)$_GET['player_id'];
$type = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$inventoryData = getPlayerInventory($playerId, $type);

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