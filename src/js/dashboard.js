// Dashboard functionality - UI interactions, smooth scrolling, etc.
document.addEventListener('DOMContentLoaded', function() {
  // Smooth scrolling for sidebar links
  document.querySelectorAll('.sidebar .nav-link').forEach(link => {
      link.addEventListener('click', function(e) {
          e.preventDefault();
          
          // Get the target element
          const targetId = this.getAttribute('href');
          const targetElement = document.querySelector(targetId);
          
          if (targetElement) {
              // Smooth scroll to target
              window.scrollTo({
                  top: targetElement.offsetTop - 70, // Account for fixed navbar
                  behavior: 'smooth'
              });
              
              // Update active state in sidebar
              document.querySelectorAll('.sidebar .nav-link').forEach(item => {
                  item.classList.remove('active');
              });
              this.classList.add('active');
          }
      });
  });
  
  // Update active sidebar link on scroll
  window.addEventListener('scroll', function() {
      const scrollPosition = window.scrollY;
      
      // Check each section's position
      document.querySelectorAll('section').forEach(section => {
          const sectionTop = section.offsetTop - 100;
          const sectionBottom = sectionTop + section.offsetHeight;
          
          if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
              const id = section.getAttribute('id');
              
              // Update active state in sidebar
              document.querySelectorAll('.sidebar .nav-link').forEach(item => {
                  item.classList.remove('active');
                  
                  if (item.getAttribute('href') === '#' + id) {
                      item.classList.add('active');
                  }
              });
          }
      });
  });
  
  // Mobile sidebar toggle (for responsive design)
  const mobileToggle = document.querySelector('.navbar-toggler');
  if (mobileToggle) {
      mobileToggle.addEventListener('click', function() {
          document.querySelector('.sidebar').classList.toggle('show');
      });
  }
  
  // Initialize tooltips
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function(tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
  });
  
  // Time range dropdown functionality
  document.querySelectorAll('.dropdown-item').forEach(item => {
      item.addEventListener('click', function(e) {
          e.preventDefault();
          
          // Update dropdown button text
          const button = document.getElementById('timeRangeDropdown');
          if (button) {
              button.innerHTML = '<i class="fas fa-calendar me-1"></i> ' + this.textContent;
          }
          
          // Update active state
          document.querySelectorAll('.dropdown-item').forEach(dropItem => {
              dropItem.classList.remove('active');
          });
          this.classList.add('active');
          
          // Refresh charts with new time range
          fetchChartData();
      });
  });
  
  // Sort by gold/days buttons functionality
  const sortByGoldBtn = document.getElementById('sort-by-gold');
  const sortByDaysBtn = document.getElementById('sort-by-days');
  
  if (sortByGoldBtn && sortByDaysBtn) {
      sortByGoldBtn.addEventListener('click', function() {
          sortLeaderboard('gold');
      });
      
      sortByDaysBtn.addEventListener('click', function() {
          sortLeaderboard('days');
      });
  }
});

// Function to sort leaderboard table
function sortLeaderboard(criteria) {
  // This would typically be an AJAX call to get sorted data
  // For this example, we'll fake it with a simple message
  
  const tbody = document.querySelector('#player-stats table tbody');
  if (!tbody) return;
  
  // Show loading indicator
  tbody.innerHTML = '<tr><td colspan="6" class="text-center"><div class="loading-spinner mx-auto"></div></td></tr>';
  
  // Simulate server delay
  setTimeout(function() {
      if (criteria === 'gold') {
          // Sort by gold (this would be server-side in a real app)
          // For demo purposes, we'll just show a message
          console.log('Sorting by gold');
      } else if (criteria === 'days') {
          // Sort by days played
          console.log('Sorting by days played');
      }
      
      // Refresh charts
      fetchChartData();
  }, 500);
}