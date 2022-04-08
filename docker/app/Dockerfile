FROM php:7-apache

# APPLICATION ENVIROMENT: production or development
ENV APP_ENV development

RUN cd /usr/bin && curl -s http://getcomposer.org/installer | php && ln -s /usr/bin/composer.phar /usr/bin/composer
RUN apt-get update \
&& apt-get install -y \
git \
zip \
unzip \
vim

RUN apt-get update \
    && apt-get install -y libpq-dev libzip-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libwebp-dev \
        libxpm-dev \
        locales \
    && docker-php-ext-install pdo_mysql pdo_pgsql \
    && docker-php-ext-install zip \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
        --with-xpm \
    && docker-php-ext-install -j$(nproc) gd

COPY ./sites-enabled/my.conf /etc/apache2/sites-available

# connect用のサイト設定有効化、rewrite有効化
RUN a2dissite 000-default \
  && a2ensite my \
  && a2enmod rewrite

# Timezone (PHP)
RUN printf '[PHP]\ndate.timezone = "${TZ}"\n' > /usr/local/etc/php/conf.d/timezone.ini


# 日本語設定
RUN sed -i -E 's/# (ja_JP.UTF-8)/\1/' /etc/locale.gen \
  && locale-gen
ENV LANG ja_JP.UTF-8

WORKDIR /var/www/html
