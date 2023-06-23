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
$current_user_highest_bid = $bid->getUserHighestBid($item_id, $user_data['user_id']);

// Get the bid amount from the POST data
$bid_amount = isset($_POST['bid_amount']) ? $_POST['bid_amount'] : '';

// If the bid amount is empty, redirect back to the item details page
if (empty($bid_amount)) {
    header("Location: item_detail.php?id=$item_id&error=empty_bid");
    exit;
}

// Check if the bid amount is negative
if ($bid_amount < 0) {
    // Redirect back to the item details page with an error message
    header("Location: item_detail.php?id=$item_id&error=negative_bid");
    exit;
}

// Check if the bid amount is negative
if ($bid_amount < $item_details['start_price']) {
    // Redirect back to the item details page with an error message
    header("Location: item_detail.php?id=$item_id&error=bid_less_than_starting_price");
    exit;
}

$encrypted_bid = $bid->placeBid($item_id, $user_data['user_id'], $bid_amount);

if ($user_data['user_type'] === 'PREMIUM') {
    // Output a success message and the encrypted bid in JSON format for PREMIUM users
    header('Content-Type: application/json');
    echo json_encode(array('success' => true, 'encrypted_bid' => $encrypted_bid));
    exit;
} else {
    // Redirect back to the item details page for non-PREMIUM users
    header("Location: item_detail.php?id=$item_id&success=bid_placed");
    exit;
}

?>