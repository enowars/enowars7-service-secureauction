<?php 
session_start();
include("config.php");
include("user.php");
include("item.php");

$user = new User($con);
$user_data = $user->checkLogin($con);

include("includes/header.php");


$item = new Item($con);
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = 5; // Change this to the desired number of items per page
$result = $item->getItems($page, $itemsPerPage);
$totalItems = $item->getTotalItems();

?>  
<div class="container">
    <h1 class="mt-4 mb-4">List of Items in Auction</h1>
    <?php
    if ($result->num_rows > 0) {
        echo '<table class="table table-striped">';
        echo '<thead>';
        echo '<tr><th scope="col">Item ID</th><th scope="col">Name</th><th scope="col">Start Price</th><th scope="col">Action</th></tr>';
        echo '</thead>';
        echo '<tbody>';
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<th scope="row">' . $row['id'] . '</th>';
            echo '<td>' . $row['name'] . '</td>';
            echo '<td>' . $row['start_price'] . '</td>';
            echo '<td><a class="btn btn-primary" href="item_detail.php?id=' . $row['id'] . '">Place Bid</a></td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo "<div class='alert alert-warning' role='alert'>No items found.</div>";
    }

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
    include("includes/footer.php"); 
?> 
</body>
</html>
