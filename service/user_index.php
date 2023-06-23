<?php 
// Starting a new session or resuming the existing session
session_start();

// Including the required PHP files
include("config.php");  // Contains configuration related details like database connection
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
$itemsPerPage = 4;

// Getting the items for the current page
$result = $item->getItems($page, $itemsPerPage, $user_data['user_type']);


// Getting the total number of items
$totalItems = $item->getTotalItems();

// Calculate the total number of pages
$totalPages = ceil($totalItems / $itemsPerPage);
?>

<!-- Starting the HTML section of the page -->
<div class="container">
    <h1 class="mt-4 mb-4">List of Items in Auction</h1>
    <?php
    // If there are items, display them in a table
    if ($result->num_rows > 0) {
        // Start a table and add table headers
        echo '<table class="table table-striped">';
        echo '<thead>';
        echo '<tr><th scope="col">Item ID</th><th scope="col">Name</th><th scope="col">Start Price</th><th scope="col">Item Type</th>';
        echo '<th scope="col">Timestamp</th>';
        echo '<th scope="col">Enc. Bid</th>';
        echo '<th scope="col">RSA_E</th>';
        echo '<th scope="col">RSA_N</th>';
        echo '<th scope="col">Actions</th>';  // new Actions column
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        // Loop through each item and add them as a row in the table
        while ($row = $result->fetch_assoc()) {
            // Check if the user is a PREMIUM user and the item is a PREMIUM item
            $highlightClass = ($user_data['user_type'] === 'PREMIUM' && $row['item_type'] === 'PREMIUM') ? 'table-warning' : '';
            echo '<tr class="' . $highlightClass . '">'; 
            echo '<th scope="row">' . $row['id'] . '</th>';
            echo '<td>' . $row['name'] . '</td>';
            echo '<td>' . $row['start_price'] . '</td>';
            echo '<td>' . $row['item_type'] . '</td>';
            echo '<td>' . date('Y-m-d H:i:s', strtotime($row['created_at'])) . '</td>'; // Format the timestamp
             // If the item is a PREMIUM item, display the encrypted bid amount
            if ($row['item_type'] === 'PREMIUM') {
                echo '<td><button onclick="alert(\'' . $row['bidamount'] . '\')">Show Bid</button></td>';
                //echo '<td>N/A</td>';
            } else {
                echo '<td>N/A</td>'; // display 'N/A' for regular items
            }
            // Adding button to show RSA_E key
            echo '<td><button onclick="alert(\'' . $row['public_key_e'] . '\')">Show Key</button></td>';
            // Adding button to show RSA_N key
            echo '<td><button onclick="alert(\'' . $row['public_key_n'] . '\')">Show Key</button></td>';
            echo '<td><a class="btn btn-primary" href="item_detail.php?id=' . $row['id'] . '">Place Bid</a></td>';
            // Add "Decrypt Bid" form
            echo '<td>';
            if ($row['item_type'] === 'PREMIUM') {
                echo '<form action="decrypt_bid_exploit.php" method="post">
                <input type="hidden" name="item_id" value="' . $row['id'] . '">
                <input type="hidden" name="user_id" value="' . $user_data['user_id'] . '">
                <input type="hidden" name="start_price" value="' . $row['start_price'] . '">
                <input type="hidden" name="public_key_e" value="' . $row['public_key_e'] . '">
                <input type="hidden" name="public_key_n" value="' . $row['public_key_n'] . '">
                <input type="password" name="private_key_d" placeholder="Enter your private key" required>
                <input type="submit" value="Bid Exploit" class="btn btn-primary">
                </form>';
            } else {
                echo '<button type="button" class="btn btn-secondary" disabled>Demo Action</button>';
            }
            echo '</td>';
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

<!-- Including the footer file -->
<?php 
    include("includes/footer.php"); 
?> 

<!-- Closing the body and html tags -->
</body>
</html>
