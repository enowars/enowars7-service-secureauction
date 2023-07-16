<?php
session_start();
if (!isset($_SESSION['private_key'])) {
    // User has not just signed up, redirect them to the signup page
    header("Location: index.php");
    exit;
}

$private_key = $_SESSION['private_key']; 
$user_id = $_SESSION['user_id']; // get user_id from the session
// Unset the private key in the session so that refreshing the page won't display it again
unset($_SESSION['private_key']);
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
