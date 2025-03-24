<?php
// Include your config file with database connection
require_once 'config.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Function to send JSON response
function sendResponse($code, $message, $data = null) {
    http_response_code($code);
    echo json_encode([
        'status' => $code < 400 ? 'success' : 'error',
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Handle different HTTP request methods
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGetRequest();
        break;
    case 'POST':
        handlePostRequest();
        break;
    default:
        sendResponse(405, "Method not allowed");
}

// Handle GET requests (login)
function handleGetRequest() {
    global $conn;
    
    // Get player by ID
    if (isset($_GET['id'])) {
        $playerId = (int)$_GET['id'];
        $query = "SELECT p.*, ps.total_gold_earned, ps.in_game_days 
                 FROM players p 
                 LEFT JOIN player_statistics ps ON p.player_id = ps.player_id 
                 WHERE p.player_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $playerId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $player = $result->fetch_assoc();
            sendResponse(200, "Player retrieved successfully", $player);
        } else {
            sendResponse(404, "Player not found");
        }
    }
    
    // Get player by name
    if (isset($_GET['name'])) {
        $playerName = $_GET['name'];
        $query = "SELECT p.*, ps.total_gold_earned, ps.in_game_days 
                 FROM players p 
                 LEFT JOIN player_statistics ps ON p.player_id = ps.player_id 
                 WHERE p.name = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $playerName);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $player = $result->fetch_assoc();
            sendResponse(200, "Player retrieved successfully", $player);
        } else {
            sendResponse(404, "Player not found");
        }
    }
    
    // Get all players if no specific player requested
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

// Handle POST requests (register)
function handlePostRequest() {
    global $conn;
    
    // Validate required fields
    if (!isset($_POST['name']) || !isset($_POST['farm_name']) || 
        trim($_POST['name']) === '' || trim($_POST['farm_name']) === '') {
        sendResponse(400, "Missing required fields: name and farm_name are required");
    }
    
    // Sanitize input
    $name = $conn->real_escape_string($_POST['name']);
    $avatar = isset($_POST['avatar']) ? $conn->real_escape_string($_POST['avatar']) : 'default';
    $farmName = $conn->real_escape_string($_POST['farm_name']);
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Insert player data
        $query = "INSERT INTO players (name, avatar, farm_name) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $name, $avatar, $farmName);
        
        if ($stmt->execute()) {
            $playerId = $conn->insert_id;
            
            // Initialize player statistics data
            $totalGold = isset($_POST['total_gold_earned']) ? (int)$_POST['total_gold_earned'] : 0;
            $inGameDays = isset($_POST['in_game_days']) ? (int)$_POST['in_game_days'] : 0;
            
            $statsQuery = "INSERT INTO player_statistics (player_id, total_gold_earned, in_game_days) 
                          VALUES (?, ?, ?)";
            $statsStmt = $conn->prepare($statsQuery);
            $statsStmt->bind_param("iii", $playerId, $totalGold, $inGameDays);
            
            if ($statsStmt->execute()) {
                $conn->commit();
                
                // Get newly created player data
                $newPlayerQuery = "SELECT p.*, ps.total_gold_earned, ps.in_game_days 
                                  FROM players p 
                                  LEFT JOIN player_statistics ps ON p.player_id = ps.player_id 
                                  WHERE p.player_id = ?";
                
                $newPlayerStmt = $conn->prepare($newPlayerQuery);
                $newPlayerStmt->bind_param("i", $playerId);
                $newPlayerStmt->execute();
                $result = $newPlayerStmt->get_result();
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
?>