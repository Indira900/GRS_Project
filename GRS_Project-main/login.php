<?php
session_start();

// If already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] == 'admin') {
        header("Location: admin/dashboard.php");
        exit();
    } else {
        header("Location: student/dashboard.php");
        exit();
    }
}

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
    <title>Login - Grievance Redressal System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <i class="fas fa-book-open"></i>
                <h1>Grievance Redressal System</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="login.php" class="btn-login active">Login</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <?php if ($message): ?>
            <div class="alert alert-<?php echo htmlspecialchars($message_type); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>
            
            <div class="auth-container">
                <div class="auth-header">
                    <h2>Login to Your Account</h2>
                    <p>Enter your credentials to access your account</p>
                </div>

                <form action="includes/process_login.php" method="POST">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>

                    <div class="form-group">
                        <label>I am a:</label>
                        <div class="radio-group">
                            <div class="radio-option">
                                <input type="radio" id="student" name="user_type" value="student" checked>
                                <label for="student">Student</label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" id="lecturer" name="user_type" value="lecturer">
                                <label for="lecturer">Lecturer</label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" id="admin" name="user_type" value="admin">
                                <label for="admin">Admin</label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-block">Login</button>
                </form>

                <div class="auth-footer">
                    <p>Don't have an account? <a href="register.php">Register</a></p>
                    <p><a href="forgot-password.php">Forgot your password?</a></p>
                </div>
            </div>
        </main>

        <footer>
            <div class="footer-content">
                <div class="footer-logo">
                    <i class="fas fa-book-open"></i>
                    <h2>Grievance Redressal System</h2>
                    <p>© <?php echo date('Y'); ?> All rights reserved</p>
                </div>
                <div class="footer-links">
                    <a href="about.php">About</a>
                    <a href="contact.php">Contact</a>
                    <a href="privacy.php">Privacy Policy</a>
                </div>
            </div>
        </footer>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>