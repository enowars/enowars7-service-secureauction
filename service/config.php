<?php
     $dbhost = "db";         // Keep it as "db" to match the service name in docker-compose.yml
     $dbuser = "appuser";       // Use the root user for database connection
     $dbpass = "secure_password";   // Set the password defined in docker-compose.yml
     $dbname = "secureauction";

    $con = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

    if (!$con) {
        die("Failed to connect to the database: " . mysqli_connect_error());
    } else {
        //echo "Connected successfully";
    }
?>
