#!/usr/bin/python3

import time
import os
def func(lines):
    cmds = 'ping -c 3 -i 1 %s'% lines
    p = os.system(cmds)
    file = open("ping.txt", 'a')
    if p == 0:
        print("%s is ping yes"%lines)
        file.write("\033[31 %s is ping yes \n"%lines)
    else:
        print("%s is ping no"%lines)
        file.write("%s is ping no \n"%lines)
    file.close()

if __name__ == '__main__':
    time1 = time.time()
    with open('ip.txt', 'r') as f:
        for line in f.readlines():
            line = line.strip()
            func(line)
    time2 = time.time()
    print(time2-time1)
