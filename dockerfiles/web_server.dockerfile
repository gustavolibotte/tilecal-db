# Use the official PHP 8.2.7
FROM php:8.2.7
EXPOSE 8080

LABEL maintainer="Gustavo Libotte"
LABEL email="gustavo.barbosa.libotte@cern.ch"

ARG app_baseURL
ARG app_indexPage
ARG ENVIRONMENT
ARG CI_VERSION "4.3.6"
ARG COMPOSER_VERSION "2.6.2"
ARG NODE_VERSION "20.11.0"
ARG OPENID_CONNECT_VERSION "1.0.0"
ARG PHP_JWT_VERSION "6.10.0"
ARG PHP_SECLIB_VERSION "3.0.34"
ARG COMPOSER_ALLOW_SUPERUSER 1
LABEL ci_version=${CI_VERSION}

# Installs needed/useful packages
RUN apt-get -y update \
&& apt-get install -y git libicu-dev libonig-dev libzip-dev zip unzip locales \
&& apt-get -y install libpq-dev libxml2-dev libbz2-dev zlib1g-dev \
&& docker-php-ext-install intl mysqli zip bcmath\
&& apt-get install -y bash vim nano \
&& apt install dos2unix \
&& apt-get clean

# composer command
COPY --from=composer:2.6.2 /usr/bin/composer /usr/bin/composer
# node command
COPY --from=node:20.11.0 /usr/local/bin /usr/local/bin
# npm command
COPY --from=node:20.11.0 /usr/local/lib /usr/local/lib
# yarn command
COPY --from=node:20.11.0 /opt /opt

# Create the working directory early
WORKDIR /repo

# Copy ONLY the files needed for dependency installation first
COPY backend/composer.json backend/composer.lock ./backend/
COPY frontend/package.json frontend/package-lock.json ./frontend/

# Install backend dependencies (using install instead of update for speed/consistency)
RUN cd backend && composer install --no-dev --optimize-autoloader

# Install frontend dependencies (using ci for speed)
RUN cd frontend && npm ci

# Now copy the rest of the application code
COPY . .

# Run the build script (which we will also optimize)
RUN bash build_app.sh \
&& cp -r backend/* /var/www/html \
&& chmod -R 777 /var/www/html

WORKDIR /var/www/html
CMD php spark serve --host 0.0.0.0 --port 8080