<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get some basic statistics
$playerCount = count(getPlayers());
$topPlayer = getTopPlayers(1)[0] ?? null;
$completedAchievements = getTopAchievementPlayers(1)[0] ?? null;
?>

<?php include 'components/header.php'; ?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <div class="jumbotron p-4 bg-light rounded">
                <h1 class="display-4">Welcome to Stardew Valley Player Management</h1>
                <p class="lead">Track player progress, analyze game data, and manage achievements in Stardew Valley.</p>
                <hr class="my-4">
                <p>View detailed statistics, manage player profiles, and explore farm analytics through our interactive dashboard.</p>
                <a class="btn btn-primary btn-lg" href="dashboard.php" role="button">
                    <i class="fas fa-chart-line me-2"></i>View Dashboard
                </a>
                <a class="btn btn-success btn-lg ms-2" href="players.php" role="button">
                    <i class="fas fa-users me-2"></i>Manage Players
                </a>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center d-flex flex-column justify-content-between">
                    <div>
                        <i class="fas fa-users fa-3x mb-3 text-primary"></i>
                        <h5 class="card-title">Total Players</h5>
                        <p class="card-text stat-counter display-4 mb-4"><?php echo $playerCount; ?></p>
                    </div>
                    <div>
                        <a href="players.php" class="btn btn-primary">View Players</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center d-flex flex-column justify-content-between">
                    <div>
                        <i class="fas fa-trophy fa-3x mb-3 text-warning"></i>
                        <h5 class="card-title">Top Farmer</h5>
                        <?php if ($topPlayer): ?>
                        <p class="card-text stat-counter display-4"><?php echo formatGold($topPlayer['score']); ?></p>
                        <p class="stat-label mb-4"><?php echo htmlspecialchars($topPlayer['name']); ?> - <?php echo htmlspecialchars($topPlayer['farm_name']); ?></p>
                        <?php else: ?>
                        <p class="card-text mb-4">No data available</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center d-flex flex-column justify-content-between">
                    <div>
                        <i class="fas fa-award fa-3x mb-3 text-success"></i>
                        <h5 class="card-title">Achievement Master</h5>
                        <?php if ($completedAchievements): ?>
                        <p class="card-text stat-counter display-4"><?php echo $completedAchievements['completed_achievements']; ?></p>
                        <p class="stat-label mb-4">Completed by <?php echo htmlspecialchars($completedAchievements['player_name']); ?></p>
                        <?php else: ?>
                        <p class="card-text mb-4">No data available</p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <a href="achievements.php" class="btn btn-success">View Achievements</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Recent Game Sessions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Player</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Get recent sessions
                                $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                                $stmt = $pdo->query("
                                    SELECT gs.session_id, p.name, gs.start_time, gs.end_time, 
                                           TIMESTAMPDIFF(MINUTE, gs.start_time, gs.end_time) as duration
                                    FROM game_sessions gs
                                    JOIN players p ON gs.player_id = p.player_id
                                    ORDER BY gs.start_time DESC
                                    LIMIT 5
                                ");
                                $recentSessions = $stmt->fetchAll();
                                
                                foreach ($recentSessions as $session):
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($session['name']); ?></td>
                                    <td><?php echo date('M d, Y g:i A', strtotime($session['start_time'])); ?></td>
                                    <td><?php echo date('M d, Y g:i A', strtotime($session['end_time'])); ?></td>
                                    <td><?php echo formatDuration($session['duration']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <a href="dashboard.php#sessions" class="btn btn-outline-primary">View All Sessions</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'components/footer.php'; ?>