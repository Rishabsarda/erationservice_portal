<?php
// includes/functions.php

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Logic: Calculate allotted quantity
// Rice: 5 kg per person per month
// Wheat: 3 kg per person
// Sugar: 1 kg per person
// Kerosene: 2 liters per family (flat)
function calculate_ration($family_members) {
    return [
        'rice' => 5 * $family_members,
        'wheat' => 3 * $family_members,
        'sugar' => 1 * $family_members,
        'kerosene' => 2 // Flat 2 liters per family
    ];
}

// Check if valid ration card number (alphanumeric, 10-16 chars)
function is_valid_ration_card($rc_number) {
    return preg_match('/^[a-zA-Z0-9]{10,16}$/', $rc_number);
}
?>
