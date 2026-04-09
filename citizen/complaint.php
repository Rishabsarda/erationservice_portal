<?php
// citizen/complaint.php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireCitizenLogin();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $text = sanitize_input($_POST['complaint_text']);
    if (empty($text)) {
        $error = "Complaint text cannot be empty.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO complaints (user_id, complaint_text, complaint_date) VALUES (?, ?, CURDATE())");
        if ($stmt->execute([$user_id, $text])) {
            $success = "Complaint submitted successfully.";
        } else {
            $error = "Error submitting complaint.";
        }
    }
}

// Fetch past complaints
$stmt2 = $pdo->prepare("SELECT * FROM complaints WHERE user_id = ? ORDER BY complaint_date DESC");
$stmt2->execute([$user_id]);
$complaints = $stmt2->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Complaints - E-Ration</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container header-content">
            <div class="logo">🌾 e-Ration</div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="history.php">History</a>
                <a href="complaint.php" style="font-weight:bold;">Complaints</a>
                <a href="logout.php" class="btn btn-danger" style="color:white; padding: 0.4rem 1rem;">Logout</a>
            </div>
        </div>
    </header>

    <main class="container" style="margin-top: 2rem;">
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
            
            <div class="card">
                <h3>Submit a Complaint</h3>
                <?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>
                <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
                
                <form method="post" style="margin-top: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Describe your issue:</label>
                        <textarea name="complaint_text" class="form-control" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Submit</button>
                </form>
            </div>

            <div class="card">
                <h3>My Previous Complaints</h3>
                <?php if (count($complaints) > 0): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Complaint</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($complaints as $c): ?>
                                <tr>
                                    <td style="white-space:nowrap;"><?= date('d M Y', strtotime($c['complaint_date'])) ?></td>
                                    <td><?= htmlspecialchars($c['complaint_text']) ?></td>
                                    <td>
                                        <span class="status-badge <?= $c['status'] == 'Resolved' ? 'status-open' : 'status-closed' ?>">
                                            <?= $c['status'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>No complaints submitted.</p>
                <?php endif; ?>
            </div>

        </div>
    </main>
</body>
</html>
