<?php
$page_title = "My Profile";
$is_dashboard = true;
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

// Get user details
$user_id = $_SESSION['user_id'];
$user = get_user_details($user_id);

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Update profile information
        $name = sanitize_input($_POST['name']);
        $email = sanitize_input($_POST['email']);
        $department = sanitize_input($_POST['department']);
        $id_number = sanitize_input($_POST['id_number']);
        
        // Check if email already exists for another user
        $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "Email already exists. Please use a different email.";
        } else {
            // Update user information
            $sql = "UPDATE users SET name = ?, email = ?, department = ?, id_number = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $name, $email, $department, $id_number, $user_id);
            
            if ($stmt->execute()) {
                // Update session variables
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                
                // Log activity
                log_activity($user_id, 'update_profile', null, 'User updated profile information');
                
                $success_message = "Profile updated successfully.";
                
                // Refresh user data
                $user = get_user_details($user_id);
            } else {
                $error_message = "Failed to update profile. Please try again.";
            }
        }
        
        $stmt->close();
    } else if (isset($_POST['change_password'])) {
        // Change password
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate input
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_message = "Please fill in all password fields.";
        } else if ($new_password !== $confirm_password) {
            $error_message = "New passwords do not match.";
        } else if (strlen($new_password) < 6) {
            $error_message = "New password must be at least 6 characters long.";
        } else {
            // Verify current password
            if (password_verify($current_password, $user['password'])) {
                // Hash new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update password
                $sql = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $hashed_password, $user_id);
                
                if ($stmt->execute()) {
                    // Log activity
                    log_activity($user_id, 'change_password', null, 'User changed password');
                    
                    $success_message = "Password changed successfully.";
                } else {
                    $error_message = "Failed to change password. Please try again.";
                }
                
                $stmt->close();
            } else {
                $error_message = "Current password is incorrect.";
            }
        }
    }
}

require_once '../includes/header.php';
?>
<div class="dashboard">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="dashboard-header">
            <div class="dashboard-title">
                <h1>My Profile</h1>
                <p>View and update your profile information</p>
            </div>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="profile-grid">
            <!-- Profile Information -->
            <div class="form-card">
                <div class="form-card-header">
                    <h2>Profile Information</h2>
                    <p>Update your personal information</p>
                </div>

                <form action="profile.php" method="POST">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo $user['name']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo $user['email']; ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="id_number">ID Number</label>
                            <input type="text" id="id_number" name="id_number" class="form-control" value="<?php echo $user['id_number'] ?? ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="department">Department</label>
                            <input type="text" id="department" name="department" class="form-control" value="<?php echo $user['department'] ?? ''; ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="user_type">User Type</label>
                        <input type="text" id="user_type" class="form-control" value="<?php echo ucfirst($user['user_type']); ?>" readonly>
                        <p class="form-text">User type cannot be changed</p>
                    </div>

                    <div class="form-group">
                        <label for="created_at">Account Created</label>
                        <input type="text" id="created_at" class="form-control" value="<?php echo date('F j, Y', strtotime($user['created_at'])); ?>" readonly>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="form-card">
                <div class="form-card-header">
                    <h2>Change Password</h2>
                    <p>Update your password</p>
                </div>

                <form action="profile.php" method="POST">
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                        <p class="form-text">Password must be at least 6 characters long</p>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Account Activity -->
        <div class="form-card">
            <div class="form-card-header">
                <h2>Account Activity</h2>
                <p>Recent activity on your account</p>
            </div>
            
            <div class="timeline">
                <?php
                // Get recent account activities
                $sql = "SELECT * FROM logs WHERE user_id = ? AND (action = 'login' OR action = 'update_profile' OR action = 'change_password') ORDER BY timestamp DESC LIMIT 10";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $activities = [];
                
                while ($row = $result->fetch_assoc()) {
                    $activities[] = $row;
                }
                
                $stmt->close();
                
                if (count($activities) > 0):
                    foreach ($activities as $activity):
                ?>
                    <div class="timeline-item">
                        <div class="timeline-icon">
                            <i class="fas fa-<?php 
                            if ($activity['action'] == 'login') {
                                echo 'sign-in-alt';
                            } else if ($activity['action'] == 'update_profile') {
                                echo 'user-edit';
                            } else if ($activity['action'] == 'change_password') {
                                echo 'key';
                            } else {
                                echo 'history';
                            }
                            ?>"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-date"><?php echo date('Y-m-d H:i', strtotime($activity['timestamp'])); ?></div>
                            <div class="timeline-title">
                                <?php 
                                if ($activity['action'] == 'login') {
                                    echo 'Account Login';
                                } else if ($activity['action'] == 'update_profile') {
                                    echo 'Profile Updated';
                                } else if ($activity['action'] == 'change_password') {
                                    echo 'Password Changed';
                                } else {
                                    echo ucfirst(str_replace('_', ' ', $activity['action']));
                                }
                                ?>
                            </div>
                            <div class="timeline-text"><?php echo $activity['details']; ?></div>
                        </div>
                    </div>
                <?php 
                    endforeach;
                else:
                ?>
                    <div class="empty-state" style="padding: 2rem 0;">
                        <p>No recent account activity found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Additional CSS for profile page */
.profile-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1.5rem;
}

.alert-success {
    background-color: var(--success-color);
    color: white;
}

.alert-error {
    background-color: var(--danger-color);
    color: white;
}

@media (max-width: 992px) {
    .profile-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>
