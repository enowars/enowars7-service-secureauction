# SecureAuction
SecureAuction is a web-based auction platform where users can create accounts, list items for auction, bid on items, and manage their auctions.

# Application Structure
The SecureAuction application consists of several PHP scripts and classes that interact with a MySQL database to fetch and manipulate data. Here is an overview of the main files and their functionalities:

## config.php
This script is responsible for establishing a connection with the MySQL database. The database credentials are defined in this file, and a new mysqli object is created to handle the database connection.

## index.php
The index.php is responsible of handling both user registration (signup) and user authentication (login) for the auction system.

If the action is signup, the code first checks if a user with the same username already exists. If not, it hashes the provided password and, in the case of a 'Premium' user, generates RSA keys. These details are then stored in the users table in the database. If the signup is successful, user data (username, user type, user id) is stored in the session. If the user is 'Premium', the private key is also stored in the session and the user is redirected to display_key.php. If the user is 'Regular', they are redirected to user_index.php.

If the action is login, the code checks if a user with the provided username exists. If the user exists, the password provided is verified against the stored hashed password in the database. If the password is correct, user data (username, user type, user id) is stored in the session and the user is redirected to user_index.php.

## display_key.php
The display_key.php file is responsible for displaying a user's private key when they first sign up. The private key is retrieved from a session variable, split into manageable chunks, and displayed on a webpage. The private key is then removed from the session to ensure that it isn't shown again if the user refreshes the page. The user can then proceed to the main user page by clicking a "Proceed" button.

## user.php
This file contains the User class, which handles operations related to a User in the system. It provides various methods, including checkLogin(), getUsers(), getUserById(), getUserItems(), getUserItemsCount(), getUserItemsWithBids(), and getUserBidsCount(). These methods are used to manage user-related information and interactions within the application. The user.php is also generatig RSA keys for Premium users.

## my_profile.php
The my_profile.php script is a part of a user profile page for a bidding/auction system. It shows the items an user has placed bids on, along with an option to change the bid and decrypd bids for Premium users. 

## item.php
The item.php file contains the Item class, which handles operations related to the items in the system. It offers methods to retrieve items, fetch item details, and obtain the total number of items. These methods are used to manage item-related information within the application.

## create_item.php
 It's responsible for handling the item creation form submission, validating inputs, and invoking the createItem() method from the Item class to insert the new item into the database. After successful item creation, it redirects the user to the item details page. If the operation fails, an error message is displayed. 

## item_detail.php
Displays the details of a specific auction item, as well as the highest bid the current user has placed on it. It also provides a form for the user to place a new bid.

## bid.php
The bid.php file contains the Bid class, which handles operations related to the bids in the system. It provides methods to retrieve bids, place bids, obtain the highest bid for an item, and get a user's highest bid for an item. These methods are essential for managing the bidding process and maintaining bid-related information. Furthermore, this file provides methods to encrypt and decrypt bids for Premium users.

## change_bid.php
It's responsible for handling the bid form submission, validating inputs, checking permissions, and invoking the placeBid() method from the Bid class to update or insert the bid into the database. After successful bid placement, it redirects the user to the item details page. If the operation fails, an error message is displayed.

## decrypt_bid.php
The decrypt_bid.php file is responsible for decrypting an encrypted bid. When called, it retrieves the private key, item id, and user id from a POST request. It creates a User and a Bid object, retrieves the public key associated with the user id, and uses the private and public keys to decrypt the amount of the bid. This decrypted bid amount is then displayed.

## place_bid.php
The place_bid.php script handles the bidding process. It verifies the bid and uses the placeBid() method from the Bid class to place a bid. This script ensures that users can participate in auctions and submit their bids.

## logout.php
The logout.php script is responsible for logging out a user. It destroys the session and redirects the user to the login page.

# Note:
First and second Vuln are working on server side. But unfortunately i did not have time to finish the checker for the second vulnerability. I have the code for it but it is not working properly. I will try to fix it and submit it later. Therefore the participants have only one vulnerability to exploit and patch. 

