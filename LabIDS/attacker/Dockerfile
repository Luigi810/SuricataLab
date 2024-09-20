# Usa un'immagine di base di Kali Linux o Ubuntu
FROM kalilinux/kali-rolling

# Imposta la configurazione non interattiva per tzdata
ENV DEBIAN_FRONTEND=noninteractive

# Aggiorna il sistema e installa strumenti di attacco
RUN apt-get update && apt-get install -y \
    metasploit-framework \
    nmap \
    netcat-openbsd \
    curl \
    vim \
    tzdata

# Definisci una directory di lavoro
WORKDIR /root

# Espone le porte necessarie (modifica in base alle esigenze)
EXPOSE 4444 5555

# Comando di default (puoi modificare in base all'attacco che vuoi simulare)
CMD ["/bin/bash"]

