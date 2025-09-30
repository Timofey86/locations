# Locations Project

A Symfony 7 project for managing locations, using Docker, RabbitMQ, and asynchronous command handling via Messenger.

## Project Structure

- `app/` — main Symfony code (src, config, templates, etc.)
- `docs/` — API documentation
- `nginx/templates/` — Nginx configuration templates
- `docker-compose.dev.yml` — development environment Docker Compose
- `worker.yml` — worker containers for async message handling
- `.env` and `.env.local.example` — environment variables
- `Makefile` — scripts for running containers and workers

## Technologies

- PHP 8.4, Symfony 7
- PostgreSQL 16
- RabbitMQ for async command processing (Symfony Messenger)
- Redis for caching
- Docker + Docker Compose for local development
- JWT authentication via LexikJWTAuthenticationBundle

## Environment Setup

1. Copy the example environment file and configure your environment variables:
    ```bash
    cp .env.local.example .env.local
    ```

2. Build Docker containers:
    ```bash
    make build
    ```

3. Build Composer dependencies:
    ```bash
    make composer
    ```

3. Start the containers:
    ```bash
    make start
    ```

## JWT Authentication

- Keys are stored in `config/jwt/`:
    - `private.pem` — private key
    - `public.pem` — public key
- Tokens are valid for 1 hour, after which a new token must be obtained or refreshed.


## Notes

- All dependencies are installed via Composer; PHP >= 8.4 is required.
- In development, workers must be run manually or configured to auto-start via Supervisor.
- Xdebug is included in the dev environment for debugging purposes.