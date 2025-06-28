<?php
require_once 'config.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect_with_message('../login.php', 'You do not have permission to perform this action.', 'error');
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $grievance_id = sanitize_input($_POST['grievance_id']);
    $status = sanitize_input($_POST['status']);
    $message = sanitize_input($_POST['update_message']);
    $user_id = $_SESSION['user_id'];
    
    // Validate input
    if (empty($grievance_id) || empty($status) || empty($message)) {
        redirect_with_message('../admin/grievance-detail.php?id=' . $grievance_id, 'Please fill in all required fields.', 'error');
    }
    
    // Insert update into database
    $sql = "INSERT INTO responses (grievance_id, user_id, status, message, created_at) 
            VALUES (?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siss", $grievance_id, $user_id, $status, $message);
    
    if ($stmt->execute()) {
        // Update grievance status
        $sql = "UPDATE grievance SET status = ? WHERE grievance_id = ?";
        $stmt_update = $conn->prepare($sql);
        $stmt_update->bind_param("ss", $status, $grievance_id);
        $stmt_update->execute();
        $stmt_update->close();
        
        // Log activity
        log_activity($user_id, 'add_update', $grievance_id, 'Admin updated grievance status to ' . $status);
        
        // Redirect back to grievance detail page
        redirect_with_message('../admin/grievance-detail.php?id=' . $grievance_id, 'Update added successfully.', 'success');
    } else {
        redirect_with_message('../admin/grievance-detail.php?id=' . $grievance_id, 'Failed to add update. Please try again.', 'error');
    }
    
    $stmt->close();
} else {
    // If not a POST request, redirect to home page
    header("Location: ../index.php");
    exit();
}
?>
