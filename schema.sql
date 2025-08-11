-- schema.sql
CREATE DATABASE IF NOT EXISTS book_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE book_system;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','editor','viewer') NOT NULL DEFAULT 'viewer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS genres (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  author VARCHAR(255) NOT NULL,
  isbn VARCHAR(50),
  year INT,
  language_code VARCHAR(10) NOT NULL,
  genre_id INT,
  tags VARCHAR(255),
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Seed genres (editable)
INSERT IGNORE INTO genres(name) VALUES
 ('Fiction'),('Non-Fiction'),('History'),('Biography'),('Poetry'),('Science'),('Technology'),('Philosophy'),('Psychology'),('Religion'),('Children'),('Education');

-- Create initial admin (change email & password after import)
INSERT INTO users (name, email, password_hash, role)
VALUES ('Admin', 'admin@example.com', SHA2('admin123', 256), 'admin');
-- Note: Above uses SHA2 for convenience; app uses password_hash(). Update password after first login via users.php