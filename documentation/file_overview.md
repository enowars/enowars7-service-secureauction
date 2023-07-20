## Service File Overview
### db_connect.php
This PHP script serves as a connector to a MySQL database. It establishes a connection that other scripts can use to interact with the database, fetching or modifying the data as required. Furthermore, it uses environment variables for database connection details. This allows for greater security and flexibility, as the connection details are not hard-coded into the script.

### index.php
The index.php is responsible of handling both user registration (signup) and user authentication (login) for the auction system.

If the action is signup, the code first checks if a user with the same username already exists. If not, it hashes the provided password and, in the case of a 'Premium' user, generates RSA keys. These details are then stored in the users table in the database. If the signup is successful, user data (username, user type, user id) is stored in the session. If the user is 'Premium', the private key is also stored in the session and the user is redirected to display_key.php. If the user is 'Regular', they are redirected to user_index.php.

If the action is login, the code checks if a user with the provided username exists. If the user exists, the password provided is verified against the stored hashed password in the database. If the password is correct, user data (username, user type, user id) is stored in the session and the user is redirected to user_index.php.

### display_key.php
The display_key.php file is responsible for displaying a user's private key when they first sign up. The private key is retrieved from a session variable, split into manageable chunks, and displayed on a webpage. The private key is then removed from the session to ensure that it isn't shown again if the user refreshes the page. The user can then proceed to the main user page by clicking a "Proceed" button.

### user.php
This file contains the User class, which handles operations related to a User in the system.  This class includes methods for fetching users and user-related data, as well as operations related to login sessions, and generating RSA keys for encryption purposes. As well as ranking the bids placed on an item. For premium users, bids are decrypted before ranking.

### my_profile.php
The my_profile.php script is a part of a user profile page for a auction service. It shows the items the user placed bids on, as well as received bids for the items they placed for the auction. Moreover, it provides a form for the premium user to decrypt and rank the bids they received for a specific item. The same goes for regualr user but without the decryption part.

### item.php
The item.php script defines an Item class, which is the main object to handle the functionality related to items in the auction system. The class is designed to connect to the database, add new items, retrieve information about items and their associated bids, and manipulate the item data as required.

### create_item.php
 It's responsible for handling the item creation form submission, validating inputs, and invoking the createItem() method from the Item class to insert the new item into the database. After successful item creation, it redirects the user to the item details page. If the operation fails, an error message is displayed. 

### item_detail.php
Displays the details of a specific auction item, as well as the highest bid the current user has placed on it. It also provides a form for the user to place a new bid.

### item_info.php
Upon receiving a POST request, it extracts required information including the private key, bid amount, and identifiers. The script then decrypts the bid using the provided private key and displays it on an HTML page.

### bid.php
The bid.php file contains the Bid class, which handles operations related to the bids in the system. It provides methods to retrieve bids, place bids, obtain the highest bid for an item, and get a user's highest bid for an item. These methods are essential for managing the bidding process and maintaining bid-related information. Furthermore, this file provides methods to encrypt and decrypt bids for Premium users.

### change_bid.php
It's responsible for handling the bid form submission, validating inputs, checking permissions, and invoking the placeBid() method from the Bid class to insert the bid into the database. After successful bid placement, it redirects the user to the item details page. If the operation fails, an error message is displayed.

### decrypt_bid.php
The decrypt_bid.php file is responsible for decrypting an encrypted bid. When called, it retrieves the private key, item id, and user id from a POST request. It creates a User and a Bid object, retrieves the public key associated with the user id, and uses the private and public keys to decrypt the amount of the bid. This decrypted bid amount is then displayed.

### place_bid.php
The place_bid.php script handles the bidding process. It verifies the bid and uses the placeBid() method from the Bid class to place a bid. This script ensures that users can participate in auctions and submit their bids.

### logout.php
The logout.php script is responsible for logging out a user. It destroys the session and redirects the user to the login page.