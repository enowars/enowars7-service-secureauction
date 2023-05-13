# SecureAuction
SecureAuction is a web-based auction platform where users can create accounts, list items for auction, bid on items, and manage their auctions.

# Bid Information Vulnerability
The SecureAuction service contains a vulnerability in which bid information is not properly protected and can be accessed by unauthorized users. This vulnerability can be exploited by an attacker using SQL injection techniques to gain access to the bid information database.

# Prevention
To prevent the bid information vulnerability, input validation should be implemented to ensure that only authorized users are able to access the bid information database. Additionally, encryption and other security measures should be used to protect sensitive data.

# Application Structure
The SecureAuction application consists of several PHP scripts and classes that interact with a MySQL database to fetch and manipulate data. Here is an overview of the main files and their functionalities:

## config.php
This script is responsible for establishing a connection with the MySQL database. The database credentials are defined in this file, and a new mysqli object is created to handle the database connection.

## user.php
This file contains the User class, which handles operations related to a User in the system. It provides various methods, including checkLogin(), getUsers(), getUserById(), getUserItems(), getUserItemsCount(), getUserItemsWithBids(), and getUserBidsCount(). These methods are used to manage user-related information and interactions within the application.

## item.php
The item.php file contains the Item class, which handles operations related to the items in the system. It offers methods to retrieve items, fetch item details, and obtain the total number of items. These methods are used to manage item-related information within the application.

## bid.php
The bid.php file contains the Bid class, which handles operations related to the bids in the system. It provides methods to retrieve bids, place bids, obtain the highest bid for an item, and get a user's highest bid for an item. These methods are essential for managing the bidding process and maintaining bid-related information.

## index.php
The index.php script fetches and displays a list of items. It utilizes the getItems() method from the Item class and the getTotalItems() method for pagination purposes. This script allows users to view available items for auction.

## item_detail.php
The item_detail.php script fetches and displays the details of a specific item. It uses the getItemDetails() method from the Item class and the getHighestBid() method from the Bid class. This script provides users with in-depth information about an item they are interested in.

## my_profile.php
The my_profile.php script displays the items a user has bid on, along with the respective highest bids. It utilizes the getUserItemsWithBids() method from the User class and the getUserBidsCount() method for pagination. This script allows users to review their bidding activity and track their highest bids.

## place_bid.php
The place_bid.php script handles the bidding process. It verifies the bid and uses the placeBid() method from the Bid class to place a bid. This script ensures that users can participate in auctions and submit their bids securely.

In general, this application allows users to view items, access item details, bid on items, and view their own profile with the items they have bid on. The application implements a basic login system to authenticate users. It's important to note that every bid placed

## Todo
Setting up the flags and designing ways to exploit the application to capture those flags.