FROM php:8.1-cli

# Install PDO MySQL extension for database connectivity
RUN docker-php-ext-install pdo pdo_mysql

# Set a default PORT value (Railway will override this with its own $PORT)
ENV PORT=8080

# Set working directory to the web root
WORKDIR /app

# Copy all application files to the container
COPY . .

# Expose the port
EXPOSE ${PORT}

# Start the PHP built-in web server
CMD php -S 0.0.0.0:${PORT} -t /app
