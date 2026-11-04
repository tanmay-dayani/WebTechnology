-- =======================================================
-- StudentHub Portal Database Schema
-- Database Name: studenthub_db
-- Integrated for Practicals 1 to 15
-- =======================================================

CREATE DATABASE IF NOT EXISTS `studenthub_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `studenthub_db`;

-- Drop existing tables to allow clean re-import
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- -------------------------------------------------------
-- Table 1: Users (Authentication & Role Management)
-- Practical 8, 9, 10
-- -------------------------------------------------------
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'student') NOT NULL DEFAULT 'student',
  `remember_token` VARCHAR(64) DEFAULT NULL,
  `last_login` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- Table 2: Students (Student Records for CRUD)
-- Practical 8, 11
-- -------------------------------------------------------
CREATE TABLE `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `student_id_code` VARCHAR(20) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(15) NOT NULL,
  `course` VARCHAR(50) NOT NULL,
  `year` VARCHAR(10) NOT NULL,
  `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
  `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- Table 3: Events (Events Management & Uploads)
-- Practical 8, 12, 13
-- -------------------------------------------------------
CREATE TABLE `events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `description` TEXT NOT NULL,
  `event_date` DATE NOT NULL,
  `location` VARCHAR(100) NOT NULL,
  `poster_image` VARCHAR(255) DEFAULT 'assets/images/default-event.png',
  `status` ENUM('Open', 'Closed') NOT NULL DEFAULT 'Open',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- Table 4: Registrations (Event Participation)
-- Practical 8, 14
-- -------------------------------------------------------
CREATE TABLE `registrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `event_id` INT NOT NULL,
  `registered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('Confirmed', 'Pending', 'Cancelled') NOT NULL DEFAULT 'Confirmed',
  UNIQUE KEY `unique_student_event` (`student_id`, `event_id`),
  CONSTRAINT `fk_reg_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reg_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- Table 5: Audit Logs (System Traceability & Moderation)
