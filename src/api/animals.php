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
$building = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$animalsData = getPlayerAnimals($playerId, $building);

if ($animalsData !== false) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Animals data retrieved successfully',
        'data' => $animalsData
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to retrieve animals data',
        'data' => []
    ]);
} 