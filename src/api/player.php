<?php
require_once '../functions.php';

header('Content-Type: application/json');

// 检查是否提供了玩家ID
if (!isset($_GET['id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No player ID provided'
    ]);
    exit;
}

$playerId = (int)$_GET['id'];

// 获取玩家基本信息
$playerData = getPlayers($playerId);

if ($playerData) {
    // 获取玩家统计数据
    $playerStats = getPlayerStatistics($playerId);
    
    if ($playerStats) {
        $playerData = array_merge($playerData, $playerStats);
    }
    
    // 获取玩家成就数据 - 修改这部分以匹配数据库结构
    $playerAchievements = getPlayerAchievements($playerId);
    if ($playerAchievements) {
        // 计算成就总数
        $achievementsCount = count($playerAchievements);
        $playerData['achievements_count'] = $achievementsCount;
        
        // 添加成就详细信息到响应中
        $playerData['achievements'] = [];
        foreach ($playerAchievements as $achievement) {
            // 只使用数据库中实际存在的字段
            $achievementData = [
                'achievement_id' => $achievement['achievement_id'],
                'name' => $achievement['name'],
                'goal' => $achievement['goal'],
                'status' => $achievement['status'],
                // 基于status字段确定是否完成
                'completed' => ($achievement['status'] === 'completed')
            ];
            
            // 如果前端需要进度信息，我们可以基于status提供一个简单的值
            // 而不是随机生成
            if ($achievement['status'] === 'completed') {
                $achievementData['progress'] = 100;
            } else if ($achievement['status'] === 'not_started') {
                $achievementData['progress'] = 0;
            } else {
                // 对于"in_progress"状态，使用一个固定值而不是随机值
                $achievementData['progress'] = 50;
            }
            
            $playerData['achievements'][] = $achievementData;
        }
    } else {
        $playerData['achievements_count'] = 0;
        $playerData['achievements'] = [];
    }
    
    // 获取玩家平均游戏时间
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
    
    // 获取玩家最近的游戏会话
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
            // 格式化会话数据
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
    
    // 获取每周游戏时间数据
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
            // 将数据组织成按周分组的格式
            $weeklyPlaytime = [];
            foreach ($weeklyData as $day) {
                $weekNumber = $day['week_number'];
                $dayOfWeek = $day['day_of_week'];
                $minutes = $day['total_minutes'];
                
                // 确保每周的数据是一个数组
                if (!isset($weeklyPlaytime[$weekNumber])) {
                    $weeklyPlaytime[$weekNumber] = [];
                }
                
                // 将星期几转换为更友好的格式（Mon, Tue等）
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