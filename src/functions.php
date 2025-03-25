<?php
require_once 'config.php';

// Function to get all players or a specific player
function getPlayers($playerId = null) {
    global $pdo;
    
    if (!$pdo) {
        error_log("PDO connection is null in getPlayers()");
        return false;
    }
    
    try {
        if ($playerId) {
            $stmt = $pdo->prepare("SELECT * FROM players WHERE player_id = ?");
            $stmt->execute([$playerId]);
            return $stmt->fetch();
        } else {
            $stmt = $pdo->query("SELECT * FROM players");
            return $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        error_log("Error getting players: " . $e->getMessage());
        return false;
    }
}

// Function to get sessions for a player
function getPlayerSessions($playerId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM game_sessions WHERE player_id = ?");
        $stmt->execute([$playerId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting player sessions: " . $e->getMessage());
        return false;
    }
}

// Function to get achievements
function getAchievements() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM achievements");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting achievements: " . $e->getMessage());
        return false;
    }
}

// Function to get player achievements
function getPlayerAchievements($playerId) {
    global $pdo;
    
    try {
        $sql = "SELECT a.achievement_id, a.name, a.goal, pa.status, gs.session_id
                FROM achievements a
                JOIN player_achievements pa ON a.achievement_id = pa.achievement_id
                JOIN game_sessions gs ON pa.session_id = gs.session_id
                WHERE gs.player_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$playerId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting player achievements: " . $e->getMessage());
        return false;
    }
}

// Function to get average playtime per player
function getAveragePlaytimePerPlayer() {
    global $pdo;
    
    try {
        $sql = "SELECT p.player_id, p.name, 
                       AVG(TIMESTAMPDIFF(MINUTE, gs.start_time, gs.end_time)) as avg_playtime_minutes
                FROM players p
                JOIN game_sessions gs ON p.player_id = gs.player_id
                GROUP BY p.player_id, p.name
                ORDER BY avg_playtime_minutes DESC";
        
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting average playtime: " . $e->getMessage());
        return false;
    }
}

// Function to get total achievements per game session
function getTotalAchievementsPerSession() {
    global $pdo;
    
    try {
        $sql = "SELECT gs.session_id, p.name as player_name, 
                       COUNT(pa.achievement_id) as total_achievements
                FROM game_sessions gs
                JOIN players p ON gs.player_id = p.player_id
                LEFT JOIN player_achievements pa ON gs.session_id = pa.session_id
                GROUP BY gs.session_id, p.name
                ORDER BY total_achievements DESC";
        
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting total achievements per session: " . $e->getMessage());
        return false;
    }
}

// Function to get weekly playtime per player
function getWeeklyPlaytimePerPlayer() {
    global $pdo;
    
    try {
        $sql = "SELECT p.player_id, p.name, 
                       YEARWEEK(gs.start_time) as year_week, 
                       CONCAT(
                           YEAR(gs.start_time), '-W', 
                           LPAD(WEEK(gs.start_time), 2, '0')
                       ) as week_label,
                       SUM(TIMESTAMPDIFF(MINUTE, gs.start_time, gs.end_time)) as total_minutes
                FROM players p
                JOIN game_sessions gs ON p.player_id = gs.player_id
                GROUP BY p.player_id, p.name, year_week
                ORDER BY p.player_id, year_week";
        
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        
        // Organize results by player
        $players = [];
        foreach ($results as $row) {
            $playerId = $row['player_id'];
            
            if (!isset($players[$playerId])) {
                $players[$playerId] = [
                    'player_id' => $row['player_id'],
                    'name' => $row['name'],
                    'weekly_playtime' => []
                ];
            }
            
            $players[$playerId]['weekly_playtime'][] = [
                'week' => $row['week_label'],
                'total_minutes' => $row['total_minutes'],
                'total_hours' => round($row['total_minutes'] / 60, 2)
            ];
        }
        
        return array_values($players);
    } catch (PDOException $e) {
        error_log("Error getting weekly playtime: " . $e->getMessage());
        return false;
    }
}

