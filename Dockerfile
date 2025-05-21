# 🐘 Use official PHP 8.2 image with Apache
FROM php:8.2-apache

# 🔧 Enable Apache mod_rewrite for clean URLs (important for Symfony)
RUN a2enmod rewrite

# 🛠️ Install necessary system dependencies
RUN apt-get update \
  && apt-get install -y libzip-dev git wget unzip --no-install-recommends \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# 📦 Install required PHP extensions
RUN docker-php-ext-install pdo mysqli pdo_mysql zip

# Install latest stable Composer
RUN wget https://getcomposer.org/composer-stable.phar -O composer.phar \
  && mv composer.phar /usr/bin/composer \
  && chmod +x /usr/bin/composer

WORKDIR /var/www

# Copy entire project first (including bin/console)
COPY . .

# Then install dependencies and run scripts (like cache:clear)
RUN composer install --no-interaction --optimize-autoloader

# 🛡️ Set correct permissions for Symfony's var/ directory
RUN chown -R www-data:www-data var

# 🌐 Apache configuration
COPY docker/apache.conf /etc/apache2/sites-enabled/000-default.conf

# 🚀 Copy custom entrypoint for running migrations, etc.
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# 🏁 Default command
CMD ["/entrypoint.sh"]