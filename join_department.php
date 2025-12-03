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

$orgId = $_GET['org_id'] ?? null;
if (!$orgId) {
    header('Location: tenant_main.php');
    exit;
}

$organization = getOrganizationById($orgId);
if (!$organization) {
    header('Location: tenant_main.php');
    exit;
}

// Get all departments for this organization
$allDepartments = getDepartmentsByOrganization($orgId);

// Get departments user is already in
$userDeptRelations = getUserDepartments($user['id'], $orgId);
$userDeptIds = array_column($userDeptRelations, 'department_id');

// Filter to show only departments user hasn't joined
$availableDepartments = array_filter($allDepartments, function($dept) use ($userDeptIds) {
    return !in_array($dept['id'], $userDeptIds);
});

$successMessage = '';
$errorMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_department'])) {
    $departmentIds = $_POST['departments'] ?? [];
    $unitNumber = $_POST['unit_number'] ?? '';
    
    // Get unit number from existing relations if available
    if (empty($unitNumber) && !empty($userDeptRelations)) {
        $unitNumber = $userDeptRelations[0]['unit_number'];
    }
    
    if (empty($departmentIds)) {
        $errorMessage = 'Please select at least one department';
    } elseif (empty($unitNumber)) {
        $errorMessage = 'Please provide your unit number';
    } else {
        $userDepartments = getJsonData('user_departments.json');
        
        // Add user to selected departments
        foreach ($departmentIds as $deptId) {
            // Check if already in department
            $alreadyJoined = false;
            foreach ($userDepartments as $ud) {
                if ($ud['user_id'] == $user['id'] && $ud['department_id'] == $deptId) {
                    $alreadyJoined = true;
                    break;
                }
            }
            
            if (!$alreadyJoined) {
                $newRelation = [
                    'id' => getNextId('user_departments.json'),
                    'user_id' => $user['id'],
                    'organization_id' => (int)$orgId,
                    'department_id' => (int)$deptId,
                    'unit_number' => $unitNumber,
                    'joined_at' => date('Y-m-d\TH:i:s')
                ];
                
                $userDepartments[] = $newRelation;
            }
        }
        
        saveJsonData('user_departments.json', $userDepartments);
        
        $_SESSION['selected_org_id'] = (int)$orgId;
        header('Location: tenant_main.php?success=joined_dept');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Department - <?php echo htmlspecialchars($organization['name']); ?></title>
    <link rel="stylesheet" href="join_org.css">
</head>
<body>
    <div class="page-container">
        <div class="page-header">
            <a href="tenant_main.php?org_id=<?php echo $orgId; ?>" class="back-btn">
                <span>←</span> Back
            </a>
            <h1>Join Department</h1>
            <p><?php echo htmlspecialchars($organization['name']); ?></p>
        </div>

        <div class="form-container">
            <?php if ($errorMessage): ?>
                <div class="error-alert">
                    ✗ <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($availableDepartments)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🎉</div>
                    <h2>All Set!</h2>
                    <p>You're already a member of all departments in this organization.</p>
                    <a href="tenant_main.php?org_id=<?php echo $orgId; ?>" class="btn-primary">Back to Departments</a>
                </div>
            <?php else: ?>
                <form method="POST">
                    <?php if (empty($userDeptRelations)): ?>
                        <div class="form-group">
                            <label for="unit_number">Your Unit Number *</label>
                            <input type="text" 
                                   name="unit_number" 
                                   id="unit_number" 
                                   placeholder="e.g., A-201, 3B, 105" 
                                   required>
                            <small>Enter your apartment/unit number in this building</small>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Select Departments to Join *</label>
                        <small>Choose the departments you need for maintenance requests</small>
                        <div class="departments-list">
                            <?php foreach ($availableDepartments as $dept): ?>
                                <label class="dept-checkbox">
                                    <input type="checkbox" 
                                           name="departments[]" 
                                           value="<?php echo $dept['id']; ?>"
                                           id="dept_<?php echo $dept['id']; ?>">
                                    <div class="dept-card">
                                        <div class="dept-icon" style="background: <?php echo $dept['color']; ?>20; color: <?php echo $dept['color']; ?>">
                                            <?php echo $dept['icon']; ?>
                                        </div>
                                        <div class="dept-info">
                                            <h4><?php echo htmlspecialchars($dept['name']); ?></h4>
                                            <p><?php echo htmlspecialchars($dept['description']); ?></p>
                                            <span class="dept-manager">👤 <?php echo htmlspecialchars($dept['manager']); ?></span>
                                        </div>
                                        <div class="check-indicator">✓</div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="join_department" class="btn-submit">
                            Join Departments
                        </button>
                        <a href="tenant_main.php?org_id=<?php echo $orgId; ?>" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
