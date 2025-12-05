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
global $conn;
$result = mysqli_query($conn, "SELECT * FROM organizations ORDER BY name ASC");
$allOrganizations = mysqli_fetch_all($result, MYSQLI_ASSOC);

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
    
    if (empty($orgId)) {
        $errorMessage = 'Please select an organization';
    } elseif (empty($unitNumber)) {
        $errorMessage = 'Please provide your unit number';
    } else {
        // Check if user already in this organization
        if (in_array($orgId, $userOrgIds)) {
            $errorMessage = 'You are already a member of this organization';
        } else {
            // Database operations
            global $conn;
            
            $orgId = (int)$orgId;
            $unitNumber = mysqli_real_escape_string($conn, $unitNumber);
            
            // Check if unit exists
            $query = "SELECT id FROM units WHERE organization_id = $orgId AND name = '$unitNumber'";
            $result = mysqli_query($conn, $query);
            
            if (mysqli_num_rows($result) > 0) {
                $unit = mysqli_fetch_assoc($result);
                $unitId = $unit['id'];
            } else {
                // Create new unit
                $query = "INSERT INTO units (organization_id, name) VALUES ($orgId, '$unitNumber')";
                mysqli_query($conn, $query);
                $unitId = mysqli_insert_id($conn);
            }
            
            // Link user to unit
            $userId = (int)$user['id'];
            $query = "INSERT INTO user_units (user_id, organization_id, unit_id, status) VALUES ($userId, $orgId, $unitId, 1)";
            
            if (mysqli_query($conn, $query)) {
                $_SESSION['selected_org_id'] = $orgId;
                header('Location: tenant_main.php?success=joined');
                exit;
            } else {
                $errorMessage = "Error joining organization: " . mysqli_error($conn);
            }
        }
    }
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
            <p>Select an organization and enter your unit number</p>
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
                    <a href="tenant_main.php" class="btn-primary">Back to My Apartments</a>
                </div>
            <?php else: ?>
                <form method="POST" id="joinForm">
                    <div class="form-group">
                        <label for="organization_id">Select Organization *</label>
                        <select name="organization_id" id="organization_id" required onchange="showOrgInfo()">
                            <option value="">Choose an organization...</option>
                            <?php foreach ($availableOrganizations as $org): ?>
                                <option value="<?php echo $org['id']; ?>" 
                                        data-address="<?php echo htmlspecialchars($org['address'] ?? ''); ?>">
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
        function showOrgInfo() {
            const select = document.getElementById('organization_id');
            const orgInfo = document.getElementById('orgInfo');
            
            if (!select.value) {
                orgInfo.innerHTML = '';
                return;
            }
            
            const selectedOption = select.options[select.selectedIndex];
            const address = selectedOption.getAttribute('data-address');
            
            if (address) {
                orgInfo.innerHTML = `
                    <div class="info-card">
                        <span>📍 ${address}</span>
                    </div>
                `;
            } else {
                orgInfo.innerHTML = '';
            }
        }
    </script>
</body>
</html>
