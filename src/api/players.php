<?php
require_once 'config.php';

// GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // check if request specific player
    if (isset($_GET['id'])) {
        $playerId = (int)$_GET['id'];
        $query = "SELECT p.*, ps.total_gold_earned, ps.in_game_days 
                 FROM players p 
                 LEFT JOIN player_statistics ps ON p.player_id = ps.player_id 
                 WHERE p.player_id = $playerId";
        
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            $player = $result->fetch_assoc();
            sendResponse(200, "Player retrieved successfully", $player);
        } else {
            sendResponse(404, "Player not found");
        }
    } else {
        // get all players
        $query = "SELECT p.*, ps.total_gold_earned, ps.in_game_days 
                 FROM players p 
                 LEFT JOIN player_statistics ps ON p.player_id = ps.player_id";
        
        $result = $conn->query($query);
        
        if ($result) {
            $players = [];
            while ($row = $result->fetch_assoc()) {
                $players[] = $row;
            }
            sendResponse(200, "Players retrieved successfully", $players);
        } else {
            sendResponse(500, "Failed to retrieve players: " . $conn->error);
        }
    }
}

// POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // validate required fields
    if (!isset($_POST['name']) || !isset($_POST['farm_name']) || 
        trim($_POST['name']) === '' || trim($_POST['farm_name']) === '') {
        sendResponse(400, "Missing required fields: name and farm_name are required");
    }
    
    $name = $conn->real_escape_string($_POST['name']);
    $avatar = isset($_POST['avatar']) ? $conn->real_escape_string($_POST['avatar']) : 'default';
    $farmName = $conn->real_escape_string($_POST['farm_name']);
    
    // begin transaction
    $conn->begin_transaction();
    
    try {
        // insert player data
        $query = "INSERT INTO players (name, avatar, farm_name) VALUES ('$name', '$avatar', '$farmName')";
        
        if ($conn->query($query)) {
            $playerId = $conn->insert_id;
            
            // initialize player statistics data
            $totalGold = isset($_POST['total_gold_earned']) ? (int)$_POST['total_gold_earned'] : 0;
            $inGameDays = isset($_POST['in_game_days']) ? (int)$_POST['in_game_days'] : 0;
            
            $statsQuery = "INSERT INTO player_statistics (player_id, total_gold_earned, in_game_days) 
                          VALUES ($playerId, $totalGold, $inGameDays)";
            
            if ($conn->query($statsQuery)) {
                $conn->commit();
                
                // get new created player data
                $newPlayerQuery = "SELECT p.*, ps.total_gold_earned, ps.in_game_days 
                                  FROM players p 
                                  LEFT JOIN player_statistics ps ON p.player_id = ps.player_id 
                                  WHERE p.player_id = $playerId";
                
                $result = $conn->query($newPlayerQuery);
                $newPlayer = $result->fetch_assoc();
                
                sendResponse(201, "Player created successfully", $newPlayer);
            } else {
                $conn->rollback();
                sendResponse(500, "Failed to create player statistics: " . $conn->error);
            }
        } else {
            $conn->rollback();
            sendResponse(500, "Failed to create player: " . $conn->error);
        }
    } catch (Exception $e) {
        $conn->rollback();
        sendResponse(500, "An error occurred: " . $e->getMessage());
    }
}

