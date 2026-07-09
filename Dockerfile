FROM php:8.3-fpm-alpine3.18 as local

RUN apk add --no-cache \
    nginx icu-dev \
    autoconf automake libtool nasm \
    pcre-dev g++ gcc make sudo \
    libpng-dev \
    freetype-dev libjpeg-turbo-dev \
    openrc supervisor rsyslog \
    nodejs npm \
    shadow \
    tzdata \
    libzip-dev \
    git

RUN export TZ=Asia/Tokyo

RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install intl pdo_mysql exif gd zip

RUN pecl install redis \
    && docker-php-ext-enable redis

RUN groupadd -g 1000 www && \
    useradd -s /bin/bash -u 1000 -N -g www -K MAIL_DIR=/dev/null -d /var/www www

WORKDIR /var/www
ADD ./.docker/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf
ADD ./.docker/nginx/nginx.conf /etc/nginx/nginx.conf

ADD ./.docker/php/8.3/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/zzz-www.conf
ADD ./.docker/php/8.3/php.ini /usr/local/etc/php/conf.d/my-app.ini

ADD ./.docker/supervisor/supervisord.conf /etc/supervisord.conf
ADD ./.docker/supervisor/supervisor.d/ /etc/supervisor.d/

RUN mkdir /run/php-fpm8.3

RUN chown www:www -R /run/php-fpm8.3 && \
    chown www:www -R /var/www && \
    chown www:www -R /var/lib/nginx && \
    ln -sf /dev/stdout /var/log/messages && \
    ln -sf /dev/stdout /var/log/nginx/access.log && \
    ln -sf /dev/stderr /var/log/nginx/error.log

ADD ./.docker/setup.sh /var/www/.docker/setup.sh
RUN chmod 755 /var/www/.docker/setup.sh

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY --from=php:8.3-cli-alpine3.18 /usr/local/bin/phpdbg /usr/local/bin/

ADD . /var/www
RUN chown www:www -R /var/www
RUN sh /var/www/.docker/setup.sh

CMD ["/usr/bin/supervisord"]