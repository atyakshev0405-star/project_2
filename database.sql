-- Database schema for Canteen Schedule System
-- MySQL Database

-- Create database
CREATE DATABASE IF NOT EXISTS `php-canteen-app.local`;
USE `php-canteen-app.local`;

-- Table: admins (admin accounts)
CREATE TABLE IF NOT EXISTS `admins` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Table: groups (student groups)
CREATE TABLE IF NOT EXISTS `groups` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_name VARCHAR(50) UNIQUE NOT NULL,
    student_count INT NOT NULL,
    stream INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Table: schedules
CREATE TABLE IF NOT EXISTS `schedules` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    schedule_date DATE NOT NULL,
    arrival_time TIME NOT NULL,
    stream INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_group_date (group_id, schedule_date),
    INDEX idx_date (schedule_date),
    INDEX idx_stream (stream)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Insert default admin account
-- Username: pav313
-- Password: sip313 (hashed with password_hash)
INSERT INTO `admins` (username, password) VALUES
('pav313', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- Note: Default password is 'sip313' - change after deployment

-- Sample groups from the example
INSERT INTO `groups` (group_name, student_count, stream) VALUES
('СИП-113/25', 25, 1),
('СИП-123/25', 28, 1),
('СИП-133/25', 22, 1),
('СИП-143/25', 27, 1),
('СОБ-113/25', 24, 1),
('СОБ-123/25', 26, 1),
('СОБ-134/25', 23, 1),
('СЭС-113/25', 29, 1),
('СТГ-113/25', 21, 1),
('НЭС-113/25', 25, 1),
('СИП-213/24', 26, 2),
('СЭС-223/24', 28, 2),
('СИП-243/24', 24, 2),
('СОБ-233/24', 27, 2),
('СЭС-213/24', 25, 2),
('НЭС-213/24', 22, 2),
('СОБ-313/23', 24, 3),
('СИП-333/23', 26, 3),
('СТГ-323/23', 23, 3),
('СОБ-333/23', 25, 3),
('СЭС-413/22', 27, 3),
('СТГ-413/22', 28, 3);
