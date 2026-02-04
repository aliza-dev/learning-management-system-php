-- Complete Database Schema for Department and Faculty Management System
-- Run this SQL file to set up all required tables

CREATE DATABASE IF NOT EXISTS university_db;
USE university_db;

-- Admin Table
CREATE TABLE IF NOT EXISTS admin (
    id INT(11) NOT NULL AUTO_INCREMENT,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Departments Table
CREATE TABLE IF NOT EXISTS departments (
    depart_id INT(11) NOT NULL AUTO_INCREMENT,
    depart_code VARCHAR(10) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (depart_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Faculty Table with user_rights and focal person support
CREATE TABLE IF NOT EXISTS faculty (
    id INT(11) NOT NULL AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    department_id INT(11) NOT NULL,
    hire_date DATE,
    bio TEXT,
    password VARCHAR(255) NOT NULL,
    user_rights VARCHAR(50) DEFAULT 'normal' COMMENT 'normal, focal_person, admin',
    is_focal_person TINYINT(1) DEFAULT 0 COMMENT '1 if focal person, 0 otherwise',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (department_id) REFERENCES departments(depart_id) ON DELETE CASCADE,
    INDEX idx_department (department_id),
    INDEX idx_focal_person (is_focal_person)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- News Table
CREATE TABLE IF NOT EXISTS news (
    id INT(11) NOT NULL AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    department_id INT(11),
    posted_by INT(11) NOT NULL,
    posted_by_type VARCHAR(20) DEFAULT 'faculty',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (department_id) REFERENCES departments(depart_id) ON DELETE SET NULL,
    FOREIGN KEY (posted_by) REFERENCES faculty(id) ON DELETE CASCADE,
    INDEX idx_department (department_id),
    INDEX idx_posted_by (posted_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notice Board Table
CREATE TABLE IF NOT EXISTS notices (
    id INT(11) NOT NULL AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    notice_date DATE NOT NULL,
    department_id INT(11),
    posted_by INT(11) NOT NULL,
    posted_by_type VARCHAR(20) DEFAULT 'faculty',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (department_id) REFERENCES departments(depart_id) ON DELETE SET NULL,
    FOREIGN KEY (posted_by) REFERENCES faculty(id) ON DELETE CASCADE,
    INDEX idx_department (department_id),
    INDEX idx_posted_by (posted_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Events Table
CREATE TABLE IF NOT EXISTS events (
    id INT(11) NOT NULL AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME,
    location VARCHAR(200),
    department_id INT(11),
    posted_by INT(11) NOT NULL,
    posted_by_type VARCHAR(20) DEFAULT 'faculty',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (department_id) REFERENCES departments(depart_id) ON DELETE SET NULL,
    FOREIGN KEY (posted_by) REFERENCES faculty(id) ON DELETE CASCADE,
    INDEX idx_department (department_id),
    INDEX idx_posted_by (posted_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    id INT(11) NOT NULL AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    target_audience VARCHAR(50) DEFAULT 'all' COMMENT 'all, students, faculty, department',
    department_id INT(11),
    posted_by INT(11) NOT NULL,
    posted_by_type VARCHAR(20) DEFAULT 'faculty',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (department_id) REFERENCES departments(depart_id) ON DELETE SET NULL,
    FOREIGN KEY (posted_by) REFERENCES faculty(id) ON DELETE CASCADE,
    INDEX idx_department (department_id),
    INDEX idx_posted_by (posted_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Students Table (if not exists)
CREATE TABLE IF NOT EXISTS students (
    id INT(11) NOT NULL AUTO_INCREMENT,
    student_id VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    major VARCHAR(100),
    year VARCHAR(20),
    gpa DECIMAL(3,2),
    enrollment_date DATE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Courses Table (if not exists)
CREATE TABLE IF NOT EXISTS courses (
    id INT(11) NOT NULL AUTO_INCREMENT,
    code VARCHAR(20) NOT NULL,
    name VARCHAR(200) NOT NULL,
    credits INT(11),
    schedule VARCHAR(100),
    room VARCHAR(50),
    instructor_id INT(11),
    semester VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (instructor_id) REFERENCES faculty(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS rooms (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(200),
    capacity INT(11),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS room_bookings (
    id INT(11) NOT NULL AUTO_INCREMENT,
    faculty_id INT(11) NOT NULL,
    department_id INT(11),
    room_id INT(11) NOT NULL,
    booking_date DATE NOT NULL,
    time_slot VARCHAR(20) NOT NULL,
    event_title VARCHAR(255) NOT NULL,
    num_persons INT(11) NOT NULL,
    status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_room_bookings_faculty FOREIGN KEY (faculty_id) REFERENCES faculty(id) ON DELETE CASCADE,
    CONSTRAINT fk_room_bookings_department FOREIGN KEY (department_id) REFERENCES departments(depart_id) ON DELETE SET NULL,
    CONSTRAINT fk_room_bookings_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    UNIQUE KEY uq_room_booking_unique (room_id, booking_date, time_slot)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS room_booking_pdfs (
    id INT(11) NOT NULL AUTO_INCREMENT,
    booking_id INT(11) NOT NULL,
    pdf_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_room_booking_pdfs_booking FOREIGN KEY (booking_id) REFERENCES room_bookings(id) ON DELETE CASCADE,
    UNIQUE KEY uq_room_booking_pdf_booking (booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (password: admin123)
INSERT INTO admin (fullname, email, password) 
VALUES ('System Administrator', 'admin@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE email=email;

-- Trigger to ensure only one focal person per department (UPDATE)
DELIMITER $$

DROP TRIGGER IF EXISTS ensure_one_focal_person_update;
CREATE TRIGGER ensure_one_focal_person_update
BEFORE UPDATE ON faculty
FOR EACH ROW
BEGIN
    IF NEW.is_focal_person = 1 AND OLD.is_focal_person = 0 THEN
        -- Remove focal person status from other faculty in the same department
        UPDATE faculty 
        SET is_focal_person = 0, user_rights = 'normal'
        WHERE department_id = NEW.department_id 
        AND id != NEW.id 
        AND is_focal_person = 1;
        
        -- Set user_rights for the new focal person
        SET NEW.user_rights = 'focal_person';
    END IF;
    
    IF NEW.is_focal_person = 0 AND OLD.is_focal_person = 1 THEN
        -- Reset user_rights when removing focal person status
        SET NEW.user_rights = 'normal';
    END IF;
END$$

-- Trigger to ensure only one focal person per department (INSERT)
DROP TRIGGER IF EXISTS ensure_one_focal_person_insert;
CREATE TRIGGER ensure_one_focal_person_insert
BEFORE INSERT ON faculty
FOR EACH ROW
BEGIN
    IF NEW.is_focal_person = 1 THEN
        -- Remove focal person status from other faculty in the same department
        UPDATE faculty 
        SET is_focal_person = 0, user_rights = 'normal'
        WHERE department_id = NEW.department_id 
        AND is_focal_person = 1;
        
        -- Set user_rights for the new focal person
        SET NEW.user_rights = 'focal_person';
    ELSE
        SET NEW.user_rights = 'normal';
    END IF;
END$$

DELIMITER ;

