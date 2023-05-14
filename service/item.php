<?php
    // Define the Item class
    class Item {
        // Declare a private property to hold the mysqli database connection object
        private $mysqli;

        // Define the constructor method which is automatically called when a new Item object is created
        public function __construct($con) {
            // Set the $mysqli property to the mysqli connection object passed as an argument to the constructor
            $this->mysqli = $con;
        }

        // Define a method to create a new item in the database
        public function createItem($userId, $itemName, $startPrice) {
            // Escape special characters in the item name
            $itemName = $this->mysqli->real_escape_string($itemName);

            // Prepare the SQL statement to insert a new item
            $query = "INSERT INTO items (user_id, name, start_price) VALUES ($userId, '$itemName', $startPrice)";

            // Execute the query
            $result = $this->mysqli->query($query);

            // If the query was successful, return the ID of the newly created item. Otherwise, return false.
            if ($result) {
                return $this->mysqli->insert_id;
            } else {
                return false;
            }
        }

        // Define a method to get a page of items from the database
        public function getItems($page, $itemsPerPage) {
            // Calculate the offset for the SQL query based on the current page number and the number of items per page
            $offset = ($page - 1) * $itemsPerPage;
            // Define the SQL query to select a page of items
            $query = "SELECT * FROM items LIMIT $itemsPerPage OFFSET $offset";
            // Execute the query
            $result = $this->mysqli->query($query);
        
            // If the query was successful, return the result. Otherwise, print an error message and return false.
            if ($result) {
                return $result;
            } else {
                echo 'Query Error: ' . $this->mysqli->error;
                return false;
            }
        }

        // Define a method to get the total number of items in the database
        public function getTotalItems() {
            // Define the SQL query to count the total number of items
            $query = "SELECT COUNT(*) as total FROM items";
            // Execute the query
            $result = $this->mysqli->query($query);
        
            // If the query was successful, return the total number of items. Otherwise, print an error message and return false.
            if ($result) {
                return $result->fetch_assoc()['total'];
            } else {
                echo 'Query Error: ' . $this->mysqli->error;
                return false;
            }
        }

        // Define a method to get the details of a specific item
        public function getItemDetails($itemId) {
            // Define the SQL query to select the item with the given id
            $query = "SELECT * FROM items WHERE id = $itemId";
            // Execute the query
            $result = $this->mysqli->query($query);
            
            // If the query was successful, return the item details. Otherwise, print an error message and return false.
            if ($result) {
                return $result->fetch_assoc();
            } else {
                echo 'Query Error: ' . $this->mysqli->error;
                return false;
            }
        }
        
        
    }
?>
