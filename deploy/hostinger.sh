#!/usr/bin/env bash
# Hostinger: run after git pull (Laravel API + admin)
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"

echo "==> Installing backend dependencies..."
cd "$ROOT"

composer install --no-dev --optimize-autoloader --no-interaction

if [ -f .env ]; then
  php artisan migrate --force
  php artisan storage:link 2>/dev/null || true
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  php artisan cache:clear
fi

chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "==> Backend deployed successfully."
echo "    Document root must point to: $ROOT/public"
