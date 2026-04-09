FROM php:8.1-apache

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# 1. Fix Apache MPM Conflict: Disable event/worker and enable prefork
# This prevents the 'More than one MPM loaded' crash
RUN a2dismod mpm_event mpm_worker || true
RUN a2enmod mpm_prefork

# 2. Copy application files
COPY . /var/www/html/

# 3. Configure Apache to listen on $PORT instead of 80 (required by Railway)
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# 4. Enable rewrite module for modern URL structures
RUN a2enmod rewrite

# 5. Ensure Apache stays in foreground
CMD ["apache2-foreground"]
