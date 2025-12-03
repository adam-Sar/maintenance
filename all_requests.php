<?php
session_start();
require_once 'helpers.php';

// Check if user is logged in and is a tenant
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$user = getUserByEmail($_SESSION['user_email']);
if (!$user || $user['role'] !== 'tenant') {
    header('Location: login.php');
    exit;
}

// Get all user's departments
$allUserDepartments = getUserDepartments($user['id']);
$userDeptIds = array_column($allUserDepartments, 'department_id');

// Get all complaints by this user
$allComplaints = getComplaintsByUser($user['id']);

// Sort by submitted date (newest first)
usort($allComplaints, function($a, $b) {
    return strtotime($b['submitted_at']) - strtotime($a['submitted_at']);
});

// Group by department
$complaintsByDepartment = [];
$departments = getJsonData('departments.json');
$organizations = getJsonData('organizations.json');

foreach ($allComplaints as $complaint) {
    $deptId = $complaint['department_id'] ?? null;
    if (!isset($complaintsByDepartment[$deptId])) {
        $complaintsByDepartment[$deptId] = [];
    }
    $complaintsByDepartment[$deptId][] = $complaint;
}

// Get statistics
$totalRequests = count($allComplaints);
$pendingCount = count(array_filter($allComplaints, fn($c) => $c['status'] === 'pending'));
$inProgressCount = count(array_filter($allComplaints, fn($c) => $c['status'] === 'in_progress'));
$resolvedCount = count(array_filter($allComplaints, fn($c) => $c['status'] === 'resolved'));

// Filter
$filterStatus = $_GET['status'] ?? 'all';
$filterDept = $_GET['dept'] ?? 'all';

$filteredComplaints = $allComplaints;

if ($filterStatus !== 'all') {
    $filteredComplaints = array_filter($filteredComplaints, fn($c) => $c['status'] === $filterStatus);
}

if ($filterDept !== 'all') {
    $filteredComplaints = array_filter($filteredComplaints, fn($c) => ($c['department_id'] ?? 0) == $filterDept);
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
    <title>All Requests - MaintenanceHub</title>
    <link rel="stylesheet" href="all_requests.css">
</head>
<body>
    <!-- Hamburger Menu -->
    <div class="hamburger-menu">
        <button class="hamburger-btn" onclick="toggleMenu()">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="user-avatar">👤</div>
            <div class="user-info">
                <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="tenant_main.php" class="nav-item">
                <span class="nav-icon">🏠</span>
                <span>My Departments</span>
            </a>
            <a href="all_requests.php" class="nav-item active">
                <span class="nav-icon">📋</span>
                <span>All Requests</span>
            </a>
            <a href="#" class="nav-item">
                <span class="nav-icon">👤</span>
                <span>Profile Settings</span>
            </a>
            <a href="?logout=1" class="nav-item">
                <span class="nav-icon">🚪</span>
                <span>Logout</span>
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="page-header">
            <div class="header-content">
                <div>
                    <h1>📋 All Maintenance Requests</h1>
                    <p>View and track all your submitted maintenance requests</p>
                </div>
                <a href="tenant_main.php" class="btn-back">Back to Departments</a>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-container">
            <div class="stat-card total">
                <div class="stat-icon">📊</div>
                <div>
                    <h3><?php echo $totalRequests; ?></h3>
                    <p>Total Requests</p>
                </div>
            </div>
            <div class="stat-card pending">
                <div class="stat-icon">⏳</div>
                <div>
                    <h3><?php echo $pendingCount; ?></h3>
                    <p>Pending</p>
                </div>
            </div>
            <div class="stat-card progress">
                <div class="stat-icon">🔧</div>
                <div>
                    <h3><?php echo $inProgressCount; ?></h3>
                    <p>In Progress</p>
                </div>
            </div>
            <div class="stat-card resolved">
                <div class="stat-icon">✅</div>
                <div>
                    <h3><?php echo $resolvedCount; ?></h3>
                    <p>Resolved</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-section">
            <div class="filter-group">
                <label>Status:</label>
                <select onchange="applyFilter()" id="statusFilter">
                    <option value="all" <?php echo $filterStatus === 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="pending" <?php echo $filterStatus === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="in_progress" <?php echo $filterStatus === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="resolved" <?php echo $filterStatus === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Department:</label>
                <select onchange="applyFilter()" id="deptFilter">
                    <option value="all" <?php echo $filterDept === 'all' ? 'selected' : ''; ?>>All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                        <?php if (in_array($dept['id'], $userDeptIds)): ?>
                            <option value="<?php echo $dept['id']; ?>" <?php echo $filterDept == $dept['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['name']); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="results-count">
                Showing <?php echo count($filteredComplaints); ?> of <?php echo $totalRequests; ?> requests
            </div>
        </div>

        <!-- Requests List -->
        <div class="requests-container">
            <?php if (empty($filteredComplaints)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h2>No Requests Found</h2>
                    <p>You haven't submitted any maintenance requests yet.</p>
                    <a href="tenant_main.php" class="btn-primary">Submit First Request</a>
                </div>
            <?php else: ?>
                <div class="requests-list">
                    <?php foreach ($filteredComplaints as $complaint): ?>
                        <?php 
                        $dept = getDepartmentById($complaint['department_id'] ?? 0);
                        $org = getOrganizationById($complaint['organization_id'] ?? 0);
                        ?>
                        <div class="request-card">
                            <div class="request-header">
                                <div class="dept-badge" style="background: <?php echo $dept ? $dept['color'] : '#6b7280'; ?>20; color: <?php echo $dept ? $dept['color'] : '#6b7280'; ?>">
                                    <?php echo $dept ? $dept['icon'] : '🏢'; ?>
                                    <?php echo $dept ? htmlspecialchars($dept['name']) : 'Unknown'; ?>
                                </div>
                                <div class="badges">
                                    <span class="badge <?php echo getPriorityBadgeClass($complaint['priority']); ?>">
                                        <?php echo ucfirst($complaint['priority']); ?>
                                    </span>
                                    <span class="badge <?php echo getStatusBadgeClass($complaint['status']); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <h3><?php echo htmlspecialchars($complaint['title']); ?></h3>
                            
                            <p class="request-description"><?php echo htmlspecialchars($complaint['description']); ?></p>
                            
                            <div class="request-meta">
                                <span>🏢 <?php echo $org ? htmlspecialchars($org['name']) : 'Unknown'; ?></span>
                                <span>🏠 Unit <?php echo htmlspecialchars($complaint['unit_number']); ?></span>
                                <span>📁 <?php echo htmlspecialchars($complaint['category']); ?></span>
                                <span>🕒 <?php echo formatDateTime($complaint['submitted_at']); ?></span>
                            </div>
                            
                            <?php if ($complaint['updated_at'] !== $complaint['submitted_at']): ?>
                                <div class="request-footer">
                                    Last updated: <?php echo formatDateTime($complaint['updated_at']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleMenu() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        function applyFilter() {
            const status = document.getElementById('statusFilter').value;
            const dept = document.getElementById('deptFilter').value;
            
            let url = 'all_requests.php?';
            if (status !== 'all') url += 'status=' + status + '&';
            if (dept !== 'all') url += 'dept=' + dept;
            
            window.location.href = url;
        }
    </script>
</body>
</html>
