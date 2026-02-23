-- Database initialization for internships_app
CREATE DATABASE IF NOT EXISTS internships_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE internships_app;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('student','recruiter','admin','public') DEFAULT 'public',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample application users (demo passwords stored as plain text here for simplicity; setup script will create MySQL users below)
INSERT INTO users (name, email, password, role) VALUES
('John Student','student@example.com','password123','student'),
('Jane Recruiter','recruiter@example.com','password123','recruiter'),
('Admin User','admin@example.com','password123','admin')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Create MySQL accounts for demo (run as root). Adjust passwords in production.
CREATE USER IF NOT EXISTS 'student'@'localhost' IDENTIFIED BY 'password123';
CREATE USER IF NOT EXISTS 'recruiter'@'localhost' IDENTIFIED BY 'password123';
CREATE USER IF NOT EXISTS 'admin'@'localhost' IDENTIFIED BY 'password123';

GRANT SELECT, INSERT ON internships_app.* TO 'student'@'localhost';
GRANT ALL PRIVILEGES ON internships_app.* TO 'recruiter'@'localhost';
GRANT ALL PRIVILEGES ON internships_app.* TO 'admin'@'localhost';

FLUSH PRIVILEGES;
