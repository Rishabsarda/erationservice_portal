FROM php:8.1-cli

# Install PDO MySQL extension for database connectivity
RUN docker-php-ext-install pdo pdo_mysql

# Set working directory to the web root
WORKDIR /app

# Copy all application files to the container
COPY . .

# Expose the port (informative only for Docker, Railway uses the $PORT env var)
EXPOSE 8080

# Start the PHP built-in web server binding to 0.0.0.0 and the Railway-provided $PORT
# We use the shell form of CMD to ensure $PORT expansion works correctly
CMD php -S 0.0.0.0:$PORT
