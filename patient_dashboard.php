<?php
/**
 * ==============================================================
 * ENHANCED PATIENT DASHBOARD
 * --------------------------------------------------------------
 * Features:
 *   - Modern card-based layout
 *   - Appointment statistics
 *   - Doctor search and filtering
 *   - Better appointment management
 *   - Responsive design
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

// Get patient statistics
$statsQuery = "SELECT 
    COUNT(*) as total_appointments,
    COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as upcoming_appointments,
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_appointments,
    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_appointments
    FROM appointments WHERE patient_id = ?";
$stats = ['total_appointments' => 0, 'upcoming_appointments' => 0, 'completed_appointments' => 0, 'cancelled_appointments' => 0];
if ($stmt = $dbLink->prepare($statsQuery)) {
    $stmt->bind_param("i", $currentPatientId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stats = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - MediConnect</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-content">
            <a href="index.php" class="navbar-brand">
                <i class="fas fa-stethoscope"></i>
                MediConnect
            </a>
            <ul class="navbar-nav">
                <li><a href="index.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="my_appointments.php"><i class="fas fa-calendar-alt"></i> Appointments</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container fade-in">
        <!-- Welcome Header -->
        <div class="dashboard-header">
            <div class="welcome-section">
                <h1>Welcome back, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
                <p>Manage your healthcare appointments and consultations</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="openBookingModal()">
                    <i class="fas fa-plus"></i>
                    Book New Appointment
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_appointments']; ?></h3>
                    <p>Total Appointments</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon upcoming">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['upcoming_appointments']; ?></h3>
                    <p>Upcoming</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon completed">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['completed_appointments']; ?></h3>
                    <p>Completed</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon cancelled">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['cancelled_appointments']; ?></h3>
                    <p>Cancelled</p>
                </div>
            </div>
        </div>

        <!-- Recent Appointments -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2>Recent Appointments</h2>
                <a href="my_appointments.php" class="btn btn-secondary btn-sm">View All</a>
            </div>
            
            <div class="appointments-grid">
                <?php
                $sqlRecentAppointments = "
                    SELECT a.id, u.username AS doctor_name, dp.specialty, a.appointment_time, a.status, a.consultation_type
                    FROM appointments a
                    JOIN users u ON a.doctor_id = u.id
                    LEFT JOIN doctors_profiles dp ON u.id = dp.user_id
                    WHERE a.patient_id = ?
                    ORDER BY a.appointment_time DESC
                    LIMIT 6
                ";
                if ($stmt = $dbLink->prepare($sqlRecentAppointments)) {
                    $stmt->bind_param("i", $currentPatientId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $statusClass = strtolower($row['status']);
                            $statusIcon = [
                                'scheduled' => 'fas fa-clock',
                                'in_progress' => 'fas fa-spinner',
                                'completed' => 'fas fa-check-circle',
                                'cancelled' => 'fas fa-times-circle'
                            ][$row['status']] ?? 'fas fa-question-circle';
                            
                            echo '<div class="appointment-card">';
                            echo '<div class="appointment-header">';
                            echo '<div class="doctor-info">';
                            echo '<div class="doctor-avatar">' . strtoupper(substr($row['doctor_name'], 0, 2)) . '</div>';
                            echo '<div class="doctor-details">';
                            echo '<h4>Dr. ' . htmlspecialchars($row['doctor_name']) . '</h4>';
                            echo '<p>' . htmlspecialchars($row['specialty'] ?? 'General Medicine') . '</p>';
                            echo '</div>';
                            echo '</div>';
                            echo '<span class="badge badge-' . $statusClass . '">';
                            echo '<i class="' . $statusIcon . '"></i> ' . ucfirst($row['status']);
                            echo '</span>';
                            echo '</div>';
                            echo '<div class="appointment-details">';
                            echo '<div class="appointment-time">';
                            echo '<i class="fas fa-calendar-alt"></i>';
                            echo date('M d, Y', strtotime($row['appointment_time']));
                            echo '<span>' . date('h:i A', strtotime($row['appointment_time'])) . '</span>';
                            echo '</div>';
                            echo '<div class="consultation-type">';
                            echo '<i class="fas fa-' . ($row['consultation_type'] == 'video_call' ? 'video' : 'comments') . '"></i>';
                            echo ucfirst(str_replace('_', ' ', $row['consultation_type']));
                            echo '</div>';
                            echo '</div>';
                            if ($row['status'] === 'scheduled') {
                                echo '<div class="appointment-actions">';
                                echo '<a href="chat.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm">';
                                echo '<i class="fas fa-comments"></i> Join Chat';
                                echo '</a>';
                                echo '</div>';
                            }
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="no-appointments">';
                        echo '<i class="fas fa-calendar-plus"></i>';
                        echo '<h3>No appointments yet</h3>';
                        echo '<p>Book your first appointment to get started with professional healthcare consultations.</p>';
                        echo '<button class="btn btn-primary" onclick="openBookingModal()">Book Appointment</button>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>

        <!-- Available Doctors Section -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2>Available Doctors</h2>
                <div class="doctor-filters">
                    <select id="specialtyFilter" class="form-select" onchange="filterDoctors()">
                        <option value="">All Specialties</option>
                        <option value="Cardiology">Cardiology</option>
                        <option value="General Medicine">General Medicine</option>
                        <option value="Pediatrics">Pediatrics</option>
                        <option value="Dermatology">Dermatology</option>
                        <option value="Orthopedics">Orthopedics</option>
                    </select>
                </div>
            </div>
            
            <div class="doctors-grid" id="doctorsGrid">
                <?php
                $sqlDoctors = "
                    SELECT u.id, u.username, dp.specialty, dp.experience_years, dp.consultation_fee, dp.rating, dp.bio
                    FROM users u
                    JOIN doctors_profiles dp ON u.id = dp.user_id
                    WHERE u.role = 'doctor' AND dp.approved = 1 AND u.is_active = 1
                    ORDER BY dp.rating DESC, dp.experience_years DESC
                    LIMIT 8
                ";
                $result = $dbLink->query($sqlDoctors);
                while ($doctor = $result->fetch_assoc()) {
                    echo '<div class="doctor-card" data-specialty="' . htmlspecialchars($doctor['specialty']) . '">';
                    echo '<div class="doctor-avatar-large">' . strtoupper(substr($doctor['username'], 0, 2)) . '</div>';
                    echo '<div class="doctor-info">';
                    echo '<h4>Dr. ' . htmlspecialchars($doctor['username']) . '</h4>';
                    echo '<p class="doctor-specialty">' . htmlspecialchars($doctor['specialty']) . '</p>';
                    echo '<div class="doctor-rating">';
                    $rating = $doctor['rating'];
                    for ($i = 1; $i <= 5; $i++) {
                        echo '<i class="fas fa-star' . ($i <= $rating ? ' star' : '') . '"></i>';
                    }
                    echo '<span>' . number_format($rating, 1) . '</span>';
                    echo '</div>';
                    echo '<div class="doctor-meta">';
                    echo '<span><i class="fas fa-graduation-cap"></i> ' . $doctor['experience_years'] . ' years exp</span>';
                    echo '<span><i class="fas fa-rupee-sign"></i> ₹' . number_format($doctor['consultation_fee']) . '</span>';
                    echo '</div>';
                    if ($doctor['bio']) {
                        echo '<p class="doctor-bio">' . htmlspecialchars(substr($doctor['bio'], 0, 100)) . '...</p>';
                    }
                    echo '<button class="btn btn-primary btn-sm book-appointment-btn" onclick="selectDoctor(' . $doctor['id'] . ', \'' . htmlspecialchars($doctor['username']) . '\')">';
                    echo '<i class="fas fa-calendar-plus"></i> Book Appointment';
                    echo '</button>';
                    echo '</div>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Booking Modal -->
    <div id="bookingModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Book New Appointment</h3>
                <span class="close" onclick="closeBookingModal()">&times;</span>
            </div>
            <form action="book_appointment.php" method="post" class="booking-form">
                <div class="form-group">
                    <label class="form-label">Select Doctor</label>
                    <select name="doctor_id" id="doctorSelect" class="form-select" required>
                        <option value="">Choose a doctor</option>
                        <?php
                        $sqlAllDoctors = "
                            SELECT u.id, u.username, dp.specialty, dp.consultation_fee
                            FROM users u
                            JOIN doctors_profiles dp ON u.id = dp.user_id
                            WHERE u.role = 'doctor' AND dp.approved = 1 AND u.is_active = 1
                            ORDER BY u.username ASC
                        ";
                        $result = $dbLink->query($sqlAllDoctors);
                        while ($doctor = $result->fetch_assoc()) {
                            echo '<option value="' . $doctor['id'] . '" data-fee="' . $doctor['consultation_fee'] . '">';
                            echo 'Dr. ' . htmlspecialchars($doctor['username']) . ' (' . htmlspecialchars($doctor['specialty']) . ') - ₹' . number_format($doctor['consultation_fee']);
                            echo '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Appointment Date & Time</label>
                    <input type="datetime-local" name="appointment_time" class="form-input" required min="<?php echo date('Y-m-d\TH:i', strtotime('+1 hour')); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Consultation Type</label>
                    <select name="consultation_type" class="form-select" required>
                        <option value="text_chat">Text Chat</option>
                        <option value="video_call">Video Call</option>
                        <option value="phone_call">Phone Call</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Brief Description (Optional)</label>
                    <textarea name="notes" class="form-input" rows="3" placeholder="Describe your symptoms or concerns..."></textarea>
                </div>

                <div class="consultation-fee">
                    <span>Consultation Fee: ₹<span id="selectedFee">500</span></span>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeBookingModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Book Appointment</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functionality
        function openBookingModal() {
            document.getElementById('bookingModal').style.display = 'block';
        }

        function closeBookingModal() {
            document.getElementById('bookingModal').style.display = 'none';
        }

        function selectDoctor(doctorId, doctorName) {
            document.getElementById('doctorSelect').value = doctorId;
            updateConsultationFee();
            openBookingModal();
        }

        // Update consultation fee when doctor is selected
        document.getElementById('doctorSelect').addEventListener('change', function() {
            updateConsultationFee();
        });

        function updateConsultationFee() {
            const select = document.getElementById('doctorSelect');
            const selectedOption = select.options[select.selectedIndex];
            const fee = selectedOption.getAttribute('data-fee') || '500';
            document.getElementById('selectedFee').textContent = parseInt(fee).toLocaleString();
        }

        // Filter doctors by specialty
        function filterDoctors() {
            const filter = document.getElementById('specialtyFilter').value;
            const doctorCards = document.querySelectorAll('.doctor-card');
            
            doctorCards.forEach(card => {
                const specialty = card.getAttribute('data-specialty');
                if (filter === '' || specialty === filter) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('bookingModal');
            if (event.target === modal) {
                closeBookingModal();
            }
        }

        // Set minimum datetime to current time + 1 hour
        document.addEventListener('DOMContentLoaded', function() {
            const now = new Date();
            now.setHours(now.getHours() + 1);
            const minDateTime = now.toISOString().slice(0, 16);
            document.querySelector('input[name="appointment_time"]').setAttribute('min', minDateTime);
        });
    </script>

    <style>
        /* Dashboard specific styles */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 2rem;
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }

        .welcome-section h1 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .welcome-section p {
            color: var(--text-secondary);
            margin: 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.total { background: linear-gradient(135deg, var(--primary-color), var(--primary-light)); }
        .stat-icon.upcoming { background: linear-gradient(135deg, var(--warning-color), #f59e0b); }
        .stat-icon.completed { background: linear-gradient(135deg, var(--success-color), #22c55e); }
        .stat-icon.cancelled { background: linear-gradient(135deg, var(--danger-color), #f87171); }

        .stat-content h3 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0 0 0.25rem 0;
            color: var(--text-primary);
        }

        .stat-content p {
            margin: 0;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .dashboard-section {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-header h2 {
            color: var(--primary-color);
            margin: 0;
        }

        .appointments-grid {
            display: grid;
            gap: 1rem;
        }

        .appointment-card {
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 1rem;
            transition: all 0.3s ease;
        }

        .appointment-card:hover {
            box-shadow: var(--shadow-md);
            border-color: var(--primary-color);
        }

        .appointment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .doctor-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .doctor-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .doctor-details h4 {
            margin: 0 0 0.25rem 0;
            color: var(--text-primary);
        }

        .doctor-details p {
            margin: 0;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .appointment-details {
            display: flex;
            gap: 2rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .appointment-time,
        .consultation-type {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .appointment-time span {
            font-weight: 600;
            color: var(--text-primary);
        }

        .appointment-actions {
            text-align: right;
        }

        .no-appointments {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-secondary);
        }

        .no-appointments i {
            font-size: 3rem;
            color: var(--text-light);
            margin-bottom: 1rem;
        }

        .no-appointments h3 {
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .doctors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .doctor-card {
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            background: white;
        }

        .doctor-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
        }

        .doctor-avatar-large {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: 700;
            margin: 0 auto 1rem;
        }

        .doctor-specialty {
            color: var(--text-secondary);
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .doctor-rating {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            margin-bottom: 1rem;
        }

        .doctor-rating .star {
            color: var(--accent-color);
        }

        .doctor-rating span {
            margin-left: 0.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .doctor-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .doctor-bio {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
            text-align: left;
        }

        .doctor-filters {
            display: flex;
            gap: 1rem;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: var(--radius-lg);
            width: 90%;
            max-width: 500px;
            box-shadow: var(--shadow-xl);
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            color: var(--primary-color);
        }

        .close {
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            color: var(--text-light);
            transition: color 0.3s ease;
        }

        .close:hover {
            color: var(--text-primary);
        }

        .booking-form {
            padding: 1.5rem;
        }

        .consultation-fee {
            background: var(--bg-secondary);
            padding: 1rem;
            border-radius: var(--radius-md);
            text-align: center;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .appointment-details {
                flex-direction: column;
                gap: 0.5rem;
            }

            .doctors-grid {
                grid-template-columns: 1fr;
            }

            .doctor-meta {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }

            .modal-content {
                margin: 10% auto;
                width: 95%;
            }
        }
    </style>
</body>
</html>