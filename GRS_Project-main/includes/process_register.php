<?php
require_once 'config.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = sanitize_input($_POST['user_type']);
    
    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($user_type)) {
        redirect_with_message('../register.php', 'Please fill in all required fields.', 'error');
    }
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        redirect_with_message('../register.php', 'Passwords do not match.', 'error');
    }
    
    // Check if email already exists
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        redirect_with_message('../register.php', 'Email already exists. Please use a different email.', 'error');
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user into database
    $sql = "INSERT INTO users (name, email, password, user_type, created_at) 
            VALUES (?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $email, $hashed_password, $user_type);
    
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        
        // Log activity
        log_activity($user_id, 'register', null, 'User registered');
        
        // Set session variables
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_type'] = $user_type;
        
        // Redirect based on user type
        if ($user_type == 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../student/dashboard.php");
        }
        exit();
    } else {
        redirect_with_message('../register.php', 'Registration failed. Please try again.', 'error');
    }
    
    $stmt->close();
} else {
    // If not a POST request, redirect to registration page
    header("Location: ../register.php");
    exit();
}
?>
