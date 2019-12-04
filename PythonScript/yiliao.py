#!/usr/bin/env python3
# -*- coding: utf-8 -*-
# @Date    : 2018-01-28 18:47:39
# @Author  : xchaoinfo (xchaoinfo)
import base64
import sys
import uuid
from Crypto.Cipher import AES
from binascii import b2a_hex, a2b_hex
from datetime import datetime
import time
from datetime import timedelta
import json
import requests

arguments = sys.argv


class PrpCrypt(object):

    def __init__(self, key):
        self.key = key
        self.mode = AES.MODE_ECB

    # 加密函数，如果text不足16位就用空格补足为16位，
    # 如果大于16当时不是16的倍数，那就补足为16的倍数。
    def encrypt(self, text):
        text = text.encode('utf-8')
        cryptor = AES.new(self.key, self.mode)
        # 这里密钥key 长度必须为16（AES-128）,
        # 24（AES-192）,或者32 （AES-256）Bytes 长度
        # 目前AES-128 足够目前使用
        length = 16
        count = len(text)
        if count < length:
            add = (length - count)
            # \0 backspace
            # text = text + ('\0' * add)
            num = chr(add)
            text = text + (num * add).encode('utf-8')
        elif count > length:
            add = (length - (count % length))
            # text = text + ('\0' * add)
            num = chr(add)
            text = text + (num * add).encode('utf-8')

        self.ciphertext = cryptor.encrypt(text)
        # 因为AES加密时候得到的字符串不一定是ascii字符集的，输出到终端或者保存时候可能存在问题
        # 所以这里统一把加密后的字符串转化为16进制字符串
        data = str(b2a_hex(self.ciphertext).upper(), encoding="utf-8")
        return data

    # 解密后，去掉补足的空格用strip() 去掉
    def decrypt(self, text):
        cryptor = AES.new(self.key, self.mode)
        plain_text = cryptor.decrypt(a2b_hex(text))
        # return plain_text.rstrip('\0')
        return bytes.decode(plain_text)
        # return plain_text


def get_message_data(start_date, end_date, page_number=1):
    key = base64.b64decode('XwEv38v3fMEVyweNzyuQcw==')
    pc = PrpCrypt(key)  # 初始化密钥
    company_id = "24148"

    query = json.dumps(
        {"dataType": "chatRecord", "startTime": start_date, "endTime": end_date, "pageNumber": page_number})
    e = pc.encrypt(query)  # 加密
    url = "http://api.jswebcall.com/data/api/invoke"
    key = "aoRxZ9%2Fhj7tKi1ClghY83rpJSC0kO1ohgey8pUluoS6Y8%2BSWGRwinwdIPCIIDhkPFBFtnYUlFyfjIa%2B6Aqn9OLTLFbUA3Tf8MMbUDHz4Y2tfowqLN1RRTx594tUPw%2BBWR4WHpZZsVLBsXjlXZtuNSl4CrEOfQyT5CvyNaDP1bcY%3D&="
    payload = "companyId=" + company_id + "&query=" + e + "&key=" + key
    headers = {
        'Content-Type': "application/x-www-form-urlencoded"
    }
    r = requests.request("POST", url, data=payload, headers=headers)
    return json.loads(r.text)


def get_all(start_date, end_date):
    result = []
    first_data = get_message_data(start_date, end_date)

    if first_data['code'] == 0:
        result += first_data['data']
        page_count = first_data['pageCount']

        if page_count > 1:
            for i in range(2, page_count + 1):
                data = get_message_data(start_date, end_date, i)
                result += data['data']

    return result


if __name__ == '__main__':
    startDate = arguments[1]
    endDate = arguments[2]

    data = get_all(startDate, endDate)
    print(json.dumps(data))
