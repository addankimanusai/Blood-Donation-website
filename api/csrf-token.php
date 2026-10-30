<?php
/**
 * CSRF Token API
 * Blood Donation Website
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/functions.php';

startSecureSession();

$token = generateCSRFToken();
jsonResponse(true, [
    'token' => $token,
    'expires' => time() + CSRF_TOKEN_LIFETIME
], 'Token generated');
