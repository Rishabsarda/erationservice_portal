<?php
// citizen/dashboard.php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireCitizenLogin();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$family_members = $_SESSION['family_members'];

$allotted = calculate_ration($family_members);

// Check if collected this month
$current_month = date('Y-m');
$stmt = $pdo->prepare("SELECT distribution_date FROM ration_distribution WHERE user_id = ? AND DATE_FORMAT(distribution_date, '%Y-%m') = ?");
$stmt->execute([$user_id, $current_month]);
$already_collected = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Citizen Dashboard - E-Ration</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auto-refresh-status">
    <header class="header">
        <div class="container header-content">
            <div class="logo">🌾 e-Ration</div>
            <div class="nav-links">
                <a href="dashboard.php" style="font-weight:bold;">Dashboard</a>
                <a href="history.php">History</a>
                <a href="complaint.php">Complaints</a>
                <a href="logout.php" class="btn btn-danger" style="color:white; padding: 0.4rem 1rem;">Logout</a>
            </div>
        </div>
    </header>

    <main class="container" style="margin-top: 2rem;">
        <h2>Welcome, <?= htmlspecialchars($user_name) ?>!</h2>
        <p>Family Members: <?= $family_members ?> | Monthly Allocation: Rice <?= $allotted['rice'] ?>kg, Wheat <?= $allotted['wheat'] ?>kg, Sugar <?= $allotted['sugar'] ?>kg, Kerosene <?= $allotted['kerosene'] ?>L</p>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-top: 2rem;">
            
            <!-- Left Column: Status and Stock -->
            <div>
                <div class="card" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3>Shop Status</h3>
                        <p id="lastUpdatedText" style="font-size:0.8rem;">Checking...</p>
                    </div>
                    <div id="shopStatusBadge" class="status-badge" style="font-size: 1.2rem;">...</div>
                </div>

                <h3>Available Stock at Shop</h3>
                <div class="dashboard-grid" style="margin-top: 1rem;">
                    <div class="card">
                        <h5>Rice</h5>
                        <h3 style="color: var(--primary-color);"><span id="rice-qty">0</span> Kg</h3>
                        <div class="progress-container"><div id="rice-bar" class="progress-bar" style="width: 0%"></div></div>
                    </div>
                    <div class="card">
                        <h5>Wheat</h5>
                        <h3 style="color: var(--primary-color);"><span id="wheat-qty">0</span> Kg</h3>
                        <div class="progress-container"><div id="wheat-bar" class="progress-bar" style="width: 0%"></div></div>
                    </div>
                    <div class="card">
                        <h5>Sugar</h5>
                        <h3 style="color: var(--primary-color);"><span id="sugar-qty">0</span> Kg</h3>
                        <div class="progress-container"><div id="sugar-bar" class="progress-bar" style="width: 0%"></div></div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Time Slot Booking -->
            <div>
                <div class="card" style="min-height: 100%; height: auto; background: #f8fafc; border: 1px solid var(--border-color);">
                    <?php if ($already_collected): ?>
                        <div class="alert alert-success" style="text-align: center;">
                            <strong>Ration Collected!</strong>
                            <p>You received your ration for this month on <?= date('d M Y', strtotime($already_collected['distribution_date'])) ?>.</p>
                            <p style="margin-top: 1rem; font-size: 0.9em;">Time slot booking is disabled.</p>
                        </div>
                    <?php else: ?>
                        <h3 style="margin-bottom: 1rem; text-align: center;">Book a Time Slot</h3>
                        <p style="font-size: 0.9rem; margin-bottom: 1.5rem; color: var(--text-muted); text-align: center;">Select a date to view available time slots. Limit 1 active booking per month.</p>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label for="slotDate" style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Select Date:</label>
                            <input type="date" id="slotDate" class="form-input" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" onchange="fetchTimeSlots()">
                        </div>

                        <div id="slotsContainer">
                            <p style="text-align:center; color: var(--text-muted);">Loading slots...</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>

    <!-- Success Modal -->
    <div class="modal-overlay" id="confirmationModal">
        <div class="modal">
            <div id="modalMessage"></div>
            <button class="btn btn-primary" style="margin-top:2rem; width:100%;" onclick="closeModal()">Close</button>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
