<?php
require_once '../functions.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'no player id provided'
    ]);
    exit;
}

$playerId = (int)$_GET['id'];

$playerData = getPlayers($playerId);

if ($playerData) {
    $playerStats = getPlayerStatistics($playerId);
    
    if ($playerStats) {
        $playerData = array_merge($playerData, $playerStats);
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'success get player data',
        'data' => $playerData
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'player not found'
    ]);
} 