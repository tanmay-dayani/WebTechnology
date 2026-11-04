<?php
/**
 * Audit Logger Helper
 * Practical 14: Dynamic Menu and Audit Log Traceability
 */

require_once __DIR__ . '/db.php';

function record_audit_log($action, $details = '') {
    global $conn, $db_connected;

    $user_id = $_SESSION['user_id'] ?? null;
    $username = $_SESSION['username'] ?? 'Guest';
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $timestamp = date('Y-m-d H:i:s');

    // 1. Insert into MySQL audit_logs table if database is connected
    if ($db_connected && $conn) {
        $sql = "INSERT INTO audit_logs (user_id, username, action, details, ip_address) VALUES (?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "issss", $user_id, $username, $action, $details, $ip_address);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    // 2. Fallback text file log (data/audit_log.txt)
    $log_file = dirname(__DIR__) . '/data/audit_log.txt';
    $entry = sprintf("[%s] [%s] User: %s (ID: %s) | Action: %s | %s\n",
        $timestamp,
        $ip_address,
        $username,
        $user_id ? $user_id : 'N/A',
        $action,
        $details
    );
    @file_put_contents($log_file, $entry, FILE_APPEND);
}

