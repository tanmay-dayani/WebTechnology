<?php
/**
 * StudentHub Portal - Student List CSV Exporter
 * Practical 11 Intermediate Extension: Export Student List to CSV File
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/logger.php';

require_admin();

$user = current_user();

if (!$db_connected || !$conn) {
    die("Database connection failed. Please ensure MySQL is running.");
}

// Set CSV download headers
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="studenthub_students_' . date('Y-m-d_His') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// Output column headers
fputcsv($output, [
    'Student ID Code',
    'Full Name',
    'Email Address',
    'Phone Number',
    'Course',
    'Academic Year',
    'Gender',
    'Status',
    'Created At'
]);

// Fetch and stream student records
$sql = "SELECT student_id_code, full_name, email, phone, course, year, gender, status, created_at FROM students ORDER BY id ASC";
$res = mysqli_query($conn, $sql);

if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        fputcsv($output, [
            $row['student_id_code'],
            $row['full_name'],
            $row['email'],
            $row['phone'],
            $row['course'],
            $row['year'],
            $row['gender'],
            $row['status'],
            $row['created_at']
        ]);
    }
}

fclose($output);

// Record in audit log
record_audit_log('STUDENTS_EXPORTED_CSV', "Admin exported complete student directory to CSV", $user['id'], $user['username']);
exit;

