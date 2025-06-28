<?php
require_once 'config.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect_with_message('../login.php', 'Please login to add a comment.', 'error');
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $grievance_id = sanitize_input($_POST['grievance_id']);
    $comment = sanitize_input($_POST['comment']);
    $user_id = $_SESSION['user_id'];
    
    // Validate input
    if (empty($grievance_id) || empty($comment)) {
        if (is_admin()) {
            redirect_with_message('../admin/grievance-detail.php?id=' . $grievance_id, 'Please enter a comment.', 'error');
        } else {
            redirect_with_message('../student/grievance-detail.php?id=' . $grievance_id, 'Please enter a comment.', 'error');
        }
    }
    
    // Insert comment into database
    $sql = "INSERT INTO comments (grievance_id, user_id, comment, created_at) 
            VALUES (?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sis", $grievance_id, $user_id, $comment);
    
    if ($stmt->execute()) {
        // Log activity
        log_activity($user_id, 'add_comment', $grievance_id, 'User added a comment');
        
        // Redirect back to grievance detail page
        if (is_admin()) {
            redirect_with_message('../admin/grievance-detail.php?id=' . $grievance_id, 'Comment added successfully.', 'success');
        } else {
            redirect_with_message('../student/grievance-detail.php?id=' . $grievance_id, 'Comment added successfully.', 'success');
        }
    } else {
        if (is_admin()) {
            redirect_with_message('../admin/grievance-detail.php?id=' . $grievance_id, 'Failed to add comment. Please try again.', 'error');
        } else {
            redirect_with_message('../student/grievance-detail.php?id=' . $grievance_id, 'Failed to add comment. Please try again.', 'error');
        }
    }
    
    $stmt->close();
} else {
    // If not a POST request, redirect to home page
    header("Location: ../index.php");
    exit();
}
?>
