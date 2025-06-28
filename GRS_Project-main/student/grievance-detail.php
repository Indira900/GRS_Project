NO<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect_with_message('../login.php', 'Please login to view grievance details.', 'error');
}

// Check if user is student or lecturer
if ($_SESSION['user_type'] != 'student' && $_SESSION['user_type'] != 'lecturer') {
    redirect_with_message('../admin/dashboard.php', 'You are logged in as admin.', 'info');
}

// Check if grievance ID is provided
if (!isset($_GET['id'])) {
    redirect_with_message('dashboard.php', 'No grievance ID provided.', 'error');
}

$grievance_id = sanitize_input($_GET['id']);
$grievance = get_grievance_details($grievance_id);

// Map category_id to category name
$categories = [
    1 => 'Health & Hygiene',
    2 => 'Safety & Security',
    3 => 'Harassment & Discrimination',
    4 => 'Education & Career',
    5 => 'Mental Health',
    6 => 'Facilities & Infrastructure',
    7 => 'Other'
];
$grievance['category'] = isset($categories[$grievance['category_id']]) ? $categories[$grievance['category_id']] : 'N/A';

// Check if grievance exists and belongs to the user
if (!$grievance || ($grievance['user_id'] != $_SESSION['user_id'] && $_SESSION['user_type'] != 'admin')) {
    redirect_with_message('dashboard.php', 'You do not have permission to view this grievance.', 'error');
}

