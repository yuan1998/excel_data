#!/usr/bin/env python3
# -*- coding: utf-8 -*-
# @Date    : 2018-01-28 18:47:39
# @Author  : yuan1998 (yuan1998)
from urllib.parse import quote_plus
import base64
import sys
import json
import rsa
import binascii


arguments = sys.argv

if __name__ == '__main__':
    data = json.loads(arguments[1]);

    pubkey = data['pubkey']
    servertime =data['servertime']
    nonce = data['nonce']
    password = data['password']
    rsaPublickey = int(pubkey, 16)
    key = rsa.PublicKey(rsaPublickey, 65537)
    message = str(servertime) + '\t' + str(nonce) + '\n' + str(password)  # 拼接明文js加密文件中得到
    message = message.encode("utf-8")
    passwd = rsa.encrypt(message, key)  # 加密
    passwd = binascii.b2a_hex(passwd)  # 将加密信息转换为16进制。
    result = {'password':passwd.decode("utf-8")}
    test = json.dumps(result);
    print(test)
