FROM php:8.2.7
EXPOSE 8080

LABEL maintainer="Lucas Nunes"
LABEL email="lucas.nunes@cern.ch"

ENV CI_VERSION "4.3.6"
LABEL ci_version=${CI_VERSION}
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

# Defining custom php.ini
COPY php.ini /usr/local/etc/php/

# Compose installation (zip is required by codeigniter4 installation thrgough composer)
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN apt install -y zip

# Building app file structure
WORKDIR /usr/local/tilecal-db/
ADD tilecal-db/ /usr/local/tilecal-db/
RUN composer require codeigniter4/framework:${CI_VERSION}
RUN composer require jumbojett/openid-connect-php
RUN composer require firebase/php-jwt
RUN composer require fakerphp/faker

CMD php spark serve --host 0.0.0.0
