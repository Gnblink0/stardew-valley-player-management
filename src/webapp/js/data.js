document.addEventListener('DOMContentLoaded', function() {
    // Check if user is logged in
    // const playerData = JSON.parse(localStorage.getItem('currentPlayer'));
    // if (!playerData) {
    //     window.location.href = 'login.html';
    //     return;
    // }
    const playerData = {
        playerName: 'Renie',
        farmName: 'Sunset Valley Farm',
        gold: 1000,
        days: 5,
        avatar: 'farmer1', // Avatar image name without extension
    };
    
    // Save the mock player data to localStorage
    localStorage.setItem('currentPlayer', JSON.stringify(playerData));

    // Update player info in the header
    document.getElementById('player-name').textContent = playerData.playerName;
    document.getElementById('farm-name').textContent = playerData.farmName || 'Farm';
    document.getElementById('player-gold').textContent = playerData.gold || 0;
    document.getElementById('player-days').textContent = playerData.days || 0;
    
    // Set avatar image
    if (playerData.avatar) {
        document.getElementById('player-avatar-img').src = `../images/${playerData.avatar}.png`;
    } else {
        document.getElementById('player-avatar-img').src = '../images/farmer1.png';
    }

    // Logout button
    document.getElementById('logout-btn').addEventListener('click', function() {
        localStorage.removeItem('currentPlayer');
        window.location.href = 'login.html';
    });

    // Tab switching
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('active'));
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Hide all tab content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Show the selected tab content
            const tabName = this.getAttribute('data-tab');
            document.getElementById(`${tabName}-tab`).classList.add('active');
            
            // Load data for the selected tab
            loadTabData(tabName);
        });
    });

    // Season filter buttons
    const seasonButtons = document.querySelectorAll('.season-btn');
    seasonButtons.forEach(button => {
        button.addEventListener('click', function() {
            seasonButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            filterCropsBySeason(this.getAttribute('data-season'));
        });
    });

    // Animal filter buttons
    const animalButtons = document.querySelectorAll('.animal-btn');
    animalButtons.forEach(button => {
        button.addEventListener('click', function() {
            animalButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            filterAnimalsByType(this.getAttribute('data-type'));
        });
    });

    // Inventory filter buttons
    const inventoryButtons = document.querySelectorAll('.inventory-btn');
    inventoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            inventoryButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            filterInventoryByType(this.getAttribute('data-type'));
        });
    });

    // Load initial tab data
    loadTabData('crops');
});

// Function to load data based on the selected tab
function loadTabData(tabName) {
    switch(tabName) {
        case 'crops':
            loadCropsData();
            break;
        case 'animals':
            loadAnimalsData();
            break;
        case 'inventory':
            loadInventoryData();
            break;
    }
}

// Load crops data
function loadCropsData() {
    // This would typically be an API call to fetch the player's crops data
    // For now, we'll use mock data
    const mockCropsData = [
        {
            crop_id: 1,
            name: 'Parsnip',
            season: 'Spring',
            harvested: 24,
            sold: 20,
            image: 'parsnip.png'
        },
        {
            crop_id: 2,
            name: 'Potato',
            season: 'Spring',
            harvested: 15,
            sold: 12,
            image: 'potato.png'
        },
        {
            crop_id: 3,
            name: 'Corn',
            season: 'Summer',
            harvested: 30,
            sold: 25,
            image: 'corn.png'
        },
        {
            crop_id: 4,
            name: 'Blueberry',
            season: 'Summer',
            harvested: 45,
            sold: 40,
            image: 'blueberry.png'
        },
        {
            crop_id: 5,
            name: 'Pumpkin',
            season: 'Fall',
            harvested: 12,
            sold: 10,
            image: 'pumpkin.png'
        }
    ];
    
    renderCrops(mockCropsData);
}

