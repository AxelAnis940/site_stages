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

-- Sample application users (passwords are hashed with PASSWORD_DEFAULT / bcrypt)
SET FOREIGN_KEY_CHECKS=0;
DELETE FROM company_evaluations WHERE user_id IN (1, 2, 3);
DELETE FROM applications WHERE user_id IN (1, 2, 3);
DELETE FROM users WHERE id IN (1, 2, 3);
SET FOREIGN_KEY_CHECKS=1;
INSERT INTO users (id, name, email, password, role) VALUES
(1, 'John Student','student@example.com','$2y$10$f3M4hC0XvtN2xJmj7LdZVeLKVF4JJ/2To6Vgj/QoF.70.MAyDNDze','student'),
(2, 'Jane Recruiter','recruiter@example.com','$2y$10$f3M4hC0XvtN2xJmj7LdZVeLKVF4JJ/2To6Vgj/QoF.70.MAyDNDze','recruiter'),
(3, 'Admin User','admin@example.com','$2y$10$f3M4hC0XvtN2xJmj7LdZVeLKVF4JJ/2To6Vgj/QoF.70.MAyDNDze','admin')
ON DUPLICATE KEY UPDATE password=VALUES(password);


-- Companies and related entities
CREATE TABLE IF NOT EXISTS companies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  email VARCHAR(150),
  phone VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- sample companies for demo
INSERT INTO companies (name, description, email, phone) VALUES
('TechVision AI','Entreprise spécialisée dans l\'IA et le ML','contact@techvision.ai','+33 1 23 45 67 89'),
('CreativeStudio','Agence de marketing et design','info@creativestudio.com','+33 2 98 76 54 32')
ON DUPLICATE KEY UPDATE name=VALUES(name);

CREATE TABLE IF NOT EXISTS offers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  company_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

-- sample offers; company ids assume the earlier inserts gave ids 1 and 2
INSERT INTO offers (company_id, title, description) VALUES
(1,'Junior AI Engineer','Stage en apprentissage automatique pour débutants.'),
(2,'Marketing Intern','Stage en marketing digital avec CreativeStudio.');

CREATE TABLE IF NOT EXISTS applications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  offer_id INT NOT NULL,
  user_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS company_evaluations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  company_id INT NOT NULL,
  user_id INT NOT NULL,
  rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
  comment TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- sample evaluations will be inserted after users are created below

-- Create MySQL accounts for demo (run as root). Adjust passwords in production.
CREATE USER IF NOT EXISTS 'student'@'localhost' IDENTIFIED BY 'password123';
CREATE USER IF NOT EXISTS 'recruiter'@'localhost' IDENTIFIED BY 'password123';
CREATE USER IF NOT EXISTS 'admin'@'localhost' IDENTIFIED BY 'password123';

GRANT SELECT, INSERT ON internships_app.* TO 'student'@'localhost';
GRANT ALL PRIVILEGES ON internships_app.* TO 'recruiter'@'localhost';
GRANT ALL PRIVILEGES ON internships_app.* TO 'admin'@'localhost';

FLUSH PRIVILEGES;
