# Usa un'immagine di base di Ubuntu
FROM ubuntu:20.04

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

# Aggiunge il PPA di Suricata e installa Suricata
RUN add-apt-repository ppa:oisf/suricata-stable -y && \
    apt-get update && \
    apt-get install -y suricata && \
    apt-get clean

# Directory di lavoro
WORKDIR /etc/suricata

# Copiare il file di configurazione di Suricata
COPY ./suricata.yaml /etc/suricata/suricata.yaml
COPY ./suricata.rules /etc/suricata/rules/suricata.rules
COPY ./start.sh /start.sh
RUN chmod +x /start.sh

# Configurazione di Suricata
RUN mkdir -p /var/log/suricata && \
    mkdir -p /etc/suricata/rules #&& \
   
# Porta 8000 per accedere ai log di Suricata
EXPOSE 8000

CMD ["/start.sh"]

