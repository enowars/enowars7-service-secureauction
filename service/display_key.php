<?php
session_start();
if (!isset($_SESSION['private_key']) || !isset($_SESSION['p']) || !isset($_SESSION['q'])) {
    // User has not just signed up, redirect them to the signup page
    header("Location: index.php");
    exit;
}

$private_key = $_SESSION['private_key'];
$p = $_SESSION['p'];
$q = $_SESSION['q'];
$e = $_SESSION['public_key_e'];
$n = $_SESSION['public_key_n'];
$user_id = $_SESSION['user_id']; // get user_id from the session
// Unset the private key, p and q in the session so that refreshing the page won't display it again
unset($_SESSION['private_key']);
unset($_SESSION['p']);
unset($_SESSION['q']);
unset($_SESSION['public_key_e']);
unset($_SESSION['public_key_n']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Display Key</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style type="text/css">
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: royalblue;
        }

        .key-chunk {
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Welcome to the SecureAuction System, here's your private key
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Please write down this key and store it securely:</h5>
                        <?php
                            // Split the private key into chunks for better readability
                            $chunks = str_split($private_key, 50); 
                            foreach ($chunks as $chunk) {
                                echo "<p class='key-chunk'>$chunk</p>";
                            }
                            
                            // Display p and q
                            //echo "<h5 class='mt-5'>Here are your primes p and q:</h5>";
                            //echo "<p>p: $p</p>";
                            //echo "<p>q: $q</p>";
                            //echo "<h5 class='mt-5'>Here is your public key e:</h5>";
                            //echo "<p>e: $e</p>";
                            //echo "<h5 class='mt-5'>Here is your public key n:</h5>";
                            //echo "<p>n: $n</p>";
                        ?>
                         <input type="hidden" id="userId" value="<?php echo $user_id; ?>">
                        <button class="btn btn-primary" onclick="window.location.href = 'user_index.php';">Proceed</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
