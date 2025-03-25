<?php
require_once 'config.php';

/**
 * 获取所有玩家或特定玩家的信息
 * 
 * @param int|null $playerId 玩家ID（可选）
 * @return array 玩家信息数组
 */
function getPlayers($playerId = null) {
    global $pdo;
    
    if ($playerId !== null) {
        $stmt = $pdo->prepare("
            SELECT p.*, ps.total_gold_earned as gold_earned, ps.in_game_days
            FROM players p
            LEFT JOIN player_statistics ps ON p.player_id = ps.player_id
            WHERE p.player_id = ?
        ");
        $stmt->execute([$playerId]);
        return $stmt->fetch();
    } else {
        $stmt = $pdo->query("
            SELECT p.*, ps.total_gold_earned as gold_earned, ps.in_game_days
            FROM players p
            LEFT JOIN player_statistics ps ON p.player_id = ps.player_id
            ORDER BY p.player_id ASC
        ");
        return $stmt->fetchAll();
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
function getAchievements($playerId = null) {
    global $pdo;
    
    try {
        if ($playerId) {
            $stmt = $pdo->prepare("
                SELECT 
                    a.*,
                    COALESCE(pa.status, 'Not Started') as status,
                    COALESCE(pa.progress, 0) as progress
                FROM achievements a
                LEFT JOIN player_achievements pa ON a.achievement_id = pa.achievement_id 
                    AND pa.player_id = :player_id
                ORDER BY a.category, a.name
            ");
            $stmt->bindValue(':player_id', $playerId, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $pdo->query("
                SELECT 
                    a.*,
                    'Not Started' as status,
                    0 as progress,
                    (
                        SELECT COUNT(*)
                        FROM player_achievements pa
                        WHERE pa.achievement_id = a.achievement_id
                        AND pa.status = 'Completed'
                    ) as completion_count
                FROM achievements a
                ORDER BY a.category, a.name
            ");
        }
        
        $achievements = $stmt->fetchAll();
        
        // Ensure all required fields have default values
        foreach ($achievements as &$achievement) {
            $achievement['status'] = $achievement['status'] ?? 'Not Started';
            $achievement['progress'] = $achievement['progress'] ?? 0;
            $achievement['description'] = $achievement['description'] ?? $achievement['goal'];
            $achievement['reward'] = $achievement['reward'] ?? 0;
            $achievement['category'] = $achievement['category'] ?? 'General';
        }
        
        return $achievements;
    } catch (PDOException $e) {
        error_log("Error getting achievements: " . $e->getMessage());
        return [];
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

//function get top achievement players
function getTopAchievementPlayers($limit = 5) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT p.name AS player_name, COUNT(*) AS completed_achievements
        FROM player_achievements pa
        JOIN game_sessions gs ON pa.session_id = gs.session_id
        JOIN players p ON gs.player_id = p.player_id
        WHERE pa.status = 'completed'
        GROUP BY gs.player_id
        ORDER BY completed_achievements DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
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

/**
 * 获取玩家游戏时间统计
 * 
 * @return array 游戏时间统计数据
 */
function getPlayerPlaytimeStats() {
    global $pdo;
    
    $stats = [
        'total_sessions' => 0,
        'total_playtime' => 0,
        'avg_session_length' => 0,
        'most_active_player' => null,
        'recent_sessions' => []
    ];
    
    // 获取总会话数
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM game_sessions");
    $stats['total_sessions'] = $stmt->fetch()['count'];
    
    // 计算总游戏时间和平均会话长度
    $stmt = $pdo->query("
        SELECT 
            SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as total_minutes,
            AVG(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as avg_minutes
        FROM game_sessions
    ");
    $timeStats = $stmt->fetch();
    $stats['total_playtime'] = $timeStats['total_minutes'] ?? 0;
    $stats['avg_session_length'] = round($timeStats['avg_minutes'] ?? 0);
    
    // 获取最活跃的玩家
    $stmt = $pdo->query("
        SELECT 
            p.player_id,
            p.name,
            p.farm_name,
            COUNT(gs.session_id) as session_count,
            SUM(TIMESTAMPDIFF(MINUTE, gs.start_time, gs.end_time)) as total_playtime
        FROM players p
        JOIN game_sessions gs ON p.player_id = gs.player_id
        GROUP BY p.player_id
        ORDER BY total_playtime DESC
        LIMIT 1
    ");
    $stats['most_active_player'] = $stmt->fetch();
    
    // 获取最近的游戏会话
    $stmt = $pdo->query("
        SELECT 
            gs.*,
            p.name as player_name,
            p.farm_name
        FROM game_sessions gs
        JOIN players p ON gs.player_id = p.player_id
        ORDER BY gs.start_time DESC
        LIMIT 5
    ");
    $stats['recent_sessions'] = $stmt->fetchAll();
    
    return $stats;
}

/**
 * Get achievement statistics
 * @return array Achievement statistics including completion rate and top achievers
 */
function getAchievementStats() {
    global $pdo;
    
    $stats = [
        'total_achievements' => 0,
        'total_completed' => 0,
        'completion_rate' => 0,
        'top_achievers' => [],
        'recent_achievements' => []
    ];
    
    try {
        // Get total number of achievements
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM achievements");
        $stats['total_achievements'] = $stmt->fetch()['total'];
        
        // Get completed achievements count
        $stmt = $pdo->query("SELECT COUNT(*) as completed FROM player_achievements WHERE status = 'Completed'");
        $stats['total_completed'] = $stmt->fetch()['completed'];
        
        // Calculate completion rate
        $stats['completion_rate'] = $stats['total_achievements'] > 0 
            ? round(($stats['total_completed'] / $stats['total_achievements']) * 100) 
            : 0;
        
        // Get top achievers
        $stmt = $pdo->query("
            SELECT p.name, p.farm_name, COUNT(pa.achievement_id) as achievement_count
            FROM players p
            LEFT JOIN player_achievements pa ON p.player_id = pa.player_id
            WHERE pa.status = 'Completed'
            GROUP BY p.player_id, p.name, p.farm_name
            ORDER BY achievement_count DESC
            LIMIT 5
        ");
        $stats['top_achievers'] = $stmt->fetchAll();
        
        // Get recent achievements
        $stmt = $pdo->query("
            SELECT 
                a.name as achievement_name,
                p.name as player_name,
                pa.completion_date
            FROM player_achievements pa
            JOIN achievements a ON pa.achievement_id = a.achievement_id
            JOIN players p ON pa.player_id = p.player_id
            WHERE pa.status = 'Completed'
            ORDER BY pa.completion_date DESC
            LIMIT 5
        ");
        $stats['recent_achievements'] = $stmt->fetchAll();
        
        return $stats;
    } catch (PDOException $e) {
        error_log("Error getting achievement stats: " . $e->getMessage());
        return $stats; // Return default values if there's an error
    }
}

/**
 * Get farm statistics
 * @return array Farm statistics including total gold and richest players
 */
function getFarmStats() {
    global $pdo;
    
    try {
        // Get total gold earned across all players
        $stmt = $pdo->query("SELECT SUM(total_gold_earned) as total FROM player_statistics");
        $totalGold = $stmt->fetch()['total'];
        
        // Get richest players
        $stmt = $pdo->query("
            SELECT p.name, p.farm_name, ps.total_gold_earned
            FROM players p
            JOIN player_statistics ps ON p.player_id = ps.player_id
            ORDER BY ps.total_gold_earned DESC
            LIMIT 5
        ");
        $richestPlayers = $stmt->fetchAll();
        
        // Get most experienced players
        $stmt = $pdo->query("
            SELECT p.name, p.farm_name, ps.in_game_days
            FROM players p
            JOIN player_statistics ps ON p.player_id = ps.player_id
            ORDER BY ps.in_game_days DESC
            LIMIT 5
        ");
        $mostExperiencedPlayers = $stmt->fetchAll();
        
        // Get crop diversity
        $stmt = $pdo->query("
            SELECT COUNT(DISTINCT crop_id) as crop_diversity
            FROM player_crops_harvested
        ");
        $cropDiversity = $stmt->fetch()['crop_diversity'];
        
        // Get animal happiness
        $stmt = $pdo->query("
            SELECT AVG(happiness) as avg_happiness
            FROM farm_animals
            WHERE happiness IS NOT NULL
        ");
        $animalHappiness = round($stmt->fetch()['avg_happiness'] ?? 0);
        
        // Get building count
        $stmt = $pdo->query("
            SELECT COUNT(*) as building_count
            FROM farm_buildings
        ");
        $buildingCount = $stmt->fetch()['building_count'];
        
        return [
            'total_gold' => $totalGold,
            'richest_players' => $richestPlayers,
            'most_experienced_players' => $mostExperiencedPlayers,
            'crop_diversity' => $cropDiversity,
            'animal_happiness' => $animalHappiness,
            'building_count' => $buildingCount
        ];
    } catch (PDOException $e) {
        error_log("Error getting farm stats: " . $e->getMessage());
        return [
            'total_gold' => 0,
            'richest_players' => [],
            'most_experienced_players' => [],
            'crop_diversity' => 0,
            'animal_happiness' => 0,
            'building_count' => 0
        ];
    }
}

/**
 * Get crops statistics by season
 * @return array Crops statistics grouped by season
 */
function getCropsBySeason() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("
            SELECT 
                c.season,
                COUNT(DISTINCT pch.crop_id) as unique_crops,
                SUM(pch.quantity) as total_harvested,
                SUM(pch.quantity * c.sell_price) as total_value
            FROM player_crops_harvested pch
            JOIN crops c ON pch.crop_id = c.crop_id
            GROUP BY c.season
            ORDER BY FIELD(c.season, 'Spring', 'Summer', 'Fall', 'Winter')
        ");
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting crops by season: " . $e->getMessage());
        return [];
    }
}

/**
 * Get most valuable items
 * @param int $limit Number of items to return
 * @return array Most valuable items with their statistics
 */
function getMostValuableItems($limit = 5) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                i.item_id,
                i.name as item_name,
                i.type as item_type,
                i.base_price as unit_price,
                SUM(inv.quantity) as quantity,
                SUM(inv.quantity * i.base_price) as total_value,
                p.name as player_name
            FROM inventory inv
            JOIN items i ON inv.item_id = i.item_id
            JOIN players p ON inv.player_id = p.player_id
            GROUP BY i.item_id, p.player_id
            ORDER BY total_value DESC
            LIMIT :limit
        ");
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting most valuable items: " . $e->getMessage());
        return [];
    }
}

/**
 * Get the achievement master (player with most completed achievements)
 * @return array|null Achievement master details or null if no data
 */
function getAchievementMaster() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("
            SELECT 
                p.name,
                p.farm_name,
                COUNT(*) as achievement_count
            FROM players p
            JOIN player_achievements pa ON p.player_id = pa.player_id
            WHERE pa.status = 'Completed'
            GROUP BY p.player_id, p.name, p.farm_name
            ORDER BY achievement_count DESC
            LIMIT 1
        ");
        
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting achievement master: " . $e->getMessage());
        return null;
    }
}

/**
 * Get CSS class for achievement status badge
 * @param string $status Achievement status
 * @return string CSS class name
 */
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'Completed':
            return 'success';
        case 'In Progress':
            return 'warning';
        default:
            return 'secondary';
    }
}
?>