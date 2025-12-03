<?php
session_start();
require_once 'helpers.php';

// Check if user is logged in and is a tenant
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$user = getUserByEmail($_SESSION['user_email']);
if (!$user) {
    header('Location: login.php');
    exit;
}

// Redirect landlords to admin dashboard
if ($user['role'] === 'landlord') {
    header('Location: admin_dashboard.php');
    exit;
}

// Get organization details
$organization = getOrganizationById($user['organization_id']);

// Get user's complaints
$complaints = getComplaintsByUser($user['id']);

// Sort by submitted date (newest first)
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
        $allComplaints = getJsonData('complaints.json');
        
        $newComplaint = [
            'id' => getNextId('complaints.json'),
            'organization_id' => $user['organization_id'],
            'user_id' => $user['id'],
            'unit_number' => $user['unit_number'],
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
        
        // Refresh complaints list
        $complaints = getComplaintsByUser($user['id']);
        usort($complaints, function($a, $b) {
            return strtotime($b['submitted_at']) - strtotime($a['submitted_at']);
        });
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
    <title>My Dashboard - Apartment Maintenance</title>
    <meta name="description" content="Submit and track your apartment maintenance requests">
    <link rel="stylesheet" href="user_styles.css">
</head>
<body>
    <div class="dashboard-header">
        <div class="header-content">
            <div>
                <h1>🏠 My Apartment</h1>
                <p class="unit-info">Unit <?php echo htmlspecialchars($user['unit_number']); ?> - <?php echo htmlspecialchars($organization['name']); ?></p>
            </div>
            <div class="header-actions">
                <span class="user-info">👤 <?php echo htmlspecialchars($user['name']); ?></span>
                <a href="?logout=1" class="logout-btn">Logout</a>
            </div>
        </div>
    </div>

    <div class="dashboard-content">
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

        <!-- New Complaint Form -->
        <div class="form-section">
            <h2>🔧 Submit Maintenance Request</h2>
            <form method="POST" class="complaint-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select name="category" id="category" required>
                            <option value="">Select category...</option>
                            <option value="Plumbing">🚰 Plumbing</option>
                            <option value="Electrical">⚡ Electrical</option>
                            <option value="HVAC">🌡️ HVAC (Heating/Cooling)</option>
                            <option value="Appliances">🔌 Appliances</option>
                            <option value="Structural">🏗️ Structural</option>
                            <option value="General">🔧 General Maintenance</option>
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
                    <input type="text" name="title" id="title" placeholder="Brief description of the issue" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Detailed Description *</label>
                    <textarea name="description" id="description" rows="4" placeholder="Provide detailed information about the maintenance issue..." required></textarea>
                </div>
                
                <button type="submit" name="submit_complaint" class="btn-submit">Submit Request</button>
            </form>
        </div>

        <!-- My Complaints -->
        <div class="complaints-section">
            <h2>📋 My Maintenance Requests</h2>
            
            <?php if (empty($complaints)): ?>
                <div class="empty-state">
                    <p>📭 You haven't submitted any maintenance requests yet</p>
                </div>
            <?php else: ?>
                <div class="complaints-list">
                    <?php foreach ($complaints as $complaint): ?>
                        <div class="complaint-card">
                            <div class="complaint-header">
                                <div>
                                    <h3><?php echo htmlspecialchars($complaint['title']); ?></h3>
                                    <div class="complaint-meta">
                                        <span class="meta-item">📁 <?php echo htmlspecialchars($complaint['category']); ?></span>
                                        <span class="meta-item">🕒 <?php echo formatDateTime($complaint['submitted_at']); ?></span>
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
                            
                            <div class="complaint-body">
                                <p><?php echo htmlspecialchars($complaint['description']); ?></p>
                            </div>
                            
                            <?php if ($complaint['status'] === 'resolved'): ?>
                                <div class="resolved-badge">
                                    ✅ This issue has been resolved
                                </div>
                            <?php elseif ($complaint['status'] === 'in_progress'): ?>
                                <div class="progress-badge">
                                    🔧 Work in progress
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
