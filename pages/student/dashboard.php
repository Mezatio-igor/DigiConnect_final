<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] != 'Student') {
    header('Location: ../../login.php');
    exit();
}

include '../../config/db_connect.php';

$studentID = $_SESSION['userID'];
$name = $_SESSION['name'];
$universityID = $_SESSION['universityID'];

// Get student university name
$uniStmt = $conn->prepare("SELECT name FROM universities WHERE universityID = ?");
$uniStmt->execute([$universityID]);
$university = $uniStmt->fetch();

// Get student’s registered courses
$courseStmt = $conn->prepare("
    SELECT c.courseID, c.name, c.code 
    FROM courses c 
    JOIN student_courses sc ON c.courseID = sc.courseID 
    WHERE sc.studentID = ?
    ORDER BY c.name
");
$courseStmt->execute([$studentID]);
$courses = $courseStmt->fetchAll();

// Get student’s timetables (only for registered courses)
$timetableStmt = $conn->prepare("
    SELECT t.*, c.name AS courseName, c.code AS courseCode 
    FROM timetables t 
    JOIN courses c ON t.courseID = c.courseID 
    JOIN student_courses sc ON c.courseID = sc.courseID 
    WHERE sc.studentID = ? 
    ORDER BY FIELD(t.dayOfWeek, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), t.startTime
");
$timetableStmt->execute([$studentID]);
$timetables = $timetableStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>DigiConnect - Student Dashboard</title>
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
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../../index.php">
            <div class="sidebar-brand-text mx-3" style="font-size: 1.4rem;">DigiConnect <sup>4.0</sup></div>
        </a>
        <hr class="sidebar-divider my-0">

        <li class="nav-item active">
            <a class="nav-link" href="dashboard.php">
                <i class="fas fa-home"></i>
                <span>Home</span>
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

        <li class="nav-item">
            <a class="nav-link" href="courses_registration.php">
                <i class="fas fa-book"></i>
                <span>Register Courses</span>
            </a>
        </li>

        <hr class="sidebar-divider d-none d-md-block">
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <!-- Topbar -->
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
                            <h2 class="mb-1" style="color: #000; font-weight: bold;">
                                Welcome back, <?= htmlspecialchars($name) ?>!
                            </h2>
                            <p class="mb-0" style="color: #000; font-size: 1.2rem;">
                                Your Timetable & Academic Overview
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Registered Courses -->
                <div class="row mb-4">
                    <div class="col-lg-12">
                        <div class="card shadow">
                            <div class="card-header py-3 bg-danger text-white">
                                <h6 class="m-0 font-weight-bold">My Registered Courses</h6>
                            </div>
                            <div class="card-body">
                                <?php if (empty($courses)): ?>
                                    <p class="text-center text-muted">You are not registered for any courses yet.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Code</th>
                                                    <th>Course Name</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($courses as $c): ?>
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
                    </div>
                </div>

                <!-- My Timetable -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card shadow">
                            <div class="card-header py-3 bg-danger text-white">
                                <h6 class="m-0 font-weight-bold">My Timetable</h6>
                            </div>
                            <div class="card-body">
                                <?php if (empty($timetables)): ?>
                                    <p class="text-center text-muted">No timetable entries yet for your registered courses.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Day</th>
                                                    <th>Time</th>
                                                    <th>Course</th>
                                                    <th>Type</th>
                                                    <th>Room</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($timetables as $t): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($t['dayOfWeek']) ?></td>
                                                    <td><?= $t['startTime'] ?> - <?= $t['endTime'] ?></td>
                                                    <td><?= htmlspecialchars($t['courseName']) ?> (<?= htmlspecialchars($t['courseCode']) ?>)</td>
                                                    <td><?= htmlspecialchars($t['type']) ?></td>
                                                    <td><?= htmlspecialchars($t['room'] ?: 'N/A') ?></td>
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