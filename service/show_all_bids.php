<?php
// Start session
session_start();
// Include your connection configuration and the User class
include("config.php");
include("user.php");

// Get the user's ID from the session or elsewhere
$user_id = $_SESSION['user_id'];
echo "User ID: " . $user_id . "<br>";

// Get a new User object
$user = new User($con);

// Get the bids for this user
$result = $user->getUserBids($user_id);

// Process the results
if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        echo "id: " . $row["id"]. " - Item Name: " . $row["item_name"]. " - Bid Amount: " . $row["amount"]. "<br>";
    }
} else {
    echo "No bids found";
}
?>