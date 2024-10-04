Si possono trovare tutte le informazioni relative al laboratorio nella documentazione ad esso relativa presente su Github:   https://github.com/Luigi810/SuricataLab/blob/main/ProgettoNS_Guerrera(26p).pdf

# Suricata IDS/IPS

Suricata è un IDS open-source network-based e rule-based, un sistema di rilevamento e prevenzione delle intrusioni (IDS/IPS) open-source, particolarmente avanzato e versatile in quanto trova applicazione quando non è possibile installare un agent su un dispositivo, come ad esempio succede nel caso mobile.

Oltre a monitorare il traffico di rete per individuare minacce e attacchi, Suricata offre funzionalità avanzate come l'ispezione del protocollo, il rilevamento delle anomalie e la possibilità di decodificare il traffico in tempo reale. Una delle sue caratteristiche principali è **l'utilizzo di regole personalizzabili che permettono di specificare quali tipi di traffico o comportamenti monitorare**, oltre a supportare l'integrazione con strumenti come **Emerging Threats** per mantenere aggiornato il database di minacce.

Uno dei limiti di Suricata è che se lavoriamo con protocolli che prevedono cifratura, come ad esempio succede con HTTPS, abbiamo che tutti i pacchetti sono criptati e quindi non vediamo nulla se non l’IP. In questi casi quindi possiamo usare solo una IP black list.

Suricata può funzionare non solo come IDS ma anche come IPS.

**IPS (Intrusion Prevention System)** è un sistema di sicurezza progettato per prevenire e bloccare attacchi o intrusioni dannose all'interno di una rete. A differenza di un IDS, che si limita a rilevare e segnalare eventuali minacce o comportamenti sospetti, un IPS agisce attivamente per fermare gli attacchi in corso.

## File di configurazione Suricata.yaml

Il file **suricata.yaml** permette di configurare Suricata per gestire numerosi aspetti dell'IDS/IPS quali il traffico da monitorare e il modo in cui vengono gestiti gli allarmi.

Inoltre permette di configurare l’ottimizzazione delle prestazioni e di gestire l'integrazione con altri strumenti di monitoraggio.

Un'accurata configurazione di questo file consente a Suricata di operare in modo efficiente e di adattarsi alle necessità specifiche di rete e sicurezza di ciascun ambiente.

Il file di configurazione usato dal container `router_suricata` è mostrato nel paragrafo 2.1.1, dove viene anche descritta la particolare configurazione utilizzata più nel dettaglio.

Nella sezione **af-packet**, è possibile specificare le interfacce di rete che Suricata deve monitorare e i relativi parametri di acquisizione del traffico.
In particolare si possono specificare:
- **interface**, che specifica quali interfacce di rete utilizzare per catturare il traffico,
- **defrag**, che se abilitato indica a Suricata di occuparsi della ricostruzione dei pacchetti frammentati ai fini dell'analisi.

Ulteriori parametri quali **threads** e **cluster-type** per bilanciare il carico su CPU multiple, **ring-size** per specificare la buffer size per i pacchetti per ciascun thread, altri parametri **use-mmap** e **tpacket-v3** ottimizzano Suricata nella modalità IPS. Insieme a questi per abilitare il funzionamento di Suricata come IPS si settano anche i valori **drop** e **reject** di **block**. Quindi in questo caso (IPS) la configurazione di questa sezione sarebbe:

```yaml
af-packet:
  - interface: eth0
    ...
    defrag: yes
    block:
      drop: yes
      reject: yes
```


Nella sezione rule-files, è possibile specificare quali file di regole devono essere caricati da Suricata. Serve specificare anche il path dove vanno cercate tali regole all'interno del FS della macchina
```
default-rule-path: /etc/suricata/rules
rule-files:
  - suricata.rules
  - fileAlternativo.rules
```
Le variabili di rete (vars) vengono usate per definire reti interne, esterne o specifiche, consentendo alle regole di essere flessibili. In particolare è pratica comune definire le reti HOME_NET, che definisce l'insieme degli indirizzi IP considerati parte della rete locale, ed EXTERNAL_NET, che definisce gli indirizzi esterni. Solitamente si imposta il valore di EXTERNAL_NET in modo che tutto ciò che non è parte di HOME_NET viene considerato EXTERNAL_NET, quindi i valori che più comunemente si usano sono:

```
vars:
  address-groups:
    HOME_NET: "[172.18.0.0/16, 10.0.0.0/16]"
    EXTERNAL_NET: "!$HOME_NET"
```

Suricata può essere configurato per riconoscere e analizzare specifici protocolli a vari livelli del modello ISO/OSI attraverso la key-word app-layer.

