<?php
/**
 * Form Validation Utilities
 * Blood Donation Website
 */

require_once __DIR__ . '/functions.php';

/**
 * Validate donor registration form
 */
function validateDonorForm($data) {
    $errors = [];

    // Name validation
    if (empty($data['name'])) {
        $errors['name'] = 'Name is required';
    } elseif (strlen($data['name']) < 2) {
        $errors['name'] = 'Name must be at least 2 characters';
    } elseif (strlen($data['name']) > 100) {
        $errors['name'] = 'Name must not exceed 100 characters';
    }

    // Age validation
    if (empty($data['age'])) {
        $errors['age'] = 'Age is required';
    } elseif (!is_numeric($data['age'])) {
        $errors['age'] = 'Age must be a number';
    } elseif ($data['age'] < 18 || $data['age'] > 65) {
        $errors['age'] = 'Age must be between 18 and 65';
    }

    // Phone validation
    if (empty($data['phone'])) {
        $errors['phone'] = 'Phone is required';
    } elseif (!validatePhone($data['phone'])) {
        $errors['phone'] = 'Phone must be 10 digits';
    }

    // Gender validation
    if (empty($data['gender'])) {
        $errors['gender'] = 'Gender is required';
    } elseif (!in_array($data['gender'], ['Male', 'Female', 'Other'])) {
        $errors['gender'] = 'Invalid gender selection';
    }

    // Blood group validation
    if (empty($data['bloodGroup'])) {
        $errors['bloodGroup'] = 'Blood group is required';
    } elseif (!in_array($data['bloodGroup'], ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'])) {
        $errors['bloodGroup'] = 'Invalid blood group';
    }

    // State validation
    if (empty($data['state'])) {
        $errors['state'] = 'State is required';
    }

    // City validation
    if (empty($data['city'])) {
        $errors['city'] = 'City is required';
    }

    return $errors;
}

/**
 * Validate search form
 */
function validateSearchForm($data) {
    $errors = [];

    if (empty($data['searchBloodGroup'])) {
        $errors['searchBloodGroup'] = 'Blood group is required';
    }

    if (empty($data['searchState'])) {
        $errors['searchState'] = 'State is required';
    }

    if (empty($data['searchCity'])) {
        $errors['searchCity'] = 'City is required';
    }

    return $errors;
}

/**
 * Validate appointment data
 */
function validateAppointment($data) {
    $errors = [];

    if (empty($data['appointment_date'])) {
        $errors['appointment_date'] = 'Date is required';
    } else {
        $date = DateTime::createFromFormat('Y-m-d', $data['appointment_date']);
        if (!$date || $date->format('Y-m-d') !== $data['appointment_date']) {
            $errors['appointment_date'] = 'Invalid date format';
        } elseif ($date < new DateTime('today')) {
            $errors['appointment_date'] = 'Date cannot be in the past';
        }
    }

    if (empty($data['appointment_time'])) {
        $errors['appointment_time'] = 'Time is required';
    }

    if (empty($data['location'])) {
        $errors['location'] = 'Location is required';
    }

    return $errors;
}

/**
 * Validate registration form
 */
function validateRegistrationForm($data) {
    $errors = [];

    if (empty($data['email'])) {
        $errors['email'] = 'Email is required';
    } elseif (!validateEmail($data['email'])) {
        $errors['email'] = 'Invalid email format';
    }

    if (empty($data['password'])) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($data['password']) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    }

    if (empty($data['confirm_password'])) {
        $errors['confirm_password'] = 'Confirm password is required';
    } elseif ($data['password'] !== $data['confirm_password']) {
        $errors['confirm_password'] = 'Passwords do not match';
    }

    return $errors;
}

/**
 * Validate login form
 */
function validateLoginForm($data) {
    $errors = [];

    if (empty($data['email'])) {
        $errors['email'] = 'Email is required';
    } elseif (!validateEmail($data['email'])) {
        $errors['email'] = 'Invalid email format';
    }

    if (empty($data['password'])) {
        $errors['password'] = 'Password is required';
    }

    return $errors;
}
