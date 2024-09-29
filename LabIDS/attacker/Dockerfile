FROM kalilinux/kali-rolling

# Aggiorna i pacchetti e installa i tool desiderati
RUN apt-get update && apt-get install -y \
    software-properties-common \
    nmap \
    iproute2 \
    curl \
    net-tools \
    vim 

# Work directory
WORKDIR /app

# Copia lo script start.sh nella working directory
COPY ./start_a.sh /start_a.sh
RUN chmod +x /start_a.sh

CMD ["/start_a.sh"]
