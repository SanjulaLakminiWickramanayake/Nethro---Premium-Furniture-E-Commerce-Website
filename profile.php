<?php
$pageTitle = 'My Profile';
require_once 'includes/db.php';

if (!$current_user) {
    setFlash('warning', 'Please sign in to view your profile');
    redirect('login.php?redirect=profile.php');
}

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    
    if (empty($firstName) || empty($lastName)) {
        $error = 'First and last name are required';
    } else {
        executeQuery("UPDATE users SET first_name = ?, last_name = ?, phone = ?, address = ?, city = ? WHERE id = ?",
            "sssssi", [$firstName, $lastName, $phone, $address, $city, $_SESSION['user_id']]);
        
        // Refresh user data
        $current_user = fetchOne("SELECT * FROM users WHERE id = ?", "i", [$_SESSION['user_id']]);
        $success = true;
    }
    
    // Handle password change
    if (!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
        if (password_verify($_POST['current_password'], $current_user['password'])) {
            if (strlen($_POST['new_password']) >= 6) {
                $newHash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                executeQuery("UPDATE users SET password = ? WHERE id = ?", "si", [$newHash, $_SESSION['user_id']]);
                $success = true;
            } else {
                $error = 'New password must be at least 6 characters';
            }
        } else {
            $error = 'Current password is incorrect';
        }
    }
}

$orderCount = fetchOne("SELECT COUNT(*) as count FROM orders WHERE user_id = ?", "i", [$_SESSION['user_id']]);
$totalSpent = fetchOne("SELECT COALESCE(SUM(final_amount), 0) as total FROM orders WHERE user_id = ? AND status != 'cancelled'", "i", [$_SESSION['user_id']]);

require_once 'includes/header.php';
?>

<section class="section" style="padding-top: 40px;">
    <div class="profile-layout">
        <!-- Sidebar -->
        <div class="profile-sidebar">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($current_user['first_name'], 0, 1)); ?>
            </div>
            <h3 style="text-align: center; font-family: var(--font-display); margin-bottom: 4px;"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></h3>
            <p style="text-align: center; color: var(--gray-600); font-size: 14px; margin-bottom: 24px;"><?php echo htmlspecialchars($current_user['email']); ?></p>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; text-align: center;">
                <div style="padding: 16px; background: var(--gray-100); border-radius: var(--radius-md);">
                    <div style="font-size: 24px; font-weight: 700; color: var(--accent);"><?php echo $orderCount['count']; ?></div>
                    <div style="font-size: 12px; color: var(--gray-600);">Orders</div>
                </div>
                <div style="padding: 16px; background: var(--gray-100); border-radius: var(--radius-md);">
                    <div style="font-size: 24px; font-weight: 700; color: var(--accent);"><?php echo formatPrice($totalSpent['total']); ?></div>
                    <div style="font-size: 12px; color: var(--gray-600);">Spent</div>
                </div>
            </div>
            
            <ul class="profile-menu">
                <li><a href="profile.php" class="active"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="orders.php"><i class="fas fa-box"></i> My Orders</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div>
            <?php if ($success): ?>
            <div class="flash-message flash-success" style="position: static; margin-bottom: 24px; animation: none;">
                Profile updated successfully!
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="flash-message flash-error" style="position: static; margin-bottom: 24px; animation: none;">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <div style="background: white; padding: 32px; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); margin-bottom: 24px;">
                <h3 style="font-family: var(--font-display); margin-bottom: 24px; font-size: 24px;">Personal Information</h3>
                
                <form method="POST" action="" data-validate>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" class="form-control" required value="<?php echo htmlspecialchars($current_user['first_name']); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="last_name" class="form-control" required value="<?php echo htmlspecialchars($current_user['last_name']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($current_user['email']); ?>" disabled style="background: var(--gray-200);">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($current_user['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($current_user['address'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($current_user['city'] ?? ''); ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
            
            <div style="background: white; padding: 32px; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
                <h3 style="font-family: var(--font-display); margin-bottom: 24px; font-size: 24px;">Change Password</h3>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" placeholder="Enter current password">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" placeholder="Min. 6 characters" minlength="6">
                    </div>
                    
                    <button type="submit" class="btn btn-outline">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>