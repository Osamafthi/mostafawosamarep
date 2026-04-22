# Ecommerce Backend

The Laravel API in `new-backend/` is the single source of truth for the
backend. It exposes the same product/category/order endpoints the old
hand-rolled PHP API did, plus customer authentication
(register/login/logout/me) via Laravel Sanctum. All clients (web admin,
storefront, Flutter) should point at `http://<host>/api/v1/...`.

| Folder         | Role                                                         |
| -------------- | ------------------------------------------------------------ |
| `new-backend/` | Laravel 13 API (Sanctum, Form Requests, Queues, Flysystem) — **the backend** |
| `index.php`    | Static storefront landing page (Tailwind CDN)                |
| `views/admin/` | Admin SPA (Products, Categories, Orders, Admins) — vanilla JS, wired to the Laravel API |
| `assets/`      | CSS/JS for the admin SPA (`admin.js`, `orders.js`, `categories.js`, `admins.js`, `pagination.js`) + uploaded images |
| `scripts/`     | Small helper scripts (filesystem permissions, etc.)          |

The old `api/`, `classes/`, `includes/`, and `migrations/` folders have been
removed — their responsibilities now live entirely inside `new-backend/`.
The remaining root-level files above are frontend assets.

> **Running on XAMPP?** XAMPP ships PHP 8.2.x, but Laravel 13 requires PHP 8.3+.
> Do **not** try to have XAMPP Apache serve the Laravel app. Instead:
>
> 1. Run the Laravel API on its own dev server: `cd new-backend && php artisan serve` → `http://localhost:8000`.
> 2. Let XAMPP keep serving the legacy admin SPA (`views/admin/...`) and the storefront (`index.php`).
> 3. The admin SPA is already wired to call `http://localhost:8000/api/v1` (see `views/admin/*.php` and `assets/js/api.js`). CORS is fully open for development.

---

## New Laravel backend (`new-backend/`)

### Stack

