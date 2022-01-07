#!/usr/bin/env python

import asyncio
import websockets
import socket
from threading import Thread


async def receive(websocket):
    # try:
    #     s.connect(("localhost", 31337))
    # except:
    #     pass
    while True:
        print("hi")
        message = await websocket.recv() + "\n"
        print(message)
        s.send(message.encode('utf-8'))


async def send(websocket):
    while True:
        print("hello")
        output = s.recv(1024)
        print(output)
        await websocket.send(output.decode("utf-8"))


async def handler(websocket, _):
    try:
        s.connect(("localhost", 31337))
    except:
        pass
    Thread(target=asyncio.run(receive), args=(websocket,)).start()
    Thread(target=asyncio.run(send), args=(websocket,)).start()


async def main():
    async with websockets.serve(handler, "", 8001):
        await asyncio.Future()

s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
asyncio.run(main())
