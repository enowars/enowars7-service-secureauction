import requests
from bs4 import BeautifulSoup
import random
import re


BASE_URL = "http://localhost:8080"  # Base URL of the web application
INDEX_URL = f"{BASE_URL}/index.php"  # URL of the index page


def signup(user_name, password):
    signup_url = f"{BASE_URL}/signup.php"
    signup_data = {
        "user_name": user_name,
        "password": password
    }
    requests.post(signup_url, data=signup_data)


def login(user_name, password):
    login_url = f"{BASE_URL}/login.php"
    login_data = {
        "user_name": user_name,
        "password": password
    }

    with requests.Session() as s:
        response = s.post(login_url, data=login_data)

        if response.status_code == 200:
            profile_url = f"{BASE_URL}/my_profile.php"
            profile_response = s.get(profile_url)

            if profile_response.status_code == 200:
                soup = BeautifulSoup(profile_response.text, 'html.parser')
                user_id_h1 = soup.find('h1')

                if user_id_h1:
                    user_id = re.findall('ID: (\d+)', user_id_h1.text)
                    if user_id:
                        return s, user_id[0]  
        return None
     

def place_bid(session, item_id, bid_amount):
    place_bid_url = f"{BASE_URL}/place_bid.php"  
    bid_data = {
        "item_id": item_id,  
        "bid_amount": bid_amount,
    }
    response = session.post(place_bid_url, data=bid_data)


def get_items(session, page):
    get_items_url = f"{BASE_URL}/index.php"
    params = {'page': page}
    response = session.get(get_items_url, params=params)
    soup = BeautifulSoup(response.text, 'html.parser')
    item_rows = soup.find_all('tr')[1:]

    items = []
    for row in item_rows:
        item_id = row.find('th', attrs={'scope': 'row'}).text
        items.append(item_id)

    return items, len(items)


def main():
    user_name = "appuser_root"
    password = "testpassword"
    signup(user_name, password)

    session, user_id = login(user_name, password)

    if session is not None:
        response = session.get(INDEX_URL)
        soup = BeautifulSoup(response.text, 'html.parser')

        pagination_nav = soup.find('nav', class_='pagination-nav')
        page_links = pagination_nav.find_all('a', class_='page-link')

        total_pages = len(page_links) -1

        if total_pages > 0:
            random_page = random.randint(1, total_pages)

            items, total_items = get_items(session, random_page)

            if items:
                random_item = random.choice(items)
                item_id = random_item
                bid_amount = "eno6464646boom"
                place_bid(session, item_id, bid_amount)
        else:
            print("No pages found.")

if __name__ == "__main__":
    main()
