# Use the official PHP 7.4 image with Apache as the base image
FROM php:7.4-apache

# Install the necessary PHP extensions for MySQL and PDO
RUN docker-php-ext-install mysqli 
RUN docker-php-ext-enable mysqli 

# Install the MySQL client utilities
RUN apt-get update && \
    apt-get install -y default-mysql-client && \
    rm -rf /var/lib/apt/lists/*

# Install the GMP library
RUN apt-get update && \
    apt-get install -y libgmp-dev && \
    docker-php-ext-install gmp

# Copy the entrypoint.sh script to the container
COPY entrypoint.sh /entrypoint.sh

# Make the entrypoint.sh script executable
RUN chmod +x /entrypoint.sh

COPY cleanup.sh /cleanup.sh
RUN chmod +x /cleanup.sh

# Set the entrypoint.sh script as the container's entrypoint
ENTRYPOINT ["/entrypoint.sh"]
