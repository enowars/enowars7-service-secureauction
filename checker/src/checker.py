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

checker = Enochecker("secureauction", 8181)
app = lambda: checker.app


async def signup(client: AsyncClient, user_name, password):
    signup_data = {
        "user_name": user_name,
        "password": password
    }
    response = await client.post("/signup.php", data=signup_data)
    status_code = response.status_code
    if status_code == 200:
        return
    else:
        raise MumbleException(f"Failed to sign up the user. {status_code}")


async def login(client: AsyncClient, user_name, password):
    login_data = {
        "user_name": user_name,
        "password": password
    }

    await client.post("login.php", data=login_data)


async def create_item(client: AsyncClient, item_name, start_price) -> int:
    item_data = {
        "item_name": item_name,
        "start_price": start_price
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

    await signup(client, user_name, password)
    await login(client, user_name, password)

    item_name = ''.join(random.choices(string.ascii_lowercase, k=10))

    item_id = await create_item(client, item_name, 0)
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
    await login(client, user_name, password)

    response = await client.get("my_profile.php")
    logger.debug(response.text)

    assert_in(task.flag, response.text, "Flag missing")


if __name__ == "main":
    checker.run()