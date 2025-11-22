# Usa PHP 8 con Apache
FROM php:8.2-apache

# Installa le dipendenze necessarie per PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Copia i file del backend nella root di Apache
COPY . /var/www/html/

# Espone la porta 80
EXPOSE 80
