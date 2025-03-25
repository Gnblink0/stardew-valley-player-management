<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT 
            animal_type as type,
            COUNT(*) as count
        FROM farm_animals
        GROUP BY animal_type
        ORDER BY count DESC
    ");
    
    echo json_encode($stmt->fetchAll());
} catch (PDOException $e) {
    error_log("Error getting animal distribution: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
} 