<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'SuperAdmin') {
    header('Location: ../../login.php');
    exit();
}

include '../../config/db_connect.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title   = trim($_POST['title']);
    $message = trim($_POST['message']);
    $target  = isset($_POST['target']) ? $_POST['target'] : 'all'; // all, schooladmins, teachers, students

    if (empty($title) || empty($message)) {
        $error = "Title and message are required";
    } else {
        // For now, just save to a notifications table (create it first)
        $stmt = $conn->prepare("INSERT INTO notifications (title, message, target, createdBy) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $message, $target, $_SESSION['userID']]);

        $success = "Notification sent successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Send Notification - DigiConnect</title>
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
                <h1 class="h3 mb-4 text-gray-800">Send System Notification</h1>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="card shadow">
                    <div class="card-header py-3 bg-danger text-white">
                        <h6 class="m-0 font-weight-bold">New Notification</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Message</label>
                                <textarea name="message" class="form-control" rows="5" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Send to</label>
                                <select name="target" class="form-control">
                                    <option value="all">All Users</option>
                                    <option value="schooladmins">SchoolAdmins Only</option>
                                    <option value="teachers">Teachers Only</option>
                                    <option value="students">Students Only</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-danger btn-lg">Send Notification</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php include '../../include/footer.php'; ?>
    </div>
</div>
</body>
</html>