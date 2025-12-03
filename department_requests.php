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

$deptId = $_GET['dept_id'] ?? null;
if (!$deptId) {
    header('Location: tenant_main.php');
    exit;
}

// Verify user has access to this department
if (!isUserInDepartment($user['id'], $deptId)) {
    header('Location: tenant_main.php?error=access_denied');
    exit;
}

$department = getDepartmentById($deptId);
$organization = getOrganizationById($department['organization_id']);

// Get complaints for this department and user
$complaints = getComplaintsByDepartment($deptId, $user['id']);
usort($complaints, function($a, $b) {
    return strtotime($b['submitted_at']) - strtotime($a['submitted_at']);
});

$successMessage = '';
$errorMessage = '';

// Handle new complaint submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_complaint'])) {
    $category = $_POST['category'] ?? '';
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $priority = $_POST['priority'] ?? 'medium';
    
    if (empty($category) || empty($title) || empty($description)) {
        $errorMessage = 'Please fill in all required fields';
    } else {
        // Get unit number from user_departments
        $userDepts = getUserDepartments($user['id']);
        $unitNumber = '';
        foreach ($userDepts as $ud) {
            if ($ud['department_id'] == $deptId) {
                $unitNumber = $ud['unit_number'];
                break;
            }
        }
        
        $allComplaints = getJsonData('complaints.json');
        
        $newComplaint = [
            'id' => getNextId('complaints.json'),
            'organization_id' => $department['organization_id'],
            'department_id' => (int)$deptId,
            'user_id' => $user['id'],
            'unit_number' => $unitNumber,
            'category' => $category,
            'title' => $title,
            'description' => $description,
            'status' => 'pending',
            'priority' => $priority,
            'submitted_at' => date('Y-m-d\TH:i:s'),
            'updated_at' => date('Y-m-d\TH:i:s')
        ];
        
        $allComplaints[] = $newComplaint;
        saveJsonData('complaints.json', $allComplaints);
        
        $successMessage = 'Maintenance request submitted successfully!';
        
        // Refresh complaints
        $complaints = getComplaintsByDepartment($deptId, $user['id']);
        usort($complaints, function($a, $b) {
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
    <title><?php echo htmlspecialchars($department['name']); ?> - Requests</title>
    <link rel="stylesheet" href="dept_requests.css">
</head>
<body>
    <!-- Header -->
    <div class="page-header">
        <div class="header-container">
            <a href="tenant_main.php" class="back-btn">
                <span>←</span> Back to Departments
            </a>
            <div class="dept-info">
                <div class="dept-icon-large" style="background: <?php echo $department['color']; ?>20; color: <?php echo $department['color']; ?>">
                    <?php echo $department['icon']; ?>
                </div>
                <div>
                    <h1><?php echo htmlspecialchars($department['name']); ?></h1>
                    <p><?php echo htmlspecialchars($organization['name']); ?> • <?php echo htmlspecialchars($department['manager']); ?></p>
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

        <!-- New Request Form -->
        <div class="form-section">
            <h2>🔧 Submit New Request</h2>
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
                    <textarea name="description" id="description" rows="4" placeholder="Provide details..." required></textarea>
                </div>
                
                <button type="submit" name="submit_complaint" class="btn-submit">Submit Request</button>
            </form>
        </div>

        <!-- My Requests -->
        <div class="requests-section">
            <h2>📋 My Requests (<?php echo count($complaints); ?>)</h2>
            
            <?php if (empty($complaints)): ?>
                <div class="empty-state">
                    <p>📭 No requests submitted yet</p>
                </div>
            <?php else: ?>
                <div class="requests-list">
                    <?php foreach ($complaints as $complaint): ?>
                        <div class="request-card">
                            <div class="request-header">
                                <div>
                                    <h3><?php echo htmlspecialchars($complaint['title']); ?></h3>
                                    <div class="request-meta">
                                        <span>📁 <?php echo htmlspecialchars($complaint['category']); ?></span>
                                        <span>🕒 <?php echo formatDateTime($complaint['submitted_at']); ?></span>
                                    </div>
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
                            <p><?php echo htmlspecialchars($complaint['description']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
