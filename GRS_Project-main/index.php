<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grievance Redressal System</title>
    <link rel="stylesheet" href="includes/css/style.css">
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
                    <li><a href="index.php" class="active">Home</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="login.php" class="btn-login">Login</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <section class="hero">
                <div class="hero-content">
                    <h1>Streamlined Grievance Management</h1>
                    <p>A comprehensive platform for students and lecturers to submit, track, and resolve grievances efficiently.</p>
                    <div class="hero-buttons">
                        <a href="login.php" class="btn btn-primary">Get Started</a>
                        <a href="#features" class="btn btn-secondary">Learn More</a>
                    </div>
                </div>
            </section>

            <section id="features" class="features">
                <h2>Key Features</h2>
                <div class="feature-cards">
                    <div class="card">
                        <div class="card-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3>Easy Submission</h3>
                        <p>Submit grievances with a simple, intuitive form with all the necessary details.</p>
                    </div>

                    <div class="card">
                        <div class="card-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Real-time Tracking</h3>
                        <p>Keep track of your submitted grievances with real-time status updates throughout the resolution process.</p>
                    </div>

                    <div class="card">
                        <div class="card-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3>Efficient Resolution</h3>
                        <p>Our system ensures that grievances are addressed promptly by the appropriate authorities with full transparency.</p>
                    </div>
                </div>
            </section>

            <section class="how-it-works">
                <h2>How It Works</h2>
                <div class="steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <h3>Register</h3>
                        <p>Create an account as a student or lecturer</p>
                    </div>

                    <div class="step">
                        <div class="step-number">2</div>
                        <h3>Submit</h3>
                        <p>File your grievance with relevant details</p>
                    </div>

                    <div class="step">
                        <div class="step-number">3</div>
                        <h3>Track</h3>
                        <p>Monitor the status of your grievance</p>
                    </div>

                    <div class="step">
                        <div class="step-number">4</div>
                        <h3>Resolve</h3>
                        <p>Get timely resolution and feedback</p>
                    </div>
                </div>
            </section>
        </main>

        <footer>
            <div class="footer-content">
                <div class="footer-logo">
                    <i class="fas fa-book-open"></i>
                    <h2>Grievance Redressal System</h2>
                    <p>&copy; <?php echo date('Y'); ?> All rights reserved</p>
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
