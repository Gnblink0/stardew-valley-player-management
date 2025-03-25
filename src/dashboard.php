<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get data for dashboard
$topPlayers = getTopPlayers(5);
$playerPlaytime = getPlayerPlaytimeStats();
$topAchievements = getTopAchievementPlayers(5);
$cropsBySeason = getCropsBySeason();
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
    
    .achievement-row:hover {
        background-color: rgba(0, 0, 0, 0.075) !important;
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
                                        <tr class="achievement-row" style="cursor: pointer;" 
                                            onclick="window.location.href='achievements.php?player_id=<?php echo isset($player['player_id']) ? htmlspecialchars($player['player_id']) : ''; ?>'">
                                            <td><?php echo $index + 1; ?></td>
                                            <td>
                                                <a href="achievements.php?player_id=<?php echo isset($player['player_id']) ? htmlspecialchars($player['player_id']) : ''; ?>" 
                                                   class="text-decoration-none text-dark">
                                                    <?php echo isset($player['player_name']) ? htmlspecialchars($player['player_name']) : 'Unknown Player'; ?>
                                                </a>
                                            </td>
                                            <td><?php echo isset($player['completed_achievements']) ? $player['completed_achievements'] : 0; ?></td>
                                            <td>
                                                <?php
                                                // Calculate completion percentage
                                                $stmt = $pdo->prepare("
                                                    SELECT COUNT(*) as total
                                                    FROM achievements
                                                ");
                                                $stmt->execute();
                                                $totalAchievements = $stmt->fetch()['total'];
                                                
                                                $completedAchievements = isset($player['completed_achievements']) ? $player['completed_achievements'] : 0;
                                                $percentage = $totalAchievements > 0 ? round(($completedAchievements / $totalAchievements) * 100) : 0;
                                                ?>
                                                <div class="progress">
                                                    <div class="progress-bar bg-success" role="progressbar" 
                                                         style="width: <?php echo $percentage; ?>%" 
                                                         aria-valuenow="<?php echo $percentage; ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
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