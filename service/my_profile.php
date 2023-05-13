<?php 
// Starts a new session or resumes an existing one.
// This is required to access session variables.
session_start();

// Includes the configuration, user, and item classes.
include("config.php");
include("user.php");
include("item.php");

// Creates a new User object and checks if a user is logged in.
// The checkLogin method returns the user's data if a user is logged in, and redirects to the login page if not.
$user = new User($con);
$user_data = $user->checkLogin($con);

// Includes the page's header.
include("includes/header.php");

// Gets the current page number from the query string, or defaults to 1 if it's not set.
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Sets the desired number of items to display per page.
$itemsPerPage = 5;

// Gets the items that the user has placed bids on.
// This method returns a MySQLi result object.
$result = $user->getUserItemsWithBids($user_data['user_id'], $page, $itemsPerPage);

// Gets the total number of items the user has placed bids on.
$totalItems = $user->getUserBidsCount($user_data['user_id']);
?>  

<div class="container">
    <h1 class="mt-4 mb-4">Logged in as  <?= $user_data['user_name'] ?></h1>

    <?php
    // If the user has placed bids on items, it displays them in a table.
    if ($result->num_rows > 0) {
        echo '<table class="table table-striped">';
        echo '<thead>';
        echo '<tr><th scope="col">Item ID</th><th scope="col">Name</th><th scope="col">Start Price</th><th scope="col">Created At</th><th scope="col">Bid Amount</th></tr>';
        echo '</thead>';
        echo '<tbody>';
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<th scope="row">' . $row['id'] . '</th>';
            echo '<td>' . $row['name'] . '</td>';
            echo '<td>' . $row['start_price'] . '</td>';
            echo '<td>' . $row['created_at'] . '</td>';
            echo '<td>' . $row['amount'] . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        // If the user hasn't placed any bids, it displays a warning message.
        echo "<div class='alert alert-warning' role='alert'>No items found.</div>";
    }

    // Prints pagination links.
    echo '<nav class="pagination-nav" aria-label="Page navigation">';
    echo '<ul class="pagination">';
    for ($i = 1; $i <= ceil($totalItems / $itemsPerPage); $i++) {
        echo '<li class="page-item' . ($i == $page ? ' active' : '') . '"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
    }
    echo '</ul>';
    echo '</nav>';
    ?>
</div>

<?php 
    // Includes the page's footer.
    include("includes/footer.php"); 
?> 
</body>
</html>
