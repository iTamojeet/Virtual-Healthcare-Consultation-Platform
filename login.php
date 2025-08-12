<?php
/**
 * ==============================================================
 * USER LOGIN PAGE
 * --------------------------------------------------------------
 * Handles:
 *   - Email & password verification
 *   - Session creation
 *   - Role-based redirection
 * 
 * Author: Tamojeet Pal
 * ==============================================================
 */

require_once 'config.php';

$inputEmail = $inputPassword = "";
$errorLogin = "";

// Process login form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $inputEmail    = trim($_POST["email"]);
    $inputPassword = trim($_POST["password"]);

    $query = "SELECT id, username, password, role FROM users WHERE email = ?";
    if ($stmt = $dbLink->prepare($query)) {
        $stmt->bind_param("s", $inputEmail);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows === 1) {
                $stmt->bind_result($userId, $userName, $hashedPass, $userRole);
                $stmt->fetch();
                if (password_verify($inputPassword, $hashedPass)) {
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"]       = $userId;
                    $_SESSION["username"] = $userName;
                    $_SESSION["role"]     = $userRole;
                    goToPage("index.php");
                } else {
                    $errorLogin = "Invalid credentials.";
                }
            } else {
                $errorLogin = "Invalid credentials.";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Online Doctor Consultation</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>User Login</h2>
    <?php if(!empty($errorLogin)) echo '<div class="error">'.$errorLogin.'</div>'; ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <input type="submit" value="Login">
        <p>Don't have an account? <a href="register.php">Sign up here</a></p>
    </form>
</div>
</body>
</html>
