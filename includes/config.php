<?php
/**
 * Centralized Configuration
 * Blood Donation Website
 */

// Database Configuration
// Production credentials belong in environment variables or config.local.php, never Git.
$localConfig = [];
$localConfigPath = __DIR__ . '/config.local.php';
if (is_file($localConfigPath)) {
    $loadedConfig = require $localConfigPath;
    if (is_array($loadedConfig)) {
        $localConfig = $loadedConfig;
    }
}

function configValue($key, $fallback = '') {
    $environmentValue = getenv($key);
    if ($environmentValue !== false && $environmentValue !== '') {
        return $environmentValue;
    }

    global $localConfig;
    return $localConfig[$key] ?? $fallback;
}

define('APP_ENV', configValue('APP_ENV', 'development'));
define('DB_HOST', configValue('DB_HOST', 'localhost'));
define('DB_USER', configValue('DB_USER', 'root'));
define('DB_PASS', configValue('DB_PASS', ''));
define('DB_NAME', configValue('DB_NAME', 'donorsdatabase'));

// Security
define('SESSION_LIFETIME', 3600 * 24); // 24 hours
define('CSRF_TOKEN_LIFETIME', 3600);    // 1 hour
define('HASH_COST', 12);                // bcrypt cost

// Site Configuration
define('SITE_URL', configValue('SITE_URL', 'http://localhost/Blood%20Donation%20Website'));
define('ADMIN_EMAIL', configValue('ADMIN_EMAIL', 'admin@bloodbond.com'));

// Pagination
define('RESULTS_PER_PAGE', 20);
define('MAX_SEARCH_RESULTS', 100);

// Donation Eligibility (in days - approximately 4 months)
define('DONATION_INTERVAL_DAYS', 120);

// Email Configuration (SMTP)
define('SMTP_HOST', configValue('SMTP_HOST', ''));
define('SMTP_USER', configValue('SMTP_USER', ''));
define('SMTP_PASS', configValue('SMTP_PASS', ''));
define('SMTP_PORT', (int) configValue('SMTP_PORT', 587));

// Timezone
date_default_timezone_set('Asia/Kolkata');
