<?php
// api/admin_get_slot_users.php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$time_slot = $_GET['time_slot'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d');
$shop_id = 1;

if (empty($time_slot)) {
    echo json_encode(['success' => false, 'message' => 'Time slot is required.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT u.name, u.ration_card_no 
        FROM slot_bookings sb
        JOIN users u ON sb.user_id = u.user_id
        WHERE sb.shop_id = ? AND sb.slot_date = ? AND sb.time_slot = ?
        AND NOT EXISTS (
            SELECT 1 FROM ration_distribution rd 
            WHERE rd.user_id = sb.user_id 
            AND YEAR(rd.distribution_date) = YEAR(sb.slot_date)
            AND MONTH(rd.distribution_date) = MONTH(sb.slot_date)
        )
        ORDER BY sb.booked_at ASC
    ");
    $stmt->execute([$shop_id, $date, $time_slot]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'users' => $users]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
