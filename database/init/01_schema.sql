CREATE DATABASE IF NOT EXISTS citas
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE citas;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'employee') NOT NULL DEFAULT 'employee',
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE patients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_type ENUM('CC', 'TI', 'CE', 'PA') NOT NULL DEFAULT 'CC',
    document_number VARCHAR(30) NOT NULL UNIQUE,
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    birth_date DATE NOT NULL,
    sex ENUM('F', 'M', 'O') NOT NULL,
    phone VARCHAR(30) NULL,
    email VARCHAR(150) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_patients_name (last_name, first_name)
) ENGINE=InnoDB;

CREATE TABLE doctors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    license_number VARCHAR(40) NOT NULL UNIQUE,
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    specialty VARCHAR(100) NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE rooms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE appointments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT UNSIGNED NOT NULL,
    doctor_id BIGINT UNSIGNED NOT NULL,
    room_id BIGINT UNSIGNED NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('scheduled', 'completed', 'cancelled') NOT NULL DEFAULT 'scheduled',
    notes VARCHAR(500) NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    cancelled_by BIGINT UNSIGNED NULL,
    cancelled_at DATETIME NULL,
    completed_by BIGINT UNSIGNED NULL,
    completed_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    scheduled_patient_id BIGINT UNSIGNED
        GENERATED ALWAYS AS (IF(status = 'scheduled', patient_id, NULL)) STORED,
    scheduled_doctor_id BIGINT UNSIGNED
        GENERATED ALWAYS AS (IF(status = 'scheduled', doctor_id, NULL)) STORED,
    scheduled_room_id BIGINT UNSIGNED
        GENERATED ALWAYS AS (IF(status = 'scheduled', room_id, NULL)) STORED,

    CONSTRAINT fk_appointments_patient FOREIGN KEY (patient_id) REFERENCES patients(id),
    CONSTRAINT fk_appointments_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id),
    CONSTRAINT fk_appointments_room FOREIGN KEY (room_id) REFERENCES rooms(id),
    CONSTRAINT fk_appointments_created_by FOREIGN KEY (created_by) REFERENCES users(id),
    CONSTRAINT fk_appointments_cancelled_by FOREIGN KEY (cancelled_by) REFERENCES users(id),
    CONSTRAINT fk_appointments_completed_by FOREIGN KEY (completed_by) REFERENCES users(id),

    CONSTRAINT uq_scheduled_patient
    UNIQUE (scheduled_patient_id, appointment_date, appointment_time),
    CONSTRAINT uq_scheduled_doctor
    UNIQUE (scheduled_doctor_id, appointment_date, appointment_time),
    CONSTRAINT uq_scheduled_room
    UNIQUE (scheduled_room_id, appointment_date, appointment_time),
    
    INDEX idx_appointments_patient (patient_id),
    INDEX idx_appointments_date_status (appointment_date, status)
) ENGINE=InnoDB;