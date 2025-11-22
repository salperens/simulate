FROM php:8.4-fpm-alpine AS dependencies

RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zlib-dev \
    oniguruma-dev \
    libxml2-dev \
    $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache \
    && docker-php-ext-enable opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    libpng \
    libjpeg-turbo \
    freetype \
    libzip \
    zlib \
    oniguruma \
    libxml2 \
    netcat-openbsd \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zlib-dev \
    oniguruma-dev \
    libxml2-dev \
    $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache \
    && docker-php-ext-enable opcache \
    && apk del --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zlib-dev \
    oniguruma-dev \
    libxml2-dev \
    $PHPIZE_DEPS

COPY docker/php/php.ini /usr/local/etc/php/php.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/healthcheck.sh /usr/local/bin/php-fpm-healthcheck
COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/html

RUN addgroup -g 1000 -S www && \
    adduser -u 1000 -S www -G www && \
    chmod +x /usr/local/bin/php-fpm-healthcheck && \
    chmod +x /usr/local/bin/docker-entrypoint.sh

COPY --chown=www:www . /var/www/html

COPY --from=dependencies /usr/bin/composer /usr/bin/composer

RUN chown -R www:www /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 775 /var/www/html/storage && \
    chmod -R 775 /var/www/html/bootstrap/cache

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD /usr/local/bin/php-fpm-healthcheck || exit 1

CMD ["php-fpm"]
