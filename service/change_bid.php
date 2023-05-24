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

    // Ensure new bid is higher than the starting price bid
    if ($newBid <= $item_details['start_price']) {
        $errorMessage = "Your new bid must be higher than the starting price bid of {$item_details['start_price']} for the item (ID: {$itemId}, Name: '{$item_details['name']}', Start Price: {$item_details['start_price']}).";
    }

    // Get the current highest bid for the item from the logged-in user
    $highestBid = $bid->getHighestBidByUser($itemId, $userId);

    // Ensure new bid is higher than the current highest bid from the logged-in user
    if ($newBid <= $highestBid) {
        $errorMessage = "Your new bid must be higher than the current highest bid of {$highestBid} for the item (ID: {$itemId}, Name: '{$item_details['name']}', Start Price: {$item_details['start_price']}).";
    }

    // Display the error message
    if (isset($errorMessage)) {
        echo '<div class="alert alert-danger" role="alert">' . $errorMessage . '</div>';
    }

    // Update the bid
    $result = $bid->placeBid($itemId, $userId, $newBid);

    // If bid was updated successfully
    if ($result) {
        //echo "Bid updated successfully!";
        // Redirect back to the previous page
        header("Location: ".$_SERVER['HTTP_REFERER']);
    } else {
        echo "There was an error updating your bid.";
    }
} else {
    echo "No form data received.";
}
?>
