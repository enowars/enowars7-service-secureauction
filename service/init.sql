-- Create a `secureauction` database (if it doesn't already exist)
CREATE DATABASE IF NOT EXISTS secureauction;
USE secureauction;

-- Set MySQL server timezone to UTC
SET @@global.time_zone = '+00:00';

-- Create a `users` table for storing user account information
CREATE TABLE IF NOT EXISTS users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  user_name VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  user_type ENUM('REGULAR', 'PREMIUM') DEFAULT 'REGULAR',
  public_key_e VARCHAR(255) DEFAULT NULL,
  public_key_n VARCHAR(1024) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_name (user_name),
  INDEX idx_password (password)
);

-- Create an `items` table for storing auction items
CREATE TABLE IF NOT EXISTS items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  start_price VARCHAR(1024) NOT NULL,
  item_type ENUM('REGULAR', 'PREMIUM') DEFAULT 'REGULAR',
  status ENUM('OPEN', 'CLOSED') DEFAULT 'OPEN',
  user_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  end_time  TIMESTAMP DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(user_id),
  INDEX (created_at)
);

-- Create a `bids` table for storing bids on auction items
CREATE TABLE IF NOT EXISTS bids (
  id INT AUTO_INCREMENT PRIMARY KEY,
  amount VARCHAR(1024) NOT NULL,
  user_id INT,
  item_id INT,
  ranking INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id),
  FOREIGN KEY (item_id) REFERENCES items(id),
  INDEX (created_at)
);


-- Create new user with limited privileges
CREATE USER IF NOT EXISTS 'appuser'@'%' IDENTIFIED BY 'secure_password';

-- Grant limited permissions to new user on the `secureauction` database
GRANT SELECT, INSERT ON secureauction.* TO 'appuser'@'%';

-- Tell MySQL server to reload user privileges
FLUSH PRIVILEGES;