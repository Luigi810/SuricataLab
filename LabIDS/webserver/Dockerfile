FROM ubuntu:20.04

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    apache2 \
    vim \
    curl \
    net-tools \
    iproute2 \
    php \
    php-mysqli \
    libapache2-mod-php

# per l'interazione col database
#RUN docker-php-ext-install mysqli

# Disabilita mpm_event e abilita mpm_prefork
RUN a2dismod mpm_event && a2enmod mpm_prefork

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN echo "AddHandler application/x-httpd-php .php" >> /etc/apache2/apache2.conf

COPY ./start_ws.sh /start_ws.sh
RUN chmod +x /start_ws.sh

COPY ./website/ /var/www/html/

EXPOSE 80

CMD ["/start_ws.sh"]