// Render crops to the DOM
function renderCrops(cropsData) {
    const container = document.getElementById('crops-container');
    const emptyMessage = document.getElementById('empty-crops');
    const template = document.getElementById('crop-card-template');
    
    // Clear container
    container.innerHTML = '';
    
    if (cropsData.length === 0) {
        emptyMessage.style.display = 'block';
        return;
    }
    
    emptyMessage.style.display = 'none';
    
    // Create and append crop cards
    cropsData.forEach(crop => {
        const cropCard = template.content.cloneNode(true);
        
        // Set data and content
        cropCard.querySelector('.crop-card').setAttribute('data-crop-id', crop.crop_id);
        cropCard.querySelector('.crop-card').setAttribute('data-season', crop.season.toLowerCase());
        cropCard.querySelector('.crop-name').textContent = crop.name;
        cropCard.querySelector('.crop-season').textContent = crop.season;
        cropCard.querySelector('.harvested span').textContent = crop.harvested;
        cropCard.querySelector('.sold span').textContent = crop.sold;
        
        // Set image if available
        if (crop.image) {
            cropCard.querySelector('.crop-image img').src = `../images/crops/${crop.image}`;
            cropCard.querySelector('.crop-image img').alt = crop.name;
        } else {
            cropCard.querySelector('.crop-image img').src = '../images/crops/default.png';
            cropCard.querySelector('.crop-image img').alt = 'Crop';
        }
        
        container.appendChild(cropCard);
    });
}

