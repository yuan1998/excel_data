#!/usr/bin/env python3
# -*- coding: utf-8 -*-
# @Date    : 2018-01-28 18:47:39
# @Author  : yuan1998 (yuan1998)
from urllib.parse import quote_plus
import base64
import sys
import json

arguments = sys.argv

if __name__ == '__main__':
    username = arguments[1]  # 用户名
    username_quote = quote_plus(username)
    username_base64 = base64.b64encode(username_quote.encode("utf-8"))
    print(json.dumps(username_base64.decode("utf-8")))
