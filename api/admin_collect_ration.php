<?php
// api/admin_collect_ration.php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $ration_card_no = trim($data['ration_card_no'] ?? '');
    
    if (empty($ration_card_no)) {
        echo json_encode(['success' => false, 'message' => 'Ration card number is missing.']);
        exit;
    }

    $shop_id = 1; // Assuming shop 1

    // 1. Get user details
    $stmt = $pdo->prepare("SELECT user_id, family_members, name FROM users WHERE ration_card_no = ?");
    $stmt->execute([$ration_card_no]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Citizen not found!']);
        exit;
    }
    
    $user_id = $user['user_id'];

    // 2. Check if shop is open (Optional block, but good to have)
    $stmt = $pdo->prepare("SELECT is_open FROM ration_shop WHERE shop_id = ?");
    $stmt->execute([$shop_id]);
    $shop = $stmt->fetch();
    
    if (!$shop || $shop['is_open'] == 0) {
        echo json_encode(['success' => false, 'message' => 'The shop is currently closed. Cannot distribute ration.']);
        exit;
    }

    // 3. Check if already collected this month
    $current_month = date('Y-m');
    $stmt = $pdo->prepare("SELECT distribution_id FROM ration_distribution WHERE user_id = ? AND DATE_FORMAT(distribution_date, '%Y-%m') = ?");
    $stmt->execute([$user_id, $current_month]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Ration already collected for this month by this citizen.']);
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
        echo json_encode(['success' => false, 'message' => 'Insufficient stock at the shop to distribute this allotment.']);
        exit;
    }

    // 5. Deduct stock and Insert distribution record + mark time slot as fulfilled (optional, but harmless since we check ration_distribution)
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
            'message' => 'Ration marked as collected successfully for ' . $user['name'] . '!',
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