// Filter crops by season
function filterCropsBySeason(season) {
    const cropCards = document.querySelectorAll('.crop-card');
    
    cropCards.forEach(card => {
        if (season === 'all' || card.getAttribute('data-season') === season) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
    
    // Show empty message if no crops are visible
    const visibleCrops = document.querySelectorAll('.crop-card[style="display: block"]');
    document.getElementById('empty-crops').style.display = visibleCrops.length === 0 ? 'block' : 'none';
}

// Load animals data
function loadAnimalsData() {
    // Mock data for animals
    const mockAnimalsData = [
        {
            animal_id: 1,
            name: 'Bessie',
            type: 'Cow',
            produce: 'Milk',
            friendship: 8,
            location: 'barn',
            image: 'cow.png'
        },
        {
            animal_id: 2,
            name: 'Clucky',
            type: 'Chicken',
            produce: 'Egg',
            friendship: 10,
            location: 'coop',
            image: 'chicken.png'
        },
        {
            animal_id: 3,
            name: 'Woolly',
            type: 'Sheep',
            produce: 'Wool',
            friendship: 6,
            location: 'barn',
            image: 'sheep.png'
        },
        {
            animal_id: 4,
            name: 'Quackers',
            type: 'Duck',
            produce: 'Duck Egg',
            friendship: 7,
            location: 'coop',
            image: 'duck.png'
        }
    ];
    
    renderAnimals(mockAnimalsData);
}

// Render animals to the DOM
function renderAnimals(animalsData) {
    const container = document.getElementById('animals-container');
    const emptyMessage = document.getElementById('empty-animals');
    const template = document.getElementById('animal-card-template');
    
    // Clear container
    container.innerHTML = '';
    
    if (animalsData.length === 0) {
        emptyMessage.style.display = 'block';
        return;
    }
    
    emptyMessage.style.display = 'none';
    
    // Create and append animal cards
    animalsData.forEach(animal => {
        const animalCard = template.content.cloneNode(true);
        
        // Set data and content
        animalCard.querySelector('.animal-card').setAttribute('data-animal-id', animal.animal_id);
        animalCard.querySelector('.animal-card').setAttribute('data-type', animal.location);
        animalCard.querySelector('.animal-name').textContent = animal.name;
        animalCard.querySelector('.animal-type').textContent = animal.type;
        animalCard.querySelector('.animal-produce').textContent = `Produces: ${animal.produce}`;
        
        // Set image if available
        if (animal.image) {
            animalCard.querySelector('.animal-image img').src = `../images/animals/${animal.image}`;
            animalCard.querySelector('.animal-image img').alt = animal.type;
        } else {
            animalCard.querySelector('.animal-image img').src = '../images/animals/default.png';
            animalCard.querySelector('.animal-image img').alt = 'Animal';
        }
        
        // Create friendship hearts
        const heartsContainer = animalCard.querySelector('.friendship-hearts');
        for (let i = 0; i < 10; i++) {
            const heart = document.createElement('div');
            heart.className = 'heart';
            // Fill hearts based on friendship level
            if (i < animal.friendship) {
                heart.classList.add('filled');
            }
            heartsContainer.appendChild(heart);
        }
        
        container.appendChild(animalCard);
    });
}

// Filter animals by type
function filterAnimalsByType(type) {
    const animalCards = document.querySelectorAll('.animal-card');
    
    animalCards.forEach(card => {
        if (type === 'all' || card.getAttribute('data-type') === type) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
    
    // Show empty message if no animals are visible
    const visibleAnimals = document.querySelectorAll('.animal-card[style="display: block"]');
    document.getElementById('empty-animals').style.display = visibleAnimals.length === 0 ? 'block' : 'none';
}

// Load inventory data
function loadInventoryData() {
    // Mock data for inventory
    const mockInventoryData = [
        {
            item_id: 1,
            name: 'Watering Can',
            type: 'tools',
            quantity: 1,
            value: 100,
            image: 'watering_can.png'
        },
        {
            item_id: 2,
            name: 'Parsnip Seeds',
            type: 'seeds',
            quantity: 15,
            value: 20,
            image: 'parsnip_seeds.png'
        },
        {
            item_id: 3,
            name: 'Potato',
            type: 'produce',
            quantity: 8,
            value: 80,
            image: 'potato.png'
        },
        {
            item_id: 4,
            name: 'Ancient Seed',
            type: 'seeds',
            quantity: 1,
            value: 500,
            image: 'ancient_seed.png'
        },
        {
            item_id: 5,
            name: 'Dinosaur Egg',
            type: 'artifacts',
            quantity: 1,
            value: 350,
            image: 'dinosaur_egg.png'
        }
    ];
    
    renderInventory(mockInventoryData);
}

// Render inventory to the DOM
function renderInventory(inventoryData) {
    const container = document.getElementById('inventory-container');
    const emptyMessage = document.getElementById('empty-inventory');
    const template = document.getElementById('item-card-template');
    
    // Clear container
    container.innerHTML = '';
    
    if (inventoryData.length === 0) {
        emptyMessage.style.display = 'block';
        return;
    }
    
    emptyMessage.style.display = 'none';
    
    // Create and append item cards
    inventoryData.forEach(item => {
        const itemCard = template.content.cloneNode(true);
        
        // Set data and content
        itemCard.querySelector('.item-card').setAttribute('data-item-id', item.item_id);
        itemCard.querySelector('.item-card').setAttribute('data-type', item.type);
        itemCard.querySelector('.item-name').textContent = item.name;
        itemCard.querySelector('.item-type').textContent = capitalizeFirstLetter(item.type);
        itemCard.querySelector('.item-quantity span').textContent = item.quantity;
        itemCard.querySelector('.item-value span').textContent = item.value;
        
        // Set image if available
        if (item.image) {
            itemCard.querySelector('.item-image img').src = `../images/items/${item.image}`;
            itemCard.querySelector('.item-image img').alt = item.name;
        } else {
            itemCard.querySelector('.item-image img').src = '../images/items/default.png';
            itemCard.querySelector('.item-image img').alt = 'Item';
        }
        
        container.appendChild(itemCard);
    });
}

// Filter inventory by type
function filterInventoryByType(type) {
    const itemCards = document.querySelectorAll('.item-card');
    
    itemCards.forEach(card => {
        if (type === 'all' || card.getAttribute('data-type') === type) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
    
    // Show empty message if no items are visible
    const visibleItems = document.querySelectorAll('.item-card[style="display: block"]');
    document.getElementById('empty-inventory').style.display = visibleItems.length === 0 ? 'block' : 'none';
}

// Helper function to capitalize first letter
function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}