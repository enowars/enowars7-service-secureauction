<?php
    class Bid {
        private $mysqli;

        public function __construct($con) {
            $this->mysqli = $con;
        }

        public function getHighestBid($itemId) {
            $query = "SELECT MAX(amount) as highest_bid FROM bids WHERE item_id = $itemId";
            $result = $this->mysqli->query($query);
        
            if ($result) {
                $data = $result->fetch_assoc();
                return $data['highest_bid'];
            } else {
                echo 'Query Error: ' . $this->mysqli->error;
                return false;
            }
        }

        public function placeBid($itemId, $userId, $amount) {
            // First check if the user has already placed a bid on this item
            $checkBidQuery = "SELECT * FROM bids WHERE user_id = $userId AND item_id = $itemId";
            $checkBidResult = $this->mysqli->query($checkBidQuery);
        
            if ($checkBidResult->num_rows > 0) {
                // User has already placed a bid, update it
                $updateQuery = "UPDATE bids SET amount = $amount WHERE user_id = $userId AND item_id = $itemId";
                $updateResult = $this->mysqli->query($updateQuery);
        
                if ($updateResult) {
                    return true;
                } else {
                    echo 'Update Error: ' . $this->mysqli->error;
                    return false;
                }
            } else {
                // No existing bid, create a new one
                $insertQuery = "INSERT INTO bids (amount, user_id, item_id) VALUES ($amount, $userId, $itemId)";
                $insertResult = $this->mysqli->query($insertQuery);
        
                if ($insertResult) {
                    return true;
                } else {
                    echo 'Insert Error: ' . $this->mysqli->error;
                    return false;
                }
            }
        }
        
    }
?>
