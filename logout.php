<?php
session_start();
session_destroy();  // Clear login data
header('Location: login.php');
exit();
?>