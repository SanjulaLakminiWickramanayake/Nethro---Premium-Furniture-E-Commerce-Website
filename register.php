<?php
$pageTitle = 'Register';
require_once 'includes/db.php';

if ($current_user) {
    redirect('index.php');
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        // Check if email exists
        $existing = fetchOne("SELECT id FROM users WHERE email = ?", "s", [$email]);
        if ($existing) {
            $error = 'Email address already registered';
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $sql = "INSERT INTO users (first_name, last_name, email, phone, address, city, password) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = executeQuery($sql, "sssssss", [$firstName, $lastName, $email, $phone, $address, $city, $hashedPassword]);
            
            if ($stmt->affected_rows > 0) {
                $userId = $stmt->insert_id;
                $_SESSION['user_id'] = $userId;
                
                // Transfer guest cart to user
                if (isset($_SESSION['cart_session'])) {
                    $sessionId = $_SESSION['cart_session'];
                    executeQuery("UPDATE cart SET user_id = ?, session_id = NULL WHERE session_id = ?", "is", [$userId, $sessionId]);
                }
                
                setFlash('success', 'Welcome to Nethro Furniture, ' . $firstName . '!');
                redirect('index.php');
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="auth-page">
    <div class="auth-container" style="max-width: 1100px;">
        <div class="auth-image">
            <div>
                <h2>Join Our Family</h2>
                <p>Create an account to enjoy exclusive benefits, faster checkout, and personalized recommendations.</p>
                <div style="margin-top: 40px; font-size: 64px; opacity: 0.3;">
                    <i class="fas fa-user-plus"></i>
                </div>
            </div>
        </div>
        
        <div class="auth-form" style="padding: 40px;">
            <h1>Create Account</h1>
            <p>Fill in your details to get started</p>
            
            <?php if ($error): ?>
            <div class="flash-message flash-error" style="position: static; margin-bottom: 20px; animation: none;">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" data-validate>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">First Name *</label>
                        <input type="text" name="first_name" class="form-control" placeholder="John" required value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name *</label>
                        <input type="text" name="last_name" class="form-control" placeholder="Doe" required value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control" placeholder="you@example.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-control" placeholder="+1 (555) 123-4567" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" placeholder="123 Main Street" value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" placeholder="New York" value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Create Account</button>
            </form>
            
            <p style="text-align: center; margin-top: 24px; color: var(--gray-600);">
                Already have an account? <a href="login.php" style="color: var(--accent); font-weight: 600;">Sign in</a>
            </p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>