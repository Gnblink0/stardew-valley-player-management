<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get all players for the filter
$stmt = $pdo->query("SELECT player_id, name FROM players ORDER BY name");
$players = $stmt->fetchAll();

// Get all weeks from sessions
$stmt = $pdo->query("
    SELECT DISTINCT 
        DATE_FORMAT(start_time, '%Y-%u') as week_key,
        DATE_FORMAT(start_time, '%Y Week %u') as week_label
    FROM game_sessions 
    ORDER BY week_key DESC
");
$available_weeks = $stmt->fetchAll();

include 'components/header.php';
?>

<div class="row">
    <!-- Sidebar -->
    <?php include 'components/sidebar.php'; ?>
    
    <!-- Main content -->
    <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Weekly Playtime Analysis</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="refresh-data">
                    <i class="fas fa-sync-alt"></i> Refresh Data
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-filter me-2"></i>Filters
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="weekSelect" class="form-label">Select Week</label>
                        <select class="form-select" id="weekSelect">
                            <option value="all">All Weeks</option>
                            <?php foreach ($available_weeks as $week): ?>
                            <option value="<?php echo $week['week_key']; ?>">
                                <?php echo $week['week_label']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Select Players</label>
                        <div class="player-checkboxes border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            <div class="mb-2">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                                <label class="form-check-label" for="selectAll">
                                    <strong>Select All Players</strong>
                                </label>
                            </div>
                            <hr class="my-2">
                            <?php foreach ($players as $player): ?>
                            <div class="form-check">
                                <input class="form-check-input player-checkbox" type="checkbox" 
                                       value="<?php echo $player['player_id']; ?>" 
                                       id="player<?php echo $player['player_id']; ?>" checked>
                                <label class="form-check-label" for="player<?php echo $player['player_id']; ?>">
                                    <?php echo htmlspecialchars($player['name']); ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Playtime Chart -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Weekly Playtime Distribution
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height:50vh;">
                            <canvas id="playtimeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Data Table -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-table me-2"></i>Detailed Playtime Data
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="playtimeTable">
                                <thead>
                                    <tr>
                                        <th>Week</th>
                                        <th>Player</th>
                                        <th>Total Playtime</th>
                                        <th>Sessions</th>
                                        <th>Average Session</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let playtimeChart = null;
    const weekSelect = document.getElementById('weekSelect');
    const playerCheckboxes = document.querySelectorAll('.player-checkbox');
    const selectAll = document.getElementById('selectAll');
    const refreshButton = document.getElementById('refresh-data');

    // Initialize select all checkbox
    selectAll.checked = true;
    selectAll.addEventListener('change', function() {
        playerCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        fetchData();
    });

    // Add event listeners
    weekSelect.addEventListener('change', fetchData);
    playerCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            selectAll.checked = Array.from(playerCheckboxes).every(cb => cb.checked);
            fetchData();
        });
    });
    refreshButton.addEventListener('click', fetchData);

    function fetchData() {
        const selectedWeek = weekSelect.value;
        const selectedPlayers = Array.from(playerCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        // Show loading states
        document.getElementById('playtimeTable').querySelector('tbody').innerHTML = `
            <tr>
                <td colspan="5" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mb-0 mt-2">Loading data...</p>
                </td>
            </tr>
        `;

        fetch('api/weekly_playtime.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                week: selectedWeek,
                players: selectedPlayers
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                updateChart(data.chart_data);
                updateTable(data.table_data);
            } else {
                throw new Error(data.message || 'Failed to load data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('playtimeTable').querySelector('tbody').innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Error loading data. Please try again.
                    </td>
                </tr>
            `;
        });
    }

    function updateChart(chartData) {
        const ctx = document.getElementById('playtimeChart').getContext('2d');
        
        if (playtimeChart) {
            playtimeChart.destroy();
        }

        playtimeChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: chartData.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Playtime (hours)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Week'
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
                        text: 'Weekly Playtime Distribution'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.raw} hours`;
                            }
                        }
                    }
                }
            }
        });
    }

    function updateTable(tableData) {
        const tbody = document.getElementById('playtimeTable').querySelector('tbody');
        tbody.innerHTML = '';

        if (tableData.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center">
                        <i class="fas fa-info-circle me-2"></i>
                        No data available for the selected filters
                    </td>
                </tr>
            `;
            return;
        }

        tableData.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${row.week}</td>
                <td>${row.player_name}</td>
                <td>${formatDuration(row.total_playtime)}</td>
                <td>${row.session_count}</td>
                <td>${formatDuration(row.avg_session)}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function formatDuration(minutes) {
        const hours = Math.floor(minutes / 60);
        const mins = Math.round(minutes % 60);
        return `${hours}h ${mins}m`;
    }

    // Initial data load
    fetchData();
});
</script>

<?php include 'components/footer.php'; ?> 