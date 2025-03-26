<?php
require_once '../functions.php';

header('Content-Type: application/json');

// check if player ID is provided
if (!isset($_GET['id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No player ID provided'
    ]);
    exit;
}

$playerId = (int)$_GET['id'];

// get player basic information
$playerData = getPlayers($playerId);

if ($playerData) {
    // get player statistics
    $playerStats = getPlayerStatistics($playerId);
    
    if ($playerStats) {
        $playerData = array_merge($playerData, $playerStats);
    }
    
    // get player achievements data - modify this part to match database structure
    $playerAchievements = getPlayerAchievements($playerId);
    if ($playerAchievements) {
        // calculate achievements count
        $achievementsCount = count($playerAchievements);
        $playerData['achievements_count'] = $achievementsCount;
        
        // add achievements details to response
        $playerData['achievements'] = [];
        foreach ($playerAchievements as $achievement) {
            // only use fields that exist in the database
            $achievementData = [
                'achievement_id' => $achievement['achievement_id'],
                'name' => $achievement['name'],
                'goal' => $achievement['goal'],
                'status' => $achievement['status'],
                // determine if achievement is completed based on status
                'completed' => ($achievement['status'] === 'completed')
            ];
            
            $playerData['achievements'][] = $achievementData;
        }
    } else {
        $playerData['achievements_count'] = 0;
        $playerData['achievements'] = [];
    }
    
    // get player average playtime
    try {
        global $pdo;
        $sql = "SELECT AVG(TIMESTAMPDIFF(MINUTE, gs.start_time, gs.end_time)) as avg_playtime
                FROM game_sessions gs
                WHERE gs.player_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$playerId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && $result['avg_playtime'] !== null) {
            $playerData['average_playtime'] = round($result['avg_playtime']);
        } else {
            $playerData['average_playtime'] = 0;
        }
    } catch (PDOException $e) {
        error_log("Error getting average playtime: " . $e->getMessage());
        $playerData['average_playtime'] = 0;
    }
    
    // get player recent game sessions
    try {
        $sql = "SELECT 
                    gs.session_id,
                    gs.start_time,
                    gs.end_time,
                    TIMESTAMPDIFF(MINUTE, gs.start_time, gs.end_time) as duration,
                    (SELECT COUNT(*) FROM player_achievements pa WHERE pa.session_id = gs.session_id) as achievements_earned
                FROM 
                    game_sessions gs
                WHERE 
                    gs.player_id = ?
                ORDER BY 
                    gs.start_time DESC
                LIMIT 5";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$playerId]);
        $gameSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($gameSessions) {
            // format session data
            $formattedSessions = [];
            foreach ($gameSessions as $session) {
                $formattedSessions[] = [
                    'id' => $session['session_id'],
                    'date' => date('M d, Y', strtotime($session['start_time'])),
                    'duration' => $session['duration'],
                    'achievements' => $session['achievements_earned']
                ];
            }
            $playerData['gameSessions'] = $formattedSessions;
        } else {
            $playerData['gameSessions'] = [];
        }
    } catch (PDOException $e) {
        error_log("Error getting game sessions: " . $e->getMessage());
        $playerData['gameSessions'] = [];
    }
    
    // get weekly game time data
    try {
        $sql = "SELECT 
                    WEEK(start_time) as week_number,
                    DAYOFWEEK(start_time) as day_of_week,
                    SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as total_minutes
                FROM 
                    game_sessions
                WHERE 
                    player_id = ? AND
                    start_time >= DATE_SUB(NOW(), INTERVAL 4 WEEK)
                GROUP BY 
                    WEEK(start_time), DAYOFWEEK(start_time)
                ORDER BY 
                    week_number, day_of_week";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$playerId]);
        $weeklyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($weeklyData) {
            // organize data into weekly grouped format
            $weeklyPlaytime = [];
            foreach ($weeklyData as $day) {
                $weekNumber = $day['week_number'];
                $dayOfWeek = $day['day_of_week'];
                $minutes = $day['total_minutes'];
                
                // ensure weekly data is an array
                if (!isset($weeklyPlaytime[$weekNumber])) {
                    $weeklyPlaytime[$weekNumber] = [];
                }
                
                // convert day of week to more friendly format (Mon, Tue, etc.)
                $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                $dayName = $dayNames[$dayOfWeek - 1];
                
                $weeklyPlaytime[$weekNumber][] = [
                    'day' => $dayName,
                    'minutes' => (int)$minutes
                ];
            }
            
            $playerData['weeklyPlaytime'] = $weeklyPlaytime;
        } else {
            $playerData['weeklyPlaytime'] = [];
        }
    } catch (PDOException $e) {
        error_log("Error getting weekly playtime: " . $e->getMessage());
        $playerData['weeklyPlaytime'] = [];
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Player data retrieved successfully',
        'data' => $playerData
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Player not found'
    ]);
}