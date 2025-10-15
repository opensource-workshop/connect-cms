# https://hub.docker.com/_/php/tags?page=1&name=apache (official)
FROM php:8.2-apache

# Allow build-time overrides via docker-compose build args
ARG PHP_INI_ENV=development
ARG TZ=Asia/Tokyo
ENV PHP_INI_ENV=${PHP_INI_ENV}
ENV TZ=${TZ}

# Install Composer from the official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        git \
        zip \
        unzip \
        vim \
        libpq-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libwebp-dev \
        libxpm-dev \
        locales; \
    docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
        --with-xpm; \
    docker-php-ext-configure exif --enable-exif; \
    docker-php-ext-configure pcntl --enable-pcntl; \
    docker-php-ext-install \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        mysqli \
        pcntl \
        pdo_mysql \
        pdo_pgsql \
        zip; \
    rm -rf /var/lib/apt/lists/*

COPY ./sites-enabled/my.conf /etc/apache2/sites-available

# connect用のサイト設定有効化、rewrite有効化
RUN a2dissite 000-default \
  && a2ensite my \
  && a2enmod rewrite

# Timezone (PHP)
RUN set -eux; \
    ln -snf "/usr/share/zoneinfo/${TZ}" /etc/localtime; \
    echo "${TZ}" > /etc/timezone; \
    printf '[PHP]\ndate.timezone = "%s"\n' "${TZ}" > /usr/local/etc/php/conf.d/timezone.ini

# PHP_INI_ENVのiniファイルを有効化
RUN mv "$PHP_INI_DIR/php.ini-${PHP_INI_ENV}" "$PHP_INI_DIR/php.ini"

# 日本語設定
RUN sed -i -E 's/# (ja_JP.UTF-8)/\1/' /etc/locale.gen \
  && locale-gen
ENV LANG=ja_JP.UTF-8
ENV LC_ALL=ja_JP.UTF-8

WORKDIR /var/www/html/connect-cms
