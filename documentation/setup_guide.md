# SecureAuction - Setup Guide

This guide provides step-by-step instructions on how to set up and get started with the Secure Auction application.

## Prerequisites

1. **Docker and Docker Compose**: You should have Docker and Docker Compose installed on your system. If not, download and install them from the official Docker [website](https://www.docker.com/products/docker-desktop).

2. **Python and pip**: These are necessary for running the checker tests. If not installed already, download the latest version of Python from the official [website](https://www.python.org/downloads/).

## Setup Instructions

### 1. Clone the Repository

Clone the Secure Auction repository from GitHub with the following command:
```bash
git clone git@github.com:enowars/enowars7-service-secureauction.git
```

### Step 2: Build and Start Docker Services

Build and start the Docker services by running the following command:
```bash
docker-compose up --build
```


Make sure port 8181 is available on your host. This command will start two services - a web server (`www`) and a database server (`db`).

- **Web Service (www)**: This service runs an Apache server with PHP 7.4, and has several additional PHP extensions installed (`mysqli`, `gmp`). It uses the Dockerfile in the current directory for building its image, exposes its port 80 as port 8181 on the host, and mounts the public directory from your host to the `/var/www/html` directory inside the container.

- **DB Service (db)**: This service runs a MySQL server using the latest MySQL Docker image. It mounts an init.sql file from your host to the `/docker-entrypoint-initdb.d/init.sql` directory inside the container. This SQL file is used to initialize the MySQL server when it starts.

### Step 3: Access the Application

You can access the application in your web browser by navigating to `http://localhost:8181`.

### Step 4: Checker
```bash
cd checker
```
```bash
docker-compose up --build --remove-orphans --force-recreate -d
```

Install the `enochecker_test` Python package by running the following command:

```bash
pip install enochecker_test
```

Run the checker tests:
```bash
enochecker_test -a localhost -p 5058 -A host.docker.internal
```