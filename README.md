# Multi-Vendor Checkout

Laravel-based multi-vendor checkout demo with vendor-split orders, cart and checkout services, form requests, events/listeners, Sanctum authentication, and admin authorization via Gates.

## Setup

1. Clone the repository.
2. Install dependencies:

```bash
composer install
```

3. Copy environment settings:

```bash
copy .env.example .env
```

4. Configure the database in `.env`.
   Default `.env.example` uses SQLite. For SQLite, create the database file if needed:

```bash
New-Item -ItemType File -Force database\\database.sqlite
```

5. Generate the application key:

```bash
php artisan key:generate
```

6. Run migrations and seeders:

```bash
php artisan migrate:fresh --seed
```

7. Start the app:

```bash
php artisan serve
```

8. Optional background workers:

```bash
php artisan queue:work
php artisan schedule:work
```

9. Logging:

The project uses Laravel daily log rotation. Logs are written under `storage/logs/` with filenames like `laravel-YYYY-MM-DD.log`, and the default retention is `14` days. You can adjust this through:

```env
LOG_CHANNEL=stack
LOG_STACK=daily
LOG_DAILY_DAYS=14
```

10. Queue and email testing:

This project uses queued listeners for order placement and payment side effects. With the default configuration:

```env
QUEUE_CONNECTION=database
MAIL_MAILER=log
```

run the queue worker to process queued listeners and see the email/log activity:

```bash
php artisan queue:work
```

With `MAIL_MAILER=log`, the email content is written to the daily log file in `storage/logs/` instead of being sent to a real inbox.

If you want the listener logic to run immediately during the request for simpler local testing, you can switch to:

```env
QUEUE_CONNECTION=sync
```

In that mode, no queue worker is needed, but it is less representative of a production-style asynchronous setup.

## Seeded Accounts

All seeded users use the password `password123`.

### Customers

- `customer1@example.com`
- `customer2@example.com`

### Vendor Owners

- `john@techstore.com` -> `TechStore Pro`
- `sarah@fashionhub.com` -> `FashionHub`
- `mike@homedecor.com` -> `HomeDecor Plus`

### Admin

- `admin@example.com`

## Seed Data

The main seeder runs:

- [`CustomerSeeder`](C:/laragon/www/multi-vendor-checkout/database/seeders/CustomerSeeder.php)
- [`VendorSeeder`](C:/laragon/www/multi-vendor-checkout/database/seeders/VendorSeeder.php)
- [`ProductSeeder`](C:/laragon/www/multi-vendor-checkout/database/seeders/ProductSeeder.php)
- [`AdminSeeder`](C:/laragon/www/multi-vendor-checkout/database/seeders/AdminSeeder.php)

## Useful Endpoints

All API endpoints are served under the `/api` prefix.

- `POST /api/register`
- `POST /api/login`
- `POST /api/logout`
- `GET /api/products`
- `POST /api/cart/add`
- `GET /api/cart`
- `POST /api/checkout`
- `POST /api/payment/success`
- `GET /api/orders`
- `GET /api/admin/orders`
- `GET /api/admin/stats`

For protected routes, send the Sanctum bearer token returned by `POST /api/login` or `POST /api/register`, along with `Accept: application/json`.

## Blade UI

A minimal Blade-based frontend is also included for local demo purposes. Available UI routes:

- `GET /`
- `GET /login`
- `GET /products`
- `GET /cart`
- `GET /checkout/payment`
- `GET /checkout/success`

How it works:

- The Blade UI calls the existing `/api/...` endpoints using JavaScript `fetch`.
- Login stores the returned Sanctum bearer token in `localStorage`.
- Protected API calls send `Authorization: Bearer <token>` automatically from the frontend.
- The payment page uses the checkout response stored in browser state to confirm payment through `POST /api/payment/success`.

Suggested UI demo flow:

1. Open `/login`
2. Login with `customer1@example.com` / `password123`
3. Browse `/products` and add items to cart
4. Open `/cart` and click checkout
5. Confirm payment on `/checkout/payment`
6. View the result on `/checkout/success`

## Authentication And Access Note

The project uses a shared authentication flow for both customers and admins, so the same login UI is used for both roles. Access is still separated securely through server-side Gates and policies.

- Customer accounts can be created through the public registration flow.
- Public registration always creates users with the `customer` role.
- Admin accounts are provisioned only through seeders or direct database setup.
- Even though the login entry point is shared, only users with the `admin` role can access admin APIs and the admin portal.

## Architecture Notes

- Business logic lives in service classes:
  [`CartService`](C:/laragon/www/multi-vendor-checkout/app/Services/CartService.php) and [`CheckoutService`](C:/laragon/www/multi-vendor-checkout/app/Services/CheckoutService.php).
- Validation is handled with form requests for cart actions, checkout, and payment success.
- Checkout emits [`OrderPlaced`](C:/laragon/www/multi-vendor-checkout/app/Events/OrderPlaced.php) and [`PaymentSucceeded`](C:/laragon/www/multi-vendor-checkout/app/Events/PaymentSucceeded.php), which are mapped in [`EventServiceProvider`](C:/laragon/www/multi-vendor-checkout/app/Providers/EventServiceProvider.php).
- Admin access is restricted through Gates backed by [`AdminPolicy`](C:/laragon/www/multi-vendor-checkout/app/Policies/AdminPolicy.php).
- Checkout creates one order per vendor so a mixed cart becomes multiple vendor-owned orders.
- Inventory protection is simulated with atomic stock decrements during checkout and stock restoration when stale unpaid orders are auto-cancelled.

## Trade-offs And Assumptions

- Vendor owners are stored as normal users with a linked `vendors` record. The app currently uses the `role` field mainly for `admin` and `customer`, so vendor owners remain `customer` users with vendor profiles.
- Payment handling is mocked. Checkout creates `pending` orders and `pending` payments, and `POST /api/payment/success` simulates the gateway callback/finalization step.
- Email notification is implemented as a lightweight mail/log flow in the listener so local environments work without a real mail provider.
- Application logging uses Laravel daily log rotation for easier maintenance and log retention.
- Race-condition protection is simulated at the database query level rather than through distributed locking.
- Auto-cancel scheduling is registered, but it requires a scheduler process such as `php artisan schedule:work` or a real cron entry to run continuously.
