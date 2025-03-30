# working
FROM php:8.3-fpm

LABEL maintainer="Roberto L Padilha"

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
  git \
  unzip \
  curl \
  libcurl4-openssl-dev \
  libzip-dev \
  libfreetype6-dev \
  libjpeg62-turbo-dev \
  libpng-dev \
  libssl-dev \
  libonig-dev \
  libxml2-dev \
  libpq-dev \
  tzdata \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg 
RUN docker-php-ext-install -j$(nproc) gd curl pdo_mysql mysqli mbstring zip

RUN echo "UTC" > /etc/timezone && dpkg-reconfigure -f noninteractive tzdata

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer global require phpunit/phpunit:^9.5

COPY . /var/www/html/

RUN composer install --no-interaction --prefer-dist --no-progress

EXPOSE 80

CMD ["php-fpm"]
