<?php
// TEMPORARY FILE - DELETE AFTER USE
require_once 'includes/db.php';

// Check if shop exists
$shopCheck = $pdo->query("SELECT COUNT(*) FROM ration_shop WHERE shop_id = 1")->fetchColumn();

if ($shopCheck == 0) {
    $pdo->query("INSERT INTO ration_shop (shop_id, shop_name, shop_address, village, shop_timings, shopkeeper_name, mobile_no, is_open) VALUES (1, 'Govt FPS - 001', 'Main Market', 'Rampur', '09:00 AM - 05:00 PM', 'Ramesh Kumar', '9876543210', 0)");
    echo "✅ Shop created!<br>";
} else {
    echo "ℹ️ Shop already exists.<br>";
}

// Check if stock exists
$stockCheck = $pdo->query("SELECT COUNT(*) FROM ration_stock WHERE shop_id = 1")->fetchColumn();

if ($stockCheck == 0) {
    $pdo->query("INSERT INTO ration_stock (shop_id, rice_qty, wheat_qty, sugar_qty, kerosene_qty) VALUES (1, 0, 0, 0, 0)");
    echo "✅ Stock record created!<br>";
} else {
    echo "ℹ️ Stock already exists.<br>";
}

echo "<br><b style='color:green;'>All done! <a href='shopkeeper/dashboard.php'>Go to Dashboard</a></b><br><br>";
echo "<span style='color:red;'>IMPORTANT: Delete this file (setup_shop.php) after visiting the dashboard!</span>";
?>
