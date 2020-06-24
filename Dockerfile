FROM php:7.2-apache

RUN pwd && ls -la

RUN cp /var/www/html/x-cart-5.4.1.5-en.tgz /tmp \
    && cd /tmp \
    && tar xf x-cart-5.4.1.5-en.tgz \
    && rm -rf /var/www/html/ \
    && mv xcart /var/www/html/

RUN buildDeps="libxml2-dev" \
    && set -x \
    && apt-get update && apt-get install -y \
        unzip \
        $buildDeps \
        less \
        mariadb-client \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        pkg-config \
        patch \
        --no-install-recommends && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install -j$(nproc) pdo_mysql soap mysqli pdo mbstring zip \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false -o APT::AutoRemove::SuggestsImportant=false $buildDeps

ADD ./config/ /
RUN chmod +x /*.sh

ENTRYPOINT ["/install.sh"]
CMD ["apache2-foreground"]