<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
$db_host = "localhost";
$db_user = "root";
$db_pass = "1234"; // Use a secure password if possible
$db_name = "Grievance_system";

// Create database connection with error handling
$conn = new mysqli($db_host, $db_user, (string)$db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character encoding to avoid character-related issues
$conn->set_charset("utf8mb4");

// Function to sanitize user input
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Function to log activity
function log_activity($user_id, $action, $grievance_id = null, $details = '') {
    global $conn;
    
    $sql = "INSERT INTO logs (user_id, action, grievance_id, details, timestamp) 
            VALUES (?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("isss", $user_id, $action, $grievance_id, $details);
        $stmt->execute();
        $stmt->close();
    } else {
        error_log("Database error: " . $conn->error);
    }
}

// Function to redirect with message
function redirect_with_message($url, $message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: $url");
    exit();
}

/**
 * Get user details by user ID
 *
 * @param int $user_id
 * @return array|null
 */
function get_user_details($user_id) {
    global $conn;
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }
    return null;
}

function get_user_grievances($user_id) {
    global $conn;
    $sql = "SELECT * FROM grievances WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $grievances = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $grievances;
}

/**
 * Generate a unique grievance ID
 *
 * @return string
 */
function generate_grievance_id() {
    return 'GRV-' . strtoupper(bin2hex(random_bytes(4))) . '-' . time();
}

/**
 * Get grievance details by grievance ID
 *
 * @param string $grievance_id
 * @return array|null
 */
function get_grievance_details($grievance_id) {
    global $conn;
    $sql = "SELECT * FROM grievances WHERE grievance_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $grievance_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $grievance = $result->fetch_assoc();
        $stmt->close();
        return $grievance;
    }
    return null;
}

/**
 * Get grievance updates/responses by grievance ID
 *
 * @param string $grievance_id
 * @return array
 */
function get_grievance_updates($grievance_id) {
    global $conn;
    $sql = "SELECT * FROM responses WHERE grievance_id = ? ORDER BY created_at ASC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $grievance_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $updates = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $updates;
    }
    return [];
}

/**
 * Get grievance comments by grievance ID
 *
 * @param string $grievance_id
 * @return array
 */
function get_grievance_comments($grievance_id) {
    global $conn;
    $sql = "SELECT * FROM comments WHERE grievance_id = ? ORDER BY created_at ASC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $grievance_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $comments = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $comments;
    }
    return [];
}
?>
