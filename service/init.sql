-- Create a `secureauction` database (if it doesn't already exist)
CREATE DATABASE IF NOT EXISTS secureauction;
USE secureauction;

-- Create a `users` table for storing user account information
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(255) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create an `items` table for storing auction items
CREATE TABLE IF NOT EXISTS items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  image_url VARCHAR(1024),
  start_price DECIMAL(10,2) NOT NULL,
  user_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create a `bids` table for storing bids on auction items
CREATE TABLE IF NOT EXISTS bids (
  id INT AUTO_INCREMENT PRIMARY KEY,
  amount DECIMAL(10,2) NOT NULL,
  user_id INT,
  item_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (item_id) REFERENCES items(id)
);

-- Create an `auctions` table for managing auctions
CREATE TABLE IF NOT EXISTS auctions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  item_id INT,
  start_time TIMESTAMP NOT NULL,
  end_time TIMESTAMP NOT NULL,
  completed BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (item_id) REFERENCES items(id)
);


-- Insert data
-- Users
INSERT INTO users (username, password) VALUES ('user1', 'password1');
INSERT INTO users (username, password) VALUES ('user2', 'password2');
INSERT INTO users (username, password) VALUES ('user3', 'password3');
INSERT INTO users (username, password) VALUES ('user4', 'password4');
INSERT INTO users (username, password) VALUES ('user5', 'password5');

-- Items
INSERT INTO items (user_id, item_name, starting_price) VALUES (1, 'Vintage Camera', 50);
INSERT INTO items (user_id, item_name, starting_price) VALUES (2, 'Antique Vase', 100);
INSERT INTO items (user_id, item_name, starting_price) VALUES (3, 'Rare Book', 200);
INSERT INTO items (user_id, item_name, starting_price) VALUES (4, 'Old Map', 30);
INSERT INTO items (user_id, item_name, starting_price) VALUES (5, 'Collector Coin', 10);

-- Bids
INSERT INTO bids (user_id, item_id, bid_amount) VALUES (2, 1, 60);
INSERT INTO bids (user_id, item_id, bid_amount) VALUES (3, 1, 70);
INSERT INTO bids (user_id, item_id, bid_amount) VALUES (4, 2, 120);
INSERT INTO bids (user_id, item_id, bid_amount) VALUES (5, 2, 130);
INSERT INTO bids (user_id, item_id, bid_amount) VALUES (1, 3, 250);
INSERT INTO bids (user_id, item_id, bid_amount) VALUES (2, 3, 300);
INSERT INTO bids (user_id, item_id, bid_amount) VALUES (3, 4, 40);
INSERT INTO bids (user_id, item_id, bid_amount) VALUES (4, 4, 50);
INSERT INTO bids (user_id, item_id, bid_amount) VALUES (5, 5, 20);
INSERT INTO bids (user_id, item_id, bid_amount) VALUES (1, 5, 25);
