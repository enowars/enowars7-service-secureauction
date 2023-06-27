<?php 
include("config.php");
include("user.php");
include("item.php");
include("bid.php");

// Get keys, identifiers, and name from POST request
$item_id = $_POST['item_id'];
$user_id = $_POST['user_id'];
$public_key_e = $_POST['public_key_e'];
$public_key_n = $_POST['public_key_n'];
$name = $_POST['name'];
$amount = $_POST['bidamount'];


$private_key_d = '';
$decrypted_bid = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['private_key_d'])) {
    $private_key_d = $_POST['private_key_d'];
    $public_key_n = $_POST['public_key_n'];  // capturing the public_key_n from the form
    $amount = $_POST['amount'];  // capturing the amount from the form

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Decrypt Bid</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-4 mb-4">Item Info</h1>
        <div class="card mb-4">
            <div class="card-header">
                Item Name
            </div>
            <div class="card-body">
                <p class="card-text"><?= $name ?></p>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header">
                Item ID
            </div>
            <div class="card-body">
                <p class="card-text"><?= $item_id ?></p>
            </div>
        </div>

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
                Public Key E
            </div>
            <div class="card-body">
                <p class="card-text"><?= $public_key_e ?></p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                Public Key N
            </div>
            <div class="card-body">
                <p class="card-text"><?= $public_key_n ?></p>
            </div>
        </div>

        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <input type="hidden" name="public_key_n" value="<?= $public_key_n ?>">
            <input type="hidden" name="amount" value="<?= $amount ?>">
            <div class="form-group">
                <label for="private_key_d">Enter Private Key D:</label>
                <input type="text" class="form-control" id="private_key_d" name="private_key_d" required>
            </div>
            <div class="row">
                <div class="col">
                    <button type="submit" class="btn btn-primary w-100">Decrypt Bid</button>
                </div>
                <div class="col">
                    <a href="user_index.php" class="btn btn-secondary w-100">Go Back</a>
                </div>
            </div>
        </form>

        <?php if (!empty($private_key_d)): ?>
        <div class="card mt-4">
            <div class="card-header">
                Private Key D
            </div>
            <div class="card-body">
                <p class="card-text"><?= $private_key_d ?></p>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                Decrypted Bid
            </div>
            <div class="card-body">
                <p class="card-text"><?= $decrypted_bid ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
