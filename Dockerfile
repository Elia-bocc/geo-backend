# Usa PHP 8 con Apache
FROM php:8.2-apache

# Abilita estensioni necessarie (PostgreSQL)
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# Copia i file del backend nella root di Apache
COPY . /var/www/html/

# Espone la porta 80
EXPOSE 80
