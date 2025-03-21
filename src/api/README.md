# Stardew Valley Player Management API

This document outlines the API endpoints available for the Stardew Valley Player Management system.

## API Endpoints

| Endpoint | Method | Description | Parameters | Response |
|----------|--------|-------------|------------|----------|
| `/api/players.php` | GET | Get all players or a specific player | `id` (optional) - Player ID | List of players or a specific player |
| `/api/players.php` | POST | Create a new player | `name`, `farm_name`, `avatar` (optional) | Newly created player |
| `/api/players.php` | PUT | Update a player | `id` - Player ID, `name`, `farm_name`, `avatar` | Updated player |
| `/api/players.php` | DELETE | Delete a player | `id` - Player ID | Success message |
| `/api/statistics.php` | GET | Get player statistics | `player_id` (optional), `sort_by` (optional), `limit` (optional) | Player statistics |
| `/api/achievements.php` | GET | Get player achievements | `player_id` (optional), `status` (optional) | Player achievements |
| `/api/achievements.php` | POST | Update achievement status | `player_id`, `achievement_id`, `status` | Updated achievement |
| `/api/dashboard/top_players.php` | GET | Get top players by score | `limit` (optional) - Default: 5 | Top players |
| `/api/dashboard/playtime.php` | GET | Get average playtime per player | `group_by` (optional) - Group by day/week/month | Playtime statistics |
| `/api/dashboard/achievements_summary.php` | GET | Get achievements summary | None | Achievements summary |

## Query Types Implemented

| Query Type | API Endpoint | Description |
|------------|--------------|-------------|
| Join Query | `/api/achievements.php` | Display top players and their unlocked achievements |
| Aggregation Query | `/api/dashboard/playtime.php` | Compute average playtime per player and total achievements |
| Nested Aggregation with Group-By | `/api/dashboard/playtime.php?group_by=week` | Find total playtime per week grouped by player |
| Filtering & Ranking Query | `/api/dashboard/top_players.php` | Display the top 5 players with the highest scores |
| Update Operation | `/api/players.php` (PUT) | Modify player profile details |
| Delete Operation (Cascade) | `/api/players.php` (DELETE) | Delete a player and related data |

## Usage Examples

### Get all players

```
GET /api/players.php
```

### Get a specific player
```
GET /api/players.php?id=1
```

### Create a new player
```
POST /api/players.php
Body: name=NewPlayer&farm_name=Test Farm&avatar=male_1
```

### Update a player
```
PUT /api/players.php?id=1
Body: name=UpdatedName&farm_name=Updated Farm
```

### Delete a player
```
DELETE /api/players.php?id=1
```

### Get top 5 players
```
GET /api/dashboard/top_players.php
```

### Get playtime statistics grouped by week
```
GET /api/dashboard/playtime.php?group_by=week
```
