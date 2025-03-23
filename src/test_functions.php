<!DOCTYPE html>
<html>
<head>
    <title>Function Tests</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        h3 {
            color: #0066cc;
            margin-top: 20px;
        }
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            overflow: auto;
        }
        hr {
            border: 0;
            border-top: 1px solid #eee;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h1>Testing PHP Functions</h1>

<?php
// Include the functions file
require_once 'functions.php';

// Set up error reporting for testing
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if PDO connection exists
global $pdo;
if (!isset($pdo)) {
    die("Database connection not established. Check your config.php file.");
}

// Function to print results in a readable format
function printResult($functionName, $result) {
    echo "<h3>Testing: $functionName</h3>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    echo "<hr>";
}

// Test functions below 

// Test getPlayers() - all players
printResult("getPlayers()", getPlayers());

// Test getPlayers() - specific player
printResult("getPlayers(4)", getPlayers(1));

// Test getPlayerSessions()
printResult("getPlayerSessions(4)", getPlayerSessions(4));

// Test getAchievements()
printResult("getAchievements()", getAchievements());

// Test getPlayerAchievements()
printResult("getPlayerAchievements(4)", getPlayerAchievements(4));

// Test getAveragePlaytimePerPlayer()
printResult("getAveragePlaytimePerPlayer()", getAveragePlaytimePerPlayer());

// Test getTotalAchievementsPerSession()
printResult("getTotalAchievementsPerSession()", getTotalAchievementsPerSession());

// Test getWeeklyPlaytimePerPlayer()
printResult("getWeeklyPlaytimePerPlayer()", getWeeklyPlaytimePerPlayer());

?>
</body>
</html> 