<?php 
// Starts a new session or resumes an existing one.
session_start();

// If a message is set in the session, display it.
if(isset($_SESSION['message'])) {
    echo $_SESSION['message'];
    unset($_SESSION['message']);
}

// Includes the configuration, user,bid and item classes.
include("db_connect.php");
include("user.php");
include("item.php");
include("bid.php");

// Creates a new User object and checks if a user is logged in.
$user = new User($con);

$user_data = $user->checkLogin($con);

// If no user is logged in, redirect to the login page.
if(!$user_data) {
    header("Location: index.php");
    die;
}

// Take user_id as a GET parameter
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $_SESSION['user_id'];

// Check if the user_id matches the one stored in the session (current logged-in user)
if ($user_id != $_SESSION['user_id']) {
    // If user_id is a number, it's possibly a legitimate user_id, so abort the script.
    if (is_numeric($user_id)) {
        die("Unauthorized access.");
    }
}

// Includes the page's header.
include("includes/header.php");

// Gets the current page number from the query string, or defaults to 1 if it's not set.
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Solve pagination issue, get total bids
if (isset($con) && $con) {
    $totalBids = getTotalBids($con);
} else {
    echo "No database connection";
}

// Sets the desired number of items to display per page.
$itemsPerPage = $totalBids;

// Calculate the offset for the SQL query
$offset = ($page - 1) * $itemsPerPage;

$view = isset($_GET['view']) ? $_GET['view'] : 'getUserBids';
switch ($view) {
    case 'getUserBids':
        $result = $user->getUserBids($user_id, $offset, $itemsPerPage, true); // Get the bids received by the user
        $canChangeBid = false;
        break;
    case 'myBids':
        $result = $user->getMyBids($user_id, $offset, $itemsPerPage); // Get the bids placed by the user
        $canChangeBid = true;
        break;
}

// Gets the total number of items the user has placed bids on.
$totalItems = $user->getUserBidsCount($user_data['user_id']);

// Calculate total pages
if ($itemsPerPage == 0) {
    $totalPages = 1; 
} else {
    $totalPages = ceil($totalItems / $itemsPerPage);
}


?> <div class="container">
    <h1 class="mt-2">
        Welcome, <?= htmlspecialchars($user_data['user_name'], ENT_QUOTES, 'UTF-8') ?> (User ID: <?= $user_data['user_id'] ?>)
    </h1>
    <h3 class="mt-2 text-secondary">
        Status: <?= htmlspecialchars($user_data['user_type'], ENT_QUOTES, 'UTF-8') ?>
    </h3>

    
    <form method="GET" action="">
        <select name="view" id="view" onchange="this.form.submit()">
            <option value="getUserBids" <?= isset($_GET['view']) && $_GET['view'] === 'getUserBids' ? 'selected' : '' ?>>Received Bids</option>
            <option value="myBids" <?= isset($_GET['view']) && $_GET['view'] === 'myBids' ? 'selected' : '' ?>>Placed Bids</option>
        </select>
    </form>

    <!-- Show the "Show Ranking" form only if the user is PREMIUM -->
    <form method="post" action="" class="form-padding">
        <input type="text" name="item_id" id="item_id" placeholder="Item id" required>
        <!-- Show the private key input field only if the user is PREMIUM -->
        <?php if($user_data['user_type'] === 'PREMIUM') : ?>
            <input type="text" name="private_key" id="private_key" placeholder="Enter your private key" required>
        <?php endif; ?>
        <input type="submit" name="decrypt_and_rank" value="Show Ranking">
    </form>



    <!-- Ranking for a specific item -->
    <?php
    if (isset($_POST['decrypt_and_rank'])) {
        $item_id = isset($_POST['item_id']) ? $_POST['item_id'] : null;
        $private_key = isset($_POST['private_key']) ? $_POST['private_key'] : null;

        // Initialize a Bid object
        $bid = new Bid($con);

        // Retrieve and decrypt bids for a specific item
        $decrypted_and_ranked_bids = $user->decryptAndRankUserBids($user_data['user_id'], $item_id, $private_key, $bid, $user_data['user_type']);
        echo "<h3>Your Ranked Bids</h3>";
        echo "<ul style='list-style-type: none;'>";
        foreach($decrypted_and_ranked_bids as $index => $bid) {
            $rank = $index + 1; // rank is index + 1 because index is 0-based
            echo "<li>Rank: " . $rank . " - Item ID: " . $bid['item_id'] . " - Bidder ID: " . $bid['bidder_id'] . " - Bid Amount: " . $bid['amount'] . "</li>";
        }
        echo "</ul>";
    }
    ?>

    
