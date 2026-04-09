<?php
// citizen/logout.php
session_start();
unset($_SESSION['user_id']);
unset($_SESSION['user_name']);
unset($_SESSION['family_members']);
header("Location: login.php");
exit;
?>
