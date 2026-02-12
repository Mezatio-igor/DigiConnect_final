<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] != 'SuperAdmin') {
    header('Location: login.php');
    exit();
}

include 'config/db_connect.php';
$name = $_SESSION['name'];

// Fetch universities for dropdown
$stmt = $conn->query("SELECT * FROM universities ORDER BY name");
$universities = $stmt->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $universityID = $_POST['university'];

    if (empty($admin_name) || empty($email) || empty($password) || empty($universityID)) {
        $error = "All fields are required";
    } else {
        // Check if email already exists
        $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->rowCount() > 0) {
            $error = "Email already exists";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, universityID) VALUES (?, ?, ?, 'SchoolAdmin', ?)");
            $stmt->execute([$admin_name, $email, $hashed, $universityID]);
            $success = "SchoolAdmin created successfully!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>DigiConnect - Register SchoolAdmin</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .sidebar { background: linear-gradient(180deg, #E30613, #c70410) !important; }
        .btn-primary { background-color: #E30613 !important; border-color: #E30613 !important; }
        .text-primary { color: #E30613 !important; }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar (copy from index.php, without logo) -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-text mx-3" style="font-size: 1.4rem; font-weight: bold;">
                    DigiConnect <sup style="font-size: 0.8rem;">4.0</sup>
                </div>
            </a>
            <hr class="sidebar-divider">
            <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>

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
        </ul>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Topbar (copy from index.php) -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-toggle="dropdown">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo htmlspecialchars($name); ?></span>
                                <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            </div>
                        </li>
                    </ul>
                </nav>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Register New SchoolAdmin</h1>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-danger text-white">
                            <h6 class="m-0 font-weight-bold">SchoolAdmin Details</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Full Name</label>
                                            <input type="text" name="name" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" name="email" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Password</label>
                                            <input type="password" name="password" class="form-control" required minlength="6">
                                        </div>
                                        <div class="form-group">
                                            <label>Assign to University</label>
                                            <select name="university" class="form-control" required>
                                                <option value="">Select University</option>
                                                <?php foreach ($universities as $uni): ?>
                                                    <option value="<?php echo $uni['universityID']; ?>">
                                                        <?php echo htmlspecialchars($uni['name']); ?> (<?php echo htmlspecialchars($uni['location'] ?: 'No location'); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-danger btn-lg">Create SchoolAdmin</button>
                            </form>
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

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
</body>
</html>