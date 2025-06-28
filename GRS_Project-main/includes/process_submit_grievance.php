<?php
require_once 'config.php';
require_once 'auth_check.php';

// Remove generate_grievance_id function here to avoid redeclaration error
// It is assumed to be defined in config.php or another included file

// Check if user is logged in
if (!is_logged_in()) {
    redirect_with_message('../login.php', 'Please login to submit a grievance.', 'error');
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $title = sanitize_input($_POST['title']);
    $category_id = intval($_POST['category']);
    $priority = sanitize_input($_POST['priority']);
    $description = sanitize_input($_POST['description']);
    $anonymous = isset($_POST['anonymous']) ? 1 : 0;
    $anonymous_email = $anonymous ? sanitize_input($_POST['anonymous_email']) : null;
    $user_id = $_SESSION['user_id'];

    // Validate input
    if (empty($title) || empty($category_id) || empty($description)) {
        redirect_with_message('../student/new-grievance.php', 'Please fill in all required fields.', 'error');
    }

    // Simple automatic rejection validation example
    $banned_words = ['spam', 'test', 'invalid'];
    $is_invalid = false;
    foreach ($banned_words as $word) {
        if (stripos($title, $word) !== false || stripos($description, $word) !== false) {
            $is_invalid = true;
            break;
        }
    }

    // Generate unique grievance_id string
    $grievance_unique_id = generate_grievance_id();

    // Determine status based on validation
    $status = $is_invalid ? 'rejected' : 'pending';

    // Insert grievance into database
    $sql = "INSERT INTO grievances (grievance_id, user_id, title, category_id, description, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        redirect_with_message('../student/new-grievance.php', 'Database error: ' . $conn->error, 'error');
        exit();
    }
    $stmt->bind_param("sssiss", $grievance_unique_id, $user_id, $title, $category_id, $description, $status);

    if (!$stmt->execute()) {
        redirect_with_message('../student/new-grievance.php', 'Failed to submit grievance: ' . $stmt->error, 'error');
        exit();
    }
    $grievance_id = $grievance_unique_id;

    if (empty($grievance_id)) {
        redirect_with_message('../student/new-grievance.php', 'Failed to get grievance ID after submission.', 'error');
        exit();
    }

    // Handle file uploads if any
    if (!empty($_FILES['attachments']['name'][0])) {
        $upload_dir = "../uploads/";

        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        foreach ($_FILES['attachments']['name'] as $key => $name) {
            if ($_FILES['attachments']['error'][$key] == 0) {
                $tmp_name = $_FILES['attachments']['tmp_name'][$key];
                $file_name = time() . '_' . basename($name);
                $file_path = $upload_dir . $file_name;

                if (move_uploaded_file($tmp_name, $file_path)) {
                    // Save file info to database
                    $sql = "INSERT INTO file_uploads (grievance_id, file_name, file_path, uploaded_at) 
                            VALUES (?, ?, ?, NOW())";

                    $stmt_file = $conn->prepare($sql);
                    if ($stmt_file === false) {
                        error_log("Prepare failed: " . $conn->error);
                    } else {
                        $stmt_file->bind_param("sss", $grievance_id, $file_name, $file_path);
                        $stmt_file->execute();
                        $stmt_file->close();
                    }
                }
            }
        }
    }

    // Add initial status update
    $response_message = $is_invalid ? 'Grievance automatically rejected due to invalid content.' : 'Grievance submitted and pending review.';
    $sql = "INSERT INTO responses (grievance_id, user_id, status, message, created_at) 
            VALUES (?, ?, ?, ?, NOW())";

    $stmt_update = $conn->prepare($sql);
    if ($stmt_update === false) {
        error_log("Prepare failed for responses insert: " . $conn->error);
    } else {
        $grievance_id_trimmed = trim($grievance_id);
        $stmt_update->bind_param("siss", $grievance_id_trimmed, $user_id, $status, $response_message);
        $stmt_update->execute();
        $stmt_update->close();
    }

    // Log activity
    log_activity($user_id, 'submit_grievance', $grievance_id, 'User submitted a new grievance');

    // Redirect to appropriate page based on status
    $_SESSION['grievance_id'] = $grievance_id;
    if ($is_invalid) {
        $_SESSION['message'] = 'Your grievance was automatically rejected due to invalid content.';
        $_SESSION['message_type'] = 'error';
        header("Location: /GRS_Project-main/student/new-grievance.php");
    } else {
        header("Location: /GRS_Project-main/student/submission-success.php");
    }
    exit();

} else {
    // If not a POST request, redirect to submission page
    header("Location: ../student/new-grievance.php");
    exit();
}
?>
