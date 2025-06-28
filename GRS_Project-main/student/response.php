<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

// Check if user is logged in and is coordinator
if (!is_logged_in() || $_SESSION['user_type'] !== 'coordinator') {
    redirect_with_message('../login.php', 'Please login as coordinator to view responses.', 'error');
}

// Check if grievance ID is provided
if (!isset($_GET['id'])) {
    redirect_with_message('dashboard.php', 'No grievance ID provided.', 'error');
}

$grievance_id = sanitize_input($_GET['id']);
$grievance = get_grievance_details($grievance_id);

if (!$grievance) {
    redirect_with_message('dashboard.php', 'Grievance not found.', 'error');
}

// Get responses for the grievance
$responses = get_grievance_updates($grievance_id);

// Get user details
$user_id = $_SESSION['user_id'];
$user = get_user_details($user_id);
if (!$user) {
    redirect_with_message('../login.php', 'User not found.', 'error');
}
$user_initials = strtoupper(substr($user['name'], 0, 1));

// Check for any messages
$message = '';
$message_type = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Grievance Responses - Grievance Redressal System</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>

        <div class="main-content">
            <?php if ($message): ?>
            <div class="alert alert-<?php echo htmlspecialchars($message_type); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <div class="detail-header">
                <a href="dashboard.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <div class="detail-title">
                    <h1>Responses for: <?php echo htmlspecialchars($grievance['title']); ?></h1>
                    <span class="status-badge status-<?php echo htmlspecialchars($grievance['status']); ?>">
                        <?php echo ucfirst(htmlspecialchars($grievance['status'])); ?>
                    </span>
                </div>
            </div>

            <div class="response-list">
                <?php if (count($responses) > 0): ?>
                    <?php foreach ($responses as $response): ?>
                        <div class="response-item">
                            <div class="response-header">
                                <div class="response-user-avatar">
                                    <?php
                                    $responder = get_user_details($response['user_id']);
                                    echo strtoupper(substr($responder['name'], 0, 1));
                                    ?>
                                </div>
                                <div class="response-user-info">
                                    <strong><?php echo htmlspecialchars($responder['name']); ?></strong>
                                    <span class="response-date"><?php echo date('Y-m-d H:i', strtotime($response['created_at'])); ?></span>
                                    <span class="response-status status-<?php echo htmlspecialchars($response['status']); ?>">
                                        <?php echo ucfirst(htmlspecialchars($response['status'])); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="response-message">
                                <?php echo nl2br(htmlspecialchars($response['message'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No responses yet for this grievance.</p>
                <?php endif; ?>
            </div>

            <div class="response-form">
                <h3>Add a Response</h3>
                <form action="../includes/process_add_response.php" method="POST">
                    <input type="hidden" name="grievance_id" value="<?php echo htmlspecialchars($grievance_id); ?>" />
                    <div class="form-group">
                        <label for="response_message">Response Message</label>
                        <textarea id="response_message" name="response_message" rows="5" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="status">Update Status</label>
                        <select id="status" name="status" required>
                            <option value="pending" <?php echo $grievance['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="in-progress" <?php echo $grievance['status'] == 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="resolved" <?php echo $grievance['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                            <option value="rejected" <?php echo $grievance['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-reply"></i> Submit Response
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
