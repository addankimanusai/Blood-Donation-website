<?php
/**
 * User Registration API
 * Blood Donation Website
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Method not allowed', 405);
}

$data = $_POST;

// Validate registration form
$errors = validateRegistrationForm($data);
if (!empty($errors)) {
    jsonResponse(false, $errors, 'Validation failed', 400);
}

// Check if email already exists
$conn = getDBConnection();
if ($conn) {
    $checkSql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($checkSql);
    $email = sanitize($data['email']);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        jsonResponse(false, null, 'Email already registered', 400);
    }
}

// Register user
$result = registerUser($data['email'], $data['password']);

if ($result['success']) {
    jsonResponse(true, ['user_id' => $result['user_id']], 'Registration successful');
} else {
    jsonResponse(false, null, $result['message'], 500);
}
