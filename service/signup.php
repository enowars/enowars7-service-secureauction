<?php 
session_start();

	include("config.php");
    include("user.php");


	if($_SERVER['REQUEST_METHOD'] == "POST")
	{
		//something was posted
		$user_name = $_POST['user_name'];
		$password = $_POST['password'];

		if(!empty($user_name) && !empty($password) && !is_numeric($user_name))
		{

			//save to database
			$user_id = rand(6,20);
			$query = "insert into users (user_id,user_name,password) values ('$user_id','$user_name','$password')";

			mysqli_query($con, $query);

            // Redirect to login page
			header("Location: login.php");
			die;
		}else
		{
			echo "Please enter some valid information!";
		}
	}
?>

<!DOCTYPE html>
<html>
<head>
    <title>
        SignUp
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
            <div style="font-size: 20px;margin: 10px">Signup</div>
            <input id="text"   type="text" name="user_name"><br><br>
            <input id="text"   type="password" name="password"><br><br>
            <input id="button" type="submit" value="Signup"><br><br>
            <a href="login.php">Click to Login</a><br><br>
        </form>
    </div>
</body>
</html