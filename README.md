# Library API

Laravel **12** JSON API for a small library system. **REST**, prefix `api/v1`. Authentication: **Laravel Sanctum** (personal access tokens).

## Domain overview

A library manages **users**, **books**, and **book rentals**. Users can register and log in, search for books, rent them, extend rentals, track reading progress, and complete rentals.

## Goal

Build a small, well-structured backend API. The implementation is meant to show architectural choices, justified trade-offs, code quality, and maintainability — using a conventional REST surface rather than GraphQL or a heavy CQRS split for this scope.

## Domain entities

1. **User** — identity, profile, password; Sanctum tokens; CRUD for managed users (assignment scope).
2. **Book** — catalog metadata and copy counts (`total_copies`, `available_copies`).
3. **BookRent** — links a user to a book for a period; status, due date, reading progress, extend/finish flows.

## Functional requirements & API reference

Base URL for routes below: `/api/v1`. Public routes: `register`, `login`, `GET /status/liveness`. All other endpoints require `Authorization: Bearer {token}`.

**Authorization trade-offs:** The brief does not specify RBAC. **Any authenticated user may CRUD books** and use **full `/users` CRUD** as an explicit assignment-level simplification; a real product would restrict catalog and user admin by roles. **Rentals** are always scoped to the current user: `{bookRent}` resolves only that user’s row; another user’s id returns **404** (no enumeration). See `AppServiceProvider::registerBookRentRouteBinding()`.

### Authentication

| Method | Path | Notes |
|--------|------|--------|
| `POST` | `/register` | Creates user; response includes `data.user`, `data.token`, `data.token_type` (`Bearer`). |
| `POST` | `/login` | Email + password; same token shape as register; **401** `Invalid credentials` if wrong. Rate limited: `throttle:login`, 5/minute per email + IP. |
| `POST` | `/logout` | Revokes the **current** bearer token only. Response: `{"message":"Logout successful"}` (no `data`). |

### Current user (self-service)

| Method | Path | Notes |
|--------|------|--------|
| `GET` | `/user` | Current profile. |
| `PATCH` | `/user` | Update `name` and/or `email`. |
| `PUT` | `/user/password` | Body: `current_password`, `password`, `password_confirmation`. |

### User management (CRUD)

| Method | Path | Notes |
|--------|------|--------|
| `GET` | `/users` | Paginated; query `per_page` (1–100, default 15). |
| `POST` | `/users` | Create (`name`, `email`, `password`, `password_confirmation`). |
| `GET` | `/users/{id}` | |
| `PATCH` | `/users/{id}` | Partial update `name` and/or `email`. |
| `DELETE` | `/users/{id}` | **409** if target is yourself or user has any `book_rents` (DB `RESTRICT`). |

Self-service `/user` always uses the token’s user only.

### Book management (CRUD, search, sort, filter)

| Method | Path | Notes |
|--------|------|--------|
| `GET` | `/books` | Query: `title`, `author`, `genre` — case-insensitive substring (`LIKE`). `available_only` (boolean). `sort_by` whitelist: `title`, `author`, `genre`, `created_at`, `available_copies`, `total_copies`; `sort_dir` `asc`/`desc`. Defaults: `sort_by=title`, `sort_dir=asc`, `per_page=15` (max 100). |
| `POST` | `/books` | Create; `available_copies` defaults to `total_copies` if omitted. |
| `GET` | `/books/{id}` | |
| `PATCH` | `/books/{id}` | |
| `DELETE` | `/books/{id}` | **409** if any **active** rental exists (`DeleteBookAction`). |

**Deletes and history:** Deleting a book is blocked while **active** rentals exist. If delete is allowed, `book_rents.book_id` is **ON DELETE CASCADE**, so finished rental rows for that book are removed with it. `book_rents.user_id` is **ON DELETE RESTRICT**. PostgreSQL enforces `CHECK` on copies and `reading_progress`; SQLite (CI) relies on validation and tests.

### Renting books

