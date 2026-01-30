<?php
/**
 * Helper functions for the School Management System
 */

/**
 * Send JSON response
 */
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate date format (YYYY-MM-DD)
 */
function isValidDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Format date for display
 */
function formatDate($dateString, $format = 'M j, Y') {
    $date = new DateTime($dateString);
    return $date->format($format);
}

/**
 * Calculate age from date of birth
 */
function calculateAge($dob) {
    $birthDate = new DateTime($dob);
    $currentDate = new DateTime();
    return $currentDate->diff($birthDate)->y;
}

/**
 * Generate a random password
 */
function generateRandomPassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    return substr(str_shuffle($chars), 0, $length);
}

/**
 * Check if user has permission to access resource
 * This is a simplified version - in a real app you'd have more sophisticated permission checking
 */
function hasPermission($userId, $resourceType, $action, $resourceOwnerId = null) {
    // In a real implementation, you would check permissions based on user role
    // For now, we'll just allow users to access their own resources
    if ($resourceOwnerId && $userId != $resourceOwnerId) {
        // Check if user is admin
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN') {
            return true;
        }
        return false;
    }
    return true;
}

/**
 * Log activity
 */
function logActivity($userId, $action, $details = '') {
    // This would typically log to a database
    // For now, we'll just return true
    return true;
}

/**
 * Get grade letter from score
 */
function getGradeFromScore($score) {
    if ($score >= 90) return 'A';
    if ($score >= 80) return 'B';
    if ($score >= 70) return 'C';
    if ($score >= 60) return 'D';
    return 'F';
}

/**
 * Format currency
 */
function formatCurrency($amount, $currency = 'USD') {
    return '$' . number_format($amount, 2);
}
?>