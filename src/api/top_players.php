<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

// get query parameters
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
$criteria = isset($_GET['criteria']) ? $_GET['criteria'] : 'total_gold_earned';

// validate and limit parameters
if ($limit <= 0 || $limit > 20) {
    $limit = 5; // default value
}

// get ranking data
$topPlayers = getTopPlayers($limit, $criteria);

if ($topPlayers !== false) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Top players retrieved successfully',
        'data' => $topPlayers
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to retrieve top players',
        'data' => []
    ]);
} 
