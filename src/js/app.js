// app.js - 主应用程序 JavaScript 文件

// 基础 URL
const baseUrl = "http://localhost/stardew-valley-player-management/src/api";

// DOM 加载完成后执行
document.addEventListener("DOMContentLoaded", function () {
  // 加载玩家列表
  loadPlayers();

  // 加载玩家下拉列表（用于过滤）
  loadPlayerDropdown();

  // 加载成就列表
  loadAchievements();

  // 加载统计数据
  loadStatistics();

  // 加载仪表板数据
  loadDashboard();

  // 设置事件监听器
  setupEventListeners();
});

// 加载玩家列表
function loadPlayers() {
  fetch(`${baseUrl}/players.php`)
    .then((response) => response.json())
    .then((data) => {
      if (data.status === 200) {
        const playerList = document.getElementById("playerList");
        playerList.innerHTML = "";

        data.data.forEach((player) => {
          const row = document.createElement("tr");
          row.innerHTML = `
                        <td>${player.player_id}</td>
                        <td>${player.name}</td>
                        <td>${player.farm_name}</td>
                        <td>${player.avatar || "N/A"}</td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-player" data-id="${
                              player.player_id
                            }">Edit</button>
                            <button class="btn btn-sm btn-danger delete-player" data-id="${
                              player.player_id
                            }">Delete</button>
                        </td>
                    `;
          playerList.appendChild(row);
        });

        // 添加编辑和删除事件监听器
        addPlayerActionListeners();
      } else {
        console.error("Error loading players:", data.message);
      }
    })
    .catch((error) => console.error("Error:", error));
}

// 加载玩家下拉列表
function loadPlayerDropdown() {
  fetch(`${baseUrl}/players.php`)
    .then((response) => response.json())
    .then((data) => {
      if (data.status === 200) {
        const playerFilter = document.getElementById("playerFilter");

        data.data.forEach((player) => {
          const option = document.createElement("option");
          option.value = player.player_id;
          option.textContent = player.name;
          playerFilter.appendChild(option);
        });
      }
    })
    .catch((error) => console.error("Error:", error));
}

// 加载成就列表
function loadAchievements() {
  const playerId = document.getElementById("playerFilter").value;
  let url = `${baseUrl}/achievements.php`;

  if (playerId) {
    url += `?player_id=${playerId}`;
  }

  fetch(url)
    .then((response) => response.json())
    .then((data) => {
      if (data.status === 200) {
        const achievementList = document.getElementById("achievementList");
        achievementList.innerHTML = "";

        data.data.forEach((achievement) => {
          const row = document.createElement("tr");
          row.innerHTML = `
                        <td>${achievement.player_name}</td>
                        <td>${achievement.achievement_name}</td>
                        <td>${achievement.status}</td>
                        <td>
                            <button class="btn btn-sm btn-primary update-achievement" 
                                data-player-id="${achievement.player_id}" 
                                data-achievement-id="${achievement.achievement_id}" 
                                data-status="${achievement.status}">
                                Update Status
                            </button>
                        </td>
                    `;
          achievementList.appendChild(row);
        });

        // 添加更新成就状态事件监听器
        addAchievementActionListeners();
      } else {
        console.error("Error loading achievements:", data.message);
      }
    })
    .catch((error) => console.error("Error:", error));
}

