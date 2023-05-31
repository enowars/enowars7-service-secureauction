import requests
from bs4 import BeautifulSoup
import random
import re


BASE_URL = "http://localhost:8080"  # Base URL of the web application
INDEX_URL = f"{BASE_URL}/index.php"  # URL of the index page

# Function to sign up a new user
def signup(user_name, password):
    signup_url = f"{BASE_URL}/signup.php"
    signup_data = {
        "user_name": user_name,
        "password": password
    }
    response = requests.post(signup_url, data=signup_data)
    if response.status_code == 200:
        print("User signed up successfully.")
    else:
        print("Failed to sign up the user.")

# Function to log in with user credentials
def login(user_name, password):
    login_url = f"{BASE_URL}/login.php"
    login_data = {
        "user_name": user_name,
        "password": password
    }
    print(f"Logging in with: {login_data}")

    # Start a session
    with requests.Session() as s:
        # Send login data
        response = s.post(login_url, data=login_data)
        print(f"Login Response: {response.text}")
        print("Login Response Status Code:", response.status_code)

        if response.status_code == 200:
            profile_url = f"{BASE_URL}/my_profile.php"
            print(f"Getting profile from: {profile_url} with cookies: {response.cookies}")

            # Use the session's get method so that cookies are sent
            profile_response = s.get(profile_url)
            print(f"Profile Response: {profile_response.text}")
            print("Profile Response Status Code:", profile_response.status_code)

            if profile_response.status_code == 200:
                soup = BeautifulSoup(profile_response.text, 'html.parser')
                print("Parsed HTML:")
                print(soup.prettify())

                # Find the <h1> tag
                user_id_h1 = soup.find('h1')
                print("Result of find('h1'):")
                print(user_id_h1)

                if user_id_h1:
                    user_id = re.findall('ID: (\d+)', user_id_h1.text)
                    if user_id:
                        print(f"User logged in successfully. User ID: {user_id[0]}")
                        return s, user_id[0]  # Return both session and user ID
                    else:
                        print("Failed to extract user ID.")
                        return None
        print("Failed to log in.")
        return None
     
# Function to place a bid on an item
def place_bid(session, user_id, item_id, bid_amount):
    place_bid_url = f"{BASE_URL}/bid.php"
    bid_data = {
        "item_id": item_id,
        "amount": bid_amount,
        "user_id": user_id
    }
    response = session.post(place_bid_url, data=bid_data)
    if response.status_code == 200:
        print("Bid placed successfully.")
    else:
        print("Failed to place the bid.")

def place_bid(session, item_id, bid_amount):
    place_bid_url = f"{BASE_URL}/place_bid.php"  # Change URL to place_bid.php
    bid_data = {
        "item_id": item_id,  # Item ID is sent as part of the POST data, not the URL
        "bid_amount": bid_amount,  # The correct parameter name is bid_amount, not amount
    }

    print("Placing bid with the following data:")
    print("URL:", place_bid_url)
    print("Data:", bid_data)

    response = session.post(place_bid_url, data=bid_data)

    print("Bid request status code:", response.status_code)
    print("Bid request response text:", response.text)

    if response.status_code == 200:
        print("Bid placed successfully.")
    else:
        print("Failed to place the bid.")



def get_items(session, page):
    get_items_url = f"{BASE_URL}/index.php"
    params = {'page': page}
    response = session.get(get_items_url, params=params)
    soup = BeautifulSoup(response.text, 'html.parser')
    item_rows = soup.find_all('tr')[1:]  # Exclude the table header row

    items = []
    for row in item_rows:
        item_id = row.find('th', attrs={'scope': 'row'}).text
        items.append(item_id)

    total_items = len(items)
    return items, total_items


def main():
    # Sign up a new user
    user_name = "appuser_root"
    password = "testpassword"
    signup(user_name, password)

    # Log in with the created user
    session, user_id = login(user_name, password)

    if session is not None:
        # Get the HTML response from the index page
        response = session.get(INDEX_URL)
        soup = BeautifulSoup(response.text, 'html.parser')

        # Find the pagination navigation element
        pagination_nav = soup.find('nav', class_='pagination-nav')
        page_links = pagination_nav.find_all('a', class_='page-link')

        # Calculate the total number of pages
        total_pages = len(page_links) -1

        if total_pages > 0:
            # Choose a random page
            random_page = random.randint(1, total_pages)
            print("Total pages:", total_pages)
            print("Random page:", random_page)

            items, total_items = get_items(session, random_page)
            print("Total items:", total_items)
            print("Items:", items)

            if items:
                # Choose a random item from the list
                random_item = random.choice(items)
                item_id = random_item
                bid_amount = "eno6464646works"  # Update with the desired bid amount
                print("Placing bid for item ID:", item_id)
                print("Bid amount:", bid_amount)
                #place_bid(session, item_id, user_id, bid_amount)
                place_bid(session, item_id, bid_amount)
            else:
                print("No items found on the selected page.")
        else:
            print("No pages found.")

if __name__ == "__main__":
    main()

