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

if (empty($grievance_id)) {
    redirect_with_message('../student/dashboard.php', 'Missing grievance ID.', 'error');
}

// Sanitize input
$grievance_id = sanitize_input($grievance_id);
$user_id = $_SESSION['user_id'];

// Check if grievance exists and belongs to user or user is admin
$grievance = get_grievance_details($grievance_id);
if (!$grievance) {
    redirect_with_message('../student/dashboard.php', 'Grievance not found.', 'error');
}
if ($grievance['user_id'] != $user_id && $_SESSION['user_type'] != 'admin') {
    redirect_with_message('../student/dashboard.php', 'You do not have permission to perform this action.', 'error');
}

// Notify admin (this can be an email or a log entry; here we log the activity)
log_activity($user_id, 'contact_admin', $grievance_id, 'User contacted administrator regarding grievance.');

// Redirect back with success message
redirect_with_message("../student/grievance-detail.php?id=$grievance_id", 'Administrator has been contacted successfully.', 'success');
?>
