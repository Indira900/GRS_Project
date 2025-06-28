<?php
require_once 'config.php';

// Check if form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $_SESSION['message'] = "Please enter a valid email address.";
        $_SESSION['message_type'] = "error";
        header("Location: ../forgot-password.php");
        exit();
    }

    // Check if email exists in users table
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        $_SESSION['message'] = "An error occurred. Please try again later.";
        $_SESSION['message_type'] = "error";
        header("Location: ../forgot-password.php");
        exit();
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        $_SESSION['message'] = "Email address not found.";
        $_SESSION['message_type'] = "error";
        header("Location: ../forgot-password.php");
        exit();
    }

    // Generate a temporary token for password reset (in real app, send email)
    $token = bin2hex(random_bytes(16));
    $user_id = $user['id'];

    // Store token in database with expiry (for simplicity, skipping expiry here)
    $sql = "UPDATE users SET reset_token = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        $_SESSION['message'] = "An error occurred. Please try again later.";
        $_SESSION['message_type'] = "error";
        header("Location: ../forgot-password.php");
        exit();
    }
    $stmt->bind_param("si", $token, $user_id);
    $stmt->execute();
    $stmt->close();

    // Redirect to reset password page with token
    header("Location: ../reset-password.php?token=" . $token);
    exit();
} else {
    header("Location: ../forgot-password.php");
    exit();
}
?>
