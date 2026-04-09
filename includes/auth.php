<?php
// includes/auth.php

session_start();

function isCitizenLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireCitizenLogin() {
    if (!isCitizenLoggedIn()) {
        header("Location: ../citizen/login.php");
        exit;
    }
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header("Location: ../shopkeeper/login.php");
        exit;
    }
}
?>
