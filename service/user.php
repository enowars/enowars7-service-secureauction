<?php

class User {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function checkLogin() {
        if (isset($_SESSION['user_id'])) {
            $id = $_SESSION['user_id'];

            $query = "SELECT * FROM users WHERE user_id = '$id' LIMIT 1";
            $result = mysqli_query($this->connection, $query);

            if ($result && mysqli_num_rows($result) > 0) {
                $user_data = mysqli_fetch_assoc($result);
                return $user_data;
            }
        } else {
            header("Location: login.php");
            die;
        }
    }

    public function getUsers() {
        $query = "SELECT * FROM users";
        $result = mysqli_query($this->connection, $query);
        return $result;
    }

    public function getUserById($user_id) {
        $query = "SELECT * FROM users WHERE user_id = '$user_id'";
        $result = mysqli_query($this->connection, $query);
        return $result;
    }

    public function getUserItems($user_id, $page, $itemsPerPage) {
        $offset = ($page - 1) * $itemsPerPage;
        $stmt = $this->connection->prepare("SELECT * FROM items WHERE user_id = ? LIMIT ? OFFSET ?");
        $stmt->bind_param("iii", $user_id, $itemsPerPage, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }

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

    public function getUserItemsWithBids($userId, $page, $itemsPerPage) {
        $offset = ($page - 1) * $itemsPerPage;
        $query = "SELECT items.*, bids.amount FROM items 
                  INNER JOIN bids ON items.id = bids.item_id 
                  WHERE bids.user_id = $userId 
                  ORDER BY bids.created_at DESC 
                  LIMIT $offset, $itemsPerPage";
        
        $result = $this->connection->query($query);
    
        if ($result) {
            return $result;
        } else {
            echo 'Query Error: ' . $this->connection->error;
            return false;
        }
    }
    
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

class RandomNumberGenerator {
    public static function generateRandomNumber($length) {
        $text = "";
        if ($length < 5) {
            $length = 5;
        }
        $len = rand(4, $length);
        for ($i = 0; $i < $len; $i++) {
            $text .= rand(0, 9);
        }
        return $text;
    }
}

?>
