CREATE DATABASE IF NOT EXISTS tgbtl_ott CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tgbtl_ott;
CREATE TABLE admins(id INT AUTO_INCREMENT PRIMARY KEY,username VARCHAR(50) UNIQUE,password_hash VARCHAR(255),role ENUM('super','admin','support') DEFAULT 'support',locked TINYINT DEFAULT 0,last_login DATETIME NULL,created_at DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE players(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(80),account VARCHAR(50),balance BIGINT DEFAULT 0,status ENUM('active','locked') DEFAULT 'active',is_demo TINYINT DEFAULT 0,created_at DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE tables_game(id INT AUTO_INCREMENT PRIMARY KEY,code VARCHAR(20),name VARCHAR(80),players INT DEFAULT 0,pot BIGINT DEFAULT 0,active TINYINT DEFAULT 1,created_at DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE transactions(id INT AUTO_INCREMENT PRIMARY KEY,type ENUM('deposit','withdraw','transfer') NOT NULL,player_id INT,to_player_id INT NULL,amount BIGINT,fee BIGINT DEFAULT 0,method VARCHAR(40),status ENUM('pending','done','rejected') DEFAULT 'pending',is_demo TINYINT DEFAULT 0,created_at DATETIME DEFAULT CURRENT_TIMESTAMP,INDEX(type,status,created_at));
CREATE TABLE logs(id INT AUTO_INCREMENT PRIMARY KEY,admin VARCHAR(50),action VARCHAR(60),detail TEXT,created_at DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE settings(k VARCHAR(40) PRIMARY KEY,v VARCHAR(255));
INSERT INTO settings VALUES('fee_percent','2'),('daily_goal','5000000');
-- Admin mặc định: admin / ChangeMe@123 (ĐỔI NGAY SAU KHI ĐĂNG NHẬP, hash tạo bằng setup.php)
