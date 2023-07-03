<?php
class User
{
    private $connection;

    // Constructor method
    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    // Function to check if the user is logged in
    public function checkLogin()
    {
        if (isset($_SESSION['user_id']))
        {
            $user_id = $_SESSION['user_id'];
            $sql = "SELECT * FROM users WHERE user_id = " . $user_id;
            $result = $this
                ->connection
                ->query($sql);

            if ($result && $result->num_rows > 0)
            {
                $user_data = $result->fetch_assoc();
                return $user_data;
            }
        }
        else
        {
            header("Location: index.php"); // If the user is not logged in, redirect to the login page
            die;
        }
    }

    // Function to fetch a specific user from the database by their user ID
    public function getUserById($user_id)
    {
        // Prepare the query with a placeholder for the user ID
        $stmt = $this
            ->connection
            ->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id); // Bind the user ID parameter
        $stmt->execute(); // Execute the statement
        $result = $stmt->get_result(); // Get the result
        $userData = $result->fetch_assoc(); // Fetch the result row as an associative array
        return $userData;
    }

    // Function to fetch a specific set of items for a user, with support for pagination
    public function getUserItems($user_id, $page, $itemsPerPage)
    {
        $offset = ($page - 1) * $itemsPerPage;

        // Prepare the query with placeholders for the user ID, itemsPerPage, and offset
        $stmt = $this
            ->connection
            ->prepare("SELECT * FROM items WHERE user_id = ? LIMIT ?, ?");
        $stmt->bind_param("iii", $user_id, $offset, $itemsPerPage); // Bind the parameters
        $stmt->execute(); // Execute the statement
        $result = $stmt->get_result(); // Get the result
        return $result;
    }

    // Function to get the total count of items for a specific user
    public function getUserItemsCount($user_id)
    {
        // Prepare the query with a placeholder for the user ID
        $stmt = $this
            ->connection
            ->prepare("SELECT COUNT(*) AS count FROM items WHERE user_id = ?");
        $stmt->bind_param("i", $user_id); // Bind the user ID parameter
        $stmt->execute(); // Execute the statement
        $result = $stmt->get_result(); // Get the result
        if ($result && $result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            return $row['count'];
        }

        return 0;
    }

    // Function to fetch a specific set of items for a user along with the respective bids, with support for pagination
    public function getUserItemsWithBids($user_id, $page, $itemsPerPage)
    {
        $offset = ($page - 1) * $itemsPerPage;

        // Prepare the query with placeholders for the user ID, itemsPerPage, and offset
        $stmt = $this
            ->connection
            ->prepare("SELECT items.*, bids.amount 
                                                FROM items 
                                                LEFT JOIN (SELECT item_id, MAX(amount) as amount FROM bids WHERE user_id = ? GROUP BY item_id) as bids 
                                                ON items.id = bids.item_id 
                                                WHERE items.user_id = ? 
                                                ORDER BY items.created_at DESC 
                                                LIMIT ?, ?");
        $stmt->bind_param("iiii", $user_id, $user_id, $offset, $itemsPerPage); // Bind the parameters
        $stmt->execute(); // Execute the statement
        $result = $stmt->get_result(); // Get the result
        return $result;
    }

    // Function to get the total count of bids for a specific user
    public function getUserBidsCount($user_id)
    {
        // Prepare the query with a placeholder for the user ID
        $stmt = $this
            ->connection
            ->prepare("SELECT COUNT(*) as bid_count FROM bids WHERE user_id = ?");
        $stmt->bind_param("i", $user_id); // Bind the user ID parameter
        $stmt->execute(); // Execute the statement
        $result = $stmt->get_result(); // Get the result
        if ($result && $result->num_rows > 0)
        {
            $data = $result->fetch_assoc();
            return $data['bid_count'];
        }

        return 0;
    }

    // Function to fetch the bids that all users have placed on the items created by a particular user
    // So, if Alice ($user_id) created various items, this query will return all the bids that other users have placed on Alice's items.
    public function getUserBids($user_id, $offset, $limit)
    {
        $sql = "SELECT items.id as item_id, items.name as item_name, items.start_price, items.item_type, items.created_at as item_created_at,
        bids.user_id as bidder_id, bids.created_at as bid_created_at, bids.amount as bid_amount
        FROM items 
        JOIN bids ON items.id = bids.item_id 
        WHERE items.user_id = " . $user_id . " 
        ORDER BY bids.created_at DESC, bids.amount DESC LIMIT " . $offset . ", " . $limit;

        // Execute the query
        $result = $this
            ->connection
            ->query($sql);

        // Return the result
        return $result;
        
    }

    // Get the bids i placed on other users' items
    public function getMyBids($user_id, $offset, $limit)
    {
        $sql = "SELECT 
                    items.id as item_id, 
                    items.name as item_name, 
                    items.start_price, 
                    items.item_type, 
                    items.created_at as item_created_at,
                    items.user_id as creator_id,
                    bids.user_id as bidder_id, 
                    bids.created_at as bid_created_at, 
                    bids.amount as bid_amount
                FROM items 
                INNER JOIN bids ON items.id = bids.item_id 
                WHERE bids.user_id = ? 
                ORDER BY bids.created_at DESC, bids.amount DESC
                LIMIT ?, ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("iii", $user_id, $offset, $limit);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result;
    }

    public function decryptAndRankUserBids($user_id, $private_key_d, $bid)
    {
        // Get all bids of the user
        $bids = $this->getUserBids($user_id, 0, PHP_INT_MAX);
        
        // Debug: dump the contents of the $bids object
        echo "Debug: contents of \$bids:";
        var_dump($bids);
        echo "<br>";
    
        // Decrypt bids and store them along with bid ids in a new array
        $decrypted_bids = array();
        while($row = $bids->fetch_assoc()) {
            $decrypted_bid_amount = $bid->decryptBid($row['bid_amount'], $private_key_d);
            array_push($decrypted_bids, array("id" => $row['item_id'], "amount" => $decrypted_bid_amount));
        }
        
        // Debug: dump the contents of the $decrypted_bids array
        echo "Debug: contents of \$decrypted_bids:";
        var_dump($decrypted_bids);
        echo "<br>";
    
        // Sort bids in descending order
        usort($decrypted_bids, function($a, $b) {
            return $b['amount'] - $a['amount'];
        });
    
        return $decrypted_bids;
    }
    

    // Function to fetch a user by username
    public function getUserByUsername($user_name)
    {
        // Prepare the query with a placeholder for the user name
        $stmt = $this
            ->connection
            ->prepare("SELECT * FROM users WHERE user_name = ?");
        $stmt->bind_param("s", $user_name); // Bind the user name parameter
        $stmt->execute(); // Execute the statement
        $result = $stmt->get_result(); // Get the result
        // Check if a user with the given username exists
        if ($result && $result->num_rows > 0)
        {
            $user_data = $result->fetch_assoc();
            return $user_data;
        }
        return null;
    }

    // Function to generate a random prime number of a given bit length
    private function generate_random_prime($bits)
    {
        // Generate a random number of $bits length
        $random_number = gmp_random_bits($bits);

        // Make sure it's odd, to increase the probability that it's prime
        if (gmp_mod($random_number, 2) == 0)
        {
            $random_number = gmp_add($random_number, 1);
        }

        // Increment by 2 (to stay odd) until we find a prime
        while (!gmp_prob_prime($random_number))
        {
            $random_number = gmp_add($random_number, 2);
        }

        return $random_number;
    }

    // Function to generate RSA keys
    public function generate_stateful_rsa_keys($bit_length = 1024)
    {
        // Generate a random prime number p
        $p = $this->generate_random_prime(($bit_length - 1) / 2);
       
        // Generate a random prime number q
        $offset = gmp_init("10");
        $increased_p = gmp_add($p, $offset);
        $number = gmp_mul($p, $increased_p);
        $q = gmp_nextprime($number);

        // Calculate n = p * q
        $n = gmp_mul($p, $q);

        // Calculate the totient = (p-1) * (q-1)
        $totient = gmp_mul(gmp_sub($p, 1) , gmp_sub($q, 1));

        // Choose e such that 1 < e < totient and e and totient are coprime
        $e = gmp_init(65537);
       
        // Calculate d, the modular inverse of e mod totient
        $d = gmp_invert($e, $totient);

        // Return both the public key (e, n) and the private key (d, n)
        return [
            'public' => ['e' => gmp_strval($e) , 'n' => gmp_strval($n)],
            'private' => ['d' => gmp_strval($d)]
        ];
    }

    // Function to generate and store RSA keys for a user
    public function generateAndStoreKeysForUser($user_id)
    {
        $rsa_keys = $this->generate_stateful_rsa_keys();
        $public_key = $rsa_keys['public'];
        $private_key = $rsa_keys['private'];

        // Update the user's keys in the database
        $stmt = $this
            ->connection
            ->prepare("UPDATE users SET public_key_e = ?, public_key_n = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $public_key['e'], $public_key['n'], $user_id);
        $stmt->execute();   
    }

    // Function to get the public key of a user
    public function getPublicKey($userId) {
        $stmt = $this->connection->prepare("SELECT public_key_e, public_key_n FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId); 
        $stmt->execute(); 
        $result = $stmt->get_result(); 

        if ($result) {
            $data = $result->fetch_assoc();
            return $data; // return both public_key_e and public_key_n
        } else {
            echo 'Query Error: ' . $this->mysqli->error;
            return false;
        }
    }
}
?>