// 加载统计数据
function loadStatistics() {
  // 加载顶级玩家
  fetch(`${baseUrl}/dashboard/top_players.php`)
    .then((response) => response.json())
    .then((data) => {
      if (data.status === 200) {
        renderTopPlayersChart(data.data);
      } else {
        console.error("Error loading top players:", data.message);
      }
    })
    .catch((error) => console.error("Error:", error));

  // 加载游戏时间统计
  const groupBy = document.getElementById("groupBy").value;
  fetch(`${baseUrl}/dashboard/playtime.php?group_by=${groupBy}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.status === 200) {
        renderPlaytimeChart(data.data);
      } else {
        console.error("Error loading playtime statistics:", data.message);
      }
    })
    .catch((error) => console.error("Error:", error));
}

// 加载仪表板数据
function loadDashboard() {
  // 加载玩家概览
  fetch(`${baseUrl}/statistics.php`)
    .then((response) => response.json())
    .then((data) => {
      if (data.status === 200) {
        renderPlayerOverviewChart(data.data);
      } else {
        console.error("Error loading player statistics:", data.message);
      }
    })
    .catch((error) => console.error("Error:", error));

  // 加载成就摘要
  fetch(`${baseUrl}/dashboard/achievements_summary.php`)
    .then((response) => response.json())
    .then((data) => {
      if (data.status === 200) {
        renderAchievementSummaryChart(data.data);
      } else {
        console.error("Error loading achievement summary:", data.message);
      }
    })
    .catch((error) => console.error("Error:", error));
}

// 设置事件监听器
function setupEventListeners() {
  // 玩家表单提交
  document
    .getElementById("playerForm")
    .addEventListener("submit", function (e) {
      e.preventDefault();
      savePlayer();
    });

  // 清除表单按钮
  document.getElementById("clearForm").addEventListener("click", function () {
    clearPlayerForm();
  });

  // 玩家过滤器更改
  document
    .getElementById("playerFilter")
    .addEventListener("change", function () {
      loadAchievements();
    });

  // 分组方式更改
  document.getElementById("groupBy").addEventListener("change", function () {
    loadStatistics();
  });
}

// 添加玩家操作事件监听器
function addPlayerActionListeners() {
  // 编辑玩家按钮
  document.querySelectorAll(".edit-player").forEach((button) => {
    button.addEventListener("click", function () {
      const playerId = this.getAttribute("data-id");
      editPlayer(playerId);
    });
  });

  // 删除玩家按钮
  document.querySelectorAll(".delete-player").forEach((button) => {
    button.addEventListener("click", function () {
      const playerId = this.getAttribute("data-id");
      if (
        confirm(
          "Are you sure you want to delete this player? This will also delete all related data."
        )
      ) {
        deletePlayer(playerId);
      }
    });
  });
}

// 添加成就操作事件监听器
function addAchievementActionListeners() {
  document.querySelectorAll(".update-achievement").forEach((button) => {
    button.addEventListener("click", function () {
      const playerId = this.getAttribute("data-player-id");
      const achievementId = this.getAttribute("data-achievement-id");
      const currentStatus = this.getAttribute("data-status");

      // 简单的状态循环：Not Started -> In Progress -> Completed -> Not Started
      let newStatus;
      switch (currentStatus) {
        case "Not Started":
          newStatus = "In Progress";
          break;
        case "In Progress":
          newStatus = "Completed";
          break;
        case "Completed":
          newStatus = "Not Started";
          break;
        default:
          newStatus = "Not Started";
      }

      updateAchievementStatus(playerId, achievementId, newStatus);
    });
  });
}

// 保存玩家
function savePlayer() {
  const playerId = document.getElementById("playerId").value;
  const name = document.getElementById("playerName").value;
  const farmName = document.getElementById("farmName").value;
  const avatar = document.getElementById("avatar").value;

  const formData = new FormData();
  formData.append("name", name);
  formData.append("farm_name", farmName);
  if (avatar) {
    formData.append("avatar", avatar);
  }

  let url = `${baseUrl}/players.php`;
  let method = "POST";

  if (playerId) {
    // 更新现有玩家
    url += `?id=${playerId}`;
    method = "PUT";
  }

  fetch(url, {
    method: method,
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === 200 || data.status === 201) {
        alert(
          playerId
            ? "Player updated successfully!"
            : "Player created successfully!"
        );
        clearPlayerForm();
        loadPlayers();
        loadPlayerDropdown();
      } else {
        alert(`Error: ${data.message}`);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("An error occurred. Please try again.");
    });
}

// 编辑玩家
function editPlayer(playerId) {
  fetch(`${baseUrl}/players.php?id=${playerId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.status === 200) {
        const player = data.data;
        document.getElementById("playerId").value = player.player_id;
        document.getElementById("playerName").value = player.name;
        document.getElementById("farmName").value = player.farm_name;
        document.getElementById("avatar").value = player.avatar || "";

        // 滚动到表单
        document
          .getElementById("playerForm")
          .scrollIntoView({ behavior: "smooth" });
      } else {
        alert(`Error: ${data.message}`);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("An error occurred. Please try again.");
    });
}

// 删除玩家
function deletePlayer(playerId) {
  fetch(`${baseUrl}/players.php?id=${playerId}`, {
    method: "DELETE",
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === 200) {
        alert("Player deleted successfully!");
        loadPlayers();
        loadPlayerDropdown();
      } else {
        alert(`Error: ${data.message}`);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("An error occurred. Please try again.");
    });
}

