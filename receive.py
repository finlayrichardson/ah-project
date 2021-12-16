#!/usr/bin/env python

import asyncio
import websockets
import subprocess
import socket


async def handler(websocket, _):
    try:
        s.connect(("localhost", 31337))
    except:
        pass
    while True:
        message = await websocket.recv() + "\n"
        s.send(message.encode('utf-8'))
        output = s.recv(1024)
        print(output)
        await websocket.send(output.decode("utf-8"))
        # await websocket.send("Command error")


async def main():
    async with websockets.serve(handler, "", 8001):
        await asyncio.Future()

s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
asyncio.run(main())
