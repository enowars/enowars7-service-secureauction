<?php 
//ob_start(); // Start output buffering
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
                //echo "User found.<br>";
                $user_data = mysqli_fetch_assoc($result);
                if ($user_data['password'] == $password) {
                    // Password is correct, set user_id session and redirect to index.php
                    $_SESSION['user_id'] = $user_data['user_id'];
                    
                    // Print all variables and their values related to the user
                    /*echo "ID: " . $user_data['id'] . "<br>";
                    echo "User ID: " . $user_data['user_id'] . "<br>";
                    echo "User Name: " . $user_data['user_name'] . "<br>";
                    echo "Password: " . $user_data['password'] . "<br>";
                    echo "Created At: " . $user_data['created_at'] . "<br>";*/
                    // Add any other user-related fields you have in your database
                    //ob_end_clean(); // Clean (erase) the output buffer and turn off output buffering
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
    </head>
    <body>
        <style type="text/css">
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
                }
        </style>
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
