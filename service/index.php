<!DOCTYPE html>
<html>
<head>
    <title>Welcome to the Auction System</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: royalblue;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .jumbotron {
            background-color: white;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="jumbotron">
            <h1 class="display-4">Welcome to the Auction System</h1>
            <p class="lead">Please select your user type:</p>
            <hr class="my-4">
            <div class="mb-3">
                <a class="btn btn-primary btn-lg" href="signup.php" role="button">Sign Up as Regular User</a>
                <a class="btn btn-success btn-lg" href="premium_user_signup.php" role="button">Sign Up as Premium User</a>
            </div>
            <p>If you already have an account, please log in:</p>
            <div>
                <a class="btn btn-primary" href="login.php" role="button">Log In as Regular User</a>
                <a class="btn btn-success" href="premium_user_login.php" role="button">Log In as Premium User</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
