FROM php:8.1-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip

# Install Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Configure Apache
RUN a2enmod rewrite
RUN echo '<Directory /var/www/html/>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/custom-directory.conf
RUN a2enconf custom-directory

# Set correct permissions for .htaccess
copy .htaccess /var/www/html/.htaccess
RUN chown www-data:www-data /var/www/html/.htaccess && chmod 644 /var/www/html/.htaccess

# Configure Xdebug
RUN echo "xdebug.mode=debug\n\
xdebug.start_with_request=yes\n\
xdebug.client_port=9003\n\
xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Enable Apache error logging
RUN sed -i 's/LogLevel warn/LogLevel debug/' /etc/apache2/apache2.conf

EXPOSE 80