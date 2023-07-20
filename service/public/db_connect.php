<?php
    $servername = getenv("MYSQL_HOST");
    $dbname = getenv("MYSQL_DATABASE");
    $username = getenv("MYSQL_USER");
    $password = getenv("MYSQL_PASSWORD");

    $con = mysqli_connect($servername, $username, $password, $dbname);

    if (!$con) {
        die("Failed to connect to the database: " . mysqli_connect_error());
    } 
?>
