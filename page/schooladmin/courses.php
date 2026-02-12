<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] != 'SchoolAdmin') {
    header('Location: ../../login.php');
    exit();
}

include '../../config/db_connect.php';

$schoolAdminID = $_SESSION['userID'];
$universityID  = $_SESSION['universityID'];

// Fetch departments in this university
$deptStmt = $conn->prepare("SELECT * FROM departments WHERE universityID = ?");
$deptStmt->execute([$universityID]);
$departments = $deptStmt->fetchAll();

// Fetch teachers in this university (role = Teacher)
$teacherStmt = $conn->prepare("SELECT userID, name FROM users WHERE role = 'Teacher' AND universityID = ?");
$teacherStmt->execute([$universityID]);
$teachers = $teacherStmt->fetchAll();

// Fetch all courses
$courseStmt = $conn->prepare("SELECT c.*, d.name AS deptName, t.name AS teacherName 
                              FROM courses c 
                              LEFT JOIN departments d ON c.departmentID = d.departmentID 
                              LEFT JOIN users t ON c.teacherID = t.userID 
                              WHERE c.universityID = ? 
                              ORDER BY c.name");
$courseStmt->execute([$universityID]);
$courses = $courseStmt->fetchAll();

// Delete course
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteID = $_GET['delete'];
    $delStmt = $conn->prepare("DELETE FROM courses WHERE courseID = ? AND universityID = ?");
    $delStmt->execute([$deleteID, $universityID]);
    header("Location: courses.php?msg=deleted");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>DigiConnect - Manage Courses</title>
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">
    <?php include '../../include/sidebar.php'; ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?php include '../../include/topbar.php'; ?>

            <div class="container-fluid">
                <h1 class="h3 mb-4 text-gray-800">Manage Courses</h1>

                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert alert-success">Action completed!</div>
                <?php endif; ?>

                <!-- Add / Edit Course Form -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-danger text-white">
                        <h6 class="m-0 font-weight-bold">Add New Course</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="course_add.php">
                            <div class="row">
                                <div class="col-md-4">
                                    <label>Course Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <label>Course Code</label>
                                    <input type="text" name="code" class="form-control" required placeholder="e.g., CSC 101">
                                </div>
                                <div class="col-md-3">
                                    <label>Department</label>
                                    <select name="departmentID" class="form-control" required>
                                        <option value="">Select Department</option>
                                        <?php foreach ($departments as $d): ?>
                                            <option value="<?= $d['departmentID'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Credits</label>
                                    <input type="number" name="credits" class="form-control" value="3" min="1" required>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label>Assign Teacher (optional)</label>
                                    <select name="teacherID" class="form-control">
                                        <option value="">-- Not Assigned Yet --</option>
                                        <?php foreach ($teachers as $t): ?>
                                            <option value="<?= $t['userID'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mt-4">
                                    <button type="submit" class="btn btn-danger btn-lg">Add Course</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Courses List -->
                <div class="card shadow">
                    <div class="card-header py-3 bg-danger text-white">
                        <h6 class="m-0 font-weight-bold">Current Courses</h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($courses)): ?>
                            <p>No courses added yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Department</th>
                                            <th>Assigned Teacher</th>
                                            <th>Credits</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($courses as $c): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($c['code']) ?></td>
                                            <td><?= htmlspecialchars($c['name']) ?></td>
                                            <td><?= htmlspecialchars($c['deptName']) ?></td>
                                            <td><?= htmlspecialchars($c['teacherName'] ?: 'Not Assigned') ?></td>
                                            <td><?= $c['credits'] ?></td>
                                            <td>
                                                <a href="course_add.php?edit=<?= $c['courseID'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="?delete=<?= $c['courseID'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this course?')">Delete</a>
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
</body>
</html>