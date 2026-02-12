<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
<?php if ($_SESSION['role'] == 'SuperAdmin'): ?>
<li class="nav-item">
    <a class="nav-link" href="universities.php">
        <i class="fas fa-university"></i>
        <span>Manage Universities</span>
    </a>
</li>
<?php endif; ?>
            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.html">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-laugh-wink"></i>
                </div>
                <div class="sidebar-brand-text mx-3">SB Admin <sup>2</sup></div>
            </a>