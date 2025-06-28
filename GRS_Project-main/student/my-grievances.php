<?php
$page_title = "My Grievances";
$is_dashboard = true;
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!is_logged_in()) {
    redirect_with_message('../login.php', 'Please login to access this page.', 'error');
}

$user_id = $_SESSION['user_id'];

// Fetch grievances for the user without category name (only category_id)
$sql = "SELECT * FROM grievances WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$grievances = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

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

// Calculate grievance statistics
$total_grievances = count($grievances);
$pending_grievances = 0;
$in_progress_grievances = 0;
$resolved_grievances = 0;
$rejected_grievances = 0;

foreach ($grievances as $grievance) {
    switch ($grievance['status']) {
        case 'pending':
            $pending_grievances++;
            break;
/* Removed in-progress case as it is not responding
case 'in-progress':
    $in_progress_grievances++;
    break;
*/
        case 'resolved':
            $resolved_grievances++;
            break;
        case 'rejected':
            $rejected_grievances++;
            break;
    }
}

require_once '../includes/header.php';
?>

<div class="dashboard">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="dashboard-header">
            <div class="dashboard-title">
                <h1>My Grievances</h1>
                <p>View and manage all your submitted grievances</p>
            </div>
            <div class="dashboard-actions">
                <a href="new-grievance.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i>
                    New Grievance
                </a>
            </div>
        </div>

        <div class="stats-cards" style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; flex: 1; box-shadow: 0 4px 6px rgba(102, 126, 234, 0.4); border-radius: 8px;">
                <div class="stat-card-header">
                    <div class="stat-card-title">Total Grievances</div>
                    <div class="stat-card-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
                <div class="stat-card-value"><?php echo $total_grievances; ?></div>
                <div class="stat-card-info">All grievances submitted</div>
            </div>

            <div class="stat-card" style="background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); color: white; flex: 1; box-shadow: 0 4px 6px rgba(246, 211, 101, 0.4); border-radius: 8px;">
                <div class="stat-card-header">
                    <div class="stat-card-title">Pending</div>
                    <div class="stat-card-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-card-value"><?php echo $pending_grievances; ?></div>
                <div class="stat-card-info">Awaiting review</div>
            </div>

<!-- Removed In Progress stat card as it is not responding -->
<!--
            <div class="stat-card" style="background: linear-gradient(135deg, #89f7fe 0%, #66a6ff 100%); color: white; flex: 1; box-shadow: 0 4px 6px rgba(102, 166, 255, 0.4); border-radius: 8px;">
                <div class="stat-card-header">
                    <div class="stat-card-title">In Progress</div>
                    <div class="stat-card-icon">
                        <i class="fas fa-spinner"></i>
                    </div>
                </div>
                <div class="stat-card-value"><?php echo $in_progress_grievances; ?></div>
                <div class="stat-card-info">Currently being addressed</div>
            </div>
-->

            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; flex: 1; box-shadow: 0 4px 6px rgba(56, 249, 215, 0.4); border-radius: 8px;">
                <div class="stat-card-header">
                    <div class="stat-card-title">Resolved</div>
                    <div class="stat-card-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-card-value"><?php echo $resolved_grievances; ?></div>
                <div class="stat-card-info">Successfully resolved</div>
            </div>
        </div>

        <div class="tabs" style="margin-top: 2rem;">
            <div class="tab-list" style="display: flex; gap: 1rem; cursor: pointer;">
                <div class="tab-item active" data-tab="all" style="padding: 0.5rem 1rem; background-color: #007bff; color: white; border-radius: 4px;">All</div>
                <div class="tab-item" data-tab="pending" style="padding: 0.5rem 1rem; background-color: #ffc107; color: black; border-radius: 4px;">Pending</div>
