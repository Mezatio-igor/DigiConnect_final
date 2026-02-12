<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] != 'SchoolAdmin') {
    header('Location: ../../login.php');
    exit();
}

include __DIR__ . '/../../config/db_connect.php';

$universityID = $_SESSION['universityID'];
$editID = isset($_GET['edit']) ? $_GET['edit'] : null;
$course = null;

if ($editID) {
    $stmt = $conn->prepare("SELECT * FROM courses WHERE courseID = ? AND universityID = ?");
    $stmt->execute([$editID, $universityID]);
    $course = $stmt->fetch();
    if (!$course) die("Course not found.");
}

// Departments and Teachers for dropdown
$deptStmt = $conn->prepare("SELECT * FROM departments WHERE universityID = ?");
$deptStmt->execute([$universityID]);
$departments = $deptStmt->fetchAll();

$teacherStmt = $conn->prepare("SELECT userID, name FROM users WHERE role = 'Teacher' AND universityID = ?");
$teacherStmt->execute([$universityID]);
$teachers = $teacherStmt->fetchAll();

// Save course
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name        = trim($_POST['name']);
    $code        = trim($_POST['code']);
    $deptID      = $_POST['departmentID'];
    $teacherID   = $_POST['teacherID'] ?: null;
    $credits     = (int)$_POST['credits'];

    if (empty($name) || empty($code) || empty($deptID)) {
        $error = "Required fields missing";
    } else {
        if ($editID) {
            $stmt = $conn->prepare("UPDATE courses SET name=?, code=?, departmentID=?, teacherID=?, credits=? WHERE courseID=? AND universityID=?");
            $stmt->execute([$name, $code, $deptID, $teacherID, $credits, $editID, $universityID]);
        } else {
            $stmt = $conn->prepare("INSERT INTO courses (name, code, departmentID, teacherID, credits, universityID) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$name, $code, $deptID, $teacherID, $credits, $universityID]);
        }
        header("Location: dashboard.php?msg=saved");
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
    <title><?= $editID ? 'Edit' : 'Add' ?> Course</title>
    <!-- Bootstrap CSS -->
    <link href="../../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">

            <div class="container-fluid">
                <h1 class="h3 mb-4"><?= $editID ? 'Edit' : 'Add New' ?> Course</h1>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <div class="card shadow">
                    <div class="card-header py-3 bg-danger text-white">
                        <h6 class="m-0 font-weight-bold">Course Details</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Course Name</label>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($course ? $course['name'] : '') ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label>Course Code</label>
                                    <input type="text" name="code" class="form-control" value="<?= htmlspecialchars($course ? $course['code'] : '') ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label>Credits</label>
                                    <input type="number" name="credits" class="form-control" value="<?= $course ? $course['credits'] : 3 ?>" min="1" required>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label>Department</label>
                                    <select name="departmentID" class="form-control" required>
                                        <option value="">Select Department</option>
                                        <?php foreach ($departments as $d): ?>
                                            <option value="<?= $d['departmentID'] ?>" <?= ($course && $course['departmentID'] == $d['departmentID']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($d['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label>Assigned Teacher (optional)</label>
                                    <select name="teacherID" class="form-control">
                                        <option value="">-- Not Assigned --</option>
                                        <?php foreach ($teachers as $t): ?>
                                            <option value="<?= $t['userID'] ?>" <?= ($course && $course['teacherID'] == $t['userID']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($t['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-danger btn-lg">Save Course</button>
                                <a href="dashboard.php" class="btn btn-secondary btn-lg">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../../vendor/jquery/jquery.min.js"></script>
<script src="../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../js/sb-admin-2.min.js"></script>
</body>
</html>