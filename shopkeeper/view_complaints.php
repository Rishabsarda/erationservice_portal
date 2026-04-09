<?php
// shopkeeper/view_complaints.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdminLogin();

$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'resolve') {
    $comp_id = (int)$_POST['complaint_id'];
    $stmt = $pdo->prepare("UPDATE complaints SET status = 'Resolved' WHERE complaint_id = ?");
    if ($stmt->execute([$comp_id])) {
        $success = "Complaint marked as resolved.";
    }
}

// Fetch all complaints
$stmt2 = $pdo->query("SELECT c.*, u.name, u.ration_card_no FROM complaints c JOIN users u ON c.user_id = u.user_id ORDER BY c.status ASC, c.complaint_date DESC");
$complaints = $stmt2->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Complaints - E-Ration</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container header-content">
            <div class="logo">🌾 e-Ration Admin</div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="update_stock.php">Update Stock</a>
                <a href="view_complaints.php" style="font-weight:bold;">Complaints</a>
                <a href="logout.php" class="btn btn-danger" style="color:white; padding: 0.4rem 1rem;">Logout</a>
            </div>
        </div>
    </header>

    <main class="container" style="margin-top: 2rem;">
        <div class="card">
            <h2>Citizen Complaints</h2>
            
            <?php if($success) echo "<div class='alert alert-success' style='margin-top: 1rem;'>$success</div>"; ?>
            
            <?php if (count($complaints) > 0): ?>
            <div class="table-responsive" style="margin-top: 1rem;">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Citizen</th>
                            <th>Card No</th>
                            <th>Complaint Details</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($complaints as $c): ?>
                        <tr>
                            <td style="white-space:nowrap;"><?= date('d M Y', strtotime($c['complaint_date'])) ?></td>
                            <td><?= htmlspecialchars($c['name']) ?></td>
                            <td><?= htmlspecialchars($c['ration_card_no']) ?></td>
                            <td><?= htmlspecialchars($c['complaint_text']) ?></td>
                            <td>
                                <span class="status-badge <?= $c['status'] == 'Resolved' ? 'status-open' : 'status-closed' ?>">
                                    <?= $c['status'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if($c['status'] == 'Pending'): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="action" value="resolve">
                                        <input type="hidden" name="complaint_id" value="<?= $c['complaint_id'] ?>">
                                        <button type="submit" class="btn btn-success" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">Mark Resolved</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color:var(--text-muted)">Done</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p style="margin-top: 1rem;">No complaints found.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
