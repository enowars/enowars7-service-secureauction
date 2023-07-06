-- Create a `secureauction` database (if it doesn't already exist)
CREATE DATABASE IF NOT EXISTS secureauction;
USE secureauction;

-- Set MySQL server timezone to UTC
SET @@global.time_zone = '+00:00';

-- Ensuring that MYSQL event scheduler is ON
-- SET GLOBAL event_scheduler = ON;

-- Close expired auctions
/*CREATE EVENT IF NOT EXISTS close_expired_auctions
ON SCHEDULE EVERY 3 MINUTE
DO
  UPDATE items 
  SET status = 'CLOSED' 
  WHERE end_time <= CURRENT_TIMESTAMP AND status = 'OPEN';*/


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

-- Create a `notifications` table for storing notifications
CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  message TEXT,
  read_status BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id)
);


-- Insert data
-- Users
INSERT INTO users (user_id, user_name, password) VALUES ('1', 'user1', 'password1');
INSERT INTO users (user_id, user_name, password) VALUES ('2', 'user2', 'password2');
INSERT INTO users (user_id, user_name, password) VALUES ('3', 'user3', 'password3');
INSERT INTO users (user_id, user_name, password) VALUES ('4', 'user4', 'password4');
INSERT INTO users (user_id, user_name, password) VALUES ('5', 'user5', 'password5');

-- Update user_type to 'PREMIUM' for two users
UPDATE users SET user_type = 'PREMIUM' WHERE user_id IN (1, 2);

-- Items
INSERT INTO items (user_id, name, start_price) VALUES (1, 'Vintage Camera', 50);
INSERT INTO items (user_id, name, start_price) VALUES (2, 'Antique Vase', 100);
INSERT INTO items (user_id, name, start_price) VALUES (3, 'Rare Book', 200);
INSERT INTO items (user_id, name, start_price) VALUES (4, 'Old Map', 30);
INSERT INTO items (user_id, name, start_price) VALUES (5, 'Collector Coin', 10);
INSERT INTO items (user_id, name, start_price) VALUES (2, 'Vintage Watch', 250);
INSERT INTO items (user_id, name, start_price) VALUES (1, 'Antique Lamp', 80);
INSERT INTO items (user_id, name, start_price) VALUES (3, 'Rare Painting', 500);
INSERT INTO items (user_id, name, start_price) VALUES (2, 'Antique Mirror', 100);
INSERT INTO items (user_id, name, start_price) VALUES (1, 'Vintage Record Player', 150);
INSERT INTO items (user_id, name, start_price) VALUES (1, 'Rare Comic Book', 300);
INSERT INTO items (user_id, name, start_price) VALUES (4, 'Vintage Guitar', 350);
INSERT INTO items (user_id, name, start_price) VALUES (5, 'Antique Clock', 120);
INSERT INTO items (user_id, name, start_price) VALUES (4, 'Rare Stamp Collection', 400);
INSERT INTO items (user_id, name, start_price) VALUES (5, 'Vintage Typewriter', 75);
INSERT INTO items (user_id, name, start_price) VALUES (2, 'Antique Desk', 200);
INSERT INTO items (user_id, name, start_price) VALUES (1, 'Rare Vinyl Record', 100);
INSERT INTO items (user_id, name, start_price) VALUES (1, 'Vintage Radio', 60);
INSERT INTO items (user_id, name, start_price) VALUES (3, 'Antique Chair', 90);
INSERT INTO items (user_id, name, start_price) VALUES (4, 'Rare Autographed Photo', 250);

-- Update item_type to 'PREMIUM' for five items
UPDATE items SET item_type = 'PREMIUM' WHERE id IN (1, 2, 3, 4, 5);

-- Set the end_time for all the items to be 2 minutes from their created_at timestamps
UPDATE items
SET end_time = DATE_ADD(created_at, INTERVAL 2 MINUTE);

-- Bids
INSERT INTO bids (user_id, item_id, amount) VALUES (2, 1, 60);
INSERT INTO bids (user_id, item_id, amount) VALUES (3, 1, 70);
INSERT INTO bids (user_id, item_id, amount) VALUES (4, 2, 120);
INSERT INTO bids (user_id, item_id, amount) VALUES (5, 2, 130);
INSERT INTO bids (user_id, item_id, amount) VALUES (1, 3, 250);
INSERT INTO bids (user_id, item_id, amount) VALUES (2, 3, 300);
INSERT INTO bids (user_id, item_id, amount) VALUES (3, 4, 40);
INSERT INTO bids (user_id, item_id, amount) VALUES (4, 4, 50);
INSERT INTO bids (user_id, item_id, amount) VALUES (5, 5, 20);
INSERT INTO bids (user_id, item_id, amount) VALUES (1, 5, 25);

-- Create new user with limited privileges
CREATE USER IF NOT EXISTS 'appuser'@'%' IDENTIFIED BY 'secure_password';

-- Grant limited permissions to new user on the `secureauction` database
GRANT SELECT, INSERT ON secureauction.* TO 'appuser'@'%';

-- Tell MySQL server to reload user privileges
FLUSH PRIVILEGES;
