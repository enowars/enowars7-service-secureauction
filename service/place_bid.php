<?php
// Start session
session_start();

// Include necessary files
include("config.php");
include("user.php");
include("item.php");
include("bid.php");

// Create a User object
$user = new User($con);

// Check if the user is logged in
$user_data = $user->checkLogin($con);

// If the user is not logged in, redirect to the login page
if (!$user_data) {
    header("Location: login.php");
    exit;
}

// Create an Item object
$item = new Item($con);

// Get the item ID from the POST data
$item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;

// Get the item details
$item_details = $item->getItemDetails($item_id);

// If the item does not exist, redirect to the item list page
if (!$item_details) {
    header("Location: items.php");
    exit;
}

// Create a Bid object
$bid = new Bid($con);

// Get the highest bid for this item
$highest_bid = $bid->getHighestBid($item_id);

// Fetch current user's highest bid for this item
$current_user_highest_bid = $bid->getUserHighestBid($item_id, $user_data['id']);

// Get the bid amount from the POST data
$bid_amount = isset($_POST['bid_amount']) ? floatval($_POST['bid_amount']) : 0.0;

// If the bid amount is less than the starting price, redirect back to the item details page
if ($bid_amount < $item_details['start_price']) {
    header("Location: item_detail.php?id=$item_id&error=low_bid");
    exit;
}

// Place the bid
$bid->placeBid($item_id, $user_data['id'], $bid_amount);

// Redirect back to the item details page
header("Location: item_detail.php?id=$item_id&success=bid_placed");
exit;
?>