// 更新成就状态
function updateAchievementStatus(playerId, achievementId, status) {
  const formData = new FormData();
  formData.append("player_id", playerId);
  formData.append("achievement_id", achievementId);
  formData.append("status", status);

  fetch(`${baseUrl}/achievements.php`, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === 200) {
        alert("Achievement status updated successfully!");
        loadAchievements();
      } else {
        alert(`Error: ${data.message}`);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("An error occurred. Please try again.");
    });
}

// 清除玩家表单
function clearPlayerForm() {
  document.getElementById("playerId").value = "";
  document.getElementById("playerName").value = "";
  document.getElementById("farmName").value = "";
  document.getElementById("avatar").value = "";
}

// 渲染顶级玩家图表
function renderTopPlayersChart(data) {
  const ctx = document.getElementById("topPlayersChart");

  // 如果已经存在图表，销毁它
  if (window.topPlayersChart) {
    window.topPlayersChart.destroy();
  }

  const labels = data.map((player) => player.name);
  const scores = data.map((player) => player.total_gold_earned);

  window.topPlayersChart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Total Gold Earned",
          data: scores,
          backgroundColor: "rgba(75, 192, 192, 0.2)",
          borderColor: "rgba(75, 192, 192, 1)",
          borderWidth: 1,
        },
      ],
    },
    options: {
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });
}

// 渲染游戏时间图表
function renderPlaytimeChart(data) {
  const ctx = document.getElementById("playtimeChart");

  // 如果已经存在图表，销毁它
  if (window.playtimeChart) {
    window.playtimeChart.destroy();
  }

  const labels = data.map((item) => item.period);
  const playtimes = data.map((item) => item.total_minutes);

  window.playtimeChart = new Chart(ctx, {
    type: "line",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Playtime (minutes)",
          data: playtimes,
          fill: false,
          borderColor: "rgb(75, 192, 192)",
          tension: 0.1,
        },
      ],
    },
    options: {
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });
}

// 渲染玩家概览图表
function renderPlayerOverviewChart(data) {
  const ctx = document.getElementById("playerOverviewChart");

  // 如果已经存在图表，销毁它
  if (window.playerOverviewChart) {
    window.playerOverviewChart.destroy();
  }

  const labels = data.map((player) => player.name);
  const goldEarned = data.map((player) => player.total_gold_earned);
  const inGameDays = data.map((player) => player.in_game_days);

  window.playerOverviewChart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Total Gold Earned",
          data: goldEarned,
          backgroundColor: "rgba(75, 192, 192, 0.2)",
          borderColor: "rgba(75, 192, 192, 1)",
          borderWidth: 1,
          yAxisID: "y",
        },
        {
          label: "In-Game Days",
          data: inGameDays,
          backgroundColor: "rgba(153, 102, 255, 0.2)",
          borderColor: "rgba(153, 102, 255, 1)",
          borderWidth: 1,
          yAxisID: "y1",
        },
      ],
    },
    options: {
      scales: {
        y: {
          type: "linear",
          display: true,
          position: "left",
          beginAtZero: true,
        },
        y1: {
          type: "linear",
          display: true,
          position: "right",
          beginAtZero: true,
          grid: {
            drawOnChartArea: false,
          },
        },
      },
    },
  });
}

// 渲染成就摘要图表
function renderAchievementSummaryChart(data) {
  const ctx = document.getElementById("achievementSummaryChart");

  // 如果已经存在图表，销毁它
  if (window.achievementSummaryChart) {
    window.achievementSummaryChart.destroy();
  }

  const labels = ["Completed", "In Progress", "Not Started"];
  const counts = [
    data.completed || 0,
    data.in_progress || 0,
    data.not_started || 0,
  ];

  window.achievementSummaryChart = new Chart(ctx, {
    type: "pie",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Achievement Status",
          data: counts,
          backgroundColor: [
            "rgba(75, 192, 192, 0.2)",
            "rgba(255, 206, 86, 0.2)",
            "rgba(255, 99, 132, 0.2)",
          ],
          borderColor: [
            "rgba(75, 192, 192, 1)",
            "rgba(255, 206, 86, 1)",
            "rgba(255, 99, 132, 1)",
          ],
          borderWidth: 1,
        },
      ],
    },
  });
}
