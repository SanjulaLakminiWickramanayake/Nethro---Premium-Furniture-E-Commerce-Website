<?php
$pageTitle = 'Login';
require_once 'includes/db.php';

if ($current_user || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        redirect('admin/dashboard.php');
    } else {
        redirect('index.php');
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginInput = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($loginInput) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $user = null;
        $user_type = '';

        // 1. First check admins by email or username
        $admin = fetchOne("SELECT * FROM admins WHERE email = ? OR username = ?", "ss", [$loginInput, $loginInput]);
        if ($admin && password_verify($password, $admin['password'])) {
            $user = $admin;
            $user_type = 'admin';
        }

        if (!$user) {
            $customer = fetchOne("SELECT * FROM users WHERE email = ?", "s", [$loginInput]);
            if ($customer && password_verify($password, $customer['password'])) {
                $user = $customer;
                $user_type = 'customer';
            }
        }

        if ($user) {
            $_SESSION['role'] = $user_type;
            $_SESSION['username'] = $user['username'] ?? $user['full_name'] ?? $user['first_name'] ?? '';

            if ($user_type === 'admin') {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_email'] = $user['email'] ?? ($user['username'] ?? 'admin@example.com');
                unset($_SESSION['user_id']);
                setFlash('success', 'Welcome back, Admin!');
                redirect('admin/dashboard.php');
            } else {
                $_SESSION['user_id'] = $user['id'];
                unset($_SESSION['admin_id']);
                setFlash('success', 'Welcome back, ' . $user['first_name']);
                redirect('index.php');
            }
        } else {
            $error = 'Invalid email/username or password';
        }

    }
}

require_once 'includes/header.php';
?>

<div class="auth-page">
    <div class="auth-container">
        <div class="auth-image">
            <div>
                <h2>Welcome Back</h2>
                <p>Sign in to access your account, track orders, and enjoy personalized recommendations.</p>
                <div style="margin-top: 40px; font-size: 64px; opacity: 0.3;">
                                        <i class="fas fa-couch"></i>
                </div>
            </div>
        </div>
        
        <div class="auth-form">
            <h1>Sign In</h1>
            <p>Enter your credentials to access your account</p>
            
            <?php if ($error): ?>
            <div class="flash-message flash-error" style="position: static; margin-bottom: 20px; animation: none;">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" data-validate>
                <div class="form-group">
                    <label class="form-label">Email or Username</label>
                    <input type="text" name="email" class="form-control" placeholder="admin@example.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; color: var(--gray-600);">
                        <input type="checkbox" name="remember" style="width: 16px; height: 16px; accent-color: var(--accent);">
                        Remember me
                    </label>
                    <a href="#" style="font-size: 14px; color: var(--accent);">Forgot password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Sign In</button>
            </form>
            
            <p style="text-align: center; margin-top: 24px; color: var(--gray-600);">
                Don't have an account? <a href="register.php" style="color: var(--accent); font-weight: 600;">Create one</a>
            </p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>