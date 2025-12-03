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

$successMessage = '';
$errorMessage = '';

// Get all organizations
$allOrganizations = getJsonData('organizations.json');

// Get organizations user is already in
$userOrgs = getUserOrganizations($user['id']);
$userOrgIds = array_column($userOrgs, 'id');

// Filter to show only organizations user hasn't joined
$availableOrganizations = array_filter($allOrganizations, function($org) use ($userOrgIds) {
    return !in_array($org['id'], $userOrgIds);
});

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_organization'])) {
    $orgId = $_POST['organization_id'] ?? '';
    $unitNumber = $_POST['unit_number'] ?? '';
    $departmentIds = $_POST['departments'] ?? [];
    
    if (empty($orgId)) {
        $errorMessage = 'Please select an organization';
    } elseif (empty($unitNumber)) {
        $errorMessage = 'Please provide your unit number';
    } elseif (empty($departmentIds)) {
        $errorMessage = 'Please select at least one department';
    } else {
        // Check if user already in this organization
        if (in_array($orgId, $userOrgIds)) {
            $errorMessage = 'You are already a member of this organization';
        } else {
            $userDepartments = getJsonData('user_departments.json');
            
            // Add user to selected departments
            foreach ($departmentIds as $deptId) {
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
            
            saveJsonData('user_departments.json', $userDepartments);
            
            $_SESSION['selected_org_id'] = (int)$orgId;
            header('Location: tenant_main.php?success=joined');
            exit;
        }
    }
}

// Get departments for selected organization (for AJAX)
if (isset($_GET['get_departments']) && isset($_GET['org_id'])) {
    $orgId = (int)$_GET['org_id'];
    $departments = getDepartmentsByOrganization($orgId);
    header('Content-Type: application/json');
    echo json_encode(array_values($departments));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Organization - MaintenanceHub</title>
    <link rel="stylesheet" href="join_org.css">
</head>
<body>
    <div class="page-container">
        <div class="page-header">
            <a href="tenant_main.php" class="back-btn">
                <span>←</span> Back
            </a>
            <h1>Join New Organization</h1>
            <p>Select an organization and departments to get started</p>
        </div>

        <div class="form-container">
            <?php if ($errorMessage): ?>
                <div class="error-alert">
                    ✗ <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($availableOrganizations)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🎉</div>
                    <h2>All Set!</h2>
                    <p>You're already a member of all available organizations.</p>
                    <a href="tenant_main.php" class="btn-primary">Back to Departments</a>
                </div>
            <?php else: ?>
                <form method="POST" id="joinForm">
                    <div class="form-group">
                        <label for="organization_id">Select Organization *</label>
                        <select name="organization_id" id="organization_id" required onchange="loadDepartments()">
                            <option value="">Choose an organization...</option>
                            <?php foreach ($availableOrganizations as $org): ?>
                                <option value="<?php echo $org['id']; ?>" 
                                        data-address="<?php echo htmlspecialchars($org['address']); ?>"
                                        data-units="<?php echo $org['total_units']; ?>">
                                    <?php echo htmlspecialchars($org['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="org-info" id="orgInfo"></div>
                    </div>

                    <div class="form-group">
                        <label for="unit_number">Your Unit Number *</label>
                        <input type="text" 
                               name="unit_number" 
                               id="unit_number" 
                               placeholder="e.g., A-201, 3B, 105" 
                               required>
                        <small>Enter your apartment/unit number in this building</small>
                    </div>

                    <div class="form-group">
                        <label>Select Departments to Join *</label>
                        <small>Choose the departments you need for maintenance requests</small>
                        <div class="departments-list" id="departmentsList">
                            <p class="select-org-first">Please select an organization first</p>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="join_organization" class="btn-submit">
                            Join Organization
                        </button>
                        <a href="tenant_main.php" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function loadDepartments() {
            const select = document.getElementById('organization_id');
            const orgId = select.value;
            const orgInfo = document.getElementById('orgInfo');
            const deptList = document.getElementById('departmentsList');
            
            if (!orgId) {
                orgInfo.innerHTML = '';
                deptList.innerHTML = '<p class="select-org-first">Please select an organization first</p>';
                return;
            }
            
            // Show organization info
            const selectedOption = select.options[select.selectedIndex];
            const address = selectedOption.getAttribute('data-address');
            const units = selectedOption.getAttribute('data-units');
            
            orgInfo.innerHTML = `
                <div class="info-card">
                    <span>📍 ${address}</span>
                    <span>🏠 ${units} units</span>
                </div>
            `;
            
            // Load departments
            deptList.innerHTML = '<p class="loading">Loading departments...</p>';
            
            fetch(`join_organization.php?get_departments=1&org_id=${orgId}`)
                .then(response => response.json())
                .then(departments => {
                    if (departments.length === 0) {
                        deptList.innerHTML = '<p class="no-depts">No departments available</p>';
                        return;
                    }
                    
                    let html = '';
                    departments.forEach(dept => {
                        html += `
                            <label class="dept-checkbox">
                                <input type="checkbox" 
                                       name="departments[]" 
                                       value="${dept.id}"
                                       id="dept_${dept.id}">
                                <div class="dept-card">
                                    <div class="dept-icon" style="background: ${dept.color}20; color: ${dept.color}">
                                        ${dept.icon}
                                    </div>
                                    <div class="dept-info">
                                        <h4>${dept.name}</h4>
                                        <p>${dept.description}</p>
                                        <span class="dept-manager">👤 ${dept.manager}</span>
                                    </div>
                                    <div class="check-indicator">✓</div>
                                </div>
                            </label>
                        `;
                    });
                    
                    deptList.innerHTML = html;
                })
                .catch(error => {
                    deptList.innerHTML = '<p class="error">Error loading departments</p>';
                    console.error('Error:', error);
                });
        }
    </script>
</body>
</html>
