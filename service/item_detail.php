<?php
// Starting a new session or resuming the existing session
session_start();

// Including required PHP files 
include("config.php");  // Contains configuration related details like database connection
include("user.php");    // Contains User class definition
include("item.php");    // Contains Item class definition
include("bid.php");     // Contains Bid class definition

// Creating a User object and passing database connection as a parameter
$user = new User($con);

// Calling the checkLogin function to verify if the user is logged in
// If logged in, it returns user data, else redirects to login page
$user_data = $user->checkLogin($con);

// Creating an Item object and passing database connection as a parameter
$item = new Item($con);

// Getting the item ID from the URL or setting it to 0 if it's not set
$item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Getting the item details for the specified item ID
$item_details = $item->getItemDetails($item_id);

// Creating a Bid object and passing database connection as a parameter
$bid = new Bid($con);

// Including the header file
include("includes/header.php");
?>

<!-- Starting the HTML section of the page -->
<div class="container">
    <h1 class="mt-4 mb-4">Item Details</h1>
    <?php
    // If item details are available, display them
    if ($item_details) {
        // Displaying the item name, description, and starting price
        echo '<h2>' . $item_details['name'] . '</h2>';
        echo '<p>' . $item_details['description'] . '</p>';
        echo '<p>Starting Price: ' . $item_details['start_price'] . '</p>';
        // Get the highest bid for the item by the user
        $highest_bid = $bid->getUserHighestBid($item_id, $user_data['user_id']);
        
        if ($highest_bid) {
            // If there is a highest bid, display it
            echo '<p>Your Highest Bid: ' . $highest_bid . '</p>';
        } else {
            // If there is no highest bid, display a message indicating no bids have been placed
            echo '<p>You have not placed a bid on this item yet.</p>';
        }
        
        // Displaying a form to place a bid
        echo '<form method="POST" action="place_bid.php">';
        echo '<input type="hidden" name="item_id" value="' . $item_id . '">';
        echo '<input type="number" name="bid_amount" placeholder="Your bid">';
        echo '<button type="submit" class="btn btn-primary">Place Bid</button>';
        echo '</form>';
    } else {
        // If item details are not available, display an error message
        echo "<div class='alert alert-warning' role='alert'>Item not found.</div>";
    }
    ?>
</div>

<!-- Including the footer file -->
<?php 
include("includes/footer.php"); 
?>

<!-- Closing the body and html tags -->
</body>
</html>
