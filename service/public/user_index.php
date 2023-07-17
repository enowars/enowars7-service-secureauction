<?php 
// Starting a new session or resuming the existing session
session_start();

// Including the required PHP files
include("db_connect.php");
include("user.php");    // Contains User class definition
include("item.php");    // Contains Item class definition


// Creating a User object and passing database connection as a parameter
$user = new User($con);

// Checking if the user is logged in, if logged in it returns user data, else redirects to login page
$user_data = $user->checkLogin($con);

// Including the header file
include("includes/header.php");

// Creating an Item object and passing database connection as a parameter
$item = new Item($con);

// Getting the current page number from the URL or setting it to 1 if it's not set
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Setting the number of items to be displayed per page
$itemsPerPage = 5;

// If form is submitted, get the search results, else get the items for the current page
if (isset($_POST['submit'])) {
    $item_name = isset($_POST['item_name']) ? $_POST['item_name'] : null;
    $item_id = isset($_POST['item_id']) ? $_POST['item_id'] : null;
    $result = $item->getSearchedItems($user_data['user_type'], $item_name, $item_id);
} else {
    $result = $item->getItems($page, $itemsPerPage, $user_data['user_type']);
}

// Getting the total number of items
//$totalItems = $item->getTotalItems();
$totalItems = $item->getTotalItems($user_data['user_type']);

// Calculate the total number of pages
$totalPages = ceil($totalItems / $itemsPerPage)+1;
?>

