#!/usr/bin/env python3
# -*- coding: utf-8 -*-
# @Date    : 2018-01-28 18:47:39
# @Author  : xchaoinfo (xchaoinfo)
import json
import time
import sys
import rsa
import base64
import binascii
import requests
import re
from PIL import Image
import random
from urllib.parse import quote_plus
from datetime import date
import redis
import requests.packages.urllib3


"""
整体的思路是，
1. 先登录到 weibo.com，
2. 然后用 weibo.com 的 cookie 跳转到 m.weibo.cn
3. 保存 cookie 方便以后使用
3. 仅仅在 Python3.4+ 测试通过，低版本没有测试
4. 代码 PEP8 规范
"""
arguments = sys.argv
requests.packages.urllib3.disable_warnings()

agent = 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Mobile Safari/537.36'
headers = {
    'User-Agent': agent,
}


# noinspection PyInterpreter
class WeiboLogin(object):
    """
    通过登录 weibo.com 然后跳转到 m.weibo.cn
    """

    def __init__(self, user, password):
        super(WeiboLogin, self).__init__()
        self.user = user
        self.password = password
        self.session = requests.Session()
        self.index_url = "http://weibo.com/login.php"
        self.postdata = dict()

    def get_su(self):
        """
        对 email 地址和手机号码 先 javascript 中 encodeURIComponent
        对应 Python 3 中的是 urllib.parse.quote_plus
        然后在 base64 加密后decode
        """
        username_quote = quote_plus(self.user)
        username_base64 = base64.b64encode(username_quote.encode("utf-8"))
        return username_base64.decode("utf-8")

    # 预登陆获得 servertime, nonce, pubkey, rsakv
    def get_server_data(self, su):
        """与原来的相比，微博的登录从 v1.4.18 升级到了 v1.4.19
        这里使用了 URL 拼接的方式，也可以用 Params 参数传递的方式
        https://login.sina.com.cn/sso/prelogin.php?entry=weibo&callback=sinaSSOController.preloginCallBack&su=MTczOTI0NDg3OTY%3D&rsakt=mod&checkpin=1&client=ssologin.js(v1.4.19)&_=1597196205262
        """
        pre_url = "http://login.sina.com.cn/sso/prelogin.php?entry=weibo&callback=sinaSSOController.preloginCallBack&su="
        pre_url = pre_url + su + "&rsakt=mod&checkpin=1&client=ssologin.js(v1.4.15)&_="
        pre_url = pre_url + str(int(time.time() * 1000))
        pre_data_res = self.session.get(pre_url, headers=headers)
        # print(pre_data_res.text)
        sever_data = eval(pre_data_res.content.decode("utf-8").replace("sinaSSOController.preloginCallBack", ''))

        return sever_data

    def get_password(self, servertime, nonce, pubkey):
        """对密码进行 RSA 的加密"""
        rsaPublickey = int(pubkey, 16)
        key = rsa.PublicKey(rsaPublickey, 65537)  # 创建公钥

        message = str(servertime) + '\t' + str(nonce) + '\n' + str(self.password)  # 拼接明文js加密文件中得到
        message = message.encode("utf-8")
        passwd = rsa.encrypt(message, key)  # 加密
        passwd = binascii.b2a_hex(passwd)  # 将加密信息转换为16进制。
        return passwd

    def pre_login(self):
        # su 是加密后的用户名
        su = self.get_su()
        sever_data = self.get_server_data(su)
        servertime = sever_data["servertime"]
        nonce = sever_data['nonce']
        rsakv = sever_data["rsakv"]
        pubkey = sever_data["pubkey"]
        showpin = sever_data["showpin"]  # 这个参数的意义待探索
        password_secret = self.get_password(servertime, nonce, pubkey)


        self.postdata = {
            'entry': 'account',
            'gateway': '1',
            'from': '',
            'savestate': '30',
            'useticket': '0',
            'pagerefer': "https://sina.com.cn/",
            'vsnf': '1',
            'su': su,
            'service': 'account',
            'servertime': servertime,
            'nonce': nonce,
            'pwencode': 'rsa2',
            'rsakv': rsakv,
            'sp': password_secret,
            'sr': '2560*1440',
            'encoding': 'UTF-8',
            'cdult' : 3,
            'prelt': '80',
            'domain': 'sina.com.cn',
            'returntype': 'TEXT'  # 这里是 TEXT 和 META 选择，具体含义待探索
        }
        return sever_data

    def login(self):
        # 先不输入验证码登录测试
        sever_data = self.pre_login()
        login_url = 'https://login.sina.com.cn/sso/login.php?client=ssologin.js(v1.4.15)&_='
        login_url = login_url + str(int(time.time() * 1000))

        login_list_page = self.session.post(login_url, data=self.postdata, headers=headers,verify=False)
        login_content = login_list_page.text;
        print(login_content)
        url_list = [i.replace("\/", "/") for i in login_list_page.text.split('"') if "http" in i]
        for i in url_list:
            self.session.get(i, headers=headers,verify=False)
            time.sleep(0.5)

        print(self.session.cookies.get_dict())

    def isLogin(self):
        page = self.session.get('https://my.sina.com.cn/',headers=headers)
        print(page.text.decode('gbk'))
        regexp = re.compile(r'\$CONFIG\[\'uid\'\]=\'\d+\'')
        if regexp.search(page.text):
            return True
        else:
            return False


if __name__ == '__main__':
    username = arguments[1]  # 用户名
    password = arguments[2]  # 密码
    weibo = WeiboLogin(username, password)
    weibo.login()
    if (weibo.isLogin()):
        print('登录成功')
    else:
        print('登录失败')
