#!/bin/bash
# Setup the directories and starts the server
cd /var/www/html && \
chmod -R 777 . && \
source setup_directories.sh
# php spark serve --host 0.0.0.0