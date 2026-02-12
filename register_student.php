<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] != 'SchoolAdmin') {
    header('Location: login.php');
    exit();
}

include 'config/db_connect.php';
$name = $_SESSION['name'];
$universityID = $_SESSION['universityID'];

// For security: confirm SchoolAdmin's university
$stmt = $conn->prepare("SELECT * FROM universities WHERE universityID = ?");
$stmt->execute([$universityID]);
$university = $stmt->fetch();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $matricule = trim($_POST['matricule']);
    $password = $_POST['password'];

    if (empty($student_name) || empty($email) || empty($matricule) || empty($password)) {
        $error = "All fields are required";
    } else {
        $check = $conn->prepare("SELECT * FROM users WHERE email = ? OR matricule = ?");
        $check->execute([$email, $matricule]);
        if ($check->rowCount() > 0) {
            $error = "Email or Matricule already exists";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            // Add matricule column if not exists - we'll do it next
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, universityID, matricule) VALUES (?, ?, ?, 'Student', ?, ?)");
            $stmt->execute([$student_name, $email, $hashed, $universityID, $matricule]);
            $success = "Student registered successfully!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>DigiConnect - Register Student</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .sidebar { background: linear-gradient(180deg, #E30613, #c70410) !important; }
        .btn-danger { background-color: #E30613 !important; border-color: #E30613 !important; }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-text mx-3" style="font-size: 1.4rem;">DigiConnect <sup>4.0</sup></div>
            </a>
            <hr class="sidebar-divider">
            <li class="nav-item"><a class="nav-link" href="school_dashboard.php"><i class="fas fa-school"></i> My University</a></li>
            <li class="nav-item active"><a class="nav-link" href="register_student.php"><i class="fas fa-user-graduate"></i> Register Student</a></li>
            <li class="nav-item"><a class="nav-link" href="resources.php"><i class="fas fa-globe-africa"></i> Global Resources</a></li>
            <li class="nav-item"><a class="nav-link" href="forum.php"><i class="fas fa-comments"></i> Community Forum</a></li>
            <hr class="sidebar-divider d-none d-md-block">
        </ul>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-toggle="dropdown">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo htmlspecialchars($name); ?></span>
                                <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="logout.php">Logout</a>
                            </div>
                        </li>
                    </ul>
                </nav>

                <div class="container-fluid">
                    <div class="container-fluid py-5 mb-4" style="background-color: #FFC107; border-radius: 15px;">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center">
                                <img src="img/digiconnect-logo.jpg" style="height: 90px;">
                            </div>
                            <div class="col-md-10">
                                <h2 style="color: #000;">Register New Student</h2>
                                <p style="color: #000;">University: <strong><?php echo htmlspecialchars($university['name']); ?></strong></p>
                            </div>
                        </div>
                    </div>

                    <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
                    <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

                    <div class="card shadow">
                        <div class="card-header py-3 bg-danger text-white">
                            <h6 class="m-0 font-weight-bold">Student Information</h6>
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
                                            <label>Matricule</label>
                                            <input type="text" name="matricule" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" name="email" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Password</label>
                                            <input type="password" name="password" class="form-control" required minlength="6">
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-danger btn-lg">Register Student</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="sticky-footer bg-white">
                <div class="copyright text-center my-auto">
                    <span>DigiConnect © 2025</span>
                </div>
            </footer>
        </div>
    </div>
</body>
</html>