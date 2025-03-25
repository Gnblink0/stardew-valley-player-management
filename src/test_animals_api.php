<?php
// Test script for animals.php API

// Include database connection
require_once 'config.php';

// Test player ID
$testPlayerId = 1; // Use an existing player ID from your database

echo "Testing animals API with player ID: $testPlayerId\n\n";

// Test direct database query
echo "Direct database query test:\n";
try {
    global $pdo;
    
    $sql = "SELECT a.animal_id, a.name, at.type as type, 
                   CASE 
                       WHEN ba.animal_id IS NOT NULL THEN 'barn' 
                       WHEN ca.animal_id IS NOT NULL THEN 'coop' 
                       ELSE 'unknown' 
                   END as building,
                   ap.produce_type as produce,
                   pao.friendship_level, pao.owned as days_owned
            FROM animals a
            JOIN animal_types at ON a.type_id = at.type_id
            LEFT JOIN barn_animals ba ON a.animal_id = ba.animal_id
            LEFT JOIN coop_animals ca ON a.animal_id = ca.animal_id
            LEFT JOIN animal_produce ap ON a.type_id = ap.type_id
            JOIN player_animals_owned pao ON a.animal_id = pao.animal_id
            WHERE pao.player_id = ? AND pao.owned = 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$testPlayerId]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($result) > 0) {
        echo "Found " . count($result) . " animals for player ID $testPlayerId\n";
        echo "Sample data:\n";
        print_r($result[0]);
    } else {
        echo "No animals found for player ID $testPlayerId\n";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

echo "\n\n";

// Test API endpoint
echo "API endpoint test:\n";
$apiUrl = "http://localhost/stardew-valley-player-management/src/api/animals.php?player_id=$testPlayerId";
echo "Calling API: $apiUrl\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Response Code: $httpCode\n";
if ($httpCode == 200) {
    $data = json_decode($response, true);
    echo "API Response:\n";
    print_r($data);
    
    if (isset($data['status']) && $data['status'] === 'success') {
        echo "\nAPI test successful!\n";
    } else {
        echo "\nAPI returned error status\n";
    }
} else {
    echo "API request failed with HTTP code $httpCode\n";
    echo "Response: $response\n";
} 