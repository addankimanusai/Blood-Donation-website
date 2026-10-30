<?php
/**
 * User Login API
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

// Validate login form
$errors = validateLoginForm($data);
if (!empty($errors)) {
    jsonResponse(false, $errors, 'Validation failed', 400);
}

// Login user
$result = loginUser($data['email'], $data['password']);

if ($result['success']) {
    jsonResponse(true, ['user' => $result['user']], 'Login successful');
} else {
    jsonResponse(false, null, $result['message'], 401);
}
