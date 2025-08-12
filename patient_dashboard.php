<?php
/**
 * ==============================================================
 * PATIENT DASHBOARD
 * --------------------------------------------------------------
 * Displays:
 *   - Upcoming and past appointments
 *   - Option to book a new appointment
 * 
 * Author: Tamojeet Pal
 * ==============================================================
 */

require_once 'config.php';

// Restrict access to patients only
if (empty($_SESSION['loggedin']) || $_SESSION['role'] !== 'patient') {
    goToPage('login.php');
}

$currentPatientId = $_SESSION['id'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Patient Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="navbar">
    <a href="#">Dashboard</a>
    <a href="logout.php" class="right">Logout</a>
</div>

<div class="container">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>

    <!-- Appointment List -->
    <h3>Your Appointments</h3>
    <?php
    $sqlAppointments = "
        SELECT a.id, u.username AS doctor_name, a.appointment_time, a.status
        FROM appointments a
        JOIN users u ON a.doctor_id = u.id
        WHERE a.patient_id = ?
        ORDER BY a.appointment_time DESC
    ";
    if ($stmt = $dbLink->prepare($sqlAppointments)) {
        $stmt->bind_param("i", $currentPatientId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li>Dr. " . htmlspecialchars($row['doctor_name']) . 
                     " on " . htmlspecialchars($row['appointment_time']) .
                     " - Status: " . htmlspecialchars($row['status']);
                if ($row['status'] === 'scheduled') {
                    echo " <a href='chat.php?id=" . $row['id'] . "' class='btn'>Join Chat</a>";
                }
                echo "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No appointments found.</p>";
        }
    }
    ?>

    <hr>
    <!-- Appointment Booking Form -->
    <h3>Book a New Appointment</h3>
    <form action="book_appointment.php" method="post">
        <label>Select Doctor:</label>
        <select name="doctor_id" required>
            <option value="">--Choose a Doctor--</option>
            <?php
            $sqlDoctors = "
                SELECT u.id, u.username, dp.specialty
                FROM users u
                JOIN doctors_profiles dp ON u.id = dp.user_id
                WHERE u.role = 'doctor' AND dp.approved = 1
            ";
            $resultDoctors = $dbLink->query($sqlDoctors);
            while ($doctor = $resultDoctors->fetch_assoc()) {
                echo "<option value='" . $doctor['id'] . "'>Dr. " .
                     htmlspecialchars($doctor['username']) . " (" .
                     htmlspecialchars($doctor['specialty']) . ")</option>";
            }
            ?>
        </select>

        <label>Appointment Time:</label>
        <input type="datetime-local" name="appointment_time" required>

        <input type="submit" value="Book Appointment">
    </form>
</div>
</body>
</html>
