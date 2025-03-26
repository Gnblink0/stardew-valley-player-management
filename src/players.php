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

<style>
.filter-group {
    margin-bottom: 15px;
}

.filter-group span {
    margin-right: 10px;
    font-weight: 500;
}

.btn-group .btn {
    margin-right: 5px;
}

.content-group {
    min-height: 200px;
}

.friendship-hearts {
    color: #ff6b6b;
}

.table th {
    background-color: #f8f9fa;
}
</style>

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
        
        <!-- 在 Statistics card 后面添加 -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Player Assets</h5>
                    </div>
                    <div class="card-body">
                        <!-- Filter Tabs -->
                        <div class="tabs mb-3">
                            <div class="btn-group" role="group">
                                <button class="btn btn-outline-primary active" data-tab="crops">Crops</button>
                                <button class="btn btn-outline-primary" data-tab="animals">Animals</button>
                                <button class="btn btn-outline-primary" data-tab="inventory">Inventory</button>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div id="filters-area" class="mb-3">
                            <!-- Crops Filter -->
                            <div class="filter-group" id="crops-filter">
                                <span>Season:</span>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary active" data-filter="all">All</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="spring">Spring</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="summer">Summer</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="fall">Fall</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="winter">Winter</button>
                                </div>
                            </div>

                            <!-- Animals Filter -->
                            <div class="filter-group" id="animals-filter" style="display:none">
                                <span>Type:</span>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-secondary active" data-filter="all">All</button>
                                    <button class="btn btn-sm btn-outline-secondary" data-filter="barn">Barn</button>
                                    <button class="btn btn-sm btn-outline-secondary" data-filter="coop">Coop</button>
                                </div>
                            </div>

                            <!-- Inventory Filter -->
                            <div class="filter-group" id="inventory-filter" style="display:none">
                                <span>Type:</span>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-secondary active" data-filter="all">All</button>
                                    <button class="btn btn-sm btn-outline-secondary" data-filter="tools">Tools</button>
                                    <button class="btn btn-sm btn-outline-secondary" data-filter="seeds">Seeds</button>
                                    <button class="btn btn-sm btn-outline-secondary" data-filter="vegetable">Vegetable</button>
                                    <button class="btn btn-sm btn-outline-secondary" data-filter="fruit">Fruit</button>
                                    <button class="btn btn-sm btn-outline-secondary" data-filter="animal-product">Animal Product</button>
                                </div>
                            </div>
                        </div>

                        <!-- Content Area -->
                        <div id="content-area">
                            <div id="crops-content" class="content-group">
                                <div class="table-responsive">
                                    <table class="table table-sm" id="crops-table">
                                        <thead>
                                            <tr>
                                                <th>Crop Name</th>
                                                <th>Season</th>
                                                <th>Harvested</th>
                                                <th>Sold</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="animals-content" class="content-group" style="display:none">
                                <div class="table-responsive">
                                    <table class="table table-sm" id="animals-table">
                                        <thead>
                                            <tr>
                                                <th>Animal Name</th>
                                                <th>Type</th>
                                                <th>Building</th>
                                                <th>Friendship</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="inventory-content" class="content-group" style="display:none">
                                <div class="table-responsive">
                                    <table class="table table-sm" id="inventory-table">
                                        <thead>
                                            <tr>
                                                <th>Item Name</th>
                                                <th>Type</th>
                                                <th>Quantity</th>
                                                <th>Value</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
                                        <th class="sortable" data-sort="id">ID <i class="fas fa-sort"></i></th>
                                        <th class="sortable" data-sort="name">Name <i class="fas fa-sort"></i></th>
                                        <th class="sortable" data-sort="farm_name">Farm Name <i class="fas fa-sort"></i></th>
                                        <th class="sortable" data-sort="gold">Gold Earned <i class="fas fa-sort"></i></th>
                                        <th class="sortable" data-sort="days">In-Game Days <i class="fas fa-sort"></i></th>
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
                                        <td data-value="<?php echo isset($player['total_gold_earned']) ? $player['total_gold_earned'] : 0; ?>">
                                            <?php echo isset($player['total_gold_earned']) ? formatGold($player['total_gold_earned']) : 'N/A'; ?>
                                        </td>
                                        <td data-value="<?php echo isset($player['in_game_days']) ? $player['in_game_days'] : 0; ?>">
                                            <?php echo isset($player['in_game_days']) ? $player['in_game_days'] : 'N/A'; ?>
                                        </td>
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

        <style>
            .sortable {
                cursor: pointer;
            }
            .sortable i {
                margin-left: 5px;
            }
            .sortable.asc i:before {
                content: "\f0de" !important;
            }
            .sortable.desc i:before {
                content: "\f0dd" !important;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const table = document.getElementById('playersTable');
                const headers = table.querySelectorAll('th.sortable');
                
                headers.forEach(header => {
                    header.addEventListener('click', () => {
                        const sortBy = header.getAttribute('data-sort');
                        const tbody = table.querySelector('tbody');
                        const rows = Array.from(tbody.querySelectorAll('tr'));
                        
                        // Remove existing sort classes
                        headers.forEach(h => h.classList.remove('asc', 'desc'));
                        
                        // Determine sort direction
                        const isAsc = !header.classList.contains('asc');
                        header.classList.toggle('asc', isAsc);
                        header.classList.toggle('desc', !isAsc);
                        
                        // Sort rows
                        rows.sort((a, b) => {
                            let aValue, bValue;
                            
                            switch(sortBy) {
                                case 'id':
                                    aValue = parseInt(a.cells[0].textContent);
                                    bValue = parseInt(b.cells[0].textContent);
                                    break;
                                case 'name':
                                case 'farm_name':
                                    const columnIndex = sortBy === 'name' ? 1 : 2;
                                    aValue = a.cells[columnIndex].textContent.trim().toLowerCase();
                                    bValue = b.cells[columnIndex].textContent.trim().toLowerCase();
                                    break;
                                case 'gold':
                                    aValue = parseInt(a.cells[3].getAttribute('data-value')) || 0;
                                    bValue = parseInt(b.cells[3].getAttribute('data-value')) || 0;
                                    break;
                                case 'days':
                                    aValue = parseInt(a.cells[4].getAttribute('data-value')) || 0;
                                    bValue = parseInt(b.cells[4].getAttribute('data-value')) || 0;
                                    break;
                            }
                            
                            if (aValue < bValue) return isAsc ? -1 : 1;
                            if (aValue > bValue) return isAsc ? 1 : -1;
                            return 0;
                        });
                        
                        // Reorder rows in the table
                        rows.forEach(row => tbody.appendChild(row));
                    });
                });
            });
        </script>
    <?php endif; ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const playerId = <?php echo $playerId; ?>;
    if (!playerId) return;

    // Tab 切换
    document.querySelectorAll('[data-tab]').forEach((btn) => {
        btn.addEventListener("click", function () {
            document.querySelectorAll('[data-tab]').forEach((b) => b.classList.remove("active"));
            btn.classList.add("active");

            document.querySelectorAll(".content-group, .filter-group").forEach((el) => el.style.display = "none");

            const tab = btn.dataset.tab;
            document.getElementById(`${tab}-content`).style.display = "block";
            document.getElementById(`${tab}-filter`).style.display = "block";

            loadTabData(tab);
        });
    });

    // 各类 filter 按钮绑定
    bindFilterButtons("crops", filterCropsBySeason);
    bindFilterButtons("animals", filterAnimalsByType);
    bindFilterButtons("inventory", filterInventoryByType);

    function bindFilterButtons(type, filterFn) {
        console.log(`Binding filter buttons for ${type}`);
        document.querySelectorAll(`#${type}-filter button`).forEach((btn) => {
            btn.addEventListener("click", function() {
                console.log(`Filter button clicked for ${type}:`, btn.dataset.filter);
                document.querySelectorAll(`#${type}-filter button`).forEach((b) => b.classList.remove("active"));
                btn.classList.add("active");
                filterFn(btn.dataset.filter);
            });
        });
    }

    function loadTabData(tabName) {
        const activeFilter = document.querySelector(`#${tabName}-filter button.active`);
        const filterValue = activeFilter ? activeFilter.dataset.filter : "all";
        console.log(`Loading ${tabName} data with filter:`, filterValue);
        
        const url = `/stardew-valley-player-management/src/api/${tabName}.php?player_id=${playerId}&filter=${filterValue}`;
        console.log('Fetching URL:', url);

        fetch(url)
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log(`Received ${tabName} data:`, data);
                if (data.status === 'success') {
                    switch (tabName) {
                        case "crops":
                            renderCropTable(data.data);
                            break;
                        case "animals":
                            renderAnimalTable(data.data);
                            break;
                        case "inventory":
                            renderInventoryTable(data.data);
                            break;
                    }
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                console.error(`Error loading ${tabName} data:`, error);
                const tbody = document.querySelector(`#${tabName}-table tbody`);
                tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger">Error loading data: ${error.message}</td></tr>`;
            });
    }

    function renderCropTable(crops) {
        const tbody = document.querySelector("#crops-table tbody");
        tbody.innerHTML = "";
        if (crops.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center">No crops found</td></tr>`;
            return;
        }
        crops.forEach((crop) => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${crop.name || ''}</td>
                <td>${crop.season || ''}</td>
                <td>${crop.harvested || 0}</td>
                <td>${crop.sold || 0}</td>`;
            tbody.appendChild(row);
        });
    }

    function filterCropsBySeason(season) {
        loadTabData("crops");
    }

    function renderAnimalTable(animals) {
        const tbody = document.querySelector("#animals-table tbody");
        tbody.innerHTML = "";
        if (animals.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center">No animals found</td></tr>`;
            return;
        }
        animals.forEach((animal) => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${animal.name || ''}</td>
                <td>${animal.type || ''}</td>
                <td>${animal.building || ''}</td>
                <td>${"❤️".repeat(parseInt(animal.friendship_level) || 0)}</td>`;
            tbody.appendChild(row);
        });
    }

    function filterAnimalsByType(type) {
        loadTabData("animals");
    }

    function renderInventoryTable(items) {
        const tbody = document.querySelector("#inventory-table tbody");
        tbody.innerHTML = "";
        if (items.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center">No items found</td></tr>`;
            return;
        }
        items.forEach((item) => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${item.name || ''}</td>
                <td>${item.type || ''}</td>
                <td>${item.quantity || 0}</td>
                <td>${item.value || 0}g</td>`;
            tbody.appendChild(row);
        });
    }

    function filterInventoryByType(type) {
        loadTabData("inventory");
    }

    // 初始默认加载 crops tab
    document.querySelector('[data-tab="crops"]').click();
});
</script>

<?php include 'components/footer.php'; ?>