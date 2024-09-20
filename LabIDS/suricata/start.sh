#!/bin/bash

# Abilitare l'inoltro IP
sysctl -w net.ipv4.ip_forward=1

# Configurare iptables per il forwarding e NAT
iptables -t nat -A POSTROUTING -o eth0 -j MASQUERADE
iptables -A FORWARD -i eth1 -o eth0 -j ACCEPT
iptables -A FORWARD -i eth0 -o eth1 -j ACCEPT
iptables-save > /etc/iptables/rules.v4

cd /tmp/ && curl -LO https://rules.emergingthreats.net/open/suricata-6.0.8/emerging.rules.tar.gz
tar -xvzf emerging.rules.tar.gz && mv rules/*.rules /etc/suricata/rules/
chmod 640 /etc/suricata/rules/*.rules

# Avviare Suricata
#suricata -c /etc/suricata/suricata.yaml -i eth0 -i eth1

tail -f /dev/null    #per tenere il container aperto, altrimenti si chiuderebbe da  solo