<!-- Starting the HTML section of the page -->
<div class="container">
    <h1 class="mt-4 mb-4  pb-1">
        Welcome: <?= htmlspecialchars($user_data['user_name'], ENT_QUOTES, 'UTF-8') ?>
        (User ID: <?= htmlspecialchars($user_data['user_id'], ENT_QUOTES, 'UTF-8') ?>)
    </h1>
    <h3 class="mt-1 pb-2 text-secondary">
        User Type: <?= htmlspecialchars($user_data['user_type'], ENT_QUOTES, 'UTF-8') ?>
    </h3>
    
    <form method="post" action="" class="form-padding">
        <input type="text" name="item_name" placeholder="Item name">
        <input type="text" name="item_id" placeholder="Item id">
        <input type="submit" name="submit" value="Search">
    </form>

    <?php
    // If there are items, display them in a table
    if ($result->num_rows > 0) {
        // Start a table and add table headers
        echo '<table class="table table-striped">';
        echo '<thead>';
        echo '<tr><th scope="col">Item ID</th><th scope="col">Item Name</th><th scope="col">Start Price</th><th scope="col">Item Type</th>';
        echo '<th scope="col">Time Remaining</th>';
        echo '<th scope="col">Creator ID</th>';  
        echo '<th scope="col">Bidder ID</th>';  
        echo '<th scope="col">Actions</th>';  
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        // Array to store creation times
        $creationTimes = array();
        // Loop through each item and add them as a row in the table
        while ($row = $result->fetch_assoc()) {
            //$highlightClass = ($user_data['user_type'] === 'PREMIUM' && $row['item_type'] === 'PREMIUM') ? 'table-warning' : '';
            //echo '<tr class="' . $highlightClass . '">'; 
            echo '<th scope="row">' . $row['id'] . '</th>';
            echo '<td>' . $row['name'] . '</td>';
            echo '<td>' . $row['start_price'] . '</td>';
            echo '<td>' . $row['item_type'] . '</td>';
            // Fetch and store the creation time for each item
            $createdAtUtc = date('c', strtotime($row['created_at']));
            // Store the creation time in the array
            $creationTimes[$row['id']] = $createdAtUtc;
            echo '<td id="timer_' . $row['id'] . '">' . $createdAtUtc . '</td>'; 

            echo '<td>' . $row['creator_id'] . '</td>';
            echo '<td>' . (isset($row['bidder_id']) ? $row['bidder_id'] : 'N/A') . '</td>'; // Display the bidder ID or 'N/A' if there's no bidder
           
            // Add "Decrypt Bid" form
            echo '<td>';
            if ($user_data['user_type'] === 'PREMIUM') {
                // "More Info" form
                echo '<form action="item_info.php" method="post" data-item-id="' . $row['id'] . '" data-name="' . $row['name'] . '" data-user-id="' . $user_data['user_id'] . '" data-start-price="' . $row['start_price'] . '" data-bidamount="' . $row['bidamount'] . '" data-public-key-e="' . $row['public_key_e'] . '" data-public-key-n="' . $row['public_key_n'] . '">
                <input type="hidden" name="item_id" value="' . $row['id'] . '">
                <input type="hidden" name="name" value="' . $row['name'] . '">
                <input type="hidden" name="user_id" value="' . $user_data['user_id'] . '">
                <input type="hidden" name="start_price" value="' . $row['start_price'] . '">
                <input type="hidden" name="bidamount" value="' . $row['bidamount'] . '">
                <input type="hidden" name="public_key_e" value="' . $row['public_key_e'] . '">
                <input type="hidden" name="public_key_n" value="' . $row['public_key_n'] . '">
                <input type="submit" value="More Info" class="btn btn-danger">
                </form>';
            } else {
                echo '<button type="button" class="btn btn-secondary" disabled>Demo Action</button>';
            }
            echo '</td>';
            // "Place Bid" button
            echo '<td><a class="btn btn-success" href="item_detail.php?id=' . $row['id'] . '">Place Bid</a></td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        // If there are no items, display an error message
        echo "<div class='alert alert-warning' role='alert'>No items found.</div>";
    }

    // Displaying the pagination links
    echo '<nav class="pagination-nav" aria-label="Page navigation">';
    echo '<ul class="pagination justify-content-center">';
    // Previous page link
    if ($page > 1) {
        echo '<li class="page-item"><a class="page-link" href="?page=' . ($page - 1) . '">Previous</a></li>';
    } else {
        echo '<li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>';
    }
    // Page numbers
    $start = max(1, $page - 2);
    $end = min($totalPages, $page + 2);
    for ($i = $start; $i <= $end; $i++) {
        echo '<li class="page-item' . ($i == $page ? ' active' : '') . '"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
    }
    // Next page link
    if ($page < $totalPages) {
        echo '<li class="page-item"><a class="page-link" href="?page=' . ($page + 1) . '">Next</a></li>';
    } else {
        echo '<li class="page-item disabled"><a class="page-link" href="#">Next</a></li>';
    }
    echo '</ul>';
    echo '</nav>';


    ?>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript">
    // Trigger function once the document is fully loaded
    $(document).ready(function() {
        // Convert the PHP creationTimes array into a JavaScript array
        var creationTimes = <?php echo json_encode($creationTimes) ?>;

        // For each item in the creationTimes array, start a timer
        for (var itemId in creationTimes) {
            console.log("Raw creation time for item " + itemId + ": " + creationTimes[itemId]);  // Add this line
            startTimer(itemId, creationTimes[itemId]);
        }
    });


    function startTimer(itemId, createdAtUtc) {
        // Convert the item's creation time to a moment.js date object
        var createdAt = moment.utc(createdAtUtc);

        // Set the time when the auction expires (10 minutes after creation)
        var expiresAt = moment(createdAt).add(10, 'minutes');

        // For debugging, print out the creation time and expiry time for each item
        console.log("Creation time for item " + itemId + ": " + createdAt.format());
        console.log("Expiry time for item " + itemId + ": " + expiresAt.format());

        // Start a timer that updates every second
        var timerId = setInterval(function() {
            // Get the current time in UTC
            var now = moment.utc();

            // For debugging, print out the current time at each update
            console.log("Current time: " + now.format());

            // Calculate how much time remains until the auction expires
            var duration = moment.duration(expiresAt.diff(now));

            // Get the remaining minutes and seconds
            var mins = Math.floor(duration.asMinutes());
            var secs = Math.floor(duration.seconds());

            // For debugging, print out the remaining time at each update
            console.log("Remaining time for item " + itemId + ": " + mins + " mins and " + secs + " secs.");

            // Pad the minutes and seconds with a leading zero if they are less than 10
            var minsString = (mins < 10 ? '0' : '') + mins;
            var secsString = (secs < 10 ? '0' : '') + secs;

            // Update the HTML element with the remaining time
            $('#timer_' + itemId).html(minsString + ":" + secsString);

            // If the current time is the same or later than the expiry time
            if (now.isSameOrAfter(expiresAt)) {
                // Stop the timer
                clearInterval(timerId);

                // Update the HTML element to show that the auction is closed
                $('#timer_' + itemId).html("Closed");
            }
        }, 1000);  // Timer updates every 1000 milliseconds (1 second)
    }

</script>

<!-- Including the footer file -->
<?php 
    include("includes/footer.php"); 
?> 

<!-- Closing the body and html tags -->
</body>
</html>
