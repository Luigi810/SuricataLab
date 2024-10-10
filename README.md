# SuricataLab
Laboratorio basato su Docker-compose per fare pratica con alcune tipologie di attacco e sulla identificazione dei suddetti attraverso l'utilizzo di IDS Suricata-based, e agire in modo appropriato con tecniche di remediation per salvaguardare il webserver di interesse.


## Aggiornamento per l'integrazione in DSP:
Nel laboratorio in ambiente DSP è stato sostitito l'attacker con un nuovo container generato a partire dal Dockerfile, presente nella cartella attacker, che descrive un container che aggiunge all'imagine di kali linux della Docker hub varie utilities e tools che vengono usati durante il laboratorio ed insoltre viene impostato l'entrypoint start_a.sh utile per eseguire il comando per costruire il path tra attaccante e webserver specificando il passaggio per il router_suricata  
```
ip route add 172.18.0.0/16 via 172.19.0.2
```
## Avvio dei containers
Per l'avvio dei container del laboratorio si esegue 
```
docker-compose up --build
```
Dopodiché si possono lanciare le shell dei tre container usando lo script avvioShell.sh, la shell suricata è facile da identificare, per le altre 2 bisogna controllare con il comando docker ps l'id del container.
In oltre serve avviare suricata, una volta soddisfatti delle regole suricata specificate in /etc/suricata/rules/suricata.rules, tramite il comando 
```
suricata -c /etc/suricata/suricata.yaml -i eth0 -i eth1 
```
in quanto se si facesse all'avvio del container il processo sarebbe legato al container e quindi sarebbe impossibile riavviarlo dopo aver cambiato la configurazione senza chiudere anche il container, per cui nel dockerfile lasciamo come comando l'avvio di uno script di startup start.sh in modo da mantenere il container in esecuzione dopo il setup (in particolare si settano le direttive di forwarding per ottenere il comportamento di un router).

Una volta che suricata è in esecuzione si può avviare il comando tail nel container suricata per controllare i log una volta che siamo soddisfatti con la configurazione del nostro IDS suricata:
```
tail -f /var/log/suricata/eve.json | jq 'select(.event_type=="alert")'
```
Se vogliamo riconfigurare suricata a runtime, dopo aver modificato uno o entrambi i file 
```
/etc/suricata/suricata.yaml    e    /etc/suricata/rules/suricata.rules  ,
```
per la cui modifica abbiamo a disposizione l'editor vim (i per modalità insert e :wq per salvataggio e quit), bisogna terminare il processo suricata usando il comando kill sul pid del suddetto processo oppure un semplice comando di interrupt con ctrl + C e riavviarlo con il comando
```
suricata -c /etc/suricata/suricata.yaml -i eth0 -i eth1 
```
che specifica in particolare che il traffico da tenere d'occhio passa attraverso le interfacce eth0 ed eth1.

## Attacchi in esame

Il laboratorio consiste nell'eseguire degli attacchi di diverso tipo sul webserver e riuscire a riconfigurare Suricata in modo da identificare e magari reagire ad una serie di tipologie di attacco quali 

1) lo scanning attraverso nmap, con i comandi 
```
nmap -sS 172.18.0.3  

nmap -sV 172.18.0.3
```

2) XSS sfruttando la pagina http://172.18.0.3/xssVuln.php. Per farlo da shell possiamo usare il comando curl come segue
```
curl 'http://172.18.0.3/xssVuln.php?comment=%3Cscript%3Ealert%28%27XSS%21%27%29%3C%2Fscript%3E'
```
3) Attacco DoS, sfruttando ad esempio il comando nella suite nmap
```
nping --tcp-connect -p 80 --rate 1000 172.18.0.3 -c 10000
```
dove -c in particolare specifica il numero di iterazioni dopo il quale fermarsi, il rate è il numero di pacchetti inviati al secondo, e ovviamente sono specificati IP destinazione, porta e tipo di connessione.

4) Attacco SQL Injection sfruttando la vulnerabilità della pagina sqlVuln.phpdove è presente un form da compilare per poter accedere ad un database.Si può eseguire l'attacco da shell con il comando curl:
```
curl http://172.18.0.3/sqlVuln.php?user_id=1'OR+1%3D1+%23
```
Ovviamente prima dell'esecuzione dell'attacco va innanzitutto creata e riempita la tabella del database MySql:
```
-- Per fare il login in MySQL

mysql -u user -p   

-- Per selezionare il database nel DBServer

USE testdb;        

-- Per creare una table nel DB

CREATE TABLE utenti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
);                 

-- Per inserire un valore nella tabella

INSERT INTO utenti (nome) VALUES ('Kvaratskhelia'); 
```

## Possibili Regole Suricata per la loro identificazione

1) Per rilevare uno scan TCP Connect, che rispetti i vincoli temporali della seguente, può essere usata la regola:
```
alert tcp any any -> $HOME_NET any (msg:"Nmap TCP Connect scan detected"; flags:S,A; threshold: type both, track by_src, count 20, seconds 3; classtype:attempted-recon; sid:100002; rev:1;)
```
2) Per rilevare un attacco XSS in cui si inserisce il tag html per incorporare codice senza modifiche può essere:
```
alert http any any -> $HOME_NET any (msg:"XSS Attack Detected: Basic script tag"; flow:to_server,established; content:"<script>"; http_uri; nocase; classtype:web-application-attack; sid:1000004; rev:1;)
```
3) Per rilevare l'attacco SYN flood  (attacco DoS), che rispetti i vincoli temporali della seguente, può essere usata la regola:
```
alert tcp any any -> $HOME_NET 80 (msg:"SYN Flood Detected"; flags:S; threshold: type both, track by_src, count 200, seconds 1; classtype:attempted-dos; sid:1000005; rev:1;)
```
4) Per rilevare questo particolare Attacco SQL Injection una possibile regola per l'identificazione è 
 ```  
alert http any any -> $HOME_NET any (msg:"SQL Injection Attempt - OR 1=1 URL-encoded"; content:"OR+1%3D1"; nocase; classtype:web-application-attack; sid:1000011; rev:1;)
 ```  
## Lettura dei log di Suricata

Per controllare che i log siano quelli delle regole che vogliamo (ossia che l'IDS Suricata si comporti come previsto) usiamo i soliti comandi per la stampa dei log di suricata
```
tail -f /var/log/suricata/eve.json | jq 'select(.event_type=="alert")'

tail -f /var/log/suricata/fast.log
```
