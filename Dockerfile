# Use the official PHP 7.4 image with Apache as the base image
FROM php:7.4-apache

# Install the necessary PHP extensions for MySQL and PDO
RUN docker-php-ext-install mysqli
RUN docker-php-ext-enable mysqli


# Copy the application's source code to the container's web directory
COPY ./src /var/www/html

# Set the default command to run Apache in the foreground
CMD ["apache2-foreground"]
