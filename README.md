# Library API

Backend API (Laravel 12, API-first). Authentication: Laravel Sanctum.

## Project layout and naming

- Application root: this directory (`laravel12-library-api/`). Clone or open the repo anywhere on your machine; commands below assume your shell’s current working directory is this folder.
- `APP_NAME` in `.env`: **Library API**.
- PostgreSQL: `DB_DATABASE=library`, `DB_USERNAME=library_user` (database name and DB login are separate from the app folder name).

## Requirements

- PHP **8.3+** — use the **same** `php` binary for Composer and Artisan (on macOS, default `php` may be older, e.g. MAMP; then set an explicit path).
- Composer
- PostgreSQL, with superuser access once to create the app role and database

### PHP binary (example)

If `php -v` is not 8.3+, point to Homebrew PHP for all commands below:

```bash
export PHP_BIN=/opt/homebrew/opt/php@8.3/bin/php
# Then replace leading `php` with `$PHP_BIN` and run Composer as:
# $PHP_BIN $(command -v composer) install
# (or: $PHP_BIN /path/to/composer install)
```

If `php -v` already shows 8.3+, you can use `composer` and `php artisan` without `PHP_BIN`.

## Setup (first-time / clone)

Order matters.

### 1. Install PHP dependencies

From this directory:

```bash
composer install
```

Without `vendor/`, `php artisan` will not run.

### 2. Environment file

```bash
cp .env.example .env
```

In `.env`, confirm PostgreSQL settings: `DB_CONNECTION=pgsql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE=library`, `DB_USERNAME=library_user`.

Generate a **random** password for the DB role and set **`DB_PASSWORD`** to that value. **Do not commit** `.env`.

**Important:** the string you use in `CREATE USER ... PASSWORD '...'` below must be **exactly the same** as `DB_PASSWORD` in `.env`.

### 3. Create PostgreSQL role and database (once)

As a PostgreSQL superuser (e.g. `postgres`), run (replace the password with the same value as `DB_PASSWORD`):

```sql
CREATE USER library_user WITH PASSWORD 'same_value_as_db_password_in_env';
CREATE DATABASE library OWNER library_user ENCODING 'UTF8';
```

If the role or database already exists, align the password (`ALTER USER library_user PASSWORD '...'`) or recreate objects deliberately.

### 4. Application key and migrations

```bash
php artisan key:generate
php artisan migrate
```

If migration fails with PostgreSQL authentication errors, check that `DB_PASSWORD` matches the role password and that `pg_hba.conf` allows connections from `127.0.0.1` if you use TCP.

### 5. Optional

```bash
php artisan config:clear
```

Useful after changing `.env` when config is cached.

## Run

```bash
php artisan serve
```

Liveness: `GET /api/v1/status/liveness` returns unified JSON (`data`, `message`) with HTTP **200** — see [`App\Support\ApiResponse`](app/Support/ApiResponse.php) and exception JSON shaping in [`bootstrap/app.php`](bootstrap/app.php).

Routes: [`routes/api.php`](routes/api.php); API prefix `/api` is registered in [`bootstrap/app.php`](bootstrap/app.php).

## OpenAPI

Spec is generated with [zircote/swagger-php](https://github.com/zircote/swagger-php) from PHP 8 attributes on the API contract (e.g. [`StatusControllerInterface`](app/Http/Controllers/Interfaces/StatusControllerInterface.php)) and root metadata in [`app/OpenApi/OpenApiInfo.php`](app/OpenApi/OpenApiInfo.php).

```bash
composer run openapi
```

Writes **`storage/api-docs/openapi.yaml`** (ignored by git except `.gitignore` in that folder). Full liveness URL in the spec is server `/api/v1` + path `/status/liveness` → **`GET /api/v1/status/liveness`**.

## Code style

```bash
./vendor/bin/pint
```
