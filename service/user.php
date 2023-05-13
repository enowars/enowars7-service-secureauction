<?php
// The User class is designed to handle operations related to a User in the system
class User {
    // Private class property to hold the MySQL connection
    private $connection;

     // Class constructor that receives a MySQL connection
    public function __construct($connection) {
        // Assigning the received connection to the private class property
        $this->connection = $connection;
    }

    // Function to check if the user is logged in, by verifying if the session variable 'user_id' is set
    public function checkLogin() {
        if (isset($_SESSION['user_id'])) {
            // Fetching the user ID from the session
            $id = $_SESSION['user_id'];

            // Query to fetch user data from the database for the given user ID
            $query = "SELECT * FROM users WHERE user_id = '$id' LIMIT 1";
            $result = mysqli_query($this->connection, $query);

            // If the query was successful and returned at least one row, return the user data
            if ($result && mysqli_num_rows($result) > 0) {
                $user_data = mysqli_fetch_assoc($result);
                return $user_data;
            }
        } else {
            // If the user is not logged in, redirect them to the login page
            header("Location: login.php");
            die;
        }
    }

    // Function to fetch all users from the database
    public function getUsers() {
        $query = "SELECT * FROM users";
        $result = mysqli_query($this->connection, $query);
        return $result;
    }

    // Function to fetch a specific user from the database by their user ID
    public function getUserById($user_id) {
        $query = "SELECT * FROM users WHERE user_id = '$user_id'";
        $result = mysqli_query($this->connection, $query);
        return $result;
    }

    // Function to fetch a specific set of items for a user, with support for paginatio
    public function getUserItems($user_id, $page, $itemsPerPage) {
        $offset = ($page - 1) * $itemsPerPage;
        $stmt = $this->connection->prepare("SELECT * FROM items WHERE user_id = ? LIMIT ? OFFSET ?");
        $stmt->bind_param("iii", $user_id, $itemsPerPage, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }

    // Function to get the total count of items for a specific user
    public function getUserItemsCount($user_id) {
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM items WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['count'];
        }
        return 0;
    }

     // Function to fetch a specific set of items for a user along with the respective bids, with support for pagination
    public function getUserItemsWithBids($userId, $page, $itemsPerPage) {
        $offset = ($page - 1) * $itemsPerPage;
        $query = "SELECT items.*, bids.amount 
        FROM items 
        INNER JOIN (SELECT item_id, MAX(amount) as amount FROM bids WHERE user_id = $userId GROUP BY item_id) as bids 
        ON items.id = bids.item_id 
        ORDER BY items.created_at DESC 
        LIMIT $offset, $itemsPerPage";
        
        $result = $this->connection->query($query);
    
        if ($result) {
            return $result;
        } else {
            echo 'Query Error: ' . $this->connection->error;
            return false;
        }
    }
    
    // Function to get the total count of bids for a specific user
    public function getUserBidsCount($userId) {
        $query = "SELECT COUNT(*) as bid_count FROM bids WHERE user_id = $userId";
        
        $result = $this->connection->query($query);
    
        if ($result) {
            $data = $result->fetch_assoc();
            return $data['bid_count'];
        } else {
            echo 'Query Error: ' . $this->connection->error;
            return false;
        }
    }  
}
?>
