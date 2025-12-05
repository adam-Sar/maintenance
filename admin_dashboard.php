<?php
session_start();
require_once 'helpers.php';

// Check if user is logged in and is a landlord
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$user = getUserByEmail($_SESSION['user_email']);
if (!$user || $user['role'] !== 'landlord') {
    header('Location: tenant_main.php');
    exit;
}

// Check if landlord manages an organization
// In SQL schema, organizations table has admin_id.
// We need to find the organization where admin_id = user_id
global $conn;
$userId = (int)$user['id'];
$query = "SELECT * FROM organizations WHERE admin_id = $userId";
$result = mysqli_query($conn, $query);
$organization = mysqli_fetch_assoc($result);

if (!$organization) {
    // Handle case where landlord has no organization
}

// Get all complaints for this organization
$complaints = [];
if ($organization) {
    $complaints = getComplaintsByOrganization($organization['id']);
}

// Statistics
$totalComplaints = count($complaints);
$pendingCount = count(array_filter($complaints, fn($c) => $c['status'] === 'pending'));
$inProgressCount = count(array_filter($complaints, fn($c) => $c['status'] === 'in_progress'));
$resolvedCount = count(array_filter($complaints, fn($c) => $c['status'] === 'resolved'));

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $complaintId = (int)$_POST['complaint_id'];
    $newStatus = mysqli_real_escape_string($conn, $_POST['status']);
    
    global $conn;
    $query = "UPDATE complaints SET status = '$newStatus' WHERE id = $complaintId";
    
    if (mysqli_query($conn, $query)) {
        header('Location: admin_dashboard.php?success=1');
        exit;
    }
}

// Logout handling
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo $organization ? htmlspecialchars($organization['name']) : 'Maintenance'; ?></title>
    <meta name="description" content="Manage maintenance requests for your properties">
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>
    <div class="dashboard-header">
        <div class="header-content">
            <div>
                <h1>🏢 <?php echo $organization ? htmlspecialchars($organization['name']) : 'No Organization'; ?></h1>
                <p class="org-address"><?php echo $organization ? htmlspecialchars($organization['address']) : ''; ?></p>
            </div>
            <div class="header-actions">
                <span class="user-info">👤 <?php echo htmlspecialchars($user['name']); ?></span>
                <a href="?logout=1" class="logout-btn">Logout</a>
            </div>
        </div>
    </div>

    <div class="dashboard-content">
        <?php if (isset($_GET['success'])): ?>
            <div class="success-alert">
                ✓ Complaint status updated successfully!
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-info">
                    <h3><?php echo $totalComplaints; ?></h3>
                    <p>Total Complaints</p>
                </div>
            </div>
            <div class="stat-card pending">
                <div class="stat-icon">⏳</div>
                <div class="stat-info">
                    <h3><?php echo $pendingCount; ?></h3>
                    <p>Pending</p>
                </div>
            </div>
            <div class="stat-card progress">
                <div class="stat-icon">🔧</div>
                <div class="stat-info">
                    <h3><?php echo $inProgressCount; ?></h3>
                    <p>In Progress</p>
                </div>
            </div>
            <div class="stat-card resolved">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h3><?php echo $resolvedCount; ?></h3>
                    <p>Resolved</p>
                </div>
            </div>
        </div>

        <!-- Complaints Table -->
        <div class="complaints-section">
            <h2>Maintenance Requests</h2>
            
            <?php if (empty($complaints)): ?>
                <div class="empty-state">
                    <p>📭 No maintenance requests yet</p>
                </div>
            <?php else: ?>
                <div class="complaints-table">
                    <?php foreach ($complaints as $complaint): ?>
                        <div class="complaint-card">
                            <div class="complaint-header">
                                <div>
                                    <h3><?php echo htmlspecialchars($complaint['title']); ?></h3>
                                    <div class="complaint-meta">
                                        <span class="meta-item">🏠 Unit <?php echo htmlspecialchars($complaint['unit_number']); ?></span>
                                        <span class="meta-item">📁 <?php echo htmlspecialchars($complaint['category']); ?></span>
                                        <span class="meta-item">🕒 <?php echo formatDateTime($complaint['submitted_at']); ?></span>
                                    </div>
                                </div>
                                <div class="badges">
                                    <span class="badge <?php echo getStatusBadgeClass($complaint['status']); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="complaint-body">
                                <p><?php echo htmlspecialchars($complaint['description']); ?></p>
                            </div>
                            
                            <div class="complaint-footer">
                                <form method="POST" class="status-form">
                                    <input type="hidden" name="complaint_id" value="<?php echo $complaint['id']; ?>">
                                    <label for="status_<?php echo $complaint['id']; ?>">Update Status:</label>
                                    <select name="status" id="status_<?php echo $complaint['id']; ?>">
                                        <option value="pending" <?php echo $complaint['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="in_progress" <?php echo $complaint['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="resolved" <?php echo $complaint['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn-update">Update</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
