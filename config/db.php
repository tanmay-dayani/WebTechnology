<?php
/**
 * Database Configuration & Connection Helper
 * Practical 8: MySQL Schema Design, Connectivity, and Prepared Statements
 */

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_NAME', getenv('DB_NAME') ?: 'studenthub_db');
define('DB_PORT', getenv('DB_PORT') ? intval(getenv('DB_PORT')) : 3306);

// Disable default exception throwing in PHP 8.1+ so we can handle connection gracefully
mysqli_report(MYSQLI_REPORT_OFF);

$conn = null;
$db_connected = false;
$db_error_message = '';

try {
    // Attempt database connection using MySQLi
    $conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($conn) {
        $db_connected = true;
        mysqli_set_charset($conn, "utf8mb4");
    } else {
        $db_error_message = mysqli_connect_error();
    }
} catch (Throwable $e) {
    $conn = null;
    $db_connected = false;
    $db_error_message = $e->getMessage();
}

/**
 * Returns active database connection or null
 */
function get_db_connection() {
    global $conn;
    return $conn;
}

/**
 * Basic Sanitization Helper (Practical 7)
 */
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Helper to display flash alerts
 */
function display_alert($message, $type = 'info') {
    $class = 'alert-' . $type;
    echo "<div class='alert {$class}' role='alert'>" . htmlspecialchars($message) . "</div>";
}