- PHP 8.3, Laravel 13
- MySQL (development can also use SQLite)
- [Laravel Sanctum](https://laravel.com/docs/sanctum) for token authentication (admin + customer)
- Form Requests for validation (`app/Http/Requests/V1`)
- Database-backed queue + 3 dispatched jobs (`app/Jobs`)
- Flysystem / `Storage` facade for image uploads (public disk)
- API versioned under `/api/v1`, fully stateless (no sessions, no cookies)

### First-time setup

```bash
cd new-backend
cp .env.example .env
composer install
php artisan key:generate

# Create the MySQL database first:
#   CREATE DATABASE Ecommerce CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
php artisan migrate --seed

# Publish the /storage/... symlink so uploaded images are web-accessible.
php artisan storage:link

# In one terminal: serve the API
php artisan serve          # → http://localhost:8000

# In another terminal: run the queue worker (for order emails, image optimization, etc.)
php artisan queue:work database
```

The seeder creates a default admin:

- Email: `admin@ecommerce.local`
- Password: `admin123`

...plus five starter categories (Electronics, Fashion, Home & Office,
Health & Beauty, Computing). Change the admin password after first login.

### Environment

Key `.env` values:

| Key                   | Default                  | Purpose                                   |
| --------------------- | ------------------------ | ----------------------------------------- |
| `DB_CONNECTION`       | `mysql`                  | Primary datastore                         |
| `QUEUE_CONNECTION`    | `database`               | Drives the `jobs` table                   |
| `FILESYSTEM_DISK`     | `public`                 | Uploads go to `storage/app/public`        |
| `MAIL_MAILER`         | `log`                    | Dev: writes emails to `storage/logs`      |
| `MAIL_ADMIN_ADDRESS`  | `admin@ecommerce.local`  | Recipient of "new order" admin emails     |
| `SANCTUM_STATEFUL_DOMAINS` | *(empty)*           | Pure token mode — no session fallback     |

### Authentication (Sanctum abilities)

The API uses **one** Sanctum guard but **two** Eloquent models as token
holders. Every token is issued with exactly one ability:

- **`admin`** ability → minted by `POST /api/v1/auth/admin/login`, used for all `/api/v1/admin/*` routes.
- **`customer`** ability → minted by `POST /api/v1/auth/customer/{register,login}`, used for all `/api/v1/customer/*` routes.

Routes are protected by `auth:sanctum` + `abilities:admin` or `abilities:customer`.
Unauthenticated guests can still browse the public catalog and place orders
(guest checkout). If a customer token is presented on `POST /api/v1/orders`,
the order is linked to that customer automatically.

Clients send the token in the standard `Authorization` header:

```
Authorization: Bearer <token>
```

#### Admin login

```bash
curl -X POST http://localhost:8000/api/v1/auth/admin/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"admin@ecommerce.local","password":"admin123"}'
```

#### Customer register / login

```bash
curl -X POST http://localhost:8000/api/v1/auth/customer/register \
  -H 'Content-Type: application/json' \
  -d '{"name":"Jane","email":"jane@example.com","password":"secret123","password_confirmation":"secret123"}'
```

```bash
curl -X POST http://localhost:8000/api/v1/auth/customer/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"jane@example.com","password":"secret123"}'
```

### Endpoints (base: `/api/v1`)

#### Public

| Method | Path                              | Notes                                       |
| ------ | --------------------------------- | ------------------------------------------- |
| POST   | `/auth/admin/login`               | `{ email, password }` → admin token         |
| POST   | `/auth/customer/register`         | `{ name, email, password, password_confirmation, phone? }` |
| POST   | `/auth/customer/login`            | `{ email, password }` → customer token      |
| GET    | `/products`                       | `?q=&category_id=&status=&page=&limit=`     |
| GET    | `/products/{id}`                  |                                             |
| GET    | `/categories`                     |                                             |
| POST   | `/orders`                         | Guest checkout (or customer-linked if a `customer`-ability token is present) |

#### Customer (`Authorization: Bearer <customer token>`)

| Method | Path                    |
| ------ | ----------------------- |
| POST   | `/customer/logout`      |
| GET    | `/customer/me`          |
| GET    | `/customer/orders`      |
| GET    | `/customer/orders/{id}` |

#### Admin (`Authorization: Bearer <admin token>`)

| Method | Path                                      |
| ------ | ----------------------------------------- |
| POST   | `/admin/logout`                           |
| GET    | `/admin/me`                               |
| GET    | `/admin/stats`                            |
| GET    | `/admin/products`                         |
| POST   | `/admin/products`                         |
| GET    | `/admin/products/{id}`                    |
| PUT    | `/admin/products/{id}`                    |
| DELETE | `/admin/products/{id}`                    |
| GET    | `/admin/categories`                       |
| POST   | `/admin/categories`                       |
| PUT    | `/admin/categories/{id}`                  |
| DELETE | `/admin/categories/{id}`                  |
| GET    | `/admin/admins`                           |
| POST   | `/admin/admins`                           |
| DELETE | `/admin/admins/{id}`                      |
| GET    | `/admin/orders`                           |
| GET    | `/admin/orders/stats`                     |
| GET    | `/admin/orders/{id}`                      |
| PATCH  | `/admin/orders/{id}/status`               |
| PATCH  | `/admin/orders/{id}/payment-status`       |
| POST   | `/admin/upload` (multipart, field `file`) |

### Response envelope

Every JSON response uses the same shape the legacy API used, so existing
clients don't need to change:

```jsonc
// Success
{ "success": true, "data": { /* ... */ } }

// Error (400 / 401 / 403 / 404 / 409 / 422 / 500)
{ "success": false, "error": "message", "errors": { "field": ["..."] } }
```

The envelope is enforced centrally in
[`new-backend/bootstrap/app.php`](new-backend/bootstrap/app.php) via exception
renderers that map `ValidationException`, `AuthenticationException`,
`AuthorizationException`, `ModelNotFoundException`, and generic `HttpException`s
onto `{ success, error, errors? }`.

### Example: place a guest order

```bash
curl -X POST http://localhost:8000/api/v1/orders \
  -H 'Content-Type: application/json' \
  -d '{
    "customer_name":    "Jane Doe",
    "customer_email":   "jane@example.com",
    "customer_phone":   "+201234567890",
    "shipping_address": "Cairo, Egypt",
    "items": [ { "product_id": 1, "quantity": 2 } ]
  }'
```

Totals are recomputed server-side from the current product prices — client
totals are ignored. Stock is decremented inside a transaction. After the
transaction commits, two queued jobs are dispatched:

- `SendOrderConfirmationToCustomer` → sends `OrderPlacedMail` to the customer
- `SendOrderNotificationToAdmin` → sends `OrderReceivedMail` to `MAIL_ADMIN_ADDRESS`

Make sure `php artisan queue:work` is running to process them.

### Example: create product (admin)

```bash
curl -X POST http://localhost:8000/api/v1/admin/products \
  -H 'Content-Type: application/json' \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -d '{
    "name": "Sample Watch",
    "description": "A nice watch",
    "price": 199.99,
    "discount_price": 149.99,
    "stock": 25,
    "category_id": 1,
    "status": "active"
  }'
```

### Example: upload an image (admin)

```bash
curl -X POST http://localhost:8000/api/v1/admin/upload \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -F "file=@/path/to/photo.jpg"
```

The response returns `{ url, path, disk, filename, size, mime }`. The URL
resolves through `/storage/products/<hashed-name>.<ext>` (requires
`php artisan storage:link`). An `OptimizeProductImage` job is queued to
re-encode and resize the stored file in the background.

### Queues & Jobs

All business-critical side effects are queued so the API responds fast and
is crash-resilient:

| Job                                 | Dispatched from              | Purpose                               |
| ----------------------------------- | ---------------------------- | ------------------------------------- |
| `SendOrderConfirmationToCustomer`   | `OrderPlacementService`      | Customer-facing order confirmation    |
| `SendOrderNotificationToAdmin`      | `OrderPlacementService`      | Admin-facing new-order notification   |
| `OptimizeProductImage`              | `Admin\UploadController`     | Downscale + re-encode uploaded images |

Run a worker with `php artisan queue:work database`.

### Storage

Product images are stored on the `public` disk (configured in
[`config/filesystems.php`](new-backend/config/filesystems.php)) at
`storage/app/public/products/`. Run `php artisan storage:link` once so they
are reachable at `http://<host>/storage/products/...`. Switching to S3 is a
configuration-only change (set `FILESYSTEM_DISK=s3` and the AWS vars).

### Statelessness & versioning

- No session middleware on `/api/*` (the `api` route group is not wrapped by
  `StartSession` / `ShareErrorsFromSession`). Authentication relies entirely on
  Sanctum bearer tokens. Clients are free to scale horizontally.
- `SANCTUM_STATEFUL_DOMAINS` is empty and `config/sanctum.php` sets `'guard' => []`,
  so Sanctum never attempts session-based auth for the API.
- Versioning lives in the URL: `/api/v1/...`. Introduce `/api/v2/...` by
  adding a sibling route group in [`routes/api.php`](new-backend/routes/api.php)
  and a matching `App\Http\Controllers\Api\V2\*` namespace.

### Security notes (before production)

- Change the seeded admin password immediately.
- Restrict CORS origins in [`config/cors.php`](new-backend/config/cors.php) to
  your real frontend origin(s) rather than `*`.
- Set `APP_DEBUG=false`.
- Configure a real mailer (SMTP / SES / Postmark / Resend) instead of `log`.
- Serve over HTTPS.
- Consider token expiration via `config('sanctum.expiration')` (minutes).

---

## Seeding test data

### Products seeder (300 fake products)

For development and pagination testing, a dedicated seeder generates a
configurable number of fake products with realistic data (random names,
prices, 30% discount rate, stock 0-150, random categories, 85% active).
Each product gets a real image randomly selected from the existing files
in `assets/js/uploads/`, so thumbnails render immediately.

```bash
cd new-backend

# Create 300 products (default)
php artisan db:seed --class=ProductsSeeder

# Or override the count
PRODUCTS_SEED_COUNT=500 php artisan db:seed --class=ProductsSeeder
```

The seeder is **not** registered in `DatabaseSeeder::run()` on purpose —
running `php artisan db:seed` won't spam more products. You only seed
them when you explicitly ask for it.

Architecture:

| File                                                | Role                                            |
| --------------------------------------------------- | ----------------------------------------------- |
| `database/factories/ProductFactory.php`             | Defines one fake product via Faker              |
| `database/seeders/ProductsSeeder.php`               | Runs the factory `N` times in batches of 50     |

The `Product` model's `booted()` hook handles slug uniqueness via
`SlugService`, so the factory never worries about collisions.

---

## Admin SPA (`views/admin/`)

The admin panel is a vanilla-JS SPA that talks to the Laravel API. It is
intentionally kept framework-free so it stays easy to read, edit, and
eventually port to React/Vue/Flutter.

### Layout

| Page                          | Script               | Purpose                                    |
| ----------------------------- | -------------------- | ------------------------------------------ |
| `views/admin/login.php`       | `assets/js/auth.js`  | Admin login (Sanctum token in localStorage)|
| `views/admin/index.php`       | `assets/js/admin.js` | Products CRUD + stats + pagination         |
| `views/admin/categories.php`  | `assets/js/categories.js` | Categories CRUD                       |
| `views/admin/orders.php`      | `assets/js/orders.js`| Orders list + status / payment updates     |
| `views/admin/admins.php`      | `assets/js/admins.js`| Admin accounts CRUD (self-protection on)   |

Shared modules:

| File                              | Responsibility                                              |
| --------------------------------- | ----------------------------------------------------------- |
| `assets/js/api.js`                | Fetch wrapper — auto-attaches Bearer token, auto-redirects on 401, unwraps the `{ success, data }` envelope |
| `assets/js/auth.js`               | Login form, `guardAdminPage()`, `formatError()`, `bindLogout()` |
| `assets/js/pagination.js`         | Reusable base `Pagination` class                            |
| `assets/js/pagination-compact.js` | `CompactPagination extends Pagination` (first/last + ellipsis) |
| `assets/css/admin.css`            | Shared design tokens + component styles                     |

All pages configure the API base URL the same way:

```js
window.APP_CONFIG = {
    basePath: '/mostafawosama',
    apiBase:  'http://localhost:8000/api/v1'
};
```

### Categories management

Full CRUD for categories lives at `views/admin/categories.php` and hits
the existing admin endpoints:

- `GET    /admin/categories` — list
- `POST   /admin/categories` — create
- `PUT    /admin/categories/{id}` — rename / edit description
- `DELETE /admin/categories/{id}` — delete (backend returns `409` if the
  category is still referenced by products; the UI surfaces it as a red
  toast)

Slugs are auto-generated by the `Category` model's `booted()` hook via
`SlugService`, so the form only asks for `name` + `description`.

### Admin account management

Admins can create and remove other admin accounts from
`views/admin/admins.php`. The feature follows the same architecture as
every other admin resource:

| Layer            | File                                                                   |
| ---------------- | ---------------------------------------------------------------------- |
| Form request     | `app/Http/Requests/V1/Admin/StoreAdminRequest.php`                     |
| Controller       | `app/Http/Controllers/Api/V1/Admin/AdminController.php`                |
| Resource         | Reuses `AdminResource` (already exposes `id`, `name`, `email`, timestamps) |
| Routes           | Inside the existing `auth:sanctum` + `abilities:admin` group in `routes/api.php` |

Rules enforced server-side in `AdminController::destroy`:

1. **Cannot delete yourself** — returns `422` with
   `"You cannot delete your own admin account."`
2. **Cannot delete the last admin** — returns `422` with
   `"At least one admin account must remain."`
3. On successful delete, the target admin's Sanctum tokens are revoked
   (`$admin->tokens()->delete()`) so any active session is killed
   immediately.

Password rules mirror customer registration: `min:8` + `confirmed`
(requires `password_confirmation` in the payload). Hashing is automatic
via the `Admin` model's `'password' => 'hashed'` cast — no manual
`Hash::make()` anywhere.

#### Example: create a new admin

```bash
curl -X POST http://localhost:8000/api/v1/admin/admins \
  -H 'Content-Type: application/json' \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -d '{
    "name": "Jane",
    "email": "jane@ecommerce.local",
    "password": "secretpass",
    "password_confirmation": "secretpass"
  }'
```

### Pagination (reusable base class + inheritance)

Pagination is a single reusable component shared by every admin page that
lists data. The old duplicate `renderPagination()` functions in `admin.js`
and `orders.js` are gone — they were replaced by one class:

```
window.Pagination              ← base (assets/js/pagination.js)
       │
       └── window.CompactPagination   ← subclass (assets/js/pagination-compact.js)
                                        adds first/last buttons + ellipses
```

Every rendering step is a small overridable method so a subclass only has
to touch the part it cares about:

| Method                          | Purpose                                      |
| ------------------------------- | -------------------------------------------- |
| `render(data)`                  | Entry point — consumes the API payload       |
| `build()`                       | Composes `prev + pages + next`               |
| `buildPrev()` / `buildNext()`   | The ‹ / › arrows                             |
| `buildPages()`                  | Middle strip (override for different windowing) |
| `windowRange()`                 | Computes `{ from, to }` around the current page |
| `button(opts)`                  | Renders one `<button data-page>`             |

Event handling uses a single delegated listener on the root container, so
re-renders don't leak listeners. A `destroy()` method cleans it up on
teardown.

Consumes the Laravel `PaginatedCollection` envelope as-is:

```json
{ "items": [...], "total": 305, "page": 1, "limit": 20, "last_page": 16 }
```

#### Using it on a page

```js
let pager;
async function init() {
    pager = new Pagination({              // or CompactPagination
        root: '#pagination',
        siblings: 2,
        onPageChange: (page) => {
            state.page = page;
            loadProducts();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
    });
    await loadProducts();
}

async function loadProducts() {
    const data = await Api.get('/admin/products', { query: { page: state.page } });
    renderRows(data);
    pager.render(data);                   // same call on every page
}
```

Products uses `CompactPagination` (16+ pages → needs ellipses). Orders
uses the base `Pagination` (fewer pages). Adding a new paginated page is
two lines of setup plus `pager.render(data)` — zero copy-paste.

#### Subclassing for page-specific rules

```js
class NoArrowPagination extends Pagination {
    constructor(opts) { super({ ...opts, showPrevNext: false }); }
}

class BiggerWindowPagination extends Pagination {
    windowRange() {
        return {
            from: Math.max(1, this.page - 4),
            to:   Math.min(this.lastPage, this.page + 4),
        };
    }
}
```

The `CompactPagination` class in `pagination-compact.js` is the canonical
example of this pattern — it overrides exactly **one** method
(`buildPages`) and inherits everything else.

### Image paths convention

Uploaded-image URLs stored in the database are absolute paths that
include the XAMPP mount prefix (`/mostafawosama`) so the browser can
resolve them directly, e.g.:

```
/mostafawosama/assets/js/uploads/20260419_030351_86010ab0a706.webp
```

If you seed products with a custom script, keep this shape so thumbnails
render on the admin SPA. The `ProductsSeeder` above already does.

---

## Storefront (`index.php`)

The root `index.php` is a static Tailwind-CDN landing page. It currently
serves as a simple public face; wiring it to the Laravel API (products,
categories, cart) is the recommended next step.
