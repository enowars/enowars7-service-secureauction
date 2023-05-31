import requests
from bs4 import BeautifulSoup
import logging

logging.basicConfig(level=logging.DEBUG)

BASE_URL = "http://localhost:8080"  # Base URL of the web application
USER_URL = f"{BASE_URL}/my_profile.php"  # URL for getting user bids

# Function to sign up a new user
def signup(user_name, password):
    logging.debug('Attempting to sign up a new user.')
    signup_url = f"{BASE_URL}/signup.php"
    signup_data = {
        "user_name": user_name,
        "password": password
    }
    response = requests.post(signup_url, data=signup_data)
    if response.status_code == 200:
        logging.debug("User signed up successfully.")
    else:
        logging.error("Failed to sign up the user.")

# Function to log in and retrieve the session cookie
def login(user_name, password):
    logging.debug('Attempting to log in.')
    login_url = f"{BASE_URL}/login.php"
    login_data = {
        "user_name": user_name,
        "password": password
    }
    session = requests.session()
    response = session.post(login_url, data=login_data)
    if response.status_code == 200:
        logging.debug("User logged in successfully.")
        return session.cookies.get_dict()
    else:
        logging.error("Failed to log in.")
        return None

# Function to get user bids
def get_user_bids(cookie):
    page = 1
    while True:
        logging.debug('Fetching bids for page %d.', page)
        response = requests.get(USER_URL, params={'page': page}, cookies=cookie)
        if response.status_code == 200:
            soup = BeautifulSoup(response.text, "html.parser")
            bids_table = soup.find("table", {"class": "table"})
            if bids_table:
                rows = bids_table.find_all("tr")
                if len(rows) > 1:
                    for row in rows[1:]:
                        cells = row.find_all("td")
                        item_id = cells[0].text.strip()
                        item_name = cells[1].text.strip()
                        start_price = cells[2].text.strip()
                        created_at = cells[3].text.strip()
                        bid_amount = cells[4].text.strip()
                        logging.debug('Processing bid with Item ID: %s, Item Name: %s, Start Price: %s, Created At: %s, Bid Amount: %s', item_id, item_name, start_price, created_at, bid_amount)
                        if 'eno' in start_price.lower():  # Check if 'eno' is in the start price (case insensitive)
                            logging.info('Found bid with "eno" in start price: Item ID: %s, Item Name: %s, Start Price: %s, Created At: %s, Bid Amount: %s', item_id, item_name, start_price, created_at, bid_amount)
                else:
                    logging.debug("No bids found on this page.")
                    break  # End the loop if there are no bids on this page
            else:
                logging.debug("No bids table found on this page.")
                break  # End the loop if there is no bids table on this page
        else:
            logging.error("Failed to fetch user bids for page %d.", page)
            break  # End the loop if the request failed
        page += 1  # Increment the page number for the next iteration

def main():
    # Sign up a new user
    user_name = "appuser_root"
    password = "testpassword"
    logging.info('User name: %s', user_name)
    # Uncomment the next line to enable user signup.
    #signup(user_name, password)

    # Log in with the created user
    logging.info('Logging in user %s.', user_name)
    cookie = login(user_name, password)

    if cookie:
        logging.debug('Received session cookie: %s', cookie)
        # Get user bids
        logging.info('Fetching user bids.')
        get_user_bids(cookie)
    else:
        logging.error('Failed to get session cookie.')

if __name__ == "__main__":
    logging.info('Starting script.')
    main()
    logging.info('Script finished.')
