<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT 
            season,
            COUNT(*) as crop_count
        FROM crops
        GROUP BY season
        ORDER BY 
            CASE season
                WHEN 'Spring' THEN 1
                WHEN 'Summer' THEN 2
                WHEN 'Fall' THEN 3
                WHEN 'Winter' THEN 4
            END
    ");
    
    $crops = $stmt->fetchAll();
    
    // Convert to simple array of counts
    $counts = array_map(function($row) {
        return (int)$row['crop_count'];
    }, $crops);
    
    echo json_encode($counts);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} 