# Library API

Backend API (Laravel 12, API-first). Authentication: Laravel Sanctum.

## Project layout and naming

- Application root: this directory (`laravel12-library-api/`). Clone or open the repo anywhere on your machine; commands below assume your shell’s current working directory is this folder.
- `APP_NAME` in `.env`: **Library API**.
- PostgreSQL: `DB_DATABASE=library`, `DB_USERNAME=library_user` (database name and DB login are separate from the app folder name).

## Project structure

```text
.
├── app/
│   ├── Actions/
│   │   ├── Book/
│   │   │   ├── CreateBookAction.php
│   │   │   ├── DeleteBookAction.php
│   │   │   ├── ListBooksAction.php
│   │   │   └── UpdateBookAction.php
│   │   ├── BookRent/
│   │   │   ├── ExtendBookRentAction.php
│   │   │   ├── FinishBookRentAction.php
│   │   │   ├── ListBookRentsAction.php
│   │   │   ├── RentBookAction.php
│   │   │   └── UpdateReadingProgressAction.php
│   │   └── User/
│   │       ├── CreateUserAction.php
│   │       ├── DeleteUserAction.php
│   │       ├── ListUsersAction.php
│   │       ├── LoginUserAction.php
│   │       ├── UpdateUserAction.php
│   │       └── UpdateUserPasswordAction.php
│   ├── Enums/
│   │   └── BookRentStatus.php
│   ├── Exceptions/
│   │   ├── ApiException.php
│   │   └── ResourceConflictException.php
│   ├── Http/
│   │   ├── Contracts/
│   │   │   ├── BookControllerInterface.php
│   │   │   ├── BookRentControllerInterface.php
│   │   │   ├── CurrentUserControllerInterface.php
│   │   │   ├── LoginUserControllerInterface.php
│   │   │   ├── LogoutUserControllerInterface.php
│   │   │   ├── RegisterUserControllerInterface.php
│   │   │   ├── StatusControllerInterface.php
│   │   │   └── UserControllerInterface.php
│   │   ├── Controllers/
│   │   │   ├── Controller.php
│   │   │   └── Api/
│   │   │       ├── BookController.php
│   │   │       ├── BookRentController.php
│   │   │       ├── CurrentUserController.php
│   │   │       ├── LoginUserController.php
│   │   │       ├── LogoutUserController.php
│   │   │       ├── RegisterUserController.php
│   │   │       ├── StatusController.php
│   │   │       └── UserController.php
│   │   ├── Requests/
│   │   │   ├── Book/
│   │   │   │   ├── ListBooksRequest.php
│   │   │   │   ├── StoreBookRequest.php
│   │   │   │   └── UpdateBookRequest.php
│   │   │   ├── BookRent/
│   │   │   │   ├── ExtendBookRentRequest.php
│   │   │   │   ├── FinishBookRentRequest.php
│   │   │   │   ├── ListBookRentsRequest.php
│   │   │   │   ├── StoreBookRentRequest.php
│   │   │   │   ├── UpdateBookRentReadingProgressRequest.php
│   │   │   │   └── ViewBookRentRequest.php
│   │   │   └── User/
│   │   │       ├── DeleteUserRequest.php
│   │   │       ├── IndexUsersRequest.php
│   │   │       ├── LoginUserRequest.php
│   │   │       ├── ShowUserRequest.php
│   │   │       ├── StoreManagedUserRequest.php
│   │   │       ├── StoreUserRequest.php
│   │   │       ├── UpdateManagedUserRequest.php
│   │   │       ├── UpdateUserPasswordRequest.php
│   │   │       └── UpdateUserRequest.php
│   │   └── Resources/
│   │       ├── BookResource.php
│   │       ├── BookRentResource.php
│   │       └── UserResource.php
│   ├── Models/
│   │   ├── Book.php
│   │   ├── BookRent.php
│   │   └── User.php
│   ├── OpenApi/
│   │   ├── OpenApiInfo.php
│   │   └── Schemas/
│   │       ├── Common/
│   │       │   ├── MessageResponse.php
│   │       │   └── PaginationMeta.php
│   │       ├── Book/
│   │       │   ├── BookDataResponse.php
│   │       │   ├── BookResource.php
│   │       │   ├── PaginatedBooksResponse.php
│   │       │   ├── StoreBookRequestBody.php
│   │       │   └── UpdateBookRequestBody.php
│   │       ├── BookRent/
│   │       │   ├── BookRentDataResponse.php
│   │       │   ├── BookRentResource.php
│   │       │   ├── ExtendRentRequestBody.php
│   │       │   ├── PaginatedBookRentsResponse.php
│   │       │   ├── ReadingProgressDataResponse.php
│   │       │   ├── RentBookRequestBody.php
│   │       │   └── UpdateReadingProgressRequestBody.php
│   │       └── User/
│   │           ├── LoginRequestBody.php
│   │           ├── RegisterRequestBody.php
│   │           ├── RegistrationResponse.php
│   │           ├── UpdateUserPasswordRequestBody.php
│   │           ├── UpdateUserProfileRequestBody.php
│   │           ├── PaginatedUsersResponse.php
│   │           ├── StoreManagedUserRequestBody.php
│   │           ├── UpdateManagedUserRequestBody.php
│   │           ├── UserDataResponse.php
│   │           └── UserResource.php
│   ├── Policies/
│   │   ├── BookPolicy.php
│   │   ├── BookRentPolicy.php
│   │   └── UserPolicy.php
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   └── Support/
│       └── ApiResponse.php
├── database/
│   ├── factories/
│   │   ├── BookFactory.php
│   │   ├── BookRentFactory.php
│   │   └── UserFactory.php
│   └── migrations/
│       ├── 0001_01_01_000000_create_users_table.php
│       ├── 0001_01_01_000001_create_cache_table.php
│       ├── 0001_01_01_000002_create_jobs_table.php
│       ├── 2026_03_26_153657_create_personal_access_tokens_table.php
│       └── 2026_03_27_120000_create_books_and_book_rents_tables.php
├── routes/
│   ├── api.php
│   ├── console.php
│   └── web.php
└── tests/
    ├── Feature/
    ├── Unit/
    └── TestCase.php
```

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

