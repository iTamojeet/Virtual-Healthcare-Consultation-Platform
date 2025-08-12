<?php
/**
 * ==============================================================
 * DOCTOR DASHBOARD
 * --------------------------------------------------------------
 * Displays:
 *   - Upcoming scheduled appointments
 *   - Option to start a chat with patients
 * 
 * Author: Tamojeet Pal
 * ==============================================================
 */

require_once 'config.php';

// Restrict access to doctors only
if (empty($_SESSION['loggedin']) || $_SESSION['role'] !== 'doctor') {
    goToPage('login.php');
}

$currentDoctorId = $_SESSION['id'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="navbar">
    <a href="#">Dashboard</a>
    <a href="logout.php" class="right">Logout</a>
</div>

<div class="container">
    <h2>Welcome, Dr. <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>

    <!-- Upcoming Appointments -->
    <h3>Your Upcoming Appointments</h3>
    <?php
    $sqlUpcoming = "
        SELECT a.id, u.username AS patient_name, a.appointment_time, a.status
        FROM appointments a
        JOIN users u ON a.patient_id = u.id
        WHERE a.doctor_id = ? AND a.status = 'scheduled'
        ORDER BY a.appointment_time ASC
    ";
    if ($stmt = $dbLink->prepare($sqlUpcoming)) {
        $stmt->bind_param("i", $currentDoctorId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($row['patient_name']) .
                     " on " . htmlspecialchars($row['appointment_time']) .
                     " <a href='chat.php?id=" . $row['id'] . "' class='btn'>Start Chat</a></li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No upcoming appointments.</p>";
        }
    }
    ?>
</div>
</body>
</html>
