import requests
from bs4 import BeautifulSoup

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

# Function to log in and retrieve the session cookie
def login(user_name, password):
    login_url = f"{BASE_URL}/login.php"
    login_data = {
        "user_name": user_name,
        "password": password
    }
    session = requests.session()
    response = session.post(login_url, data=login_data)
    if response.status_code == 200:
        print("User logged in successfully.")
        return session.cookies.get_dict()
    else:
        print("Failed to log in.")
        return None

# Function to create an item
def create_item(cookie, item_name, start_price):
    create_item_url = f"{BASE_URL}/create_item.php"
    item_data = {
        "item_name": item_name,
        "start_price": start_price
    }
    response = requests.post(create_item_url, data=item_data, cookies=cookie)
    if response.status_code == 200:
        print("Item created successfully.")
        return f"Item Name: {item_name}, Start Price: {start_price}"
    else:
        print("Failed to create the item.")
        return None


def main():
    # Sign up a new user
    user_name = "appuser_root"
    password = "testpassword"
    signup(user_name, password)

    # Log in with the created user
    cookie = login(user_name, password)

    if cookie:
        # Create an item
        item_name = "ZZZ"
        start_price = "eno6464646sveva"
        item_detail = create_item(cookie, item_name, start_price)

        # Print the created item information
        print("Created Item Information:")
        print(item_detail)

if __name__ == "__main__":
    main()
