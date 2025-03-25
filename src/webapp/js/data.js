document.addEventListener("DOMContentLoaded", function () {
  const urlParams = new URLSearchParams(window.location.search);
  const playerId = urlParams.get("id");

  if (!playerId) {
    // if no player id, show error message
    document.body.innerHTML =
      '<div style="text-align: center; margin-top: 50px;"><h2>错误：未指定玩家ID</h2><p>请使用格式 data.html?id=玩家ID 访问此页面</p></div>';
    return;
  }

  fetchPlayerData(playerId);
});

function fetchPlayerData(playerId) {
  fetch(`/stardew-valley-player-management/src/api/player.php?id=${playerId}`)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response not ok");
      }
      return response.json();
    })
    .then((data) => {
      if (data.status === "success") {
        updatePlayerInfo(data.data);
        loadTabData("crops");
      } else {
        document.body.innerHTML = `<div style="text-align: center; margin-top: 50px;"><h2>错误：${data.message}</h2></div>`;
      }
    })
    .catch((error) => {
      console.error("Error fetching player data:", error);
      document.body.innerHTML =
        '<div style="text-align: center; margin-top: 50px;"><h2>错误：无法连接到服务器</h2><p>请稍后再试</p></div>';
    });
}

function updatePlayerInfo(playerData) {
  document.getElementById("player-name").textContent = playerData.name;
  document.getElementById("farm-name").textContent =
    playerData.farm_name || "Farm";
  document.getElementById("player-gold").textContent =
    playerData.total_gold_earned || 0;
  document.getElementById("player-days").textContent =
    playerData.in_game_days || 0;
}

// Logout button
document.getElementById("logout-btn").addEventListener("click", function () {
  localStorage.removeItem("currentPlayer");
  window.location.href = "login.html";
});

// Tab switching
const tabs = document.querySelectorAll(".tab");
tabs.forEach((tab) => {
  tab.addEventListener("click", function () {
    // Remove active class from all tabs
    tabs.forEach((t) => t.classList.remove("active"));
    // Add active class to clicked tab
    this.classList.add("active");

    // Hide all tab content
    document.querySelectorAll(".tab-content").forEach((content) => {
      content.classList.remove("active");
    });

    // Show the selected tab content
    const tabName = this.getAttribute("data-tab");
    document.getElementById(`${tabName}-tab`).classList.add("active");

    // Load data for the selected tab
    loadTabData(tabName);
  });
});

// Season filter buttons
const seasonButtons = document.querySelectorAll(".season-btn");
seasonButtons.forEach((button) => {
  button.addEventListener("click", function () {
    seasonButtons.forEach((btn) => btn.classList.remove("active"));
    this.classList.add("active");
    filterCropsBySeason(this.getAttribute("data-season"));
  });
});

// Animal filter buttons
const animalButtons = document.querySelectorAll(".animal-btn");
animalButtons.forEach((button) => {
  button.addEventListener("click", function () {
    animalButtons.forEach((btn) => btn.classList.remove("active"));
    this.classList.add("active");
    filterAnimalsByType(this.getAttribute("data-type"));
  });
});

// Inventory filter buttons
const inventoryButtons = document.querySelectorAll(".inventory-btn");
inventoryButtons.forEach((button) => {
  button.addEventListener("click", function () {
    inventoryButtons.forEach((btn) => btn.classList.remove("active"));
    this.classList.add("active");
    filterInventoryByType(this.getAttribute("data-type"));
  });
});

// Function to load data based on the selected tab
function loadTabData(tabName) {
  switch (tabName) {
    case "crops":
      loadCropsData();
      break;
    case "animals":
      loadAnimalsData();
      break;
    case "inventory":
      loadInventoryData();
      break;
  }
}

function loadCropsData() {
  const playerId = new URLSearchParams(window.location.search).get("id");

  fetch(
    `/stardew-valley-player-management/src/api/crops.php?player_id=${playerId}`
  )
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response not ok");
      }
      return response.json();
    })
    .then((data) => {
      if (data.status === "success") {
        renderCrops(data.data);
      } else {
        document.getElementById(
          "crops-container"
        ).innerHTML = `<div class="error-message">Error: ${data.message}</div>`;
      }
    })
    .catch((error) => {
      console.error("Error fetching crops data:", error);
      document.getElementById("crops-container").innerHTML =
        '<div class="error-message">Error: Unable to load crops data</div>';
    });
}

