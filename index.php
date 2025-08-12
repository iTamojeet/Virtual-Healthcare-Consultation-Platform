<?php
/**
 * ==============================================================
 * INDEX / ROLE ROUTER
 * --------------------------------------------------------------
 * Redirects logged-in users to their respective dashboards
 * 
 * Author: Tamojeet Pal
 * ==============================================================
 */
require_once 'config.php';

// Ensure the user is authenticated
if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    goToPage('login.php');
}

// Redirect based on user role
switch ($_SESSION['role']) {
    case 'patient':
        goToPage('patient_dashboard.php');
        break;
    case 'doctor':
        goToPage('doctor_dashboard.php');
        break;
    default: // admin or other roles
        echo "Welcome, Administrator!";
        echo '<br><a href="logout.php">Logout</a>';
}
?>
