<?php
/**
 * ==============================================================
 * LOGOUT SCRIPT
 * --------------------------------------------------------------
 * Clears all session data and returns user to login page
 * 
 * Author: Tamojeet Pal
 * ==============================================================
 */

session_start();

// Clear session variables
$_SESSION = [];

// Destroy session completely
session_destroy();

// Redirect back to login page
header("Location: login.php");
exit;
?>
