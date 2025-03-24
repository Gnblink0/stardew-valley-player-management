document.addEventListener('DOMContentLoaded', function() {
    // Fetch player data (in a real app, this would come from an API/backend)
    fetchPlayerData().then(playerData => {
        try {
            populatePlayerInfo(playerData);
            populatePlayerStats(playerData);
            
            // Only set up weekly playtime if the element exists
            if (document.getElementById('weekly-playtime-chart')) {
                populateWeeklyPlaytime(playerData.weeklyPlaytime);
                
                // Set up weekly playtime selector (only if it exists)
                const weekSelector = document.getElementById('week-selector');
                if (weekSelector) {
                    weekSelector.addEventListener('change', function(e) {
                        const weekNumber = parseInt(e.target.value);
                        // Fetch data for the selected week and update the chart
                        fetchWeeklyPlaytime(weekNumber).then(weeklyData => {
                            updateWeeklyPlaytimeChart(weeklyData);
                            // Calculate and display average instead of showing the table
                            displayWeeklyAverage(weeklyData);
                        });
                    });
                }
            }
            
            populateGameSessions(playerData.gameSessions);
            populateAchievements(playerData.achievements);
        } catch (err) {
            console.error('Error populating data:', err);
        }
    }).catch(error => {
        console.error('Error loading player data:', error);
    });
    
    // Set up event listeners (only if elements exist)
    const dashboardBtn = document.getElementById('dashboard-btn');
    if (dashboardBtn) {
        dashboardBtn.addEventListener('click', () => {
            window.location.href = '../dashboard/data.html';
        });
    }
    
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            // In a real app, this would call a logout API
            localStorage.removeItem('playerToken');
            window.location.href = '../login/index.html';
        });
    }
});

// For demo purposes, return mock data
// In a real application, this would fetch data from a server
function fetchPlayerData() {
    return new Promise(resolve => {
        setTimeout(() => {
            resolve({
                name: "Renie",
                farmName: "Sunset Valley Farm",
                avatarUrl: "./assets/images/farmer_avatar.png",
                totalGoldEarned: 2500000,
                daysPlayed: 156,
                achievementsCompleted: 15,
                totalAchievements: 30,
                averagePlaytime: 75,
                weeklyPlaytime: generateWeeklyPlaytimeData(),
                gameSessions: generateGameSessions(),
                achievements: generateAchievements()
            });
        }, 500);
    });
}

function populatePlayerInfo(playerData) {
    const playerNameEl = document.getElementById('player-name');
    const farmNameEl = document.getElementById('farm-name');
    const avatarEl = document.getElementById('player-avatar-img');
    
    if (playerNameEl) playerNameEl.textContent = playerData.name;
    if (farmNameEl) farmNameEl.textContent = playerData.farmName;
    if (avatarEl) avatarEl.src = playerData.avatarUrl;
}

function populatePlayerStats(playerData) {
    // Use safe element access with null checks
    safeSetTextContent('stat-gold', formatNumber(playerData.totalGoldEarned) + 'g');
    safeSetTextContent('stat-days', playerData.daysPlayed);
    safeSetTextContent('stat-achievements', playerData.achievementsCompleted);
    safeSetTextContent('stat-average-playtime', playerData.averagePlaytime + ' mins');
}

// Helper function to safely set text content
function safeSetTextContent(elementId, content) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = content;
    }
}

// Weekly Playtime Functions
function populateWeeklyPlaytime(weeklyData) {
    // Populate week selector
    const weekSelector = document.getElementById('week-selector');
    if (!weekSelector) return;
    
    // Clear existing options
    weekSelector.innerHTML = '';
    
    for (let i = 1; i <= 4; i++) {
        const option = document.createElement('option');
        option.value = i;
        option.textContent = `Week ${i}`;
        weekSelector.appendChild(option);
    }
    
    // Set the first week as default and show its data
    updateWeeklyPlaytimeChart(weeklyData);
    // Calculate and display average
    displayWeeklyAverage(weeklyData);
    
    // Hide the table container, as we're not using it
    const tableContainer = document.querySelector('.weekly-playtime-table-container');
    if (tableContainer) {
        tableContainer.style.display = 'none';
    }
    
    // Create a centered average display if it doesn't exist
    createWeeklyAverageDisplay();
}

