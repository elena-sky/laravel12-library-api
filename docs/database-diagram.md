# Database diagram (Library API)

Simple view of **domain tables** and how they relate. Laravel/Sanctum framework tables (`sessions`, `cache`, `jobs`, …) are omitted unless noted.

## ER diagram

Rendered export (PNG):

![Library API database ER diagram](mermaid-diagram-2026-03-30-102825.png)

Editable source (Mermaid):

```mermaid
erDiagram
    users ||--o{ book_rents : "has many"
    books ||--o{ book_rents : "has many"
    users ||--o{ personal_access_tokens : "Sanctum tokens"

    users {
        bigint id PK
        string name
        string email
        string password
        timestamp email_verified_at
        timestamp created_at
        timestamp updated_at
    }

    books {
        bigint id PK
        string title
        string author
        string genre
        text description
        int total_copies
        int available_copies
        timestamp created_at
        timestamp updated_at
    }

    book_rents {
        bigint id PK
        bigint user_id FK
        bigint book_id FK
        string status
        timestamp rented_at
        timestamp due_date
        timestamp returned_at
        int reading_progress
        int extended_count
        timestamp created_at
        timestamp updated_at
    }

    personal_access_tokens {
        bigint id PK
        string tokenable_type
        bigint tokenable_id
        string name
        string token
        text abilities
        timestamp last_used_at
        timestamp expires_at
        timestamp created_at
        timestamp updated_at
    }
```

## Referential actions

| From | To | On delete |
|------|-----|-----------|
| `book_rents.user_id` | `users.id` | **RESTRICT** |
| `book_rents.book_id` | `books.id` | **CASCADE** |

## Other constraints

- **`users.email`** — unique (see migration).

## PostgreSQL-only checks (see migrations)

On **`books`:** `total_copies >= 0`, `available_copies >= 0`, `available_copies <= total_copies`.

On **`book_rents`:** `reading_progress` in **0–100**, `extended_count >= 0`.

SQLite in CI relies on application validation instead of these `CHECK` constraints.
