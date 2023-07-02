<?php 
include("config.php");
include("user.php");
include("item.php");
include("bid.php");

// Get keys and identifiers from POST request
$private_key_d = str_replace(array("\n", "\r", " "), '', $_POST['private_key_d']);
$item_id = $_POST['item_id'];
$user_id = $_POST['user_id']; // Not used
$public_key_e = $_POST['public_key_e'];
$public_key_n = $_POST['public_key_n'];
$name = $_POST['name'];

// Check if amount is set in POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['bidamount'])) {
        $amount = $_POST['bidamount'];
        //echo "Amount received: " . $amount;
    } else {
        echo "No amount received <br>";  
        var_dump($_POST);      
    }
}

// Create a new User and Bid object
$user = new User($con);
$bid = new Bid($con);

// Prepare keys
$keys = [
    'private_key_d' => $private_key_d,
    'public_key_n' => $public_key_n,
];

// Perform bid decryption
$decrypted_bid = $bid->decryptBid($amount, $keys);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Decrypt Bid</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-4 mb-4">Item Details</h1>
        <div class="card mb-4">
            <div class="card-header">
                Item Name:
            </div>
            <div class="card-body">
                <p class="card-text"><?= $name ?></p>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header">
                Item Id:
            </div>
            <div class="card-body">
                <p class="card-text"><?= $item_id ?></p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                Encrypted Bid:
            </div>
            <div class="card-body">
                <p class="card-text"><?= $amount ?></p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                Public Key: e
            </div>
            <div class="card-body">
                <p class="card-text"><?= $public_key_e ?></p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                Public Key: n
            </div>
            <div class="card-body">
                <p class="card-text"><?= $public_key_n ?></p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                Private Key: d 
            </div>
            <div class="card-body">
                <p class="card-text"><?= $private_key_d ?></p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                Decrypted Bid:
            </div>
            <div class="card-body">
                <p class="card-text"><?= $decrypted_bid ?></p>
            </div>
        </div>
        
    </div>
</body>
</html>