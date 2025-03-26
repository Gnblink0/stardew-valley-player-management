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

$playerId = (int)$_GET['player_id'];
$season = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$cropsData = getPlayerCrops($playerId, $season);

if ($cropsData !== false) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Crops data retrieved successfully',
        'data' => $cropsData
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to retrieve crops data',
        'data' => []
    ]);
} 