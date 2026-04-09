<?php
// TEMPORARY FILE - DELETE AFTER USE
require_once 'includes/db.php';

$new_password = 'admin123';
$hash = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE admin SET password = ? WHERE username = 'admin'");
if ($stmt->execute([$hash])) {
    echo "<h2 style='color:green;font-family:sans-serif;'>✅ Admin password updated successfully!</h2>";
    echo "<p style='font-family:sans-serif;'>You can now login with:<br><b>Username:</b> admin<br><b>Password:</b> admin123</p>";
    echo "<p style='font-family:sans-serif;color:red;'><b>IMPORTANT:</b> Delete this file (fix_password.php) after logging in!</p>";
} else {
    echo "<h2 style='color:red;font-family:sans-serif;'>❌ Failed to update password.</h2>";
}
?>
