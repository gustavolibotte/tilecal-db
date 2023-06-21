FROM php:8.2.7
EXPOSE 8080

LABEL maintainer="Lucas Nunes"
LABEL email="lucas.nunes@cern.ch"

ARG version
LABEL version=$version
WORKDIR /root

# Installs int php extension
# https://stackoverflow.com/questions/48674297/php-intl-extension-in-docker-container
RUN apt-get -y update \
&& apt-get install -y libicu-dev \
&& docker-php-ext-configure intl \
&& docker-php-ext-install intl

# Install mysqli php extension
# https://stackoverflow.com/questions/46879196/mysqli-not-found-dockerized-php
RUN docker-php-ext-install mysqli

# Solves pearl location for installing postgres
# https://stackoverflow.com/questions/2499794/how-to-fix-a-locale-setting-warning-from-perl
# RUN export LC_CTYPE=en_US.UTF-8 >> ~/.bashrc
# RUN export LC_ALL=en_US.UTF-8 >> ~/.bashrc

# Install postgres
# https://forums.docker.com/t/php-postgresql-in-docker-compose/104159
# RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install pdo pdo_pgsql

# Install git
RUN apt install -y git

# Install htop command
RUN apt install -y htop

# Install composer for dependency management
# https://hub.docker.com/_/composer
ARG ci4_version
COPY --from=composer /usr/bin/composer /usr/bin/composer
# zip is required by codeigniter4 installation thrgough composer
RUN apt install -y zip
RUN composer create-project codeigniter4/appstarter:${ci4_version} ci-app-starter

WORKDIR /root/tilecal-db
