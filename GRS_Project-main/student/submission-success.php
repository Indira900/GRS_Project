<?php
require_once '../includes/auth_check.php';

if (!is_logged_in()) {
    header('Location: ../login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Submission Successful</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <style>
        /* Center container vertically and horizontally */
        body, html {
            height: 100%;
            margin: 0;
        }
        .container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container">
        <h1>Grievance Submitted Successfully</h1>
        <p>Your grievance has been submitted and is pending review.</p>
        <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
