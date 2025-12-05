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

// Get user's apartments (organizations they're in)
$userApartments = getUserOrganizations($user['id']);

// Get user department relations to get unit numbers
$userDeptRelations = getUserDepartments($user['id']);

// Create apartment data with unit numbers
$apartments = [];
foreach ($userApartments as $apt) {
    // Find unit number for this apartment
    $unitNumber = '';
    foreach ($userDeptRelations as $relation) {
        if ($relation['organization_id'] == $apt['id']) {
            $unitNumber = $relation['unit_name'] ?? ''; // Use unit_name from JOIN
            break;
        }
    }
    
    $apt['my_unit'] = $unitNumber;
    // Initialize missing fields for display
    $apt['images'] = []; // No images in SQL
    $apt['amenities'] = []; // No amenities in SQL
    $apt['property_type'] = 'Apartment Complex';
    $apt['year_built'] = 'N/A';
    $apt['total_units'] = 'N/A';
    
    $apartments[] = $apt;
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
    <title>My Apartments - MaintenanceHub</title>
    <link rel="stylesheet" href="apartments_main.css">
</head>
<body>
    <!-- Hamburger Menu -->
    <div class="hamburger-menu" id="hamburgerMenu">
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
            <a href="tenant_main.php" class="nav-item active">
                <span class="nav-icon">🏠</span>
                <span>My Apartments</span>
            </a>
            <a href="all_requests.php" class="nav-item">
                <span class="nav-icon">📋</span>
                <span>All Requests</span>
            </a>
            <a href="#" class="nav-item">
                <span class="nav-icon">👤</span>
                <span>Profile</span>
            </a>
            <a href="?logout=1" class="nav-item">
                <span class="nav-icon">🚪</span>
                <span>Logout</span>
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="page-title">
                <h1>My Apartments</h1>
                <p>View and manage your rental properties</p>
            </div>
            <a href="join_organization.php" class="btn-add">
                <span>➕</span> Add New Apartment
            </a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="success-alert">
                <?php if ($_GET['success'] === 'joined'): ?>
                    ✓ Successfully joined apartment! You can now submit maintenance requests.
                <?php elseif ($_GET['success'] === 'joined_dept'): ?>
                    ✓ Successfully joined apartment!
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($apartments)): ?>
            <!-- No Apartments State -->
            <div class="empty-state">
                <div class="empty-icon">🏢</div>
                <h2>No Apartments Found</h2>
                <p>You haven't joined any apartments yet. Add your first apartment to get started with maintenance requests.</p>
                <a href="join_organization.php" class="btn-primary">Add My Apartment</a>
            </div>
        <?php else: ?>
            <!-- Apartments Grid -->
            <div class="apartments-grid">
                <?php foreach ($apartments as $apt): ?>
                    <a href="apartment_detail.php?apt_id=<?php echo $apt['id']; ?>" class="apartment-card">
                        <div class="apartment-images">
                            <?php if (!empty($apt['images'])): ?>
                                <img src="<?php echo $apt['images'][0]; ?>" alt="<?php echo htmlspecialchars($apt['name']); ?>">
                                <?php if (count($apt['images']) > 1): ?>
                                    <div class="image-count">
                                        <span>📷 <?php echo count($apt['images']); ?> photos</span>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="no-image">🏢</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="apartment-content">
                            <div class="apt-header">
                                <h2><?php echo htmlspecialchars($apt['name']); ?></h2>
                                <span class="my-unit-badge">Unit <?php echo htmlspecialchars($apt['my_unit']); ?></span>
                            </div>
                            
                            <p class="apt-address">📍 <?php echo htmlspecialchars($apt['address']); ?></p>
                            
                            <?php if (!empty($apt['description'])): ?>
                                <p class="apt-description"><?php echo htmlspecialchars($apt['description']); ?></p>
                            <?php endif; ?>
                            
                            <div class="apt-details">
                                <div class="detail-item">
                                    <span class="detail-icon">🏗️</span>
                                    <span><?php echo $apt['property_type'] ?? 'Apartment'; ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-icon">📅</span>
                                    <span>Built <?php echo $apt['year_built'] ?? 'N/A'; ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-icon">🏠</span>
                                    <span><?php echo $apt['total_units']; ?> units</span>
                                </div>
                            </div>
                            
                            <?php if (!empty($apt['amenities'])): ?>
                                <div class="amenities">
                                    <?php foreach (array_slice($apt['amenities'], 0, 4) as $amenity): ?>
                                        <span class="amenity-tag"><?php echo htmlspecialchars($amenity); ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($apt['amenities']) > 4): ?>
                                        <span class="amenity-tag more">+<?php echo count($apt['amenities']) - 4; ?> more</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-footer">
                                <span class="submit-request-btn">Submit Request →</span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleMenu() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        // Close sidebar when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const hamburger = document.getElementById('hamburgerMenu');
            
            if (!sidebar.contains(event.target) && !hamburger.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        });
    </script>
</body>
</html>
