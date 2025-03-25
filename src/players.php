<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if specific player ID is provided
$playerId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle player actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update player
    if (isset($_POST['update_player']) && isset($_POST['player_id'])) {
        $data = [
            'name' => $_POST['name'] ?? null,
            'avatar' => $_POST['avatar'] ?? null,
            'farm_name' => $_POST['farm_name'] ?? null
        ];
        
        if (updatePlayer($_POST['player_id'], $data)) {
            $successMessage = "Player updated successfully!";
            header("Location: players.php?id=" . $_POST['player_id'] . "&success=update");
            exit;
        } else {
            $errorMessage = "Failed to update player.";
        }
    }
    
    // Delete player (usually better handled via AJAX, but simplified for this example)
    if (isset($_POST['delete_player']) && isset($_POST['player_id'])) {
        if (deletePlayer($_POST['player_id'])) {
            header("Location: players.php?success=delete");
            exit;
        } else {
            $errorMessage = "Failed to delete player.";
        }
    }
}

// Get success messages from redirects
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'update') {
        $successMessage = "Player updated successfully!";
    } elseif ($_GET['success'] === 'delete') {
        $successMessage = "Player deleted successfully!";
    }
}

include 'components/header.php';
?>

<div class="container">
    <?php if (isset($successMessage)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $successMessage; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <?php if (isset($errorMessage)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $errorMessage; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if ($playerId): ?>
        <?php
        // Get player details
        $player = getPlayers($playerId);
        
        if (!$player) {
            echo '<div class="alert alert-warning">Player not found!</div>';
            echo '<div class="text-center mt-4"><a href="players.php" class="btn btn-primary">Back to Players</a></div>';
        } else {
            // Get additional player data
            $stmt = $pdo->prepare("SELECT * FROM player_statistics WHERE player_id = ?");
            $stmt->execute([$playerId]);
            $playerStats = $stmt->fetch();
            
            // Get player sessions
            $playerSessions = getPlayerSessions($playerId);
            
            // Get player achievements
            $playerAchievements = getPlayerAchievements($playerId);
        ?>
        
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>Player Profile: <?php echo htmlspecialchars($player['name']); ?></h2>
                    <div>
                        <?php if ($action !== 'edit'): ?>
                        <a href="players.php?id=<?php echo $playerId; ?>&action=edit" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <?php endif; ?>
                        <a href="players.php" class="btn btn-secondary ms-2">
                            <i class="fas fa-arrow-left me-1"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($action === 'edit'): ?>
        <!-- Edit Player Form -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5>Edit Player</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="players.php">
                            <input type="hidden" name="player_id" value="<?php echo $playerId; ?>">
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Player Name</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($player['name']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="avatar" class="form-label">Avatar</label>
                                <select class="form-select" id="avatar" name="avatar">
                                    <option value="male_1" <?php echo $player['avatar'] === 'male_1' ? 'selected' : ''; ?>>Male 1</option>
                                    <option value="male_2" <?php echo $player['avatar'] === 'male_2' ? 'selected' : ''; ?>>Male 2</option>
                                    <option value="female_1" <?php echo $player['avatar'] === 'female_1' ? 'selected' : ''; ?>>Female 1</option>
                                    <option value="female_2" <?php echo $player['avatar'] === 'female_2' ? 'selected' : ''; ?>>Female 2</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="farm_name" class="form-label">Farm Name</label>
                                <input type="text" class="form-control" id="farm_name" name="farm_name" 
                                       value="<?php echo htmlspecialchars($player['farm_name']); ?>">
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <button type="submit" name="update_player" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Save Changes
                                </button>
                                <a href="players.php?id=<?php echo $playerId; ?>" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- Player Info -->
        <div class="row mb-4">
            <div class="col-md-4 mb-4">
                <div class="card card-player h-100">
                    <div class="card-header">
                        <h5>Player Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <?php
                            // Display avatar image (in a real system, these would be actual images)
                            $avatarStyle = "";
                            switch ($player['avatar']) {
                                case 'male_1':
                                    $avatarStyle = "bg-primary text-white";
                                    $avatarIcon = "fa-user";
                                    break;
                                case 'male_2':
                                    $avatarStyle = "bg-info text-white";
                                    $avatarIcon = "fa-user";
                                    break;
                                case 'female_1':
                                    $avatarStyle = "bg-danger text-white";
                                    $avatarIcon = "fa-user";
                                    break;
                                case 'female_2':
                                    $avatarStyle = "bg-warning text-white";
                                    $avatarIcon = "fa-user";
                                    break;
                                default:
                                    $avatarStyle = "bg-secondary text-white";
                                    $avatarIcon = "fa-user";
                            }
                            ?>
                            <div class="avatar-circle <?php echo $avatarStyle; ?> mx-auto mb-3" style="width:100px;height:100px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                <i class="fas <?php echo $avatarIcon; ?> fa-3x"></i>
                            </div>
                            <h4><?php echo htmlspecialchars($player['name']); ?></h4>
                            <p class="lead"><?php echo htmlspecialchars($player['farm_name']); ?></p>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-3">
                            <h6>Player ID</h6>
                            <p><?php echo $player['player_id']; ?></p>
                        </div>
                        
                        <?php if ($playerStats): ?>
                        <div class="mb-3">
                            <h6>Total Gold Earned</h6>
                            <p class="text-warning fw-bold"><?php echo formatGold($playerStats['total_gold_earned']); ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <h6>In-Game Days</h6>
                            <p><?php echo $playerStats['in_game_days']; ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5>Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Achievements -->
                            <div class="col-md-6 mb-4">
                                <h6>Achievements</h6>
                                <?php
                                $completed = $inProgress = $notStarted = 0;
                                
                                foreach ($playerAchievements as $achievement) {
                                    if ($achievement['status'] === 'Completed') {
                                        $completed++;
                                    } elseif ($achievement['status'] === 'In Progress') {
                                        $inProgress++;
                                    } elseif ($achievement['status'] === 'Not Started') {
                                        $notStarted++;
                                    }
                                }
                                
                                $total = $completed + $inProgress + $notStarted;
                                $completedPercent = $total > 0 ? round(($completed / $total) * 100) : 0;
                                ?>
                                
                                <div class="progress mb-2">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $completedPercent; ?>%" 
                                         aria-valuenow="<?php echo $completedPercent; ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?php echo $completedPercent; ?>%
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span><i class="fas fa-check-circle text-success"></i> Completed: <?php echo $completed; ?></span>
                                    <span><i class="fas fa-spinner text-warning"></i> In Progress: <?php echo $inProgress; ?></span>
                                    <span><i class="fas fa-clock text-secondary"></i> Not Started: <?php echo $notStarted; ?></span>
                                </div>
                                
                                <a href="achievements.php?player_id=<?php echo $playerId; ?>" class="btn btn-sm btn-primary">
                                    View Achievements
                                </a>
                            </div>
                            
                            <!-- Session Stats -->
                            <div class="col-md-6 mb-4">
                                <h6>Game Sessions</h6>
                                <?php
                                $totalSessions = count($playerSessions);
                                $totalMinutes = 0;
                                
                                foreach ($playerSessions as $session) {
                                    $start = new DateTime($session['start_time']);
                                    $end = new DateTime($session['end_time']);
                                    $diff = $start->diff($end);
                                    $minutes = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
                                    $totalMinutes += $minutes;
                                }
                                
                                $avgMinutes = $totalSessions > 0 ? round($totalMinutes / $totalSessions) : 0;
                                ?>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <div>
                                        <p class="mb-0"><i class="fas fa-gamepad text-primary"></i> Total Sessions</p>
                                        <p class="h5"><?php echo $totalSessions; ?></p>
                                    </div>
                                    <div>
                                        <p class="mb-0"><i class="fas fa-clock text-info"></i> Total Playtime</p>
                                        <p class="h5"><?php echo formatDuration($totalMinutes); ?></p>
                                    </div>
                                    <div>
                                        <p class="mb-0"><i class="fas fa-hourglass-half text-warning"></i> Avg. Session</p>
                                        <p class="h5"><?php echo formatDuration($avgMinutes); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Farm Items -->
                            <div class="col-md-12 mb-3">
                                <h6>Inventory Items</h6>
                                <?php
                                $stmt = $pdo->prepare("
                                    SELECT i.name, i.type, i.value, inv.quantity, (i.value * inv.quantity) as total_value
                                    FROM inventory inv
                                    JOIN items i ON inv.item_id = i.item_id
                                    WHERE inv.player_id = ?
                                    ORDER BY total_value DESC
                                    LIMIT 5
                                ");
                                $stmt->execute([$playerId]);
                                $inventory = $stmt->fetchAll();
                                
                                if (count($inventory) > 0):
                                ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th>Type</th>
                                                <th>Quantity</th>
                                                <th>Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($inventory as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($item['type']); ?></span></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td><?php echo formatGold($item['total_value']); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <p class="text-muted">No inventory items found.</p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Recent Sessions -->
                            <div class="col-md-12">
                                <h6>Recent Sessions</h6>
                                <?php if (count($playerSessions) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Start Time</th>
                                                <th>End Time</th>
                                                <th>Duration</th>
                                                <th>Achievements</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Sort sessions by start time descending
                                            usort($playerSessions, function($a, $b) {
                                                return strtotime($b['start_time']) - strtotime($a['start_time']);
                                            });
                                            
                                            // Show only the 5 most recent
                                            $recentSessions = array_slice($playerSessions, 0, 5);
                                            
                                            foreach ($recentSessions as $session):
                                                $start = new DateTime($session['start_time']);
                                                $end = new DateTime($session['end_time']);
                                                $diff = $start->diff($end);
                                                $duration = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
                                                
                                                // Get achievements for this session
                                                $stmt = $pdo->prepare("
                                                    SELECT COUNT(*) as count 
                                                    FROM player_achievements 
                                                    WHERE session_id = ?
                                                ");
                                                $stmt->execute([$session['session_id']]);
                                                $achievementCount = $stmt->fetch()['count'];
                                            ?>
                                            <tr>
                                                <td><?php echo date('M d, Y g:i A', strtotime($session['start_time'])); ?></td>
                                                <td><?php echo date('M d, Y g:i A', strtotime($session['end_time'])); ?></td>
                                                <td><?php echo formatDuration($duration); ?></td>
                                                <td><span class="badge bg-primary"><?php echo $achievementCount; ?></span></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <p class="text-muted">No sessions found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Delete Player Modal Form -->
        <div class="modal fade" id="deletePlayerModal" tabindex="-1" aria-labelledby="deletePlayerModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deletePlayerModalLabel">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete player <?php echo htmlspecialchars($player['name']); ?>?</p>
                        <p class="text-danger"><strong>Warning:</strong> This action cannot be undone and will delete all related data including sessions, achievements, inventory, etc.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form method="POST" action="players.php">
                            <input type="hidden" name="player_id" value="<?php echo $playerId; ?>">
                            <button type="submit" name="delete_player" class="btn btn-danger">Delete Player</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Delete button (fixed at bottom-right) -->
        <div class="position-fixed bottom-0 end-0 p-3">
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deletePlayerModal">
                <i class="fas fa-trash-alt me-1"></i> Delete Player
            </button>
        </div>
        <?php endif; ?>
        
    <?php } ?>
    <?php else: ?>
        <!-- All Players List -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>All Players</h2>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5>Player List</h5>
                            <input type="text" id="playerSearch" class="form-control form-control-sm w-25" placeholder="Search...">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="playersTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Farm Name</th>
                                        <th>Gold Earned</th>
                                        <th>In-Game Days</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->query("
                                        SELECT p.player_id, p.name, p.avatar, p.farm_name, ps.total_gold_earned, ps.in_game_days
                                        FROM players p
                                        LEFT JOIN player_statistics ps ON p.player_id = ps.player_id
                                        ORDER BY p.name
                                    ");
                                    $players = $stmt->fetchAll();
                                    
                                    foreach ($players as $player):
                                    ?>
                                    <tr>
                                        <td><?php echo $player['player_id']; ?></td>
                                        <td><?php echo htmlspecialchars($player['name']); ?></td>
                                        <td><?php echo htmlspecialchars($player['farm_name']); ?></td>
                                        <td><?php echo isset($player['total_gold_earned']) ? formatGold($player['total_gold_earned']) : 'N/A'; ?></td>
                                        <td><?php echo isset($player['in_game_days']) ? $player['in_game_days'] : 'N/A'; ?></td>
                                        <td>
                                            <a href="players.php?id=<?php echo $player['player_id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="players.php?id=<?php echo $player['player_id']; ?>&action=edit" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
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
    <?php endif; ?>
</div>

<?php include 'components/footer.php'; ?>