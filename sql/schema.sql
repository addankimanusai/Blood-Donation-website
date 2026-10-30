-- =============================================
-- Blood Donation Website Database Schema
-- Self-contained schema for local XAMPP setup
-- =============================================

CREATE DATABASE IF NOT EXISTS donorsdatabase
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE donorsdatabase;

-- =============================================
-- Donors
-- =============================================
CREATE TABLE IF NOT EXISTS donors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    age INT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    gender VARCHAR(20) NOT NULL,
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-') NOT NULL,
    state VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    email VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    last_donation_date DATE NULL,
    next_eligible_date DATE NULL,
    KEY idx_blood_group (blood_group),
    KEY idx_location (state, city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Users table for authentication
-- =============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('donor', 'admin') DEFAULT 'donor',
    is_active BOOLEAN DEFAULT TRUE,
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_email (email),
    CONSTRAINT fk_users_donor
      FOREIGN KEY (donor_id) REFERENCES donors(id)
      ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Sessions
-- =============================================
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at INT NOT NULL,
    CONSTRAINT fk_sessions_user
      FOREIGN KEY (user_id) REFERENCES users(id)
      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- CSRF tokens
-- =============================================
CREATE TABLE IF NOT EXISTS csrf_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) UNIQUE NOT NULL,
    user_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at INT NOT NULL,
    CONSTRAINT fk_csrf_user
      FOREIGN KEY (user_id) REFERENCES users(id)
      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Blood inventory
-- =============================================
CREATE TABLE IF NOT EXISTS blood_inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blood_type ENUM('A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-') NOT NULL,
    units_available INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expiry_warning_sent BOOLEAN DEFAULT FALSE,
    UNIQUE KEY unique_blood_type (blood_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO blood_inventory (blood_type, units_available) VALUES
('A+', 0), ('A-', 0), ('B+', 0), ('B-', 0),
('O+', 0), ('O-', 0), ('AB+', 0), ('AB-', 0);

-- =============================================
-- Time slots
-- =============================================
CREATE TABLE IF NOT EXISTS time_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slot_date DATE NOT NULL,
    slot_time TIME NOT NULL,
    max_donations INT DEFAULT 10,
    current_bookings INT DEFAULT 0,
    is_available BOOLEAN DEFAULT TRUE,
    location VARCHAR(255) DEFAULT 'Main Blood Bank',
    UNIQUE KEY unique_slot (slot_date, slot_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Appointments
-- =============================================
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    time_slot_id INT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled', 'no_show') DEFAULT 'pending',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_appointments_donor
      FOREIGN KEY (donor_id) REFERENCES donors(id)
      ON DELETE CASCADE,
    CONSTRAINT fk_appointments_slot
      FOREIGN KEY (time_slot_id) REFERENCES time_slots(id)
      ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Donation history
-- =============================================
CREATE TABLE IF NOT EXISTS donation_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    donation_date DATE NOT NULL,
    blood_unit_id VARCHAR(50) NULL,
    status ENUM('completed', 'cancelled', 'pending') DEFAULT 'completed',
    next_eligible_date DATE NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_donation_history_donor
      FOREIGN KEY (donor_id) REFERENCES donors(id)
      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Notifications queue
-- =============================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    notification_type ENUM('email', 'sms') NOT NULL,
    recipient VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    scheduled_at DATETIME NULL,
    sent_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user
      FOREIGN KEY (user_id) REFERENCES users(id)
      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Blood compatibility reference
-- =============================================
CREATE TABLE IF NOT EXISTS blood_compatibility (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_blood_type ENUM('A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-') NOT NULL,
    recipient_blood_type ENUM('A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-') NOT NULL,
    is_compatible BOOLEAN NOT NULL,
    UNIQUE KEY unique_compatibility (donor_blood_type, recipient_blood_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO blood_compatibility (donor_blood_type, recipient_blood_type, is_compatible) VALUES
('A+', 'A+', TRUE), ('A+', 'AB+', TRUE),
('A-', 'A-', TRUE), ('A-', 'A+', TRUE), ('A-', 'AB-', TRUE), ('A-', 'AB+', TRUE),
('B+', 'B+', TRUE), ('B+', 'AB+', TRUE),
('B-', 'B-', TRUE), ('B-', 'B+', TRUE), ('B-', 'AB-', TRUE), ('B-', 'AB+', TRUE),
('O+', 'O+', TRUE), ('O+', 'A+', TRUE), ('O+', 'B+', TRUE), ('O+', 'AB+', TRUE),
('O-', 'A+', TRUE), ('O-', 'A-', TRUE), ('O-', 'B+', TRUE), ('O-', 'B-', TRUE),
('O-', 'AB+', TRUE), ('O-', 'AB-', TRUE), ('O-', 'O+', TRUE), ('O-', 'O-', TRUE),
('AB+', 'AB+', TRUE),
('AB-', 'AB-', TRUE), ('AB-', 'AB+', TRUE);

-- Create an admin account after deployment by registering normally, then run:
-- UPDATE users SET role = 'admin' WHERE email = 'your-admin-email@example.com';
