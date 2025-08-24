# Smart-Tasks API (Laravel 12)

A modular Laravel REST API for task management using:

* **Laravel 12**, **Sanctum** (auth)
* **nwidart/laravel-modules** (modular architecture)
* **prettus/l5-repository** (repository pattern)
* **darkaonline/l5-swagger** (OpenAPI docs)

Supports both **local (bare-metal)** and **Docker** development.

---

## Table of Contents

1. [Requirements](#requirements)
2. [Project Structure](#project-structure)
3. [Setup — Normal (bare-metal)](#setup--normal-bare-metal)
4. [Setup — Docker](#setup--docker)
5. [Running the App](#running-the-app)
6. [Database Migrations & Seeding](#database-migrations--seeding)
7. [Authentication (Sanctum)](#authentication-sanctum)
8. [API Documentation (Swagger)](#api-documentation-swagger)
9. [Testing (PHPUnit)](#testing-phpunit)
10. [Common Scripts](#common-scripts)
11. [Troubleshooting](#troubleshooting)

---

## Requirements

### Normal (bare-metal)

* PHP **8.2+**
* Composer **2+**
* MySQL **8.0+** (or PostgreSQL 14+/16+ if you switch the `.env`)
* Redis **6/7** (optional but recommended for queues/cache)
* Node.js (if you plan to use Vite for any frontend assets; not required for the API)

### Docker

* Docker Desktop (Compose v2)

---

## Project Structure

Modular by domain using **nwidart/laravel-modules**:

```
app/
bootstrap/
config/
Modules/
  Core/
    Http/Middleware/SecureHeaders.php
    Providers/CoreServiceProvider.php
    Database/Migrations/... (audits table)
    Entities/Audit.php
    Traits/Auditable.php
  Auth/
    Routes/api.php
    Models/User.php
    Http/Controllers/AuthController.php
    Http/Requests/LoginRequest.php
    Http/Requests/RegisterRequest.php
    Repositories/{AuthRepository, AuthInterface}.php
    Services/AuthService.php
  Task/
    Routes/api.php
    Models/{Task,Status}.php
    Database/Migrations/...
    Database/Seeders/StatusSeeder.php
    Http/Controllers/TaskController.php
    Http/Requests/{StoreTaskRequest,UpdateTaskRequest}.php
    Http/Resources/TaskResource.php
    Http/Resources/StatusResource.php
    Repositories/{TaskRepository,TaskInterface}.php
    Repositories/{StatusRepository, StatusInterface}.php
    Services/{TaskService,StatusService}.php
public/
routes/
tests/
```

Key ideas:

* **Auth** (Sanctum tokens) and **Tasks** live in separate modules with their own routes, controllers, migrations, etc.
* **Core** hosts cross-cutting concerns (secure headers, auditing).
* **Repositories** (Prettus) encapsulate persistence.
* **FormRequests** validate input.
* **Resources** shape API responses.

---

## Setup — Normal (bare-metal)

1. **Clone & install**

```bash
git clone <repo> smart-tasks-api
cd smart-tasks-api
composer install
cp .env.example .env
php artisan key:generate
```

2. **Configure `.env`** (MySQL example)

```env
APP_NAME="Smart Task API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smart_task_api
DB_USERNAME=root
DB_PASSWORD= 

CACHE_DRIVER=file      # or redis
QUEUE_CONNECTION=sync  # or redis
SESSION_DRIVER=file    # or redis
```

3. **Migrate & seed**

```bash
php artisan migrate --seed
```

4. **(Optional) Vendor configs**

```bash
php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider"
php artisan vendor:publish --provider="Nwidart\Modules\LaravelModulesServiceProvider"
```

---

## Setup — Docker

This repo includes a ready Compose stack (**Nginx + PHP-FPM + MySQL + Redis**).

1. **.env for containers**

```env
APP_NAME=SmartTasks
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=smart_task_api
DB_USERNAME=laravel
DB_PASSWORD=laravel

REDIS_HOST=redis
REDIS_PORT=6379
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

2. **Bring it up**

```bash
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

> If port **3306** on your host is busy, remove or change the `db.ports` mapping in `docker-compose.yml` (e.g., `ports: ["3307:3306"]`) or remove it entirely (your app talks to `db:3306` internally).

---

## Running the App

### Normal

```bash
php artisan serve  # http://127.0.0.1:8000
```

### Docker

* Nginx exposes **[http://localhost](http://localhost)**
* PHP-FPM is behind Nginx (container `app`)
* Redis, DB are on the Compose network

---

## Database Migrations & Seeding

* **Normal:** `php artisan migrate --seed`
* **Docker:** `docker compose exec app php artisan migrate --seed`

> The `Task` module seeds base statuses (To Do, In Progress, Done). Add more in `Modules/Task/Database/Seeders/StatusSeeder.php`.

---

## Authentication (Sanctum)

* **Register:** `POST /api/v1/auth/register`
  `{ name, email, password, password_confirmation }`
* **Login:** `POST /api/v1/auth/login`
  Returns `{ token }`
* **Logout:** `POST /api/v1/auth/logout` (Authorization: `Bearer <token>`)

Use the returned token with `Authorization: Bearer <token>` for protected endpoints.

---

## API Documentation (Swagger)

This project uses **l5-swagger** to generate OpenAPI docs.

1. **Generate docs**

    * Normal: `php artisan l5-swagger:generate`
    * Docker: `docker compose exec app php artisan l5-swagger:generate`

2. **Open Swagger UI**

    * Visit: **`/api/documentation`** (e.g., `http://localhost/api/documentation`)

> The config scans controller annotations under `Modules/Auth/Http/Controllers` and `Modules/Task/Http/Controllers`. Adjust `config/l5-swagger.php` → `paths.annotations` if you add more modules.

---

## Testing (PHPUnit)

No Pest—pure PHPUnit in module folders:

* **Feature tests** under `Modules/*/Tests/Feature`
* **Unit tests** under `Modules/*/Tests/Unit`
* **Factories** under `Modules/Task/Database/factories`

Run:

```bash
# Normal
php artisan test

# Docker
docker compose exec app php artisan test
```

> Ensure `phpunit.xml` includes `./Modules/*/Tests` so tests are discovered.

---

## Common Scripts

`composer.json` includes helpful scripts:

* **Dev multi-process runner** (serve, queue listener, pail logs, Vite):

  ```bash
  composer run dev
  ```
* **Tests:**

  ```bash
  composer test
  ```

---

## Example API Surface

```
POST   /api/v1/auth/register
POST   /api/v1/auth/login
POST   /api/v1/auth/logout             (auth)

GET    /api/v1/statuses                   ?search=title:Testing&orderBy=created_at&sortedBy=desc&per_page=10

GET    /api/v1/tasks                   ?search=title:Testing&orderBy=created_at&sortedBy=desc&per_page=10
GET    /api/v1/tasks/{id}
POST   /api/v1/tasks                   (auth)
PUT    /api/v1/tasks/{id}              (auth)
DELETE /api/v1/tasks/{id}              (auth)
POST   /api/v1/tasks/{id}/assign       (auth)
POST   /api/v1/tasks/{id}/change-status       (auth)
```

---

## Troubleshooting

**MySQL container fails with `mysql.user` table missing**

* The volume got half-initialized. Reset it:

  ```bash
  docker compose down
  docker volume rm <your_mysqldata_volume_name>
  docker compose up -d
  ```

**Port 3306 in use**

* Remove `ports` mapping on `db` in Compose (app uses internal network), or map to a different host port:
  `ports: ["3307:3306"]`

**`pg_config not found` during build**

* You included `pdo_pgsql` in the Dockerfile without Postgres headers. Either remove `pdo_pgsql` (if using MySQL) or add `postgresql-dev build-base` during build.

**`phpize failed` when installing PECL redis**

* Install build tools first: `apk add --no-cache --virtual .build-deps $PHPIZE_DEPS`, then `pecl install redis`, then `apk del .build-deps`.

**403/401 on protected routes**

* Include `Authorization: Bearer <token>` returned from the login endpoint.

---
