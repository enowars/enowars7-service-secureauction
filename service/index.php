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
$itemsPerPage = 5;

// Getting the items for the current page
$result = $item->getItems($page, $itemsPerPage);

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
        // Starting a table and adding table headers
        echo '<table class="table table-striped">';
        echo '<thead>';
        echo '<tr><th scope="col">Item ID</th><th scope="col">Name</th><th scope="col">Start Price</th><th scope="col">Action</th></tr>';
        echo '</thead>';
        echo '<tbody>';
        // Looping through each item and adding them as a row in the table
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<th scope="row">' . $row['id'] . '</th>';
            echo '<td>' . $row['name'] . '</td>';
            echo '<td>' . $row['start_price'] . '</td>';
            echo '<td><a class="btn btn-primary" href="item_detail.php?id=' . $row['id'] . '">Place Bid</a></td>';
            echo '</tr>';
        }
        // Closing the table body and table tags
        echo '</tbody>';
        echo '</table>';
    } else {
        // If there are no items, display an error message
        echo "<div class='alert alert-warning' role='alert'>No items found.</div>";
    }

    // Displaying the pagination links
echo '<nav class="pagination-nav" aria-label="Page navigation">';
echo '<ul class="pagination">';
// Previous page link
if ($page > 1) {
    echo '<li class="page-item"><a class="page-link" href="?page=' . ($page - 1) . '">Previous</a></li>';
} else {
    echo '<li class="page-item"></li>'; // Add an empty element for alignment
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
</div>

<!-- Including the footer file -->
<?php 
    include("includes/footer.php"); 
?> 

<!-- Closing the body and html tags -->
</body>
</html>

