# SecureAuction
SecureAuction is a web-based auction platform where users can create accounts, list items for auction, bid on items, and manage their auctions.

# Bid Information Vulnerability
The SecureAuction service contains a vulnerability in which bid information is not properly protected and can be accessed by unauthorized users. This vulnerability can be exploited by an attacker using SQL injection techniques to gain access to the bid information database. Note: Flags are bids.

# Prevention
To prevent the bid information vulnerability, input validation should be implemented to ensure that only authorized users are able to access the bid information database. Additionally, encryption and other security measures should be used to protect sensitive data.

# Application Structure
The SecureAuction application consists of several PHP scripts and classes that interact with a MySQL database to fetch and manipulate data. Here is an overview of the main files and their functionalities:

## config.php
This script is responsible for establishing a connection with the MySQL database. The database credentials are defined in this file, and a new mysqli object is created to handle the database connection.

## index.php
The index.php script fetches and displays a list of items. It utilizes the getItems() method from the Item class and the getTotalItems() method for pagination purposes. This script allows users to view available items for auction.

## user.php
This file contains the User class, which handles operations related to a User in the system. It provides various methods, including checkLogin(), getUsers(), getUserById(), getUserItems(), getUserItemsCount(), getUserItemsWithBids(), and getUserBidsCount(). These methods are used to manage user-related information and interactions within the application.

## my_profile.php
The my_profile.php script is a part of a user profile page for a bidding/auction system. It shows the items a user has placed bids on, along with an option to change the bid.

## item.php
The item.php file contains the Item class, which handles operations related to the items in the system. It offers methods to retrieve items, fetch item details, and obtain the total number of items. These methods are used to manage item-related information within the application.

## item_detail.php
Displays the details of a specific auction item, as well as the highest bid the current user has placed on it. It also provides a form for the user to place a new bid.

## bid.php
The bid.php file contains the Bid class, which handles operations related to the bids in the system. It provides methods to retrieve bids, place bids, obtain the highest bid for an item, and get a user's highest bid for an item. These methods are essential for managing the bidding process and maintaining bid-related information. This class, however, has a critical vulnerability related to SQL Injection. The parameters $itemId, $userId, and $amount are directly interpolated into the SQL queries. An attacker could potentially manipulate these parameters to execute arbitrary SQL commands.

## change_bid.php
It's responsible for handling the bid form submission, validating inputs, checking permissions, and invoking the placeBid() method from the Bid class to update or insert the bid into the database. This script, as with bid.php, is vulnerable to SQL Injection as it directly uses form data ($itemId, $userId, $newBid) in SQL queries without sanitizing it. This vulnerability can be mitigated by using prepared statements.

## show_all_bids.php
Displaying all bids made by a logged-in user.

## create_item.php
 It's responsible for handling the item creation form submission, validating inputs, and invoking the createItem() method from the Item class to insert the new item into the database. After successful item creation, it redirects the user to the item details page. If the operation fails, an error message is displayed. 

## place_bid.php
The place_bid.php script handles the bidding process. It verifies the bid and uses the placeBid() method from the Bid class to place a bid. This script ensures that users can participate in auctions and submit their bids.

## login.php
This PHP script is a simple login page for a user to enter their username and password. It is not secure against SQL injection attacks, as it directly interpolates the username into the SQL query without any sanitization or escaping. It's recommended to use prepared statements or a database abstraction layer that automatically escapes variables. Furthermore, the password is stored and compared in plaintext, which is a severe security risk. It's recommended to store hashed and salted passwords instead, and to compare the hashed (and salted) version of the input password to the stored hash.

## signup.php
Script for user registration. Has a lot of issues like username not unique, random number cause collision etc.

## Security issues
login.php:
User input is not properly validated or sanitized before being used in the SQL query. Passwords are stored in plain text, which is not secure. It is recommended to use hashing and salting techniques to store passwords securely. SOLVED!!!

signup.php:
User input is not properly validated or sanitized before being used in the SQL query. Passwords are stored in plain text, which is not secure. It is recommended to use hashing and salting techniques to store passwords securely. SOLVED!!!

item_detail.php:
Lack of input validation and sanitization when retrieving and displaying item details. User input should be properly validated, and any user-entered data should be sanitized or properly escaped before being displayed to prevent attacks. SOLVED!!!

place_bid.php:
Lack of input validation and sanitization when retrieving the bid amount from user input. This can lead to potential security vulnerabilities, including SQL Injection attacks. User input should be properly validated and sanitized before being used in SQL queries to prevent SQL Injection attacks.

user.php:
SQL Injection vulnerability in the getUserById() and getUserItems() methods. User input is not properly validated or sanitized before being used in SQL queries. Lack of input validation and sanitization in getUserItemsWithBids() and getUserBidsCount() methods. User input should be properly validated and sanitized to prevent potential security vulnerabilities. SOLVED!!!

## TODO
SQL Injection Prevention but let one free, Password Hashing, Enhanced Input Validation and Sanitization
Bid Encryption, Rigorous Testing for Flag Security


