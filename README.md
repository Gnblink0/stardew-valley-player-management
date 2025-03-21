# Stardew Valley Player Management

## Project Overview
This project is a CS 5200 Practicum database and web development practice which allows users to:
- Manage player information (update/delete players)
- Track player achievements (unlock progress, leaderboards)
- Analyze game data (playtime, gold statistics, farm performance, etc.)
- Interactive data visualization (dashboard)


## Tech Stack
- Frontend: HTML, CSS, JavaScript (optional Bootstrap / Chart. js)
- Backend: PHP
- Database: MySQL
- Server: XAMPP (Apache + MySQL)


## Installation & Setup

### 1. Install XAMPP
- Download XAMPP
- After installation, open XAMPP and start:
  - Apache (Web Server)
  - MySQL (Database)

### 2. Run the Project Code
1. Clone the GitHub repository
   ```bash
   git clone https://github.com/your-team/stardew-valley-player-management.git
   ```
2. Move the project to the XAMPP directory (if not already in `htdocs/`)

Find the stardew-valley-player-management folder on your computer and move it into the htdocs directory inside the XAMPP installation (/Applications/XAMPP/htdocs/ on macOS). This allows Apache to detect and serve the project files properly.

3. Access in browser

To verify the setup, open a browser and visit http://localhost/stardew-valley-player-management/src/index.php. If you see "Hello, World!" displayed, the connection is successful.

**Note:** XAMPP will only automatically run PHP files. You must manually import SQL files and connect to the database as described in the Database Setup section below.


## Database Setup

### 1. Create the Database in phpMyAdmin
1. Open browser and visit http://localhost/phpmyadmin
2. Click "New" to create a database, name it `stardew_valley`
3. Choose utf8_general_ci as the character set

### 2. Run SQL Scripts
In `phpMyAdmin`:
- Import `sql/create_tables.sql` (creates table structures)
- Import `sql/insert_data.sql` (populates test data)

### 3. Test Database Connection

In the `src` folder, create a file named `config.php` with the following code:

```php
<?php
$servername = "localhost";
$username = "root";
$password = ""; // Replace with your password if you set one in a previous class activity
$dbname = "stardew_valley";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Successfully connected to the database!";
}

$conn->close();
?>
```

To test your database connection:
1. Save the `config.php` file
2. Open your browser and navigate to `http://localhost/stardew-valley-player-management/src/config.php`
3. If successful, you should see the message "Successfully connected to the database!"