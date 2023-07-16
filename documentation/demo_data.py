import requests
import logging
import random
import string
from bs4 import BeautifulSoup
from typing import Tuple, Optional
import urllib.parse


# Create a requests session
session = requests.Session()
def signup(user_name, password, user_type='REGULAR'):
    signup_data = {
        "user_name": user_name,
        "password": password,
        "user_type": user_type,
        "action": "signup"
    }
    response = session.post("http://localhost:8181/", data=signup_data)
    status_code = response.status_code
    

    if status_code in [200, 302]:
        # Check if a redirection occurred
        if 'Location' in response.headers:
            new_url = response.headers['Location']
            response = requests.get(new_url)  # Fetch the redirected page
            status_code = response.status_code
           


        if user_type == 'PREMIUM':
            # Parse the private key and user_id from the response
            soup = BeautifulSoup(response.text, 'html.parser')
            private_key_elements = soup.find_all('p', class_='key-chunk')
            private_key = ''.join(element.text for element in private_key_elements)

            user_id_element = soup.find('input', id='userId')
            user_id = user_id_element['value'] if user_id_element else None    # type: ignore
            return private_key, user_id
        elif user_type == 'REGULAR':   
            return 
        else: 
            raise Exception(f"Invalid user type: {user_type}")
    else:
        raise Exception(f"Failed to sign up the user. {status_code}")
    

    
def create_item(item_name, start_price, item_type):
        item_data = {'item_name': item_name, 'start_price': start_price, 'item_type': item_type}
        response = session.post("http://localhost:8181/create_item.php", data=item_data, allow_redirects=False)

        # Check the status code of the response
        if response.status_code != 302:
            raise Exception(f"Unexpected status code: {response.status_code}")

        if response.status_code  == 302:
            redirect_uri = response.headers['Location']
            parsed_url = urllib.parse.urlparse(redirect_uri)
            query_params = urllib.parse.parse_qs(parsed_url.query)
            item_id = int(query_params.get('id')[0]) # type: ignore
            encrypted_amount = query_params.get('encryptedAmount', [None])[0]
            return item_id, encrypted_amount



if __name__ == "__main__":
    # Create a demo data
    print("Creating demo data...")
    # Create demo users and items
    for i in range(10):
        # Create a premium user
        user_name = ''.join(random.choices(string.ascii_lowercase, k=10))
        password = ''.join(random.choices(string.ascii_lowercase, k=10))
        private_key, user_id = signup(user_name, password, 'PREMIUM')  # type: ignore
        # Create an items for premium user
        item_name = ''.join(random.choices(string.ascii_lowercase, k=10))
        item_type = random.choice(['REGULAR', 'PREMIUM'])
        start_price = random.randint(100, 1000)
        create_item(item_name, start_price, item_type) # type: ignore
    
        # Create regular user
        user_name = ''.join(random.choices(string.ascii_lowercase, k=10))
        password = ''.join(random.choices(string.ascii_lowercase, k=10))
        user_id = signup(user_name, password, 'REGULAR')  # type: ignore

        # Create an item for regular user
        item_name = ''.join(random.choices(string.ascii_lowercase, k=10))
        start_price = random.randint(10, 1000)
        # Create an item for regular user
        create_item(item_name, start_price, 'REGULAR') # type: ignore

        # Finish the demo data creation
    print("Demo data created successfully")
    