<?php 
session_start();
    $_SESSION;
    include("config.php");
    include("user.php");
    $user_data = check_login($con);
?>

<!DOCTYPE html>
<html>
    <head>
        <title>
            My Website
        </title>
    </head>
    <body>
        <a href="logout.php">logout</a>
        <h1>
            This is the login page
        </h1>
        <br>
        Hello, <?php echo $user_data['user_name']; ?>
    </body>
</html>