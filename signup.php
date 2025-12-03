<?php
session_start();
require_once 'helpers.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $organization_id = $_POST['organization_id'] ?? '';
    $unit_number = $_POST['unit_number'] ?? '';
    
    // Validation
    if (empty($role) || empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($organization_id)) {
        $error = 'Please fill in all required fields';
    } elseif ($role === 'tenant' && empty($unit_number)) {
        $error = 'Tenants must provide a unit number';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (getUserByEmail($email)) {
        $error = 'Email address is already registered';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        $users = getJsonData('users.json');
        
        $newUser = [
            'id' => getNextId('users.json'),
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'organization_id' => (int)$organization_id,
            'unit_number' => $role === 'tenant' ? $unit_number : null,
            'created_at' => date('Y-m-d\TH:i:s')
        ];
        
        $users[] = $newUser;
        saveJsonData('users.json', $users);
        
        $success = 'Account created successfully! You can now <a href="login.php" style="color: #059669; font-weight: 600;">login</a>.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Apartment Maintenance System</title>
    <meta name="description" content="Create an account to submit or manage apartment maintenance requests">
    <link rel="stylesheet" href="auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h1>🏢 Join Our System</h1>
            <p>Register as a landlord or tenant to get started</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="role">I am a</label>
                <select 
                    id="role" 
                    name="role" 
                    required
                    style="width: 100%; padding: 14px 16px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 15px; font-family: 'Inter', sans-serif; background: white; cursor: pointer;"
                    onchange="toggleUnitField()"
                >
                    <option value="">Select your role...</option>
                    <option value="landlord" <?php echo (($_POST['role'] ?? '') === 'landlord') ? 'selected' : ''; ?>>🏗️ Landlord / Property Manager</option>
                    <option value="tenant" <?php echo (($_POST['role'] ?? '') === 'tenant') ? 'selected' : ''; ?>>👤 Tenant / Resident</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="organization_id">Organization / Apartment Complex *</label>
                <select 
                    id="organization_id" 
                    name="organization_id" 
                    required
                    style="width: 100%; padding: 14px 16px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 15px; font-family: 'Inter', sans-serif; background: white; cursor: pointer;"
                >
                    <option value="">Select your organization...</option>
                    <?php
                    $organizations = getJsonData('organizations.json');
                    foreach ($organizations as $org) {
                        $selected = (($_POST['organization_id'] ?? '') == $org['id']) ? 'selected' : '';
                        echo '<option value="' . $org['id'] . '" ' . $selected . '>🏢 ' . htmlspecialchars($org['name']) . '</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group" id="unit-field" style="display: none;">
                <label for="unit_number">Unit Number</label>
                <input 
                    type="text" 
                    id="unit_number" 
                    name="unit_number" 
                    placeholder="e.g., A-201, 3B, 105"
                    value="<?php echo htmlspecialchars($_POST['unit_number'] ?? ''); ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="name">Full Name</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    placeholder="John Doe"
                    value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="your.email@example.com"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Create a secure password (min. 6 characters)"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    placeholder="Re-enter your password"
                    required
                >
            </div>
            
            <button type="submit" class="submit-btn">Create Account</button>
        </form>
        
        <div class="form-footer">
            Already have an account? <a href="login.php">Sign in</a>
        </div>
    </div>
    
    <script>
        function toggleUnitField() {
            const role = document.getElementById('role').value;
            const unitField = document.getElementById('unit-field');
            const unitInput = document.getElementById('unit_number');
            
            if (role === 'tenant') {
                unitField.style.display = 'block';
                unitInput.required = true;
            } else {
                unitField.style.display = 'none';
                unitInput.required = false;
            }
        }
        
        // Check on page load
        window.addEventListener('DOMContentLoaded', toggleUnitField);
    </script>
</body>
</html>
