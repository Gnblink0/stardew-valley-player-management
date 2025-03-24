document.addEventListener('DOMContentLoaded', function() {
    // Tab switching functionality
    const loginTab = document.getElementById('login-tab');
    const registerTab = document.getElementById('register-tab');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    
    loginTab.addEventListener('click', function() {
        loginTab.classList.add('active');
        registerTab.classList.remove('active');
        loginForm.classList.add('active');
        registerForm.classList.remove('active');
        clearMessages();
    });
    
    registerTab.addEventListener('click', function() {
        registerTab.classList.add('active');
        loginTab.classList.remove('active');
        registerForm.classList.add('active');
        loginForm.classList.remove('active');
        clearMessages();
    });
    
    // Clear error and success messages
    function clearMessages() {
        document.getElementById('login-error-message').textContent = '';
        document.getElementById('register-error-message').textContent = '';
        document.getElementById('login-success-message').style.display = 'none';
        document.getElementById('register-success-message').style.display = 'none';
    }
    
    // Avatar selection functionality
    const avatarOptions = document.querySelectorAll('.avatar-option');
    avatarOptions.forEach(option => {
        option.addEventListener('click', function() {
            avatarOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
    
    // Register form submission
    const registerBtn = document.getElementById('register-btn');
    registerBtn.addEventListener('click', function() {
        const playerName = document.getElementById('player-name').value.trim();
        const farmName = document.getElementById('farm-name').value.trim();
        const selectedAvatar = document.querySelector('.avatar-option.selected').dataset.avatar;
        const initGold = document.getElementById('init-gold').value || 0;
        const initDays = document.getElementById('init-days').value || 0;
        
        const registerErrorMessage = document.getElementById('register-error-message');
        const registerSuccessMessage = document.getElementById('register-success-message');
        
        // Validation
        if (!playerName) {
            registerErrorMessage.textContent = 'Please enter a player name';
            return;
        }
        
        if (!farmName) {
            registerErrorMessage.textContent = 'Please enter a farm name';
            return;
        }
        
        // Create form data
        const formData = new FormData();
        formData.append('name', playerName);
        formData.append('farm_name', farmName);
        formData.append('avatar', selectedAvatar);
        formData.append('total_gold_earned', initGold);
        formData.append('in_game_days', initDays);
        
        // Send POST request
        fetch('../../api/login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                registerErrorMessage.textContent = '';
                document.getElementById('new-player-id').textContent = data.data.player_id;
                registerSuccessMessage.style.display = 'block';
                
                // Reset form
                document.getElementById('player-name').value = '';
                document.getElementById('farm-name').value = '';
                document.getElementById('init-gold').value = '';
                document.getElementById('init-days').value = '';
            } else {
                registerErrorMessage.textContent = data.message || 'Registration failed, please try again later';
                registerSuccessMessage.style.display = 'none';
            }
        })
        .catch(error => {
            registerErrorMessage.textContent = 'Server connection failed, please try again later';
            console.error('Error:', error);
        });
    });
    
    // Login form submission
    const loginBtn = document.getElementById('login-btn');
    loginBtn.addEventListener('click', function() {
        const playerId = document.getElementById('login-id').value.trim();
        const playerName = document.getElementById('login-name').value.trim();
        
        const loginErrorMessage = document.getElementById('login-error-message');
        const loginSuccessMessage = document.getElementById('login-success-message');
        
        // Validation
        if (!playerId && !playerName) {
            loginErrorMessage.textContent = 'Please enter player ID or player name';
            return;
        }
        
        // Fetch player data
        let url = '../../api/login.php';
        if (playerId) {
            url += `?id=${playerId}`;
        } else {
            // Note: Your API may need to be adjusted to support name-based queries
            url += `?name=${encodeURIComponent(playerName)}`;
        }
        
        fetch(url, {
            method: 'GET'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                loginErrorMessage.textContent = '';
                loginSuccessMessage.style.display = 'block';
                
                // Store player data in localStorage for session management
                localStorage.setItem('playerData', JSON.stringify(data.data));
                
                // Redirect to dashboard after successful login
                setTimeout(() => {
                    window.location.href = 'dashboard.html';
                }, 1500);
            } else {
                loginErrorMessage.textContent = data.message || 'Player not found. Please check your ID or name.';
                loginSuccessMessage.style.display = 'none';
            }
        })
        .catch(error => {
            loginErrorMessage.textContent = 'Server connection failed, please try again later';
            console.error('Error:', error);
        });
    });
});
