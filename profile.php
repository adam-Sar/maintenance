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
    <title>Profile - MaintenanceHub</title>
    <link rel="stylesheet" href="profile.css"> <!-- use existing styles -->
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
            <a href="tenant_main.php" class="nav-item">
                <span class="nav-icon">🏠</span>
                <span>My Apartments</span>
            </a>
            <a href="all_requests.php" class="nav-item">
                <span class="nav-icon">📋</span>
                <span>All Requests</span>
            </a>
            <a href="profile.php" class="nav-item active">
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
        <div class="page-header">
            <div class="header-content">
                <div>
                    <h1>👤 My Profile</h1>
                    <p>View and update your personal information</p>
                </div>
                <a href="tenant_main.php" class="btn-back">Back to Apartments</a>
            </div>
        </div>

        <!-- Profile Details -->
        <div class="requests-container">
            <div class="request-card">
                <h3>Name</h3>
                <p><?php echo htmlspecialchars($user['name']); ?></p>
            </div>
            <div class="request-card">
                <h3>Email</h3>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <div class="request-card">
                <h3>Role</h3>
                <p><?php echo htmlspecialchars($user['role']); ?></p>
            </div>
        </div>
    </div>

    <script>
        function toggleMenu(){
            const sidebar = document.getElementById('sidebar');
            const hamburger = document.getElementById('hamburgerMenu');
            sidebar.classList.toggle('active');
            hamburger.classList.toggle('shifted');
        }
    </script>
</body>
</html>