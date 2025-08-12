<?php
/**
 * ==============================================================
 * USER REGISTRATION PAGE
 * --------------------------------------------------------------
 * Handles:
 *   - Patient/Doctor account creation
 *   - Doctor profile specialty addition
 * 
 * Author: Tamojeet Pal
 * ==============================================================
 */

require_once 'config.php';

// Initialize variables
$newUsername = $newEmail = $newPassword = $newRole = "";
$errorUsername = $errorEmail = $errorPassword = $errorRole = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $newUsername = trim($_POST["username"]);
    $newEmail    = trim($_POST["email"]);
    $newPassword = password_hash(trim($_POST["password"]), PASSWORD_DEFAULT);
    $newRole     = trim($_POST["role"]);

    // Insert new user into database
    $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
    if ($stmt = $dbLink->prepare($query)) {
        $stmt->bind_param("ssss", $newUsername, $newEmail, $newPassword, $newRole);
        if ($stmt->execute()) {
            // If doctor, also insert into doctors_profiles table
            if ($newRole === 'doctor') {
                $userId      = $stmt->insert_id;
                $specialty   = trim($_POST['specialty']);
                $queryDoctor = "INSERT INTO doctors_profiles (user_id, specialty) VALUES (?, ?)";
                if ($stmtDoctor = $dbLink->prepare($queryDoctor)) {
                    $stmtDoctor->bind_param("is", $userId, $specialty);
                    $stmtDoctor->execute();
                }
            }
            goToPage("login.php");
        } else {
            echo "Registration failed. Please try again later.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Online Doctor Consultation</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Create an Account</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Account Type</label>
        <select name="role" id="role_select" onchange="toggleSpecialty()" required>
            <option value="patient">Patient</option>
            <option value="doctor">Doctor</option>
        </select>

        <div id="specialty_field" style="display:none;">
            <label>Specialty</label>
            <input type="text" name="specialty">
        </div>

        <input type="submit" value="Register">
        <p>Already registered? <a href="login.php">Login here</a></p>
    </form>
</div>
<script>
function toggleSpecialty() {
    const role = document.getElementById('role_select').value;
    const specialtyField = document.getElementById('specialty_field');
    specialtyField.style.display = (role === 'doctor') ? 'block' : 'none';
}
</script>
</body>
</html>
