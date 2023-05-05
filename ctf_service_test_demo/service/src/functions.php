<?php
include 'config.php';

// Function to register a new user
function registerUser($username, $password) {
    global $conn;

    $username = mysqli_real_escape_string($conn, $username);
    $password = mysqli_real_escape_string($conn, $password);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, password) VALUES ('$username', '$hashed_password')";

    if ($conn->query($sql) === TRUE) {
        return true;
    } else {
        return false;
    }
}

// Function to authenticate a user
function loginUser($username, $password) {
    global $conn;

    $username = mysqli_real_escape_string($conn, $username);
    $password = mysqli_real_escape_string($conn, $password);

    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            session_start();
            $_SESSION['user_id'] = $row['id'];
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

// Function to list an item for auction
function listItem($user_id, $item_name, $starting_price) {
    global $conn;

    $item_name = mysqli_real_escape_string($conn, $item_name);
    $starting_price = mysqli_real_escape_string($conn, $starting_price);

    $sql = "INSERT INTO items (user_id, item_name, starting_price) VALUES ('$user_id', '$item_name', '$starting_price')";

    if ($conn->query($sql) === TRUE) {
        return true;
    } else {
        return false;
    }
}

// Function to place a bid on an item
function placeBid($user_id, $item_id, $bid_amount) {
    global $conn;

    $item_id = mysqli_real_escape_string($conn, $item_id);
    $bid_amount = mysqli_real_escape_string($conn, $bid_amount);

    $sql = "INSERT INTO bids (user_id, item_id, bid_amount) VALUES ('$user_id', '$item_id', '$bid_amount')";

    if ($conn->query($sql) === TRUE) {
        return true;
    } else {
        return false;
    }
}

// Function to get a list of items and their highest bids
function getItemsWithHighestBids() {
    global $conn;

    $sql = "SELECT items.id, items.item_name, items.starting_price, MAX(bids.bid_amount) as highest_bid
        FROM items
        LEFT JOIN bids ON items.id = bids.item_id
        GROUP BY items.id, items.item_name, items.starting_price
        ORDER BY items.id";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $items = array();
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        return $items;
    } else {
        return array();
    }
}