// PUT request: update player data
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // parse PUT request data
    parse_str(file_get_contents('php://input'), $putData);
    
    if (!isset($_GET['id'])) {
        sendResponse(400, "Player ID is required");
    }
    
    $playerId = (int)$_GET['id'];
    
    // check if player exists
    $checkQuery = "SELECT * FROM players WHERE player_id = $playerId";
    $result = $conn->query($checkQuery);
    
    if (!$result || $result->num_rows === 0) {
        sendResponse(404, "Player not found");
    }
    
    // build update query
    $updateFields = [];
    
    if (isset($putData['name']) && trim($putData['name']) !== '') {
        $name = $conn->real_escape_string($putData['name']);
        $updateFields[] = "name = '$name'";
    }
    
    if (isset($putData['avatar']) && trim($putData['avatar']) !== '') {
        $avatar = $conn->real_escape_string($putData['avatar']);
        $updateFields[] = "avatar = '$avatar'";
    }
    
    if (isset($putData['farm_name']) && trim($putData['farm_name']) !== '') {
        $farmName = $conn->real_escape_string($putData['farm_name']);
        $updateFields[] = "farm_name = '$farmName'";
    }
    
    if (empty($updateFields)) {
        sendResponse(400, "No fields to update");
    }
    
    // begin transaction
    $conn->begin_transaction();
    
    try {
        // update player data
        $updateQuery = "UPDATE players SET " . implode(", ", $updateFields) . " WHERE player_id = $playerId";
        
        if ($conn->query($updateQuery)) {
            // check if need to update statistics data
            $statsUpdateFields = [];
            
            if (isset($putData['total_gold_earned'])) {
                $totalGold = (int)$putData['total_gold_earned'];
                $statsUpdateFields[] = "total_gold_earned = $totalGold";
            }
            
            if (isset($putData['in_game_days'])) {
                $inGameDays = (int)$putData['in_game_days'];
                $statsUpdateFields[] = "in_game_days = $inGameDays";
            }
            
            // if there is statistics data to update
            if (!empty($statsUpdateFields)) {
                $statsUpdateQuery = "UPDATE player_statistics SET " . implode(", ", $statsUpdateFields) . 
                                   " WHERE player_id = $playerId";
                
                if (!$conn->query($statsUpdateQuery)) {
                    $conn->rollback();
                    sendResponse(500, "Failed to update player statistics: " . $conn->error);
                }
            }
            
            $conn->commit();
            
            // get updated player data
            $updatedPlayerQuery = "SELECT p.*, ps.total_gold_earned, ps.in_game_days 
                                  FROM players p 
                                  LEFT JOIN player_statistics ps ON p.player_id = ps.player_id 
                                  WHERE p.player_id = $playerId";
            
            $result = $conn->query($updatedPlayerQuery);
            $updatedPlayer = $result->fetch_assoc();
            
            sendResponse(200, "Player updated successfully", $updatedPlayer);
        } else {
            $conn->rollback();
            sendResponse(500, "Failed to update player: " . $conn->error);
        }
    } catch (Exception $e) {
        $conn->rollback();
        sendResponse(500, "An error occurred: " . $e->getMessage());
    }
}

// DELETE request
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!isset($_GET['id'])) {
        sendResponse(400, "Player ID is required");
    }
    
    $playerId = (int)$_GET['id'];
    
    // check if player exists
    $checkQuery = "SELECT * FROM players WHERE player_id = $playerId";
    $result = $conn->query($checkQuery);
    
    if (!$result || $result->num_rows === 0) {
        sendResponse(404, "Player not found");
    }
    
    // begin transaction
    $conn->begin_transaction();
    
    try {
        // delete player related data
        // 1. delete player achievements
        $deleteAchievementsQuery = "DELETE FROM player_achievements 
                                   WHERE session_id IN (SELECT session_id FROM game_sessions WHERE player_id = $playerId)";
        $conn->query($deleteAchievementsQuery);
        
        // 2. delete game sessions
        $deleteSessionsQuery = "DELETE FROM game_sessions WHERE player_id = $playerId";
        $conn->query($deleteSessionsQuery);
        
        // 3. delete inventory
        $deleteInventoryQuery = "DELETE FROM inventory WHERE player_id = $playerId";
        $conn->query($deleteInventoryQuery);
        
        // 4. delete owned animals
        $deleteAnimalsQuery = "DELETE FROM player_animals_owned WHERE player_id = $playerId";
        $conn->query($deleteAnimalsQuery);
        
        // 5. delete harvested crops
        $deleteCropsQuery = "DELETE FROM player_crops_harvested WHERE player_id = $playerId";
        $conn->query($deleteCropsQuery);
        
        // 6. delete statistics data
        $deleteStatsQuery = "DELETE FROM player_statistics WHERE player_id = $playerId";
        $conn->query($deleteStatsQuery);
        
        // 7. delete player
        $deletePlayerQuery = "DELETE FROM players WHERE player_id = $playerId";
        
        if ($conn->query($deletePlayerQuery)) {
            $conn->commit();
            sendResponse(200, "Player deleted successfully");
        } else {
            $conn->rollback();
            sendResponse(500, "Failed to delete player: " . $conn->error);
        }
    } catch (Exception $e) {
        $conn->rollback();
        sendResponse(500, "An error occurred: " . $e->getMessage());
    }
}

// if request method is not supported
sendResponse(405, "Method not allowed");
?>
