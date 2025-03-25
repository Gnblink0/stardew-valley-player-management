<?php
require_once '../functions.php';

header('Content-Type: application/json');

$players = getPlayers();

if ($players) {
    echo json_encode([
        'status' => 'success',
        'message' => 'success to get players',
        'data' => $players
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'failed to get players',
        'data' => []
    ]);
} 