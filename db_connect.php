<?php
/**
 * Backward-compatible database bootstrap for legacy pages.
 * Credentials are read from environment variables or includes/config.local.php.
 */

require_once __DIR__ . '/includes/functions.php';

$conn = getDBConnection();
if (!$conn) {
    http_response_code(500);
    exit('Database connection failed. Please check the server configuration.');
}
