<?php
// Define the Item class
class Item
{
    // Declare a private property to hold the mysqli database connection object
    private $mysqli;

    // Define the constructor method which is automatically called when a new Item object is created
    public function __construct($con)
    {
        // Set the $mysqli property to the mysqli connection object passed as an argument to the constructor
        $this->mysqli = $con;
    }

    // Define a method to create a new item in the database
    public function createItemWithBid($userId, $itemName, $bidAmount, $itemType)
    {
        // If the bidAmount is a numeric string, set it as the start price. If not, it's a flag and the start price should be 0.
        $startPrice = is_numeric($bidAmount) ? $bidAmount : 0;

        // Prepare the SQL statement to insert a new item with the given name, start price, item type and end time
        $stmt = $this
            ->mysqli
            ->prepare("INSERT INTO items (user_id, name, start_price, item_type, end_time) VALUES (?, ?, ?, ?, ADDDATE(CURRENT_TIMESTAMP, INTERVAL 15 MINUTE))");

        // Bind the variables to the statement as parameters
        $stmt->bind_param("isss", $userId, $itemName, $startPrice, $itemType);

        // Execute the statement
        $result = $stmt->execute();

        // If the query was successful, return the ID of the newly created item. Otherwise, return false.
        if ($result)
        {
            $itemId = $this
                ->mysqli->insert_id;

            // Create an instance of the User class
            $user = new User($this->mysqli);
            // Get the user data
            $userData = $user->getUserById($userId);

            // Check the user type
            $userType = $userData['user_type'];

            // Set encrypted bid for PREMIUM items
            if($userType === 'PREMIUM') {
                // Create an instance of the Bid class
                $bid = new Bid($this->mysqli);
                // Enc. Bid Amount
                $encryptedAmount = $bid->placeBid($itemId, $userId, $bidAmount);
            }
            else{
                // For regular items use the bid amount, either it will be the start price or the flag
                $bidStmt = $this->mysqli
                                ->prepare("INSERT INTO bids (item_id, user_id, amount) VALUES (?, ?, ?)");
                $bidStmt->bind_param("iis", $itemId, $userId, $bidAmount);
                $bidResult = $bidStmt->execute();
            }
            if ($userType === 'PREMIUM'){
                return ['itemId' => $itemId, 'encryptedAmount' => $encryptedAmount];
            }
            if ($bidResult)
            {
                return ['itemId' => $itemId];
            }
        }
        else
        {
            return false;
        }
    }

    // Get the items, the bids for that item and the e and n values for the user who placed the bid
    public function getItems($page, $itemsPerPage, $userType)
    {
        // Calculate the offset for the SQL query based on the current page number and the number of items per page
        $offset = ($page - 1) * $itemsPerPage;

        // Prepare the SQL statement
        if ($userType === 'PREMIUM')
        {
            $stmt = $this->mysqli->prepare("
            SELECT items.*, bids.amount AS bidamount, users.public_key_e, users.public_key_n 
        FROM items
        LEFT JOIN bids ON items.id = bids.item_id
        LEFT JOIN users ON bids.user_id = users.user_id
        WHERE TIMESTAMPDIFF(SECOND, items.created_at, NOW()) < 600 
        ORDER BY items.id DESC
        LIMIT ? OFFSET ?
            "); 
            if($stmt === false) {
                die('prepare() failed: ' . htmlspecialchars($this->mysqli->error));
            }
    
            $stmt->bind_param("ii", $itemsPerPage, $offset);
        }
        else
        {
            $stmt = $this
                ->mysqli
                ->prepare("SELECT * FROM items WHERE item_type = 'REGULAR' 
                                                AND TIMESTAMPDIFF(SECOND, items.created_at, NOW()) < 600 
                                                ORDER BY items.id DESC
                                                LIMIT ? OFFSET ?"); 
            $stmt->bind_param("ii", $itemsPerPage, $offset);
        }

        // Execute the statement
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        // If the query was successful, return the result. Otherwise, print an error message and return false.
        if ($result)
        {
            return $result;
        }
        else
        {
            echo 'Query Error: ' . $this
                ->mysqli->error;
            return false;
        }
    }

    // Define a method to get the total number of items in the database
    public function getTotalItems()
    {
         // Count the total number of items
        $query = "SELECT COUNT(*) as total FROM items";
        $result = $this->mysqli->query($query);
        $totalItems = $result->fetch_assoc()['total'];

        // Count the number of item_id in bids table that appear more than once
        $query = "SELECT COUNT(item_id) as additional 
                FROM bids 
                GROUP BY item_id 
                HAVING COUNT(item_id) > 1";

        $result = $this->mysqli->query($query);
        $additionalBids = 0;
        while($row = $result->fetch_assoc()) {
            $additionalBids += $row['additional'] - 1; // Subtract 1 because one bid is counted in totalItems
        }

        return $totalItems + $additionalBids;
        }

    // Define a method to get the details of a specific item
    public function getItemDetails($itemId)
    {
        // Prepare the SQL statement
        $stmt = $this
            ->mysqli
            ->prepare("SELECT * FROM items WHERE id = ?");

        // Bind the item ID to the statement
        $stmt->bind_param("i", $itemId);

        // Execute the statement
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        // If the query was successful, return the item details. Otherwise, return false.
        if ($result)
        {
            return $result->fetch_assoc();
        }
        else
        {
            echo 'Query Error: ' . $this
                ->mysqli->error;
            return false;
        }
    }

    public function getSearchedItems($user_type = null, $name = null, $item_id = null)
    {
        // Prepare the base query
        $query = "SELECT items.*, bids.amount AS bidamount, users.public_key_e, users.public_key_n 
                FROM items 
                LEFT JOIN bids ON items.id = bids.item_id 
                LEFT JOIN users ON bids.user_id = users.user_id 
                WHERE TIMESTAMPDIFF(SECOND, items.created_at, NOW()) < 600 AND";
    
        // Determine whether to search by name or id or both
        if (!is_null($name) && !is_null($item_id)) {
            $query .= " (LOWER(items.name) LIKE LOWER(?) OR items.id = ?)";
        } elseif (!is_null($name)) {
            $query .= " LOWER(items.name) LIKE LOWER(?)";
        } else {  
            $query .= " items.id = ?";
        }
    
        // Add condition for user type (if user_type is 'REGULAR', only show 'REGULAR' items with status 'OPEN')
        if ($user_type == 'REGULAR') {
            $query .= " AND items.item_type = 'REGULAR'";
        }
    
        // Prepare the query
        $stmt = $this->mysqli->prepare($query);
    
        // Bind parameters
        if (!is_null($name) && !is_null($item_id)) {
            $stmt->bind_param('si', $name, $item_id);
        } elseif (!is_null($name)) {
            $stmt->bind_param('s', $name);
        } else {  
            $stmt->bind_param('i', $item_id);
        }
    
        // Execute the query and return the result
        $stmt->execute();
        return $stmt->get_result();
    }   
}
?>
