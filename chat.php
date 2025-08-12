<?php
/**
 * ==============================================================
 * CONSULTATION CHAT PAGE
 * --------------------------------------------------------------
 * Allows real-time text communication between
 * patients and doctors for a scheduled appointment
 * 
 * Author: Tamojeet Pal
 * ==============================================================
 */

require_once 'config.php';

// Ensure user is logged in
if (empty($_SESSION['loggedin'])) {
    goToPage('login.php');
}

$appointmentId = $_GET['id'];
$currentUserId = $_SESSION['id'];

// TODO: Verify that this user has access to this appointment before showing chat

// Handle sending a message
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['message'])) {
    $messageText = trim($_POST['message']);
    $queryInsertMessage = "
        INSERT INTO chats (appointment_id, sender_id, message)
        VALUES (?, ?, ?)
    ";
    if ($stmt = $dbLink->prepare($queryInsertMessage)) {
        $stmt->bind_param("iis", $appointmentId, $currentUserId, $messageText);
        $stmt->execute();
        goToPage("chat.php?id=" . $appointmentId);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Consultation Chat</title>
    <link rel="stylesheet" href="style.css">
    <meta http-equiv="refresh" content="5"><!-- Refresh chat every 5 seconds -->
</head>
<body>
<div class="container">
    <h2>Chat</h2>
    <div class="chat-box">
        <?php
        $queryGetMessages = "
            SELECT c.message, u.username AS sender_name, c.timestamp
            FROM chats c
            JOIN users u ON c.sender_id = u.id
            WHERE c.appointment_id = ?
            ORDER BY c.timestamp ASC
        ";
        if ($stmt = $dbLink->prepare($queryGetMessages)) {
            $stmt->bind_param("i", $appointmentId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                echo "<div class='chat-message'><strong>" .
                     htmlspecialchars($row['sender_name']) . ":</strong> " .
                     htmlspecialchars($row['message']) . "</div>";
            }
        }
        ?>
    </div>
    <form action="chat.php?id=<?php echo $appointmentId; ?>" method="post">
        <input type="text" name="message" placeholder="Type your message..." required autocomplete="off">
        <input type="submit" value="Send">
    </form>
    <br>
    <a href="index.php" class="btn">Back to Dashboard</a>
</div>
</body>
</html>
