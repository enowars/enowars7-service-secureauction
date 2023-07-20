<?php 
include("db_connect.php");
include("user.php");
include("item.php");
include("bid.php");

// Get private key, item id and user id from POST request
$private_key_d = str_replace(array("\n", "\r", " "), '', $_POST['private_key_d']);
$item_id = $_POST['item_id'];
$user_id = $_POST['user_id'];


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['amount'])) {
        $amount = $_POST['amount'];
    } else {
        echo "No amount received <br>";
    }
}

// Create a new User and Bid object
$user = new User($con);
$bid = new Bid($con);


// Get public key components e and n
$publicKey = $user->getPublicKey($user_id);
$public_key_e = $publicKey['public_key_e'];
$public_key_n = $publicKey['public_key_n'];

$chunkSize = 50; // Define the chunk size

// Split keys into chunks for display
$chunksPublicKeyN = str_split($public_key_n, $chunkSize);
$chunksPrivateKeyD = str_split($private_key_d, $chunkSize);



$keys = [
    'private_key_d' => $private_key_d,
    'public_key_n' => $public_key_n,
];

$decrypted_bid = $bid->decryptBid($amount, $keys);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Decrypt Bid</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
            body {
               font-family: 'Comic Sans MS', cursive, sans-serif;
            }
      </style>
</head>
<body>
    <div class="container">
        <h1 class="mt-4 mb-4">Decryption Details</h1>

        <div class="card mb-4">
            <div class="card-header">
                Encrypted Bid
            </div>
            <div class="card-body">
                <p class="card-text"><?= $amount ?></p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                Public Key N
            </div>
            <div class="card-body">
                <?php foreach ($chunksPublicKeyN as $chunk): ?>
                    <p class="card-text"><?= $chunk ?></p>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                Private Key D
            </div>
            <div class="card-body">
                <?php foreach ($chunksPrivateKeyD as $chunk): ?>
                    <p class="card-text"><?= $chunk ?></p>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                Decrypted Bid
            </div>
            <div class="card-body">
                <p class="card-text"><?= $decrypted_bid ?></p>
            </div>
        </div>
        <a href="my_profile.php" class="btn btn-primary">Go Back</a>
    </div>
</body>
</html>
