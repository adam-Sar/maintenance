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

// Get user's departments in this apartment
$userDeptRelations = getUserDepartments($user['id'], $aptId);
if (empty($userDeptRelations)) {
    header('Location: tenant_main.php?error=no_access');
    exit;
}

$myUnit = $userDeptRelations[0]['unit_number'];

// Get all departments for this apartment
$allDepartments = getDepartmentsByOrganization($aptId);
$userDeptIds = array_column($userDeptRelations, 'department_id');

// Get user's departments
$myDepartments = array_filter($allDepartments, fn($d) => in_array($d['id'], $userDeptIds));

// Get all complaints for this apartment by this user
$allComplaints = getJsonData('complaints.json');
$myComplaints = array_filter($allComplaints, fn($c) => 
    $c['organization_id'] == $aptId && $c['user_id'] == $user['id']
);

usort($myComplaints, function($a, $b) {
    return strtotime($b['submitted_at']) - strtotime($a['submitted_at']);
});

$successMessage = '';
$errorMessage = '';

// Handle new complaint submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_complaint'])) {
    $departmentId = $_POST['department_id'] ?? '';
    $category = $_POST['category'] ?? '';
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $priority = $_POST['priority'] ?? 'medium';
    
    if (empty($departmentId) || empty($category) || empty($title) || empty($description)) {
        $errorMessage = 'Please fill in all required fields';
    } elseif (!in_array($departmentId, $userDeptIds)) {
        $errorMessage = 'Invalid department selected';
    } else {
        $complaints = getJsonData('complaints.json');
        
        $newComplaint = [
            'id' => getNextId('complaints.json'),
            'organization_id' => (int)$aptId,
            'department_id' => (int)$departmentId,
            'user_id' => $user['id'],
            'unit_number' => $myUnit,
            'category' => $category,
            'title' => $title,
            'description' => $description,
            'status' => 'pending',
            'priority' => $priority,
            'submitted_at' => date('Y-m-d\TH:i:s'),
            'updated_at' => date('Y-m-d\TH:i:s')
        ];
        
        $complaints[] = $newComplaint;
        saveJsonData('complaints.json', $complaints);
        
        $successMessage = 'Maintenance request submitted successfully!';
        
        // Refresh complaints
        $allComplaints = getJsonData('complaints.json');
        $myComplaints = array_filter($allComplaints, fn($c) => 
            $c['organization_id'] == $aptId && $c['user_id'] == $user['id']
        );
        usort($myComplaints, function($a, $b) {
            return strtotime($b['submitted_at']) - strtotime($a['submitted_at']);
        });
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
                <?php if (!empty($apartment['images'])): ?>
                    <div class="header-image">
                        <img src="<?php echo $apartment['images'][0]; ?>" alt="<?php echo htmlspecialchars($apartment['name']); ?>">
                    </div>
                <?php endif; ?>
                <div class="header-details">
                    <h1><?php echo htmlspecialchars($apartment['name']); ?></h1>
                    <p class="apt-address">📍 <?php echo htmlspecialchars($apartment['address']); ?></p>
                    <div class="header-meta">
                        <span class="my-unit">🏠 My Unit: <strong><?php echo htmlspecialchars($myUnit); ?></strong></span>
                        <span>🏗️ <?php echo $apartment['property_type'] ?? 'Apartment'; ?></span>
                        <span>📅 Built <?php echo $apartment['year_built'] ?? 'N/A'; ?></span>
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
                            <label for="department_id">Department *</label>
                            <select name="department_id" id="department_id" required>
                                <option value="">Select department...</option>
                                <?php foreach ($myDepartments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>">
                                        <?php echo $dept['icon']; ?> <?php echo htmlspecialchars($dept['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
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
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="priority">Priority *</label>
                            <select name="priority" id="priority" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="title">Issue Title *</label>
                            <input type="text" name="title" id="title" placeholder="Brief description" required>
                        </div>
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
                            <?php $dept = getDepartmentById($complaint['department_id'] ?? 0); ?>
                            <div class="request-card">
                                <div class="request-header">
                                    <?php if ($dept): ?>
                                        <span class="dept-badge" style="background: <?php echo $dept['color']; ?>20; color: <?php echo $dept['color']; ?>">
                                            <?php echo $dept['icon']; ?> <?php echo htmlspecialchars($dept['name']); ?>
                                        </span>
                                    <?php endif; ?>
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
                                <p><?php echo htmlspecialchars($complaint['description']); ?></p>
                                <div class="request-meta">
                                    <span>📁 <?php echo htmlspecialchars($complaint['category']); ?></span>
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
