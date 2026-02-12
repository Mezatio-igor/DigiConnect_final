<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="DigiConnect - School Management & Academic Network">
    <meta name="author" content="">
    <title>DigiConnect - Login</title>
    <!-- Bootstrap CSS -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <!-- Custom CSS -->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-danger">
<?php
session_start();
include 'config/db_connect.php';

$error = '';  // Start empty

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($email) || empty($password)) {
        $error = "Please enter email and password";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['userID']      = $user['userID'];
            $_SESSION['name']        = $user['name'];
            $_SESSION['role']        = $user['role'];
            $_SESSION['universityID'] = isset($user['universityID']) ? $user['universityID'] : null;

            // Redirect based on role
            if ($user['role'] == 'SuperAdmin') {
                header('Location: index.php');
            } elseif ($user['role'] == 'SchoolAdmin') {
                header('Location: pages/schooladmin/dashboard.php');
            } elseif ($user['role'] == 'Student') {
                header('Location: pages/student/dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit();  // VERY IMPORTANT - stop execution after redirect
        } else {
            $error = "Wrong email or password";
        }
    }
}
?>

<!-- HTML part of the page (keep your existing design) -->
<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-lg-6 col-md-8 col-sm-10">
            <div class="card o-hidden border-0 shadow-lg">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="p-5">
                                <div class="text-center mb-5">
                                    <img src="img/digiconnect-logo.jpg" alt="DigiConnect" style="height: 100px; margin-bottom: 20px;">
                                    <h1 class="h4 text-gray-900">Welcome Back!</h1>
                                    <p class="text-gray-600">DigiConnect 4.0 - School Management & Academic Network</p>
                                </div>

                                <?php if (!empty($error)): ?>
                                    <div class="alert alert-danger text-center">
                                        <?php echo htmlspecialchars($error); ?>
                                    </div>
                                <?php endif; ?>

                                <form method="POST">
                                    <div class="form-group">
                                        <input type="email" name="email" class="form-control form-control-user" 
                                               placeholder="Enter Email Address..." required autofocus>
                                    </div>
                                    <div class="form-group">
                                        <input type="password" name="password" class="form-control form-control-user" 
                                               placeholder="Password" required>
                                    </div>
                                    <button type="submit" class="btn btn-danger btn-user btn-block">
                                        Login
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="js/sb-admin-2.min.js"></script>
</body>
</html>