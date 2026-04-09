<?php
// index.php
require_once 'includes/db.php';

// Fetch basic info just to have it statically on load, JS will take over updates
$stmt = $pdo->query("SELECT * FROM ration_shop WHERE shop_id = 1");
$shop = $stmt->fetch();

$stmt2 = $pdo->query("SELECT * FROM ration_stock WHERE shop_id = 1");
$stock = $stmt2->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ration Service Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auto-refresh-status">
    
    <header class="header">
        <div class="container header-content">
            <div class="logo">🌾 e-Ration Portal</div>
            <div class="nav-links">
                <a href="citizen/login.php" class="btn btn-primary">Citizen Login</a>
                <a href="shopkeeper/login.php" class="btn btn-success">Shopkeeper Login</a>
            </div>
        </div>
    </header>

    <main class="container">
        <section class="hero-section">
            <h1>Government Ration Shop Services</h1>
            <p>Transparent, fast, and digital ration distribution system for citizens.</p>
        </section>

        <div class="card" style="text-align: center; margin-bottom: 2rem;">
            <h2><?= htmlspecialchars($shop['shop_name'] ?? 'Govt FPS') ?></h2>
            <p><?= htmlspecialchars($shop['shop_address'] ?? '') ?>, <?= htmlspecialchars($shop['village'] ?? '') ?></p>
            <p style="margin-bottom: 1rem;"><strong>Timings:</strong> <?= htmlspecialchars($shop['shop_timings'] ?? '') ?></p>
            
            <div id="shopStatusBadge" class="status-badge <?= (isset($shop['is_open']) && $shop['is_open'] == 1) ? 'status-open' : 'status-closed' ?>">
                <?= (isset($shop['is_open']) && $shop['is_open'] == 1) ? '🟢 SHOP OPEN' : '🔴 SHOP CLOSED' ?>
            </div>
        </div>

        <h3 style="margin-top: 2rem; margin-bottom: 1rem;">Live Stock Availability</h3>
        <p style="font-size: 0.9rem; color: var(--text-muted);">Last updated: <span id="lastUpdatedText"><?= $stock['last_updated'] ?? 'N/A' ?></span> (Auto-refreshes every 60s)</p>

        <div class="dashboard-grid">
            <div class="card">
                <h3>Rice</h3>
                <h2 style="color: var(--primary-color);"><span id="rice-qty"><?= $stock['rice_qty'] ?? 0 ?></span> Kg</h2>
                <div class="progress-container">
                    <div id="rice-bar" class="progress-bar" style="width: <?= min(100, (($stock['rice_qty'] ?? 0)/5000)*100) ?>%"></div>
                </div>
            </div>
            
            <div class="card">
                <h3>Wheat</h3>
                <h2 style="color: var(--primary-color);"><span id="wheat-qty"><?= $stock['wheat_qty'] ?? 0 ?></span> Kg</h2>
                <div class="progress-container">
                    <div id="wheat-bar" class="progress-bar" style="width: <?= min(100, (($stock['wheat_qty'] ?? 0)/5000)*100) ?>%"></div>
                </div>
            </div>

            <div class="card">
                <h3>Sugar</h3>
                <h2 style="color: var(--primary-color);"><span id="sugar-qty"><?= $stock['sugar_qty'] ?? 0 ?></span> Kg</h2>
                <div class="progress-container">
                    <div id="sugar-bar" class="progress-bar" style="width: <?= min(100, (($stock['sugar_qty'] ?? 0)/1000)*100) ?>%"></div>
                </div>
            </div>

            <div class="card">
                <h3>Kerosene</h3>
                <h2 style="color: var(--primary-color);"><span id="kerosene-qty"><?= $stock['kerosene_qty'] ?? 0 ?></span> L</h2>
                <div class="progress-container">
                    <div id="kerosene-bar" class="progress-bar" style="width: <?= min(100, (($stock['kerosene_qty'] ?? 0)/1000)*100) ?>%"></div>
                </div>
            </div>
        </div>
    </main>

    <footer style="text-align: center; padding: 2rem; margin-top: 3rem; color: var(--text-muted);">
        <p>&copy; <?= date('Y') ?> Government E-Ration Services. All rights reserved.</p>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
