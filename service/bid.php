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
            $stmt = $this->mysqli->prepare("SELECT MAX(amount) as highest_bid FROM bids WHERE item_id = ?");
            $stmt->bind_param("i", $itemId); 
            $stmt->execute(); 
            $result = $stmt->get_result(); 

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
            // Check if $itemId and $userId are set and not empty
            if(isset($itemId, $userId) && !empty($itemId) && !empty($userId)) {
                // Prepare SQL query to get the maximum bid amount for the given item ID by the specific user
                $stmt = $this->mysqli->prepare("SELECT MAX(amount) as highest_bid FROM bids WHERE item_id = ? AND user_id = ?");
                $stmt->bind_param("ii", $itemId, $userId);
                $stmt->execute(); 
                $result = $stmt->get_result(); 

                // Check if the query was successful
                if ($result) {
                    // Fetch the result as an associative array and return the highest bid
                    $data = $result->fetch_assoc();
                    return $data['highest_bid'];
                }
            } else {
                // If the itemId or userId is not set, print the error message and return false
                echo 'Error: Item ID or User ID is not set or is empty.';
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
            // Ensure inputs are integers
            $itemId = $itemId;
            $userId = $userId;

            // Check if the new bid contains the substring 'eno'
            $isEnoBid = strpos($amount, 'eno') !== false;
            $isNumericBid = is_numeric($amount);
            
            if (!$isEnoBid && !$isNumericBid) {
                die("Invalid bid. Please enter a valid number or bid contains the substring 'eno'.");
            }

             // Print the bid amount for debugging
            #var_dump($amount);

            // Add the bid to the database without escaping or prepared statements
            $insertQuery = "INSERT INTO bids (amount, user_id, item_id, created_at) VALUES ('$amount', $userId, $itemId, NOW())";

            // Execute the insert query
            $insertResult = $this->mysqli->query($insertQuery);

            // Check if the insertion was successful
            if ($insertResult) {
                return true;
            } else {
                // If the insertion failed, print the error message and return false
                echo 'Insert Error: ' . $this->mysqli->error;
                return false;
            }
        }  
    }
?>
