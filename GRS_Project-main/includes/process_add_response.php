<?php
require_once 'config.php';
require_once 'auth_check.php';

// Check if user is logged in and is coordinator
if (!is_logged_in() || $_SESSION['user_type'] !== 'coordinator') {
    redirect_with_message('../login.php', 'Please login as coordinator to perform this action.', 'error');
}

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message('../student/dashboard.php', 'Invalid request method.', 'error');
}

// Validate inputs
$grievance_id = $_POST['grievance_id'] ?? '';
$response_message = $_POST['response_message'] ?? '';
$new_status = $_POST['status'] ?? '';

if (empty($grievance_id) || empty($response_message) || empty($new_status)) {
    redirect_with_message('../student/dashboard.php', 'Missing required fields.', 'error');
}

// Sanitize inputs
$grievance_id = sanitize_input($grievance_id);
$response_message = sanitize_input($response_message);
$new_status = sanitize_input($new_status);

$user_id = $_SESSION['user_id'];

// Check if grievance exists
$grievance = get_grievance_details($grievance_id);
if (!$grievance) {
    redirect_with_message('../student/dashboard.php', 'Grievance not found.', 'error');
}

// Insert response into responses table
$sql = "INSERT INTO responses (grievance_id, user_id, status, message, created_at) VALUES (?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    redirect_with_message("../student/grievance-detail.php?id=$grievance_id", 'Database error: ' . $conn->error, 'error');
}
$stmt->bind_param('siss', $grievance_id, $user_id, $new_status, $response_message);
if (!$stmt->execute()) {
    $stmt->close();
    redirect_with_message("../student/grievance-detail.php?id=$grievance_id", 'Failed to add response.', 'error');
}
$stmt->close();

// Update grievance status
$sql = "UPDATE grievances SET status = ? WHERE grievance_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    redirect_with_message("../student/grievance-detail.php?id=$grievance_id", 'Database error: ' . $conn->error, 'error');
}
$stmt->bind_param('ss', $new_status, $grievance_id);
if (!$stmt->execute()) {
    $stmt->close();
    redirect_with_message("../student/grievance-detail.php?id=$grievance_id", 'Failed to update grievance status.', 'error');
}
$stmt->close();

// Log activity
log_activity($user_id, 'add_response', $grievance_id, 'Coordinator responded to grievance.');

// Redirect back to grievance detail page
redirect_with_message("../student/grievance-detail.php?id=$grievance_id", 'Response added successfully.', 'success');
?>
