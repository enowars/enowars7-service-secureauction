<?php
session_start();
include("config.php");
include("user.php");
include("item.php");
include("bid.php");

// Create a User object
$user = new User($con);
// Check if the user is logged in
$user_data = $user->checkLogin($con);

// Create an Item object
$item = new Item($con);
// Get the item ID from the URL
$item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
// Get the item details
$item_details = $item->getItemDetails($item_id);

// Create a Bid object
$bid = new Bid($con);
// Get the highest bid for this item
$highest_bid = $bid->getHighestBid($item_id);

include("includes/header.php");
?>

<div class="container">
    <h1 class="mt-4 mb-4">Item Tesrt Details</h1>
    <?php
    if ($item_details) {
        echo '<h2>' . $item_details['name'] . '</h2>';
        echo '<p>' . $item_details['description'] . '</p>';
        echo '<p>Starting Price: ' . $item_details['start_price'] . '</p>';
        echo '<p>Highest Bid: ' . ($highest_bid ? $highest_bid : 'No bids yet') . '</p>';
        echo '<form method="POST" action="place_bid.php">';
        echo '<input type="hidden" name="item_id" value="' . $item_id . '">';
        echo '<input type="number" name="bid_amount" placeholder="Your bid">';
        echo '<button type="submit" class="btn btn-primary">Place Bid</button>';
        echo '</form>';
    } else {
        echo "<div class='alert alert-warning' role='alert'>Item not found.</div>";
    }
    ?>
</div>

<?php 
include("includes/footer.php"); 
?>

</body>
</html>
