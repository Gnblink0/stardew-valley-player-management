<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get data for dashboard
$topPlayers = getTopPlayers(5);
$playerPlaytime = getPlayerPlaytimeStats();
$topAchievements = getTopAchievementPlayers(5);
$cropsBySeason = getCropsBySeason();
$mostValuableItems = getMostValuableItems(5);
?>

<?php include 'components/header.php'; ?>

<!-- 添加排名区域的 CSS 样式 -->
<style>
    .top-players-list {
        margin-top: 10px;
    }
    
    .rank-item {
        display: flex;
        align-items: center;
        padding: 12px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        transition: background-color 0.3s;
    }
    
    .rank-item:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }
    
    .rank-item:last-child {
        border-bottom: none;
    }
    
    .rank-position {
        font-weight: bold;
        font-size: 18px;
        color: #6c757d;
        width: 40px;
        text-align: center;
    }
    
    .rank-position.top-1 {
        color: #ffc107; /* Gold */
    }
    
    .rank-position.top-2 {
        color: #adb5bd; /* Silver */
    }
    
    .rank-position.top-3 {
        color: #cd7f32; /* Bronze */
    }
    
    .rank-info {
        flex-grow: 1;
        padding: 0 15px;
    }
    
    .rank-info h4 {
        margin: 0;
        color: #212529;
        font-size: 16px;
    }
    
    .rank-info p {
        margin: 2px 0 0;
        color: #6c757d;
        font-size: 14px;
    }
    
    .rank-score {
        font-weight: bold;
        color: #28a745;
        font-size: 16px;
        text-align: right;
        min-width: 100px;
    }
    
    .rank-actions {
        margin-left: 15px;
    }
</style>

