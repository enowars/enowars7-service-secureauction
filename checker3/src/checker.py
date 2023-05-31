from asyncio import StreamReader, StreamWriter
import asyncio
import random
import string
import faker
from httpx import AsyncClient, Response
from typing import Optional
from logging import LoggerAdapter
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

checker = Enochecker("secureauction", 8080)
app = lambda: checker.app

BASE_URL = "http://localhost:8080"  # Base URL of the web application
INDEX_URL = f"{BASE_URL}/index.php"  # URL of the index page


async def signup(client: AsyncClient, user_name, password):
    signup_url = "http://localhost:8080/signup.php"
    signup_data = {
        "user_name": user_name,
        "password": password
    }
    response = await client.post(signup_url, data=signup_data)
    
    status_code = response.status_code
    print(f"Signup response status code: {status_code}")
    
    if status_code == 200:
        print("User signed up successfully.")
    elif status_code == 302:
        redirect_url = response.headers.get('Location')
        if redirect_url:
            response = await client.get(redirect_url)
            if response.status_code != 200:
                raise MumbleException(f"Failed to follow the redirect. {response.status_code}")
            print("User signed up successfully and followed the redirect.")
        else:
            raise MumbleException(f"Failed to sign up the user. {status_code}")
    else:
        raise MumbleException(f"Failed to sign up the user. {status_code}")


async def login(client: AsyncClient, user_name, password):
    login_data = {
        "user_name": user_name,
        "password": password
    }

    response = await client.post("http://localhost:8080/login.php", data=login_data)
    print(f"Login response status code: {response.status_code}")
    
    if response.status_code == 200:
        print(f"User {user_name} logged in successfully.")
    else:
        raise MumbleException("Failed to log in.")


async def place_bid(client: AsyncClient, item_id: str, bid_amount: str):
    place_bid_url = f"{BASE_URL}/place_bid.php"  
    bid_data = {
        "item_id": item_id,  
        "bid_amount": bid_amount,
    }
    response = await client.post(place_bid_url, data=bid_data)
    print(f"Place bid response status code: {response.status_code}")
    if response.status_code == 200:
        print(f"Bid placed successfully for item_id: {item_id} with amount: {bid_amount}")
    else:
        print("Failed to place bid.")


async def get_items(client: AsyncClient, page: int):
    get_items_url = f"{BASE_URL}/index.php"
    params = {'page': page}
    response = await client.get(get_items_url, params=params)
    soup = BeautifulSoup(response.text, 'html.parser')
    item_rows = soup.find_all('tr')[1:]

    items = []
    for row in item_rows:
        item_id = row.find('th', attrs={'scope': 'row'}).text
        items.append(item_id)
    print(f"Items fetched: {items}")

    return items, len(items)


@checker.putflag(0)
async def putflag_note(
        task: PutflagCheckerTaskMessage,
        db: ChainDB,
        client: AsyncClient,
        logger: LoggerAdapter,
) -> None:
    user_name = ''.join(random.choices(string.ascii_lowercase, k=10))
    password = ''.join(random.choices(string.ascii_lowercase, k=10))
    print(f"Generated user_name: {user_name}")
    print(f"Generated password: {password}")
    await signup(client, user_name, password)
    user_id = await login(client, user_name, password)
    
    if user_id is not None:
        response = await client.get(INDEX_URL)
        soup = BeautifulSoup(response.text, 'html.parser')

        pagination_nav = soup.find('nav', class_='pagination-nav')
        page_links = pagination_nav.find_all('a', class_='page-link')

        total_pages = len(page_links) - 1
        print(f"Total number of pages: {total_pages}")

        if total_pages > 0:
            random_page = random.randint(1, total_pages)
            print(f"Randomly selected page: {random_page}")

            items, total_items = await get_items(client, random_page)
            print(f"Total items on selected page: {total_items}")

            if items:
                random_item = random.choice(items)
                item_id = random_item
                bid_amount = task.flag
                print(f"Placing bid for item: {item_id} with bid amount: {bid_amount}")
                await place_bid(client, item_id, bid_amount)
    
    return user_name



'''@checker.getflag(0)
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
    await login(client, user_name, password)

    response = await client.get("my_profile.php")
    logger.debug(response.text)

    assert_in(task.flag, response.text, "Flag missing")'''

if __name__ == "__main__":
    checker.run()