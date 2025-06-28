<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect_with_message('../login.php', 'Please login to submit a grievance.', 'error');
}

// Check if user is student or lecturer
if ($_SESSION['user_type'] != 'student' && $_SESSION['user_type'] != 'lecturer') {
    redirect_with_message('../admin/dashboard.php', 'You are logged in as admin.', 'info');
}

// Get user details
$user_id = $_SESSION['user_id'];
$user = get_user_details($user_id);
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
    <title>Submit Grievance - Grievance Redressal System</title>
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
                <div class="user-avatar"><?php echo $user_initials; ?></div>
                <div class="user-details">
                    <h3><?php echo $user['name']; ?></h3>
                    <p><?php echo ucfirst($user['user_type']); ?></p>
                </div>
            </div>

            <div class="sidebar-menu">
                <a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="my-grievances.php">
                    <i class="fas fa-file-alt"></i>
                    <span>My Grievances</span>
                </a>
                <a href="new-grievance.php" class="active">
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
            <?php if($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <div class="dashboard-header">
                <div class="dashboard-title">
                    <h1>Submit a New Grievance</h1>
                    <p>Fill out the form below to submit your grievance</p>
                </div>
            </div>

            <div class="form-card">
                <div class="form-card-header">
                    <h2>Grievance Details</h2>
                    <p>Provide detailed information about your grievance to help us address it effectively.</p>
                </div>

                <form action="../includes/process_submit_grievance.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" class="form-control" placeholder="Brief title of your grievance" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category" class="form-control" required>
                                <option value="">Select category</option>
                                <option value="1">Health & Hygiene</option>
                                <option value="2">Safety & Security</option>
                                <option value="3">Harassment & Discrimination</option>
                                <option value="4">Education & Career</option>
                                <option value="5">Mental Health</option>
                                <option value="6">Facilities & Infrastructure</option>
                                <option value="7">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Priority</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" id="low" name="priority" value="low">
                                    <label for="low">Low</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" id="medium" name="priority" value="medium" checked>
                                    <label for="medium">Medium</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" id="high" name="priority" value="high">
                                    <label for="high">High</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="6" placeholder="Provide a detailed description of your grievance" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="attachments">Attachments (Optional)</label>
                        <input type="file" id="attachments" name="attachments[]" class="form-control" multiple>
                        <p class="form-text">You can attach relevant documents, images, or evidence (Max 5MB per file)</p>
                    </div>

                    <div class="form-group">
                        <div class="radio-option">
                            <input type="checkbox" id="anonymous" name="anonymous" value="1" onchange="toggleEmailInput(this)">
                            <label for="anonymous">Submit anonymously</label>
                        </div>
                    </div>

                    <div class="form-group" id="anonymous-email-group" style="display:none;">
                        <label for="anonymous_email">Your Email (for verification)</label>
                        <input type="email" id="anonymous_email" name="anonymous_email" class="form-control" placeholder="Enter your email for verification">
                    </div>

                    <script>
                        function toggleEmailInput(checkbox) {
                            const emailGroup = document.getElementById('anonymous-email-group');
                            if (checkbox.checked) {
                                emailGroup.style.display = 'block';
                            } else {
                                emailGroup.style.display = 'none';
                            }
                        }
                    </script>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary-outline" onclick="window.history.back()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Grievance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
