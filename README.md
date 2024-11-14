# SuricataLab
Docker-compose based lab for practicing various types of attacks and identifying them using a Suricata-based IDS. Additionally, it includes taking appropriate remediation measures to protect the target web server.

## Update for DSP Integration:
In the DSP environment lab, the attacker container has been replaced with a new container generated from the Dockerfile located in the attacker folder. This Dockerfile describes a container that adds several utilities and tools to the Kali Linux image from Docker Hub, which are used during the lab. Additionally, it sets up the `start_a.sh` entrypoint script, which is useful for executing the command to establish the path between the attacker and the web server by specifying routing through `router_suricata`.

```
ip route add 172.18.0.0/16 via 172.19.0.2
```

## Starting the Containers
To start the lab containers, execute:
```
docker-compose up --build
```
Then, you can launch the shells of the three containers using the `avvioShell.sh` script. The Suricata shell is easy to identify, while for the other two, you may need to check the container ID with the `docker ps` command. Additionally, start Suricata once you are satisfied with the Suricata rules specified in `/etc/suricata/rules/suricata.rules`, using:

```
suricata -c /etc/suricata/suricata.yaml -i eth0 -i eth1 
```
This command is run manually to avoid binding the Suricata process to the container. Instead, we leave the Dockerfile setup to run a startup script (`start.sh`) to keep the container running after setup (setting up forwarding directives to simulate a router).

Once Suricata is running, you can launch the `tail` command in the Suricata container to monitor logs once you're satisfied with the IDS configuration:

```
tail -f /var/log/suricata/eve.json | jq 'select(.event_type=="alert")'
```

If you want to reconfigure Suricata at runtime, after modifying either one or both files:
```
/etc/suricata/suricata.yaml    ;    /etc/suricata/rules/suricata.rules  ,
```
you can edit these files with the `vim` editor (use `i` for insert mode and `:wq` to save and quit), then terminate the Suricata process using `kill` on the process ID or simply `ctrl + C`, and restart it with:
```
suricata -c /etc/suricata/suricata.yaml -i eth0 -i eth1 
```
This command specifies that the traffic to monitor flows through interfaces `eth0` and `eth1`.

## Examined Attacks

The lab involves executing different types of attacks on the web server and reconfiguring Suricata to detect and possibly react to a series of attack types, including:

1) Scanning via `nmap`, using commands:

```
nmap -sS 172.18.0.3  

nmap -sV 172.18.0.3
```


2) XSS using the page `http://172.18.0.3/xssVuln.php`. From the shell, use `curl` as follows:
   
```
curl 'http://172.18.0.3/xssVuln.php?comment=%3Cscript%3Ealert%28%27XSS%21%27%29%3C%2Fscript%3E'
```

3) DoS attack, using the command in the `nmap` suite:
   
```
nping --tcp-connect -p 80 --rate 1000 172.18.0.3 -c 10000
```
where `-c` specifies the iteration count, `rate` sets the packet-per-second rate, and IP, port, and connection type are specified.

4) SQL Injection attack using the vulnerability in `sqlVuln.php`, where a form allows access to a database. Execute from the shell using `curl`:

```
curl http://172.18.0.3/sqlVuln.php?user_id=1'OR+1%3D1+%23
```
Before executing the attack, create and populate the MySQL database table:

```
-- To login in MySQL

mysql -u user -p   

-- To select the database in the DBServer

USE testdb;        

-- Create the following table in the DB

CREATE TABLE utenti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
);                 

-- To add values in the table you can use 

INSERT INTO utenti (nome) VALUES ('Kvaratskhelia'); 
```

## Possible Suricata Rules for Attack Detection

1) To detect a TCP Connect scan within temporal constraints, use:
```
alert tcp any any -> $HOME_NET any (msg:"Nmap TCP Connect scan detected"; flags:S,A; threshold: type both, track by_src, count 20, seconds 3; classtype:attempted-recon; sid:100002; rev:1;)
```

2) For detecting an XSS attack with a basic HTML script tag insertion:
```
alert http any any -> $HOME_NET any (msg:"XSS Attack Detected: Basic script tag"; flow:to_server,established; content:"<script>"; http_uri; nocase; classtype:web-application-attack; sid:1000004; rev:1;)
```

3) To detect a SYN flood DoS attack, use:
```
alert tcp any any -> $HOME_NET 80 (msg:"SYN Flood Detected"; flags:S; threshold: type both, track by_src, count 200, seconds 1; classtype:attempted-dos; sid:1000005; rev:1;)
```

4) To detect this specific SQL Injection attack, a possible detection rule is:
 ```  
alert http any any -> $HOME_NET any (msg:"SQL Injection Attempt - OR 1=1 URL-encoded"; content:"OR+1%3D1"; nocase; classtype:web-application-attack; sid:1000011; rev:1;)
 ```

## Reading Suricata Logs

To verify that the logs correspond to the rules set (confirming that the Suricata IDS is behaving as expected), use the standard commands to print Suricata logs:

```
tail -f /var/log/suricata/eve.json | jq 'select(.event_type=="alert")'

tail -f /var/log/suricata/fast.log
```
