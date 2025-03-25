document.addEventListener("DOMContentLoaded", function () {
  // 获取URL参数中的玩家ID
  const urlParams = new URLSearchParams(window.location.search);
  const playerId = urlParams.get("id");

  if (!playerId) {
    alert("No player ID provided!");
    window.location.href = "player_select.html";
    return;
  }

  // 全局变量，用于存储玩家数据
  let playerData = null;

  // 获取DOM元素
  const editProfileBtn = document.getElementById("edit-profile-btn");
  const saveProfileBtn = document.getElementById("save-profile-btn");
  const cancelEditBtn = document.getElementById("cancel-edit-btn");
  const backBtn = document.getElementById("back-btn");
  const editProfileForm = document.getElementById("edit-profile-form");
  const viewDataBtn = document.getElementById("view-data-btn");

  // 添加调试语句
  console.log("Edit Profile Button:", editProfileBtn);
  console.log("Save Profile Button:", saveProfileBtn);
  console.log("Cancel Edit Button:", cancelEditBtn);
  console.log("Back Button:", backBtn);
  console.log("Edit Profile Form:", editProfileForm);
  console.log("View Farm Data Button:", viewDataBtn);

  // 绑定按钮点击事件
  if (editProfileBtn) {
    editProfileBtn.addEventListener("click", function () {
      console.log("Edit Profile button clicked");

      // 检查playerData是否已加载
      if (!playerData) {
        console.error("Player data not loaded yet");
        alert("Please wait for player data to load");
        return;
      }

      // 显示编辑表单
      if (editProfileForm) {
        editProfileForm.style.display = "block";

        // 填充表单字段
        document.getElementById("edit-player-name").value = playerData.name;
        document.getElementById("edit-farm-name").value =
          playerData.farm_name || "";
      } else {
        console.error("Edit profile form not found");
      }
    });
  } else {
    console.error("Edit profile button not found");
  }

  if (saveProfileBtn) {
    saveProfileBtn.addEventListener("click", function () {
      console.log("Save Profile button clicked");
      // 获取表单数据
      const playerName = document.getElementById("edit-player-name").value;
      const farmName = document.getElementById("edit-farm-name").value;

      // 验证数据
      if (!playerName.trim()) {
        alert("Player name cannot be empty!");
        return;
      }

      // 准备要发送的数据
      const formData = new FormData();
      formData.append("id", playerId);
      formData.append("name", playerName);
      formData.append("farm_name", farmName);

      // 发送更新请求
      fetch("../../api/update_player.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.status === "success") {
            alert("Profile updated successfully!");
            // 隐藏编辑表单
            editProfileForm.style.display = "none";
            // 刷新页面以显示更新后的数据
            location.reload();
          } else {
            alert("Failed to update profile: " + data.message);
          }
        })
        .catch((error) => {
          console.error("Error updating profile:", error);
          alert("An error occurred while updating the profile.");
        });
    });
  }

  if (cancelEditBtn) {
    cancelEditBtn.addEventListener("click", function () {
      console.log("Cancel Edit button clicked");
      // 隐藏编辑表单
      editProfileForm.style.display = "none";
    });
  }

  if (backBtn) {
    backBtn.addEventListener("click", function () {
      console.log("Back button clicked");
      window.location.href = "player_select.html";
    });
  }

  if (viewDataBtn) {
    viewDataBtn.addEventListener("click", function () {
      console.log("View Farm Data button clicked");

      // 获取当前URL中的玩家ID
      const urlParams = new URLSearchParams(window.location.search);
      const playerId = urlParams.get("id");

      // 跳转到data.html页面，并传递玩家ID
      if (playerId) {
        window.location.href = `data.html?id=${playerId}`;
      } else {
        console.error("Player ID not found");
        alert("Cannot navigate to farm data: Player ID not found");
      }
    });
  } else {
    console.error("View Farm Data button not found");
  }

  // 获取玩家数据
  fetchPlayerData();

  // 获取玩家数据的函数
  function fetchPlayerData() {
    fetch(`../../api/player.php?id=${playerId}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.status === "success") {
          playerData = data.data;
          console.log("Player data received:", playerData);

          // 填充页面数据
          populatePlayerInfo(playerData);
          populatePlayerStats(playerData);
          populateGameSessions(playerData.gameSessions);
          populateAchievements(playerData.achievements);

          // 如果有每周游戏时间数据，则填充
          if (playerData.weeklyPlaytime) {
            populateWeeklyPlaytime(playerData.weeklyPlaytime);
          }
        } else {
          console.error("Error fetching player data:", data.message);
          alert("Failed to load player data: " + data.message);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("An error occurred while loading player data.");
      });
  }

  // Fetch player data
  fetchPlayerData()
    .then((playerData) => {
      try {
        populatePlayerInfo(playerData);
        populatePlayerStats(playerData);

        // Only set up weekly playtime if the element exists
        if (document.getElementById("weekly-playtime-chart")) {
          populateWeeklyPlaytime(playerData.weeklyPlaytime);

          // 设置周选择器（如果存在）
          const weekSelector = document.getElementById("week-selector");
          if (weekSelector) {
            weekSelector.addEventListener("change", function (e) {
              const weekNumber = parseInt(e.target.value);
              // 获取所选周的数据并更新图表
              fetchWeeklyPlaytime(weekNumber).then((weeklyData) => {
                updateWeeklyPlaytimeChart(weeklyData);
                // 计算并显示平均值而不是显示表格
                displayWeeklyAverage(weeklyData);
              });
            });
          }
        }

        // 填充游戏会话数据
        populateGameSessions(playerData.gameSessions);

        // 填充成就数据 - 确保使用正确的属性名
        if (playerData.achievements) {
          console.log("Populating achievements:", playerData.achievements);
          populateAchievements(playerData.achievements);
        } else {
          console.warn("No achievements data found in player data");
        }
      } catch (err) {
        console.error("Error populating data:", err);
      }
    })
    .catch((error) => {
      console.error("Error loading player data:", error);
    });
});

// For demo purposes, return mock data
// In a real application, this would fetch data from a server
function fetchPlayerData() {
  const urlParams = new URLSearchParams(window.location.search);
  const playerId = urlParams.get("id");

  if (!playerId) {
    console.error("No player ID provided in URL");
    return Promise.reject("No player ID provided");
  }

  return fetch(`../../api/player.php?id=${playerId}`)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      return response.json();
    })
    .then((data) => {
      if (data.status === "success") {
        return data.data;
      } else {
        throw new Error(data.message || "Failed to fetch player data");
      }
    });
}

function populatePlayerInfo(playerData) {
  const playerNameEl = document.getElementById("player-name");
  const farmNameEl = document.getElementById("farm-name");

  if (playerNameEl) playerNameEl.textContent = playerData.name;
  if (farmNameEl)
    farmNameEl.textContent = playerData.farm_name || "Unknown Farm";
}

function populatePlayerStats(playerData) {
  // 获取统计数据元素
  const goldElement = document.getElementById("stat-gold");
  const daysElement = document.getElementById("stat-days");
  const achievementsElement = document.getElementById("stat-achievements");
  const playtimeElement = document.getElementById("stat-average-playtime");

  // 安全地设置文本内容，确保元素存在
  if (goldElement) {
    goldElement.textContent =
      formatNumber(playerData.total_gold_earned || 0) + "g";
  }

  if (daysElement) {
    daysElement.textContent = playerData.in_game_days || 0;
  }

  // 处理成就数据 - 检查所有可能的字段名
  if (achievementsElement) {
    // 打印整个 playerData 对象，查看所有可用字段
    console.log("Complete player data:", playerData);

    // 尝试从不同可能的字段名获取成就数据
    let achievements = 0;

    if (playerData.achievements_completed !== undefined) {
      achievements = playerData.achievements_completed;
    } else if (playerData.achievements_count !== undefined) {
      achievements = playerData.achievements_count;
    } else if (playerData.completed_achievements !== undefined) {
      achievements = playerData.completed_achievements;
    } else if (playerData.achievements !== undefined) {
      // 如果 achievements 是一个数组，计算其长度
      if (Array.isArray(playerData.achievements)) {
        achievements = playerData.achievements.length;
      } else {
        achievements = playerData.achievements;
      }
    }

    achievementsElement.textContent = achievements;
    console.log("Setting achievements to:", achievements);
  }

  // 处理平均游戏时间数据
  if (playtimeElement) {
    // 尝试从不同可能的字段名获取平均游戏时间数据
    const playtime =
      playerData.average_playtime ||
      playerData.avg_playtime ||
      playerData.playtime_average ||
      0;

    playtimeElement.textContent = playtime + " mins";
  }
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
  const weekSelector = document.getElementById("week-selector");
  if (!weekSelector) return;

  // Clear existing options
  weekSelector.innerHTML = "";

  for (let i = 1; i <= 4; i++) {
    const option = document.createElement("option");
    option.value = i;
    option.textContent = `Week ${i}`;
    weekSelector.appendChild(option);
  }

  // Set the first week as default and show its data
  updateWeeklyPlaytimeChart(weeklyData);
  // Calculate and display average
  displayWeeklyAverage(weeklyData);

  // Hide the table container, as we're not using it
  const tableContainer = document.querySelector(
    ".weekly-playtime-table-container"
  );
  if (tableContainer) {
    tableContainer.style.display = "none";
  }

  // Create a centered average display if it doesn't exist
  createWeeklyAverageDisplay();
}

function createWeeklyAverageDisplay() {
  // Check if the element already exists
  if (document.getElementById("weekly-average-display")) return;

  const chartContainer = document.querySelector(".weekly-playtime-chart");
  if (!chartContainer) return;

  // Create a container for the average display
  const averageContainer = document.createElement("div");
  averageContainer.id = "weekly-average-display";
  averageContainer.className = "weekly-average-container";
  averageContainer.style.textAlign = "center";
  averageContainer.style.margin = "20px 0";
  averageContainer.style.fontSize = "18px";
  averageContainer.style.fontWeight = "bold";

  // Create the label
  const averageLabel = document.createElement("div");
  averageLabel.textContent = "Average game time this week";
  averageLabel.style.marginBottom = "10px";
  averageContainer.appendChild(averageLabel);

  // Create the value display
  const averageValue = document.createElement("div");
  averageValue.id = "weekly-average-value";
  averageValue.textContent = "0 mins";
  averageValue.style.fontSize = "24px";
  averageValue.style.color = "#5E9641";
  averageContainer.appendChild(averageValue);

  // Insert after the chart
  chartContainer.parentNode.insertBefore(
    averageContainer,
    chartContainer.nextSibling
  );
}

function displayWeeklyAverage(weeklyData) {
  // Calculate average playtime
  const total = weeklyData.reduce((sum, day) => sum + day.minutes, 0);
  const average = Math.round(total / weeklyData.length);

  // Display the average
  const averageElement = document.getElementById("weekly-average-value");
  if (averageElement) {
    averageElement.textContent = `${average} mins`;
  }
}

function updateWeeklyPlaytimeChart(weeklyData) {
  const chartCanvas = document.getElementById("weekly-playtime-chart");
  if (!chartCanvas) return;

  const ctx = chartCanvas.getContext("2d");

  // If chart already exists, destroy it before creating a new one
  if (window.weeklyChart) {
    window.weeklyChart.destroy();
  }

  window.weeklyChart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
      datasets: [
        {
          label: "Playtime (minutes)",
          data: weeklyData.map((day) => day.minutes),
          backgroundColor: "#5E9641",
          borderColor: "#4A7834",
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: "Minutes",
          },
        },
      },
    },
  });
}

// Game Sessions Functions
function populateGameSessions(sessions) {
  const sessionsContainer = document.getElementById("sessions-container");
  const template = document.getElementById("session-item-template");

  if (!sessionsContainer || !template) return;

  // Clear existing content
  sessionsContainer.innerHTML = "";

  // Add session items
  sessions.forEach((session) => {
    const sessionElement = template.content.cloneNode(true);

    sessionElement.querySelector(".session-item").dataset.sessionId =
      session.id;
    sessionElement.querySelector(".session-date").textContent = session.date;
    sessionElement.querySelector(".session-duration span").textContent =
      session.duration + " mins";
    sessionElement.querySelector(".session-achievements span").textContent =
      session.achievements;

    sessionsContainer.appendChild(sessionElement);
  });
}

// Achievements Functions
function populateAchievements(achievements) {
  const achievementsContainer = document.getElementById(
    "achievements-container"
  );
  const template = document.getElementById("achievement-card-template");

  if (!achievementsContainer || !template) return;

  // Clear existing content
  achievementsContainer.innerHTML = "";

  // 检查成就数据是否存在
  if (!achievements || achievements.length === 0) {
    achievementsContainer.innerHTML = "<p>No achievements found.</p>";
    return;
  }

  // 计算总体进度
  const completedCount = achievements.filter((a) => a.completed).length;
  const overallProgress = (completedCount / achievements.length) * 100;

  // 创建进度条容器（如果不存在）
  let progressContainer = document.querySelector(".overall-progress-container");
  let progressBar, progressText;

  if (!progressContainer) {
    progressContainer = document.createElement("div");
    progressContainer.className = "overall-progress-container";

    progressBar = document.createElement("div");
    progressBar.id = "overall-achievement-progress";
    progressBar.className = "overall-progress-bar";

    progressText = document.createElement("div");
    progressText.id = "achievement-progress-text";
    progressText.className = "progress-text";

    progressContainer.appendChild(progressBar);
    progressContainer.appendChild(progressText);

    // 将进度条添加到页面
    const progressElement = document.querySelector(".achievement-progress");
    if (progressElement) {
      progressElement.appendChild(progressContainer);
    }
  } else {
    progressBar = document.getElementById("overall-achievement-progress");
    progressText = document.getElementById("achievement-progress-text");
  }

  // 更新进度条和文本
  if (progressBar) progressBar.style.width = overallProgress + "%";
  if (progressText)
    progressText.textContent = Math.round(overallProgress) + "%";

  // 添加成就卡片
  achievements.forEach((achievement) => {
    const achievementElement = template.content.cloneNode(true);
    const card = achievementElement.querySelector(".achievement-card");

    // 设置成就ID (使用achievement_id而不是id)
    card.dataset.achievementId = achievement.achievement_id || achievement.id;

    // 设置成就名称
    const nameElement = card.querySelector(".achievement-name");
    if (nameElement) {
      nameElement.textContent = achievement.name;
    }

    // 设置成就目标描述 (使用goal而不是description)
    const goalElement = card.querySelector(".achievement-goal");
    if (goalElement) {
      goalElement.textContent = achievement.goal || achievement.description;
    }

    // 设置状态指示器
    const statusElement = card.querySelector(".achievement-status");
    if (statusElement) {
      if (achievement.completed) {
        statusElement.classList.add("completed");
        statusElement.title = "Completed";
      } else {
        statusElement.classList.add("in-progress");
        statusElement.title = "In Progress";
      }
    }

    achievementsContainer.appendChild(achievementElement);
  });
}

// Helper Functions
function formatNumber(num) {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Generate mock data for demo
function generateWeeklyPlaytimeData() {
  const days = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
  return days.map((day) => {
    return {
      day: day,
      minutes: Math.floor(Math.random() * 120) + 15, // Random playtime between 15-135 minutes
    };
  });
}

function generateGameSessions() {
  const sessions = [];
  const dates = [
    "Mar 20, 2025",
    "Mar 18, 2025",
    "Mar 15, 2025",
    "Mar 12, 2025",
    "Mar 10, 2025",
  ];

  for (let i = 0; i < 5; i++) {
    sessions.push({
      id: i + 1,
      date: dates[i],
      duration: Math.floor(Math.random() * 120) + 30, // 30-150 minutes
      achievements: Math.floor(Math.random() * 3), // 0-2 achievements
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
      progress: 100,
    },
    {
      id: 2,
      name: "Cowpoke",
      description: "Ship 300 items",
      iconUrl: "./assets/images/achievements/shipping.png",
      completed: true,
      progress: 100,
    },
    {
      id: 3,
      name: "Homesteader",
      description: "Ship 10 of each crop",
      iconUrl: "./assets/images/achievements/crops.png",
      completed: false,
      progress: 70,
    },
    {
      id: 4,
      name: "Angler",
      description: "Catch 24 different fish",
      iconUrl: "./assets/images/achievements/fishing.png",
      completed: false,
      progress: 50,
    },
    {
      id: 5,
      name: "Artificer",
      description: "Craft 30 different items",
      iconUrl: "./assets/images/achievements/crafting.png",
      completed: true,
      progress: 100,
    },
    {
      id: 6,
      name: "Artisan",
      description: "Ship 30 artisan goods",
      iconUrl: "./assets/images/achievements/artisan.png",
      completed: false,
      progress: 30,
    },
  ];

  return achievements;
}

// Function to fetch weekly playtime data for a specific week
// In a real app, this would fetch from a backend
function fetchWeeklyPlaytime(weekNumber) {
  return new Promise((resolve) => {
    setTimeout(() => {
      // Generate mock data for the selected week
      resolve(generateWeeklyPlaytimeData());
    }, 300);
  });
}
