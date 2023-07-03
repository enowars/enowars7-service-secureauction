<?php
session_start();
include("db_connect.php");
include "user.php";
include "item.php";
include "bid.php";

// Check if the user is logged in
$user = new User($con);
$user_data = $user->checkLogin($con);

// If the user is not logged in, redirect to the login page
if (!$user_data) {
    header("Location: login.php");
    exit();
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $item_name = $_POST["item_name"];
    $start_price = $_POST["start_price"];
    $item_type = $_POST["item_type"];

        // Validate form data
        if (empty($item_name)) {
            $error_message = "Item name cannot be empty. You entered: '{$item_name}'";
        } elseif ($start_price === '' || $start_price === null || $start_price < 0) {
            $error_message = "Start price cannot be empty or negative. You entered: '{$start_price}'";
        } elseif (!in_array($item_type, ['REGULAR', 'PREMIUM'])) {
            $error_message = "Item type must be either 'REGULAR' or 'PREMIUM'. You entered: '{$item_type}'";
        }  else {
        // Check if the user is trying to create a premium item but is not a premium user
        if ($item_type == 'PREMIUM' && $user_data['user_type'] != 'PREMIUM') {
            $error_message = "Regular users cannot create premium items.";
        } else {
            // Create a new Item object
            $item = new Item($con);
            // Create the item
            $result = $item->createItemWithBid(
                $user_data["user_id"],
                $item_name,
                $start_price,
                $item_type
            );

            if ($result) {
                // Item created successfully, redirect to the user's profile page
                $item_id = $result['itemId'];
                if (isset($result['encryptedAmount'])) {
                    // If an encryptedAmount was returned, include it in the redirect
                    $encryptedAmount = $result['encryptedAmount'];
                    header("Location: item_detail.php?id=$item_id&encryptedAmount=$encryptedAmount");
                } else {
                    // If no encryptedAmount was returned, redirect without it
                    header("Location: item_detail.php?id=$item_id");
                }
                exit();
            } else {
                $error_message = "Failed to create the item. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Item</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style type="text/css">
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: royalblue;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Create Item
                    </div>
                    <div class="card-body">
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        <form method="post">
                            <div class="form-group">
                                <label for="item_type">Item Type</label>
                                <select id="item_type" class="form-control" name="item_type">
                                    <option value="REGULAR">Regular</option>
                                    <option value="PREMIUM">Premium</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="item_name">Item Name</label>
                                <input id="item_name" class="form-control" type="text" name="item_name">
                            </div>
                            <div class="form-group">
                                <label for="start_price">Start Price</label>
                                <input id="start_price" class="form-control" type="text" name="start_price">
                            </div>
                            <button class="btn btn-primary" type="submit">Create</button>
                            <a class="btn btn-secondary" href="my_profile.php">Back to Profile</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
