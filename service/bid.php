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



        // Function to encrypt a message using RSA encryption, TODO put into user class!!!
        public function rsaEncrypt($message, $publicKey) {
            $messageNum = gmp_init($message);
            $encrypted = gmp_powm($messageNum, gmp_init($publicKey['public_key_e']), gmp_init($publicKey['public_key_n']));
            //var_dump($encrypted);
            return gmp_strval($encrypted);
        }

        public function decryptBid($encrypted_bid, $private_key_d, $public_key_n) {
            /*echo '<br><br>Input values: <br>';
            echo 'Encrypted bid: ' . $encrypted_bid . '<br>';
            echo 'Private key d: ' . $private_key_d . '<br>';
            echo 'Public key n: ' . $public_key_n . '<br>';*/
        
            $encrypted_bid = gmp_init($encrypted_bid);
            $private_key_d = gmp_init($private_key_d);
            $public_key_n = gmp_init($public_key_n);
            
            /*echo '<br><br>After gmp_init: <br>';
            echo 'Encrypted bid: ' . gmp_strval($encrypted_bid) . '<br>';
            echo 'Private key d: ' . gmp_strval($private_key_d) . '<br>';
            echo 'Public key n: ' . gmp_strval($public_key_n) . '<br>';*/
        
            $decrypted_bid = gmp_strval(gmp_powm($encrypted_bid, $private_key_d, $public_key_n));
            
            //echo 'Decrypted bid: ' . $decrypted_bid . '<br>';
        
            return $decrypted_bid;
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
        
            // Check if this is a premium item or a regular item
            if ($item['item_type'] === 'PREMIUM') {
                // Create an instance of the User class
                $user = new User($this->mysqli); // Pass the database connection
        
                // Get the user's public key
                $publicKey = $user->getPublicKey($userId); // Use the user's ID passed to the function
               
                // Encrypt the amount using the public key
                $encryptedAmount = $this->rsaEncrypt($amount, $publicKey);
        
                // Place the encrypted bid
                $stmt = $this->mysqli->prepare("INSERT INTO bids (user_id, item_id, amount) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $userId, $itemId, $encryptedAmount);
                $stmt->execute();

                // Return the encrypted amount
                return $encryptedAmount;
            } else {
                // Place a normal (unencrypted) bid
                $stmt = $this->mysqli->prepare("INSERT INTO bids (user_id, item_id, amount) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $userId, $itemId, $amount);
                $stmt->execute();
            }
        }
    }
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