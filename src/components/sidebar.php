<div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="#overview">
                    <i class="fas fa-tachometer-alt me-2"></i>Overview
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#player-stats">
                    <i class="fas fa-users me-2"></i>Player Statistics
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#farm-stats">
                    <i class="fas fa-seedling me-2"></i>Farm Statistics
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#achievements">
                    <i class="fas fa-trophy me-2"></i>Achievements
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#sessions">
                    <i class="fas fa-clock me-2"></i>Session Analytics
                </a>
            </li>
        </ul>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Data Filters</span>
        </h6>
        
        <div class="px-3 py-2">
            <div class="mb-3">
                <label for="player-filter" class="form-label">Player:</label>
                <select class="form-select form-select-sm" id="player-filter">
                    <option value="all" selected>All Players</option>
                    <?php
                    $players = getPlayers();
                    foreach ($players as $player) {
                        echo '<option value="' . $player['player_id'] . '">' . htmlspecialchars($player['name']) . '</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="date-range" class="form-label">Date Range:</label>
                <select class="form-select form-select-sm" id="date-range">
                    <option value="all" selected>All Time</option>
                    <option value="week">Last Week</option>
                    <option value="month">Last Month</option>
                    <option value="year">Last Year</option>
                </select>
            </div>
            
            <button type="button" class="btn btn-sm btn-primary w-100" id="apply-filters">
                Apply Filters
            </button>
        </div>
    </div>
</div>