FROM php:8.2.7
EXPOSE 8080

LABEL maintainer="Lucas Nunes"
LABEL email="lucas.nunes@cern.ch"
LABEL version=beta

ENV CI_VERSION "4.3.6"
LABEL ci_version=${CI_VERSION}
WORKDIR /root

ENV ENVIRONMENT ""
ENV BASE_URL ""
ENV DB_HOSTNAME ""
ENV DB_DATABASE ""
ENV DB_USERNAME ""
ENV DB_PASSWORD ""
ENV DB_DRIVER ""
ENV DB_PREFIX ""
ENV DB_PORT ""
ENV CLIENT_ID ""
ENV CLIENT_SECRET ""
ENV CERN_PASSWORD ""
ENV CERN_USERNAME ""
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
RUN composer create-project codeigniter4/appstarter:${CI_VERSION} ci-app-starter
RUN cp -r ci-app-starter/vendor ./
RUN rm -r ci-app-starter
RUN composer require jumbojett/openid-connect-php
RUN composer require firebase/php-jwt

CMD bash run.sh
