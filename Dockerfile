FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev libcurl4-openssl-dev ca-certificates \
    && docker-php-ext-install pdo_mysql mbstring curl \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY deploy/apache.conf /etc/apache2/conf-available/storefront.conf
RUN a2enconf storefront
COPY deploy/php.ini /usr/local/etc/php/conf.d/storefront.ini
COPY deploy/start.sh /usr/local/bin/storefront-start
RUN sed -i 's/\r$//' /usr/local/bin/storefront-start \
    && chmod +x /usr/local/bin/storefront-start

WORKDIR /var/www/html
# Only application files enter the public document root; no Git or credentials.
COPY api/ ./api/
COPY assets/ ./assets/
COPY config/ ./config/
COPY database/ ./database/
COPY img/ ./img/
COPY includes/ ./includes/
COPY scripts/setup.php scripts/setup-homepage.php ./scripts/
COPY .htaccess index.php monitor.php pc.php product.php cart.php health.php ./

ENV PORT=10000
EXPOSE 10000
CMD ["storefront-start"]
