<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="col-md-3 col-lg-2 sidebar">
    <div class="position-sticky">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-home me-2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'players.php' ? 'active' : ''; ?>" href="players.php">
                    <i class="fas fa-users me-2"></i>
                    Players
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'achievements.php' ? 'active' : ''; ?>" href="achievements.php">
                    <i class="fas fa-trophy me-2"></i>
                    Achievements
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'weekly_playtime.php' ? 'active' : ''; ?>" href="weekly_playtime.php">
                    <i class="far fa-clock me-2"></i>
                    Weekly Playtime
                </a>
            </li>
        </ul>
    </div>
</div>