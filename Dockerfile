FROM php:8.1-apache

# Instala dependências e extensão pdo_pgsql
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev zip unzip git gnupg2 libzip-dev \
    && docker-php-ext-install pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Habilita rewrite
RUN a2enmod rewrite

# Copia o projeto para o container
COPY . /var/www/html/

# Ajusta permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Define diretório de trabalho na pasta api (onde está index.php)
WORKDIR /var/www/html/api

EXPOSE 80

CMD ["apache2-foreground"]
