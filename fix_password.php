<?php
// TEMPORARY FILE - DELETE AFTER USE
require_once 'includes/db.php';

$new_password = 'admin123';
$hash = password_hash($new_password, PASSWORD_DEFAULT);

// Check if admin exists
$check = $pdo->query("SELECT COUNT(*) FROM admin WHERE username = 'admin'");
$count = $check->fetchColumn();

if ($count > 0) {
    // Update existing
    $stmt = $pdo->prepare("UPDATE admin SET password = ? WHERE username = 'admin'");
    $stmt->execute([$hash]);
    echo "<h2 style='color:green;font-family:sans-serif;'>✅ Admin password UPDATED!</h2>";
} else {
    // Insert new admin row
    $stmt = $pdo->prepare("INSERT INTO admin (admin_name, username, password) VALUES ('Ramesh Kumar', 'admin', ?)");
    $stmt->execute([$hash]);
    echo "<h2 style='color:blue;font-family:sans-serif;'>✅ Admin account CREATED!</h2>";
}

echo "<p style='font-family:sans-serif;'><b>Username:</b> admin<br><b>Password:</b> admin123</p>";
echo "<p style='font-family:sans-serif;color:red;'><b>IMPORTANT:</b> Delete this file after logging in!</p>";

// Show current admin table
$rows = $pdo->query("SELECT admin_id, admin_name, username FROM admin")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre style='font-family:sans-serif;background:#f0f0f0;padding:10px;'>";
echo "Admin table contents:\n";
print_r($rows);
echo "</pre>";
?>
