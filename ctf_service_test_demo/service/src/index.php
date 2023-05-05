<?php
include 'functions.php';

// Check if a form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['register'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        if (registerUser($username, $password)) {
            echo "Registration successful";
        } else {
            echo "Registration failed";
        }
    } elseif (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        if (loginUser($username, $password)) {
            echo "Login successful";
        } else {
            echo "Login failed";
        }
    } elseif (isset($_POST['list_item'])) {
        session_start();
        $user_id = $_SESSION['user_id'];
        $item_name = $_POST['item_name'];
        $starting_price = $_POST['starting_price'];
        if (listItem($user_id, $item_name, $starting_price)) {
            echo "Item listed successfully";
        } else {
            echo "Item listing failed";
        }
    } elseif (isset($_POST['place_bid'])) {
        session_start();
        $user_id = $_SESSION['user_id'];
        $item_id = $_POST['item_id'];
        $bid_amount = $_POST['bid_amount'];
        if (placeBid($user_id, $item_id, $bid_amount)) {
            echo "Bid placed successfully";
        } else {
            echo "Bid placement failed";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecureAuction</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>SecureAuction</h1>

    <h2>Register</h2>
    <form action="index.php" method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" name="register" value="Register">
    </form>

    <h2>Login</h2>
    <form action="index.php" method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" name="login" value="Login">
    </form>

    <h2>List Item</h2>
    <form action="index.php" method="post">
        <input type="text" name="item_name" placeholder="Item Name" required>
        <input type="number" name="starting_price" placeholder="Starting Price" required>
        <input type="submit" name="list_item" value="List Item">
    </form>

    <h2>Place Bid</h2>
    <form action="index.php" method="post">
        <input type="number" name="item_id" placeholder="Item ID" required>
        <input type="number" name="bid_amount" placeholder="Bid Amount" required>
        <input type="submit" name="place_bid" value="Place Bid">
    </form>

    <h2>Auction Items</h2>
    <p><a href="auction.php">View items for auction</a></p>
</body>
</html>

