<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset Password - Grievance Redressal System</title>
    <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container" style="display: flex; justify-content: center; align-items: center; height: 60vh; text-align: center;">
        <div>
            <h1>Reset Password</h1>
            <?php
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (isset($_SESSION['message'])) {
                // Show only the message text without extra characters
                $msg = htmlspecialchars($_SESSION['message']);
                // Remove any trailing or leading unwanted characters like '">'
                $msg = trim($msg, "\">");
                echo '<p style="color: red;">' . $msg . '</p>';
                unset($_SESSION['message']);
            }
            $token = $_GET['token'] ?? '';
            if (!$token) {
                echo '<p>Invalid or missing token.</p>';
            } else {
            ?>
            <form action="includes/process_reset_password.php" method="POST" style="margin-top: 20px;">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>" />
                <label for="password">New Password:</label><br />
                <input type="password" id="password" name="password" required style="padding: 8px; width: 250px; margin-top: 8px;" /><br />
                <label for="confirm_password" style="margin-top: 10px;">Confirm New Password:</label><br />
                <input type="password" id="confirm_password" name="confirm_password" required style="padding: 8px; width: 250px; margin-top: 8px;" /><br />
                <button type="submit" style="margin-top: 15px; padding: 10px 20px;">Update Password</button>
            </form>
            <?php } ?>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
