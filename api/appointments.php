<?php
/**
 * Appointments API
 * Blood Donation Website
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validator.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getAppointments();
        break;
    case 'POST':
        createAppointment();
        break;
    case 'PUT':
        updateAppointment();
        break;
    case 'DELETE':
        cancelAppointment();
        break;
    default:
        jsonResponse(false, null, 'Method not allowed', 405);
}

function getAppointments() {
    requireAuth();

    $conn = getDBConnection();
    if (!$conn) {
        jsonResponse(false, null, 'Database connection failed', 500);
    }

    $user = getCurrentUser();
    $isAdmin = $user['role'] === 'admin';

    if ($isAdmin) {
        $sql = "SELECT a.*, d.name as donor_name, d.phone as donor_phone
                FROM appointments a
                JOIN donors d ON a.donor_id = d.id
                ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        $stmt = $conn->prepare($sql);
    } else {
        $sql = "SELECT a.* FROM appointments a WHERE a.donor_id = ? ORDER BY a.appointment_date DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user['donor_id']);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }

    jsonResponse(true, $appointments, 'Appointments retrieved');
}

function createAppointment() {
    requireAuth();

    $data = json_decode(file_get_contents('php://input'), true);
    $user = getCurrentUser();

    $errors = validateAppointment($data ?? []);
    if (!empty($errors)) {
        jsonResponse(false, $errors, 'Please provide a valid appointment', 400);
    }

    $conn = getDBConnection();
    if (!$conn) {
        jsonResponse(false, null, 'Database connection failed', 500);
    }

    // Check if donor_id exists for user
    $donorId = $user['donor_id'];
    if (!$donorId) {
        // Get donor_id from donors table by email or create basic record
        jsonResponse(false, null, 'No donor record found. Please register as a donor first.', 400);
    }

    $date = $data['appointment_date'];
    $time = $data['appointment_time'];
    $location = sanitize($data['location']);

    $conn->begin_transaction();

    try {
        $slotSql = "SELECT id, max_donations, current_bookings
                    FROM time_slots
                    WHERE slot_date = ? AND slot_time = ? AND is_available = TRUE
                    FOR UPDATE";
        $slotStmt = $conn->prepare($slotSql);
        $slotStmt->bind_param('ss', $date, $time);
        $slotStmt->execute();
        $slot = $slotStmt->get_result()->fetch_assoc();

        if (!$slot || $slot['current_bookings'] >= $slot['max_donations']) {
            $conn->rollback();
            jsonResponse(false, null, 'This time slot is no longer available. Please choose another one.', 409);
        }

        $sql = "INSERT INTO appointments (donor_id, time_slot_id, appointment_date, appointment_time, location, status)
                VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iisss', $donorId, $slot['id'], $date, $time, $location);
        $stmt->execute();
        $appointmentId = $conn->insert_id;

        $updateSlot = $conn->prepare('UPDATE time_slots SET current_bookings = current_bookings + 1 WHERE id = ?');
        $updateSlot->bind_param('i', $slot['id']);
        $updateSlot->execute();

        $conn->commit();
        jsonResponse(true, ['id' => $appointmentId], 'Appointment created');
    } catch (Throwable $exception) {
        $conn->rollback();
        error_log('Appointment creation failed: ' . $exception->getMessage());
        jsonResponse(false, null, 'Failed to create appointment', 500);
    }
}

function updateAppointment() {
    requireAdmin();

    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id']) || empty($data['status'])) {
        jsonResponse(false, null, 'ID and status are required', 400);
    }

    $validStatuses = ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'];
    if (!in_array($data['status'], $validStatuses)) {
        jsonResponse(false, null, 'Invalid status', 400);
    }

    $conn = getDBConnection();
    if (!$conn) {
        jsonResponse(false, null, 'Database connection failed', 500);
    }

    $sql = "UPDATE appointments SET status = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $data['status'], $data['id']);

    if ($stmt->execute()) {
        jsonResponse(true, null, 'Appointment updated');
    } else {
        jsonResponse(false, null, 'Update failed', 500);
    }
}

function cancelAppointment() {
    requireAuth();

    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id'])) {
        jsonResponse(false, null, 'Appointment ID is required', 400);
    }

    $conn = getDBConnection();
    if (!$conn) {
        jsonResponse(false, null, 'Database connection failed', 500);
    }

    $user = getCurrentUser();
    $isAdmin = $user['role'] === 'admin';

    if ($isAdmin) {
        $sql = "UPDATE appointments SET status = 'cancelled' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $data['id']);
    } else {
        $sql = "UPDATE appointments SET status = 'cancelled' WHERE id = ? AND donor_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $data['id'], $user['donor_id']);
    }

    if ($stmt->execute()) {
        jsonResponse(true, null, 'Appointment cancelled');
    } else {
        jsonResponse(false, null, 'Cancellation failed', 500);
    }
}
