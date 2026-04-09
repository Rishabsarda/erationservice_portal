<?php
// citizen/login.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (isCitizenLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rc_no = trim($_POST['ration_card_no']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT user_id, name, password, family_members FROM users WHERE ration_card_no = ?");
    $stmt->execute([$rc_no]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['family_members'] = $user['family_members'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid Ration Card Number or Password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Citizen Login - E-Ration</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <h2 style="text-align:center; margin-bottom: 2rem;">Citizen Login</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="form-group">
                    <label class="form-label">Ration Card Number</label>
                    <input type="text" name="ration_card_no" class="form-control" required placeholder="e.g. RC1234567890">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Enter your password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            
            <p style="text-align:center; margin-top:1.5rem; color:var(--text-muted);">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
            <p style="text-align:center; margin-top:0.5rem;">
                <a href="../index.php">← Back to Home</a>
            </p>
        </div>
    </div>
</body>
</html>
