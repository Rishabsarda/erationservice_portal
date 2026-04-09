FROM php:8.1-apache

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Copy application files
COPY . /var/www/html/

# Ensure Apache listens on the port Railway provides
# We override the default Apache config to use the $PORT environment variable
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Enable Apache modules (like rewrite for cleaner URLs)
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html/
