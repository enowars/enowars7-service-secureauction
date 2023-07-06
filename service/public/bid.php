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

        // Function to encrypt a message using RSA encryption
        public function rsaEncrypt($message, $publicKey) {
            if ($publicKey === null || empty($publicKey['public_key_e']) || empty($publicKey['public_key_n'])) {
                throw new Exception("Public key is not available. Because the user is created without public key");
            }
            // Convert the message into bytes and then to a hex string
            $messageHex = bin2hex($message);
            // Convert the hex string into a GMP number
            $messageNum = gmp_init($messageHex, 16);
            
            // Perform the encryption
            $encrypted = gmp_powm($messageNum, gmp_init($publicKey['public_key_e'], 10), gmp_init($publicKey['public_key_n'], 10));
            // Convert the result to a string and return it
            return gmp_strval($encrypted);
        }
        
        public function decryptBid($encrypted_bid, $privateKey) {
            // Check if private_key_d is set
            if(!isset($privateKey['private_key_d'])) {
                return "Hint: You haven't provided a value for private_key_d.\n";   
            }
            // Check if private_key_d is numeric
            else if(!is_numeric($privateKey['private_key_d'])) {
                return "Hint: The value for private_key_d should be numeric. Please check your inputs.\n";   
            }

            // Check if public_key_n is set
            if(!isset($privateKey['public_key_n'])) {
                return "Hint: You haven't provided a value for public_key_n.\n";   
            }
            // Check if public_key_n is numeric
            else if(!is_numeric($privateKey['public_key_n'])) {
                return "Hint: The value for public_key_n should be numeric. Please check your inputs.\n";
            }
            // Check that encrypted_bid is numeric
            if (!is_numeric($encrypted_bid)) {
                return "Error: encrypted_bid is not numeric.\n";
            }

            // Perform the decryption
            $decrypted_bid = gmp_powm(gmp_init($encrypted_bid, 10), gmp_init($privateKey['private_key_d'], 10), gmp_init($privateKey['public_key_n'], 10));
            // Convert the result to a hexadecimal string
            $decrypted_bid_hex = gmp_strval($decrypted_bid, 16);

            // Check if the length of the hex string is even before converting it to binary
            if (strlen($decrypted_bid_hex) % 2 != 0) {
                return "Error: Decryption failed. Please check your private key.";
            }

            // Convert the hexadecimal string into a binary string (which is our original message)
            $decrypted_bid_string = hex2bin($decrypted_bid_hex);
            return $decrypted_bid_string;
        }


        // Function to place a bid
        public function placeBid($itemId, $userId, $amount) {
            // Ensure inputs are integers
            $itemId = intval($itemId);
            $userId = intval($userId);
        
            // Fetch the item details
            $stmt = $this->mysqli->prepare("SELECT * FROM items WHERE id = ?");
            $stmt->bind_param("i", $itemId);
            $stmt->execute();
            $itemResult = $stmt->get_result();
            $item = $itemResult->fetch_assoc();

            // Fetch the item owner's details
            $stmt = $this->mysqli->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $item['user_id']);  // Using the user_id from the item's details
            $stmt->execute();
            $itemOwnerResult = $stmt->get_result();
            $itemOwner = $itemOwnerResult->fetch_assoc();

            // Fetch the bidding user's details
            $stmt = $this->mysqli->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $userId);  // Using the user_id of the bidder
            $stmt->execute();
            $userResult = $stmt->get_result();
            $user = $userResult->fetch_assoc();

            // Premium user should be able to place encrypted bid for regular items and premium items
            if ($user['user_type'] === 'PREMIUM') {
                // Create an instance of the User class
                $user = new User($this->mysqli); // Pass the database connection
        
                // Get the item owner's public key
                $publicKey = $user->getPublicKey($item['user_id']);  // Using the user_id from the item's details
               
                // Encrypt the amount using the public key
                $encryptedAmount = $this->rsaEncrypt($amount, $publicKey);
        
                // Place the encrypted bid
                $stmt = $this->mysqli->prepare("INSERT INTO bids (user_id, item_id, amount) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $userId, $itemId, $encryptedAmount);
                $stmt->execute();

                // Return the encrypted amount
                return $encryptedAmount;
            } else {
                // Regular users place normal (unencrypted) bid, 
                $stmt = $this->mysqli->prepare("INSERT INTO bids (user_id, item_id, amount) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $userId, $itemId, $amount);
                $stmt->execute();
                return true;
            }
        }    
    }   
    // Global function to get the total number of bids
    function getTotalBids($mysqli){
        $stmt = $mysqli->prepare("SELECT COUNT(*) as total_bids FROM bids");
        $stmt->execute(); 
        $result = $stmt->get_result(); 
    
        // Check if the query was successful
        if ($result) {
            // Fetch the result as an associative array and return the highest bid
            $data = $result->fetch_assoc();
            return $data['total_bids'];
        } else {
            // If the query failed, print the error message and return false
            echo 'Query Error: ' . $mysqli->error;
            return false;
        }
    }   
?>