### User account (Sanctum foundation)

- `POST /api/v1/register` — create user; response includes `data.user`, `data.token`, `data.token_type` (`Bearer`).
- `POST /api/v1/login` — email + password; same token payload as register; **401** with `Invalid credentials` when the pair is wrong (neutral message). Rate limited (`throttle:login`, 5/minute per email + IP).
- `POST /api/v1/logout` — authenticated; revokes the **current** bearer token (`PersonalAccessToken` for this `Authorization` header); response `{"message":"Logout successful"}` (no `data` key).
- `GET /api/v1/user` — current profile; header `Authorization: Bearer {token}`.
- `PATCH /api/v1/user` — update `name` and `email` (authenticated).
- `PUT /api/v1/user/password` — change password (`current_password`, `password`, `password_confirmation`).

### Users CRUD (`/users`)

Authenticated routes (`Authorization: Bearer {token}`). **Trade-off:** *This is an explicit assignment-only trade-off to satisfy the CRUD requirement without inventing an RBAC model that is not present in the task. In production, list/create/update/delete on `/users` would be restricted by roles or permissions.*

| Method | Path | Notes |
|--------|------|--------|
| `GET` | `/api/v1/users` | Paginated list; query `per_page` (1–100, default 15) |
| `POST` | `/api/v1/users` | Create user (`name`, `email`, `password`, `password_confirmation`) — same password rules as register |
| `GET` | `/api/v1/users/{id}` | |
| `PATCH` | `/api/v1/users/{id}` | Partial update `name` and/or `email` |
| `DELETE` | `/api/v1/users/{id}` | **409** if target is **yourself** or user has **any** `book_rents` row (DB `RESTRICT` alignment) |

Self-service on `/api/v1/user` is unchanged: it always reads/updates only the current user from the token.

### Books and rentals (catalog + `book_rents`)

All routes below require `Authorization: Bearer {token}`.

**Book catalog authorization (trade-off):** *Book catalog authorization is intentionally relaxed: any Sanctum-authenticated user may CRUD books. This is an **assignment-level trade-off without a roles model** — not how a production library product would enforce access. In production, catalog changes would use roles or permissions.*

**Books:**

