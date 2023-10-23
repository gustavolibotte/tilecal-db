# Use the official PHP 8.2.7
FROM php:8.2.7
EXPOSE 8080

LABEL maintainer="Lucas Nunes"
LABEL email="lucas.nunes@cern.ch"

ENV CI_VERSION "4.3.6"
ENV COMPOSER_ALLOW_SUPERUSER 1
LABEL ci_version=${CI_VERSION}

# Installs php extensions
# https://stackoverflow.com/questions/48674297/php-intl-extension-in-docker-container
# https://stackoverflow.com/questions/46879196/mysqli-not-found-dockerized-php
RUN apt-get -y update \
&& apt-get install -y libicu-dev \
&& docker-php-ext-configure intl \
&& docker-php-ext-install intl \
&& docker-php-ext-install mysqli \
&& apt-get install -y zip \
&& apt-get install -y bash \
&& apt-get install -y vim \
&& apt-get install -y nano

# Apache config
COPY configs/php.ini /usr/local/etc/php/
# COPY configs/ports.conf /etc/apache2/
# COPY configs/custom.conf /etc/apache2/conf-available
# COPY configs/tilep2db-app.conf /etc/apache2/sites-available
# COPY configs/hosts /etc/
# RUN a2enmod rewrite
# RUN a2enmod vhost_alias
# RUN a2enconf custom
# RUN a2ensite tilep2db-app

# Compose installation (zip is required by codeigniter4 installation thrgough composer)
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Set the working directory to your CodeIgniter app folder
WORKDIR /var/www/html
ADD tilecal-db/ .
RUN composer require codeigniter4/framework:${CI_VERSION} \
&& composer require jumbojett/openid-connect-php \
&& composer require firebase/php-jwt \
&& composer require fakerphp/faker

# The folder is created during run time, we allow the server to write on it
RUN chmod -R 777 .

CMD bash setup.sh
