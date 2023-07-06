<?php 
session_start();
include("db_connect.php");
include("user.php");

$message = ""; // Initialize the message variable

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Something was posted
    $user_name = $_POST['user_name'];
    $password = $_POST['password'];
    $userType = $_POST['user_type'] ?? 'REGULAR'; // Get the user type from the POST parameters

    if (!in_array($userType, ['REGULAR', 'PREMIUM'])) {
        die('Invalid user type');
    }

    // Check if the user already exists
    $user = new User($con);
    $existing_user = $user->getUserByUsername($user_name);

    // Checking if we are in signup or login mode
    if(isset($_POST['action']) && $_POST['action'] == 'signup'){
        if ($existing_user) {
            // User already exists, redirect to the login page
            $message = "User already exists, please login";
        } else {
            if (!empty($user_name) && !empty($password) && !is_numeric($user_name)) {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Generate RSA keys for Premium users
                if($userType === 'PREMIUM') {
                    $rsa_keys = $user->generate_stateful_rsa_keys();
                    $public_key_e = $rsa_keys['public']['e'];
                    $public_key_n = $rsa_keys['public']['n'];
                    $private_key_d = $rsa_keys['private']['d']; // Get the private key
                           
                    $stmt = $con->prepare("INSERT INTO users (user_name, password, user_type, public_key_e, public_key_n) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssss", $user_name, $hashed_password, $userType, $public_key_e, $public_key_n);

                }
                 else {
                    $stmt = $con->prepare("INSERT INTO users (user_name, password, user_type) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $user_name, $hashed_password, $userType);
                }

                // Execute the statement
                if($stmt->execute()){
                    // Store user data in session
                    $_SESSION['user_name'] = $user_name;
                    $_SESSION['user_type'] = $userType;
                    $_SESSION['user_id'] = $con->insert_id; // get the last inserted ID

                    if($userType === 'PREMIUM') {
                        $_SESSION['private_key'] = $private_key_d; // store private key in session
                        // Redirect to intermediate page for premium user
                        header("Location: display_key.php");
                    } else {
                        // Redirect to user_index page for regular user
                        header("Location: user_index.php");
                    }
                    exit;
                }
            }
        }
    } else { // Login mode
        if ($existing_user) {
            // User exists, check the password
            if (password_verify($password, $existing_user['password'])) {
                // Password is correct

                // Store user data in session
                $_SESSION['user_name'] = $existing_user['user_name'];
                $_SESSION['user_type'] = $existing_user['user_type'];
                $_SESSION['user_id'] = $existing_user['user_id'];

                // Redirect to user_index page
                header("Location: user_index.php");
                exit;
            } else {
                $message = "Incorrect password!";
            }
        } else {
            // User does not exist
            $message = "User does not exist, please sign up!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>SignUp</title>
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
                        Welcome to the SecureAuction System, please signup!
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-danger"><?php echo $message; ?></div>
                        <?php endif; ?>
                        <form method="post">
                            <div class="form-group">
                                <label for="user_name">Username</label>
                                <input id="user_name" class="form-control" type="text" name="user_name">
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input id="password" class="form-control" type="password" name="password">
                            </div>
                            <div class="form-group">
                                <label>User Type:</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="user_type" id="regularUser" value="REGULAR" checked>
                                    <label class="form-check-label mr-2" for="regularUser">Regular User</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="user_type" id="premiumUser" value="PREMIUM">
                                    <label class="form-check-label mr-2" for="premiumUser">Premium User</label>
                                </div>
                            </div>
                            <button class="btn btn-primary" type="submit" name="action" value="signup">Signup</button>
                            <button class="btn btn-primary" type="submit" name="action" value="login">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>