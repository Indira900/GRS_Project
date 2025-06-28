<?php
require_once 'config.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $user_type = sanitize_input($_POST['user_type']);
    
    // Validate input
    if (empty($email) || empty($password)) {
        redirect_with_message('../login.php', 'Please fill in all required fields.', 'error');
    }
    
    // Check if user exists
    $sql = "SELECT * FROM users WHERE email = ? AND user_type = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        redirect_with_message('../login.php', 'Database error: Unable to prepare query.', 'error');
    }
    $stmt->bind_param("ss", $email, $user_type);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];
            
            // Log activity
            log_activity($user['id'], 'login', null, 'User logged in');
            
            // Redirect based on user type
            if ($user_type == 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../student/dashboard.php");
            }
            exit();
        } else {
            redirect_with_message('../login.php', 'Invalid password. Please try again.', 'error');
        }
    } else {
        redirect_with_message('../login.php', 'User not found. Please check your credentials.', 'error');
    }
    
    $stmt->close();
} else {
    // If not a POST request, redirect to login page
    header("Location: ../login.php");
    exit();
}
?>