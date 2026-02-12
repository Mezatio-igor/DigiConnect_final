<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] != 'Teacher') {
    header('Location: ../../login.php');
    exit();
}

include '../../config/db_connect.php';

$teacherID = $_SESSION['userID'];
$name = $_SESSION['name'];

// Fetch teacher’s assigned courses
$courseStmt = $conn->prepare("SELECT c.courseID, c.name, c.code, d.name AS deptName 
                              FROM courses c 
                              LEFT JOIN departments d ON c.departmentID = d.departmentID 
                              WHERE c.teacherID = ? 
                              ORDER BY c.name");
$courseStmt->execute([$teacherID]);
$courses = $courseStmt->fetchAll();

// Fetch teacher’s timetables (only for their courses)
$timetableStmt = $conn->prepare("SELECT t.*, c.name AS courseName, c.code AS courseCode 
                                 FROM timetables t 
                                 JOIN courses c ON t.courseID = c.courseID 
                                 WHERE c.teacherID = ? 
                                 ORDER BY FIELD(t.dayOfWeek, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), t.startTime");
$timetableStmt->execute([$teacherID]);
$timetables = $timetableStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>DigiConnect - Teacher Dashboard</title>
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
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../index.php">
            <div class="sidebar-brand-text mx-3" style="font-size: 1.4rem;">DigiConnect <sup>4.0</sup></div>
        </a>
        <hr class="sidebar-divider my-0">

        <li class="nav-item active">
            <a class="nav-link" href="dashboard.php">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
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
                <div class="container-fluid py-5 mb-4" style="background-color: #FFC107; border-radius: 15px;">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <img src="../../img/digiconnect-logo.png" style="height: 90px;">
                        </div>
                        <div class="col-md-10">
                            <h2 style="color: #000;">Welcome back, <?= htmlspecialchars($name) ?>!</h2>
                            <p style="color: #000;">You are logged in as <strong>Teacher</strong></p>
                        </div>
                    </div>
                </div>

                <!-- Assigned Courses -->
                <div class="row">
                    <div class="col-lg-12 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3 bg-danger text-white">
                                <h6 class="m-0 font-weight-bold">My Assigned Courses</h6>
                            </div>
                            <div class="card-body">
                                <?php if (empty($courses)): ?>
                                    <p class="text-center">No courses assigned yet. Contact your SchoolAdmin.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Code</th>
                                                    <th>Course Name</th>
                                                    <th>Department</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($courses as $c): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($c['code']) ?></td>
                                                    <td><?= htmlspecialchars($c['name']) ?></td>
                                                    <td><?= htmlspecialchars($c['deptName']) ?></td>
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

                <!-- My Timetables -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card shadow">
                            <div class="card-header py-3 bg-danger text-white">
                                <h6 class="m-0 font-weight-bold">My Timetables</h6>
                            </div>
                            <div class="card-body">
                                <?php if (empty($timetables)): ?>
                                    <p class="text-center">No timetables for your courses yet.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Course</th>
                                                    <th>Day</th>
                                                    <th>Time</th>
                                                    <th>Type</th>
                                                    <th>Room</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($timetables as $t): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($t['courseName']) ?> (<?= htmlspecialchars($t['courseCode']) ?>)</td>
                                                    <td><?= htmlspecialchars($t['dayOfWeek']) ?></td>
                                                    <td><?= $t['startTime'] ?> - <?= $t['endTime'] ?></td>
                                                    <td><?= htmlspecialchars($t['type']) ?></td>
                                                    <td><?= htmlspecialchars($t['room'] ?: '-') ?></td>
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