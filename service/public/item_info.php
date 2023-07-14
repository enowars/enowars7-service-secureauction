<?php 
include("db_connect.php");
include("user.php");
include("item.php");
include("bid.php");

// Get keys and identifiers from POST request
$private_key_d = str_replace(array("\n", "\r", " "), '', $_POST['private_key_d']);
$item_id = $_POST['item_id'];
$user_id = $_POST['user_id']; 
$public_key_e = $_POST['public_key_e'];
$public_key_n = $_POST['public_key_n'];
$name = $_POST['name'];

// Check if amount is set in POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['bidamount'])) {
        $amount = $_POST['bidamount'];
    } else {
        echo "No amount received <br>";        
    }
}

$decrypted_bid = '';

// Check if private_key_d is set in POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($private_key_d)) {
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
}
elseif ($_SERVER["REQUEST_METHOD"] == "POST" && empty($private_key_d)) {
    $decrypted_bid = "No private key received <br>";
}
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
         <!-- Form for private key input -->
         <div class="card mb-4">
            <div class="card-header">
               Enter Private Key to Decrypt Bid
            </div>
            <div class="card-body">
               <form method="POST">
                  <input type="hidden" id="item_id" name="item_id" value="<?= $item_id ?>">
                  <input type="hidden" id="user_id" name="user_id" value="<?= $user_id ?>">
                  <input type="hidden" id="public_key_e" name="public_key_e" value="<?= $public_key_e ?>">
                  <input type="hidden" id="public_key_n" name="public_key_n" value="<?= $public_key_n ?>">
                  <input type="hidden" id="name" name="name" value="<?= $name ?>">
                  <input type="hidden" id="bidamount" name="bidamount" value="<?= $amount ?>">
                  <label for="private_key_d">Private Key:</label>
                  <input type="text" id="private_key_d" name="private_key_d" class="form-control">
                  <div class="card-footer">
                     <input type="submit" value="Decrypt" class="btn btn-primary">
                  </div>
               </form>
            </div>
         </div>
         <div class="card mb-4">
            <div class="card-header">
               Decrypted Bid:
            </div>
            <div class="card-body">
               <p class="card-text"><?= $decrypted_bid ?></p>
               
            </div>
            <div class="card-footer">
               <a href="user_index.php" class="btn btn-primary">Go to Auction</a>
         </div>
      </div>
   </body>
</html>