// Function to get top players by criteria
function getTopPlayers($limit = 5, $criteria = 'total_gold_earned') {
    global $pdo;
    
    try {
        // Validate criteria to prevent SQL injection
        $validCriteria = ['total_gold_earned', 'in_game_days'];
        if (!in_array($criteria, $validCriteria)) {
            $criteria = 'total_gold_earned';
        }
        
        $sql = "SELECT p.player_id, p.name, p.farm_name, ps.$criteria as score
                FROM players p
                JOIN player_statistics ps ON p.player_id = ps.player_id
                ORDER BY ps.$criteria DESC
                LIMIT :limit";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT); // 明确指定为整数类型
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting top players: " . $e->getMessage());
        return false;
    }
}

// Function to update player information
function updatePlayer($playerId, $data) {
    global $pdo;
    
    try {
        $name = $data['name'] ?? null;
        $avatar = $data['avatar'] ?? null;
        $farm_name = $data['farm_name'] ?? null;
        
        $sql = "UPDATE players SET ";
        $params = [];
        $updateFields = [];
        
        if ($name !== null) {
            $updateFields[] = "name = ?";
            $params[] = $name;
        }
        
        if ($avatar !== null) {
            $updateFields[] = "avatar = ?";
            $params[] = $avatar;
        }
        
        if ($farm_name !== null) {
            $updateFields[] = "farm_name = ?";
            $params[] = $farm_name;
        }
        
        if (empty($updateFields)) {
            return false;
        }
        
        $sql .= implode(", ", $updateFields);
        $sql .= " WHERE player_id = ?";
        $params[] = $playerId;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Error updating player: " . $e->getMessage());
        return false;
    }
}

// Function to delete player and related records
function deletePlayer($playerId) {
    global $pdo;
    
    try {
        // Start transaction for cascade delete
        $pdo->beginTransaction();
        
        // Delete related records in player_achievements
        $stmt = $pdo->prepare("DELETE FROM player_achievements WHERE session_id IN (SELECT session_id FROM game_sessions WHERE player_id = ?)");
        $stmt->execute([$playerId]);
        
        // Delete related records in game_sessions
        $stmt = $pdo->prepare("DELETE FROM game_sessions WHERE player_id = ?");
        $stmt->execute([$playerId]);
        
        // Delete related records in inventory
        $stmt = $pdo->prepare("DELETE FROM inventory WHERE player_id = ?");
        $stmt->execute([$playerId]);
        
        // Delete related records in player_animals_owned
        $stmt = $pdo->prepare("DELETE FROM player_animals_owned WHERE player_id = ?");
        $stmt->execute([$playerId]);
        
        // Delete related records in player_crops_harvested
        $stmt = $pdo->prepare("DELETE FROM player_crops_harvested WHERE player_id = ?");
        $stmt->execute([$playerId]);
        
        // Delete related records in player_statistics
        $stmt = $pdo->prepare("DELETE FROM player_statistics WHERE player_id = ?");
        $stmt->execute([$playerId]);
        
        // Finally delete the player
        $stmt = $pdo->prepare("DELETE FROM players WHERE player_id = ?");
        $stmt->execute([$playerId]);
        
        // Commit transaction
        $pdo->commit();
        
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        error_log("Error deleting player: " . $e->getMessage());
        return false;
    }
}

// Function to get player statistics
function getPlayerStatistics($playerId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM player_statistics WHERE player_id = ?");
        $stmt->execute([$playerId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting player statistics: " . $e->getMessage());
        return false;
    }
}

function getPlayerCrops($playerId) {
    global $pdo;
    
    try {
        $sql = "SELECT c.crop_id, c.name, c.season, 
                       pch.harvested, pch.sold
                FROM crops c
                JOIN player_crops_harvested pch ON c.crop_id = pch.crop_id
                WHERE pch.player_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$playerId]);
        
        $crops = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $crops;
    } catch (PDOException $e) {
        error_log("Error getting player crops: " . $e->getMessage());
        return false;
    }
}

// Get player animals data
function getPlayerAnimals($playerId) {
    global $pdo;
    
    try {
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
        $stmt->execute([$playerId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting player animals: " . $e->getMessage());
        return false;
    }
}

function getPlayerInventory($playerId) {
    global $pdo;
    
    try {
        $sql = "SELECT i.item_id, i.name, i.type, i.value,
                       inv.quantity
                FROM items i
                JOIN inventory inv ON i.item_id = inv.item_id
                WHERE inv.player_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$playerId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting player inventory: " . $e->getMessage());
        return false;
    }
}?>