<!-- Removed In Progress tab as it is not responding -->
<!-- <div class="tab-item" data-tab="in-progress" style="padding: 0.5rem 1rem; background-color: #17a2b8; color: white; border-radius: 4px;">In Progress</div> -->
                <div class="tab-item" data-tab="resolved" style="padding: 0.5rem 1rem; background-color: #28a745; color: white; border-radius: 4px;">Resolved</div>
                <div class="tab-item" data-tab="rejected" style="padding: 0.5rem 1rem; background-color: #dc3545; color: white; border-radius: 4px;">Rejected</div>
            </div>

            <div class="grievance-list" style="margin-top: 1rem;">
                <div class="grievance-list-header">
                    <h2>My Grievances</h2>
                    <p>View and manage all your submitted grievances</p>
                </div>

                <?php if ($total_grievances > 0): ?>
                    <?php foreach ($grievances as $grievance): ?>
<?php
// Removed filtering of 'in-progress' grievances to fully delete the status and tab
// No longer filtering out 'in-progress' grievances here
?>
<div class="grievance-item" data-status="<?php echo htmlspecialchars($grievance['status']); ?>" style="border: 1px solid #ccc; padding: 1rem; margin-bottom: 1rem; border-radius: 4px;">
                            <div class="grievance-info">
                                <div class="grievance-title" style="font-weight: bold; font-size: 1.2rem;">
                                    <?php echo htmlspecialchars($grievance['title']); ?>
                                    <span class="status-badge status-<?php echo htmlspecialchars($grievance['status']); ?>" style="float: right; padding: 0.2rem 0.5rem; border-radius: 4px; background-color: 
                                        <?php 
                                        switch ($grievance['status']) {
                                            case 'pending':
                                                echo '#ffc107';
                                                break;
                                            case 'in-progress':
                                                echo '#17a2b8';
                                                break;
                                            case 'resolved':
                                                echo '#28a745';
                                                break;
                                            case 'rejected':
                                                echo '#dc3545';
                                                break;
                                            default:
                                                echo '#6c757d';
                                        }
                                        ?>; color: white;">
                                        <?php 
                                        if ($grievance['status'] == 'in-progress') {
                                            echo 'In Progress';
                                        } else {
                                            echo ucfirst(htmlspecialchars($grievance['status']));
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="grievance-meta" style="color: #666; font-size: 0.9rem; margin-top: 0.5rem;">
                                    ID: <?php echo htmlspecialchars($grievance['grievance_id']); ?> • 
                                    <?php 
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
                                        $category_name = isset($categories[$grievance['category_id']]) ? $categories[$grievance['category_id']] : 'N/A';
                                        echo htmlspecialchars($category_name);
                                    ?> • 
                                    Submitted on <?php echo date('Y-m-d', strtotime($grievance['created_at'])); ?>
                                </div>
                                <div class="grievance-description" style="margin-top: 0.5rem;">
                                    <?php echo substr(htmlspecialchars($grievance['description']), 0, 150); ?>
                                    <?php if (strlen($grievance['description']) > 150): ?>...<?php endif; ?>
                                </div>
                            </div>
                            <div class="grievance-actions" style="margin-top: 0.5rem;">
                                <a href="grievance-detail.php?id=<?php echo htmlspecialchars($grievance['grievance_id']); ?>" class="btn btn-secondary-outline">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state" style="text-align: center; padding: 2rem;">
                        <i class="fas fa-file-alt" style="font-size: 3rem; color: #ccc;"></i>
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

<?php require_once '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.tab-item');
    const grievanceItems = document.querySelectorAll('.grievance-item');

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('active'));
            // Add active class to clicked tab
            this.classList.add('active');

            const filter = this.getAttribute('data-tab');

            grievanceItems.forEach(item => {
                if (filter === 'all') {
                    item.style.display = 'block';
                } else {
                    // Normalize status for comparison
                    let status = item.getAttribute('data-status').toLowerCase();
                    let filterLower = filter.toLowerCase();
                    // Removed in-progress filtering as tab is removed
                    if (filterLower === 'rejected' && status === 'rejected') {
                        item.style.display = 'block';
                    } else if (status === filterLower) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                }
            });
        });
    });
});
</script>
