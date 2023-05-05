<?php
// Database connection settings
$servername = "db";
$username = "secureauction";
$password = "secureauction";
$dbname = "secureauction";

// Create connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
