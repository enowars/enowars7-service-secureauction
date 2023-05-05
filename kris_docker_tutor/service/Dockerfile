# Use the official PHP 7.4 image with Apache as the base image
FROM php:7.4-apache

# Install the necessary PHP extensions for MySQL and PDO
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy the application's source code to the container's web directory
COPY ./src /var/www/html

# Copy the entrypoint script to the container and make it executable
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Copy the SQL initialization file to the container
COPY ./init.sql /docker-entrypoint-initdb.d/

# Set the entrypoint script to be executed when the container starts up
ENTRYPOINT ["/entrypoint.sh"]

# Set the default command to run Apache in the foreground
CMD ["apache2-foreground"]


