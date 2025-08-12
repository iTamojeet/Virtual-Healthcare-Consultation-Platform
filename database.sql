-- ===============================================
-- ONLINE DOCTOR CONSULTATION DATABASE SCHEMA
-- ===============================================
-- Author: Tamojeet Pal
-- Date: 2025-08-12
-- ===============================================

CREATE DATABASE IF NOT EXISTS doctor_consultation;
USE doctor_consultation;

-- Users table (patients, doctors, admins)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('patient', 'doctor', 'admin') NOT NULL DEFAULT 'patient',
    phone VARCHAR(20),
    address TEXT,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    profile_image VARCHAR(255),
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- Doctor profiles table
CREATE TABLE doctors_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    specialty VARCHAR(100) NOT NULL,
    license_number VARCHAR(50),
    experience_years INT DEFAULT 0,
    qualification TEXT,
    bio TEXT,
    consultation_fee DECIMAL(10,2) DEFAULT 500.00,
    approved BOOLEAN DEFAULT FALSE,
    rating DECIMAL(3,2) DEFAULT 0.00,
    total_consultations INT DEFAULT 0,
    available_from TIME DEFAULT '09:00:00',
    available_to TIME DEFAULT '17:00:00',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_specialty (specialty),
    INDEX idx_approved (approved)
);

-- Patient profiles table
CREATE TABLE patients_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    blood_group VARCHAR(5),
    emergency_contact VARCHAR(20),
    medical_history TEXT,
    allergies TEXT,
    current_medications TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Appointments table
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_time DATETIME NOT NULL,
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
    consultation_type ENUM('text_chat', 'video_call', 'phone_call') DEFAULT 'text_chat',
    consultation_fee DECIMAL(10,2) DEFAULT 500.00,
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
    notes TEXT,
    prescription TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_appointment_time (appointment_time),
    INDEX idx_status (status),
    INDEX idx_patient_doctor (patient_id, doctor_id)
);

-- Chat messages table
CREATE TABLE chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    message_type ENUM('text', 'image', 'file') DEFAULT 'text',
    file_path VARCHAR(255),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_appointment_timestamp (appointment_id, timestamp)
);

-- Subscriptions table (for premium features)
CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_type ENUM('basic', 'premium', 'family') DEFAULT 'basic',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    amount_paid DECIMAL(10,2),
    payment_method VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status)
);

-- Reviews and ratings table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (appointment_id, patient_id)
);

-- System notifications table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('appointment', 'payment', 'system', 'reminder') DEFAULT 'system',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read)
);

-- Insert sample data
INSERT INTO users (username, email, password, role, phone) VALUES
('Dr. Smith', 'dr.smith@hospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor', '+91-9876543210'),
('Dr. Johnson', 'dr.johnson@clinic.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor', '+91-9876543211'),
('John Doe', 'john.doe@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', '+91-9876543212'),
('Jane Smith', 'jane.smith@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', '+91-9876543213');

INSERT INTO doctors_profiles (user_id, specialty, license_number, experience_years, qualification, bio, consultation_fee, approved) VALUES
(1, 'Cardiology', 'MED12345', 15, 'MBBS, MD Cardiology', 'Experienced cardiologist with 15+ years in treating heart conditions.', 800.00, TRUE),
(2, 'General Medicine', 'MED12346', 10, 'MBBS, MD General Medicine', 'General practitioner specializing in family medicine and preventive care.', 600.00, TRUE);

INSERT INTO patients_profiles (user_id, date_of_birth, gender, blood_group) VALUES
(3, '1985-06-15', 'male', 'A+'),
(4, '1990-03-22', 'female', 'B+');