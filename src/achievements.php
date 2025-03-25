<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check for filter parameters
$playerId = isset($_GET['player_id']) ? (int)$_GET['player_id'] : null;
$status = isset($_GET['status']) ? $_GET['status'] : null;

// Get achievements based on filters
if ($playerId) {
    $achievements = getPlayerAchievements($playerId);
} else {
    $achievements = getAchievements();
}

// Get all players for filter dropdown
$players = getPlayers();

include 'components/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Achievements Dashboard</h2>
            </div>
        </div>
    </div>
    
    <!-- Filter controls -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card filter-controls">
                <div class="card-body">
                    <form action="achievements.php" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="player_id" class="form-label">Player</label>
                            <select class="form-select" id="player_id" name="player_id">
                                <option value="">All Players</option>
                                <?php foreach ($players as $player): ?>
                                <option value="<?php echo $player['player_id']; ?>" <?php echo $playerId == $player['player_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($player['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="Completed" <?php echo $status === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="In Progress" <?php echo $status === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="Not Started" <?php echo $status === 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i> Apply Filters
                                </button>
                                <a href="achievements.php" class="btn btn-secondary ms-2">
                                    <i class="fas fa-undo me-1"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Achievement statistics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="metric-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="text-secondary mb-1">Total Achievements</div>
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) as count FROM achievements");
                        $totalCount = $stmt->fetch()['count'];
                        ?>
                        <div class="metric-value"><?php echo $totalCount; ?></div>
                    </div>
                    <div class="ms-3 text-primary">
                        <i class="fas fa-trophy fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="metric-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="text-secondary mb-1">Completed</div>
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) as count FROM player_achievements WHERE status = 'Completed'");
                        $completedCount = $stmt->fetch()['count'];
                        ?>
                        <div class="metric-value"><?php echo $completedCount; ?></div>
                    </div>
                    <div class="ms-3 text-success">
                        <i class="fas fa-check-circle fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="metric-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="text-secondary mb-1">In Progress</div>
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) as count FROM player_achievements WHERE status = 'In Progress'");
                        $inProgressCount = $stmt->fetch()['count'];
                        ?>
                        <div class="metric-value"><?php echo $inProgressCount; ?></div>
                    </div>
                    <div class="ms-3 text-warning">
                        <i class="fas fa-spinner fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($playerId): ?>
    <!-- Achievement progress for specific player -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <?php $playerName = getPlayers($playerId)['name']; ?>
                    <h5>Achievement Progress for <?php echo htmlspecialchars($playerName); ?></h5>
                </div>
                <div class="card-body">
                    <?php
                    // Calculate totals
                    $completed = $inProgress = $notStarted = 0;
                    
                    foreach ($achievements as $achievement) {
                        if ($achievement['status'] === 'Completed') {
                            $completed++;
                        } elseif ($achievement['status'] === 'In Progress') {
                            $inProgress++;
                        } elseif ($achievement['status'] === 'Not Started') {
                            $notStarted++;
                        }
                    }
                    
                    $total = count($achievements);
                    $completedPercent = $total > 0 ? round(($completed / $total) * 100) : 0;
                    ?>
                    
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-success" style="width: <?php echo $completedPercent; ?>%">
                            <?php echo $completedPercent; ?>% Complete
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-4 text-center">
                            <div class="display-6 fw-bold text-success"><?php echo $completed; ?></div>
                            <div>Completed</div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="display-6 fw-bold text-warning"><?php echo $inProgress; ?></div>
                            <div>In Progress</div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="display-6 fw-bold text-secondary"><?php echo $notStarted; ?></div>
                            <div>Not Started</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Achievement list -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5><?php echo $playerId ? 'Player Achievements' : 'All Achievements'; ?></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="achievementsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Achievement</th>
                                    <th>Goal</th>
                                    <?php if ($playerId): ?>
                                <!-- Display achievements for specific player -->
                                <?php foreach ($achievements as $achievement): ?>
                                <tr>
                                    <td><?php echo $achievement['achievement_id']; ?></td>
                                    <td><?php echo htmlspecialchars($achievement['name']); ?></td>
                                    <td><?php echo htmlspecialchars($achievement['goal']); ?></td>
                                    <td>
                                        <?php if ($achievement['status'] === 'Completed'): ?>
                                        <span class="badge bg-success">Completed</span>
                                        <?php elseif ($achievement['status'] === 'In Progress'): ?>
                                        <span class="badge bg-warning text-dark">In Progress</span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary">Not Started</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <!-- Display all achievements with completion rates -->
                                <?php
                                foreach ($achievements as $achievement):
                                    // Get completion statistics for this achievement
                                    $stmt = $pdo->prepare("
                                        SELECT 
                                            COUNT(CASE WHEN status = 'Completed' THEN 1 END) as completed,
                                            COUNT(*) as total
                                        FROM player_achievements
                                        WHERE achievement_id = ?
                                    ");
                                    $stmt->execute([$achievement['achievement_id']]);
                                    $stats = $stmt->fetch();
                                    
                                    $completionRate = $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0;
                                ?>
                                <tr>
                                    <td><?php echo $achievement['achievement_id']; ?></td>
                                    <td><?php echo htmlspecialchars($achievement['name']); ?></td>
                                    <td><?php echo htmlspecialchars($achievement['goal']); ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 10px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $completionRate; ?>%" 
                                                     aria-valuenow="<?php echo $completionRate; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="text-nowrap"><?php echo $completionRate; ?>%</div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Top Achievement Holders -->
    <div class="row mb-4">
        <div class="col-md-12">
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
                                    <th>In Progress</th>
                                    <th>Not Started</th>
                                    <th>Completion Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->query("
                                    SELECT p.player_id, p.name,
                                        COUNT(CASE WHEN pa.status = 'Completed' THEN 1 END) as completed,
                                        COUNT(CASE WHEN pa.status = 'In Progress' THEN 1 END) as in_progress,
                                        COUNT(CASE WHEN pa.status = 'Not Started' THEN 1 END) as not_started
                                    FROM players p
                                    JOIN game_sessions gs ON p.player_id = gs.player_id
                                    JOIN player_achievements pa ON gs.session_id = pa.session_id
                                    GROUP BY p.player_id, p.name
                                    ORDER BY completed DESC
                                    LIMIT 10
                                ");
                                $topPlayers = $stmt->fetchAll();
                                
                                foreach ($topPlayers as $index => $player):
                                    $total = $player['completed'] + $player['in_progress'] + $player['not_started'];
                                    $completionRate = $total > 0 ? round(($player['completed'] / $total) * 100) : 0;
                                ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <a href="players.php?id=<?php echo $player['player_id']; ?>">
                                            <?php echo htmlspecialchars($player['name']); ?>
                                        </a>
                                    </td>
                                    <td><span class="badge bg-success"><?php echo $player['completed']; ?></span></td>
                                    <td><span class="badge bg-warning text-dark"><?php echo $player['in_progress']; ?></span></td>
                                    <td><span class="badge bg-secondary"><?php echo $player['not_started']; ?></span></td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $completionRate; ?>%" 
                                                 aria-valuenow="<?php echo $completionRate; ?>" aria-valuemin="0" aria-valuemax="100">
                                                <?php echo $completionRate; ?>%
                                            </div>
                                        </div>
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
    
    <!-- Achievement Categories -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Achievement Categories</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-coins fa-3x mb-3 text-warning"></i>
                                    <h5>Wealth</h5>
                                    <p class="text-muted">Earning gold milestones</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-fish fa-3x mb-3 text-primary"></i>
                                    <h5>Fishing</h5>
                                    <p class="text-muted">Catch various fish</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-seedling fa-3x mb-3 text-success"></i>
                                    <h5>Farming</h5>
                                    <p class="text-muted">Crop and animal achievements</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-heart fa-3x mb-3 text-danger"></i>
                                    <h5>Relationships</h5>
                                    <p class="text-muted">Friendship with villagers</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize DataTables
        $('#achievementsTable').DataTable({
            pageLength: 10,
            responsive: true
        });
    });
</script>

<?php include 'components/footer.php'; ?>
                                    <th>Status</th>
                                    <?php else: ?>
                                    <th>Completion Rate</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($playerId): ?>