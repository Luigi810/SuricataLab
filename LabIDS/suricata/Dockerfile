# Usa un'immagine di base di Ubuntu
FROM ubuntu:20.04
#FROM debian:latest

# Imposta la configurazione non interattiva per tzdata
ENV DEBIAN_FRONTEND=noninteractive

# Aggiorna il sistema e installa dipendenze necessarie
RUN apt-get update && apt-get install -y \
    software-properties-common \
    curl \
    vim \
    net-tools \
    iputils-ping \
    tcpdump \
    tzdata \
    iptables \
    iproute2 \
    jq

# Aggiungi il PPA di Suricata e installa Suricata
RUN add-apt-repository ppa:oisf/suricata-stable -y && \
    apt-get update && \
    apt-get install -y suricata && \
    apt-get clean

# Abilitare il packet forwarding
#RUN echo "net.ipv4.ip_forward=1" >> /etc/sysctl.conf && sysctl -p


# Definisci una directory di lavoro
WORKDIR /etc/suricata

# Copia il file di configurazione di Suricata
COPY ./suricata.yaml /etc/suricata/suricata.yaml
COPY ./suricata.rules /etc/suricata/rules/suricata.rules
COPY ./start.sh /start.sh
RUN chmod +x /start.sh

# Configura Suricata
RUN mkdir -p /var/log/suricata && \
    mkdir -p /etc/suricata/rules #&& \
    #suricata-update #se qualcosa va storto probabilmente va commentato qui

# Espone la porta 4000 per Suricata
EXPOSE 8000

# Eseguire Suricata in modalità IDS/IPS
#CMD ["suricata", "-c", "/etc/suricata/suricata.yaml", "-i", "eth0", "-i", "eth1"]
CMD ["/start.sh"]

#per vedere i log json relativi agli alert si può usare il comando seguente nel container suricata
#tail -f /var/log/suricata/eve.json | jq 'select(.event_type=="alert")'

#per aggiungere, sostituire o cambiare le regole si deve aprire il file   /etc/suricata/rules/suricata.rules     
#ad esempio con vim (i per modalità modifica e :wq per salvare e uscire)

