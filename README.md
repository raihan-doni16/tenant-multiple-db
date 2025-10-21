# Ecommerce Multi-Tenant Platform

Modern ecommerce demo that provisions an isolated database per tenant while sharing a central admin interface. The backend is built with Laravel 12 and Stancl Tenancy v3, while the SPA frontend uses Vue 3, Pinia, and Vite.

---

## Stack Overview

- **PHP / Laravel 12** – primary backend framework, Sanctum for API auth.
- **Stancl Tenancy 3.9** – database-per-tenant orchestration, automatic DB provisioning.
- **PostgreSQL (default)** – central + tenant databases (configurable via env).  
  - The test suite uses SQLite (file-based) to keep execution isolated and fast.
- **Vue 3 + Vite + Tailwind CSS** – SPA frontend living in `resources/js`.
- **Pinia & Vue Router** – state management and single-page routing.
- **PHPUnit** – unit/feature testing.

### Composer Packages

| Package | Purpose |
| --- | --- |
| `laravel/framework` | Core Laravel application. |
| `stancl/tenancy` | Multi-database tenancy management. |
| `laravel/sanctum` | API authentication tokens. |
| `laravel/tinker` | REPL/debugging support. |

### NPM Packages

| Package | Purpose |
| --- | --- |
| `vue`, `vue-router`, `pinia` | SPA application and state management. |
| `axios` | HTTP client for frontend API calls. |
| `vite`, `@vitejs/plugin-vue` | Frontend bundler/dev server. |
| `@tailwindcss/vite`, `tailwindcss` | Utility-first styling. |

---

## Environment & Database Configuration

The application expects two database connections:

1. **Central connection** – stores tenant metadata and admin users.  
   Controlled via `CENTRAL_DB_*` env vars.
2. **Tenant template / runtime connection** – cloned per tenant on provisioning.  
   Controlled via `TENANT_DB_*` env vars.

Example `.env` snippet (PostgreSQL):

```ini
DB_CONNECTION=central

CENTRAL_DB_DRIVER=pgsql
CENTRAL_DB_HOST=localhost
CENTRAL_DB_PORT=5432
CENTRAL_DB_DATABASE=ecommerce_central
CENTRAL_DB_USERNAME=postgres
CENTRAL_DB_PASSWORD=postgres

TENANT_DB_DRIVER=pgsql
TENANT_DB_HOST=localhost
TENANT_DB_PORT=5432
TENANT_DB_USERNAME=postgres
TENANT_DB_PASSWORD=postgres
TENANCY_DATABASE_PREFIX=tenant_
```



### Running migrations

- Central schema: `php artisan migrate`
- Provisioned tenant migrations run automatically when a new tenant is created.

---

## Local Development

1. Copy the example env and tweak DB credentials.
   ```bash
   cp .env.example .env
   ```
2. Install dependencies and build assets.
   ```bash
   composer install
   npm install
   npm run build   # or npm run dev for watch mode
   ```
3. Generate keys & migrate central DB.
   ```bash
   php artisan key:generate
   php artisan migrate
   ```
4. Start servers:
   ```bash
   php artisan serve
   npm run dev
   ```

### Seed the central admin user

Run the database seeder once to create the default central admin account:

```bash
php artisan db:seed
```

You can then sign in at `/admin/login` with:

- Email: `admin@tenant.com`
- Password: `password`

---

## Tests

The project uses PHPUnit with both unit and feature suites.

| Test | Purpose |
| --- | --- |
| `Tests\Unit\ExampleTest` | Skeleton sanity check. |
| `Tests\Feature\PublicTenantCatalogTest` | Public tenant catalog pagination/search and data exposure. |
| `Tests\Feature\TenantIsolationTest` | Verifies products and carts remain isolated per tenant DB. |
| `Tests\Feature\TenantProductAccessTest` | Ensures owner-scoped product listing behaves as expected. |

Run the full suite:

```bash
php artisan test
```

All feature tests boot isolated tenant databases using SQLite during CI/local runs.

---

---

