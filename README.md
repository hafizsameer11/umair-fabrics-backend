# umair-fabrics-backend

Laravel API + Blade admin for **Umair Fabrics** — [backend.umairfabrics.com](https://backend.umairfabrics.com)

## Stack

- Laravel 12
- MySQL
- REST API at `/api/v1`
- Admin panel at `/admin`

Frontend repo: [umair-fabrics](https://github.com/hafizsameer11/umair-fabrics)

## Local development (XAMPP)

1. Create MySQL database `ecommerce_fahad` in phpMyAdmin
2. Setup:

```bash
copy .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan serve
```

Admin: `admin@store.com` / `password`

API: http://127.0.0.1:8000/api/v1

## Production (Hostinger)

1. Point subdomain `backend.umairfabrics.com` document root to `public/`
2. Copy `.env.production.example` → `.env` and fill database credentials
3. Run once:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
```

4. Git auto-deploy command in hPanel:

```bash
bash deploy/hostinger.sh
```

## Uploaded images

Product and CMS images are stored in `storage/app/public/` and committed to this repo so they deploy with the code. After `php artisan storage:link`, they are served from `/storage/...`.

## Import products (dev)

```bash
php artisan shop:import-shopify https://noorsapparel.com --limit=20 --download-images
php artisan shop:assign-collections
```
