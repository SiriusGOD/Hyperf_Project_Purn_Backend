FROM phpswoole/swoole:php8.2-alpine

ARG timezone
ARG APP_ENV=local
ARG APP_NAME=demo

ENV TIMEZONE=${timezone:-"America/Los_Angeles"} \
    APP_ENV=$APP_ENV \
    APP_NAME=$APP_NAME \
    SCAN_CACHEABLE=(true)



RUN set -ex \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd \
    && docker-php-ext-install pdo_mysql pcntl bcmath \
    && mkdir -p /usr/src/php/ext/redis \
    && cd /usr/src/php/ext \
    && curl https://github.com/phpredis/phpredis/archive/refs/tags/5.3.4.tar.gz -O -L \
    && tar -zxvf 5.3.4.tar.gz -C ./redis --strip-components 1 \
    && docker-php-ext-install redis \
    && curl -sS https://getcomposer.org/installer |  php \
    && mv composer.phar /usr/local/bin/composer \
    && ln -sf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime \
    && echo "${TIMEZONE}" > /etc/timezone \
    && { \
        echo "memory_limit=1G"; \
        echo "date.timezone=${TIMEZONE}"; \
    } | tee /usr/local/etc/php/conf.d/overrides.ini \
    && echo "swoole.use_shortname = 'Off'" >> /usr/local/etc/php/conf.d/docker-php-ext-swoole.ini \
    && rm -rf /var/cache/apk/* /tmp/* /usr/share/man /usr/src/php.tar.xz* $HOME/.composer/*-old.phar

EXPOSE 9501
