FROM php:7.2.1-apache
MAINTAINER krystof.m

RUN mkdir -p /var/www/web

RUN docker-php-ext-install pdo pdo_mysql mysqli

ADD site-api.conf /etc/apache2/sites-available/site-api.conf
RUN chmod 0644 /etc/apache2/sites-available/site-api.conf
RUN a2dissite 000-default.conf
RUN a2ensite site-api.conf
RUN a2enmod rewrite
