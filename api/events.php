<?php
/**
 * StudentHub Portal - Events REST-Style JSON API
 * Practical 13: Asynchronous JSON API for CRUD Operations
 * Supports: GET (list/search), POST (create), PUT (update), DELETE (remove)
 */

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/logger.php';

// Helper to send standard JSON response
function send_json_response($status, $message, $data = null, $http_code = 200) {
    http_response_code($http_code);
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

if (!$db_connected || !$conn) {
    send_json_response('error', 'Database connection unavailable', null, 500);
}

$method = $_SERVER['REQUEST_METHOD'];

// Handle GET: List or Search Events
if ($method === 'GET') {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $category = isset($_GET['category']) ? trim($_GET['category']) : 'All';

    $query = "SELECT * FROM events WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($search)) {
        $query .= " AND (title LIKE ? OR description LIKE ? OR location LIKE ?)";
        $wild = "%{$search}%";
        $params[] = $wild;
        $params[] = $wild;
        $params[] = $wild;
        $types .= "sss";
    }

    if ($category !== 'All' && !empty($category)) {
        $query .= " AND category = ?";
        $params[] = $category;
        $types .= "s";
    }

    $query .= " ORDER BY id DESC";

    if (!empty($params)) {
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
    } else {
        $res = mysqli_query($conn, $query);
    }

    $events = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $events[] = $row;
    }

    send_json_response('success', 'Events retrieved successfully', $events, 200);
}

// Handle POST: Create New Event
if ($method === 'POST') {
    $raw_input = file_get_contents('php://input');
    $payload = json_decode($raw_input, true) ?: $_POST;

    $title = trim($payload['title'] ?? '');
    $category = trim($payload['category'] ?? 'Technical');
    $event_date = trim($payload['event_date'] ?? '');
    $location = trim($payload['location'] ?? '');
    $description = trim($payload['description'] ?? '');
    $status = trim($payload['status'] ?? 'Open');

    // Validation
    if (empty($title) || empty($event_date) || empty($location)) {
        send_json_response('error', 'Title, Event Date, and Location are required fields', null, 400);
    }

    $poster = 'assets/images/default-event.svg';
    $ins = mysqli_prepare($conn, "INSERT INTO events (title, category, event_date, location, description, poster_image, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($ins, "sssssss", $title, $category, $event_date, $location, $description, $poster, $status);

    if (mysqli_stmt_execute($ins)) {
        $new_id = mysqli_insert_id($conn);
        record_audit_log('API_EVENT_CREATED', "Event #$new_id created via JSON API");
        send_json_response('success', "Event #$new_id created successfully via AJAX", ['id' => $new_id], 201);
    } else {
        send_json_response('error', 'Database insert failed: ' . mysqli_error($conn), null, 500);
    }
}

// Handle PUT: Update Existing Event
if ($method === 'PUT') {
    $raw_input = file_get_contents('php://input');
    $payload = json_decode($raw_input, true);

    $id = intval($payload['id'] ?? 0);
    $title = trim($payload['title'] ?? '');
    $category = trim($payload['category'] ?? 'Technical');
    $event_date = trim($payload['event_date'] ?? '');
    $location = trim($payload['location'] ?? '');
    $description = trim($payload['description'] ?? '');
    $status = trim($payload['status'] ?? 'Open');

    if ($id <= 0 || empty($title) || empty($event_date) || empty($location)) {
        send_json_response('error', 'Valid Event ID and required fields are missing', null, 400);
    }

    $up = mysqli_prepare($conn, "UPDATE events SET title = ?, category = ?, event_date = ?, location = ?, description = ?, status = ? WHERE id = ?");
    mysqli_stmt_bind_param($up, "ssssssi", $title, $category, $event_date, $location, $description, $status, $id);

    if (mysqli_stmt_execute($up)) {
        record_audit_log('API_EVENT_UPDATED', "Event #$id updated via JSON API");
        send_json_response('success', "Event #$id updated successfully via AJAX", null, 200);
    } else {
        send_json_response('error', 'Update error: ' . mysqli_error($conn), null, 500);
    }
}

// Handle DELETE: Remove Event
if ($method === 'DELETE') {
    $raw_input = file_get_contents('php://input');
    $payload = json_decode($raw_input, true);
    $id = intval($payload['id'] ?? ($_GET['id'] ?? 0));

    if ($id <= 0) {
        send_json_response('error', 'Valid event ID required for deletion', null, 400);
    }

    $del = mysqli_prepare($conn, "DELETE FROM events WHERE id = ?");
    mysqli_stmt_bind_param($del, "i", $id);

    if (mysqli_stmt_execute($del)) {
        record_audit_log('API_EVENT_DELETED', "Event #$id deleted via JSON API");
        send_json_response('success', "Event #$id deleted successfully via AJAX", null, 200);
    } else {
        send_json_response('error', 'Delete error: ' . mysqli_error($conn), null, 500);
    }
}

// Any other method
send_json_response('error', 'HTTP Method Not Supported', null, 405);

