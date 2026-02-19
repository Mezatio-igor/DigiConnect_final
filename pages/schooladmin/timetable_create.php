<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'SchoolAdmin') {
    header('Location: ../../login.php');
    exit();
}

include '../../config/db_connect.php';

$universityID = $_SESSION['universityID'];
$schoolAdminID = $_SESSION['userID'];

$success = '';
$error = '';

// Determine if we are in edit mode
$editID = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
$entry = null;

if ($editID) {
    $stmt = $conn->prepare("
        SELECT t.* 
        FROM timetables t 
        WHERE t.timetableID = ? AND t.universityID = ? AND t.createdBy = ?
    ");
    $stmt->execute([$editID, $universityID, $schoolAdminID]);
    $entry = $stmt->fetch();
    
    if (!$entry) {
        $error = "Timetable entry not found or you don't have permission to edit it.";
        $editID = null;
    }
}

// Fetch all courses in this university
$courseStmt = $conn->prepare("
    SELECT c.courseID, c.code, c.name, d.name AS deptName 
    FROM courses c 
    LEFT JOIN departments d ON c.departmentID = d.departmentID 
    WHERE c.universityID = ?
    ORDER BY c.name
");
$courseStmt->execute([$universityID]);
$courses = $courseStmt->fetchAll();

// Handle form submission (add OR edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseID   = isset($_POST['courseID'])   ? (int)$_POST['courseID']   : null;
    $day        = isset($_POST['dayOfWeek'])  ? $_POST['dayOfWeek']        : null;
    $startTime  = isset($_POST['startTime'])  ? $_POST['startTime']        : null;
    $endTime    = isset($_POST['endTime'])    ? $_POST['endTime']          : null;
    $type       = isset($_POST['type'])       ? $_POST['type']             : null;
    $room       = isset($_POST['room'])       ? trim($_POST['room'])       : '';

    if (!$courseID || !$day || !$startTime || !$endTime || !$type) {
        $error = "All required fields must be filled.";
    } elseif (strtotime($endTime) <= strtotime($startTime)) {
        $error = "End time must be after start time.";
    } else {
        if ($editID) {
            // UPDATE existing entry
            $stmt = $conn->prepare("
                UPDATE timetables 
                SET courseID = ?, dayOfWeek = ?, startTime = ?, endTime = ?, type = ?, room = ?
                WHERE timetableID = ? AND universityID = ? AND createdBy = ?
            ");
            $result = $stmt->execute([$courseID, $day, $startTime, $endTime, $type, $room, $editID, $universityID, $schoolAdminID]);
        } else {
            // INSERT new entry
            $stmt = $conn->prepare("
                INSERT INTO timetables 
                (courseID, dayOfWeek, startTime, endTime, type, room, createdBy, universityID) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $result = $stmt->execute([$courseID, $day, $startTime, $endTime, $type, $room, $schoolAdminID, $universityID]);
        }

        if ($result) {
            $success = $editID ? "Timetable entry updated successfully!" : "Timetable entry added successfully!";
            // Clear edit mode after success
            $editID = null;
            $entry = null;
        } else {
            $error = "Failed to save entry. Try again.";
        }
    }
}

// Fetch all timetable entries for this university
$timetableStmt = $conn->prepare("
    SELECT t.*, c.code, c.name AS courseName, u.name AS creatorName 
    FROM timetables t 
    JOIN courses c ON t.courseID = c.courseID 
    JOIN users u ON t.createdBy = u.userID 
    WHERE t.universityID = ?
    ORDER BY FIELD(t.dayOfWeek, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), t.startTime
");
$timetableStmt->execute([$universityID]);
$timetables = $timetableStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= $editID ? 'Edit' : 'Create' ?> Timetable - DigiConnect</title>
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
    <?php include '../../include/sidebar.php'; ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?php include '../../include/topbar.php'; ?>

            <div class="container-fluid">
                <!-- Yellow Welcome Banner -->
                <div class="container-fluid py-5 mb-4" style="background-color: #FFC107; border-radius: 15px;">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <img src="../../img/digiconnect-logo.png" alt="Logo" style="height: 90px;">
                        </div>
                        <div class="col-md-10">
                            <h2 style="color: #000;"><?= $editID ? 'Edit Timetable Entry' : 'Create & Manage Timetables' ?></h2>
                            <p style="color: #000;">Manage class and exam schedules for your university.</p>
                        </div>
                    </div>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <!-- Add / Edit Form -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-danger text-white">
                        <h6 class="m-0 font-weight-bold"><?= $editID ? 'Edit Timetable Entry' : 'Add New Timetable Entry' ?></h6>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($editID): ?>
                                <input type="hidden" name="editID" value="<?= $editID ?>">
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-4">
                                    <label>Course *</label>
                                    <select name="courseID" class="form-control" required>
                                        <option value="">-- Select Course --</option>
                                        <?php foreach ($courses as $c): ?>
                                            <option value="<?= $c['courseID'] ?>" <?= ($entry && $entry['courseID'] == $c['courseID']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($c['code']) ?> - <?= htmlspecialchars($c['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Day *</label>
                                    <select name="dayOfWeek" class="form-control" required>
                                        <?php
                                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                                        foreach ($days as $d): ?>
                                            <option <?= ($entry && $entry['dayOfWeek'] == $d) ? 'selected' : '' ?>><?= $d ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Start Time *</label>
                                    <input type="time" name="startTime" class="form-control" 
                                           value="<?= $entry ? $entry['startTime'] : '' ?>" required>
                                </div>
                                <div class="col-md-2">
                                    <label>End Time *</label>
                                    <input type="time" name="endTime" class="form-control" 
                                           value="<?= $entry ? $entry['endTime'] : '' ?>" required>
                                </div>
                                <div class="col-md-2">
                                    <label>Type *</label>
                                    <select name="type" class="form-control" required>
                                        <?php
                                        $types = ['Lecture', 'Tutorial', 'Lab', 'Exam'];
                                        foreach ($types as $t): ?>
                                            <option <?= ($entry && $entry['type'] == $t) ? 'selected' : '' ?>><?= $t ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label>Room (optional)</label>
                                    <input type="text" name="room" class="form-control" 
                                           value="<?= htmlspecialchars(isset($entry['room']) ? $entry['room'] : '') ?>">
                                </div>
                                <div class="col-md-6 mt-4">
                                    <button type="submit" class="btn btn-danger btn-lg">
                                        <?= $editID ? 'Update Entry' : 'Add Timetable Entry' ?>
                                    </button>
                                    <?php if ($editID): ?>
                                        <a href="timetable_create.php" class="btn btn-secondary btn-lg">Cancel Edit</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Existing Timetables List -->
                <div class="card shadow">
                    <div class="card-header py-3 bg-danger text-white">
                        <h6 class="m-0 font-weight-bold">Current Timetables</h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($timetables)): ?>
                            <p class="text-center text-muted">No timetable entries yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Course</th>
                                            <th>Day</th>
                                            <th>Time</th>
                                            <th>Type</th>
                                            <th>Room</th>
                                            <th>Created By</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($timetables as $t): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($t['courseName']) ?> (<?= htmlspecialchars($t['code']) ?>)</td>
                                            <td><?= htmlspecialchars($t['dayOfWeek']) ?></td>
                                            <td><?= $t['startTime'] ?> - <?= $t['endTime'] ?></td>
                                            <td><?= htmlspecialchars($t['type']) ?></td>
                                            <td><?= htmlspecialchars($t['room'] ?: 'N/A') ?></td>
                                            <td><?= htmlspecialchars($t['creatorName']) ?></td>
                                            <td>
                                                <a href="?edit=<?= $t['timetableID'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="?delete=<?= $t['timetableID'] ?>" class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Are you sure you want to delete this entry?')">Delete</a>
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