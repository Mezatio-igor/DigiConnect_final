<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] != 'Student') {
    header('Location: ../../login.php');
    exit();
}

include '../../config/db_connect.php';

$studentID = $_SESSION['userID'];
$name = $_SESSION['name'];

// Handle registration request
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_course'])) {
    $courseID = (int)$_POST['courseID'];

    // Check if already registered
    $check = $conn->prepare("SELECT * FROM student_courses WHERE studentID = ? AND courseID = ?");
    $check->execute([$studentID, $courseID]);

    if ($check->rowCount() > 0) {
        $error = "You are already registered for this course.";
    } else {
        $stmt = $conn->prepare("INSERT INTO student_courses (studentID, courseID) VALUES (?, ?)");
        if ($stmt->execute([$studentID, $courseID])) {
            $success = "Successfully registered for the course!";
        } else {
            $error = "Failed to register. Try again.";
        }
    }
}

// Get all available courses in the university (filter later if needed)
$courseStmt = $conn->prepare("
    SELECT c.courseID, c.name, c.code, d.name AS deptName, u.name AS teacherName 
    FROM courses c 
    LEFT JOIN departments d ON c.departmentID = d.departmentID 
    LEFT JOIN users u ON c.teacherID = u.userID 
    WHERE c.universityID = ?
    ORDER BY c.name
");
$courseStmt->execute([$_SESSION['universityID']]);
$allCourses = $courseStmt->fetchAll();

// Get student's already registered courses
$registeredStmt = $conn->prepare("
    SELECT c.courseID, c.name, c.code 
    FROM courses c 
    JOIN student_courses sc ON c.courseID = sc.courseID 
    WHERE sc.studentID = ?
    ORDER BY c.name
");
$registeredStmt->execute([$studentID]);
$registeredCourses = $registeredStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>DigiConnect - Course Registration</title>
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .sidebar { background: linear-gradient(180deg, #E30613, #c70410) !important; }
        .btn-danger { background-color: #E30613 !important; border-color: #E30613 !important; }
        .text-danger { color: #E30613 !important; }
    </style>
</head>
<body id="page-top">
<div id="wrapper">
    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard.php">
            <div class="sidebar-brand-text mx-3" style="font-size: 1.4rem;">DigiConnect <sup>4.0</sup></div>
        </a>
        <hr class="sidebar-divider my-0">

        <li class="nav-item">
            <a class="nav-link" href="dashboard.php">
                <i class="fas fa-home"></i>
                <span>My Dashboard</span>
            </a>
        </li>

        <li class="nav-item active">
            <a class="nav-link" href="course_registration.php">
                <i class="fas fa-book"></i>
                <span>Course Registration</span>
            </a>
        </li>

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

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-toggle="dropdown">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= htmlspecialchars($name) ?></span>
                            <img class="img-profile rounded-circle" src="../../img/undraw_profile.svg">
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="../../logout.php">Logout</a>
                        </div>
                    </li>
                </ul>
            </nav>

            <div class="container-fluid">
                <!-- Yellow Welcome Banner -->
                <div class="container-fluid py-5 mb-4" style="background-color: #FFC107; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <img src="../../img/digiconnect-logo.jpg" alt="DigiConnect" style="height: 90px;">
                        </div>
                        <div class="col-md-10">
                            <h2 style="color: #000;">Course Registration</h2>
                            <p style="color: #000;">Choose and register for your courses here.</p>
                        </div>
                    </div>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <!-- Already Registered Courses -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-danger text-white">
                        <h6 class="m-0 font-weight-bold">My Registered Courses</h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($registeredCourses)): ?>
                            <p class="text-center text-muted">You have not registered for any courses yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Course Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($registeredCourses as $c): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($c['code']) ?></td>
                                            <td><?= htmlspecialchars($c['name']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Available Courses to Register -->
                <div class="card shadow">
                    <div class="card-header py-3 bg-danger text-white">
                        <h6 class="m-0 font-weight-bold">Available Courses</h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($allCourses)): ?>
                            <p class="text-center text-muted">No courses available at the moment.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Course Name</th>
                                            <th>Department</th>
                                            <th>Teacher</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($allCourses as $c): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($c['code']) ?></td>
                                            <td><?= htmlspecialchars($c['name']) ?></td>
                                            <td><?= htmlspecialchars($c['deptName'] ?: 'N/A') ?></td>
                                            <td><?= htmlspecialchars($c['teacherName'] ?: 'Not Assigned') ?></td>
                                            <td>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="courseID" value="<?= $c['courseID'] ?>">
                                                    <input type="hidden" name="register_course" value="1">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        Register
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php include '../../include/footer.php'; ?>
    </div>
</div>

<script src="../../vendor/jquery/jquery.min.js"></script>
<script src="../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../js/sb-admin-2.min.js"></script>
</body>
</html>