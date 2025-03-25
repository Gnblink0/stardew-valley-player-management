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

// 获取所有成就和统计信息
$achievementStats = getAchievementStats();

// 获取成就大师（拥有最多成就的玩家）
$achievementMaster = getAchievementMaster();

include 'components/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Achievements</h1>
        <div class="d-flex">
            <div class="me-3">
                <select class="form-select" id="categoryFilter">
                    <option value="">All Categories</option>
                    <option value="Farming">Farming</option>
                    <option value="Mining">Mining</option>
                    <option value="Fishing">Fishing</option>
                    <option value="Social">Social</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div>
                <select class="form-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="Completed">Completed</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Not Started">Not Started</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Achievement Master Card -->
    <div class="card mb-4">
        <div class="card-body text-center">
            <i class="fas fa-trophy fa-3x text-warning mb-3"></i>
            <h2 class="card-title">Achievement Master</h2>
            <?php if ($achievementMaster): ?>
            <p class="lead"><?php echo htmlspecialchars($achievementMaster['name']); ?> - <?php echo htmlspecialchars($achievementMaster['farm_name']); ?></p>
            <p class="text-muted"><?php echo $achievementMaster['achievement_count']; ?> Achievements Completed</p>
            <?php else: ?>
            <p class="text-muted">No data available</p>
            <?php endif; ?>
            <button class="btn btn-success" onclick="location.reload()">View Achievements</button>
        </div>
    </div>

    <!-- Achievement Progress -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Overall Progress</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Achievements</span>
                        <span class="fw-bold"><?php echo $achievementStats['total_achievements']; ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Completed</span>
                        <span class="text-success"><?php echo $achievementStats['total_completed']; ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Completion Rate</span>
                        <span class="text-primary"><?php echo $achievementStats['completion_rate']; ?>%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: <?php echo $achievementStats['completion_rate']; ?>%"
                             aria-valuenow="<?php echo $achievementStats['completion_rate']; ?>" 
                             aria-valuemin="0" aria-valuemax="100">
                            <?php echo $achievementStats['completion_rate']; ?>%
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Unlocks</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php foreach ($achievementStats['recent_achievements'] as $achievement): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($achievement['achievement_name']); ?></h6>
                                    <small class="text-muted">Unlocked by <?php echo htmlspecialchars($achievement['player_name']); ?></small>
                                </div>
                                <small class="text-muted"><?php echo date('M d, Y', strtotime($achievement['completion_date'])); ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Achievements List -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">All Achievements</h5>
        </div>
        <div class="card-body">
            <div class="row" id="achievements-grid">
                <?php foreach ($achievements as $achievement): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($achievement['name']); ?></h5>
                                <span class="badge bg-<?php echo getStatusBadgeClass($achievement['status']); ?>">
                                    <?php echo $achievement['status']; ?>
                                </span>
                            </div>
                            <p class="card-text text-muted mb-3"><?php echo htmlspecialchars($achievement['description']); ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-secondary"><?php echo $achievement['category']; ?></span>
                                <?php if ($achievement['reward']): ?>
                                <span class="text-warning">
                                    <i class="fas fa-coins"></i> <?php echo formatGold($achievement['reward']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <?php if ($achievement['progress']): ?>
                            <div class="mt-3">
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: <?php echo $achievement['progress']; ?>%"
                                         aria-valuenow="<?php echo $achievement['progress']; ?>" 
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted"><?php echo $achievement['progress']; ?>% Complete</small>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    const achievementsGrid = document.getElementById('achievements-grid');

    function filterAchievements() {
        const category = categoryFilter.value;
        const status = statusFilter.value;
        
        const cards = achievementsGrid.getElementsByClassName('col-md-6');
        
        Array.from(cards).forEach(card => {
            const cardCategory = card.querySelector('.badge.bg-secondary').textContent;
            const cardStatus = card.querySelector('.badge:not(.bg-secondary)').textContent;
            
            const categoryMatch = !category || cardCategory === category;
            const statusMatch = !status || cardStatus === status;
            
            card.style.display = categoryMatch && statusMatch ? '' : 'none';
        });
    }

    categoryFilter.addEventListener('change', filterAchievements);
    statusFilter.addEventListener('change', filterAchievements);
});
</script>

<?php include 'components/footer.php'; ?>
