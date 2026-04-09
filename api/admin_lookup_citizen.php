<?php
// api/admin_lookup_citizen.php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$ration_card_no = trim($_GET['rc'] ?? '');

if (empty($ration_card_no)) {
    echo json_encode(['success' => false, 'message' => 'Ration card number is required.']);
    exit;
}

// Lookup citizen
$stmt = $pdo->prepare("SELECT user_id, name, family_members, card_type FROM users WHERE ration_card_no = ?");
$stmt->execute([$ration_card_no]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Citizen not found!']);
    exit;
}

// Check if already collected this month
$current_month = date('Y-m');
$stmt2 = $pdo->prepare("SELECT distribution_date FROM ration_distribution WHERE user_id = ? AND DATE_FORMAT(distribution_date, '%Y-%m') = ?");
$stmt2->execute([$user['user_id'], $current_month]);
$already_collected = $stmt2->fetch(PDO::FETCH_ASSOC);

// Return success with details
echo json_encode([
    'success' => true,
    'user' => $user,
    'already_collected' => $already_collected ? date('d M Y', strtotime($already_collected['distribution_date'])) : false
]);
?>
