<?php
/**
 * Donation Form Submission Handler
 * Blood Donation Website
 */

header('Content-Type: application/json');

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $age = (int)($_POST['age'] ?? 0);
    $phone = sanitize($_POST['phone'] ?? '');
    $gender = sanitize($_POST['gender'] ?? '');
    $bloodGroup = sanitize($_POST['bloodGroup'] ?? '');
    $state = sanitize($_POST['state'] ?? '');
    $city = sanitize($_POST['city'] ?? '');

    // Validate
    $errors = [];
    if (empty($name) || strlen($name) < 2) {
        $errors['name'] = 'Name must be at least 2 characters';
    }
    if ($age < 18 || $age > 65) {
        $errors['age'] = 'Age must be between 18 and 65';
    }
    if (!validatePhone($phone)) {
        $errors['phone'] = 'Phone must be 10 digits';
    }
    if (empty($gender)) {
        $errors['gender'] = 'Gender is required';
    }
    if (empty($bloodGroup)) {
        $errors['bloodGroup'] = 'Blood group is required';
    }
    if (empty($state)) {
        $errors['state'] = 'State is required';
    }
    if (empty($city)) {
        $errors['city'] = 'City is required';
    }

    if (!empty($errors)) {
        jsonResponse(false, $errors, 'Validation failed', 400);
    }

    $conn = getDBConnection();
    if (!$conn) {
        jsonResponse(false, null, 'Database connection failed', 500);
    }

    // Check eligibility (if returning donor)
    $checkSql = "SELECT id, last_donation_date FROM donors WHERE phone = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $existing = $result->fetch_assoc();
        if ($existing['last_donation_date']) {
            $nextDate = date('Y-m-d', strtotime($existing['last_donation_date'] . ' + ' . DONATION_INTERVAL_DAYS . ' days'));
            if (date('Y-m-d') < $nextDate) {
                jsonResponse(false, null, "You are not eligible to donate until {$nextDate}", 400);
            }
        }
    }

    // Insert donor
    $sql = "INSERT INTO donors (name, age, phone, gender, blood_group, state, city) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisssss", $name, $age, $phone, $gender, $bloodGroup, $state, $city);

    if ($stmt->execute()) {
        $donorId = $conn->insert_id;

        // If logged in, link donation to user
        if (isLoggedIn() && !empty($_SESSION['donor_id'])) {
            // Update user's donor_id if not set
            $userSql = "UPDATE users SET donor_id = ? WHERE id = ? AND donor_id IS NULL";
            $userStmt = $conn->prepare($userSql);
            $userStmt->bind_param("ii", $donorId, $_SESSION['user_id']);
            $userStmt->execute();
        }

        jsonResponse(true, ['donor_id' => $donorId], 'Thank you for registering as a donor!');
    } else {
        jsonResponse(false, null, 'Failed to submit donation. Please try again.', 500);
    }
}

// If not POST, show HTML form response (backward compatibility)
include 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Submission</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body { font-family: 'Poppins', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; background-color: #fdf2f2; color: #4d0000; text-align: center; }
        .response-container { background-color: #ffebeb; border: 2px solid #d9534f; border-radius: 12px; padding: 40px; max-width: 500px; box-shadow: 0 8px 16px rgba(0,0,0,0.15); }
        h1 { color: #d9534f; font-weight: 700; font-size: 2em; margin-bottom: 15px; }
        p { font-size: 1.1em; line-height: 1.6; color: #5c5c5c; }
        .button { display: inline-block; margin-top: 20px; padding: 10px 24px; background-color: #d9534f; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; transition: background-color 0.3s; }
        .button:hover { background-color: #b52d2b; }
    </style>
</head>
<body>
    <div class="response-container">
        <h1>Form Incomplete</h1>
        <p>Please complete the form properly.</p>
        <a href="index.html" class="button">Return to Home</a>
    </div>
</body>
</html>
<?php $conn->close(); ?>