function createWeeklyAverageDisplay() {
    // Check if the element already exists
    if (document.getElementById('weekly-average-display')) return;
    
    const chartContainer = document.querySelector('.weekly-playtime-chart');
    if (!chartContainer) return;
    
    // Create a container for the average display
    const averageContainer = document.createElement('div');
    averageContainer.id = 'weekly-average-display';
    averageContainer.className = 'weekly-average-container';
    averageContainer.style.textAlign = 'center';
    averageContainer.style.margin = '20px 0';
    averageContainer.style.fontSize = '18px';
    averageContainer.style.fontWeight = 'bold';
    
    // Create the label
    const averageLabel = document.createElement('div');
    averageLabel.textContent = 'Average game time this week';
    averageLabel.style.marginBottom = '10px';
    averageContainer.appendChild(averageLabel);
    
    // Create the value display
    const averageValue = document.createElement('div');
    averageValue.id = 'weekly-average-value';
    averageValue.textContent = '0 mins';
    averageValue.style.fontSize = '24px';
    averageValue.style.color = '#5E9641';
    averageContainer.appendChild(averageValue);
    
    // Insert after the chart
    chartContainer.parentNode.insertBefore(averageContainer, chartContainer.nextSibling);
}

function displayWeeklyAverage(weeklyData) {
    // Calculate average playtime
    const total = weeklyData.reduce((sum, day) => sum + day.minutes, 0);
    const average = Math.round(total / weeklyData.length);
    
    // Display the average
    const averageElement = document.getElementById('weekly-average-value');
    if (averageElement) {
        averageElement.textContent = `${average} mins`;
    }
}

function updateWeeklyPlaytimeChart(weeklyData) {
    const chartCanvas = document.getElementById('weekly-playtime-chart');
    if (!chartCanvas) return;
    
    const ctx = chartCanvas.getContext('2d');
    
    // If chart already exists, destroy it before creating a new one
    if (window.weeklyChart) {
        window.weeklyChart.destroy();
    }
    
    window.weeklyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Playtime (minutes)',
                data: weeklyData.map(day => day.minutes),
                backgroundColor: '#5E9641',
                borderColor: '#4A7834',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Minutes'
                    }
                }
            }
        }
    });
}

// Game Sessions Functions
function populateGameSessions(sessions) {
    const sessionsContainer = document.getElementById('sessions-container');
    const template = document.getElementById('session-item-template');
    
    if (!sessionsContainer || !template) return;
    
    // Clear existing content
    sessionsContainer.innerHTML = '';
    
    // Add session items
    sessions.forEach(session => {
        const sessionElement = template.content.cloneNode(true);
        
        sessionElement.querySelector('.session-item').dataset.sessionId = session.id;
        sessionElement.querySelector('.session-date').textContent = session.date;
        sessionElement.querySelector('.session-duration span').textContent = session.duration + ' mins';
        sessionElement.querySelector('.session-achievements span').textContent = session.achievements;
        
        sessionsContainer.appendChild(sessionElement);
    });
}

