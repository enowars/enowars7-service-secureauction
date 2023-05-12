<?php 
session_start();

include("config.php");
include("user.php");

// Check if the form was submitted
if($_SERVER['REQUEST_METHOD'] == "POST")
{
    // Something was posted
    $user_name = $_POST['user_name']; // Get the username from the form
    $password = $_POST['password']; // Get the password from the form

    if(!empty($user_name) && !empty($password)){

        //read from database
		$query = "select * from users where user_name = '$user_name' limit 1";
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
                    echo "Incorrect password.<br>";
                }
            } else {
                echo "No user found with the given username.<br>";
            }
        } else {
            echo "Query failed.<br>";
        }

    }

    
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>
        SecureAuction - Login
    </title>
    <link rel="stylesheet" href="../style.css">
    <style type="text/css">
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f1f1f1;
        }

        #text{
            height: 25px;
            border-radius: 5px;
            padding: 4px;
            border: solid thin #aaa;
            width: 100%;
        }
        #button{
            padding: 10px;
            width: 100px;
            color: rgb(221, 22, 22);
            background-color: lightblue;
            border: none;
        }
        #box{
            background-color: grey;
            margin: auto;
            width: 300px;
            padding: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div id="box">
        <form method="post">
            <div style="font-size: 20px;margin: 10px">Login</div>
            <input id="text"   type="text" name="user_name"><br><br>
            <input id="text"   type="password" name="password"><br><br>
            <input id="button" type="submit" value="Login"><br><br>
            <a href="signup.php">Click to Signup</a><br><br>
        </form>
    </div>
</body>
</html>
