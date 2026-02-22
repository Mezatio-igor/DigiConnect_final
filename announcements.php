<?php
session_start();

// Only SuperAdmin can access
if (!isset($_SESSION['userID']) || $_SESSION['role'] != 'SuperAdmin') {
    header('Location: login.php');
    exit();
}

include 'config/db_connect.php';
$name = $_SESSION['name'];

$success_msg = '';
$error_msg = '';

// Handle announcement edit fetch
$edit_announcement = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM announcements WHERE announcementID = ?");
    $stmt->execute([$edit_id]);
    $edit_announcement = $stmt->fetch();
    if (!$edit_announcement) {
        $error_msg = "Announcement not found.";
    }
}

// Handle announcement update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_announcement'])) {
    $edit_id = intval($_POST['announcementID']);
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $target_role = isset($_POST['target']) ? $_POST['target'] : 'all';
    if (empty($title) || empty($message)) {
        $error_msg = "Title and message are required for update.";
    } else {
        $stmt = $conn->prepare("UPDATE announcements SET title = ?, message = ?, targetRole = ? WHERE announcementID = ?");
        if ($stmt->execute([$title, $message, $target_role, $edit_id])) {
            $success_msg = "Announcement updated successfully!";
            header('Location: announcements.php');
            exit();
        } else {
            $error_msg = "Failed to update announcement.";
        }
    }
}

// Handle announcement deletion
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM announcements WHERE announcementID = ?");
    if ($stmt->execute([$delete_id])) {
        $success_msg = "Announcement deleted successfully!";
        // Redirect to avoid resubmission
        header('Location: announcements.php');
        exit();
    } else {
        $error_msg = "Failed to delete announcement.";
    }
}

// Handle announcement sending
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_announcement'])) {
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $target_role = isset($_POST['target']) ? $_POST['target'] : 'all'; // default to 'all' if missing
    
    if (empty($title) || empty($message)) {
        $error_msg = "Title and message are required";
    } else {
        // Create announcements table if it doesn't exist
        $conn->exec("CREATE TABLE IF NOT EXISTS announcements (
            announcementID INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            targetRole VARCHAR(50),
            createdBy INT NOT NULL,
            createdDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (createdBy) REFERENCES users(userID)
        )");
        
        $stmt = $conn->prepare("INSERT INTO announcements (title, message, targetRole, createdBy) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$title, $message, $target_role, $_SESSION['userID']])) {
            $success_msg = "Announcement sent successfully!";
        } else {
            $error_msg = "Failed to send announcement. Try again.";
        }
    }
}

