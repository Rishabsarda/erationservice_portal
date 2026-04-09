<?php
// api/collect_ration.php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isCitizenLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $shop_id = 1; // Hardcoded to 1 for this project

    // 1. Check if shop is open
    $stmt = $pdo->prepare("SELECT is_open FROM ration_shop WHERE shop_id = ?");
    $stmt->execute([$shop_id]);
    $shop = $stmt->fetch();
    
    if (!$shop || $shop['is_open'] == 0) {
        echo json_encode(['success' => false, 'message' => 'The shop is currently closed.']);
        exit;
    }

    // 2. Check if already collected this month
    $current_month = date('Y-m');
    $stmt = $pdo->prepare("SELECT distribution_id FROM ration_distribution WHERE user_id = ? AND DATE_FORMAT(distribution_date, '%Y-%m') = ?");
    $stmt->execute([$user_id, $current_month]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'You have already collected ration for this month.']);
        exit;
    }

    // 3. Get user details for calculation
    $stmt = $pdo->prepare("SELECT family_members FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }

    $allotted = calculate_ration($user['family_members']);

    // 4. Check stock
    $stmt = $pdo->prepare("SELECT * FROM ration_stock WHERE shop_id = ?");
    $stmt->execute([$shop_id]);
    $stock = $stmt->fetch();

    if ($stock['rice_qty'] < $allotted['rice'] || 
        $stock['wheat_qty'] < $allotted['wheat'] || 
        $stock['sugar_qty'] < $allotted['sugar'] || 
        $stock['kerosene_qty'] < $allotted['kerosene']) {
        echo json_encode(['success' => false, 'message' => 'Insufficient stock at the shop.']);
        exit;
    }

    // 5. Deduct from stock and Insert distribution record (Transaction)
    try {
        $pdo->beginTransaction();

        $update_stock = $pdo->prepare("UPDATE ration_stock SET 
            rice_qty = rice_qty - ?, 
            wheat_qty = wheat_qty - ?, 
            sugar_qty = sugar_qty - ?, 
            kerosene_qty = kerosene_qty - ? 
            WHERE shop_id = ?");
            
        $update_stock->execute([
            $allotted['rice'], 
            $allotted['wheat'], 
            $allotted['sugar'], 
            $allotted['kerosene'], 
            $shop_id
        ]);

        $insert_dist = $pdo->prepare("INSERT INTO ration_distribution (user_id, shop_id, rice_given, wheat_given, sugar_given, kerosene_given, distribution_date) VALUES (?, ?, ?, ?, ?, ?, CURDATE())");
        
        $insert_dist->execute([
            $user_id,
            $shop_id,
            $allotted['rice'],
            $allotted['wheat'],
            $allotted['sugar'],
            $allotted['kerosene']
        ]);

        // 6. Remove booking for this month
        $delete_booking = $pdo->prepare("DELETE FROM slot_bookings WHERE user_id = ? AND DATE_FORMAT(slot_date, '%Y-%m') = ?");
        $delete_booking->execute([$user_id, $current_month]);

        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Ration collected successfully!',
            'details' => $allotted
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Transaction failed. Please try again.']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
