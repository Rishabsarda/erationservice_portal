<?php
// api/toggle_shop.php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shop_id = 1; // Assuming shop_id 1 for now

    // Get current status
    $stmt = $pdo->prepare("SELECT is_open FROM ration_shop WHERE shop_id = ?");
    $stmt->execute([$shop_id]);
    $shop = $stmt->fetch();

    if ($shop) {
        $new_status = $shop['is_open'] == 1 ? 0 : 1;
        
        $update = $pdo->prepare("UPDATE ration_shop SET is_open = ? WHERE shop_id = ?");
        if ($update->execute([$new_status, $shop_id])) {
            echo json_encode([
                'success' => true, 
                'is_open' => $new_status,
                'message' => $new_status == 1 ? 'Shop is now OPEN' : 'Shop is now CLOSED'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Shop not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
