#!/usr/bin/env bash
set -euo pipefail

php artisan migrate --force
php artisan storage:link || true
php artisan optimize
php artisan view:cache
php artisan up

echo "Chambapp deployed. Run SMOKE_TEST.md before opening traffic."
