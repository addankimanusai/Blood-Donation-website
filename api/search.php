<?php
/**
 * Donor Search API
 * Blood Donation Website
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Method not allowed', 405);
}

// Get search parameters
$bloodGroup = sanitize($_GET['bloodGroup'] ?? $_POST['searchBloodGroup'] ?? '');
$state = sanitize($_GET['state'] ?? $_POST['searchState'] ?? '');
$city = sanitize($_GET['city'] ?? $_POST['searchCity'] ?? '');
$page = (int)($_GET['page'] ?? 1);

if (empty($bloodGroup) || empty($state) || empty($city)) {
    jsonResponse(false, null, 'All search fields are required', 400);
}

$conn = getDBConnection();
if (!$conn) {
    jsonResponse(false, null, 'Database connection failed', 500);
}

// Build query
$conditions = ['blood_group = ?'];
$params = [$bloodGroup];
$types = 's';

if (!empty($state)) {
    $conditions[] = 'state = ?';
    $params[] = $state;
    $types .= 's';
}

if (!empty($city)) {
    $conditions[] = 'city = ?';
    $params[] = $city;
    $types .= 's';
}

// Count total results
$countSql = "SELECT COUNT(*) FROM donors WHERE " . implode(' AND ', $conditions);
$stmt = $conn->prepare($countSql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$totalResults = $stmt->get_result()->fetch_row()[0];

// Pagination
$pagination = paginate($totalResults, $page, RESULTS_PER_PAGE);

// Fetch results
$query = "SELECT * FROM donors WHERE " . implode(' AND ', $conditions) . " LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$params[] = $pagination['per_page'];
$params[] = $pagination['offset'];
$types .= 'ii';
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$donors = [];
while ($row = $result->fetch_assoc()) {
    $donors[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'age' => $row['age'],
        'phone' => $row['phone'],
        'gender' => $row['gender'],
        'blood_group' => $row['blood_group'],
        'state' => $row['state'],
        'city' => $row['city']
    ];
}

jsonResponse(true, [
    'donors' => $donors,
    'pagination' => $pagination,
    'total' => $totalResults
], 'Search completed');
