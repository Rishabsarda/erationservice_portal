<?php
// shopkeeper/login.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (isAdminLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT admin_id, admin_name, password FROM admin WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_name'] = $admin['admin_name'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid Username or Password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopkeeper Login - E-Ration</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper" style="background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%);">
        <div class="auth-card">
            <h2 style="text-align:center; margin-bottom: 2rem;">Shopkeeper Login</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required placeholder="admin">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="admin123">
                </div>
                
                <button type="submit" class="btn btn-success btn-block">Login</button>
            </form>
            <p style="text-align:center; margin-top:1.5rem;">
                <a href="../index.php">← Back to Home</a>
            </p>
        </div>
    </div>
</body>
</html>
