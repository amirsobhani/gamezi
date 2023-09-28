FROM php:8.1-cli-buster

LABEL maintainer="amir sobhani"

ENV DEBIAN_FRONTEND=noninteractive
ENV LC_ALL=C.UTF-8
ENV TERM=xterm-color

WORKDIR /var/www/html

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN set -eux; \
    apt-get update; \
    apt-get install -y apt-transport-https; \
    apt-get upgrade -yqq; \
    pecl channel-update pecl.php.net \
    && apt-get install -yqq --no-install-recommends \
            apt-utils \
            gnupg \
            gosu \
            git \
            curl \
            ca-certificates \
            supervisor \
            libmemcached-dev \
            libz-dev \
            libbrotli-dev \
            libc-ares-dev \
            libpq-dev \
            libjpeg-dev \
            libpng-dev \
            libfreetype6-dev \
            libssl-dev \
            libwebp-dev \
            libmcrypt-dev \
            libonig-dev \
            libzip-dev zip unzip \
            libargon2-1 \
            libidn2-0 \
            libpcre2-8-0 \
            libpcre3 \
            libxml2 \
            libzstd1 \
            procps \
            libcurl4-openssl-dev
# ------------------------------------------
RUN apt-get update && \
  apt-get install -y --no-install-recommends vim nano unzip net-tools \
  libmagickwand-dev libcurl4-gnutls-dev libgmp-dev libxslt1-dev

# Install PECL and PEAR extensions
RUN pecl install -f \
  igbinary

# Enable PECL and PEAR extensions
RUN docker-php-ext-enable \
  igbinary

# Install php extensions
RUN docker-php-ext-install \
  bz2 \
  calendar \
  curl \
  exif \
  gettext \
  gmp \
  iconv \
  pdo \
  pdo_pgsql \
  shmop \
  soap \
  sysvmsg \
  sysvsem \
  sysvshm \
  xml \
  xsl
# ------------------------------------------

RUN php -m

RUN set -xe; \
    docker-php-ext-configure zip \
            && docker-php-ext-install zip \
            && php -m | grep -q 'zip'; \
    docker-php-ext-install \
            mbstring \
            pdo_mysql; \
    docker-php-ext-configure gd \
            --prefix=/usr \
            --with-jpeg \
            --with-webp \
            --with-freetype \
    && docker-php-ext-install gd; \
    php -r 'var_dump(gd_info());'

###########################################
# OPcache
###########################################

ARG INSTALL_OPCACHE=true

RUN if [ ${INSTALL_OPCACHE} = true ]; then \
    docker-php-ext-install opcache; \
  fi

###########################################
# PHP Redis
###########################################

ARG INSTALL_PHPREDIS=true

RUN if [ ${INSTALL_PHPREDIS} = true ]; then \
      pecl install -o -f redis \
      && rm -rf /tmp/pear \
      && docker-php-ext-enable redis; \
  fi

###########################################
# PCNTL
###########################################

ARG INSTALL_PCNTL=true

RUN if [ ${INSTALL_PCNTL} = true ]; then \
    docker-php-ext-install pcntl; \
  fi

###########################################
# BCMath
###########################################

ARG INSTALL_BCMATH=true

RUN if [ ${INSTALL_BCMATH} = true ]; then \
    docker-php-ext-install bcmath; \
  fi

###########################################
# Swoole extension
###########################################

ARG INSTALL_SWOOLE=true

RUN set -eux; \
    if [ ${INSTALL_SWOOLE} = true ]; then \
      pecl install -D 'enable-openssl="yes" enable-http2="yes" enable-swoole-curl="yes" enable-cares="yes"' swoole; \
      docker-php-ext-enable swoole; \
      php -m | grep -q 'swoole'; \
    fi

###########################################################################
# Human Language and Character Encoding Support
###########################################################################

ARG INSTALL_INTL=true

RUN if [ ${INSTALL_INTL} = true ]; then \
    apt-get install -yqq zlib1g-dev libicu-dev g++ \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl; \
  fi


ARG INSTALL_PG_CLIENT=true
ARG INSTALL_POSTGIS=true

RUN if [ ${INSTALL_PG_CLIENT} = true ]; then \
        apt-get install -yqq gnupg \
        && . /etc/os-release \
        && echo "deb http://apt.postgresql.org/pub/repos/apt $VERSION_CODENAME-pgdg main" > /etc/apt/sources.list.d/pgdg.list \
        && curl -sL https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add - \
        && apt-get update -yqq \
        && apt-get install -yqq --no-install-recommends --show-progress postgresql-client-12 postgis; \
        if [ ${INSTALL_POSTGIS} = true ]; then \
          apt-get install -yqq --no-install-recommends --show-progress postgis; \
        fi \
        && apt-get purge -yqq gnupg; \
  fi
###########################################

RUN curl -sL https://deb.nodesource.com/setup_14.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm \
    && curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - \
    && echo "deb https://dl.yarnpkg.com/debian/ stable main" > /etc/apt/sources.list.d/yarn.list \
    && apt-get update \
    && apt-get install -y yarn

# Install composer
ENV COMPOSER_HOME /composer
ENV PATH ./vendor/bin:/composer/vendor/bin:$PATH
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN curl -s https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer

RUN composer global require bamarni/symfony-console-autocomplete && \
    rm -rf $HOME\.composer && \
    apt-get clean


RUN mkdir -p \
  ./storage/framework/sessions \
  ./storage/framework/views \
  ./storage/framework/cache \
  ./storage/logs \
  ./public/temp-file \
  ./public/laravel-excel \
  ./bootstrap/cache \
  && touch ./storage/logs/laravel.log \
  && chown -R www-data:www-data \
  ./storage \
  ./public \
  ./bootstrap/cache \
  && chmod -R ug+rwx ./storage ./bootstrap/cache

RUN chmod -R 777 public

RUN apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
    && rm /var/log/lastlog /var/log/faillog

COPY composer.* /var/www/html/
COPY package.* /var/www/html/


RUN composer install --no-scripts --no-autoloader --no-interaction --prefer-dist
RUN yarn install

COPY . .

RUN chown -R www-data:www-data storage bootstrap/cache \
  && chmod -R ug+rwx storage bootstrap/cache \
  && composer dump-autoload --optimize

COPY docker/8.1/start-container /usr/local/bin/start-container
COPY docker/8.1/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/8.1/php.ini /usr/local/etc/php/conf.d/production.ini

RUN chmod +x /usr/local/bin/start-container
ENTRYPOINT ["start-container"]

EXPOSE 8000

HEALTHCHECK --start-period=5s --interval=2s --timeout=5s --retries=8 CMD php artisan octane:status || exit 1
