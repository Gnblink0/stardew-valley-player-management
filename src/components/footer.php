</div>
    
    <footer class="footer mt-5 py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">Stardew Valley Player Management &copy; <?php echo date('Y'); ?> - CS 5200 Practicum Project</span>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    
    <!-- DataTables -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <?php if (basename($_SERVER['PHP_SELF']) == 'dashboard.php'): ?>
    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/charts.js"></script>
    <?php endif; ?>
    
    <?php if (basename($_SERVER['PHP_SELF']) == 'players.php'): ?>
    <script src="assets/js/players.js"></script>
    <?php endif; ?>
    
    <?php if (basename($_SERVER['PHP_SELF']) == 'achievements.php'): ?>
    <script src="assets/js/achievements.js"></script>
    <?php endif; ?>
</body>
</html>