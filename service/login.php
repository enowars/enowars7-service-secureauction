<?php 
session_start();

include("config.php");
include("user.php");

$message = ""; // Initialize the message variable

// Check if the form was submitted
if($_SERVER['REQUEST_METHOD'] == "POST")
{
    // Something was posted
    $user_name = $_POST['user_name']; // Get the username from the form
    $password = $_POST['password']; // Get the password from the form

    if(!empty($user_name) && !empty($password)){

        // Read from the database
        $query = "SELECT * FROM users WHERE user_name = '$user_name' LIMIT 1";
        $result = mysqli_query($con, $query);

        // Validate the input
        if ($result) {
            // Check if a user with the given username exists
            if ($result && mysqli_num_rows($result) > 0) {
                $user_data = mysqli_fetch_assoc($result);
                if ($user_data['password'] == $password) {
                    // Password is correct, set user_id session and redirect to index.php
                    $_SESSION['user_id'] = $user_data['user_id'];
                    header("Location: index.php");
                    die;
                } else {
                    $message = "Incorrect password.";
                }
            } else {
                $message = "No user found with the given username.";
            }
        } else {
            $message = "Query failed.";
        }
    }
    else {
        $message = "Please enter valid information.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>SecureAuction - Login</title>
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
                        Login
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
                            <button class="btn btn-primary" type="submit">Login</button>
                        </form>
                        <p class="mt-3">Don't have an account? <a href="signup.php">Signup</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
