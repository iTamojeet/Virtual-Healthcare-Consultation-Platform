<?php
/**
 * ==============================================================
 * BOOK APPOINTMENT SCRIPT
 * --------------------------------------------------------------
 * Inserts a new appointment record for a patient
 * 
 * Author: Tamojeet Pal
 * ==============================================================
 */

require_once 'config.php';

// Ensure only logged-in patients can book
if (empty($_SESSION['loggedin']) || $_SESSION['role'] !== 'patient') {
    goToPage('login.php');
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $patientId        = $_SESSION['id'];
    $selectedDoctorId = $_POST['doctor_id'];
    $appointmentTime  = $_POST['appointment_time'];

    // TODO: Add subscription check logic here

    $queryInsert = "
        INSERT INTO appointments (patient_id, doctor_id, appointment_time)
        VALUES (?, ?, ?)
    ";
    if ($stmt = $dbLink->prepare($queryInsert)) {
        $stmt->bind_param("iis", $patientId, $selectedDoctorId, $appointmentTime);
        if ($stmt->execute()) {
            goToPage('patient_dashboard.php');
        } else {
            echo "Error: Unable to book appointment.";
        }
    }
}
?>
