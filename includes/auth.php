<?php
/**
 * Authentication Utilities
 * Blood Donation Website
 */

require_once __DIR__ . '/functions.php';

startSecureSession();

/**
 * Register new user
 */
function registerUser($email, $password, $donorId = null, $role = 'donor') {
    $conn = getDBConnection();
    if (!$conn) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }

    // Check if email already exists
    $checkSql = "SELECT id FROM users WHERE email = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        return ['success' => false, 'message' => 'Email already registered'];
    }

    // Hash password
    $passwordHash = hashPassword($password);

    // Insert user
    $sql = "INSERT INTO users (donor_id, email, password_hash, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($donorId) {
        $stmt->bind_param("isss", $donorId, $email, $passwordHash, $role);
    } else {
        $nullDonorId = null;
        $stmt->bind_param("isss", $nullDonorId, $email, $passwordHash, $role);
    }

    if ($stmt->execute()) {
        $userId = $conn->insert_id;
        return [
            'success' => true,
            'message' => 'Registration successful',
            'user_id' => $userId
        ];
    }

    return ['success' => false, 'message' => 'Registration failed'];
}

/**
 * Login user
 */
function loginUser($email, $password) {
    $conn = getDBConnection();
    if (!$conn) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }

    $sql = "SELECT id, donor_id, password_hash, role, is_active FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }

    $user = $result->fetch_assoc();

    if (!$user['is_active']) {
        return ['success' => false, 'message' => 'Account is deactivated'];
    }

    if (!verifyPassword($password, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }

    // Update last login
    $updateSql = "UPDATE users SET last_login = NOW() WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("i", $user['id']);
    $updateStmt->execute();

    // Create session
    $sessionId = generateRandomString(128);
    $expiresAt = time() + SESSION_LIFETIME;

    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $sessionSql = "INSERT INTO sessions (id, user_id, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, ?)";
    $sessionStmt = $conn->prepare($sessionSql);
    $sessionStmt->bind_param("sissi", $sessionId, $user['id'], $ipAddress, $userAgent, $expiresAt);
    $sessionStmt->execute();

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['donor_id'] = $user['donor_id'];
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $user['role'];
    $_SESSION['session_id'] = $sessionId;

    return [
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'email' => $email,
            'role' => $user['role']
        ]
    ];
}

/**
 * Logout user
 */
function logoutUser() {
    if (!empty($_SESSION['session_id'])) {
        $conn = getDBConnection();
        if ($conn) {
            $sql = "DELETE FROM sessions WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $_SESSION['session_id']);
            $stmt->execute();
        }
    }

    // Clear session
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    if (empty($_SESSION['user_id']) || empty($_SESSION['session_id'])) {
        return false;
    }

    $conn = getDBConnection();
    if (!$conn) {
        return false;
    }

    $sql = "SELECT expires_at FROM sessions WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $_SESSION['session_id'], $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return false;
    }

    $session = $result->fetch_assoc();
    if ($session['expires_at'] < time()) {
        logoutUser();
        return false;
    }

    return true;
}

/**
 * Require authentication - redirects if not logged in
 */
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: pages/login.html');
        exit;
    }
}

/**
 * Require admin role - redirects if not admin
 */
function requireAdmin() {
    requireAuth();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('Location: index.html');
        exit;
    }
}

/**
 * Get current user
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'] ?? null,
        'donor_id' => $_SESSION['donor_id'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'role' => $_SESSION['role'] ?? null
    ];
}

/**
 * Regenerate session ID (prevent session fixation)
 */
function regenerateSession() {
    session_regenerate_id(true);
    $_SESSION['session_id'] = generateRandomString(128);
}
