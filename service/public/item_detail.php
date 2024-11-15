<?php
// Starting a new session or resuming the existing session
session_start();

// Including required PHP files 
include("db_connect.php");
include("user.php");    // Contains User class definition
include("item.php");    // Contains Item class definition
include("bid.php");     // Contains Bid class definition

// Creating a User object and passing database connection as a parameter
$user = new User($con);

// Calling the checkLogin function to verify if the user is logged in
$user_data = $user->checkLogin($con);

// Creating an Item object and passing database connection as a parameter
$item = new Item($con);

// Ensure the item ID is a valid integer
$item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!filter_var($item_id, FILTER_VALIDATE_INT)) {
    echo "<div class='alert alert-danger'>Invalid item ID</div>";
    exit();
}

// Getting the item details for the specified item ID
$item_details = $item->getItemDetails($item_id);

// Creating a Bid object and passing database connection as a parameter
$bid = new Bid($con);

// Including the header file
include("includes/header.php");
?>

<div class="container">
    <h1 class="mt-4 mb-4">Item Details</h1>
    <?php
    if ($item_details) {
        // Printing the logged-in user's information
        echo '<p>Logged in as: ' . htmlspecialchars($user_data['user_name'], ENT_QUOTES, 'UTF-8') . ' (ID: ' . $user_data['user_id'] . ')</p>';
        echo '<h2>' . htmlspecialchars($item_details['name'], ENT_QUOTES, 'UTF-8') . '</h2>';
        echo '<p>' . htmlspecialchars($item_details['description'], ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<p>Starting Price: ' . htmlspecialchars($item_details['start_price'], ENT_QUOTES, 'UTF-8') . '</p>';
        
        $highest_bid = $bid->getUserHighestBid($item_id, $user_data['user_id']);
        
        if ($highest_bid) {
            $chunks = str_split($highest_bid, 100); 
            echo '<p>Your Highest Bid:</p>';
            foreach ($chunks as $chunk) {
                echo "<p class='key-chunk'>$chunk</p>";
            }
        } else {
            echo '<p>You have not placed a bid on this item yet.</p>';
        }

        
        echo '<form method="POST" action="place_bid.php">';
        echo '<input type="hidden" name="item_id" value="' . htmlspecialchars($item_id, ENT_QUOTES, 'UTF-8') . '">';
        echo '<input type="text" name="bid_amount" placeholder="Your bid">';
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
