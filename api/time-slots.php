<?php
/**
 * Time Slots API
 * Blood Donation Website
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/functions.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    jsonResponse(false, null, 'Method not allowed', 405);
}

// Get available time slots
$date = sanitize($_GET['date'] ?? '');

if (empty($date)) {
    jsonResponse(false, null, 'Date is required', 400);
}

$selectedDate = DateTime::createFromFormat('Y-m-d', $date);
if (!$selectedDate || $selectedDate->format('Y-m-d') !== $date || $selectedDate < new DateTime('today')) {
    jsonResponse(false, null, 'Please select a valid future date', 400);
}

$conn = getDBConnection();
if (!$conn) {
    jsonResponse(false, null, 'Database connection failed', 500);
}

// New installations have no pre-created calendar records. Add the standard
// donation slots when a visitor chooses a date, while preserving any slots
// an administrator has already configured.
$defaultSlots = ['09:00:00', '11:00:00', '13:00:00', '15:00:00'];
$seedSql = "INSERT IGNORE INTO time_slots (slot_date, slot_time, max_donations, current_bookings, is_available, location)
            VALUES (?, ?, 10, 0, TRUE, 'Main Blood Bank')";
$seedStmt = $conn->prepare($seedSql);
foreach ($defaultSlots as $defaultSlot) {
    $seedStmt->bind_param('ss', $date, $defaultSlot);
    $seedStmt->execute();
}

$sql = "SELECT id, slot_time, max_donations, current_bookings, location
        FROM time_slots
        WHERE slot_date = ? AND is_available = TRUE AND current_bookings < max_donations
        ORDER BY slot_time";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

$slots = [];
while ($row = $result->fetch_assoc()) {
    $slots[] = [
        'id' => $row['id'],
        'time' => $row['slot_time'],
        'max' => $row['max_donations'],
        'booked' => $row['current_bookings'],
        'available' => $row['max_donations'] - $row['current_bookings'],
        'location' => $row['location']
    ];
}

jsonResponse(true, $slots, 'Time slots retrieved');
