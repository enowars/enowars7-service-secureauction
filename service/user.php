<?php
class User {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    
    // Function to check if the user is logged in
    public function checkLogin() {
        if (isset($_GET['user_id'])) {
            $_SESSION['user_id'] = $_GET['user_id'];
        }
    
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
    
            // Directly insert the user_id into the SQL statement, TODO: use prepared statements otherwise SQL INJECTION
            $sql = "SELECT * FROM users WHERE user_id = " . $user_id;
            $result = $this->connection->query($sql);
    
            if ($result && $result->num_rows > 0) {
                $user_data = $result->fetch_assoc();
                #print_r($user_data);
                return $user_data;
            }
        } else {
            header("Location: login.php"); // If the user is not logged in, redirect to the login page
            die;
        }
    }
    
    


    // Function to fetch a specific user from the database by their user ID
    public function getUserById($user_id) {
        // Prepare the query with a placeholder for the user ID
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id); // Bind the user ID parameter
        $stmt->execute(); // Execute the statement
        $result = $stmt->get_result(); // Get the result
        return $result;
    }

    // Function to fetch a specific set of items for a user, with support for pagination
    public function getUserItems($user_id, $page, $itemsPerPage) {
        $offset = ($page - 1) * $itemsPerPage;

        // Prepare the query with placeholders for the user ID, itemsPerPage, and offset
        $stmt = $this->connection->prepare("SELECT * FROM items WHERE user_id = ? LIMIT ?, ?");
        $stmt->bind_param("iii", $user_id, $offset, $itemsPerPage); // Bind the parameters
        $stmt->execute(); // Execute the statement
        $result = $stmt->get_result(); // Get the result
        return $result;
    }

    // Function to get the total count of items for a specific user
    public function getUserItemsCount($user_id) {
        // Prepare the query with a placeholder for the user ID
        $stmt = $this->connection->prepare("SELECT COUNT(*) AS count FROM items WHERE user_id = ?");
        $stmt->bind_param("i", $user_id); // Bind the user ID parameter
        $stmt->execute(); // Execute the statement
        $result = $stmt->get_result(); // Get the result
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['count'];
        }
        
        return 0;
    }

        // Function to fetch a specific set of items for a user along with the respective bids, with support for pagination
        public function getUserItemsWithBids($user_id, $page, $itemsPerPage) {
            $offset = ($page - 1) * $itemsPerPage;
    
            // Prepare the query with placeholders for the user ID, itemsPerPage, and offset
            $stmt = $this->connection->prepare("SELECT items.*, bids.amount 
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
        public function getUserBidsCount($user_id) {
            // Prepare the query with a placeholder for the user ID
            $stmt = $this->connection->prepare("SELECT COUNT(*) as bid_count FROM bids WHERE user_id = ?");
            $stmt->bind_param("i", $user_id); // Bind the user ID parameter
            $stmt->execute(); // Execute the statement
            $result = $stmt->get_result(); // Get the result
            
            if ($result && $result->num_rows > 0) {
                $data = $result->fetch_assoc();
                return $data['bid_count'];
            }
            
            return 0;
        }
    
        public function getUserBids($user_id, $offset, $limit) {
            // SQL Injection here?
            #$sql = "SELECT items.id, items.name, items.start_price, items.created_at, bids.created_at, bids.amount FROM bids JOIN items ON items.id = bids.item_id WHERE bids.user_id = " . $user_id . " ORDER BY items.created_at DESC LIMIT " . $offset . ", " . $limit;
            $sql = "SELECT items.id, items.name, items.start_price, items.created_at, bids.created_at, bids.amount 
                    FROM bids 
                    JOIN items ON items.id = bids.item_id 
                    ORDER BY items.created_at DESC 
                    LIMIT " . $offset . ", " . $limit;

            // Execute the query
            $result = $this->connection->query($sql);
        
            // Return the result
            return $result;
        }

        // Function to fetch a user by username
    public function getUserByUsername($user_name) {
        // Prepare the query with a placeholder for the user name
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE user_name = ?");
        $stmt->bind_param("s", $user_name); // Bind the user name parameter
        $stmt->execute(); // Execute the statement
        $result = $stmt->get_result(); // Get the result

        // Check if a user with the given username exists
        if ($result && $result->num_rows > 0) {
            $user_data = $result->fetch_assoc();
            return $user_data;
        }

        return null;
    }
}
?>