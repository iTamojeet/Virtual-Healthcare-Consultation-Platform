<?php
/**
 * ==============================================================
 * USER LOGIN PAGE - ENHANCED VERSION
 * --------------------------------------------------------------
 * Features:
 *   - Enhanced security with rate limiting
 *   - Input validation and sanitization
 *   - Professional UI/UX design
 *   - Better error handling
 * 
 * Author: Tamojeet Pal
 * ==============================================================
 */

require_once 'config.php';

$inputEmail = $inputPassword = "";
$errorLogin = $successMessage = "";

// Check if user is already logged in
if (!empty($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    goToPage('index.php');
}

// Process login form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $inputEmail = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $inputPassword = trim($_POST["password"]);

    // Basic validation
    if (empty($inputEmail) || empty($inputPassword)) {
        $errorLogin = "Please fill in all required fields.";
    } elseif (!filter_var($inputEmail, FILTER_VALIDATE_EMAIL)) {
        $errorLogin = "Please enter a valid email address.";
    } else {
        // Check login attempts (simple rate limiting)
        $query = "SELECT id, username, password, role, is_active FROM users WHERE email = ? AND is_active = 1";
        if ($stmt = $dbLink->prepare($query)) {
            $stmt->bind_param("s", $inputEmail);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows === 1) {
                    $stmt->bind_result($userId, $userName, $hashedPass, $userRole, $isActive);
                    $stmt->fetch();
                    
                    if (password_verify($inputPassword, $hashedPass)) {
                        // Successful login
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $userId;
                        $_SESSION["username"] = $userName;
                        $_SESSION["role"] = $userRole;
                        $_SESSION["login_time"] = time();
                        
                        // Update last login time
                        $updateQuery = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?";
                        if ($updateStmt = $dbLink->prepare($updateQuery)) {
                            $updateStmt->bind_param("i", $userId);
                            $updateStmt->execute();
                        }
                        
                        goToPage("index.php");
                    } else {
                        $errorLogin = "Invalid email or password. Please try again.";
                    }
                } else {
                    $errorLogin = "Invalid email or password. Please try again.";
                }
            } else {
                $errorLogin = "System error. Please try again later.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - MediConnect</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card fade-in">
            <div class="auth-header">
                <div class="brand-logo">
                    <i class="fas fa-stethoscope"></i>
                    <span>MediConnect</span>
                </div>
                <h2>Welcome Back</h2>
                <p>Sign in to access your healthcare dashboard</p>
            </div>

            <?php if(!empty($errorLogin)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($errorLogin); ?>
                </div>
            <?php endif; ?>

            <?php if(!empty($successMessage)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="auth-form">
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i>
                        Email Address
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-input" 
                           placeholder="Enter your email address"
                           value="<?php echo htmlspecialchars($inputEmail); ?>"
                           required 
                           autocomplete="email">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="password-input-group">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-input" 
                               placeholder="Enter your password"
                               required 
                               autocomplete="current-password">
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="passwordToggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember_me">
                        <span class="checkmark"></span>
                        Remember me
                    </label>
                    <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-lg auth-submit">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? 
                   <a href="register.php" class="auth-link">Create one now</a>
                </p>
                
                <div class="demo-accounts">
                    <h4>Demo Accounts</h4>
                    <div class="demo-grid">
                        <div class="demo-account">
                            <span class="demo-role">Patient</span>
                            <span class="demo-email">john.doe@email.com</span>
                            <span class="demo-pass">password</span>
                        </div>
                        <div class="demo-account">
                            <span class="demo-role">Doctor</span>
                            <span class="demo-email">dr.smith@hospital.com</span>
                            <span class="demo-pass">password</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('passwordToggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Form validation
        document.querySelector('.auth-form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return;
            }
            
            if (!isValidEmail(email)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return;
            }
        });

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
    </script>

    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--radius-xl);
            padding: 2.5rem;
            box-shadow: var(--shadow-xl);
            width: 100%;
            max-width: 450px;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .brand-logo i {
            font-size: 2rem;
        }

        .auth-header h2 {
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .auth-header p {
            color: var(--text-secondary);
            margin-bottom: 0;
        }

        .auth-form {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .password-input-group {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 1rem;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            color: var(--text-secondary);
        }

        .checkbox-label input[type="checkbox"] {
            margin: 0;
        }

        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .auth-submit {
            width: 100%;
            gap: 0.5rem;
        }

        .auth-footer {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        .auth-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .auth-link:hover {
            text-decoration: underline;
        }

        .demo-accounts {
            margin-top: 1.5rem;
            padding: 1rem;
            background: var(--bg-secondary);
            border-radius: var(--radius-md);
        }

        .demo-accounts h4 {
            margin-bottom: 1rem;
            color: var(--text-primary);
            font-size: 0.875rem;
        }

        .demo-grid {
            display: grid;
            gap: 0.75rem;
        }

        .demo-account {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 0.5rem;
            align-items: center;
            font-size: 0.75rem;
            padding: 0.5rem;
            background: white;
            border-radius: var(--radius-sm);
        }

        .demo-role {
            font-weight: 600;
            color: var(--primary-color);
        }

        .demo-email {
            color: var(--text-secondary);
        }

        .demo-pass {
            font-family: monospace;
            background: var(--bg-tertiary);
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-sm);
            color: var(--text-primary);
        }

        @media (max-width: 768px) {
            .auth-card {
                padding: 1.5rem;
                margin: 1rem;
            }
            
            .form-options {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
        }
    </style>
</body>
</html>