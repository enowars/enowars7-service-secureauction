<?php
    // Declare a class called Bid
    class Bid {
        // Declare a private variable to store the connection to the database
        private $mysqli;

        // Construct function that is run when a new instance of this class is created
        // It takes a connection to the database as its argument
        public function __construct($con) {
            $this->mysqli = $con;
        }

        // Function to retrieve the highest bid for a given item
        public function getHighestBid($itemId) {
            // Prepare SQL query to get the maximum bid amount for the given item ID
            $query = "SELECT MAX(amount) as highest_bid FROM bids WHERE item_id = $itemId";

            // Execute the query using the established database connection
            $result = $this->mysqli->query($query);

            // Check if the query was successful
            if ($result) {
                // Fetch the result as an associative array and return the highest bid
                $data = $result->fetch_assoc();
                return $data['highest_bid'];
            } else {
                // If the query failed, print the error message and return false
                echo 'Query Error: ' . $this->mysqli->error;
                return false;
            }
        }


        // Function to retrieve the highest bid placed by a specific user for a given item
        public function getUserHighestBid($itemId, $userId) {
            // Prepare SQL query to get the maximum bid amount for the given item ID by the specific user
            $query = "SELECT MAX(amount) as highest_bid FROM bids WHERE item_id = $itemId AND user_id = $userId";

            // Execute the query using the established database connection
            $result = $this->mysqli->query($query);

            // Check if the query was successful
            if ($result) {
                // Fetch the result as an associative array and return the highest bid
                $data = $result->fetch_assoc();
                return $data['highest_bid'];
            } else {
                // If the query failed, print the error message and return false
                echo 'Query Error: ' . $this->mysqli->error;
                return false;
            }
        }

        // Function to get the highest bid placed by a specific user for a given item
        public function getHighestBidByUser($itemId, $userId) {
            // Prepare the query with placeholders for the item ID and user ID
            $stmt = $this->mysqli->prepare("SELECT MAX(amount) AS highest_bid FROM bids WHERE item_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $itemId, $userId); // Bind the item ID and user ID parameters
            $stmt->execute(); // Execute the statement
            $result = $stmt->get_result(); // Get the result

            if ($result && $result->num_rows > 0) {
                $data = $result->fetch_assoc();
                return $data['highest_bid'];
            }

            return 0;
        }

        

        // Function to place a bid on an item
        public function placeBid($itemId, $userId, $amount) {
            // First, check if the user has already placed a bid on this item
            // Prepare SQL query to get bids by the specific user on the specific item
            $checkBidQuery = "SELECT * FROM bids WHERE user_id = $userId AND item_id = $itemId";

            // Execute the query using the established database connection
            $checkBidResult = $this->mysqli->query($checkBidQuery);

            // If the user has already placed a bid on the item
            if ($checkBidResult->num_rows > 0) {
                // Prepare an SQL query to update the bid amount
                $updateQuery = "UPDATE bids SET amount = $amount WHERE user_id = $userId AND item_id = $itemId";

                // Execute the update query
                $updateResult = $this->mysqli->query($updateQuery);

                // Check if the update was successful
                if ($updateResult) {
                    // If the update was successful, return true
                    return true;
                } else {
                    // If the update failed, print the error message and return false
                    echo 'Update Error: ' . $this->mysqli->error;
                    return false;
                }
            } else {
                // If the user has not yet placed a bid on the item
                // Prepare an SQL query to insert a new bid into the bids table
                $insertQuery = "INSERT INTO bids (amount, user_id, item_id) VALUES ($amount, $userId, $itemId)";

                // Execute the insert query
                $insertResult = $this->mysqli->query($insertQuery);

                // Check if the insertion was successful
                if ($insertResult) {
                    // If the insertion was successful, return true
                    //echo 'Bid placed successfully!';
                    return true;
                } else {
                    // If the insertion failed, print the error message and return false
                    echo 'Insert Error: ' . $this->mysqli->error;
                    return false;
                }
            }
        }
    }
?>
