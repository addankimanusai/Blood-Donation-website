<?php
/**
 * Donors API
 * Blood Donation Website
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getDonors();
        break;
    case 'POST':
        createDonor();
        break;
    case 'PUT':
        updateDonor();
        break;
    default:
        jsonResponse(false, null, 'Method not allowed', 405);
}

function getDonors() {
    requireAdmin();

    $conn = getDBConnection();
    if (!$conn) {
        jsonResponse(false, null, 'Database connection failed', 500);
    }

    $page = (int)($_GET['page'] ?? 1);
    $search = sanitize($_GET['search'] ?? '');

    // Count total
    $countSql = "SELECT COUNT(*) FROM donors WHERE is_active = TRUE";
    $params = [];
    if (!empty($search)) {
        $countSql .= " AND (name LIKE ? OR phone LIKE ? OR email LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
    }
    $stmt = $conn->prepare($countSql);
    if (!empty($params)) {
        $stmt->bind_param("sss", ...$params);
    }
    $stmt->execute();
    $total = $stmt->get_result()->fetch_row()[0];

    $pagination = paginate($total, $page, RESULTS_PER_PAGE);

    // Fetch donors
    $sql = "SELECT * FROM donors WHERE is_active = TRUE";
    if (!empty($search)) {
        $sql .= " AND (name LIKE ? OR phone LIKE ? OR email LIKE ?)";
    }
    $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    if (!empty($search)) {
        $params[] = $pagination['per_page'];
        $params[] = $pagination['offset'];
        $stmt->bind_param("sssii", ...$params);
    } else {
        $stmt->bind_param("ii", $pagination['per_page'], $pagination['offset']);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $donors = [];
    while ($row = $result->fetch_assoc()) {
        $donors[] = $row;
    }

    jsonResponse(true, [
        'donors' => $donors,
        'pagination' => $pagination
    ], 'Donors retrieved');
}

function createDonor() {
    requireAuth();

    $data = json_decode(file_get_contents('php://input'), true);

    $conn = getDBConnection();
    if (!$conn) {
        jsonResponse(false, null, 'Database connection failed', 500);
    }

    $sql = "INSERT INTO donors (name, age, phone, gender, blood_group, state, city)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisssss", $data['name'], $data['age'], $data['phone'],
                       $data['gender'], $data['bloodGroup'], $data['state'], $data['city']);

    if ($stmt->execute()) {
        $donorId = $conn->insert_id;
        jsonResponse(true, ['id' => $donorId], 'Donor created');
    } else {
        jsonResponse(false, null, 'Failed to create donor', 500);
    }
}

function updateDonor() {
    requireAdmin();

    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id'])) {
        jsonResponse(false, null, 'Donor ID is required', 400);
    }

    $conn = getDBConnection();
    if (!$conn) {
        jsonResponse(false, null, 'Database connection failed', 500);
    }

    $sql = "UPDATE donors SET name = ?, age = ?, phone = ?, gender = ?,
            blood_group = ?, state = ?, city = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisssssi", $data['name'], $data['age'], $data['phone'],
                       $data['gender'], $data['bloodGroup'], $data['state'], $data['city'], $data['id']);

    if ($stmt->execute()) {
        jsonResponse(true, null, 'Donor updated');
    } else {
        jsonResponse(false, null, 'Update failed', 500);
    }
}