// Achievements Functions
function populateAchievements(achievements) {
    const achievementsContainer = document.getElementById('achievements-container');
    const template = document.getElementById('achievement-card-template');
    
    if (!achievementsContainer || !template) return;
    
    // Clear existing content
    achievementsContainer.innerHTML = '';
    
    // Calculate overall progress
    const completedCount = achievements.filter(a => a.completed).length;
    const overallProgress = (completedCount / achievements.length) * 100;
    
    // Update progress bar and text
    const progressBar = document.getElementById('overall-achievement-progress');
    const progressText = document.getElementById('achievement-progress-text');
    
    if (progressBar) progressBar.style.width = overallProgress + '%';
    if (progressText) progressText.textContent = Math.round(overallProgress) + '%';
    
    // Add achievement cards
    achievements.forEach(achievement => {
        const achievementElement = template.content.cloneNode(true);
        const card = achievementElement.querySelector('.achievement-card');
        
        card.dataset.achievementId = achievement.id;
        card.querySelector('.achievement-name').textContent = achievement.name;
        card.querySelector('.achievement-goal').textContent = achievement.description;
        
        // Update image paths to use relative paths and handle potential missing images
        const imgElement = card.querySelector('.achievement-icon img');
        imgElement.src = achievement.iconUrl;
        imgElement.alt = achievement.name;
        imgElement.onerror = function() {
            this.src = './assets/images/default_achievement.png';
        };
        
        // Set status indicator
        const statusElement = card.querySelector('.achievement-status');
        if (achievement.completed) {
            statusElement.classList.add('completed');
            statusElement.title = 'Completed';
        } else {
            statusElement.classList.add('in-progress');
            statusElement.title = 'In Progress';
        }
        
        // Set progress bar
        const progressBar = card.querySelector('.achievement-progress-bar');
        progressBar.style.width = achievement.progress + '%';
        
        achievementsContainer.appendChild(achievementElement);
    });
}

// Helper Functions
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Generate mock data for demo
function generateWeeklyPlaytimeData() {
    const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    return days.map(day => {
        return {
            day: day,
            minutes: Math.floor(Math.random() * 120) + 15 // Random playtime between 15-135 minutes
        };
    });
}

function generateGameSessions() {
    const sessions = [];
    const dates = [
        'Mar 20, 2025', 'Mar 18, 2025', 'Mar 15, 2025', 
        'Mar 12, 2025', 'Mar 10, 2025'
    ];
    
    for (let i = 0; i < 5; i++) {
        sessions.push({
            id: i + 1,
            date: dates[i],
            duration: Math.floor(Math.random() * 120) + 30, // 30-150 minutes
            achievements: Math.floor(Math.random() * 3) // 0-2 achievements
        });
    }
    
    return sessions;
}

function generateAchievements() {
    const achievements = [
        {
            id: 1,
            name: "Greenhorn",
            description: "Earn 15,000g",
            iconUrl: "./assets/images/achievements/gold.png",
            completed: true,
            progress: 100
        },
        {
            id: 2,
            name: "Cowpoke",
            description: "Ship 300 items",
            iconUrl: "./assets/images/achievements/shipping.png",
            completed: true,
            progress: 100
        },
        {
            id: 3,
            name: "Homesteader",
            description: "Ship 10 of each crop",
            iconUrl: "./assets/images/achievements/crops.png",
            completed: false,
            progress: 70
        },
        {
            id: 4,
            name: "Angler",
            description: "Catch 24 different fish",
            iconUrl: "./assets/images/achievements/fishing.png",
            completed: false,
            progress: 50
        },
        {
            id: 5,
            name: "Artificer",
            description: "Craft 30 different items",
            iconUrl: "./assets/images/achievements/crafting.png",
            completed: true,
            progress: 100
        },
        {
            id: 6,
            name: "Artisan",
            description: "Ship 30 artisan goods",
            iconUrl: "./assets/images/achievements/artisan.png",
            completed: false,
            progress: 30
        }
    ];
    
    return achievements;
}

// Function to fetch weekly playtime data for a specific week
// In a real app, this would fetch from a backend
function fetchWeeklyPlaytime(weekNumber) {
    return new Promise(resolve => {
        setTimeout(() => {
            // Generate mock data for the selected week
            resolve(generateWeeklyPlaytimeData());
        }, 300);
    });
}