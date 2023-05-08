<?php

// Function to check if a user is logged in
function check_login($con)
{
    // Check if the user_id session variable is set
    if (isset($_SESSION['user_id'])) {
        // Get the user ID from the session
        $id = $_SESSION['user_id'];

        // Prepare the query to retrieve user data from the database
        $query = "SELECT * FROM users WHERE user_id = :id LIMIT 1";

        // Execute the query
        $result = mysqli_query($con, $query);

        // Check if the query was successful and returned at least one row
        if ($result && mysqli_num_rows($result) > 0) {
            // Fetch the user's data from the result set
            $user_data = mysqli_fetch_assoc($result);

            // Return the user's data
            return $user_data;
        }
    }

    // If the user is not logged in or the query failed, redirect to the login page
    header("Location: login.php");
    // Note: This line will cause an immediate redirection, so any code after this line will not be executed.
}


// Function to generate a random number
function random_num($length)
{
    $text = "";

    // If the requested length is less than 5, set it to 5 as the minimum length
    if ($length < 5) {
        $length = 5;
    }

    // Generate a random length between 4 and the specified $length
    $len = rand(4, $length);

    // Generate random digits and append them to $text
    for ($i = 0; $i < $len; $i++) {
        $text .= rand(0, 9);
    }

    // Return the generated random number
    return $text;
}

?>
