<?php
/**
 * Admin Authentication Middleware
 * Blood Donation Website
 */

require_once __DIR__ . '/../includes/auth.php';

if (!isLoggedIn()) {
    header('HTTP/1.1 403 Forbidden');
    header('Location: ../pages/login.html');
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    header('Location: ../index.html');
    exit;
}
