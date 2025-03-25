document.addEventListener("DOMContentLoaded", function () {
  // 从URL获取玩家ID
  const urlParams = new URLSearchParams(window.location.search);
  const playerId = urlParams.get("id");

  if (!playerId) {
    // if no player id, show error message
    document.body.innerHTML =
      '<div style="text-align: center; margin-top: 50px;"><h2>错误：未指定玩家ID</h2><p>请使用格式 data.html?id=玩家ID 访问此页面</p></div>';
    return;
  }

  // 使用玩家ID从服务器获取数据
  fetchPlayerData(playerId);
});

// 从服务器获取玩家数据
function fetchPlayerData(playerId) {
  fetch(`/stardew-valley-player-management/src/api/player.php?id=${playerId}`)
    .then((response) => {
      if (!response.ok) {
        throw new Error("网络响应不正常");
      };
      return response.json();
    })
    .then((data) => {
      if (data.status === "success") {
        // 使用获取的数据更新页面
        updatePlayerInfo(data.data);
        // 加载初始标签数据
        loadTabData("crops");
      } else {
        // 显示错误信息
        document.body.innerHTML = `<div style="text-align: center; margin-top: 50px;"><h2>错误：${data.message}</h2></div>`;
      }
    })
    .catch((error) => {
      console.error("获取玩家数据时出错:", error);
      document.body.innerHTML =
        '<div style="text-align: center; margin-top: 50px;"><h2>错误：无法连接到服务器</h2><p>请稍后再试</p></div>';
    });
}

// 更新玩家信息
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
  // 根据标签名称加载相应的数据
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

