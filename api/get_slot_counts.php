<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$date = $_GET['date'] ?? date('Y-m-d');
$shop_id = 1; // Assuming single shop

// Fetch counts for each slot - exclude users who have already collected this month
$stmt = $pdo->prepare("
    SELECT sb.time_slot, COUNT(*) as count 
    FROM slot_bookings sb 
    WHERE sb.shop_id = ? AND sb.slot_date = ?
    AND NOT EXISTS (
        SELECT 1 FROM ration_distribution rd 
        WHERE rd.user_id = sb.user_id 
        AND YEAR(rd.distribution_date) = YEAR(sb.slot_date)
        AND MONTH(rd.distribution_date) = MONTH(sb.slot_date)
    )
    GROUP BY sb.time_slot
");
$stmt->execute([$shop_id, $date]);
$counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$slots = [
    '09:00 AM - 11:00 AM' => 0,
    '11:00 AM - 01:00 PM' => 0,
    '01:00 PM - 03:00 PM' => 0,
    '03:00 PM - 05:00 PM' => 0
];

foreach ($counts as $slot => $count) {
    if (isset($slots[$slot])) {
        $slots[$slot] = $count;
    }
}

// If citizen, return their own booking for the month of the selected date to highlight it
// Only return it if they haven't collected yet
$my_booking = null;
if (isset($_SESSION['user_id'])) {
    $month = date('Y-m', strtotime($date));
    $stmt2 = $pdo->prepare("
        SELECT sb.slot_date, sb.time_slot 
        FROM slot_bookings sb 
        WHERE sb.user_id = ? AND DATE_FORMAT(sb.slot_date, '%Y-%m') = ?
        AND NOT EXISTS (
            SELECT 1 FROM ration_distribution rd 
            WHERE rd.user_id = sb.user_id 
            AND DATE_FORMAT(rd.distribution_date, '%Y-%m') = ?
        )
    ");
    $stmt2->execute([$_SESSION['user_id'], $month, $month]);
    $my_booking = $stmt2->fetch(PDO::FETCH_ASSOC);
}

echo json_encode([
    'success' => true, 
    'counts' => $slots, 
    'date' => $date,
    'my_booking' => $my_booking ?: null
]);
