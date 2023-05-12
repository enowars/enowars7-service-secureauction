<?php
    class Item {
        private $mysqli;

        public function __construct($con) {
            $this->mysqli = $con;
        }

        public function getItems($page, $itemsPerPage) {
            $offset = ($page - 1) * $itemsPerPage;
            $query = "SELECT * FROM items LIMIT $itemsPerPage OFFSET $offset";
            $result = $this->mysqli->query($query);
        
            if ($result) {
                return $result;
            } else {
                echo 'Query Error: ' . $this->mysqli->error;
                return false;
            }
        }

        public function getTotalItems() {
            $query = "SELECT COUNT(*) as total FROM items";
            $result = $this->mysqli->query($query);
        
            if ($result) {
                return $result->fetch_assoc()['total'];
            } else {
                echo 'Query Error: ' . $this->mysqli->error;
                return false;
            }
        }

        public function getItemDetails($itemId) {
            $query = "SELECT * FROM items WHERE id = $itemId";
            $result = $this->mysqli->query($query);
            
            if ($result) {
                return $result->fetch_assoc();
            } else {
                echo 'Query Error: ' . $this->mysqli->error;
                return false;
            }
        }
        
        
    }
?>