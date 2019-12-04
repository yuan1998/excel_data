#!/usr/bin/env python3
# -*- coding: utf-8 -*-
# @Date    : 2018-01-28 18:47:39
# @Author  : xchaoinfo (xchaoinfo)
import json
import time
import sys
import base64
import rsa
import binascii
import requests
import re
from PIL import Image
import random
from urllib.parse import quote_plus
import http.cookiejar as cookielib
from datetime import date

"""
整体的思路是，
1. 先登录到 weibo.com，
2. 然后用 weibo.com 的 cookie 跳转到 m.weibo.cn
3. 保存 cookie 方便以后使用
3. 仅仅在 Python3.4+ 测试通过，低版本没有测试
4. 代码 PEP8 规范
"""
"""
http://smart.luming.sina.com.cn/s/formData/list?siteId=7510&formId=7226&start=0&limit=10&customer_id=6660030357
"""
todayDateString = str(date.today())
arguments = sys.argv

siteId = arguments[1]
formId = arguments[2]
customerId = arguments[6]

try:
    limit = arguments[3]
except IndexError:
    limit = "2000"

agents = [
    'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Mobile Safari/537.36',
    'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:41.0) Gecko/20100101 Firefox/41.0',
    'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36',
    'Chrome/78.0.3904.108 Mobile Safari/537.36',
]
agent = random.choice(agents)
headers = {
    'User-Agent': agent,
}


# noinspection PyInterpreter
class WeiboLogin(object):
    """
    通过登录 weibo.com 然后跳转到 m.weibo.cn
    """

    def __init__(self, user, password, cookie_path):
        super(WeiboLogin, self).__init__()
        self.user = user
        self.password = password
        self.session = requests.Session()
        self.cookie_path = cookie_path
        self.session.cookies = cookielib.LWPCookieJar(filename=self.cookie_path)
        self.index_url = "http://weibo.com/login.php"
        self.session.get(self.index_url, headers=headers, timeout=2)
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
        """
        pre_url = "http://login.sina.com.cn/sso/prelogin.php?entry=weibo&callback=sinaSSOController.preloginCallBack&su="
        pre_url = pre_url + su + "&rsakt=mod&checkpin=1&client=ssologin.js(v1.4.19)&_="
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

    def get_cha(self, pcid):
        """获取验证码，并且用PIL打开，
        1. 如果本机安装了图片查看软件，也可以用 os.subprocess 的打开验证码
        2. 可以改写此函数接入打码平台。
        """
        cha_url = "https://login.sina.com.cn/cgi/pin.php?r="
        cha_url = cha_url + str(int(random.random() * 100000000)) + "&s=0&p="
        cha_url = cha_url + pcid
        cha_page = self.session.get(cha_url, headers=headers)
        with open("cha.jpg", 'wb') as f:
            f.write(cha_page.content)
            f.close()
        try:
            im = Image.open("cha.jpg")
            im.show()
            im.close()
        except Exception as e:
            print(u"请到当前目录下，找到验证码后输入")

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
            'entry': 'weibo',
            'gateway': '1',
            'from': '',
            'savestate': '7',
            'useticket': '1',
            'pagerefer': "https://sina.com.cn/",
            'vsnf': '1',
            'su': su,
            'service': 'miniblog',
            'servertime': servertime,
            'nonce': nonce,
            'pwencode': 'rsa2',
            'rsakv': rsakv,
            'sp': password_secret,
            'sr': '1680*1050',
            'encoding': 'UTF-8',
            'prelt': '33',
            "cdult": "2",
            'url': 'http://weibo.com/ajaxlogin.php?framelogin=1&callback=parent.sinaSSOController.feedBackUrlCallBack',
            'returntype': 'TEXT'  # 这里是 TEXT 和 META 选择，具体含义待探索
        }
        return sever_data

    def login(self):
        # 先不输入验证码登录测试
        sever_data = self.pre_login()
        login_url = 'https://login.sina.com.cn/sso/login.php?client=ssologin.js(v1.4.19)&_'
        login_url = login_url + str(time.time() * 1000)
        login_page = self.session.post(login_url, data=self.postdata, headers=headers)
        ticket_js = login_page.json()
        ticket = ticket_js["ticket"]

        # 以下内容是 处理登录跳转链接
        save_pa = r'==-(\d+)-'
        ssosavestate = int(re.findall(save_pa, ticket)[0]) + 3600 * 7
        jump_ticket_params = {
            "callback": "sinaSSOController.callbackLoginStatus",
            "ticket": ticket,
            "ssosavestate": str(ssosavestate),
            "client": "ssologin.js(v1.4.19)",
            "_": str(time.time() * 1000),
        }
        jump_url = "https://passport.weibo.com/wbsso/login"
        jump_headers = {
            "Host": "passport.weibo.com",
            "Referer": "https://weibo.com/",
            "User-Agent": headers["User-Agent"]
        }
        jump_login = self.session.get(jump_url, params=jump_ticket_params, headers=jump_headers)
        uuid = jump_login.text

        uuid_pa = r'"uniqueid":"(.*?)"'
        uuid_res = re.findall(uuid_pa, uuid, re.S)[0]
        web_weibo_url = "http://weibo.com/%s/profile?topnav=1&wvr=6&is_all=1" % uuid_res
        weibo_page = self.session.get(web_weibo_url, headers=headers)
        weibo_pa = r'<title>(.*?)</title>'
        # print(weibo_page.content.decode("utf-8"))
        userID = re.findall(weibo_pa, weibo_page.content.decode("utf-8", 'ignore'), re.S)[0]

        Mheaders = {
            "User-Agent": agent,
            'Accept': 'application/json, text/plain, */*',
            "Referer": "http://smart.luming.sina.com.cn/",
            "Host": "smart.luming.sina.com.cn",
        }

        # m.weibo.cn 登录的 url 拼接
        _rand = str(time.time())
        mParams = {
            "limit": limit,
            "start": 0,
            'siteId': siteId,
            'formId': formId,
        }
        tokenUrl = "http://smart.luming.sina.com.cn/s/auth/token"
        tokenResponse = self.session.get(tokenUrl,headers=Mheaders)
        tokenJson = tokenResponse.json()
        Mheaders.update({'X-XSRF-TOKEN': tokenJson.get('data').get('token')})

        murl = "http://smart.luming.sina.com.cn/s/formData/list"
        # print(mParams)
        mhtml = self.session.get(murl, params=mParams, headers=Mheaders)
        test_result = mhtml.json()
        print(test_result)
        # print(json.dumps(test_result))


if __name__ == '__main__':
    username = arguments[4]  # 用户名
    password = arguments[5]  # 密码
    cookie_path = "./cookie_weibo_" + username + ".txt"  # 保存cookie 的文件名称
    weibo = WeiboLogin(username, password, cookie_path)
    weibo.login()
