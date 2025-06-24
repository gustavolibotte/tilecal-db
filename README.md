# TileCal Phase 2 Database Repository
Repo for keeping backend and frontend codes for the TileCal Phase 2 Database.

This document provides comprehensive instructions for setting up, running, and developing the Tile-Cal DB application. This project is a full-stack application orchestrated entirely with Docker, featuring a PHP/CodeIgniter backend and a React/TypeScript frontend.

## Core Technologies

  - **Containerization:** Docker & Docker Compose
  - **Backend:** PHP 8.2, CodeIgniter 4
  - **Frontend:** React, TypeScript, Vite
  - **Database:** MySQL 8

## Prerequisites

  - **Docker & Docker Compose:** You must have a recent version of Docker Desktop (or Docker Engine with the Compose plugin) installed and running. Download from [docker.com](https://www.docker.com/products/docker-desktop/).
  - **Git:** For cloning the repository.

## Critical Configuration (`docker-compose.yml`)

Years of collective development and debugging have shown that a stable local environment depends on a correctly configured `docker-compose.yml` file. Before you begin, ensure your file contains the following critical configurations, which solve common race conditions and compatibility issues.

1.  **Database Healthcheck:** The `database` service must have a robust `healthcheck` that verifies not just if the server is "alive," but if it's ready for connections.

    ```yaml
    # In the 'database' service:
    healthcheck:
      test: ["CMD", "mysql", "-u", "root", "-e", "SELECT 1"]
      timeout: 20s
      retries: 10
    ```

2.  **Database Authentication:** To ensure compatibility with the PHP driver, the `database` service should be configured to use the `mysql_native_password` authentication plugin.

    ```yaml
    # In the 'database' service:
    command: --default-authentication-plugin=mysql_native_password
    ```

3.  **Service Dependency:** The `web-server` service must explicitly wait for the `database` service to be healthy before starting.

    ```yaml
    # In the 'web-server' service:
    depends_on:
      database:
        condition: service_healthy
    ```

## First-Time Setup Instructions

Follow these steps to set up the project from scratch on a new machine.

#### Step 1: Clone the Repository

Clone this project to your local machine.

```bash
git clone <your-repository-url>
cd <project-directory>
```

#### Step 2: Build Docker Images

This command reads the `Dockerfiles` and builds the necessary service images, installing all tools like PHP, Composer, and Node.js.

```bash
docker compose build
```

#### Step 3: Compile the Application Assets

This key step uses a temporary container to run the build script. It will install all PHP (`vendor`) and Node (`node_modules`) dependencies and compile the frontend assets, placing the results directly into your local `./backend` directory.

```bash
docker compose run --rm web-server bash -c "cd /root/tilecal-db && bash build_app.sh"
```

*(The `--rm` flag ensures the temporary container is removed after the script completes).*

#### Step 4: Start All Services

With the application code and dependencies now present on your host machine, you can start all services. The `web-server` will now start correctly because the files it needs are available through the mounted volume.

```bash
docker compose up -d
```

#### Step 5: Set Up the Database

The application is running, but the database is still empty. Execute the following commands in sequence to prepare it.

1.  **Create the database schema:**

    ```bash
    echo "CREATE DATABASE tilecal_electronics;" | docker compose exec -T database mysql -u root
    ```

2.  **Create the tables by running migrations:**

    ```bash
    docker compose exec web-server php spark migrate
    ```

3.  **Populate the tables with initial data by running seeders:**

    ```bash
    docker compose exec web-server bash reset_database.sh
    ```

**Setup Complete\!** The application is now fully configured, running, and accessible at **[http://localhost:8080](https://www.google.com/search?q=http://localhost:8080)**.

## Daily Workflow

Once the first-time setup is complete, you will only need these commands for daily use.

  - **To Start the Application:**

    ```bash
    docker compose up -d
    ```

  - **To Stop the Application:**
    This stops the containers but preserves your database volume.

    ```bash
    docker compose down
    ```

## Development Guidelines

#### Backend Development (PHP / CodeIgniter)

Because the `./backend` directory is mounted as a volume, any changes you make to PHP files on your local machine will be reflected instantly in the running application. Simply save your file and refresh your browser.

#### Frontend Development (React / TypeScript)

If you make changes to the frontend code (any files within the `frontend/` directory), you must re-compile the assets. To do this, run the build script again:

```bash
docker compose exec -w /root/tilecal-db web-server bash build_app.sh
```

After the script finishes, refresh your browser to see the changes.

## Useful Commands & Troubleshooting

  - **Check Container Status:** See which services are running, their ports, and health status.

    ```bash
    docker compose ps
    ```

  - **View Service Logs:** Essential for debugging. View the logs of a specific service in real-time.

    ```bash
    # View web server logs
    docker compose logs -f web-server

    # View database logs
    docker compose logs -f database
    ```

  - **Access a Container's Shell:** To run commands manually inside a container.

    ```bash
    docker compose exec web-server bash
    ```

  - **Full Reset:** To stop all containers and completely delete the database volume, use:

    ```bash
    docker compose down -v
    ```

Detailed documentation can be found at the [docs page](https://tilecal-p2-db.docs.cern.ch/).