#!/usr/bin/env bash
set -euo pipefail

maintenance_started=false

keep_service_safe_on_error() {
    exit_code=$?
    trap - EXIT

    if [[ "${maintenance_started}" == true ]]; then
        echo "Deployment failed after maintenance started; Chambapp remains in maintenance mode." >&2
        echo "Restore the previous release/schema or finish the deployment, then run 'php artisan up' explicitly." >&2
    fi

    exit "${exit_code}"
}

trap keep_service_safe_on_error EXIT

php artisan config:clear
php artisan production:preflight
maintenance_started=true
php artisan down

php artisan migrate --force
php artisan storage:link
php artisan optimize
php artisan view:cache
php artisan production:preflight --runtime
php artisan queue:restart
php artisan up
maintenance_started=false

trap - EXIT

echo "Chambapp deployed. Run SMOKE_TEST.md before opening traffic."