<?php if ($result->num_rows > 0) {
        echo '<table class="table table-striped">';
        echo '<thead>';
        echo '<tr>
                <th scope="col">Item ID</th>
                <th scope="col">Item Name</th>
                <th scope="col">Start Price</th>
                <th scope="col">Item Type</th>
                <th scope="col">Item Created At</th>';

       if($canChangeBid){
            echo '<th scope="col">Creator ID</th>';
        } else {
            echo '<th scope="col">Bidder ID</th>';
        }
        echo '<th scope="col">Bid Created At</th>
              <th scope="col">Bid Amount</th> 
              <th scope="col">Action</th> 
              </tr>';
        echo '</thead>';

        echo '<tbody>';
        while ($row = $result->fetch_assoc()) {
            $highlightClass = ($user_data['user_type'] === 'PREMIUM' && $row['item_type'] === 'PREMIUM') ? 'table-warning' : '';
            echo '<tr class="' . $highlightClass . '">'
            . '<td class="item-id">' . $row['item_id'] . '</td>'
            . '<td class="item-name">' . $row['item_name'] . '</td>'
            . '<td class="start-price">' . $row['start_price'] . '</td>'
            . '<td class="item-type">' . $row['item_type'] . '</td>'
            . '<td>' . date('Y-m-d H:i:s', strtotime($row['item_created_at'])) . '</td>';
            if ($canChangeBid) {
                echo '<td class="bidder-id">' . $row['creator_id'] . '</td>';
            }
            else {
                echo '<td class="creator-id">' . $row['bidder_id'] . '</td>';
            }
            echo '<td>' . date('Y-m-d H:i:s', strtotime($row['bid_created_at'])) . '</td>';
            $amount = $row['bid_amount'];
            $chunks = str_split($amount, 60);
            echo '<td class="bid-amount">';
            foreach ($chunks as $chunk) {
                echo '<div>' . $chunk . '</div>';
            }
            echo '</td>';
            // Form to change the bid amount
            if($canChangeBid){
                echo '<td>
                <form action="change_bid.php" method="post">
                    <input type="hidden" name="item_id" value="' . $row['item_id'] . '">
                    <input type="hidden" name="user_id" value="' . $user_data['user_id'] . '">
                    <input type="text" name="new_bid" min="0" placeholder="New bid" required>
                    <input type="submit" value="Change Bid" class="btn btn-primary">
                </form>
                </td>';
            }
            echo '<td>';
            // Show the "Decrypt Bid" form only if the user is PREMIUM
            if ($user_data['user_type'] === 'PREMIUM' && $canChangeBid === false) {
                echo '<td>
                <form action="decrypt_bid.php" method="post">
                    <input type="hidden" name="item_id" value="' . $row['id'] . '">
                    <input type="hidden" name="user_id" value="' . $user_data['user_id'] . '">
                    <input type="hidden" name="amount" value="' . $row['bid_amount'] . '">
                    <input type="password" name="private_key_d" placeholder="Enter your private key" required>
                    <input type="submit" value="Show Bid" class="btn btn-primary">
                </form>
                </td>';
            } else if ($user_data['user_type'] === 'REGULAR' && $canChangeBid === false) { // Add a Demo Action button for regular users
                echo '<button type="button" class="btn btn-secondary" disabled>Demo Action</button>';
            }
            echo '</td>';
            echo '</tr>';
        }
            echo '</tbody>';
            echo '</table>';
        } 
        else 
        {
            echo "<div class='alert alert-warning' role='alert'>No items found.</div>";
        }


        echo '<nav class="pagination-nav" aria-label="Page navigation">';
        echo '<ul class="pagination justify-content-center">';
        if ($page > 1) {
            echo '<li class="page-item"><a class="page-link" href="?page=' . ($page - 1) . '">Previous</a></li>';
        } else {
            echo '<li class="page-item"></li>'; // Add an empty element for
                // alignment
            }
            // Page numbers
            for ($i = 1; $i <= $totalPages; $i++) {
                echo '<li class="page-item' . ($i == $page ? ' active' : '') . '"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
            }
            // Next page link
            if ($page < $totalPages) {
                echo '<li class="page-item"><a class="page-link" href="?page=' . ($page + 1) . '">Next</a></li>';
            }
            echo '</ul>';
            echo '</nav>';
?>
</div> <?php 
        // Includes the page's footer.
        include("includes/footer.php"); 
    ?> </body>
</html>