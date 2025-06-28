<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Forgot Password - Grievance Redressal System</title>
    <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container" style="display: flex; justify-content: center; align-items: center; height: 60vh; text-align: center;">
        <div>
            <h1>Forgot Password</h1>
            <form action="includes/process_forgot_password.php" method="POST" style="margin-top: 20px;">
                <label for="email">Enter your registered email address:</label><br />
                <input type="email" id="email" name="email" required style="padding: 8px; width: 250px; margin-top: 8px;" /><br />
                <button type="submit" style="margin-top: 15px; padding: 10px 20px;">Reset Password</button>
            </form>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
