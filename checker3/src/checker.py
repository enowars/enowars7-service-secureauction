from asyncio import StreamReader, StreamWriter
import asyncio
import random
import string
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

checker = Enochecker("secureauction", 8080)
app = lambda: checker.app


async def signup(client: AsyncClient, user_name, password):
    signup_url = "http://localhost:8080/signup.php"
    signup_data = {
        "user_name": user_name,
        "password": password
    }
    response = await client.post(signup_url, data=signup_data)
    status_code = response.status_code
    if status_code == 200:
        print("User signed up successfully.")
    else:
        raise MumbleException(f"Failed to sign up the user. {status_code}")



async def login(client: AsyncClient, user_name, password):
    login_data = {
        "user_name": user_name,
        "password": password
    }

    response = await client.post("http://localhost:8080/login.php", data=login_data)
    if response.status_code == 200:
        return
    else:
        raise MumbleException("Failed to log in.")


'''async def create_item(client: AsyncClient, item_name, start_price):
    item_data = {
        "item_name": item_name,
        "start_price": start_price
    }
    response = await client.post("create_item.php", data=item_data)
    if response.status_code == 200:
        print("Item created successfully.")
        return
    else:
        raise MumbleException("Failed to create the item.")'''


'''@checker.putflag(0)
async def putflag_note(
        task: PutflagCheckerTaskMessage,
        db: ChainDB,
        client: AsyncClient,
        logger: LoggerAdapter,
) -> None:
    user_name = ''.join(random.choices(string.ascii_lowercase, k=10))
    password = ''.join(random.choices(string.ascii_lowercase, k=10))
    logger.debug(user_name)
    logger.debug(password)
    await signup(client, user_name, password)
    await login(client, user_name, password)

    item_name = "ZZZ"

    start_price = task.flag
    #item_detail = create_item(client, item_name, start_price)

    await db.set("item", (user_name, password, item_name))

    return user_name'''


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