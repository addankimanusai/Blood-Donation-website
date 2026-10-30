<?php
/**
 * User Logout API
 * Blood Donation Website
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

logoutUser();
jsonResponse(true, null, 'Logged out successfully');