Ai fini del logging si configurano i file dove mantenere i log e attributi per la costruzione dei log quali i tipi di log da salvare. A tal scopo si usa la sezione outputs, dove vengono quindi specificati i vari file di log con i rispettivi attributi (ad esempio filetype, filename, types).

```
outputs:
  - eve-log:
      enabled: yes
      filetype: regular
      filename: /var/log/suricata/eve.json
      types:
        - alert:
            payload: yes
            payload-printable: yes
            packet: yes
        - dns:
        - http:
        - tls:
```

Ci sono sezioni che possono essere usate attraverso la sezione flow per il performance tuning se si conosce l'hardware a disposizione. Inoltre con l'opzione emergency-recovery si setta la percentuale di flussi eliminata in caso di esaurimento della memoria.

La sezione flow-timeouts controlla i timeout per diversi tipi di flusso, come TCP, UDP, e ICMP.

Un limite di Suricata riguarda il monitoring relativo a protocolli che prevedono cifratura. Nel caso di TLS si può configurare Suricata in modo da raccogliere informazioni dai certificati relativi alle comunicazioni oggetto di analisi, in particolare certs-log abilita la registrazione dei certificati TLS e certs-fingerprint definisce l'algoritmo di hashing da usare per i fingerprint dei certificati. Si può trovare ad esempio:
```
tls:
  enabled: yes
  certs-log: yes
  certs-fingerprint: sha1
```
## Suricata rules
Le regole Suricata permettono, attraverso la loro struttura flessibile e potente, di rilevare e gestire un'ampia varietà di attacchi a diversi livelli dello stack di rete, dal livello rete al livello applicativo.

Struttura delle regole Suricata
Una regola consiste di 3 componenti distinte:

(1) Action che determina l'azione da compiere quando la regola viene attivata;
(2) Header, che definisce il protocollo da ispezionare, gli indirizzi IP e le porte di mittente e destinatario e la direzione del flusso;
(3) Rule options che definiscono dei vincoli più specifici della regola.

Con un esempio possiamo vedere meglio le suddette parti, ad esempio se consideriamo la regola:

```
alert tcp any any -> any 80 (msg:"HTTP traffic detected"; sid:1000001;)
```

**1)**  La prima parola della regola definisce l'azione che Suricata intraprenderà quando la regola viene soddisfatta. Le azioni valide sono:

alert: genera un avviso;
drop: scarta il pacchetto (richiede l’uso come IPS);
pass: ignora il pacchetto;
reject: blocca e invia un messaggio di errore RST/ICMP all’origine.

**2)**  Viene quindi definito il protocollo da monitorare. Subito dopo si specificano indirizzo e porta del mittente e del destinatario. Il primo valore è l'IP che può assumere il valore di un IP specifico, una IP mask oppure il valore any che rappresenta qualsiasi indirizzo IP. Il secondo valore è la porta, anche questa può essere indicata con un numero preciso, un range del tipo 80:443 (che indica tutte le porte nel range tra 80 a 443) oppure può assumere il valore any che indica qualunque porta.

**3)** Infine vengono specificate le opzioni, in particolare le più comunemente usate sono le seguenti:
- **msg**, per fornire un messaggio descrittivo dell'evento, che viene mostrato quando la regola genera un allarme, e **sid**, l'identificativo univoco della regola.
    
- **content**, utilizzato per specificare i pattern di dati quali stringhe o byte che Suricata deve cercare all'interno del payload del pacchetto.
    
- **PCRE**, che permette di usare espressioni regolari per pattern complessi.
    
- **flow**, per definire la direzione del flusso della connessione.
    
- Controllo sui pacchetti in termini dei **flags** del protocollo TCP, della **dsize** ovvero la dimensione del payload e del **ttl** (time to live) dei pacchetti.
    
- Contenuto a livello di protocollo (es. metodo HTTP usato) o l'URL della richiesta.

### Utilizzo di Suricata a linea di comando

Per utilizzare Suricata da linea di comando, è necessario avviare l'IDS specificando il file di configurazione e le interfacce di rete da monitorare. Il comando di avvio tipico (nel caso di una interfaccia) è:

```
suricata -c /etc/suricata/suricata.yaml -i eth0 -i eth1
```
Dove -c indica il percorso del file di configurazione ed -i specifica l'interfaccia di rete da monitorare.

Per visualizzare in tempo reale i log generati da Suricata, è possibile utilizzare il comando tail -f, che aggiorna costantemente la visualizzazione del file, mostrando in tempo reale gli eventi di sicurezza rilevati da Suricata. Comandi per monitorare il log in tempo reale:

```
tail -f /var/log/suricata/eve.json | jq 'select(.event_type=="alert")'

tail -f /var/log/suricata/fast.log

```