-- Practical 8, 14
-- -------------------------------------------------------
CREATE TABLE `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `username` VARCHAR(50) NOT NULL DEFAULT 'Guest',
  `action` VARCHAR(100) NOT NULL,
  `details` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT '127.0.0.1',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =======================================================
-- Seed Data
-- Default passwords:
-- admin / admin123
-- tanmay / student123
-- =======================================================

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@studenthub.com', '$2y$10$QlUChcJHg35lussNVudbz.POJzGvu3vJ3.E3Yx8nJsrgGtDfxIEu.', 'admin', NOW()),
(2, 'tanmay', 'tanmay@studenthub.com', '$2y$10$7Qy/5YtOV9a8XEuKZgF87ehjLk.i4MJMb7AH3EjvlY3YWkxPUx53y', 'student', NOW()),
(3, 'rohit_sharma', 'rohit@studenthub.com', '$2y$10$7Qy/5YtOV9a8XEuKZgF87ehjLk.i4MJMb7AH3EjvlY3YWkxPUx53y', 'student', NOW()),
(4, 'priya_patel', 'priya@studenthub.com', '$2y$10$7Qy/5YtOV9a8XEuKZgF87ehjLk.i4MJMb7AH3EjvlY3YWkxPUx53y', 'student', NOW());

-- Seed Students (20+ records for Practical 11)
INSERT INTO `students` (`id`, `user_id`, `student_id_code`, `full_name`, `email`, `phone`, `course`, `year`, `gender`, `status`) VALUES
(1, 2, 'STU2026001', 'Tanmay Sharma', 'tanmay@studenthub.com', '9876543210', 'B.Tech CSE', '3rd Year', 'Male', 'Active'),
(2, 3, 'STU2026002', 'Rohit Sharma', 'rohit@studenthub.com', '9823456781', 'B.Tech IT', '2nd Year', 'Male', 'Active'),
(3, 4, 'STU2026003', 'Priya Patel', 'priya@studenthub.com', '9845123456', 'BCA', '1st Year', 'Female', 'Active'),
(4, NULL, 'STU2026004', 'Ananya Iyer', 'ananya.iyer@gmail.com', '9876501234', 'MCA', '2nd Year', 'Female', 'Active'),
(5, NULL, 'STU2026005', 'Karan Verma', 'karan.verma@gmail.com', '9812345670', 'B.Tech CSE', '4th Year', 'Male', 'Active'),
(6, NULL, 'STU2026006', 'Sneha Kulkarni', 'sneha.k@gmail.com', '9765432109', 'BCA', '2nd Year', 'Female', 'Active'),
(7, NULL, 'STU2026007', 'Aditya Joshi', 'aditya.j@gmail.com', '9988776655', 'B.Tech IT', '3rd Year', 'Male', 'Active'),
(8, NULL, 'STU2026008', 'Riya Sen', 'riya.sen@gmail.com', '9871234560', 'B.Tech AI', '1st Year', 'Female', 'Active'),
(9, NULL, 'STU2026009', 'Devendra Singh', 'dev.singh@gmail.com', '9822334455', 'B.Tech CSE', '2nd Year', 'Male', 'Active'),
(10, NULL, 'STU2026010', 'Meera Nair', 'meera.nair@gmail.com', '9711223344', 'MCA', '1st Year', 'Female', 'Active'),
(11, NULL, 'STU2026011', 'Varun Kapoor', 'varun.k@gmail.com', '9900112233', 'BCA', '3rd Year', 'Male', 'Active'),
(12, NULL, 'STU2026012', 'Pooja Hegde', 'pooja.h@gmail.com', '9833445566', 'B.Tech IT', '4th Year', 'Female', 'Active'),
(13, NULL, 'STU2026013', 'Siddharth Rao', 'sid.rao@gmail.com', '9844556677', 'B.Tech CSE', '1st Year', 'Male', 'Active'),
(14, NULL, 'STU2026014', 'Tanvi Deshmukh', 'tanvi.d@gmail.com', '9855667788', 'MCA', '2nd Year', 'Female', 'Active'),
(15, NULL, 'STU2026015', 'Nikhil Mehta', 'nikhil.m@gmail.com', '9866778899', 'B.Tech AI', '2nd Year', 'Male', 'Active'),
(16, NULL, 'STU2026016', 'Kavita Menon', 'kavita.m@gmail.com', '9877889900', 'BCA', '1st Year', 'Female', 'Active'),
(17, NULL, 'STU2026017', 'Harsh Agarwal', 'harsh.a@gmail.com', '9888990011', 'B.Tech IT', '3rd Year', 'Male', 'Inactive'),
(18, NULL, 'STU2026018', 'Bhavna Chawla', 'bhavna.c@gmail.com', '9899001122', 'B.Tech CSE', '4th Year', 'Female', 'Active'),
(19, NULL, 'STU2026019', 'Gaurav Dubey', 'gaurav.d@gmail.com', '9911223344', 'MCA', '1st Year', 'Male', 'Active'),
(20, NULL, 'STU2026020', 'Ishita Bansal', 'ishita.b@gmail.com', '9922334455', 'B.Tech AI', '3rd Year', 'Female', 'Active'),
(21, NULL, 'STU2026021', 'Manish Pandey', 'manish.p@gmail.com', '9933445566', 'BCA', '2nd Year', 'Male', 'Active');

-- Seed Events (15+ records for Practical 6 & 12)
INSERT INTO `events` (`id`, `title`, `category`, `description`, `event_date`, `location`, `poster_image`, `status`) VALUES
(1, 'National Hackathon 2026', 'Technical', '36-hour code sprint focusing on AI, Cloud, and Web technologies with industry mentors.', '2026-11-15', 'Campus Audi-1', 'assets/images/default-event.png', 'Open'),
(2, 'Web 3.0 & Cloud Workshop', 'Workshop', 'Hands-on training session on modern full-stack web and cloud microservices.', '2026-11-18', 'Lab 3, CS Block', 'assets/images/default-event.png', 'Open'),
(3, 'Annual Sports Fest "Urja"', 'Sports', 'Inter-college cricket, football, badminton, and athletics tournaments.', '2026-11-25', 'University Sports Complex', 'assets/images/default-event.png', 'Open'),
(4, 'AI & Robotics Symposium', 'Seminar', 'Keynotes by renowned researchers exploring generative models and automated robotics.', '2026-11-30', 'Seminar Hall B', 'assets/images/default-event.png', 'Open'),
(5, 'Cybersecurity Bootcamp', 'Workshop', 'Learn defensive security, vulnerability scanning, and ethical hacking fundamentals.', '2026-12-05', 'Network Security Lab', 'assets/images/default-event.png', 'Open'),
(6, 'Cultural Fest "Tarang 2026"', 'Cultural', 'Music band showdown, dance battles, dramatics, and fashion show.', '2026-12-12', 'Open Amphitheatre', 'assets/images/default-event.png', 'Open'),
(7, 'Alumni Networking Dinner', 'Networking', 'Meet distinguished alumni working in Fortune 500 tech companies.', '2026-12-18', 'Convention Centre', 'assets/images/default-event.png', 'Open'),
(8, 'UI/UX Design Masterclass', 'Workshop', 'Learn Figma wireframing, color theory, and accessible design principles.', '2026-12-20', 'Design Studio 2', 'assets/images/default-event.png', 'Open'),
(9, 'Inter-College Debate Championship', 'Academic', 'Parliamentary debate on digital ethics and artificial intelligence governance.', '2026-12-22', 'Conference Hall A', 'assets/images/default-event.png', 'Open'),
(10, 'Campus Blood Donation Camp', 'Social', 'Annual blood donation drive in collaboration with the Red Cross Society.', '2026-12-24', 'Student Centre', 'assets/images/default-event.png', 'Closed'),
(11, 'StartUp Pitch Competition', 'Business', 'Pitch your innovative tech idea to angel investors and win seed grants.', '2027-01-08', 'Incubation Centre', 'assets/images/default-event.png', 'Open'),
(12, 'Android App Development Bootcamp', 'Technical', 'Build production-ready Android apps with Kotlin and Jetpack Compose.', '2027-01-12', 'Lab 5, CS Block', 'assets/images/default-event.png', 'Open'),
(13, 'Photography & Film Exhibition', 'Cultural', 'Showcasing lens work and short films crafted by student media club.', '2027-01-15', 'Art Gallery', 'assets/images/default-event.png', 'Open'),
(14, 'Data Science with Python Seminar', 'Seminar', 'Exploratory data analysis, Pandas, Matplotlib, and predictive modeling.', '2027-01-20', 'Seminar Hall C', 'assets/images/default-event.png', 'Open'),
(15, 'Yoga & Mental Wellness Workshop', 'Sports', 'Guided meditation, stress release techniques, and healthy habits.', '2027-01-25', 'Gymnasium Hall', 'assets/images/default-event.png', 'Open');

-- Seed Registrations
INSERT INTO `registrations` (`student_id`, `event_id`, `status`) VALUES
(1, 1, 'Confirmed'),
(1, 2, 'Confirmed'),
(2, 1, 'Confirmed'),
(3, 3, 'Confirmed'),
(4, 2, 'Confirmed'),
(5, 4, 'Confirmed'),
(6, 1, 'Confirmed'),
(7, 5, 'Confirmed');

-- Seed Audit Logs
INSERT INTO `audit_logs` (`user_id`, `username`, `action`, `details`, `ip_address`) VALUES
(1, 'admin', 'SYSTEM_INITIALIZATION', 'Database seeded with default practical records.', '127.0.0.1'),
(1, 'admin', 'USER_LOGIN', 'Admin logged into administration panel.', '127.0.0.1'),
(2, 'tanmay', 'STUDENT_REGISTRATION', 'Student account registered and profile created.', '127.0.0.1'),
(2, 'tanmay', 'EVENT_REGISTRATION', 'Registered for National Hackathon 2026.', '127.0.0.1');

