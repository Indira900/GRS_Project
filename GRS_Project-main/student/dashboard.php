<?php
require_once '../includes/config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    // User not found, logout
    header("Location: ../includes/process_logout.php");
    exit();
}

// User initials for avatar
$user_initials = '';
if (!empty($user['name'])) {
    $names = explode(' ', $user['name']);
    foreach ($names as $n) {
        $user_initials .= strtoupper($n[0]);
    }
}

// Fetch grievances list
$sql = "SELECT * FROM grievances WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$grievances = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$categories = [
    1 => 'Health & Hygiene',
    2 => 'Safety & Security',
    3 => 'Harassment & Discrimination',
    4 => 'Education & Career',
    5 => 'Mental Health',
    6 => 'Facilities & Infrastructure',
    7 => 'Other'
];

// Filter grievances to limit repeated titles to max 2 occurrences
$filtered_grievances = [];
$title_counts = [];

foreach ($grievances as $grievance) {
    $title = $grievance['title'];
    if (!isset($title_counts[$title])) {
        $title_counts[$title] = 0;
    }
    if ($title_counts[$title] < 2) {
        $filtered_grievances[] = $grievance;
        $title_counts[$title]++;
    }
}

$grievances = $filtered_grievances;

// Calculate counts based on filtered grievances
$total_grievances = count($grievances);
$pending_count = 0;
$resolved_count = 0;

foreach ($grievances as $grievance) {
    switch ($grievance['status']) {
        case 'pending':
            $pending_count++;
            break;
        case 'resolved':
            $resolved_count++;
            break;
    }
}

// Fetch message and message_type from session if set
$message = $_SESSION['message'] ?? '';
$message_type = $_SESSION['message_type'] ?? '';
unset($_SESSION['message'], $_SESSION['message_type']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard - Grievance Redressal System</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
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
                <a href="dashboard.php" class="active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="my-grievances.php">
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

            <div class="dashboard-header">
                <div class="dashboard-title">
                    <h1>Dashboard</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($user['name']); ?></p>
                </div>
                <div class="dashboard-actions">
                    <a href="new-grievance.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i>
                        New Grievance
                    </a>
                </div>
            </div>

            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-card-title">Total Grievances</div>
                        <div class="stat-card-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo $total_grievances; ?></div>
                    <div class="stat-card-info">All grievances submitted</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-card-title">Pending</div>
                        <div class="stat-card-icon">
                            <i class="fas fa-clock" style="color: #ffc107;"></i>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo $pending_count; ?></div>
                    <div class="stat-card-info">Awaiting review</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-card-title">Resolved</div>
                        <div class="stat-card-icon">
                            <i class="fas fa-check-circle" style="color: #28a745;"></i>
                        </div>
                    </div>
                    <div class="stat-card-value"><?php echo $resolved_count; ?></div>
                    <div class="stat-card-info">Successfully resolved</div>
                </div>
            </div>

            <div class="tabs">
                <div class="tab-list">
                    <div class="tab-item active" data-tab="all">All</div>
                    <div class="tab-item" data-tab="pending">Pending</div>
                    <div class="tab-item" data-tab="resolved">Resolved</div>
                    <div class="tab-item" data-tab="rejected">Rejected</div>
                </div>

                <div class="grievance-list">
                    <div class="grievance-list-header">
                        <h2>My Grievances</h2>
                        <p>View and manage all your submitted grievances</p>
                    </div>

                    <?php if (count($grievances) > 0): ?>
                        <?php foreach ($grievances as $grievance): ?>
                            <div class="grievance-item">
                                <div class="grievance-info">
                                    <div class="grievance-title">
                                        <?php echo htmlspecialchars($grievance['title']); ?>
                                        <span class="status-badge status-<?php echo htmlspecialchars($grievance['status']); ?>">
                                            <?php 
                                                if ($grievance['status'] == 'in-progress') {
                                                    echo 'In Progress';
                                                } else {
                                                    echo ucfirst(htmlspecialchars($grievance['status']));
                                                }
                                            ?>
                                        </span>
                                    </div>
                                    <div class="grievance-meta">
                                        ID: <?php echo htmlspecialchars($grievance['grievance_id']); ?> • <?php echo isset($categories[$grievance['category_id']]) ? htmlspecialchars($categories[$grievance['category_id']]) : 'N/A'; ?> • Submitted on <?php echo date('Y-m-d', strtotime($grievance['created_at'])); ?>
                                    </div>
                                    <div class="grievance-description">
                                        <?php echo substr(htmlspecialchars($grievance['description']), 0, 150); ?><?php if (strlen($grievance['description']) > 150) echo '...'; ?>
                                    </div>
                                </div>
                                <div class="grievance-actions">
                                    <a href="grievance-detail.php?id=<?php echo htmlspecialchars($grievance['grievance_id']); ?>" class="btn btn-secondary-outline">View Details</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-times-circle"></i>
                            <h3>No grievances found</h3>
                            <p>You haven't submitted any grievances yet.</p>
                            <a href="new-grievance.php" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i>
                                Submit a Grievance
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const tabItems = document.querySelectorAll(".tab-item");
                const grievanceItems = document.querySelectorAll(".grievance-item");

                tabItems.forEach(tab => {
                    tab.addEventListener("click", () => {
                        // Remove active class from all tabs
                        tabItems.forEach(t => t.classList.remove("active"));
                        // Add active class to clicked tab
                        tab.classList.add("active");

                        const status = tab.getAttribute("data-tab");
                        console.log("Selected tab status:", status);

                        grievanceItems.forEach(item => {
                            const badge = item.querySelector(".status-badge");
                            const badgeClasses = badge ? Array.from(badge.classList) : [];
                            console.log("Grievance badge classes:", badgeClasses);

                            if (status === "all") {
                                item.style.display = "flex";
                            } else {
                                // Normalize status string for comparison
                                const normalizedStatus = status.trim().toLowerCase();
                                // Special case for in-progress status
                                if (normalizedStatus === "in-progress") {
                                    if (badge && badge.classList.contains("status-in-progress")) {
                                        item.style.display = "flex";
                                    } else {
                                        item.style.display = "none";
                                    }
                                } else {
                                    if (badge && badge.classList.contains("status-" + normalizedStatus)) {
                                        item.style.display = "flex";
                                    } else {
                                        item.style.display = "none";
                                    }
                                }
                            }
                        });
                    });
                });
            });
        </script>
</body>
</html>
