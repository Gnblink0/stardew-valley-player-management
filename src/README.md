This document provides the guide of how to use `src/functions.php` which are PHP functions available for accessing and manipulating player data in the Stardew Valley Game Analytics system.


## Setup

### Connection Check

After finish the connection with web and database, go to http://localhost/stardew-valley-player-management/src/test_functions.php

If you can see all test results, it means you can use these functions.


###

Make sure to include the function file in any script where these functions are used:

```php
require_once 'functions.php';    // Functions defined in this file

```


## Player Functions


### getPlayers($playerId = null)

Retrieves player information from the database.

**Parameters:**
- `$playerId` (optional): If provided, returns data for a specific player. If omitted, returns all players.

**Returns:**
- When `$playerId` is provided: An associative array with player data
- When `$playerId` is omitted: An array of all players
- `false` on error

**Example:**
```php
// Get all players
$allPlayers = getPlayers();

// Get a specific player
$player = getPlayers(1);
```

### updatePlayer($playerId, $data)

Updates player information.

**Parameters:**
- `$playerId`: The ID of the player to update
- `$data`: An associative array containing the fields to update:
  - `name` (optional): Player's name
  - `avatar` (optional): Player's avatar
  - `farm_name` (optional): Player's farm name

**Returns:**
- `true` if update was successful
- `false` on error or if no fields were updated

**Example:**
```php
$result = updatePlayer(1, [
    'name' => 'FarmerJohn',
    'farm_name' => 'Sunshine Farm'
]);
```

### deletePlayer($playerId)

Deletes a player and all related records (cascading delete).

**Parameters:**
- `$playerId`: The ID of the player to delete

**Returns:**
- `true` if deletion was successful
- `false` on error

**Example:**
```php
$result = deletePlayer(1);
```

## Session Functions

### getPlayerSessions($playerId)

Retrieves all game sessions for a specific player.

**Parameters:**
- `$playerId`: The ID of the player

**Returns:**
- An array of game sessions
- `false` on error

**Example:**
```php
$sessions = getPlayerSessions(1);
```

## Achievement Functions

### getAchievements()

Retrieves all achievements from the database.

**Returns:**
- An array of all achievements
- `false` on error

**Example:**
```php
$achievements = getAchievements();
```

### getPlayerAchievements($playerId)

Retrieves all achievements for a specific player.

**Parameters:**
- `$playerId`: The ID of the player

**Returns:**
- An array of player achievements with details
- `false` on error

**Example:**
```php
$playerAchievements = getPlayerAchievements(1);
```

### getTotalAchievementsPerSession()

Retrieves the count of achievements earned in each game session.

**Returns:**
- An array with session details and achievement counts
- `false` on error

**Example:**
```php
$achievementsPerSession = getTotalAchievementsPerSession();
```

## Statistics Functions

### getAveragePlaytimePerPlayer()

Calculates the average playtime for each player.

**Returns:**
- An array with player details and average playtime in minutes
- `false` on error

**Example:**
```php
$avgPlaytime = getAveragePlaytimePerPlayer();
```

### getWeeklyPlaytimePerPlayer()

Retrieves weekly playtime statistics for all players.

**Returns:**
- An array organized by player, containing weekly playtime data
- `false` on error

**Example:**
```php
$weeklyPlaytime = getWeeklyPlaytimePerPlayer();
```

### getTopPlayers($limit = 5, $criteria = 'total_gold_earned')

Retrieves top players based on specified criteria.

**Parameters:**
- `$limit` (optional): Number of players to return (default: 5)
- `$criteria` (optional): Sorting criteria (default: 'total_gold_earned')
  - Valid options: 'total_gold_earned', 'in_game_days'

**Returns:**
- An array of top players based on the criteria
- `false` on error

**Example:**
```php
// Get top 10 players by gold earned
$topGoldEarners = getTopPlayers(10, 'total_gold_earned');

// Get top 5 players by in-game days played
$topDaysPlayed = getTopPlayers(5, 'in_game_days');
```