// Get grievance updates and comments
$updates = get_grievance_updates($grievance_id);
$comments = get_grievance_comments($grievance_id);

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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grievance Details - Grievance Redressal System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-book-open"></i>
                <h2>Grievance System</h2>
            </div>

            <div class="user-info">
                <div class="user-avatar"><?php echo htmlspecialchars($user_initials); ?></div>
                <div class="user-details">
                    <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                    <p><?php echo ucfirst(htmlspecialchars($user['user_type'])); ?></p>
                </div>
            </div>

            <div class="sidebar-menu">
                <a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="my-grievances.php" class="active">
                    <i class="fas fa-file-alt"></i>
                    <span>My Grievances</span>
                </a>
                <a href="new-grievance.php">
                    <i class="fas fa-plus-circle"></i>
                    <span>New Grievance</span>
                </a>
                <a href="profile.php">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
                <a href="../includes/process_logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <div class="main-content">
            <?php if ($message): ?>
            <div class="alert alert-<?php echo htmlspecialchars($message_type); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>
            
            <div class="detail-header">
                <a href="dashboard.php" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>

                <div class="detail-title">
                    <h1><?php echo htmlspecialchars($grievance['title']); ?></h1>
                    <span class="status-badge status-<?php echo htmlspecialchars($grievance['status']); ?>">
                        <?php if ($grievance['status'] == 'pending'): ?>
                            <i class="fas fa-clock"></i>
                        <?php elseif ($grievance['status'] == 'in-progress'): ?>
                            <i class="fas fa-spinner"></i>
                        <?php elseif ($grievance['status'] == 'resolved'): ?>
                            <i class="fas fa-check-circle"></i>
                        <?php else: ?>
                            <i class="fas fa-times-circle"></i>
                        <?php endif; ?>
                        <?php 
                            if ($grievance['status'] == 'in-progress') {
                                echo 'In Progress';
                            } else {
                                echo ucfirst(htmlspecialchars($grievance['status']));
                            }
                        ?>
                    </span>
                </div>
                <div class="detail-meta">
                    ID: <?php echo htmlspecialchars($grievance['grievance_id']); ?> • <?php echo isset($grievance['category']) ? ucfirst(htmlspecialchars($grievance['category'])) : 'N/A'; ?> • Submitted on <?php echo date('Y-m-d', strtotime($grievance['created_at'])); ?>
                </div>
            </div>

            <div class="detail-grid">
                <div>
                    <div class="detail-tabs">
                        <div class="tab-list">
                            <div class="tab-item active" data-tab="details">Details</div>
                            <div class="tab-item" data-tab="updates">Updates</div>
                            <div class="tab-item" data-tab="comments">Comments</div>
                        </div>
                    </div>

                    <div class="detail-content">
                        <div class="tab-content active" id="details">
                            <div class="detail-section">
                                <h3>Description</h3>
                                <p><?php echo nl2br(htmlspecialchars($grievance['description'])); ?></p>
                            </div>

                            <div class="detail-section">
                                <div class="detail-grid-2">
                            <div class="detail-item">
                                <h4>Category</h4>
                                <p><?php echo isset($grievance['category']) ? ucfirst(htmlspecialchars($grievance['category'])) : 'N/A'; ?></p>
                            </div>
                                    <div class="detail-item">
                                        <h4>Priority</h4>
                                        <p><?php echo ucfirst(htmlspecialchars($grievance['priority'])); ?></p>
                                    </div>
                                    <div class="detail-item">
                                        <h4>Submission Date</h4>
                                        <p><?php echo date('Y-m-d', strtotime($grievance['created_at'])); ?></p>
                                    </div>
                                    <div class="detail-item">
                                        <h4>Current Status</h4>
                                        <p><?php echo $grievance['status'] == 'in-progress' ? 'In Progress' : ucfirst(htmlspecialchars($grievance['status'])); ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="detail-section">
                                <h3>Attachments</h3>
                                <?php
                                $sql = "SELECT * FROM file_uploads WHERE grievance_id = ?";
                                $stmt = $conn->prepare($sql);
                                if ($stmt) {
                                    $stmt->bind_param("s", $grievance_id);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    
if ($result->num_rows > 0) {
    echo '<ul class="attachment-list">';
    while ($attachment = $result->fetch_assoc()) {
        $file_path = htmlspecialchars($attachment['file_path']);
        $file_name = htmlspecialchars($attachment['file_name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
if (in_array($file_ext, $image_extensions)) {
    echo '<li><a href="' . $file_path . '" target="_blank"><img src="' . $file_path . '" alt="' . $file_name . '" style="max-width: 200px; max-height: 200px;"/></a></li>';
} else {
    echo '<li><a href="' . $file_path . '" target="_blank"><i class="fas fa-file"></i> ' . $file_name . '</a></li>';
}
    }
    echo '</ul>';
} else {
    echo '<div class="form-text no-attachments"><i class="fas fa-file-alt"></i> No attachments provided</div>';
}
                                    $stmt->close();
                                } else {
                                    echo '<div class="form-text"><i class="fas fa-exclamation-circle"></i> Error retrieving attachments</div>';
                                }
                                ?>
                                
                            </div>
                        </div>

                        <div class="tab-content" id="updates" style="display: none;">
                            <div class="timeline">
                                <?php if (count($updates) > 0): ?>
                                    <?php foreach ($updates as $update): ?>
                                        <div class="timeline-item">
                                            <div class="timeline-icon">
                                                <?php if ($update['status'] == 'pending'): ?>
                                                    <i class="fas fa-clock"></i>
                                                <?php elseif ($update['status'] == 'in-progress'): ?>
                                                    <i class="fas fa-spinner"></i>
                                                <?php elseif ($update['status'] == 'resolved'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="timeline-date"><?php echo date('Y-m-d', strtotime($update['created_at'])); ?></div>
                                                <div class="timeline-title">
                                                    <?php 
                                                        if ($update['status'] == 'pending') {
                                                            echo 'Grievance submitted and pending review';
                                                        } elseif ($update['status'] == 'in-progress') {
                                                            echo 'Grievance is being processed';
                                                        } elseif ($update['status'] == 'resolved') {
                                                            echo 'Grievance has been resolved';
                                                        } else {
                                                            echo 'Grievance has been rejected';
                                                        }
                                                    ?>
                                                </div>
                                                <div class="timeline-text"><?php echo htmlspecialchars($update['message']); ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <p>No updates available for this grievance yet.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="tab-content" id="comments" style="display: none;">
                            <div class="comment-list">
                                <?php if (count($comments) > 0): ?>
                                    <?php foreach ($comments as $comment): ?>
                                        <div class="comment">
                                            <div class="comment-avatar"><?php echo strtoupper(substr(htmlspecialchars($comment['user_name']), 0, 1)); ?></div>
                                            <div class="comment-content">
                                                <div class="comment-header">
                                                    <span class="comment-name"><?php echo htmlspecialchars($comment['user_name']); ?></span>
                                                    <span class="comment-badge"><?php echo ucfirst(htmlspecialchars($comment['user_type'])); ?></span>
                                                    <span class="comment-date"><?php echo date('Y-m-d', strtotime($comment['created_at'])); ?></span>
                                                </div>
                                                <div class="comment-text">
                                                    <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <p>No comments available for this grievance yet.</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="comment-form">
                                <form action="../includes/process_add_response.php" method="POST">
                                    <input type="hidden" name="grievance_id" value="<?php echo htmlspecialchars($grievance_id); ?>">
                                    <div class="form-group">
                                        <label for="response_message">Add a Response</label>
                                        <textarea id="response_message" name="response_message" class="form-control" rows="4" placeholder="Type your response here..." required></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="status">Update Status</label>
                                        <select id="status" name="status" class="form-control" required>
                                            <option value="in-progress" <?php echo $grievance['status'] == 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="resolved" <?php echo $grievance['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                            <option value="rejected" <?php echo $grievance['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                        </select>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-reply"></i>
                                            Add Response
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="sidebar-card">
                        <div class="sidebar-card-header">
                            <h3>Actions</h3>
                        </div>
                        <?php if ($grievance['status'] != 'resolved' && $grievance['status'] != 'rejected'): ?>
                            <form action="../includes/process_grievance_action.php" method="POST">
                                <input type="hidden" name="grievance_id" value="<?php echo htmlspecialchars($grievance_id); ?>">
                                <input type="hidden" name="action" value="resolve">
                                <button type="submit" class="action-btn">
                                    <i class="fas fa-check-circle"></i>
                                    Mark as Resolved
                                </button>
                            </form>
                        <?php endif; ?>
                        <form action="../includes/process_contact_admin.php" method="POST">
                            <input type="hidden" name="grievance_id" value="<?php echo htmlspecialchars($grievance_id); ?>">
                            <button type="submit" class="action-btn">
                                <i class="fas fa-comment"></i>
                                Contact Administrator
                            </button>
                        </form>
                        <?php if ($grievance['status'] != 'resolved' && $grievance['status'] != 'rejected'): ?>
                            <form action="../includes/process_grievance_action.php" method="POST">
                                <input type="hidden" name="grievance_id" value="<?php echo htmlspecialchars($grievance_id); ?>">
                                <input type="hidden" name="action" value="cancel">
                                <button type="submit" class="action-btn danger">
                                    <i class="fas fa-times-circle"></i>
                                    Cancel Grievance
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <div class="sidebar-card">
                        <div class="sidebar-card-header">
                            <h3>Assigned To</h3>
                        </div>
                        <?php if ($grievance['assigned_to']): ?>
                            <?php 
                            $assigned_user = get_user_details($grievance['assigned_to']);
                            $assigned_initials = strtoupper(substr($assigned_user['name'], 0, 1));
                            ?>
                            <div class="assigned-user">
                                <div class="comment-avatar"><?php echo htmlspecialchars($assigned_initials); ?></div>
                                <div class="assigned-user-info">
                                    <h4><?php echo htmlspecialchars($assigned_user['name']); ?></h4>
                                    <p>Assigned on <?php 
                                        foreach ($updates as $update) {
                                            if (strpos($update['message'], 'assigned') !== false) {
                                                echo date('M j, Y', strtotime($update['created_at']));
                                                break;
                                            }
                                        }
                                    ?></p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="assigned-user">
                                <div class="comment-avatar">NA</div>
                                <div class="assigned-user-info">
                                    <h4>Not Assigned Yet</h4>
                                    <p>Waiting for assignment</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>