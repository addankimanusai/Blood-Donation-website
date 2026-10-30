<?php
/**
 * Reusable Utility Functions
 * Blood Donation Website
 */

require_once __DIR__ . '/config.php';

/**
 * Sanitize input data
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_tokens'])) {
        $_SESSION['csrf_tokens'] = [];
    }

    $token = bin2hex(random_bytes(32));
    $expires = time() + CSRF_TOKEN_LIFETIME;

    // Store token
    $_SESSION['csrf_tokens'][$token] = $expires;

    // Clean expired tokens
    foreach ($_SESSION['csrf_tokens'] as $t => $exp) {
        if ($exp < time()) {
            unset($_SESSION['csrf_tokens'][$t]);
        }
    }

    return $token;
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    if (empty($_SESSION['csrf_tokens'][$token])) {
        return false;
    }

    if ($_SESSION['csrf_tokens'][$token] < time()) {
        unset($_SESSION['csrf_tokens'][$token]);
        return false;
    }

    return true;
}

/**
 * Get database connection
 */
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        return null;
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}

/**
 * Send JSON response
 */
function jsonResponse($success, $data = null, $message = '', $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (10 digits)
 */
function validatePhone($phone) {
    return preg_match('/^[0-9]{10}$/', $phone);
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
}

/**
 * Calculate pagination values
 */
function paginate($total, $page, $perPage) {
    $totalPages = ceil($total / $perPage);
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;

    return [
        'page' => $page,
        'per_page' => $perPage,
        'offset' => $offset,
        'total_pages' => $totalPages,
        'has_prev' => $page > 1,
        'has_next' => $page < $totalPages
    ];
}

/**
 * Check if donor is eligible to donate
 */
function checkEligibility($donorId, $conn) {
    $sql = "SELECT last_donation_date FROM donors WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $donorId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return ['eligible' => true, 'reason' => 'No prior donations'];
    }

    $donor = $result->fetch_assoc();
    $lastDate = $donor['last_donation_date'];

    if (!$lastDate) {
        return ['eligible' => true, 'reason' => 'No prior donations'];
    }

    $nextEligible = date('Y-m-d', strtotime($lastDate . ' + ' . DONATION_INTERVAL_DAYS . ' days'));
    $today = date('Y-m-d');

    if ($today >= $nextEligible) {
        return ['eligible' => true, 'next_date' => null];
    }

    return ['eligible' => false, 'next_date' => $nextEligible];
}

/**
 * Hash password securely
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Start secure session
 */
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.use_strict_mode', 1);
        session_start();
    }
}

/**
 * Generate random string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Log error message
 */
function logError($message, $context = []) {
    $log = date('Y-m-d H:i:s') . ' - ' . $message;
    if (!empty($context)) {
        $log .= ' - ' . json_encode($context);
    }
    $log .= PHP_EOL;
    error_log($log);
}
