<?php
/**
 * ==============================================================
 * CONFIGURATION & DATABASE CONNECTION FILE
 * --------------------------------------------------------------
 * Purpose:
 *   - Initialize PHP session
 *   - Connect to MySQL database
 *   - Provide utility functions for redirects
 * 
 * Author: Tamojeet Pal
 * Date: 2025-08-12
 * ==============================================================
 */

session_start(); // Start session for authentication management

// ---------------------
// Database Credentials
// ---------------------
define('DB_HOST', 'localhost');       // MySQL host
define('DB_USER', 'root');            // MySQL username
define('DB_PASS', '');                // MySQL password
define('DB_NAME', 'doctor_consultation_system'); // Database name

// ---------------------
// Database Connection
// ---------------------
$dbLink = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check if connection was successful
if ($dbLink->connect_error) {
    die("Database Connection Failed: " . $dbLink->connect_error);
}

// ---------------------
// Utility Functions
// ---------------------

/**
 * Redirect to a given URL and exit script execution
 *
 * @param string $url Destination URL
 */
function goToPage($url) {
    header("Location: " . $url);
    exit;
}
?>
