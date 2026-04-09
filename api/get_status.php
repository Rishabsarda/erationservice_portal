<?php
// api/get_status.php
header('Content-Type: application/json');
require_once '../includes/db.php';

$shop_id = 1;

try {
    $stmt = $pdo->prepare("SELECT * FROM ration_shop WHERE shop_id = ?");
    $stmt->execute([$shop_id]);
    $shop = $stmt->fetch();

    $stmt2 = $pdo->prepare("SELECT * FROM ration_stock WHERE shop_id = ?");
    $stmt2->execute([$shop_id]);
    $stock = $stmt2->fetch();

    if ($shop && $stock) {
        echo json_encode([
            'success' => true,
            'is_open' => $shop['is_open'],
            'shop_name' => $shop['shop_name'],
            'village' => $shop['village'],
            'shop_timings' => $shop['shop_timings'],
            'stock' => [
                'rice' => $stock['rice_qty'],
                'wheat' => $stock['wheat_qty'],
                'sugar' => $stock['sugar_qty'],
                'kerosene' => $stock['kerosene_qty'],
                'last_updated' => $stock['last_updated']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Data not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching status']);
}
?>
