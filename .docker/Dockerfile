FROM php:8-cli-alpine3.13

# @see https://floor12.net/en/blog%2Fperfect-php-fpm-image.html

# Arguments defined in docker-compose.yml
ARG USER_ID
ARG GROUP_ID

## Install system dependencies
RUN apk add --no-cache \
      git \
      unzip \
      openssl  \
      openssl-dev \
      oniguruma-dev \
      libmcrypt-dev \
      libzip-dev \
      curl-dev \
      libpng-dev \
      pkgconfig \
      autoconf \
      make \
      g++ \
      gcc

# Install PHP extensions (keep in sync them with composer.json required expentions [expect curl dom hash filter json libxml pdo])
RUN docker-php-ext-install pdo_mysql pcntl bcmath
RUN docker-php-ext-install \
      zip \
      pdo \
      pdo_mysql \
      mbstring \
      sockets \
      gd \
      pcntl


# Install ext-redis
RUN pecl install redis && docker-php-ext-enable redis

# https://blog.denisbondar.com/post/phpstorm_docker_xdebug (RU)
RUN pecl install xdebug && docker-php-ext-enable xdebug

# https://www.hmazter.com/2019/04/speeding-up-php-docker-with-opcache/
RUN docker-php-ext-install opcache

# Install ext-intl
RUN apk add --no-cache icu-dev
RUN docker-php-ext-configure intl && docker-php-ext-install -j$(nproc) intl


# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN addgroup -g $GROUP_ID $USER_ID
RUN adduser -G root -u $GROUP_ID -h /home/$USER_ID -D $USER_ID
RUN mkdir -p /home/$USER_ID/.composer && \
    chown -R $USER_ID:$USER_ID /home/$USER_ID

# Set working directory
WORKDIR /var/www

USER $USER_ID

CMD ["php", "-a"]
