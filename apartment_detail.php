<?php
session_start();
require_once 'helpers.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$user = getUserByEmail($_SESSION['user_email']);
if (!$user || $user['role'] !== 'tenant') {
    header('Location: login.php');
    exit;
}

$aptId = $_GET['apt_id'] ?? null;
if (!$aptId) {
    header('Location: tenant_main.php');
    exit;
}

$apartment = getOrganizationById($aptId);
if (!$apartment) {
    header('Location: tenant_main.php');
    exit;
}

// Get user's unit in this apartment
$userDeptRelations = getUserDepartments($user['id'], $aptId);
if (empty($userDeptRelations)) {
    header('Location: tenant_main.php?error=no_access');
    exit;
}

// In SQL schema, we assume one unit per org per user for now, or take the first one
$myUnitRelation = $userDeptRelations[0];
$myUnitName = $myUnitRelation['unit_name'];
$myUnitId = $myUnitRelation['unit_id'];

// Get all complaints for this apartment by this user
// We use getComplaintsByUser and filter by org
$allUserComplaints = getComplaintsByUser($user['id']);
$myComplaints = array_filter($allUserComplaints, fn($c) => $c['organization_id'] == $aptId);

// Sort by submitted date (newest first) - already sorted by query but good to ensure
usort($myComplaints, function($a, $b) {
    return strtotime($b['submitted_at']) - strtotime($a['submitted_at']);
});

$successMessage = '';
$errorMessage = '';

// Handle new complaint submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_complaint'])) {
    $category = $_POST['category'] ?? '';
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $priority = $_POST['priority'] ?? 'medium'; // Not in DB schema but kept for UI if needed
    
    if (empty($category) || empty($title) || empty($description)) {
        $errorMessage = 'Please fill in all required fields';
    } else {
        global $conn;
        
        $aptId = (int)$aptId;
        $userId = (int)$user['id'];
        $myUnitId = (int)$myUnitId;
        $category = mysqli_real_escape_string($conn, $category);
        $title = mysqli_real_escape_string($conn, $title);
        $description = mysqli_real_escape_string($conn, $description);
        
        $query = "INSERT INTO complaints (organization_id, user_id, unit_id, category, title, description, status) VALUES ($aptId, $userId, $myUnitId, '$category', '$title', '$description', 'pending')";
        
        if (mysqli_query($conn, $query)) {
            $successMessage = 'Maintenance request submitted successfully!';
            
            // Refresh complaints
            $allUserComplaints = getComplaintsByUser($user['id']);
            $myComplaints = array_filter($allUserComplaints, fn($c) => $c['organization_id'] == $aptId);
        } else {
            $errorMessage = 'Error submitting request: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($apartment['name']); ?> - Maintenance</title>
    <link rel="stylesheet" href="apartment_detail.css">
</head>
<body>
    <!-- Header -->
    <div class="page-header">
        <div class="header-container">
            <a href="tenant_main.php" class="back-btn">
                <span>←</span> Back to My Apartments
            </a>
            <div class="apt-header-info">
                <div class="header-details">
                    <h1><?php echo htmlspecialchars($apartment['name']); ?></h1>
                    <p class="apt-address">📍 <?php echo htmlspecialchars($apartment['address']); ?></p>
                    <div class="header-meta">
                        <span class="my-unit">🏠 My Unit: <strong><?php echo htmlspecialchars($myUnitName); ?></strong></span>
                        <span>🏗️ Apartment Complex</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-content">
        <?php if ($successMessage): ?>
            <div class="success-alert">
                ✓ <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($errorMessage): ?>
            <div class="error-alert">
                ✗ <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <div class="content-grid">
            <!-- Submit Request Form -->
            <div class="form-section">
                <h2>🔧 Submit Maintenance Request</h2>
                <form method="POST" class="request-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Category *</label>
                            <select name="category" id="category" required>
                                <option value="">Select category...</option>
                                <option value="Plumbing">🚰 Plumbing</option>
                                <option value="Electrical">⚡ Electrical</option>
                                <option value="HVAC">🌡️ HVAC</option>
                                <option value="Appliances">🔌 Appliances</option>
                                <option value="Structural">🏗️ Structural</option>
                                <option value="General">🔧 General</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="priority">Priority *</label>
                            <select name="priority" id="priority" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="title">Issue Title *</label>
                        <input type="text" name="title" id="title" placeholder="Brief description" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Detailed Description *</label>
                        <textarea name="description" id="description" rows="4" placeholder="Provide detailed information..." required></textarea>
                    </div>
                    
                    <button type="submit" name="submit_complaint" class="btn-submit">Submit Request</button>
                </form>
            </div>

            <!-- My Requests -->
            <div class="requests-section">
                <h2>📋 My Requests (<?php echo count($myComplaints); ?>)</h2>
                
                <?php if (empty($myComplaints)): ?>
                    <div class="empty-state">
                        <p>📭 No requests submitted yet</p>
                    </div>
                <?php else: ?>
                    <div class="requests-list">
                        <?php foreach ($myComplaints as $complaint): ?>
                            <div class="request-card">
                                <div class="request-header">
                                    <div class="dept-badge">
                                        📁 <?php echo htmlspecialchars($complaint['category']); ?>
                                    </div>
                                    <div class="badges">
                                        <span class="badge <?php echo getStatusBadgeClass($complaint['status']); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                                        </span>
                                    </div>
                                </div>
                                <h3><?php echo htmlspecialchars($complaint['title']); ?></h3>
                                <p><?php echo htmlspecialchars($complaint['description']); ?></p>
                                <div class="request-meta">
                                    <span>🕒 <?php echo formatDateTime($complaint['submitted_at']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
