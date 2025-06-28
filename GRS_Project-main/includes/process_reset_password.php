<?php
require_once 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($token) || empty($password) || empty($confirm_password)) {
        $_SESSION['message'] = "All fields are required.";
        header("Location: ../reset-password.php?token=" . urlencode($token));
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['message'] = "Passwords do not match.";
        header("Location: ../reset-password.php?token=" . urlencode($token));
        exit();
    }

    // Validate token and get user
    $sql = "SELECT id FROM users WHERE reset_token = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        $_SESSION['message'] = "An error occurred. Please try again later.";
        header("Location: ../reset-password.php?token=" . urlencode($token));
        exit();
    }
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        $_SESSION['message'] = "Invalid or expired token.";
        header("Location: ../forgot-password.php");
        exit();
    }

    $user_id = $user['id'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Update password and clear reset token
    $sql = "UPDATE users SET password = ?, reset_token = NULL WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        $_SESSION['message'] = "An error occurred. Please try again later.";
        header("Location: ../reset-password.php?token=" . urlencode($token));
        exit();
    }
    $stmt->bind_param("si", $hashed_password, $user_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Password updated successfully. You can now login.";
    $_SESSION['message_type'] = "success";
    header("Location: ../login.php");
    exit();
} else {
    header("Location: ../forgot-password.php");
    exit();
}
?>
