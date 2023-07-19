#!/bin/bash

# Define the number of minutes after which data should be cleaned up
NUMBER_OF_MINUTES=10

# Set the required environment variables for connecting to the MySQL container
export MYSQL_HOST=$(printenv MYSQL_HOST)
export MYSQL_USER="root"  # Root user
export MYSQL_PASSWORD=$(printenv MYSQL_ROOT_PASSWORD)  # Root password
export MYSQL_DATABASE=$(printenv MYSQL_DATABASE) # Database name


# Get the current date and time, and compute the deletion time
currentTime=$(date +%s)
deletionTime=$((currentTime - NUMBER_OF_MINUTES*60))

# Convert the deletion time into MySQL's datetime format
deletionDateTime=$(date -d @$deletionTime '+%Y-%m-%d %H:%M:%S')

# Run a single MySQL session to execute all the deletion commands
deleted_rows=$(mysql -N -s -r -h"$MYSQL_HOST" -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" <<EOF
USE $(printenv MYSQL_DATABASE);

DELETE FROM bids WHERE created_at < '$deletionDateTime';
SELECT ROW_COUNT();
EOF
)
echo "Deleted $deleted_rows rows from bids"

deleted_rows=$(mysql -N -s -r -h"$MYSQL_HOST" -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" <<EOF
USE $(printenv MYSQL_DATABASE);

DELETE FROM items WHERE created_at < '$deletionDateTime';
SELECT ROW_COUNT();
EOF
)
echo "Deleted $deleted_rows rows from items"

deleted_rows=$(mysql -N -s -r -h"$MYSQL_HOST" -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" <<EOF
USE $(printenv MYSQL_DATABASE);

DELETE FROM users WHERE created_at < '$deletionDateTime';
SELECT ROW_COUNT();
EOF
)
echo "Deleted $deleted_rows rows from users"
