<?php
// citizen/register.php
require_once '../includes/db.php';
require_once '../includes/functions.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = sanitize_input($_POST['name']);
    $rc_no = sanitize_input($_POST['ration_card_no']);
    $mobile = sanitize_input($_POST['mobile_no']);
    $password = $_POST['password'];
    $family_members = (int)$_POST['family_members'];
    $card_type = sanitize_input($_POST['card_type']);
    
    if (empty($name) || empty($rc_no) || empty($password) || empty($family_members)) {
        $error = "Please fill all required fields.";
    } elseif (!is_valid_ration_card($rc_no)) {
        $error = "Invalid Ration Card Number Format. Must be 10-16 alphanumeric characters.";
    } else {
        // Check if RC already exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE ration_card_no = ?");
        $stmt->execute([$rc_no]);
        if ($stmt->rowCount() > 0) {
            $error = "This Ration Card Number is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO users (name, ration_card_no, mobile_no, family_members, password, card_type) VALUES (?, ?, ?, ?, ?, ?)");
            if ($insert->execute([$name, $rc_no, $mobile, $family_members, $hashed_password, $card_type])) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Citizen Registration - E-Ration</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <h2 style="text-align:center; margin-bottom: 2rem;">Register for E-Ration</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?> <a href="login.php">Login here</a></div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-control" required placeholder="Enter Head of Family Name">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Ration Card Number *</label>
                    <input type="text" name="ration_card_no" class="form-control" required placeholder="e.g. RC1234567890">
                    <small style="color:var(--text-muted)">Must be 10-16 alphanumeric chars</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Mobile Number</label>
                    <input type="text" name="mobile_no" class="form-control" placeholder="10 digit mobile">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Family Members Count *</label>
                    <input type="number" name="family_members" min="1" max="20" class="form-control" required value="1">
                </div>

                <div class="form-group">
                    <label class="form-label">Card Type *</label>
                    <select name="card_type" class="form-control" required>
                        <option value="APL">APL (Above Poverty Line)</option>
                        <option value="BPL">BPL (Below Poverty Line)</option>
                        <option value="AAY">AAY (Antyodaya Anna Yojana)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-control" required placeholder="Create a password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>
            
            <p style="text-align:center; margin-top:1.5rem; color:var(--text-muted);">
                Already have an account? <a href="login.php">Login here</a>
            </p>
            <p style="text-align:center; margin-top:0.5rem;">
                <a href="../index.php">← Back to Home</a>
            </p>
        </div>
    </div>
</body>
</html>