// 加载作物数据
function loadCropsData() {
  const playerId = new URLSearchParams(window.location.search).get("id");

  // 从服务器获取作物数据
  fetch(`/stardew-valley-player-management/src/api/crops.php?player_id=${playerId}`)
    .then((response) => {
      if (!response.ok) {
        throw new Error("网络响应不正常");
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
      console.error("获取作物数据时出错:", error);
      document.getElementById("crops-container").innerHTML =
        '<div class="error-message">Error: Unable to load crops data</div>';
    });
}

// 渲染作物数据
function renderCrops(cropsData) {
  const container = document.getElementById("crops-container");

  // 清除容器内容，但保留标题和过滤器
  const title = container.querySelector("h2");
  const filterSection = document.querySelector(".season-filter");

  // 清空容器
  container.innerHTML = "";

  // 如果有标题，添加回去
  if (title) container.appendChild(title);

  // 创建作物容器
  const cropsGrid = document.createElement("div");
  cropsGrid.className = "crops-grid";
  cropsGrid.id = "crops-grid"; // 添加ID以便于过滤
  container.appendChild(cropsGrid);

  // 添加作物卡片
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

  // 初始显示所有作物
  filterCropsBySeason("all");
}

// 按季节过滤作物
function filterCropsBySeason(season) {
  const cropCards = document.querySelectorAll(".crop-card");
  let visibleCount = 0;

  cropCards.forEach((card) => {
    // 将季节字符串拆分为数组
    const seasons = card.dataset.season.split(",");

    // 检查是否为"all"或者包含所选季节
    if (season === "all" || seasons.includes(season)) {
      card.style.display = "";
      visibleCount++;
    } else {
      card.style.display = "none";
    }
  });

  // 处理空结果消息
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
  // Mock data for animals
  const mockAnimalsData = [
    {
      animal_id: 1,
      name: "Bessie",
      type: "Cow",
      produce: "Milk",
      friendship: 8,
      location: "barn",
    },
    {
      animal_id: 2,
      name: "Clucky",
      type: "Chicken",
      produce: "Egg",
      friendship: 10,
      location: "coop",
    },
    {
      animal_id: 3,
      name: "Woolly",
      type: "Sheep",
      produce: "Wool",
      friendship: 6,
      location: "barn",
    },
    {
      animal_id: 4,
      name: "Quackers",
      type: "Duck",
      produce: "Duck Egg",
      friendship: 7,
      location: "coop",
    },
  ];

  renderAnimals(mockAnimalsData);
}

// Render animals to the DOM
function renderAnimals(animalsData) {
  const container = document.getElementById("animals-container");
  const emptyMessage = document.getElementById("empty-animals");
  const template = document.getElementById("animal-card-template");

  // Clear container
  container.innerHTML = "";

  if (animalsData.length === 0) {
    emptyMessage.style.display = "block";
    return;
  }

  emptyMessage.style.display = "none";

  // Create and append animal cards
  animalsData.forEach((animal) => {
    const animalCard = template.content.cloneNode(true);

    // Set data and content
    animalCard
      .querySelector(".animal-card")
      .setAttribute("data-animal-id", animal.animal_id);
    animalCard
      .querySelector(".animal-card")
      .setAttribute("data-type", animal.location);
    animalCard.querySelector(".animal-name").textContent = animal.name;
    animalCard.querySelector(".animal-type").textContent = animal.type;
    animalCard.querySelector(
      ".animal-produce"
    ).textContent = `Produces: ${animal.produce}`;

    // Create friendship hearts
    const heartsContainer = animalCard.querySelector(".friendship-hearts");
    for (let i = 0; i < 10; i++) {
      const heart = document.createElement("div");
      heart.className = "heart";
      // Fill hearts based on friendship level
      if (i < animal.friendship) {
        heart.classList.add("filled");
      }
      heartsContainer.appendChild(heart);
    }

    container.appendChild(animalCard);
  });
}

// Filter animals by type
function filterAnimalsByType(type) {
  const animalCards = document.querySelectorAll(".animal-card");

  animalCards.forEach((card) => {
    if (type === "all" || card.getAttribute("data-type") === type) {
      card.style.display = "block";
    } else {
      card.style.display = "none";
    }
  });

  // Show empty message if no animals are visible
  const visibleAnimals = document.querySelectorAll(
    '.animal-card[style="display: block"]'
  );
  document.getElementById("empty-animals").style.display =
    visibleAnimals.length === 0 ? "block" : "none";
}

// Load inventory data
function loadInventoryData() {
  // Mock data for inventory
  const mockInventoryData = [
    {
      item_id: 1,
      name: "Watering Can",
      type: "tools",
      quantity: 1,
      value: 100,
      image: "watering_can.png",
    },
    {
      item_id: 2,
      name: "Parsnip Seeds",
      type: "seeds",
      quantity: 15,
      value: 20,
      image: "parsnip_seeds.png",
    },
    {
      item_id: 3,
      name: "Potato",
      type: "produce",
      quantity: 8,
      value: 80,
      image: "potato.png",
    },
    {
      item_id: 4,
      name: "Ancient Seed",
      type: "seeds",
      quantity: 1,
      value: 500,
      image: "ancient_seed.png",
    },
    {
      item_id: 5,
      name: "Dinosaur Egg",
      type: "artifacts",
      quantity: 1,
      value: 350,
      image: "dinosaur_egg.png",
    },
  ];

  renderInventory(mockInventoryData);
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

  itemCards.forEach((card) => {
    if (type === "all" || card.getAttribute("data-type") === type) {
      card.style.display = "block";
    } else {
      card.style.display = "none";
    }
  });

  // Show empty message if no items are visible
  const visibleItems = document.querySelectorAll(
    '.item-card[style="display: block"]'
  );
  document.getElementById("empty-inventory").style.display =
    visibleItems.length === 0 ? "block" : "none";
}

// Helper function to capitalize first letter
function capitalizeFirstLetter(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}

// 移除页面上可能存在的重复过滤器
document.addEventListener("DOMContentLoaded", function () {
  // 检查是否有多个过滤器容器
  const filterContainers = document.querySelectorAll(".filter-container");
  if (filterContainers.length > 1) {
    // 保留第一个，删除其余的
    for (let i = 1; i < filterContainers.length; i++) {
      filterContainers[i].remove();
    }
  }

  // 检查是否有多个季节过滤器组
  const seasonFilters = document.querySelectorAll(".season-filters");
  if (seasonFilters.length > 1) {
    // 保留第一个，删除其余的
    for (let i = 1; i < seasonFilters.length; i++) {
      seasonFilters[i].remove();
    }
  }
});
