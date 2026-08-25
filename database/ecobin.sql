CREATE DATABASE IF NOT EXISTS ecobin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecobin;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS activity_logs,audit_logs,system_config,announcements,notifications,reward_transactions,recycling_appointments,recycling_submissions,recycling_centers,collection_requests,waste_reports,users;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 email VARCHAR(150) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 role VARCHAR(40) NOT NULL DEFAULT 'Resident',
 status VARCHAR(20) NOT NULL DEFAULT 'Active',
 email_verified_at DATETIME NULL,
 verification_token VARCHAR(100) NULL,
 reset_token VARCHAR(100) NULL,
 reset_expires_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE waste_reports (
 id INT AUTO_INCREMENT PRIMARY KEY,
 resident_id INT NOT NULL,
 category VARCHAR(100) NOT NULL,
 description TEXT NOT NULL,
 image VARCHAR(255) NULL,
 latitude DECIMAL(10,7) NULL,
 longitude DECIMAL(10,7) NULL,
 address VARCHAR(500) NOT NULL,
 status VARCHAR(40) NOT NULL DEFAULT 'Pending',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX(resident_id),
 CONSTRAINT fk_waste_resident FOREIGN KEY(resident_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE collection_requests (
 id INT AUTO_INCREMENT PRIMARY KEY,
 waste_report_id INT NOT NULL UNIQUE,
 resident_id INT NOT NULL,
 preferred_date DATE NOT NULL,
 scheduled_date DATE NULL,
 collection_staff_id INT NULL,
 status VARCHAR(40) NOT NULL DEFAULT 'Pending',
 remarks TEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_collection_report FOREIGN KEY(waste_report_id) REFERENCES waste_reports(id),
 CONSTRAINT fk_collection_resident FOREIGN KEY(resident_id) REFERENCES users(id),
 CONSTRAINT fk_collection_staff FOREIGN KEY(collection_staff_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE recycling_centers (
 id INT AUTO_INCREMENT PRIMARY KEY,
 operator_id INT NOT NULL,
 name VARCHAR(120) NOT NULL,
 address VARCHAR(500) NOT NULL,
 accepted_materials VARCHAR(255) NOT NULL,
 availability VARCHAR(30) NOT NULL DEFAULT 'Open',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_center_operator FOREIGN KEY(operator_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE recycling_submissions (
 id INT AUTO_INCREMENT PRIMARY KEY,
 resident_id INT NOT NULL,
 center_id INT NOT NULL,
 material VARCHAR(80) NOT NULL,
 weight_kg DECIMAL(8,2) NOT NULL,
 points INT NOT NULL DEFAULT 0,
 status VARCHAR(30) NOT NULL DEFAULT 'Pending',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_submission_resident FOREIGN KEY(resident_id) REFERENCES users(id),
 CONSTRAINT fk_submission_center FOREIGN KEY(center_id) REFERENCES recycling_centers(id)
) ENGINE=InnoDB;

CREATE TABLE recycling_appointments (
 id INT AUTO_INCREMENT PRIMARY KEY,
 resident_id INT NOT NULL,
 center_id INT NOT NULL,
 appointment_at DATETIME NOT NULL,
 status VARCHAR(30) NOT NULL DEFAULT 'Pending',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_appt_resident FOREIGN KEY(resident_id) REFERENCES users(id),
 CONSTRAINT fk_appt_center FOREIGN KEY(center_id) REFERENCES recycling_centers(id)
) ENGINE=InnoDB;

CREATE TABLE reward_transactions (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NOT NULL,
 points INT NOT NULL,
 type VARCHAR(40) NOT NULL,
 description VARCHAR(255) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_reward_user FOREIGN KEY(user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE notifications (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NOT NULL,
 title VARCHAR(120) NOT NULL,
 message TEXT NOT NULL,
 type VARCHAR(50) NOT NULL DEFAULT 'System',
 is_read TINYINT(1) NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_notification_user FOREIGN KEY(user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE announcements (
 id INT AUTO_INCREMENT PRIMARY KEY,
 title VARCHAR(150) NOT NULL,
 message TEXT NOT NULL,
 created_by INT NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_announcement_admin FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE system_config (
 config_key VARCHAR(80) PRIMARY KEY,
 config_value TEXT NOT NULL,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NULL,
 action VARCHAR(80) NOT NULL,
 entity VARCHAR(80) NOT NULL,
 entity_id INT NULL,
 details TEXT NULL,
 ip_address VARCHAR(60) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE activity_logs (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NULL,
 activity VARCHAR(100) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Seed accounts. Password for all: Password123!
INSERT INTO users(name,email,password_hash,role,status,email_verified_at) VALUES
('Demo Resident','resident@ecobin.test','$2y$12$Oi9OtufMUSutxEfeBbH0JOZHTKq/HJrmEVxPTJQ3GoDcNHQtKs.9y','Resident','Active',NOW()),
('Demo Admin','admin@ecobin.test','$2y$12$Oi9OtufMUSutxEfeBbH0JOZHTKq/HJrmEVxPTJQ3GoDcNHQtKs.9y','Admin','Active',NOW()),
('Collector Amir','collector@ecobin.test','$2y$12$Oi9OtufMUSutxEfeBbH0JOZHTKq/HJrmEVxPTJQ3GoDcNHQtKs.9y','Collection Staff','Active',NOW()),
('Recycle Operator','operator@ecobin.test','$2y$12$Oi9OtufMUSutxEfeBbH0JOZHTKq/HJrmEVxPTJQ3GoDcNHQtKs.9y','Recycling Center Operator','Active',NOW());

INSERT INTO recycling_centers(operator_id,name,address,accepted_materials,availability)
VALUES(4,'EcoBin Setapak Recycling Centre','Setapak, Kuala Lumpur','Plastic, Paper, Metal, E-Waste','Open');

INSERT INTO announcements(title,message,created_by)
VALUES('Welcome to EcoBin','Use EcoBin to report waste, schedule collection and participate in recycling.',2);

INSERT INTO system_config(config_key,config_value) VALUES
('collection.max_daily','50'),
('recycling.points_per_kg','10');
