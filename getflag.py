import requests
from bs4 import BeautifulSoup

BASE_URL = "http://localhost:8080"  # Base URL of the web application
USER_URL = f"{BASE_URL}/user.php"  # URL for getting user bids

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

# Function to get user bids
def get_user_bids(cookie):
    response = requests.get(USER_URL, cookies=cookie)
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
                    print(f"Item ID: {item_id}, Item Name: {item_name}, Start Price: {start_price}, Created At: {created_at}, Bid Amount: {bid_amount}")
            else:
                print("No bids found.")
        else:
            print("No bids found.")
    else:
        print("Failed to fetch user bids.")

def main():
    # Sign up a new user
    user_name = "appuser_root"
    password = "testpassword"
    signup(user_name, password)

    # Log in with the created user
    cookie = login(user_name, password)

    if cookie:
        # Get user bids
        print("User Bids:")
        get_user_bids(cookie)

if __name__ == "__main__":
    main()
