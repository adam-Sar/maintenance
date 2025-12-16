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

// Get available units for selected organization (via AJAX or initial load)
$selectedOrgId = $_POST['organization_id'] ?? $_GET['org_id'] ?? null;
$availableUnits = [];
if ($selectedOrgId) {
    $selectedOrgId = (int)$selectedOrgId;
    // Get all units for this organization
    $query = "SELECT * FROM units WHERE organization_id = $selectedOrgId ORDER BY name ASC";
    $result = mysqli_query($conn, $query);
    $allUnits = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    // Get units user has already joined
    $userId = (int)$user['id'];
    $query = "SELECT unit_id FROM user_units WHERE user_id = $userId AND organization_id = $selectedOrgId";
    $result = mysqli_query($conn, $query);
    $userUnitIds = array_column(mysqli_fetch_all($result, MYSQLI_ASSOC), 'unit_id');
    
    // Filter to show only units user hasn't joined
    $availableUnits = array_filter($allUnits, function($unit) use ($userUnitIds) {
        return !in_array($unit['id'], $userUnitIds);
    });
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_organization'])) {
    $orgId = $_POST['organization_id'] ?? '';
    $unitId = $_POST['unit_id'] ?? '';
    
    if (empty($orgId)) {
        $errorMessage = 'Please select an organization';
    } elseif (empty($unitId)) {
        $errorMessage = 'Please select a unit';
    } else {
        // Check if user already in this organization with this unit
        if (in_array($orgId, $userOrgIds)) {
            $errorMessage = 'You are already a member of this organization';
        } else {
            $orgId = (int)$orgId;
            $unitId = (int)$unitId;
            $userId = (int)$user['id'];
            
            // Verify unit belongs to organization
            $query = "SELECT id FROM units WHERE id = $unitId AND organization_id = $orgId";
            $result = mysqli_query($conn, $query);
            
            if (mysqli_num_rows($result) === 0) {
                $errorMessage = 'Invalid unit selection';
            } else {
                // Link user to unit with pending status (0)
                $query = "INSERT INTO user_units (user_id, organization_id, unit_id, status) VALUES ($userId, $orgId, $unitId, 0)";
                
                if (mysqli_query($conn, $query)) {
                    $_SESSION['selected_org_id'] = $orgId;
                    header('Location: tenant_main.php?success=pending');
                    exit;
                } else {
                    $errorMessage = "Error joining organization: " . mysqli_error($conn);
                }
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
            <p>Select an organization and a unit to join</p>
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
                    <a href="tenant_main.php" class="btn-primary">← Back to Organizations</a>
                </div>
            <?php else: ?>
                <form method="POST" id="joinForm">
                    <div class="form-group">
                        <label for="organization_id">Select Organization *</label>
                        <select name="organization_id" id="organization_id" required onchange="loadUnits()">
                            <option value="">Choose an organization...</option>
                            <?php foreach ($availableOrganizations as $org): ?>
                                <option value="<?php echo $org['id']; ?>" 
                                        data-address="<?php echo htmlspecialchars($org['address'] ?? ''); ?>"
                                        <?php echo ($selectedOrgId == $org['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($org['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="org-info" id="orgInfo"></div>
                    </div>

                    <div class="form-group" id="unitGroup" style="<?php echo empty($availableUnits) ? 'display:none;' : ''; ?>">
                        <label for="unit_id">Select Unit *</label>
                        <select name="unit_id" id="unit_id" required>
                            <option value="">Choose a unit...</option>
                            <?php foreach ($availableUnits as $unit): ?>
                                <option value="<?php echo $unit['id']; ?>">
                                    Unit <?php echo htmlspecialchars($unit['name']); ?>
                                    <?php if (!empty($unit['description'])): ?>
                                        - <?php echo htmlspecialchars($unit['description']); ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small>Select from available units in this organization</small>
                    </div>

                    <div id="noUnitsMessage" style="display:none;" class="info-message">
                        <p>⚠️ No available units in this organization. All units are already assigned to you or no units have been created yet.</p>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="join_organization" class="btn-submit" id="submitBtn" 
                                <?php echo empty($availableUnits) && $selectedOrgId ? 'disabled' : ''; ?>>
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

        function loadUnits() {
            const orgId = document.getElementById('organization_id').value;
            const unitGroup = document.getElementById('unitGroup');
            const noUnitsMsg = document.getElementById('noUnitsMessage');
            const submitBtn = document.getElementById('submitBtn');
            
            if (!orgId) {
                unitGroup.style.display = 'none';
                noUnitsMsg.style.display = 'none';
                submitBtn.disabled = true;
                return;
            }
            
            showOrgInfo();
            
            // Reload page with selected org to get units
            window.location.href = 'join_organization.php?org_id=' + orgId;
        }

        // Show org info on page load if org is selected
        <?php if ($selectedOrgId): ?>
        showOrgInfo();
        <?php endif; ?>
    </script>
</body>
</html>
