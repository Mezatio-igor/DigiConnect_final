<?php
session_start();
if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

include 'config/db_connect.php';
$name = $_SESSION['name'];
$role = $_SESSION['role'];
$universityID = isset($_SESSION['universityID']) ? $_SESSION['universityID'] : null;

// Upload handling
$uploadMsg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $role == 'Student') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];

    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $fileName = time() . '_' . basename($_FILES['file']['name']);
        $target = "uploads/" . $fileName;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
            $stmt = $conn->prepare("INSERT INTO resources (title, description, category, filePath, uploadedBy, universityID) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $category, $target, $_SESSION['userID'], $universityID]);
            $uploadMsg = "Resource uploaded successfully!";
        }
    }
}

// Fetch all resources (public) - AFTER upload handling
$stmt = $conn->query("SELECT r.*, u.name AS uploaderName, univ.name AS uniName 
                      FROM resources r 
                      JOIN users u ON r.uploadedBy = u.userID 
                      JOIN universities univ ON r.universityID = univ.universityID 
                      ORDER BY uploadDate DESC");
$resources = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>DigiConnect - Global Resources</title>
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
        <!-- Sidebar (add this menu for all roles) -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-text mx-3" style="font-size: 1.4rem;">DigiConnect <sup>4.0</sup></div>
            </a>
            <hr class="sidebar-divider">
            <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <?php if ($role == 'SuperAdmin'): ?>
            <li class="nav-item"><a class="nav-link" href="universities.php"><i class="fas fa-university"></i> Universities</a></li>
            <li class="nav-item"><a class="nav-link" href="Register_schooladmin.php"><i class="fas fa-user-plus"></i> Add SchoolAdmin</a></li>
            <?php endif; ?>
            <?php if ($role == 'SchoolAdmin'): ?>
            <li class="nav-item"><a class="nav-link" href="pages/schooladmin/dashboard.php"><i class="fas fa-school"></i> My University</a></li>
            <?php endif; ?>
            <li class="nav-item active">
                <a class="nav-link" href="resources.php">
                    <i class="fas fa-share-alt"></i>
                    <span>Global Resources</span>
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
                                <h2 style="color: #000;">Global Academic Resource Sharing</h2>
                                <p style="color: #000;">Connect and share notes with students from all universities!</p>
                            </div>
                        </div>
                    </div>

                    <?php if ($uploadMsg): ?>
                    <div class="alert alert-success"><?php echo $uploadMsg; ?></div>
                    <?php endif; ?>

                    <!-- Upload Form (only for Students - we'll add Student role later) -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-danger text-white">
                            <h6 class="m-0 font-weight-bold">Upload New Resource</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Title</label>
                                            <input type="text" name="title" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Category</label>
                                            <select name="category" class="form-control">
                                                <option>Notes</option>
                                                <option>Assignment</option>
                                                <option>Past Paper</option>
                                                <option>Project</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Description</label>
                                            <textarea name="description" class="form-control" rows="3"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label>File (PDF, DOC, etc.)</label>
                                            <input type="file" name="file" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-danger">Upload Resource</button>
                            </form>
                        </div>
                    </div>

                    <!-- Resources List -->
                    <div class="card shadow">
                        <div class="card-header py-3 bg-danger text-white">
                            <h6 class="m-0 font-weight-bold">Shared Resources</h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($resources)): ?>
                                <p>No resources yet. Be the first to upload!</p>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($resources as $res): ?>
                                    <div class="col-md-6 mb-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5><?php echo htmlspecialchars($res['title']); ?></h5>
                                                <p><strong>Category:</strong> <?php echo htmlspecialchars($res['category']); ?></p>
                                                <p><strong>Uploaded by:</strong> <?php echo htmlspecialchars($res['uploaderName']); ?> 
                                                    (<?php echo htmlspecialchars($res['uniName']); ?>)</p>
                                                <p><strong>Date:</strong> <?php echo $res['uploadDate']; ?></p>
                                                <a href="<?php echo $res['filePath']; ?>" class="btn btn-primary" target="_blank">Download</a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
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
</body>
</html>