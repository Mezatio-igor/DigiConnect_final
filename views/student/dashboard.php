<?php
session_start();

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: ../auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
</head>
<body>

<h2>Welcome to DigiConnect</h2>

<p>You are logged in as: <strong>Student</strong></p>

<p>
Email:
<?php
if (isset($_SESSION["email"])) {
    echo $_SESSION["email"];
} else {
    echo "Not available";
}
?>
</p>


<a href="../../controllers/logout.php">Logout</a>

</body>
</html>