<div class="row">
    <!-- Sidebar -->
    <?php include 'components/sidebar.php'; ?>
    
    <!-- Main content -->
    <div class="col-md-9 col-lg-10 dashboard-container">
        <!-- Page title and filter controls -->
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Stardew Valley Analytics Dashboard</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group me-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="refresh-data">
                        <i class="fas fa-sync-alt me-1"></i> Refresh
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="export-data">
                        <i class="fas fa-download me-1"></i> Export
                    </button>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="timeRangeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-calendar me-1"></i> All Time
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="timeRangeDropdown">
                        <li><a class="dropdown-item active" href="#">All Time</a></li>
                        <li><a class="dropdown-item" href="#">Last 7 Days</a></li>
                        <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                        <li><a class="dropdown-item" href="#">Last Year</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Key metrics -->
        <section id="overview" class="mb-4">
            <div class="row mb-3">
                <div class="col-12">
                    <h2 class="h4">Key Metrics Overview</h2>
                </div>
            </div>
            
            <div class="row">
                <!-- Total Players -->
                <div class="col-md-3 mb-4">
                    <div class="metric-card">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="text-secondary mb-1">Total Players</div>
                                <div class="metric-value"><?php echo count(getPlayers()); ?></div>
                                <div class="text-success">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <div class="ms-3 text-primary">
                                <i class="fas fa-user-friends fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Total Gold Earned -->
                <div class="col-md-3 mb-4">
                    <div class="metric-card">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="text-secondary mb-1">Total Gold Earned</div>
                                <?php
                                $stmt = $pdo->query("SELECT SUM(total_gold_earned) as total FROM player_statistics");
                                $totalGold = $stmt->fetch()['total'];
                                ?>
                                <div class="metric-value"><?php echo formatGold($totalGold); ?></div>
                                <div class="text-warning">
                                    <i class="fas fa-coins"></i>
                                </div>
                            </div>
                            <div class="ms-3 text-warning">
                                <i class="fas fa-coins fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Total Playtime -->
                <div class="col-md-3 mb-4">
                    <div class="metric-card">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="text-secondary mb-1">Total Playtime</div>
                                <?php
                                $stmt = $pdo->query("SELECT SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as total_minutes FROM game_sessions");
                                $totalMinutes = $stmt->fetch()['total_minutes'];
                                $totalHours = round($totalMinutes / 60, 1);
                                ?>
                                <div class="metric-value"><?php echo $totalHours; ?> hrs</div>
                                <div class="text-info">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <div class="ms-3 text-info">
                                <i class="fas fa-clock fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Achievements Completed -->
                <div class="col-md-3 mb-4">
                    <div class="metric-card">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="text-secondary mb-1">Achievements Completed</div>
                                <?php
                                $stmt = $pdo->query("SELECT COUNT(*) as count FROM player_achievements WHERE status = 'Completed'");
                                $completedCount = $stmt->fetch()['count'];
                                ?>
                                <div class="metric-value"><?php echo $completedCount; ?></div>
                                <div class="text-success">
                                    <i class="fas fa-trophy"></i>
                                </div>
                            </div>
                            <div class="ms-3 text-success">
                                <i class="fas fa-trophy fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Player statistics -->
        <section id="player-stats" class="mb-5">
            <div class="row mb-3">
                <div class="col-12">
                    <h2 class="h4">Player Statistics</h2>
                </div>
            </div>
            
            <div class="row">
                <!-- Top Players Ranking -->
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>Top Players Ranking</h5>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary active" data-criteria="total_gold_earned">By Gold</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-criteria="in_game_days">By Days Played</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="top-players-list" id="top-players-list">
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p>Loading top players...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Playtime Distribution Chart -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Playtime Distribution</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="playtimeChart" width="100%" height="300"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Top Players Table -->
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>Top Players Leaderboard</h5>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary active" id="sort-by-gold">By Gold</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="sort-by-days">By Days Played</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Player</th>
                                            <th>Farm Name</th>
                                            <th>Gold Earned</th>
                                            <th>In-Game Days</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $stmt = $pdo->query("
                                            SELECT p.player_id, p.name, p.farm_name, ps.total_gold_earned, ps.in_game_days
                                            FROM players p
                                            JOIN player_statistics ps ON p.player_id = ps.player_id
                                            ORDER BY ps.total_gold_earned DESC
                                            LIMIT 10
                                        ");
                                        $players = $stmt->fetchAll();
                                        
                                        foreach ($players as $index => $player):
                                        ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($player['name']); ?></td>
                                            <td><?php echo htmlspecialchars($player['farm_name']); ?></td>
                                            <td><?php echo formatGold($player['total_gold_earned']); ?></td>
                                            <td><?php echo $player['in_game_days']; ?></td>
                                            <td>
                                                <a href="players.php?id=<?php echo $player['player_id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Farm statistics -->
        <section id="farm-stats" class="mb-5">
            <div class="row mb-3">
                <div class="col-12">
                    <h2 class="h4">Farm Statistics</h2>
                </div>
            </div>
            
            <div class="row">
                <!-- Crops by Season -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Crops by Season</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="seasonalCropsChart" width="100%" height="300"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Animal Distribution -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Animal Distribution</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="animalDistributionChart" width="100%" height="300"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Most Valuable Items -->
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Most Valuable Items</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Player</th>
                                            <th>Item</th>
                                            <th>Type</th>
                                            <th>Unit Price</th>
                                            <th>Quantity</th>
                                            <th>Total Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($mostValuableItems as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['player_name']); ?></td>
                                            <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($item['item_type']); ?></span></td>
                                            <td><?php echo formatGold($item['unit_price']); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td><?php echo formatGold($item['total_value']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Advanced Farm Analytics Section -->
        <section id="advanced-farm-stats" class="mb-5">
            <div class="row mb-3">
                <div class="col-12">
                    <h2 class="h4">Advanced Farm Analytics</h2>
                </div>
            </div>
            
            <div class="row">
                <!-- Seasonal Profitability Analysis -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Seasonal Crop Profitability</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="seasonalProfitChart" width="100%" height="300"></canvas>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Based on growth time, selling price, and yield</span>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary active" data-season="all">All</button>
                                    <button type="button" class="btn btn-outline-secondary" data-season="spring">Spring</button>
                                    <button type="button" class="btn btn-outline-secondary" data-season="summer">Summer</button>
                                    <button type="button" class="btn btn-outline-secondary" data-season="fall">Fall</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Animal Productivity -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Animal Productivity Analysis</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="animalProductivityChart" width="100%" height="300"></canvas>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Based on produce value and production frequency</span>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="includeBuildingCosts" checked>
                                    <label class="form-check-label" for="includeBuildingCosts">Include Building Costs</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Resource Efficiency Analysis -->
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>Resource Efficiency Analysis</h5>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary active" data-resource="gold">Gold/Day</button>
                                <button type="button" class="btn btn-outline-secondary" data-resource="space">Gold/Tile</button>
                                <button type="button" class="btn btn-outline-secondary" data-resource="time">Gold/Time</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-sm" id="resourceEfficiencyTable">
                                    <thead>
                                        <tr>
                                            <th>Resource Type</th>
                                            <th>Name</th>
                                            <th>Season</th>
                                            <th>Base Value</th>
                                            <th>Gold per Day</th>
                                            <th>Gold per Tile</th>
                                            <th>Days to Maturity</th>
                                            <th>Regrowth</th>
                                            <th>Efficiency Rating</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // This would typically come from database - using sample data here
                                        $resources = [
                                            [
                                                'type' => 'Crop',
                                                'name' => 'Strawberry',
                                                'season' => 'Spring',
                                                'value' => 120,
                                                'gold_per_day' => 20.0,
                                                'gold_per_tile' => 480,
                                                'days_to_mature' => 8,
                                                'regrowth' => 'Yes (4 days)',
                                                'rating' => 'A'
                                            ],
                                            [
                                                'type' => 'Crop',
                                                'name' => 'Blueberry',
                                                'season' => 'Summer',
                                                'value' => 50,
                                                'gold_per_day' => 18.5,
                                                'gold_per_tile' => 550,
                                                'days_to_mature' => 13,
                                                'regrowth' => 'Yes (4 days)',
                                                'rating' => 'A'
                                            ],
                                            [
                                                'type' => 'Crop',
                                                'name' => 'Cranberry',
                                                'season' => 'Fall',
                                                'value' => 75,
                                                'gold_per_day' => 16.8,
                                                'gold_per_tile' => 420,
                                                'days_to_mature' => 7,
                                                'regrowth' => 'Yes (5 days)',
                                                'rating' => 'A'
                                            ],
                                            [
                                                'type' => 'Animal',
                                                'name' => 'Chicken (Regular)',
                                                'season' => 'All',
                                                'value' => 50,
                                                'gold_per_day' => 50,
                                                'gold_per_tile' => 50,
                                                'days_to_mature' => 5,
                                                'regrowth' => 'Daily',
                                                'rating' => 'B'
                                            ],
                                            [
                                                'type' => 'Animal',
                                                'name' => 'Cow',
                                                'season' => 'All',
                                                'value' => 125,
                                                'gold_per_day' => 125,
                                                'gold_per_tile' => 62.5,
                                                'days_to_mature' => 5,
                                                'regrowth' => 'Daily',
                                                'rating' => 'B'
                                            ]
                                        ];
                                        
                                        foreach ($resources as $resource):
                                        ?>
                                        <tr>
                                            <td><span class="badge <?php echo $resource['type'] == 'Crop' ? 'bg-success' : 'bg-primary'; ?>"><?php echo $resource['type']; ?></span></td>
                                            <td><?php echo $resource['name']; ?></td>
                                            <td>
                                                <?php if ($resource['season'] == 'Spring'): ?>
                                                    <span class="season-spring"><i class="fas fa-seedling"></i> Spring</span>
                                                <?php elseif ($resource['season'] == 'Summer'): ?>
                                                    <span class="season-summer"><i class="fas fa-sun"></i> Summer</span>
                                                <?php elseif ($resource['season'] == 'Fall'): ?>
                                                    <span class="season-fall"><i class="fas fa-leaf"></i> Fall</span>
                                                <?php else: ?>
                                                    <span><i class="fas fa-calendar"></i> All</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo formatGold($resource['value']); ?></td>
                                            <td><?php echo formatGold($resource['gold_per_day']); ?></td>
                                            <td><?php echo formatGold($resource['gold_per_tile']); ?></td>
                                            <td><?php echo $resource['days_to_mature']; ?></td>
                                            <td><?php echo $resource['regrowth']; ?></td>
                                            <td>
                                                <span class="badge <?php 
                                                    if ($resource['rating'] == 'A') echo 'bg-success';
                                                    elseif ($resource['rating'] == 'B') echo 'bg-primary';
                                                    elseif ($resource['rating'] == 'C') echo 'bg-warning text-dark';
                                                    else echo 'bg-secondary';
                                                ?>"><?php echo $resource['rating']; ?></span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Farm Optimization Recommendations -->
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-lightbulb text-warning me-2"></i> Farm Optimization Recommendations</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-success">
                                        <div class="card-body">
                                            <h5 class="card-title"><i class="fas fa-coins text-success me-2"></i> Maximize Profit</h5>
                                            <ul class="list-unstyled">
                                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Focus on Blueberries in Summer</li>
                                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Cranberries are top Fall earners</li>
                                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Process high-value items into artisan goods</li>
                                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Pigs for truffle production</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-info">
                                        <div class="card-body">
                                            <h5 class="card-title"><i class="fas fa-balance-scale text-info me-2"></i> Space Efficiency</h5>
                                            <ul class="list-unstyled">
                                                <li class="mb-2"><i class="fas fa-check text-info me-2"></i> Hops in Summer (with kegs)</li>
                                                <li class="mb-2"><i class="fas fa-check text-info me-2"></i> Ancient Fruit in Greenhouse</li>
                                                <li class="mb-2"><i class="fas fa-check text-info me-2"></i> Fruit trees around pond edges</li>
                                                <li class="mb-2"><i class="fas fa-check text-info me-2"></i> Barn animals over coop (per tile)</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-warning">
                                        <div class="card-body">
                                            <h5 class="card-title"><i class="fas fa-clock text-warning me-2"></i> Time Efficiency</h5>
                                            <ul class="list-unstyled">
                                                <li class="mb-2"><i class="fas fa-check text-warning me-2"></i> Automate watering with sprinklers</li>
                                                <li class="mb-2"><i class="fas fa-check text-warning me-2"></i> Auto-feeders for barns and coops</li>
                                                <li class="mb-2"><i class="fas fa-check text-warning me-2"></i> Coffee for movement speed</li>
                                                <li class="mb-2"><i class="fas fa-check text-warning me-2"></i> Process harvests overnight</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Achievements section -->
        <section id="achievements" class="mb-5">
            <div class="row mb-3">
                <div class="col-12">
                    <h2 class="h4">Achievements</h2>
                </div>
            </div>
            
            <div class="row">
                <!-- Achievement Progress -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Achievement Status Distribution</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="achievementStatusChart" width="100%" height="300"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Top Achievement Holders -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Top Achievement Holders</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Player</th>
                                            <th>Completed</th>
                                            <th>Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($topAchievements as $index => $player): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($player['player_name']); ?></td>
                                            <td><?php echo $player['completed_achievements']; ?></td>
                                            <td>
                                                <?php
                                                // Calculate completion percentage
                                                $stmt = $pdo->prepare("
                                                    SELECT COUNT(*) as total
                                                    FROM achievements
                                                ");
                                                $stmt->execute();
                                                $totalAchievements = $stmt->fetch()['total'];
                                                
                                                $percentage = round(($player['completed_achievements'] / $totalAchievements) * 100);
                                                ?>
                                                <div class="progress">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $percentage; ?>%" 
                                                         aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                                        <?php echo $percentage; ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            <a href="achievements.php" class="btn btn-outline-primary">View All Achievements</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Player Engagement and Progression Analysis -->
        <section id="player-engagement" class="mb-5">
            <div class="row mb-3">
                <div class="col-12">
                    <h2 class="h4">Player Engagement & Progression Analysis</h2>
                </div>
            </div>
            
            <div class="row">
                <!-- Player Engagement Trend -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Player Engagement Trend</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="engagementTrendChart" width="100%" height="300"></canvas>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Based on session frequency and duration</span>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary active" data-period="week">Week</button>
                                    <button type="button" class="btn btn-outline-secondary" data-period="month">Month</button>
                                    <button type="button" class="btn btn-outline-secondary" data-period="year">Year</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Player Progression Rate -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Player Progression Rate</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="progressionRateChart" width="100%" height="300"></canvas>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Achievement completion over time</span>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="normalizeByPlaytime" checked>
                                    <label class="form-check-label" for="normalizeByPlaytime">Normalize by Playtime</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Game Day Analysis -->
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>Game Day Analysis</h5>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary active" data-metric="gold-per-day">Gold Earned</button>
                                <button type="button" class="btn btn-outline-secondary" data-metric="activities">Activities</button>
                                <button type="button" class="btn btn-outline-secondary" data-metric="achievements">Achievements</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <canvas id="gameDayAnalysisChart" width="100%" height="300"></canvas>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">Key Insights</h6>
                                            <ul class="list-unstyled">
                                                <li class="mb-2"><i class="fas fa-arrow-up text-success me-2"></i> <strong>Most productive:</strong> Game days 5-15</li>
                                                <li class="mb-2"><i class="fas fa-bolt text-warning me-2"></i> <strong>Achievement spikes:</strong> Days 28-30</li>
                                                <li class="mb-2"><i class="fas fa-chart-line text-primary me-2"></i> <strong>Income trend:</strong> 15% increase per week</li>
                                                <li class="mb-2"><i class="fas fa-exclamation-circle text-danger me-2"></i> <strong>Drop-off point:</strong> After day 120</li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <h6>Progression Milestones</h6>
                                        <div class="progress mb-2" style="height: 20px;">
                                            <div class="progress-bar bg-success" style="width: 100%">Day 1: First Farm</div>
                                        </div>
                                        <div class="progress mb-2" style="height: 20px;">
                                            <div class="progress-bar bg-info" style="width: 85%">Day 30: Community Center</div>
                                        </div>
                                        <div class="progress mb-2" style="height: 20px;">
                                            <div class="progress-bar bg-warning text-dark" style="width: 65%">Day 60: Farm Expansion</div>
                                        </div>
                                        <div class="progress mb-2" style="height: 20px;">
                                            <div class="progress-bar bg-danger" style="width: 45%">Day 90: Desert Access</div>
                                        </div>
                                        <div class="progress mb-2" style="height: 20px;">
                                            <div class="progress-bar bg-secondary" style="width: 25%">Day 120: Ginger Island</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Activity Focus Analysis -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Player Activity Focus</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-7">
                                    <canvas id="activityFocusChart" width="100%" height="250"></canvas>
                                </div>
                                <div class="col-md-5">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Activity</th>
                                                    <th>Hrs</th>
                                                    <th>%</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><i class="fas fa-seedling text-success me-1"></i> Farming</td>
                                                    <td>42.5</td>
                                                    <td>38%</td>
                                                </tr>
                                                <tr>
                                                    <td><i class="fas fa-gem text-primary me-1"></i> Mining</td>
                                                    <td>28.3</td>
                                                    <td>25%</td>
                                                </tr>
                                                <tr>
                                                    <td><i class="fas fa-fish text-info me-1"></i> Fishing</td>
                                                    <td>18.6</td>
                                                    <td>16%</td>
                                                </tr>
                                                <tr>
                                                    <td><i class="fas fa-tree text-success me-1"></i> Foraging</td>
                                                    <td>12.2</td>
                                                    <td>11%</td>
                                                </tr>
                                                <tr>
                                                    <td><i class="fas fa-heart text-danger me-1"></i> Social</td>
                                                    <td>11.4</td>
                                                    <td>10%</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Player Retention Analysis -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Player Retention Analysis</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-7">
                                    <canvas id="retentionChart" width="100%" height="250"></canvas>
                                </div>
                                <div class="col-md-5 d-flex flex-column justify-content-center">
                                    <div class="mb-3">
                                        <h6>Retention Metrics</h6>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Day 1 Retention:</span>
                                            <span class="fw-bold">95%</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Week 1 Retention:</span>
                                            <span class="fw-bold">82%</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Month 1 Retention:</span>
                                            <span class="fw-bold">68%</span>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i> Players who complete the Community Center have 85% higher retention rates.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Player Correlation Analysis -->
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Correlation Analysis</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Metric</th>
                                            <th>Gold Earned</th>
                                            <th>Days Played</th>
                                            <th>Achievements</th>
                                            <th>Relationship Points</th>
                                            <th>Farm Size</th>
                                            <th>Items Shipped</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Session Length</strong></td>
                                            <td class="bg-success bg-opacity-25">High (0.85)</td>
                                            <td class="bg-success bg-opacity-25">High (0.92)</td>
                                            <td class="bg-success bg-opacity-25">High (0.78)</td>
                                            <td class="bg-warning bg-opacity-25">Medium (0.55)</td>
                                            <td class="bg-warning bg-opacity-25">Medium (0.61)</td>
                                            <td class="bg-success bg-opacity-25">High (0.83)</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Play Frequency</strong></td>
                                            <td class="bg-success bg-opacity-25">High (0.82)</td>
                                            <td class="bg-success bg-opacity-25">High (0.95)</td>
                                            <td class="bg-success bg-opacity-25">High (0.88)</td>
                                            <td class="bg-success bg-opacity-25">High (0.75)</td>
                                            <td class="bg-warning bg-opacity-25">Medium (0.62)</td>
                                            <td class="bg-success bg-opacity-25">High (0.79)</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Achievement Rate</strong></td>
                                            <td class="bg-success bg-opacity-25">High (0.76)</td>
                                            <td class="bg-warning bg-opacity-25">Medium (0.68)</td>
                                            <td class="bg-success bg-opacity-25">High (1.00)</td>
                                            <td class="bg-warning bg-opacity-25">Medium (0.59)</td>
                                            <td class="bg-warning bg-opacity-25">Medium (0.52)</td>
                                            <td class="bg-success bg-opacity-25">High (0.74)</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Social Focus</strong></td>
                                            <td class="bg-warning bg-opacity-25">Medium (0.48)</td>
                                            <td class="bg-warning bg-opacity-25">Medium (0.54)</td>
                                            <td class="bg-warning bg-opacity-25">Medium (0.63)</td>
                                            <td class="bg-success bg-opacity-25">High (0.91)</td>
                                            <td class="bg-danger bg-opacity-25">Low (0.32)</td>
                                            <td class="bg-warning bg-opacity-25">Medium (0.45)</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Farming Focus</strong></td>
                                            <td class="bg-success bg-opacity-25">High (0.89)</td>
                                            <td class="bg-warning bg-opacity-25">Medium (0.58)</td>
                                            <td class="bg-warning bg-opacity-25">Medium (0.52)</td>
                                            <td class="bg-danger bg-opacity-25">Low (0.38)</td>
                                            <td class="bg-success bg-opacity-25">High (0.85)</td>
                                            <td class="bg-success bg-opacity-25">High (0.93)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i> Correlation strength: High (>0.7), Medium (0.4-0.7), Low (<0.4)
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Session analytics -->
        <section id="sessions" class="mb-5">
            <div class="row mb-3">
                <div class="col-12">
                    <h2 class="h4">Session Analytics</h2>
                </div>
            </div>
            
            <div class="row">
                <!-- Session Activity Calendar -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Session Activity</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="sessionActivityChart" width="100%" height="300"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Average Session Duration -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Average Session Duration</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="sessionDurationChart" width="100%" height="300"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Sessions -->
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Recent Game Sessions</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="sessionsTable">
                                    <thead>
                                        <tr>
                                            <th>Session ID</th>
                                            <th>Player</th>
                                            <th>Start Time</th>
                                            <th>End Time</th>
                                            <th>Duration</th>
                                            <th>Achievements</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $stmt = $pdo->query("
                                            SELECT gs.session_id, p.name, gs.start_time, gs.end_time, 
                                                   TIMESTAMPDIFF(MINUTE, gs.start_time, gs.end_time) as duration,
                                                   COUNT(pa.achievement_id) as achievement_count
                                            FROM game_sessions gs
                                            JOIN players p ON gs.player_id = p.player_id
                                            LEFT JOIN player_achievements pa ON gs.session_id = pa.session_id
                                            GROUP BY gs.session_id, p.name, gs.start_time, gs.end_time
                                            ORDER BY gs.start_time DESC
                                            LIMIT 10
                                        ");
                                        $recentSessions = $stmt->fetchAll();
                                        
                                        foreach ($recentSessions as $session):
                                        ?>
                                        <tr>
                                            <td><?php echo $session['session_id']; ?></td>
                                            <td><?php echo htmlspecialchars($session['name']); ?></td>
                                            <td><?php echo date('M d, Y g:i A', strtotime($session['start_time'])); ?></td>
                                            <td><?php echo date('M d, Y g:i A', strtotime($session['end_time'])); ?></td>
                                            <td><?php echo formatDuration($session['duration']); ?></td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo $session['achievement_count']; ?></span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php include 'components/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 获取并显示排名数据（默认按金币排名）
        fetchTopPlayers('total_gold_earned');
        
        // 为排名标签添加点击事件
        const rankingButtons = document.querySelectorAll('.card-header .btn-group button');
        rankingButtons.forEach(button => {
            button.addEventListener('click', function() {
                // 移除所有按钮的 active 类
                rankingButtons.forEach(btn => btn.classList.remove('active'));
                // 为当前点击的按钮添加 active 类
                this.classList.add('active');
                // 获取排名标准
                const criteria = this.getAttribute('data-criteria');
                // 获取并显示排名数据
                fetchTopPlayers(criteria);
            });
        });
    });
    
    // 获取排名数据
    function fetchTopPlayers(criteria) {
        const topPlayersList = document.getElementById('top-players-list');
        topPlayersList.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p>Loading top players...</p>
            </div>
        `;
        
        fetch(`api/top_players.php?limit=5&criteria=${criteria}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    renderTopPlayers(data.data, criteria);
                } else {
                    topPlayersList.innerHTML = `<p class="text-danger">Error: ${data.message}</p>`;
                }
            })
            .catch(error => {
                console.error('Error fetching top players:', error);
                topPlayersList.innerHTML = '<p class="text-danger">Error loading top players. Please try again later.</p>';
            });
    }
    
    // 渲染排名数据
    function renderTopPlayers(players, criteria) {
        const topPlayersList = document.getElementById('top-players-list');
        topPlayersList.innerHTML = '';
        
        if (!players || players.length === 0) {
            topPlayersList.innerHTML = '<p class="text-center">No players found.</p>';
            return;
        }
        
        const fragment = document.createDocumentFragment();
        
        players.forEach((player, index) => {
            const rankItem = document.createElement('div');
            rankItem.className = 'rank-item';
            rankItem.setAttribute('data-player-id', player.player_id);
            
            // 根据排名标准显示不同的分数标签
            const scoreLabel = criteria === 'total_gold_earned' ? 'Gold' : 'Days';
            const scoreValue = criteria === 'total_gold_earned' 
                ? formatGold(player.score) 
                : player.score;
            
            // 为前三名添加特殊样式
            const positionClass = index < 3 ? ` top-${index + 1}` : '';
            
            rankItem.innerHTML = `
                <div class="rank-position${positionClass}">#${index + 1}</div>
                <div class="rank-info">
                    <h4>${player.name}</h4>
                    <p>${player.farm_name || 'Farm'}</p>
                </div>
                <div class="rank-score">${scoreValue}</div>
                <div class="rank-actions">
                    <a href="players.php?id=${player.player_id}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i>
                    </a>
                </div>
            `;
            
            fragment.appendChild(rankItem);
        });
        
        topPlayersList.appendChild(fragment);
    }
    
    // 格式化金币数字（添加千位分隔符和 g 后缀）
    function formatGold(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',') + 'g';
    }
</script>