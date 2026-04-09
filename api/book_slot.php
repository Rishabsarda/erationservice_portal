<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireCitizenLogin();

$data = json_decode(file_get_contents('php://input'), true);
$date = $data['date'] ?? '';
$time_slot = $data['time_slot'] ?? '';
$user_id = $_SESSION['user_id'];
$shop_id = 1;

if (!$date || !$time_slot) {
    echo json_encode(['success' => false, 'message' => 'Missing date or time slot']);
    exit;
}

// Make sure date is not in the past
if (strtotime($date) < strtotime(date('Y-m-d'))) {
    echo json_encode(['success' => false, 'message' => 'Cannot book a slot in the past.']);
    exit;
}

// Check if already collected ration this month
$current_month = date('Y-m', strtotime($date));
$stmt = $pdo->prepare("SELECT distribution_date FROM ration_distribution WHERE user_id = ? AND DATE_FORMAT(distribution_date, '%Y-%m') = ?");
$stmt->execute([$user_id, $current_month]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'You have already collected ration for this month! Cannot book/reschedule.']);
    exit;
}

try {
    // Check if a booking already exists for this month
    $stmt = $pdo->prepare("SELECT booking_id FROM slot_bookings WHERE user_id = ? AND DATE_FORMAT(slot_date, '%Y-%m') = ?");
    $stmt->execute([$user_id, $current_month]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $stmt = $pdo->prepare("UPDATE slot_bookings SET slot_date = ?, time_slot = ? WHERE booking_id = ?");
        $stmt->execute([$date, $time_slot, $existing['booking_id']]);
        $message = "Slot rescheduled successfully!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO slot_bookings (user_id, shop_id, slot_date, time_slot) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $shop_id, $date, $time_slot]);
        $message = "Slot booked successfully!";
    }
    
    echo json_encode(['success' => true, 'message' => $message]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
