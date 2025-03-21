<?php
require_once 'utils.php';

// GET request
if (validateMethod('GET')) {
    $data = getRequestData();
    
    // build query
    $query = "
        SELECT 
            pa.achievement_id,
            a.name AS achievement_name,
            a.goal,
            pa.status,
            p.player_id,
            p.name AS player_name,
            gs.session_id
        FROM 
            player_achievements pa
        JOIN 
            achievements a ON pa.achievement_id = a.achievement_id
        JOIN 
            game_sessions gs ON pa.session_id = gs.session_id
        JOIN 
            players p ON gs.player_id = p.player_id
    ";
    
    $params = [];
    $types = "";
    
    // add filter conditions
    $whereConditions = [];
    
    if (isset($data['player_id']) && !empty($data['player_id'])) {
        $whereConditions[] = "p.player_id = ?";
        $params[] = $data['player_id'];
        $types .= "i";
    }
    
    if (isset($data['status']) && !empty($data['status'])) {
        $whereConditions[] = "pa.status = ?";
        $params[] = $data['status'];
        $types .= "s";
    }
    
    if (!empty($whereConditions)) {
        $query .= " WHERE " . implode(" AND ", $whereConditions);
    }
    
    $query .= " ORDER BY p.name, a.name";
    
    // prepare and execute query
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        $achievements = [];
        while ($row = $result->fetch_assoc()) {
            $achievements[] = $row;
        }
        sendResponse(200, "Achievements retrieved successfully", $achievements);
    } else {
        sendResponse(500, "Error retrieving achievements: " . $conn->error);
    }
}

// POST request
else if (validateMethod('POST')) {
    $data = getRequestData();
    
    // validate required fields
    if (!validateRequiredFields($data, ['player_id', 'achievement_id', 'status'])) {
        sendResponse(400, "Missing required fields: player_id, achievement_id, status");
    }
    
    $playerId = $data['player_id'];
    $achievementId = $data['achievement_id'];
    $status = $data['status'];
    $sessionId = isset($data['session_id']) ? $data['session_id'] : null;
    
    // validate status value
    $validStatuses = ['Not Started', 'In Progress', 'Completed'];
    if (!in_array($status, $validStatuses)) {
        sendResponse(400, "Invalid status. Must be one of: " . implode(", ", $validStatuses));
    }
    
    // if no session id provided, get the latest session for the player
    if (!$sessionId) {
        $stmt = $conn->prepare("
            SELECT session_id 
            FROM game_sessions 
            WHERE player_id = ? 
            ORDER BY start_time DESC 
            LIMIT 1
        ");
        $stmt->bind_param("i", $playerId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $sessionId = $row['session_id'];
        } else {
            sendResponse(404, "No game sessions found for this player");
        }
    }
    
    // check if the achievement record exists
    $stmt = $conn->prepare("
        SELECT * 
        FROM player_achievements 
        WHERE achievement_id = ? AND session_id = ?
    ");
    $stmt->bind_param("ii", $achievementId, $sessionId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // update existing record
        $stmt = $conn->prepare("
            UPDATE player_achievements 
            SET status = ? 
            WHERE achievement_id = ? AND session_id = ?
        ");
        $stmt->bind_param("sii", $status, $achievementId, $sessionId);
        
        if ($stmt->execute()) {
            sendResponse(200, "Achievement status updated successfully");
        } else {
            sendResponse(500, "Error updating achievement status: " . $conn->error);
        }
    } else {
        // create new record
        $stmt = $conn->prepare("
            INSERT INTO player_achievements (achievement_id, session_id, status) 
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iis", $achievementId, $sessionId, $status);
        
        if ($stmt->execute()) {
            sendResponse(201, "Achievement status created successfully");
        } else {
            sendResponse(500, "Error creating achievement status: " . $conn->error);
        }
    }
}

// handle unsupported request methods
else {
    sendResponse(405, "Method not allowed");
}
?>
