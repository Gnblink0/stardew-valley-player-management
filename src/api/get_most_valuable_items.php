<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT 
            i.name,
            i.base_value as value
        FROM items i
        WHERE i.type IN ('Artifact', 'Mineral', 'Gem')
        ORDER BY i.base_value DESC
        LIMIT 5
    ");
    
    $items = $stmt->fetchAll();
    
    echo json_encode($items);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} 