<?php
session_start();
// Only SuperAdmin can access
if (!isset($_SESSION['userID']) || $_SESSION['role'] != 'SuperAdmin') {
    header('Location: login.php');
    exit();
}

include 'config/db_connect.php';
$name = $_SESSION['name'];
$page_title = "Manage Universities";

// Fetch universities
$stmt = $conn->query("SELECT * FROM universities ORDER BY name");
$universities = $stmt->fetchAll();

// Add new university
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uni_name = trim($_POST['name']);
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';
    if ($uni_name !== '') {
        $stmt = $conn->prepare("INSERT INTO universities (name, location) VALUES (?, ?)");
        $stmt->execute([$uni_name, $location]);
        header('Location: universities.php?added=1');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>DigiConnect - <?php echo $page_title; ?></title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <style>
    /* DigiConnect Custom Theme - Red & Yellow */
    :root {
        --dc-red: #E30613;
        --dc-yellow: #FFC107;
        --dc-dark-red: #C70410;
    }

    /* Sidebar background */
    .bg-gradient-primary {
        background: linear-gradient(180deg, var(--dc-red) 0%, var(--dc-dark-red) 100%) !important;
    }

    /* Buttons, links, headings */
    .btn-primary {
        background-color: var(--dc-red);
        border-color: var(--dc-red);
    }
    .btn-primary:hover {
        background-color: var(--dc-dark-red);
        border-color: var(--dc-dark-red);
    }

    .text-primary {
        color: var(--dc-red) !important;
    }

    /* Active menu item */
    .nav-item.active .nav-link {
        background-color: var(--dc-yellow);
        color: #000 !important;
    }

    /* Card headers */
    .card-header .text-primary {
        color: var(--dc-red) !important;
    }

    /* Success alerts (when adding university) */
    .alert-success {
        background-color: #d4edda;
        border-color: var(--dc-yellow);
        color: #155724;
    }
</style>
</head>

<body id="page-top">

    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

<a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
    <div class="sidebar-brand-text mx-3" style="font-size: 1.4rem; font-weight: bold;">
        DigiConnect <sup style="font-size: 0.8rem;">4.0</sup>
    </div>
</a>

            <hr class="sidebar-divider my-0">

            <li class="nav-item">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <?php if ($_SESSION['role'] == 'SchoolAdmin'): ?>
<li class="nav-item">
    <a class="nav-link" href="school_dashboard.php">
        <i class="fas fa-school"></i>
        <span>My University Dashboard</span>
    </a>
</li>
<?php endif; ?>

            <?php if ($_SESSION['role'] == 'SuperAdmin'): ?>
<li class="nav-item">
    <a class="nav-link" href="universities.php">
        <i class="fas fa-university"></i>
        <span>Manage Universities</span>
    </a>
</li>
<?php endif; ?>

           <?php if ($_SESSION['role'] == 'SuperAdmin'): ?>
<li class="nav-item">
    <a class="nav-link" href="register_schooladmin.php">
        <i class="fas fa-user-plus"></i>
        <span>Add SchoolAdmin</span>
    </a>
</li>
<?php endif; ?>

<li class="nav-item">
    <a class="nav-link" href="resources.php">
        <i class="fas fa-globe-africa fa-fw"></i>
        <span>Global Resources</span>
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="forum.php">
        <i class="fas fa-comments fa-fw"></i>
        <span>Community Forum</span>
    </a>
</li>

            <hr class="sidebar-divider d-none d-md-block">

            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                    <?php echo htmlspecialchars($name); ?>
                                </span>
                                <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
                                <a class="dropdown-item" href="logout.php">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Manage Universities</h1>
                    </div>

                    <?php if (isset($_GET['added'])): ?>
                    <div class="alert alert-success">University added successfully!</div>
                    <?php endif; ?>

                    <!-- Universities Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Registered Universities</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>University Name</th>
                                            <th>Location</th>
                                            <th>School Admin</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($universities as $uni): ?>
                                        <tr>
                                            <td><?php echo $uni['universityID']; ?></td>
                                            <td><?php echo htmlspecialchars($uni['name']); ?></td>
                                            <td><?php echo htmlspecialchars($uni['location'] ?: 'Not specified'); ?></td>
                                            <td><?php echo $uni['schoolAdminID'] ? 'Assigned' : 'None'; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Add New University -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Add New University</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>University Name <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Location</label>
                                            <input type="text" name="location" class="form-control" placeholder="e.g., Yaoundé, Cameroon">
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Add University</button>
                            </form>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>DigiConnect &copy; 2025 - HND Project</span>
                    </div>
                </div>
            </footer>

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scripts -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>

</body>
</html>