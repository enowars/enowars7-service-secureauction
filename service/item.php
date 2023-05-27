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
        public function createItemWithBid($userId, $itemName, $bidAmount) {
            // Prepare the SQL statement to insert a new item
            $stmt = $this->mysqli->prepare("INSERT INTO items (user_id, name, start_price) VALUES (?, ?, ?)");
            
            // Bind the variables to the statement as parameters
            $stmt->bind_param("iss", $userId, $itemName, $bidAmount);
        
            // Execute the statement
            $result = $stmt->execute();
        
            // If the query was successful, return the ID of the newly created item. Otherwise, return false.
            if ($result) {
                $itemId = $this->mysqli->insert_id;
        
                // Place a bid on the item using the bidAmount
                $bidStmt = $this->mysqli->prepare("INSERT INTO bids (item_id, user_id, amount) VALUES (?, ?, ?)");
                $bidStmt->bind_param("iii", $itemId, $userId, $bidAmount);
                $bidResult = $bidStmt->execute();
        
                if ($bidResult) {
                    return $itemId;
                } else {
                    // Failed to place bid, rollback the item creation
                    $deleteStmt = $this->mysqli->prepare("DELETE FROM items WHERE id = ?");
                    $deleteStmt->bind_param("i", $itemId);
                    $deleteStmt->execute();
        
                    return false;
                }
            } else {
                return false;
            }
        }
        
        

        // Define a method to get a page of items from the database
        public function getItems($page, $itemsPerPage) {
            // Calculate the offset for the SQL query based on the current page number and the number of items per page
            $offset = ($page - 1) * $itemsPerPage;
            
            // Prepare the SQL statement
            $stmt = $this->mysqli->prepare("SELECT * FROM items LIMIT ? OFFSET ?");
            
            // Bind the parameters to the statement
            $stmt->bind_param("ii", $itemsPerPage, $offset);
            
            // Execute the statement
            $stmt->execute();
            
            // Get the result
            $result = $stmt->get_result();
            
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
            // Prepare the SQL statement
            $stmt = $this->mysqli->prepare("SELECT * FROM items WHERE id = ?");
            
            // Bind the item ID to the statement
            $stmt->bind_param("i", $itemId);
            
            // Execute the statement
            $stmt->execute();
            
            // Get the result
            $result = $stmt->get_result();
            
            // If the query was successful, return the item details. Otherwise, return false.
            if ($result) {
                return $result->fetch_assoc();
            } else {
                echo 'Query Error: ' . $this->mysqli->error;
                return false;
            }
        }
    }
?>
