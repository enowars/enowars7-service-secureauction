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
