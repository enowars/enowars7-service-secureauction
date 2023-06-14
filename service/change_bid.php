<?php
// Start session and include necessary files
session_start();
include("config.php");
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
    $userId = $_POST['user_id'];
    $newBid = $_POST['new_bid'];

    // Validate form data
    if (empty($itemId) || empty($userId) || empty($newBid)) {
        die("Please fill in all fields.");
    }

    // Ensure user can only change their own bids
    if ($userId != $user_data['user_id']) {
        die("You can only change your own bids.");
    }

    // Get the item details
    $item_details = $item->getItemDetails($itemId);

    // Create new Bid object
    $bid = new Bid($con);

    // Get the current highest bid for the item from the logged-in user
    $highestBid = $bid->getHighestBidByUser($itemId, $userId);

    // Update the bid
    $result = $bid->placeBid($itemId, $userId, $newBid);


    // TODO after changing the bid in my profile, i want to go back to the item page
    // If bid was updated successfully
    if ($result) {
        //Redirect back to the previous page
        header("Location: ".$_SERVER['HTTP_REFERER']);
    } else {
        echo "There was an error updating your bid, Volkan. Your attempted new bid was: " . $newBid;
    }
} else {
    echo "No form data received.";
}
?>