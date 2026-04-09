<?php
// shopkeeper/dashboard.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdminLogin();

$shop_id = 1;

// Fetch shop info
$stmt = $pdo->prepare("SELECT * FROM ration_shop WHERE shop_id = ?");
$stmt->execute([$shop_id]);
$shop = $stmt->fetch();

// Fetch stock info
$stmt2 = $pdo->prepare("SELECT * FROM ration_stock WHERE shop_id = ?");
$stmt2->execute([$shop_id]);
$stock = $stmt2->fetch();

// Fetch today's distributions
$stmt3 = $pdo->prepare("SELECT d.*, u.name, u.ration_card_no FROM ration_distribution d JOIN users u ON d.user_id = u.user_id WHERE d.shop_id = ? AND d.distribution_date = CURDATE() ORDER BY d.distribution_id DESC");
$stmt3->execute([$shop_id]);
$today_dist = $stmt3->fetchAll();

// Fetch today's bookings - exclude users who have already collected today/this month
$stmt4 = $pdo->prepare("
    SELECT sb.time_slot, COUNT(*) as count 
    FROM slot_bookings sb 
    WHERE sb.shop_id = ? AND sb.slot_date = CURDATE()
    AND NOT EXISTS (
        SELECT 1 FROM ration_distribution rd 
        WHERE rd.user_id = sb.user_id 
        AND YEAR(rd.distribution_date) = YEAR(sb.slot_date)
        AND MONTH(rd.distribution_date) = MONTH(sb.slot_date)
    )
    GROUP BY sb.time_slot
");
$stmt4->execute([$shop_id]);
$today_bookings_raw = $stmt4->fetchAll(PDO::FETCH_KEY_PAIR);
$today_bookings = [
    '09:00 AM - 11:00 AM' => 0,
    '11:00 AM - 01:00 PM' => 0,
    '01:00 PM - 03:00 PM' => 0,
    '03:00 PM - 05:00 PM' => 0
];
foreach($today_bookings_raw as $s => $c) {
    if(isset($today_bookings[$s])) $today_bookings[$s] = $c;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopkeeper Dashboard - E-Ration</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container header-content">
            <div class="logo">🌾 e-Ration Admin</div>
            <div class="nav-links">
                <a href="dashboard.php" style="font-weight:bold;">Dashboard</a>
                <a href="update_stock.php">Update Stock</a>
                <a href="view_complaints.php">Complaints</a>
                <a href="logout.php" class="btn btn-danger" style="color:white; padding: 0.4rem 1rem;">Logout</a>
            </div>
        </div>
    </header>

    <main class="container" style="margin-top: 2rem;">
        <h2>Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?></h2>
        
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; margin-top: 2rem;">
            
            <!-- Left Side: Controls -->
            <div>
                <div class="card" style="text-align: center; margin-bottom: 2rem;">
                    <h3>Shop Status Control</h3>
                    <div id="shopCurrentStatus" style="margin: 1rem 0;">
                        <?php if ($shop['is_open'] == 1): ?>
                            <span class="status-badge status-open">🟢 SHOP OPEN</span>
                        <?php else: ?>
                            <span class="status-badge status-closed">🔴 SHOP CLOSED</span>
                        <?php endif; ?>
                    </div>
                    
                    <button id="toggleShopBtn" onclick="toggleShopStatus()" class="btn <?= $shop['is_open'] == 1 ? 'btn-danger' : 'btn-success' ?> btn-block">
                        <?= $shop['is_open'] == 1 ? '🔴 Close Shop Now' : '🟢 Open Shop Now' ?>
                    </button>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 1rem;">Changes apply instantly on citizen dashboards.</p>
                </div>

                <div class="card" style="margin-bottom: 2rem;">
                    <h3>Manual Distribution</h3>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">Enter Ration Card No to lookup and mark collected.</p>
                    
                    <div style="display:flex; gap:0.5rem; margin-bottom: 1rem;">
                        <input type="text" id="lookupRationCard" class="form-input" placeholder="e.g. RC1234567890">
                        <button onclick="lookupCitizen()" class="btn btn-primary">Lookup</button>
                    </div>
                    
                    <div id="lookupResult" style="display: none; border: 1px solid var(--border-color); padding: 1rem; border-radius: 4px; background: #f8fafc;">
                        <p><strong>Name:</strong> <span id="lookupName"></span></p>
                        <p><strong>Family:</strong> <span id="lookupFamily"></span> / <span id="lookupType"></span></p>
                        <div id="lookupStatus" style="margin-top: 0.5rem;"></div>
                        <button id="adminCollectBtn" style="margin-top: 1rem; width: 100%; display: none;" onclick="adminCollectRation()" class="btn btn-success">Mark as Collected</button>
                    </div>
                </div>

                <div class="card">
                    <h3>Low Stock Alerts</h3>
                    <ul style="margin-top: 1rem; margin-left: 1.5rem; color: var(--danger-color);">
                        <?php if($stock['rice_qty'] < 50) echo "<li>Rice is running low ({$stock['rice_qty']} Kg)</li>"; ?>
                        <?php if($stock['wheat_qty'] < 50) echo "<li>Wheat is running low ({$stock['wheat_qty']} Kg)</li>"; ?>
                        <?php if($stock['sugar_qty'] < 20) echo "<li>Sugar is running low ({$stock['sugar_qty']} Kg)</li>"; ?>
                        <?php if($stock['kerosene_qty'] < 20) echo "<li>Kerosene is running low ({$stock['kerosene_qty']} L)</li>"; ?>
                        <?php if($stock['rice_qty'] >= 50 && $stock['wheat_qty'] >= 50 && $stock['sugar_qty'] >= 20 && $stock['kerosene_qty'] >= 20) echo "<li style='color:var(--success-color);'>All stocks are sufficient.</li>"; ?>
                    </ul>
                </div>
            </div>

            <!-- Right Side: Today's Bookings and Distributions -->
            <div>
                <div class="card" style="margin-bottom: 2rem;">
                    <h3>Today's Booked Slots</h3>
                    <p style="color:var(--text-muted); margin-bottom: 1rem;"><?= date('d M Y') ?></p>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                        <?php foreach($today_bookings as $slot => $count): 
                            $badgeColor = 'var(--success-color)';
                            if($count > 10) $badgeColor = 'var(--warning-color)';
                            if($count > 20) $badgeColor = 'var(--danger-color)';
                        ?>
                        <div style="border: 1px solid #e2e8f0; padding: 1rem; border-radius: 8px; text-align: center; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#f1f5f9';" onmouseout="this.style.background='transparent';" onclick="viewSlotDetails('<?= htmlspecialchars($slot) ?>')">
                            <strong style="display: block; font-size: 0.9rem;"><?= $slot ?></strong>
                            <h3 style="color: <?= $badgeColor ?>; margin-top: 0.5rem;"><?= $count ?></h3>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">people</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div id="slotDetailsContainer" style="display: none; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                        <h4 style="margin-bottom: 0.5rem;">Expected Citizens: <span id="slotDetailsTime"></span></h4>
                        <div id="slotDetailsList" style="max-height: 200px; overflow-y: auto;"></div>
                    </div>
                </div>

                <div class="card">
                <h3>Today's Validated Distributions</h3>
                <p style="color:var(--text-muted);"><?= date('d M Y') ?></p>
                
                <?php if (count($today_dist) > 0): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Citizen Name</th>
                                    <th>Card No</th>
                                    <th>R, W, S, K Given</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($today_dist as $d): ?>
                                <tr>
                                    <td><?= htmlspecialchars($d['name']) ?></td>
                                    <td><?= htmlspecialchars($d['ration_card_no']) ?></td>
                                    <td><?= implode(', ', [$d['rice_given'].'kg', $d['wheat_given'].'kg', $d['sugar_given'].'kg', $d['kerosene_given'].'L']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="margin-top: 1.5rem;">No distributions recorded today.</p>
                <?php endif; ?>
                </div>
            </div>
            
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