// Fetch recent announcements
$announcementsStmt = $conn->query("
    SELECT a.*, u.name AS senderName 
    FROM announcements a 
    JOIN users u ON a.createdBy = u.userID 
    ORDER BY a.createdDate DESC 
    LIMIT 20
");
$announcements = $announcementsStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>DigiConnect - Send Announcements</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <style>
    :root {
        --dc-red: #E30613;
        --dc-yellow: #FFC107;
        --dc-dark-red: #C70410;
    }

    .bg-gradient-primary {
        background: linear-gradient(180deg, var(--dc-red) 0%, var(--dc-dark-red) 100%) !important;
    }

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

    .nav-item.active .nav-link {
        background-color: var(--dc-yellow);
        color: #000 !important;
    }

    .card-header .text-primary {
        color: var(--dc-red) !important;
    }
    </style>

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion">

            <!-- Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-text mx-3" style="font-size: 1.4rem;">DigiConnect <sup>4.0</sup></div>
            </a>

            <hr class="sidebar-divider my-0">

            <li class="nav-item">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>SuperAdmin Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="universities.php">
                    <i class="fas fa-university"></i>
                    <span>Manage Universities</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="register_schooladmin.php">
                    <i class="fas fa-user-shield"></i>
                    <span>Add SchoolAdmin</span>
                </a>
            </li>

            <li class="nav-item active">
                <a class="nav-link" href="announcements.php">
                    <i class="fas fa-bell"></i>
                    <span>Send Announcements</span>
                </a>
            </li>

            <hr class="sidebar-divider d-none d-md-block">
        </ul>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-toggle="dropdown">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo htmlspecialchars($name); ?></span>
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

                <div class="container-fluid">

                    <!-- Yellow Welcome Banner -->
                    <div class="container-fluid py-5 mb-4" style="background-color: #FFC107; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                <img src="img/digiconnect-logo.jpg" alt="DigiConnect Logo" style="height: 90px;">
                            </div>
                            <div class="col-md-9">
                                <h2 class="mb-1" style="color: #000; font-weight: bold;">
                                    Send System Announcements
                                </h2>
                                <p class="mb-0" style="color: #000;">
                                    Communicate important updates, notices, and announcements to all users
                                </p>
                            </div>
                        </div>
                    </div>

                    <?php if ($success_msg): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($success_msg); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error_msg): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error_msg); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Send Announcement Form -->
                    <?php if ($edit_announcement): ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-warning text-dark">
                            <h6 class="m-0 font-weight-bold">Edit Announcement</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="announcementID" value="<?php echo htmlspecialchars($edit_announcement['announcementID']); ?>">
                                <div class="form-group">
                                    <label for="title">Announcement Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($edit_announcement['title']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="message">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="message" name="message" rows="6" required><?php echo htmlspecialchars($edit_announcement['message']); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="target_role">Send To <span class="text-danger">*</span></label>
                                    <select name="target" class="form-control">
                                        <option value="all" <?php if ($edit_announcement['targetRole'] == 'all') echo 'selected'; ?>>All Users</option>
                                        <option value="schooladmins" <?php if ($edit_announcement['targetRole'] == 'schooladmins') echo 'selected'; ?>>SchoolAdmins Only</option>
                                        <option value="teachers" <?php if ($edit_announcement['targetRole'] == 'teachers') echo 'selected'; ?>>Teachers Only</option>
                                        <option value="students" <?php if ($edit_announcement['targetRole'] == 'students') echo 'selected'; ?>>Students Only</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <button type="submit" name="update_announcement" class="btn btn-warning">
                                        <i class="fas fa-save"></i> Update Announcement
                                    </button>
                                    <a href="announcements.php" class="btn btn-secondary ml-2">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-danger text-white">
                            <h6 class="m-0 font-weight-bold">Create New Announcement</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="form-group">
                                    <label for="title">Announcement Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" placeholder="e.g., System Maintenance Notice" required>
                                </div>
                                <div class="form-group">
                                    <label for="message">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="message" name="message" rows="6" placeholder="Enter announcement message..." required></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="target_role">Send To <span class="text-danger">*</span></label>
                                    <select name="target" class="form-control">
                                        <option value="all">All Users</option>
                                        <option value="schooladmins">SchoolAdmins Only</option>
                                        <option value="teachers">Teachers Only</option>
                                        <option value="students">Students Only</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <button type="submit" name="send_announcement" class="btn btn-danger">
                                        <i class="fas fa-paper-plane"></i> Send Announcement
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Recent Announcements -->
                    <div class="card shadow">
                        <div class="card-header py-3 bg-danger text-white">
                            <h6 class="m-0 font-weight-bold">Recent Announcements</h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($announcements)): ?>
                                <p class="text-center text-muted">No announcements sent yet.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Title</th>
                                                <th>Target Audience</th>
                                                <th>Sent By</th>
                                                <th>Date</th>
                                                   <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($announcements as $ann): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($ann['title']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars(substr($ann['message'], 0, 100)); ?>...</small>
                                                </td>
                                                <td><span class="badge badge-primary"><?php echo htmlspecialchars($ann['targetRole']); ?></span></td>
                                                <td><?php echo htmlspecialchars($ann['senderName']); ?></td>
                                                <td><?php echo date('M d, Y H:i', strtotime($ann['createdDate'])); ?></td>
                                                   <td>
                                                       <a href="announcements.php?edit=<?php echo $ann['announcementID']; ?>" class="btn btn-sm btn-warning mr-1"><i class="fas fa-edit"></i> Edit</a>
                                                       <a href="announcements.php?delete=<?php echo $ann['announcementID']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this announcement?');"><i class="fas fa-trash"></i> Delete</a>
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

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>DigiConnect © 2025 - All Rights Reserved</span>
                    </div>
                </div>
            </footer>

        </div>

    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>

</body>

</html>
