<?php
/**
 * Blood Inventory API
 * Blood Donation Website
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Anyone can view inventory
        getInventory();
        break;
    case 'PUT':
        // Only admin can update inventory
        requireAdmin();
        updateInventory();
        break;
    default:
        jsonResponse(false, null, 'Method not allowed', 405);
}

function getInventory() {
    $conn = getDBConnection();
    if (!$conn) {
        jsonResponse(false, null, 'Database connection failed', 500);
    }

    $sql = "SELECT blood_type, units_available, last_updated FROM blood_inventory ORDER BY blood_type";
    $result = $conn->query($sql);

    $inventory = [];
    while ($row = $result->fetch_assoc()) {
        $inventory[] = [
            'blood_type' => $row['blood_type'],
            'units' => (int)$row['units_available'],
            'last_updated' => $row['last_updated'],
            'status' => $row['units_available'] < 10 ? 'low' : 'ok'
        ];
    }

    jsonResponse(true, $inventory, 'Inventory retrieved');
}

function updateInventory() {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['blood_type']) || !isset($data['units'])) {
        jsonResponse(false, null, 'Blood type and units are required', 400);
    }

    $validTypes = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
    if (!in_array($data['blood_type'], $validTypes)) {
        jsonResponse(false, null, 'Invalid blood type', 400);
    }

    $conn = getDBConnection();
    if (!$conn) {
        jsonResponse(false, null, 'Database connection failed', 500);
    }

    $sql = "UPDATE blood_inventory SET units_available = ? WHERE blood_type = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $data['units'], $data['blood_type']);

    if ($stmt->execute()) {
        jsonResponse(true, null, 'Inventory updated');
    } else {
        jsonResponse(false, null, 'Update failed', 500);
    }
}
