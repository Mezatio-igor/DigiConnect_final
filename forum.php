<?php
session_start();
if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

include 'config/db_connect.php';
$name = $_SESSION['name'];
$userID = $_SESSION['userID'];

// Create new thread
if (isset($_POST['new_thread'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    if ($title && $description) {
        $stmt = $conn->prepare("INSERT INTO forums (title, description, createdBy) VALUES (?, ?, ?)");
        $stmt->execute([$title, $description, $userID]);
    }
}

// Post reply
if (isset($_POST['reply']) && isset($_POST['forumID'])) {
    $forumID = $_POST['forumID'];
    $message = trim($_POST['message']);
    if ($message) {
        $stmt = $conn->prepare("INSERT INTO forum_replies (forumID, message, repliedBy) VALUES (?, ?, ?)");
        $stmt->execute([$forumID, $message, $userID]);
    }
}

// Fetch all threads with reply count
$stmt = $conn->query("SELECT f.*, u.name AS creatorName,
                      (SELECT COUNT(*) FROM forum_replies r WHERE r.forumID = f.forumID) AS replyCount
                      FROM forums f JOIN users u ON f.createdBy = u.userID
                      ORDER BY f.createdDate DESC");
$threads = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>DigiConnect - Community Forum</title>
    <!-- Bootstrap CSS -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .sidebar { background: linear-gradient(180deg, #E30613, #c70410) !important; }
        .btn-primary, .btn-danger { background-color: #E30613 !important; border-color: #E30613 !important; }
        .text-primary { color: #E30613 !important; }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-text mx-3" style="font-size: 1.4rem;">DigiConnect <sup>4.0</sup></div>
            </a>
            <hr class="sidebar-divider">
            <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="resources.php"><i class="fas fa-globe-africa"></i> Global Resources</a></li>
            <li class="nav-item active"><a class="nav-link" href="forum.php"><i class="fas fa-comments"></i> Community Forum</a></li>
            <hr class="sidebar-divider d-none d-md-block">
        </ul>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Topbar -->
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
                    <!-- Yellow Banner -->
                    <div class="container-fluid py-5 mb-4" style="background-color: #FFC107; border-radius: 15px;">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center">
                                <img src="img/digiconnect-logo.jpg" style="height: 90px;">
                            </div>
                            <div class="col-md-10">
                                <h2 style="color: #000;">Community Forum</h2>
                                <p style="color: #000;">Discuss, ask questions, and collaborate with students from all universities!</p>
                            </div>
                        </div>
                    </div>

                    <!-- New Thread Form -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-danger text-white">
                            <h6 class="m-0 font-weight-bold">Start New Discussion</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="new_thread" value="1">
                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" name="title" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Message</label>
                                    <textarea name="description" class="form-control" rows="4" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger">Create Thread</button>
                            </form>
                        </div>
                    </div>

                    <!-- Threads List -->
                    <div class="card shadow">
                        <div class="card-header py-3 bg-danger text-white">
                            <h6 class="m-0 font-weight-bold">Discussion Threads</h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($threads)): ?>
                                <p>No discussions yet. Start the first one!</p>
                            <?php else: ?>
                                <?php foreach ($threads as $thread): ?>
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <strong><?php echo htmlspecialchars($thread['title']); ?></strong>
                                        <small class="text-muted">by <?php echo htmlspecialchars($thread['creatorName']); ?> • <?php echo $thread['createdDate']; ?> • <?php echo $thread['replyCount']; ?> replies</small>
                                    </div>
                                    <div class="card-body">
                                        <p><?php echo nl2br(htmlspecialchars($thread['description'])); ?></p>

                                        <!-- Replies -->
                                        <?php
                                        $replyStmt = $conn->prepare("SELECT fr.*, u.name AS replierName FROM forum_replies fr JOIN users u ON fr.repliedBy = u.userID WHERE forumID = ? ORDER BY replyDate");
                                        $replyStmt->execute([$thread['forumID']]);
                                        $replies = $replyStmt->fetchAll();
                                        ?>
                                        <?php if (!empty($replies)): ?>
                                            <hr>
                                            <?php foreach ($replies as $reply): ?>
                                            <div class="media mb-3">
                                                <img class="mr-3 rounded-circle" src="img/undraw_profile.svg" style="width:40px;">
                                                <div class="media-body">
                                                    <h6 class="mt-0"><?php echo htmlspecialchars($reply['replierName']); ?></h6>
                                                    <p><?php echo nl2br(htmlspecialchars($reply['message'])); ?></p>
                                                    <small class="text-muted"><?php echo $reply['replyDate']; ?></small>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>

                                        <!-- Reply Form -->
                                        <form method="POST" class="mt-3">
                                            <input type="hidden" name="forumID" value="<?php echo $thread['forumID']; ?>">
                                            <input type="hidden" name="reply" value="1">
                                            <div class="form-group">
                                                <textarea name="message" class="form-control" rows="2" placeholder="Write a reply..." required></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-danger">Post Reply</button>
                                        </form>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>DigiConnect © 2025</span>
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