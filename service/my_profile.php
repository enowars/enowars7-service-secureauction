<?php 
// Starts a new session or resumes an existing one.
session_start();

// Includes the configuration, user, and item classes.
include("config.php");
include("user.php");
include("item.php");

// Creates a new User object and checks if a user is logged in.
$user = new User($con);
$user_data = $user->checkLogin($con);

// Includes the page's header.
include("includes/header.php");

// Gets the current page number from the query string, or defaults to 1 if it's not set.
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Sets the desired number of items to display per page.
$itemsPerPage = 3;

// Calculate the offset for the SQL query
$offset = ($page - 1) * $itemsPerPage;

// Get the items that the user has placed bids on.
$result = $user->getUserBids($user_data['user_id'], $offset, $itemsPerPage);

// Gets the total number of items the user has placed bids on.
$totalItems = $user->getUserBidsCount($user_data['user_id']);

// Calculate total pages
$totalPages = ceil($totalItems / $itemsPerPage);
?> <div class="container">
    <h1 class="mt-4 mb-4">Logged in as <?= $user_data['user_id'] ?> - <?= $user_data['user_name'] ?></h1> <?php
    if ($result->num_rows > 0) {
        echo '<table class="table table-striped">';
        echo '<thead>';
        echo '<tr><th scope="col">Item ID</th><th scope="col">Item Name</th><th scope="col">Start Price</th><th scope="col">Created At</th><th scope="col">Bid Amount</th></tr>';
        echo '</thead>';
        echo '<tbody>';
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row['id'] . '</td>';
            echo '<td>' . $row['name'] . '</td>';
            echo '<td>' . $row['start_price'] . '</td>';
            echo '<td>' . date('Y-m-d H:i:s', strtotime($row['created_at'])) . '</td>'; // Format the timestamp
            echo '<td>' . $row['amount'] . '</td>';
            echo '<td>
            <form action="change_bid.php" method="post">
                <input type="hidden" name="item_id" value="' . $row['id'] . '">
                <input type="hidden" name="user_id" value="' . $user_data['user_id'] . '">
                <input type="text" name="new_bid" min="0" required>
                <input type="submit" value="Change Bid" class="btn btn-primary">
            </form>
            </td>';
            echo '</tr>';
        }
        
            echo '</tbody>';
            echo '</table>';
        } else {
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