function renderCrops(cropsData) {
  const container = document.getElementById("crops-container");

  const title = container.querySelector("h2");
  const filterSection = document.querySelector(".season-filter");

  container.innerHTML = "";

  if (title) container.appendChild(title);
  if (title) container.appendChild(title);

  const cropsGrid = document.createElement("div");
  cropsGrid.className = "crops-grid";
  cropsGrid.id = "crops-grid";
  container.appendChild(cropsGrid);

  cropsData.forEach((crop) => {
    const cropCard = document.createElement("div");
    cropCard.className = "crop-card";
    cropCard.dataset.cropId = crop.crop_id;
    cropCard.dataset.season = crop.season.toLowerCase();

    cropCard.innerHTML = `
      <div class="crop-name">${crop.name}</div>
      <div class="crop-season">${crop.season}</div>
      <div class="crop-stats">
        <div class="harvested">Harvested: <span>${
          crop.harvested || 0
        }</span></div>
        <div class="sold">Sold: <span>${crop.sold || 0}</span></div>
      </div>
    `;

    cropsGrid.appendChild(cropCard);
  });

  filterCropsBySeason("all");
}

function filterCropsBySeason(season) {
  const cropCards = document.querySelectorAll(".crop-card");
  let visibleCount = 0;

  cropCards.forEach((card) => {
    const seasons = card.dataset.season.split(",");

    if (season === "all" || seasons.includes(season)) {
      card.style.display = "";
      visibleCount++;
    } else {
      card.style.display = "none";
    }
  });

  const cropsGrid = document.getElementById("crops-grid");
  const existingEmptyMessage = document.querySelector(".empty-season-message");

  if (visibleCount === 0 && cropsGrid) {
    if (!existingEmptyMessage) {
      const emptyMessage = document.createElement("div");
      emptyMessage.className = "empty-season-message";
      emptyMessage.textContent = `No ${
        season !== "all" ? season : ""
      } crops available.`;
      cropsGrid.appendChild(emptyMessage);
    } else {
      existingEmptyMessage.textContent = `No ${
        season !== "all" ? season : ""
      } crops available.`;
    }
  } else if (existingEmptyMessage) {
    existingEmptyMessage.remove();
  }
}

// Load animals data
function loadAnimalsData() {
  const playerId = new URLSearchParams(window.location.search).get("id");

  fetch(
    `/stardew-valley-player-management/src/api/animals.php?player_id=${playerId}`
  )
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response not ok");
      }
      return response.json();
    })
    .then((data) => {
      if (data.status === "success") {
        renderAnimals(data.data);
      } else {
        document.getElementById(
          "animals-container"
        ).innerHTML = `<div class="error-message">Error: ${data.message}</div>`;
      }
    })
    .catch((error) => {
      console.error("Error fetching animals data:", error);
      document.getElementById("animals-container").innerHTML =
        '<div class="error-message">Error: Unable to load animals data</div>';
    });
}

// Render animals to the DOM
function renderAnimals(animalsData) {
  const container = document.getElementById("animals-container");

  container.innerHTML = "";

  if (!animalsData || animalsData.length === 0) {
    container.innerHTML = '<div class="empty-message">No animals found.</div>';
    return;
  }

  const animalsGrid = document.createElement("div");
  animalsGrid.className = "animals-grid";
  animalsGrid.id = "animals-grid";
  container.appendChild(animalsGrid);

  animalsData.forEach((animal) => {
    const animalCard = document.createElement("div");
    animalCard.className = "animal-card";
    animalCard.dataset.animalId = animal.animal_id;
    animalCard.dataset.type = animal.building;

    animalCard.innerHTML = `
      <div class="animal-name">${animal.name}</div>
      <div class="animal-type">${capitalizeFirstLetter(animal.type)}</div>
      <div class="animal-produce">${
        animal.produce
          ? `Produces: ${capitalizeFirstLetter(animal.produce)}`
          : "No produce"
      }</div>
      <div class="friendship-hearts"></div>
    `;

    const heartsContainer = animalCard.querySelector(".friendship-hearts");
    const friendshipLevel = parseInt(animal.friendship_level) || 0;
    const maxHearts = 5;

    for (let i = 0; i < maxHearts; i++) {
      const heart = document.createElement("div");
      heart.className = i < friendshipLevel ? "heart filled" : "heart";
      heartsContainer.appendChild(heart);
    }

    animalsGrid.appendChild(animalCard);
  });
}

