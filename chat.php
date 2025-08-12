<?php
/**
 * ==============================================================
 * ENHANCED CONSULTATION CHAT PAGE
 * --------------------------------------------------------------
 * Features:
 *   - Real-time messaging with auto-refresh
 *   - File sharing capability
 *   - Better security and access control
 *   - Professional chat UI
 *   - Message status indicators
 * 
 * Author: Tamojeet Pal
 * ==============================================================
 */

require_once 'config.php';

// Ensure user is logged in
requireLogin();

$appointmentId = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
$currentUserId = $_SESSION['id'];
$currentUserRole = $_SESSION['role'];

if (!$appointmentId) {
    goToPage('index.php');
}

// Verify user has access to this appointment
$accessQuery = "SELECT a.*, 
                       p.username as patient_name, 
                       d.username as doctor_name,
                       dp.specialty as doctor_specialty
                FROM appointments a
                JOIN users p ON a.patient_id = p.id
                JOIN users d ON a.doctor_id = d.id
                LEFT JOIN doctors_profiles dp ON d.id = dp.user_id
                WHERE a.id = ? AND (a.patient_id = ? OR a.doctor_id = ?)";

$appointment = null;
if ($stmt = $dbLink->prepare($accessQuery)) {
    $stmt->bind_param("iii", $appointmentId, $currentUserId, $currentUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    $appointment = $result->fetch_assoc();
}

if (!$appointment) {
    goToPage('index.php');
}

// Handle sending a message
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    if ($_POST['action'] === 'send_message' && !empty($_POST['message'])) {
        if (verifyCsrfToken($_POST['csrf_token'])) {
            $messageText = trim($_POST['message']);
            $messageType = 'text';
            $filePath = null;
            
            // Handle file upload if present
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = uploadFile($_FILES['file'], UPLOAD_PATH . 'chat/', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);
                if ($uploadResult['success']) {
                    $filePath = $uploadResult['path'];
                    $messageType = 'file';
                    if (empty($messageText)) {
                        $messageText = 'Shared a file: ' . $_FILES['file']['name'];
                    }
                }
            }
            
            $queryInsertMessage = "INSERT INTO chats (appointment_id, sender_id, message, message_type, file_path) VALUES (?, ?, ?, ?, ?)";
            if ($stmt = $dbLink->prepare($queryInsertMessage)) {
                $stmt->bind_param("iisss", $appointmentId, $currentUserId, $messageText, $messageType, $filePath);
                if ($stmt->execute()) {
                    // Update appointment status if first message
                    if ($appointment['status']