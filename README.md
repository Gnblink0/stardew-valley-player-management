# Stardew Valley Player Management System

A web application for managing and analyzing Stardew Valley player data. The system provides player statistics, gameplay time analysis, and data visualization features.


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



## Usage Guide

Open your browser and visit: `http://localhost/stardew-valley-player-management/src/`


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
