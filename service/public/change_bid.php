<?php
// Start session and include necessary files
session_start();
include("db_connect.php");
include("user.php");
include("bid.php");
include("item.php");

// Create new User object
$user = new User($con);

// Check if user is logged in
$user_data = $user->checkLogin($con);
$item = new Item($con);

// If form data has been posted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $itemId = $_POST['item_id'];
    $userId = $user_data['user_id']; 
    $newBid = $_POST['new_bid'];

    // Validate form data
    if (empty($itemId)) {
        die("Item ID is required.");
    }

    if (empty($userId)) {
        die("User ID is required.");
    }

    if (empty($newBid)) {
        die("New Bid is required.");
    }


    // Bid should be a positive number 
    if ($newBid <= 0 ) {
        die("Bid amount has to be a positive number.");
    }

    // Get the item details
    $item_details = $item->getItemDetails($itemId);

    // Create new Bid object
    $bid = new Bid($con);

    // Get the current highest bid for the item from the logged-in user
    $highestBid = $bid->getHighestBidByUser($itemId, $userId);

    // Update the bid
    $result = $bid->placeBid($itemId, $userId, $newBid);

    // If bid was updated successfully
    if ($result) {
        //Redirect back to the previous page
        header("Location: ".$_SERVER['HTTP_REFERER']);
    } 
    else {
        echo "There was an error updating your bid. Your attempted new bid was: " . $newBid;
    }
} else {
        echo "No form data received.";
}
?>