| Method | Path | Notes |
|--------|------|--------|
| `GET` | `/rentals` | Paginated; defaults `per_page=15` (max 100). |
| `POST` | `/rentals` | Body: `book_id`, `due_date` (after now); **409** if no copies. |
| `GET` | `/rentals/{id}` | |
| `PATCH` | `/rentals/{id}/extend` | `due_date`; **409** if not `active`. |
| `GET` | `/rentals/{id}/reading-progress` | `{ "data": { "reading_progress": … } }` |
| `PATCH` | `/rentals/{id}/reading-progress` | `reading_progress` 0–100; **409** if finished. |
| `POST` | `/rentals/{id}/finish` | Returns a copy to `available_copies`; **409** if already finished. |

### Health

| Method | Path | Notes |
|--------|------|--------|
| `GET` | `/status/liveness` | **200**; unified JSON (`data`, `message`). |

Route map: [`routes/api.php`](routes/api.php); API prefix is set in [`bootstrap/app.php`](bootstrap/app.php). Response/error envelope: [`app/Support/ApiResponse.php`](app/Support/ApiResponse.php) and exception rendering for `api/*` in [`bootstrap/app.php`](bootstrap/app.php). Successful deletes and logout use **200** + `message`, not **204**.

## Architectural approach

**Chosen style: Option A — REST API** (versioned resource routes, JSON, HTTP semantics).

### Why REST (vs GraphQL / CQRS for this scope)

The domain maps cleanly to resources (`users`, `books`, `rentals`). REST keeps client and tooling ergonomics simple (curl, proxies, OpenAPI). **GraphQL** would add schema and resolver overhead for mostly CRUD and a few rental actions, without a stated need for arbitrary client field selection or a single graph for many product variants. **CQRS / heavy clean architecture** pays off for strong read/write asymmetry or event-sourced audit; here it would add ceremony beyond the assignment size. **Trade-off:** multi-step workflows are multiple HTTP calls instead of one graph query — acceptable until requirements demand aggregates or many tailored views.

### Implementation notes

- **Controller contracts + OpenAPI** — Attributes live on `app/Http/Contracts/*`; controllers in `app/Http/Controllers/Api/` are bound in `AppServiceProvider` so the spec tracks the public surface.
- **JSON shape** — Same envelope for success and errors on `api/*` via `ApiResponse` and `bootstrap/app.php`.
- **Validation** — Form Request classes per action; policies align with authorization for books, users, and rentals.
- **Domain operations** — `app/Actions/{Book,BookRent,User}/` hold use cases; no separate `DTOs/` or `Services/` trees.

**Second-iteration ideas:** cap rent extensions; rate limits on heavy list endpoints; curl/Postman collection; RBAC if requirements grow.

**Structural trade-off:** Moving OpenAPI off interfaces onto concrete controllers would shrink `AppServiceProvider` and route indirection at the cost of keeping the contract adjacent to HTTP handlers.

## Technical requirements

### Mandatory (assignment)

| Requirement | Where |
|-------------|--------|
| Laravel 12 | `composer.json` |
| Migrations & seeders | `database/migrations/`, [`database/seeders/DatabaseSeeder.php`](database/seeders/DatabaseSeeder.php) |
| Form Requests (validation) | `app/Http/Requests/` |
| Consistent error handling | [`bootstrap/app.php`](bootstrap/app.php) (`api/*`), [`app/Support/ApiResponse.php`](app/Support/ApiResponse.php) |
| Separation of concerns | Actions, Policies, Resources, HTTP layer, routes |

### Optional but valuable

| Item | Status |
|------|--------|
| Unit / feature tests | **Done** — PHPUnit; `composer test`, `composer test:ci`, `composer quality` |
| Swagger / OpenAPI (REST) | **Done** — `composer docs:generate` (alias `openapi`); attributes on contracts + [`app/OpenApi/OpenApiInfo.php`](app/OpenApi/OpenApiInfo.php) |
| GraphQL SDL + Playground | **Not used** — REST chosen |
| Dockerized dev environment | **Not included** |
| DDD-style folder structure | **Partial** — domain-oriented `Actions/`, not full DDD layout |
| API rate limiting | **Partial** — named limiter on `login` only |
| Caching strategy (e.g. book lists) | **Not implemented** |

