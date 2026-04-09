<?php
// shopkeeper/update_stock.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdminLogin();

$shop_id = 1;
$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rice = (int)$_POST['rice_qty'];
    $wheat = (int)$_POST['wheat_qty'];
    $sugar = (int)$_POST['sugar_qty'];
    $kerosene = (int)$_POST['kerosene_qty'];

    $stmt = $pdo->prepare("UPDATE ration_stock SET rice_qty = ?, wheat_qty = ?, sugar_qty = ?, kerosene_qty = ? WHERE shop_id = ?");
    if ($stmt->execute([$rice, $wheat, $sugar, $kerosene, $shop_id])) {
        $success = "Stock updated successfully.";
    } else {
        $error = "Failed to update stock.";
    }
}

// Fetch current stock
$stmt2 = $pdo->prepare("SELECT * FROM ration_stock WHERE shop_id = ?");
$stmt2->execute([$shop_id]);
$stock = $stmt2->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Stock - E-Ration</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container header-content">
            <div class="logo">🌾 e-Ration Admin</div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="update_stock.php" style="font-weight:bold;">Update Stock</a>
                <a href="view_complaints.php">Complaints</a>
                <a href="logout.php" class="btn btn-danger" style="color:white; padding: 0.4rem 1rem;">Logout</a>
            </div>
        </div>
    </header>

    <main class="container" style="margin-top: 2rem;">
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <h2 style="margin-bottom: 2rem;">Update Shop Stock</h2>
            
            <?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>
            <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
            
            <form method="post">
                <div class="form-group">
                    <label class="form-label">Rice (Kg)</label>
                    <input type="number" name="rice_qty" class="form-control" required min="0" value="<?= $stock['rice_qty'] ?? 0 ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Wheat (Kg)</label>
                    <input type="number" name="wheat_qty" class="form-control" required min="0" value="<?= $stock['wheat_qty'] ?? 0 ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Sugar (Kg)</label>
                    <input type="number" name="sugar_qty" class="form-control" required min="0" value="<?= $stock['sugar_qty'] ?? 0 ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Kerosene (Liters)</label>
                    <input type="number" name="kerosene_qty" class="form-control" required min="0" value="<?= $stock['kerosene_qty'] ?? 0 ?>">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Save Stock Levels</button>
            </form>
        </div>
    </main>
</body>
</html>
