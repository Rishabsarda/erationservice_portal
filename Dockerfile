FROM php:8.1-cli

# Install PDO MySQL extension for database connectivity
RUN docker-php-ext-install pdo pdo_mysql

# Set working directory to the web root
WORKDIR /app

# Copy all application files to the container
COPY . .

# Use shell form so ${PORT:-8080} works:
# - If Railway sets PORT=3000, app starts on 3000
# - If PORT is not set at all, defaults to 8080
CMD sh -c "php -S 0.0.0.0:${PORT:-8080} -t /app"
