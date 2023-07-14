# How the service works in general
The SecureAuction application is a web-based auction platform where users can create accounts, list items for auction, bid on items, change the the bids they made, generally manage there bids and items on the auction. The application handles regular and premium users differently. Regular users can create accounts, list items for auction, bid on items, and change the bids they made. They can also view the bids they received for their items and the bids they placed on other items. Furthermore, users have the ability to view the ranking of the bids they have received for the items they have listed in the auction. Premium users can do all of the above, as well as decrypt and ranking the bids they received for their items. The application uses RSA encryption to encrypt bids and decrypt them for premium users. The items are only 10 minutes on the auction afterwards they are removed (not visible) anymore. In addition to the primary functions, the SecureAuction application offers a dynamic countdown timer which is displayed on items still open for auction. Furthermore, a search bar function which is integrated into the platform.

## Database
`init.sql` sets up the MySQL `secureauction` database, establishing three tables (`users`, `items`, `bids`). Additionally, it creates a new user `appuser` with limited privileges (SELECT, INSERT) for secure access to the database. The script also sets the MySQL server timezone to UTC.

## Vulnerabilities & Fixes
The Secure Auction Service has vulnerabilities in its two flag stores:
- **Flag Store 1:** Contains two vulnerabilities.
- **Flag Store 2:** Contains one vulnerability.
The fixed version of the service is available in the `my_fix_branch` of the [Enowars7 Secure Auction repository](https://github.com/enowars/enowars7-service-secureauction/tree/my_fix_branch) on GitHub.

## IDOR Vulnerability (Flagstore 1)
1. **Insecure Direct Object References (IDOR) in my_profile.php**
The script takes a user_id as a GET parameter, which is inherently risky. The code then checks if this user_id is different from the user_id stored in the session (i.e., the logged-in user). If it is different, and the user_id is numeric, it stops the script and displays "Unauthorized access". However, it doesn't stop if the user_id isn't numeric, meaning that an attacker could potentially access other users' bids by providing a non-numeric user_id in the GET parameter.
```php
    // Take user_id as a GET parameter
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : $_SESSION['user_id'];
    // Check if the user_id matches the one stored in the session (current logged-in user)
    if ($user_id != $_SESSION['user_id']) {
        // If user_id is a number, it's possibly a legitimate user_id, so abort the script.
        if (is_numeric($user_id)) {
            die("Unauthorized access.");
        }
    }
```
## Pre-requisites
Before proceeding with the steps to reproduce the expoit, please execute the script `demo_data.py` to create some demo data in the system. This script signs up some users and creates an item for each user.

```python
python demo_data.py
```

### Steps to Reproduce the Exploit

1. Launch a web browser and log into the SecureAuction application.

2. After successful login, navigate to the **My Profile** page. The URL in your browser should be something similar to:

    ```
    http://localhost:8181/my_profile.php
    ```

3. Now, replace the `user_id` parameter in the URL with a non-numeric value. For example:

    ```
    http://localhost:8181/my_profile.php?user_id='1'
    ```
    
4. Press Enter to load the URL with the modified `user_id` parameter.

5. The application is vulnerable to insecure direct object references (IDOR), it will display the data of the user corresponding to the injected `user_id` value, even though this ID is non-numeric. In our case, this vulnerability exposes plain or encrypted bids corresponding to the injected `user_id`.

### Recommendation to Fix the Vuln:
To fix this issue, it is suggested to use the `intval()` function in PHP to convert the `user_id` parameter to an integer before comparing it with the `user_id` stored in the session. This way, any non-numeric characters included in the `user_id` would be discarded, enhancing the security of the user_id verification process.

Here's an example of how to modify your code:

```php
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $_SESSION['user_id'];
    // Check if the user_id matches the one stored in the session (current logged-in user)
    if ($user_id != $_SESSION['user_id']) {
        die("Unauthorized access.");
    }
 ```

## SQL Injection (Flagstore 1)
1. **SQL Injection in user.php**: The `getUserBids` function in the code is vulnerable to SQL injection as it directly incorporates user input into the SQL query without proper sanitization or parameterization.
```php
    public function getUserBids($user_id, $offset, $limit)
    {
        $sql = "SELECT  items.id as item_id, 
                        items.name as item_name, 
                        items.start_price, 
                        items.item_type, 
                        items.created_at as item_created_at,
                        bids.user_id as bidder_id, 
                        bids.created_at as bid_created_at, 
                        bids.amount as bid_amount
                FROM items 
                JOIN bids ON items.id = bids.item_id 
                WHERE items.user_id = " . $user_id . " 
                ORDER BY bids.created_at DESC, bids.amount DESC LIMIT " . $offset . ", " . $limit;

        // Execute the query
        $result = $this
            ->connection
            ->query($sql);

        // Return the result
        return $result;  
    }
```
### Steps to Reproduce the Exploit

1. Launch a web browser and navigate to the **My Profile** page in the SecureAuction application. The URL in the address bar should resemble the following:

    ```
    http://localhost:8181/my_profile.php
    ```

2. In the URL, replace the `user_id` parameter with the SQL Injection payload: `1 OR 1`. Your URL should now look like this:

    ```
    http://localhost:8181/my_profile.php?user_id= 1 OR 1
    ```

3. Press Enter to load the manipulated URL. The application is vulnerable to SQL Injection, the **My Profile** page will now display all the bids of all users, not just the logged-in user.

### Recommendation to Fix the Vuln
The use of Prepared Statements is recommended to prevent SQL Injection attacks. Prepared Statements ensure that the SQL and the data are parsed separately, making it impossible for an attacker to inject malicious SQL.

Here's an example of how the code can be modified using prepared statements:

```php
public function getUserBids($user_id, $offset, $limit)
{
    $sql = "SELECT items.id as item_id, 
                    items.name as item_name, 
                    items.start_price, 
                    items.item_type, 
                    items.created_at as item_created_at,
                    bids.user_id as bidder_id, 
                    bids.created_at as bid_created_at, 
                    bids.amount as bid_amount
            FROM items 
            JOIN bids ON items.id = bids.item_id 
            WHERE items.user_id = ? 
            ORDER BY bids.created_at DESC, 
                     bids.amount DESC LIMIT ?, ?";

    $stmt = $this->connection->prepare($sql); // Prepare the query
    $result = $stmt->bind_param("iii", $user_id, $offset, $limit); // Bind the parameters
    $result = $stmt->execute(); // Execute the query
    $result = $stmt->get_result(); // Get the result
    $stmt->close(); 

    // Return the result
    return $result;
}
```
## RSA Key Generation Vulnerability Due to Prime Number Dependence (Flagstore 2)

### Description:
The `generate_stateful_rsa_keys` function, located in `user.php`, generates RSA keys used for encrypting and decrypting bids. Here's the workflow:

1. During signup, a user (for instance, Bob) has RSA keys generated and stored. The public key is associated with Bob's user profile, while the private key is displayed only once for Bob to store separately.
2. When creating an auction item, Bob's public key is associated with the item and made available to other users.
3. Another user (say, Alice) places a bid on Bob's item. The bid is encrypted using Bob's public key.
4. As only Bob has the matching private key, only he can decrypt and view the bid.

This design pattern, while effective for privacy, introduces a potential vulnerability due to how the RSA keys are generated. The function utilizes two prime numbers, `p` and `q`, to generate the RSA keys, with `q` derived from `p`. This creates a dependency that could be potentially exploited. Notably, this issue affects only premium users.

### Vulnerability:
This prime number dependency in RSA key generation can be exploited by an attacker. An attacker can use the public key to calculate `p` and subsequently `q`, exposing the private key, leading to unauthorized access. The following code describe the dependence:

```php
    // Generate a random prime number p
    $p = $this->generate_random_prime(($bit_length - 1) / 2);
    // Calculate q
    $offset = gmp_init("10");
    $increased_p = gmp_add($p, $offset);
    $number = gmp_mul($p, $increased_p);
    $q = gmp_nextprime($number);
```    

### Steps to Reproduce The Exploit:
1. Launch a web browser and navigate to the **Auction** within the SecureAuction application.
2. Click on the **More Info** button.
3. Retrieve the  `encrypted bid ` along with the public key. The public key consists of two parts: the exponent `e`, which is  set to 65537, and the modulus `n`.
4. Run the provided script `exploit_one_key.py`, passing in the  `encrypted bid `, `e` and `n`. The script uses an algorithm to determine the value of `p` and then calculates `q` to reveal the private key `d`.

### Impact:
Successful exploitation of this vulnerability could lead to unauthorized access to sensitive user data. Specifically, in our scenario, encrypted bids could be decrypted by unauthorized individuals, compromising the integrity and confidentiality of the auction process.

### Recommendation to Fix the Vuln:
A fix would be to generate `p` and `q` independently. It's crucial in RSA key generation that the primes `p` and `q` are chosen independently and uniformly at random.
```php
    // Generate a random prime number p
    $p = $this->generate_random_prime(($bit_length) / 2);
       
    // Generate a random prime number q
    do 
    {
        $q = $this->generate_random_prime(($bit_length) / 2);
    } while (gmp_cmp($p, $q) == 0);
```

## Support and Further Assistance

If you encounter any issues or have any queries regarding the system, please feel free to reach out to us. You can contact:

- **KutalVolkan (KutalVolkan)** 
- **ENOWARS** 

Please ensure you provide detailed information about your issue or question, including any error messages you may have received, steps to reproduce the issue, and what you expected to happen. This will assist us in providing you with the most accurate and efficient response. 

We appreciate your feedback and look forward to assisting you!
