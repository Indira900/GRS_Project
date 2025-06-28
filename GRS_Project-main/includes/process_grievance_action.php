<?php
require_once 'config.php';
require_once 'auth_check.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect_with_message('../login.php', 'Please login to perform this action.', 'error');
}

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message('../student/dashboard.php', 'Invalid request method.', 'error');
}

// Validate inputs
$grievance_id = $_POST['grievance_id'] ?? '';
$action = $_POST['action'] ?? '';

if (empty($grievance_id) || empty($action)) {
    redirect_with_message('../student/dashboard.php', 'Missing required parameters.', 'error');
}

// Sanitize inputs
$grievance_id = sanitize_input($grievance_id);
$action = sanitize_input($action);

$user_id = $_SESSION['user_id'];

// Check if grievance exists and belongs to user or user is admin
$grievance = get_grievance_details($grievance_id);
if (!$grievance) {
    redirect_with_message('../student/dashboard.php', 'Grievance not found.', 'error');
}
if ($grievance['user_id'] != $user_id && $_SESSION['user_type'] != 'admin') {
    redirect_with_message('../student/dashboard.php', 'You do not have permission to perform this action.', 'error');
}

$status_map = [
    'resolve' => 'resolved',
    'cancel' => 'resolved'
];

if (!array_key_exists($action, $status_map)) {
    redirect_with_message('../student/dashboard.php', 'Invalid action.', 'error');
}

$new_status = $status_map[$action];

$sql = "UPDATE grievances SET status = ? WHERE grievance_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    redirect_with_message('../student/dashboard.php', 'Database error: ' . $conn->error, 'error');
}
$stmt->bind_param('ss', $new_status, $grievance_id);
if (!$stmt->execute()) {
    $stmt->close();
    redirect_with_message('../student/dashboard.php', 'Failed to update grievance status.', 'error');
}
$stmt->close();

// Add update entry to responses table
$message_map = [
    'resolve' => 'Grievance marked as resolved.',
    'cancel' => 'Grievance has been cancelled.'
];
$message = $message_map[$action];

$sql = "INSERT INTO responses (grievance_id, user_id, status, message, created_at) VALUES (?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param('siss', $grievance_id, $user_id, $new_status, $message);
    $stmt->execute();
    $stmt->close();
}

// Log activity
log_activity($user_id, 'grievance_' . $action, $grievance_id, $message);

// Redirect back to grievance detail page
redirect_with_message("../student/grievance-detail.php?id=$grievance_id", 'Action performed successfully.', 'success');
?>
