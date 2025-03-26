# Stardew Valley Player Management System

A web application for managing and analyzing Stardew Valley player data. The system provides player statistics, gameplay time analysis, and data visualization features.

## Features

### Dashboard
- Key metrics display (total players, top farmer, gameplay time, etc.)
- Playtime distribution chart
- Session duration analysis
- Data export functionality (PDF format)

### Player Management
- Player list display
- Sorting by ID, name, gold earned, and in-game days
- View and edit player details

## Tech Stack

- PHP 7.4+
- MySQL 5.7+
- Bootstrap 5
- Chart.js (Data visualization)
- Font Awesome (Icons)

## Installation

1. Requirements
   - XAMPP/WAMP/MAMP or other server environment supporting PHP and MySQL
   - PHP 7.4 or higher
   - MySQL 5.7 or higher

2. Database Setup
   ```sql
   -- Create database
   CREATE DATABASE stardew_valley_management;
   
   -- Import database structure
   mysql -u your_username -p stardew_valley_management < sql/create_tables.sql
   
   -- Import sample data (optional)
   mysql -u your_username -p stardew_valley_management < sql/sample_data.sql
   ```

3. Configuration
   - Copy and paste this code into a new file named `config.php` in the `includes` directory:
   ```php
   <?php
   // Database configuration
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');     // Default XAMPP username
   define('DB_PASS', '');      //Enter your XAMPP password 
   define('DB_NAME', 'stardew_valley');

   // Establish database connection
   try {
       $pdo = new PDO(
           "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
           DB_USER,
           DB_PASS,
           array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
       );
   } catch (PDOException $e) {
       die("Connection failed: " . $e->getMessage());
   }
   ?>
   ```

## Directory Structure

```
src/
├── api/                    # API endpoints
│   ├── playtime_distribution.php
│   ├── session_analytics.php
│   └── top_players.php
├── includes/              # Core functionality files
│   ├── config.php
│   └── functions.php
├── js/                    # JavaScript files
│   ├── dashboard.js
│   └── players.js
├── components/           # Page components
├── dashboard.php         # Dashboard page
├── players.php          # Player management page
└── index.php            # Home page
```

## Usage Guide

1. Accessing the System
   - Open your browser and visit: `http://localhost/stardew-valley-player-management/src/`
   - Dashboard page is displayed by default

2. Dashboard Features
   - View key statistics
   - Analyze playtime distribution through charts
   - Click "Export" to generate PDF report
   - Click "Refresh" to update data

3. Player Management
   - View all players in the player list
   - Click column headers to sort
   - Click on player rows to view/edit details

## API Endpoints

### Get Playtime Distribution
```
GET /api/playtime_distribution.php
Returns: Player playtime distribution data
```

### Get Session Analytics
```
GET /api/session_analytics.php
Returns: Player session duration distribution
```

### Get Top Players
```
GET /api/top_players.php?criteria=total_gold_earned
Parameters:
- criteria: Ranking criteria (total_gold_earned or in_game_days)
Returns: List of players sorted by specified criteria
```

## Database Structure

Main Tables:
- `players`: Basic player information
- `game_sessions`: Game session records
- `player_statistics`: Player statistics data

## Development Guide

1. Adding New Features
   - Add new functions in `includes/functions.php`
   - Add new API endpoints in the `api/` directory
   - Implement frontend functionality in respective page files

2. Style Modifications
   - System uses Bootstrap 5 framework
   - Add custom styles in CSS files

## Important Notes

- Ensure PDO extension is enabled in PHP configuration
- Regular database backups are recommended
- Periodic cleanup of expired session data is advised

## Support

For issues or suggestions, please submit an Issue or contact the system administrator.