// Filter animals by type
function filterAnimalsByType(type) {
  const animalCards = document.querySelectorAll(".animal-card");
  let visibleCount = 0;

  animalCards.forEach((card) => {
    if (type === "all" || card.getAttribute("data-type") === type) {
      card.style.display = "";
      visibleCount++;
    } else {
      card.style.display = "none";
    }
  });

  // 显示空消息（如果没有可见的动物）
  const emptyMessage =
    document.querySelector(".empty-message") || document.createElement("div");
  if (visibleCount === 0) {
    if (!document.querySelector(".empty-message")) {
      emptyMessage.className = "empty-message";
      emptyMessage.textContent = `No ${
        type !== "all" ? type : ""
      } animals available.`;
      const animalsGrid = document.getElementById("animals-grid");
      if (animalsGrid) {
        animalsGrid.appendChild(emptyMessage);
      }
    }
  } else if (document.querySelector(".empty-message")) {
    document.querySelector(".empty-message").remove();
  }
}

// Load inventory data
function loadInventoryData() {
  const playerId = new URLSearchParams(window.location.search).get("id");

  fetch(
    `/stardew-valley-player-management/src/api/inventory.php?player_id=${playerId}`
  )
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response not ok");
      }
      return response.json();
    })
    .then((data) => {
      if (data.status === "success") {
        renderInventory(data.data);
      } else {
        document.getElementById(
          "inventory-container"
        ).innerHTML = `<div class="error-message">Error: ${data.message}</div>`;
      }
    })
    .catch((error) => {
      console.error("Error fetching inventory data:", error);
      document.getElementById("inventory-container").innerHTML =
        '<div class="error-message">Error: Unable to load inventory data</div>';
    });
}

// Render inventory to the DOM
function renderInventory(inventoryData) {
  const container = document.getElementById("inventory-container");
  const emptyMessage = document.getElementById("empty-inventory");
  const template = document.getElementById("item-card-template");

  // Clear container
  container.innerHTML = "";

  if (inventoryData.length === 0) {
    emptyMessage.style.display = "block";
    return;
  }

  emptyMessage.style.display = "none";

  // Create and append item cards
  inventoryData.forEach((item) => {
    const itemCard = template.content.cloneNode(true);

    // Set data and content
    itemCard
      .querySelector(".item-card")
      .setAttribute("data-item-id", item.item_id);
    itemCard.querySelector(".item-card").setAttribute("data-type", item.type);
    itemCard.querySelector(".item-name").textContent = item.name;
    itemCard.querySelector(".item-type").textContent = capitalizeFirstLetter(
      item.type
    );
    itemCard.querySelector(".item-quantity span").textContent = item.quantity;
    itemCard.querySelector(".item-value span").textContent = item.value;

    container.appendChild(itemCard);
  });
}

// Filter inventory by type
function filterInventoryByType(type) {
  const itemCards = document.querySelectorAll(".item-card");
  let visibleCount = 0;

  itemCards.forEach((card) => {
    if (type === "all" || card.getAttribute("data-type") === type) {
      card.style.display = "block";
      visibleCount++;
    } else {
      card.style.display = "none";
    }
  });

  // 使用计数器而不是DOM查询来确定是否有可见元素
  document.getElementById("empty-inventory").style.display =
    visibleCount === 0 ? "block" : "none";
}

// Helper function to capitalize first letter
function capitalizeFirstLetter(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}
