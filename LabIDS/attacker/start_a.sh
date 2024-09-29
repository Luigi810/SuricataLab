#!/bin/bash

#ip route add 172.19.0.0/16 via 172.18.0.2
ip route add 172.18.0.0/16 via 172.19.0.2


tail -f /dev/null    #per tenere il container aperto, altrimenti si chiuderebbe da  solo
