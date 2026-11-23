FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite

COPY apache-cadegas.conf /etc/apache2/sites-available/000-default.conf

RUN printf '<?php header("Location: /cadegas/frontend/"); exit;\n' > /var/www/html/index.php
