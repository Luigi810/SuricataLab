#!/bin/bash

# Ottieni gli ID dei container in esecuzione
containers=$(docker ps -q)

# Apri una nuova finestra per ogni container
for container in $containers; do
    gnome-terminal -- bash -c  "docker exec -it $container bash" &
done