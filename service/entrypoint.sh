#!/bin/sh
# This is the entrypoint.sh script for the SecureAuction application

# Wait for the MySQL container to become ready
# This function checks if the MySQL service is accepting connections
wait_for_mysql() {
  echo "Waiting for MySQL service to become ready..."
  while ! mysqladmin ping -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASSWORD" --silent; do
    sleep 3
  done
  echo "MySQL service is now ready."
}

# Import the init.sql file to set up the initial database schema
import_database_schema() {
  echo "Importing the initial database schema from init.sql..."
  mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASSWORD" < /docker-entrypoint-initdb.d/init.sql
  echo "Initial database schema imported successfully."
}

# Set the required environment variables for connecting to the MySQL container
export DB_HOST="db"
export DB_USER="secureauction"
export DB_PASSWORD="secureauction"

# Wait for the MySQL container to become ready and then import the initial database schema
wait_for_mysql
import_database_schema

# Execute the command provided as an argument to this script, defaulting to "apache2-foreground" as specified in the Dockerfile
exec "$@"