| Method | Path | Notes |
|--------|------|--------|
| `GET` | `/api/v1/books` | Query: `title`, `author`, `genre` — **case-insensitive substring** match (`LIKE`), not full-text search. `available_only` (boolean). `sort_by` whitelist: `title`, `author`, `genre`, `created_at`, `available_copies`, `total_copies`; `sort_dir` `asc`/`desc`. **Defaults:** `sort_by=title`, `sort_dir=asc`, `per_page=15` (max 100). |
| `POST` | `/api/v1/books` | Create; `available_copies` defaults to `total_copies` if omitted |
| `GET` | `/api/v1/books/{id}` | |
| `PATCH` | `/api/v1/books/{id}` | |
| `DELETE` | `/api/v1/books/{id}` | **409** if any **active** rental exists (business rule in `DeleteBookAction`, not only policy) |

**Rentals** (scoped to the current user; another user’s id yields **404**):

| Method | Path | Notes |
|--------|------|--------|
| `GET` | `/api/v1/rentals` | Paginated list |
| `POST` | `/api/v1/rentals` | Body: `book_id`, `due_date` (must be after now); **409** if no copies |
| `GET` | `/api/v1/rentals/{id}` | |
| `PATCH` | `/api/v1/rentals/{id}/extend` | `due_date`; **409** if not `active` |
| `GET` | `/api/v1/rentals/{id}/reading-progress` | `{ "data": { "reading_progress": … } }` |
| `PATCH` | `/api/v1/rentals/{id}/reading-progress` | `reading_progress` 0–100; **409** if finished |
| `POST` | `/api/v1/rentals/{id}/finish` | Returns copy to `available_copies`; **409** if already finished |

**Delete book / rental rows (design trade-off):** The app forbids deleting a book while **active** rentals exist. On allowed delete, the DB uses **`ON DELETE CASCADE`** on `book_rents.book_id`, so **finished** rental rows are removed with the book (history for that title is dropped). `book_rents.user_id` uses **`ON DELETE RESTRICT`**. PostgreSQL adds `CHECK` constraints on copy counts and `reading_progress`; SQLite (CI) relies on validation and tests.

Routes: [`routes/api.php`](routes/api.php); API prefix `/api` is registered in [`bootstrap/app.php`](bootstrap/app.php).

## OpenAPI

Spec is generated with [zircote/swagger-php](https://github.com/zircote/swagger-php) from PHP 8 attributes on the API contract (e.g. [`StatusControllerInterface`](app/Http/Contracts/StatusControllerInterface.php)) and root metadata in [`app/OpenApi/OpenApiInfo.php`](app/OpenApi/OpenApiInfo.php).

```bash
composer run docs:generate
```

Alias: `composer run openapi` (same as `docs:generate`).

Writes **`storage/api-docs/openapi.yaml`** (ignored by git except `.gitignore` in that folder). Full liveness URL in the spec is server `/api/v1` + path `/status/liveness` → **`GET /api/v1/status/liveness`**.

## Code style

```bash
composer run format
```

Check only (no writes): `composer run lint` (`./vendor/bin/pint --test`).

## CI (GitHub Actions)

Workflow: [`.github/workflows/ci.yml`](.github/workflows/ci.yml) — PHP **8.3**, Composer cache, `composer install --optimize-autoloader`, then `composer lint`, `composer docs:generate`, `composer test:ci` (SQLite file `database/testing.sqlite`; job `env` includes `DB_*`, `CACHE_STORE=array`, `SESSION_DRIVER=array`, `QUEUE_CONNECTION=sync` so they override `.env` on the runner). No `.env.testing` — only `.env` (created from `.env.example` in CI). Triggers: **every** `push` (including `feat/…`, `main`, `master`, etc.) and **every** `pull_request`.

## Local parity with CI

Use your existing **`.env`**. Before the first `test:ci` run, create the SQLite file:

```bash
touch database/testing.sqlite
```

`composer run test:ci` runs `migrate:fresh` against the default DB from your environment. To match CI (SQLite file) without changing PostgreSQL settings in `.env` for normal work, prefix the command (Unix/macOS):

```bash
DB_CONNECTION=sqlite DB_DATABASE=database/testing.sqlite composer run test:ci
```

Or run the same checks as CI: `composer run quality` (lint + test:ci) with the same `DB_*` prefix if needed.

`phpunit.xml` forces `DB_CONNECTION=sqlite` and `DB_DATABASE=database/testing.sqlite` during `php artisan test`, so the test process uses that file; `migrate:fresh` in `test:ci` must target the same database — hence the env prefix when your `.env` uses PostgreSQL.
