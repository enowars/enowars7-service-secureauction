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

    echo "Username: " . $user_name . "<br>";
    echo "Password: " . $password . "<br>";

    // Validate the input
    if(!empty($user_name) && !empty($password) && !is_numeric($user_name))
    {   
        echo "Read from the database\n";
        // Read from the database
        $query = "select * from users where username = '$user_name' limit 1";
        $result = mysqli_query($con, $query);
        // print query result
        echo "Result content:: " . $result . "<br>";
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                // Access and display the values from each row
                echo "User ID: " . $row['user_id'] . "<br>";
                echo "Username: " . $row['username'] . "<br>";
                echo "Password: " . $row['password'] . "<br>";
                echo "Created at: " . $row['created_at'] . "<br>";
                // Add any other fields you want to display
                echo "<br>";
            }
        } else {
            echo "No rows found.";
        }
        // print query content
        echo "\nQuery content: " . $query . "<br>";
        

        if($result)
        {
            echo "Check if a user with the given username exists";
            // Check if a user with the given username exists
            if($result && mysqli_num_rows($result) > 0)
            {
                echo "Check if clause deep";
                $user_data = mysqli_fetch_assoc($result);
                if($user_data['password'] === $password)
                {
                    echo "Check last one";
                    // Password is correct, set user_id session and redirect to index.php
                    $_SESSION['user_id'] = $user_data['user_id'];
                    header("Location: index.php");
                    die;
                }
            }
        }
        // Invalid username or password
        echo "wrong username or password!";
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