## Deliverables (assignment checklist)

- [x] **Project code** in this repository (root: `laravel12-library-api/`).
- [x] **README.md** — setup, chosen architecture (REST + justification), endpoint documentation, trade-offs / design notes.
- [ ] **Optional Docker** — not provided.
- [ ] **Optional Postman / GraphQL collection** — not provided.
- [ ] **Optional database diagram** — not provided.

Application root for commands: this directory. In `.env`, use **`APP_NAME=Library API`**, PostgreSQL **`DB_DATABASE=library`**, **`DB_USERNAME=library_user`** (names are independent of the folder name).

## Refactoring session (next interview)

Out of scope for this repository: a follow-up interview may use a separate refactoring exercise; it is not part of the delivered code.

## Repository layout (summary)

`app/Actions` (use cases), `app/Http` (contracts, controllers, requests, resources), `app/Models`, `app/Policies`, `app/OpenApi`, `database/` (migrations, factories, seeders), `routes/api.php`, `tests/`. No exhaustive file tree — explore the tree in your editor or clone.

## Prerequisites

- PHP **8.3+** — use the **same** binary for Composer and Artisan (on macOS, default `php` may be older; point to e.g. Homebrew PHP).
- Composer
- PostgreSQL — superuser access once to create role and database

### PHP binary (example)

If `php -v` is not 8.3+:

```bash
export PHP_BIN=/opt/homebrew/opt/php@8.3/bin/php
# Use $PHP_BIN for `php` and when invoking Composer if needed
```

If `php -v` already shows 8.3+, use `composer` and `php artisan` normally.

## Setup (first-time / clone)

Order matters.

### 1. Install PHP dependencies

```bash
composer install
```

### 2. Environment file

```bash
cp .env.example .env
```

Set PostgreSQL: `DB_CONNECTION=pgsql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE=library`, `DB_USERNAME=library_user`, and a **random** `DB_PASSWORD`. **Do not commit** `.env`. The password in SQL below must **match** `DB_PASSWORD`.

### 3. Create PostgreSQL role and database (once)

As superuser (e.g. `postgres`):

```sql
CREATE USER library_user WITH PASSWORD 'same_value_as_db_password_in_env';
CREATE DATABASE library OWNER library_user ENCODING 'UTF8';
```

### 4. Application key and migrations

```bash
php artisan key:generate
php artisan migrate
```

### 5. Optional: demo data

```bash
php artisan db:seed
```

[`DatabaseSeeder`](database/seeders/DatabaseSeeder.php) adds a demo user (`test@example.com`), books, and rentals. Password comes from [`UserFactory`](database/factories/UserFactory.php).

### 6. Optional

```bash
php artisan config:clear
```

Useful after `.env` changes when config is cached.

## Run the API

```bash
php artisan serve
```

Example: `GET http://localhost:8000/api/v1/status/liveness` → **200** with unified JSON.

## Development & CI

- **OpenAPI** — [zircote/swagger-php](https://github.com/zircote/swagger-php): `composer run docs:generate` (writes `storage/api-docs/openapi.yaml`; gitignored output aside from folder `.gitignore`).
- **Code style** — `composer run format`; check only: `composer run lint` (`pint --test`).
- **Tests** — `composer run test` or `composer run test:ci` (`migrate:fresh` + tests).
- **CI** — [`.github/workflows/ci.yml`](.github/workflows/ci.yml): PHP 8.3, `composer install`, `lint`, `docs:generate`, `test:ci` with SQLite and env overrides (`DB_*`, `CACHE_STORE=array`, etc.).

**Local parity:** If `.env` uses PostgreSQL, create `database/testing.sqlite` and run e.g.:

```bash
DB_CONNECTION=sqlite DB_DATABASE=database/testing.sqlite composer run test:ci
```

Or `composer run quality` (lint + `test:ci`) with the same prefix. `phpunit.xml` points tests at SQLite; `test:ci`’s `migrate:fresh` must target the same DB.
