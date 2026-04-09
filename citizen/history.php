<?php
// citizen/history.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireCitizenLogin();

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM ration_distribution WHERE user_id = ? ORDER BY distribution_date DESC");
$stmt->execute([$user_id]);
$history = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Distribution History - E-Ration</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container header-content">
            <div class="logo">🌾 e-Ration</div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="history.php" style="font-weight:bold;">History</a>
                <a href="complaint.php">Complaints</a>
                <a href="logout.php" class="btn btn-danger" style="color:white; padding: 0.4rem 1rem;">Logout</a>
            </div>
        </div>
    </header>

    <main class="container" style="margin-top: 2rem;">
        <h2>My Distribution History</h2>
        
        <div class="card" style="margin-top: 1rem;">
            <?php if (count($history) > 0): ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Rice (Kg)</th>
                            <th>Wheat (Kg)</th>
                            <th>Sugar (Kg)</th>
                            <th>Kerosene (L)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($history as $record): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($record['distribution_date'])) ?></td>
                            <td><?= $record['rice_given'] ?></td>
                            <td><?= $record['wheat_given'] ?></td>
                            <td><?= $record['sugar_given'] ?></td>
                            <td><?= $record['kerosene_given'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p>No ration collected yet.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
