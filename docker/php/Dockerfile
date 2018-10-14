FROM php:7.1-fpm

# Install AMQP extension.
RUN apt-get update \
    && apt-get install -y \
        supervisor \
        librabbitmq-dev \
        libssh-dev \
    && pecl install \
        amqp \
        redis \
    && docker-php-ext-enable amqp redis


# Install MYSQLI extension
RUN docker-php-ext-install mysqli

# Install PDO MYSQL extension
RUN docker-php-ext-install pdo_mysql

WORKDIR /symfony

COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf