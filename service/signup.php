<?php 
session_start();
include("config.php");
include("user.php");

$message = ""; // Initialize the message variable

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Something was posted
    $user_name = $_POST['user_name'];
    $password = $_POST['password'];

    // Check if the user already exists
    $user = new User($con);
    $existing_user = $user->getUserByUsername($user_name);

    if ($existing_user) {
        // User already exists, redirect to the login page
        header("Location: login.php");
        exit;
    } else {
        if (!empty($user_name) && !empty($password) && !is_numeric($user_name)) {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Use prepared statements
            $stmt = $con->prepare("INSERT INTO users (user_id, user_name, password) VALUES (?, ?, ?)");
            $user_id = rand(6, 1000);
            $stmt->bind_param("iss", $user_id, $user_name, $hashed_password); // "iss" indicates that the first parameter is an integer and the second and third parameters are strings

            // Execute the statement
            $stmt->execute();

            // Redirect to login page
            #header("Location: login.php");
            #exit;
        } else {
            $message = "Please enter valid information!";
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
                        Signup
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
                            <button class="btn btn-primary" type="submit">Signup</button>
                        </form>
                        <p class="mt-3">Already have an account? <a href="login.php">Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
