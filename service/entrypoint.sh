#!/bin/bash
# This is the entrypoint.sh script for the SecureAuction application

# Note: .env already be loaded because of the env_file option in Docker Compose configuration.

# Wait for the MySQL container to become ready
# This function checks if the MySQL service is accepting connections
wait_for_mysql() {
  echo "Waiting for MySQL service to become ready..."
  while ! mysqladmin ping -h"$MYSQL_HOST" -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" --silent; do
    sleep 3
  done
  echo "MySQL service is now ready."
}

# Import the init.sql file to set up the initial database schema
import_database_schema() {
  echo "Importing the initial database schema from init.sql..."
  mysql -h"$MYSQL_HOST" -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" < /docker-entrypoint-initdb.d/init.sql
  echo "Initial database schema imported successfully."
}

# Wait for the MySQL container to become ready and then import the initial database schema
wait_for_mysql
import_database_schema

# Start the cleanup process in the background
while true; do
	  /cleanup.sh
	sleep 600
done &

# Execute the command provided as an argument to this script, defaulting to "apache2-foreground" as specified in the Dockerfile
exec "$@"
