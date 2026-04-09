<?php
// includes/db.php

// Use environment variables for production (Railway) or fall back to localhost for development
$host = getenv('MYSQLHOST') ?: 'localhost';
$dbname = getenv('MYSQLDATABASE') ?: 'erationportal';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
$port = getenv('MYSQLPORT') ?: '3306';

try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5, // 5 second timeout to prevent hanging
    ];
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password, $options);
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}
?>
