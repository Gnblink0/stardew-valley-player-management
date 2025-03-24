// Players page functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTables if on players list page
    const playersTable = document.getElementById('playersTable');
    if (playersTable) {
        const dataTable = $('#playersTable').DataTable({
            pageLength: 10,
            responsive: true,
            order: [[1, 'asc']] // Sort by name by default
        });
        
        // Player search functionality
        const searchInput = document.getElementById('playerSearch');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                dataTable.search(this.value).draw();
            });
        }
    }
    
    // Delete player confirmation
    const deletePlayerModal = document.getElementById('deletePlayerModal');
    if (deletePlayerModal) {
        deletePlayerModal.addEventListener('show.bs.modal', function(event) {
            // Button that triggered the modal
            const button = event.relatedTarget;
            // Extract player ID from button
            const playerId = button.getAttribute('data-player-id');
            
            // Update the modal's hidden input
            const playerIdInput = this.querySelector('input[name="player_id"]');
            if (playerIdInput) {
                playerIdInput.value = playerId;
            }
        });
    }
    
    // Form validation for edit player form
    const editPlayerForm = document.querySelector('form[name="edit-player-form"]');
    if (editPlayerForm) {
        editPlayerForm.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Validate player name
            const nameInput = this.querySelector('input[name="name"]');
            if (nameInput && nameInput.value.trim() === '') {
                isValid = false;
                nameInput.classList.add('is-invalid');
            } else if (nameInput) {
                nameInput.classList.remove('is-invalid');
            }
            
            // Validate farm name
            const farmNameInput = this.querySelector('input[name="farm_name"]');
            if (farmNameInput && farmNameInput.value.trim() === '') {
                isValid = false;
                farmNameInput.classList.add('is-invalid');
            } else if (farmNameInput) {
                farmNameInput.classList.remove('is-invalid');
            }
            
            if (!isValid) {
                event.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    }
});