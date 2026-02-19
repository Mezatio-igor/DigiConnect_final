<?php
session_start();
// Only SchoolAdmin can access this page
if (!isset($_SESSION['userID']) || $_SESSION['role'] != 'SchoolAdmin') {
    header('Location: login.php');
    exit();
}

include __DIR__ . '/../../config/db_connect.php';
$name = $_SESSION['name'];
$universityID = $_SESSION['universityID'];

// Get university details
$stmt = $conn->prepare("SELECT * FROM universities WHERE universityID = ?");
$stmt->execute([$universityID]);
$university = $stmt->fetch();

if (!$university) {
    die("Your university not found. Contact SuperAdmin.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>DigiConnect - My University</title>
    <!-- Bootstrap CSS -->
    <link href="../../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .sidebar { background: linear-gradient(180deg, #E30613, #c70410) !important; }
        .btn-primary, .btn-danger { background-color: #E30613 !important; border-color: #E30613 !important; }
        .text-primary { color: #E30613 !important; }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../../index.php">
        <div class="sidebar-brand-text mx-3" style="font-size: 1.4rem; font-weight: bold;">
            DigiConnect <sup>4.0</sup>
        </div>
    </a>
    <hr class="sidebar-divider my-0">

    <li class="nav-item">
        <a class="nav-link" href="../../index.php">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <li class="nav-item active">
        <a class="nav-link" href="dashboard.php">
            <i class="fas fa-school"></i>
            <span>My University Dashboard</span>
        </a>
    </li>

    <?php if ($_SESSION['role'] == 'SchoolAdmin'): ?>
    <li class="nav-item">
        <a class="nav-link" href="../../register_student.php">
            <i class="fas fa-user-graduate fa-fw"></i>
            <span>Register Student</span>
        </a>
    </li>

    <li class="nav-item">
    <a class="nav-link" href="register_teacher.php">
        <i class="fas fa-chalkboard-teacher"></i>
        <span>Register Teacher</span>
    </a>
</li>

    <li class="nav-item">
    <a class="nav-link" href="course_add.php">
        <i class="fas fa-book"></i>
        <span>Manage Courses</span>
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="pages/schooladmin/timetable_create.php">
        <i class="fas fa-calendar-alt"></i>
        <span>Timetable Management</span>
    </a>
</li>

    <?php endif; ?>

    <li class="nav-item">
        <a class="nav-link" href="../shared/resources.php">
            <i class="fas fa-globe-africa"></i>
            <span>Global Resources</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="../shared/forum.php">
            <i class="fas fa-comments"></i>
            <span>Community Forum</span>
        </a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">
</ul>
        <!-- End Sidebar -->

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-toggle="dropdown">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo htmlspecialchars($name); ?></span>
                                <img class="img-profile rounded-circle" src="../../img/undraw_profile.svg">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
    <a class="dropdown-item" href="../../logout.php">
        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
        Logout
    </a>
</div>
                        </li>
                    </ul>
                </nav>
                <!-- End Topbar -->

                <div class="container-fluid">
                    <!-- Yellow Welcome Banner -->
                    <div class="container-fluid py-2 mb-4" style="background-color: #FFC107; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center">
                                <img src="../../img/digiconnect-logo.jpg" alt="DigiConnect" style="height: 90px;">
                            </div>
                            <div class="col-md-10">
                                <h2 class="mb-0" style="color: #000; font-weight: bold;">
                                    Welcome back, <?php echo htmlspecialchars($name); ?>!
                                </h2>
                                <p class="mb-0" style="color: #000; font-size: 1rem;">
                                    You are managing:
                                    <strong><?php echo htmlspecialchars($university['name']); ?></strong>
                                    <?php if ($university['location']): ?>
                                        (<?php echo htmlspecialchars($university['location']); ?>)
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row">
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Teachers</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Coming Soon</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Students</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Coming Soon</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Active Courses</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Coming Soon</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-book-open fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-danger text-white">
                            <h6 class="m-0 font-weight-bold">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <p>Teacher, Student, Course, and Assignment management coming soon!</p>
                            <p>For now, you can explore the Global Resource Sharing when logged in as Student.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>DigiConnect © 2025 - HND Project</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="../../vendor/jquery/jquery.min.js"></script>
    <script src="../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/sb-admin-2.min.js"></script>
</body>
</html>