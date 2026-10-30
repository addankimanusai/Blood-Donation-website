<?php
/**
 * Notification Service
 * Blood Donation Website
 */

require_once __DIR__ . '/functions.php';

class NotificationService {
    private static $conn = null;

    /**
     * Get database connection
     */
    private static function getConnection() {
        if (self::$conn === null) {
            self::$conn = getDBConnection();
        }
        return self::$conn;
    }

    /**
     * Queue an email notification
     */
    public static function queueEmail($userId, $to, $subject, $body, $scheduledAt = null) {
        $conn = self::getConnection();
        if (!$conn) {
            return false;
        }

        $sql = "INSERT INTO notifications (user_id, notification_type, recipient, subject, message, scheduled_at)
                VALUES (?, 'email', ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $scheduledAt = $scheduledAt ?: date('Y-m-d H:i:s');
        $stmt->bind_param("isss", $userId, $to, $subject, $body, $scheduledAt);

        return $stmt->execute();
    }

    /**
     * Send donation confirmation email
     */
    public static function sendDonationConfirmation($donorId, $donorName, $email) {
        $subject = 'Blood Donation Registration Confirmed';
        $body = "Dear {$donorName},\n\n";
        $body .= "Thank you for registering as a blood donor. Your donation registration has been confirmed.\n\n";
        $body .= "We appreciate your willingness to save lives.\n\n";
        $body .= "Best regards,\nBlood Bond Team";

        return self::queueEmail($donorId, $email, $subject, $body);
    }

    /**
     * Send eligibility reminder
     */
    public static function sendEligibilityReminder($donorId, $donorName, $email, $nextDate) {
        $subject = 'You Are Now Eligible to Donate Blood';
        $body = "Dear {$donorName},\n\n";
        $body .= "Great news! You are now eligible to donate blood again.\n";
        $body .= "Your next eligible date is: {$nextDate}\n\n";
        $body .= "Please schedule an appointment at your convenience.\n\n";
        $body .= "Best regards,\nBlood Bond Team";

        return self::queueEmail($donorId, $email, $subject, $body);
    }

    /**
     * Send appointment reminder
     */
    public static function sendAppointmentReminder($donorId, $donorName, $email, $date, $time, $location) {
        $subject = 'Blood Donation Appointment Reminder';
        $body = "Dear {$donorName},\n\n";
        $body .= "This is a reminder of your upcoming blood donation appointment.\n\n";
        $body .= "Date: {$date}\n";
        $body .= "Time: {$time}\n";
        $body .= "Location: {$location}\n\n";
        $body .= "Please arrive 15 minutes early.\n\n";
        $body .= "Best regards,\nBlood Bond Team";

        return self::queueEmail($donorId, $email, $subject, $body);
    }

    /**
     * Send low inventory alert to admin
     */
    public static function sendLowInventoryAlert($adminEmail, $bloodType, $units) {
        $subject = 'Low Blood Inventory Alert';
        $body = "Alert: Blood type {$bloodType} inventory is low.\n";
        $body .= "Current units available: {$units}\n";
        $body .= "Please take necessary action to organize a blood drive.";

        return self::queueEmail(1, $adminEmail, $subject, $body);
    }

    /**
     * Process notification queue (for cron job)
     */
    public static function processQueue() {
        $conn = self::getConnection();
        if (!$conn) {
            return false;
        }

        // Get pending notifications
        $sql = "SELECT * FROM notifications WHERE status = 'pending' AND scheduled_at <= NOW() LIMIT 10";
        $result = $conn->query($sql);

        $processed = 0;
        while ($notification = $result->fetch_assoc()) {
            // In production, use proper SMTP library like PHPMailer
            // For now, just mark as sent
            $updateSql = "UPDATE notifications SET status = 'sent', sent_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param("i", $notification['id']);
            $stmt->execute();
            $processed++;
        }

        return $processed;
    }

    /**
     * Get user notification preferences
     */
    public static function getPreferences($userId) {
        $conn = self::getConnection();
        if (!$conn) {
            return null;
        }

        $sql = "SELECT email_notifications, sms_notifications FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        return $result->fetch_assoc();
    }
}
