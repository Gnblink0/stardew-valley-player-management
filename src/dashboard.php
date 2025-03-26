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

<head>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>

<div class="row">
    <!-- Sidebar -->
    <?php include 'components/sidebar.php'; ?>
    
    <!-- Main content -->
    <div class="col-md-9 col-lg-10 dashboard-container">
        <!-- Page title and filter controls -->
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Stardew Valley Analytics Dashboard</h1>
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
                        <div class="ms-3">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="text-secondary mb-1">Total Players</div>
                                <div class="metric-value"><?php echo count(getPlayers()); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Total Gold Earned -->
                <div class="col-md-3 mb-4">
                    <div class="metric-card">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="ms-3">
                                    <i class="fas fa-coins fa-2x text-warning"></i>
                                </div>
                                <div class="text-secondary mb-1">Total Gold Earned</div>
                                <?php
                                $stmt = $pdo->query("SELECT SUM(total_gold_earned) as total FROM player_statistics");
                                $totalGold = $stmt->fetch()['total'];
                                ?>
                                <div class="metric-value"><?php echo formatGold($totalGold); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Total Playtime -->
                <div class="col-md-3 mb-4">
                    <div class="metric-card">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                            <div class="ms-3">
                                <i class="far fa-clock fa-2x text-info"></i>
                            </div>
                                <div class="text-secondary mb-1">Total Playtime</div>
                                <?php
                                $stmt = $pdo->query("SELECT SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as total_minutes FROM game_sessions");
                                $totalMinutes = $stmt->fetch()['total_minutes'];
                                $totalHours = round($totalMinutes / 60, 1);
                                ?>
                                <div class="metric-value"><?php echo $totalHours; ?> hrs</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Achievements Completed -->
                <div class="col-md-3 mb-4">
                    <div class="metric-card">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="ms-3">
                                    <i class="fas fa-trophy fa-2x text-success"></i>
                                </div>
                                <div class="text-secondary mb-1">Achievements Completed</div>
                                <?php
                                $stmt = $pdo->query("SELECT COUNT(*) as count FROM player_achievements WHERE status = 'Completed'");
                                $completedCount = $stmt->fetch()['count'];
                                ?>
                                <div class="metric-value"><?php echo $completedCount; ?></div>
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
            </div>
                            <!-- Playtime Distribution -->
            <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Playtime Distribution</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="sessionPlaytimeChart"></canvas>
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
                <!-- Top Achievement Holders -->
                <div class="col-md-12 mb-4">
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
            <!-- Playtime Distribution Chart -->
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Playtime Distribution</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="playtimeChart" width="100%" height="300"></canvas>
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
        // fetch and display top players
        fetchTopPlayers('total_gold_earned');
        fetchPlaytimeDistribution();
        fetchSessionAnalytics();

        // add click event to ranking buttons
        const rankingButtons = document.querySelectorAll('.card-header .btn-group button');
        rankingButtons.forEach(button => {
            button.addEventListener('click', function() {
                // remove active class from all buttons
                rankingButtons.forEach(btn => btn.classList.remove('active'));
                // add active class to current button
                this.classList.add('active');
                // get ranking criteria
                const criteria = this.getAttribute('data-criteria');
                // fetch and display top players
                fetchTopPlayers(criteria);
            });
        });

        // add export button event listener
        document.getElementById('export-data').addEventListener('click', exportToPDF);
        
        // add refresh button event listener
        document.getElementById('refresh-data').addEventListener('click', async function() {
            const refreshBtn = this;
            const originalText = refreshBtn.innerHTML;
            
            // show loading state
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Refreshing...';
            refreshBtn.disabled = true;
            
            try {
                // get current ranking criteria
                const activeRankingButton = document.querySelector('.card-header .btn-group button.active');
                const criteria = activeRankingButton ? activeRankingButton.getAttribute('data-criteria') : 'total_gold_earned';
                
                // fetch and display top players
                console.log('Fetching top players...');
                const topPlayersResponse = await fetch(`api/top_players.php?criteria=${criteria}`);
                if (!topPlayersResponse.ok) throw new Error(`Failed to fetch top players: ${topPlayersResponse.status}`);
                const topPlayersData = await topPlayersResponse.json();
                if (topPlayersData.status === 'success') {
                    renderTopPlayers(topPlayersData.data, criteria);
                } else {
                    throw new Error(topPlayersData.message || 'Failed to load top players');
                }
                
                console.log('Fetching playtime distribution...');
                const playtimeResponse = await fetch('api/playtime_distribution.php');
                if (!playtimeResponse.ok) throw new Error(`Failed to fetch playtime distribution: ${playtimeResponse.status}`);
                const playtimeData = await playtimeResponse.json();
                if (playtimeData.status === 'success') {
                    renderPlaytimeChart(playtimeData.data);
                } else {
                    throw new Error(playtimeData.message || 'Failed to load playtime distribution');
                }
                
                console.log('Fetching session analytics...');
                const sessionResponse = await fetch('api/session_analytics.php');
                if (!sessionResponse.ok) throw new Error(`Failed to fetch session analytics: ${sessionResponse.status}`);
                const sessionData = await sessionResponse.json();
                if (sessionData.status === 'success') {
                    renderSessionChart(sessionData.data);
                } else {
                    throw new Error(sessionData.message || 'Failed to load session analytics');
                }
                
                // show success message
                const toastDiv = document.createElement('div');
                toastDiv.className = 'toast align-items-center text-white bg-success border-0 position-fixed bottom-0 end-0 m-3';
                toastDiv.innerHTML = `
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-check-circle me-2"></i>Data refreshed successfully!
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                `;
                document.body.appendChild(toastDiv);
                const toast = new bootstrap.Toast(toastDiv);
                toast.show();
                setTimeout(() => toastDiv.remove(), 3000);
                
            } catch (error) {
                console.error('Error refreshing data:', error);
                alert(`Failed to refresh data: ${error.message}`);
            } finally {
                // restore button state
                refreshBtn.innerHTML = originalText;
                refreshBtn.disabled = false;
            }
        });
    });
    
    // fetch top players
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
        
        fetch(`api/top_players.php?criteria=${criteria}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    renderTopPlayers(data.data, criteria);
                } else {
                    throw new Error(data.message || 'Failed to load top players');
                }
            })
            .catch(error => {
                console.error('Error fetching top players:', error);
                topPlayersList.innerHTML = '<p class="text-danger">Error loading top players. Please try again later.</p>';
            });
    }

    
    // render top players
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
            
            // display different score labels based on ranking criteria
            const scoreLabel = criteria === 'total_gold_earned' ? 'Gold' : 'Days';
            const scoreValue = criteria === 'total_gold_earned' 
                ? formatGold(player.score) 
                : player.score;
            
            // add special style to top 3 players
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
    
    // format gold number (add comma and g suffix)
    function formatGold(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',') + 'g';
    }

    // fetch and render playtime distribution data
    function fetchPlaytimeDistribution() {
        fetch('api/playtime_distribution.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    renderPlaytimeChart(data.data);
                } else {
                    throw new Error(data.message || 'Failed to load playtime distribution');
                }
            })
            .catch(error => {
                console.error('Error fetching playtime distribution:', error);
                const canvas = document.getElementById('playtimeChart');
                const ctx = canvas.getContext('2d');
                ctx.font = '14px Arial';
                ctx.fillStyle = '#dc3545';
                ctx.textAlign = 'center';
                ctx.fillText('Error loading playtime distribution. Please try again later.',
                    canvas.width/2,
                    canvas.height/2);
            });
    }

    // render playtime distribution chart
    function renderPlaytimeChart(data) {
        const ctx = document.getElementById('playtimeChart').getContext('2d');
        
        // destroy existing chart (if exists)
        if (window.playtimeChart instanceof Chart) {
            window.playtimeChart.destroy();
        }
        
        // ensure data exists and is correct format
        if (!data || !data.labels || !data.values) {
            console.error('Invalid playtime data format:', data);
            return;
        }
        
        window.playtimeChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Number of Players',
                    data: data.values,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        title: {
                            display: true,
                            text: 'Number of Players'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Playtime Range'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: 'Playtime Distribution'
                    }
                }
            }
        });
    }

    // fetch session analytics data
    function fetchSessionAnalytics() {
        fetch('api/session_analytics.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    renderSessionChart(data.data);
                } else {
                    throw new Error(data.message || 'Failed to load session analytics');
                }
            })
            .catch(error => {
                console.error('Error fetching session analytics:', error);
                const canvas = document.getElementById('sessionPlaytimeChart');
                const ctx = canvas.getContext('2d');
                ctx.font = 'Arial';
                ctx.fillStyle = '#dc3545';
                ctx.textAlign = 'center';
                ctx.fillText('Error loading session analytics. Please try again later.',
                    canvas.width/2,
                    canvas.height/2);
            });
    }

    // render session analytics chart
    function renderSessionChart(data) {
        const ctx = document.getElementById('sessionPlaytimeChart').getContext('2d');
        
        // destroy existing chart (if exists)
        if (window.sessionChart instanceof Chart) {
            window.sessionChart.destroy();
        }
        
        // ensure data exists and is correct format
        if (!data || !data.labels || !data.values) {
            console.error('Invalid session data format:', data);
            return;
        }
        
        window.sessionChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Session Duration Distribution',
                    data: data.values,
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        title: {
                            display: true,
                            text: 'Number of Sessions'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Duration Range'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: 'Session Duration Distribution'
                    }
                }
            }
        });
    }

    // add export PDF function
    async function exportToPDF() {
        try {
            // show loading prompt
            const exportBtn = document.getElementById('export-data');
            const originalText = exportBtn.innerHTML;
            exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Exporting...';
            exportBtn.disabled = true;

            // create PDF instance - use horizontal layout
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l', 'mm', 'a4'); // change to horizontal layout

            // set title
            doc.setFontSize(20);
            doc.text('Stardew Valley Analytics Report', 20, 20);
            doc.setFontSize(12);
            doc.text(`Generated on: ${new Date().toLocaleString()}`, 20, 30);

            // export key metrics
            const metricsSection = document.getElementById('overview');
            const metricsCanvas = await html2canvas(metricsSection, {
                scale: 2,
                backgroundColor: '#ffffff',
                logging: false, // disable logging
                useCORS: true // enable cross-origin resource sharing
            });
            const metricsAspectRatio = metricsCanvas.width / metricsCanvas.height;
            const metricsWidth = 250;
            const metricsHeight = metricsWidth / metricsAspectRatio;
            
            doc.addImage(
                metricsCanvas.toDataURL('image/png'),
                'PNG',
                20,
                40,
                metricsWidth,
                metricsHeight
            );

            // export playtime distribution chart
            const playtimeChart = document.getElementById('playtimeChart');
            const playtimeCanvas = await html2canvas(playtimeChart.parentElement, {
                scale: 2,
                backgroundColor: '#ffffff',
                logging: false,
                useCORS: true
            });
            const chartAspectRatio = playtimeCanvas.width / playtimeCanvas.height;
            const chartWidth = 250;
            const chartHeight = chartWidth / chartAspectRatio;

            doc.addImage(
                playtimeCanvas.toDataURL('image/png'),
                'PNG',
                20,
                metricsHeight + 50, // leave enough space below key metrics
                chartWidth,
                chartHeight
            );

            // add header
            doc.setFontSize(10);
            doc.setTextColor(128, 128, 128);
            doc.text('Stardew Valley Player Management System', doc.internal.pageSize.width - 20, 10, { align: 'right' });

            // add footer
            const pageCount = doc.internal.getNumberOfPages();
            for(let i = 1; i <= pageCount; i++) {
                doc.setPage(i);
                doc.text(`Page ${i} of ${pageCount}`, doc.internal.pageSize.width - 20, doc.internal.pageSize.height - 10, { align: 'right' });
            }

            // save PDF
            doc.save('stardew-valley-analytics.pdf');

            // restore button state
            exportBtn.innerHTML = originalText;
            exportBtn.disabled = false;

        } catch (error) {
            console.error('Error generating PDF:', error);
            alert('Failed to generate PDF. Please try again.');
            
            // restore button state
            const exportBtn = document.getElementById('export-data');
            exportBtn.innerHTML = '<i class="fas fa-download me-1"></i> Export';
            exportBtn.disabled = false;
        }
    }
</script>