<?php
session_start();

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"];
    $password = $_POST["password"];
    $role = $_POST["role"];

    $db = new Database();
    $conn = $db->connect();

    if ($role == "student") {
        $table = "Student";
        $idField = "studentID";
        $redirect = "../views/student/dashboard.php";
    }

    elseif ($role == "teacher") {
        $table = "Teacher";
        $idField = "teacherID";
        $redirect = "../views/teacher/dashboard.php";
    }

    elseif ($role == "schooladmin") {
        $table = "SchoolAdmin";
        $idField = "schoolAdminID";
        $redirect = "../views/schooladmin/dashboard.php";
    }

    elseif ($role == "superadmin") {
        $table = "SuperAdmin";
        $idField = "superAdminID";
        $redirect = "../views/superadmin/dashboard.php";
    }

    else {
        die("Invalid role");
    }

    $sql = "SELECT * FROM $table WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email]);

    if ($stmt->rowCount() == 1) {

        $user = $stmt->fetch();

        if ($password == $user["password"]) {

            $_SESSION["user_id"] = $user[$idField];
            $_SESSION["role"] = $role;
            $_SESSION["email"] = $user["email"];

            header("Location: $redirect");
            exit;

        } else {
            echo "Wrong password";
        }

    } else {
        echo "User not found";
    }
}
