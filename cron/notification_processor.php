<?php
/**
 * Notification Processor Cron Job
 * Blood Donation Website
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/notification_service.php';

// Process notification queue
$processed = NotificationService::processQueue();

echo "Processed {$processed} notifications\n";

// Check eligibility reminders (donors who became eligible today)
$conn = getDBConnection();
if ($conn && $processed >= 0) {
    $today = date('Y-m-d');

    // Find donors eligible today and send reminders
    $sql = "SELECT u.id as user_id, u.email, d.name, d.next_eligible_date
            FROM users u
            JOIN donors d ON u.donor_id = d.id
            WHERE d.next_eligible_date = ? AND u.is_active = TRUE";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($donor = $result->fetch_assoc()) {
        NotificationService::sendEligibilityReminder(
            $donor['user_id'],
            $donor['name'],
            $donor['email'],
            $donor['next_eligible_date']
        );
        echo "Sent eligibility reminder to {$donor['email']}\n";
    }
}

echo "Cron job completed at " . date('Y-m-d H:i:s') . "\n";
