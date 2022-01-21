FROM php:7-apache

RUN cd /usr/bin && curl -s http://getcomposer.org/installer | php && ln -s /usr/bin/composer.phar /usr/bin/composer
RUN apt-get update \
&& apt-get install -y \
git \
zip \
unzip \
vim

RUN apt-get update \
    && apt-get install -y libpq-dev libzip-dev \
    && docker-php-ext-install pdo_mysql pdo_pgsql \
    && docker-php-ext-install zip

COPY ./sites-enabled/my.conf /etc/apache2/sites-available

# connect用のサイト設定有効化、rewrite有効化
RUN a2dissite 000-default \
  && a2ensite my \
  && a2enmod rewrite

WORKDIR /var/www/html
