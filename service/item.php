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
        $startPrice = $bidAmount; // Set the start_price to the bid amount by default

        // Prepare the SQL statement to insert a new item
        $stmt = $this
            ->mysqli
            ->prepare("INSERT INTO items (user_id, name, start_price, item_type) VALUES (?, ?, ?, ?)");

        // Bind the variables to the statement as parameters
        $stmt->bind_param("isss", $userId, $itemName, $startPrice, $itemType);

        // Execute the statement
        $result = $stmt->execute();

        // If the query was successful, return the ID of the newly created item. Otherwise, return false.
        if ($result)
        {
            $itemId = $this
                ->mysqli->insert_id;

            // Place a bid on the item using the startPrice
            $bidStmt = $this
                ->mysqli
                ->prepare("INSERT INTO bids (item_id, user_id, amount) VALUES (?, ?, ?)");
            $bidStmt->bind_param("iis", $itemId, $userId, $startPrice);
            $bidResult = $bidStmt->execute();

            if ($bidResult)
            {
                return $itemId;
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
            SELECT items.*, max(bids.amount) as bidamount, users.public_key_e, users.public_key_n 
            FROM items
            LEFT JOIN bids ON items.id = bids.item_id
            LEFT JOIN users ON items.user_id = users.user_id
            GROUP BY items.id
            LIMIT ? OFFSET ?
            ");
            $stmt->bind_param("ii", $itemsPerPage, $offset);
        }
        else
        {
            $stmt = $this
                ->mysqli
                ->prepare("SELECT * FROM items WHERE item_type = 'REGULAR' LIMIT ? OFFSET ?");
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
        // Define the SQL query to count the total number of items
        $query = "SELECT COUNT(*) as total FROM items";
        // Execute the query
        $result = $this
            ->mysqli
            ->query($query);

        // If the query was successful, return the total number of items. Otherwise, print an error message and return false.
        if ($result)
        {
            return $result->fetch_assoc() ['total'];
        }
        else
        {
            echo 'Query Error: ' . $this
                ->mysqli->error;
            return false;
        }
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
}
?>
