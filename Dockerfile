# Imagen de la aplicación: Apache + PHP 8.3 con la extensión para MySQL.
FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite headers ssl

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/bramajo.ini
COPY docker/iniciar.sh /usr/local/bin/iniciar.sh
COPY app /var/www/bramajo/app
COPY public /var/www/bramajo/public

RUN chmod +x /usr/local/bin/iniciar.sh \
    && mkdir -p /var/www/bramajo/claves \
    && chown -R www-data:www-data /var/www/bramajo

ENV CLAVES_DIR=/var/www/bramajo/claves
EXPOSE 80 443
CMD ["iniciar.sh"]
