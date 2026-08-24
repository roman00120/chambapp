<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ProductionPreflight extends Command
{
    protected $signature = 'production:preflight
        {--runtime : Verifica la conexion, el esquema y los bloqueos compartidos reales}';

    protected $description = 'Valida que la configuracion efectiva sea segura antes de desplegar a produccion';

    public function handle(): int
    {
        $checks = [
            $this->check(
                'Version de PHP',
                version_compare(PHP_VERSION, '8.3.0', '>='),
                'Usa PHP 8.3 o superior para CLI y para el servidor web.'
            ),
            $this->check(
                'Entorno de produccion',
                config('app.env') === 'production' && config('app.debug') === false,
                'Configura APP_ENV=production y APP_DEBUG=false; limpia la cache de configuracion si cambiaste .env.'
            ),
            $this->check(
                'Clave de aplicacion',
                $this->hasValidApplicationKey(),
                'Genera una APP_KEY propia del servidor con php artisan key:generate --force.'
            ),
            $this->check(
                'URL publica',
                $this->isPublicHttpsUrl(config('app.url')),
                'Configura APP_URL con el dominio HTTPS publico definitivo.'
            ),
            $this->check(
                'Base de datos persistente',
                $this->hasProductionDatabaseConfiguration(),
                'Usa MySQL, MariaDB o PostgreSQL con nombre, usuario, contrasena y extension PDO configurados.'
            ),
            $this->check(
                'Sesion segura',
                in_array(config('session.driver'), ['database', 'redis'], true)
                    && config('session.secure') === true
                    && config('session.http_only') === true,
                'Usa sesiones database/redis y habilita SESSION_SECURE_COOKIE y SESSION_HTTP_ONLY.'
            ),
            $this->check(
                'Cola persistente',
                in_array(config('queue.default'), ['database', 'redis', 'sqs', 'beanstalkd', 'failover'], true),
                'Configura una cola persistente; no uses sync, null, deferred o background en produccion.'
            ),
            $this->check(
                'Bloqueos compartidos',
                in_array(config('cache.default'), ['database', 'redis', 'memcached', 'dynamodb'], true),
                'Usa CACHE_STORE=database o redis compartido; pagos y tareas programadas dependen de bloqueos distribuidos.'
            ),
            $this->check(
                'Correo transaccional',
                $this->hasProductionMailConfiguration(),
                'Configura un mailer real y una direccion remitente valida; no uses log, array ni localhost.'
            ),
            $this->check(
                'Credenciales de Mercado Pago',
                $this->hasMercadoPagoConfiguration(),
                'Configura client ID, user ID/collector, client secret, webhook secret y access token reales de produccion.'
            ),
            $this->check(
                'Endpoints de Mercado Pago',
                $this->hasOfficialMercadoPagoEndpoints(),
                'Usa los endpoints HTTPS oficiales de Mercado Pago para API y OAuth.'
            ),
            $this->check(
                'Configuracion monetaria',
                $this->hasValidPaymentConfiguration(),
                'Configura moneda MXN, una comision entre 0 y 100 y un timeout positivo.'
            ),
            $this->check(
                'CORS de produccion',
                $this->hasSafeCorsOrigins(),
                'Autoriza solamente origenes HTTPS publicos; elimina comodines y localhost.'
            ),
            $this->check(
                'Assets de Vite',
                ! is_file(public_path('hot')) && $this->hasValidViteManifest(),
                'Elimina public/hot y ejecuta npm ci && npm run build antes de desplegar.'
            ),
            $this->check(
                'Directorios escribibles',
                is_writable(storage_path()) && is_writable(base_path('bootstrap/cache')),
                'Concede al usuario de PHP escritura sobre storage y bootstrap/cache.'
            ),
        ];

        if ($this->option('runtime')) {
            $checks[] = $this->check(
                'Conexion y esquema activos',
                $this->hasRuntimeDatabaseSchema(),
                'Verifica la conexion y ejecuta todas las migraciones sobre la base de datos de produccion.'
            );
            $checks[] = $this->check(
                'Bloqueo compartido activo',
                $this->canAcquireRuntimeLock(),
                'Verifica la tabla/servicio de cache y que el proceso PHP pueda adquirir bloqueos atomicos.'
            );
        }

        $failures = array_filter($checks, fn (array $check): bool => ! $check['passed']);

        foreach ($checks as $check) {
            $status = $check['passed'] ? '<info>OK</info>' : '<error>FALLO</error>';
            $this->line("{$status}  {$check['label']}");
        }

        if ($failures !== []) {
            $this->newLine();
            $this->error('Despliegue bloqueado: corrige las verificaciones fallidas antes de activar mantenimiento.');

            foreach ($failures as $failure) {
                $this->line(' - '.$failure['remediation']);
            }

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Preflight aprobado. La configuracion esta lista para iniciar el despliegue.');

        return self::SUCCESS;
    }

    /**
     * @return array{label: string, passed: bool, remediation: string}
     */
    private function check(string $label, bool $passed, string $remediation): array
    {
        return compact('label', 'passed', 'remediation');
    }

    private function hasValidApplicationKey(): bool
    {
        $key = (string) config('app.key');

        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);

            if ($decoded === false) {
                return false;
            }

            $key = $decoded;
        }

        return Encrypter::supported($key, (string) config('app.cipher'));
    }

    private function hasRuntimeDatabaseSchema(): bool
    {
        try {
            DB::connection()->select('select 1');

            $requiredTables = [
                'users',
                'professional_profiles',
                'job_requests',
                'payments',
                'payment_transactions',
                'commerce_orders',
            ];
            if (config('session.driver') === 'database') {
                $requiredTables[] = 'sessions';
            }
            if (config('queue.default') === 'database') {
                $requiredTables[] = 'jobs';
                $requiredTables[] = 'failed_jobs';
            }
            if (config('cache.default') === 'database') {
                $requiredTables[] = 'cache';
                $requiredTables[] = 'cache_locks';
            }

            if (! collect($requiredTables)->every(fn (string $table): bool => Schema::hasTable($table))) {
                return false;
            }

            return Schema::hasColumns('payments', [
                'kind',
                'external_payment_id',
                'checkout_expires_at',
                'refunded_amount',
                'last_reconciled_at',
            ]) && Schema::hasColumns('commerce_orders', [
                'external_reference',
                'external_preference_id',
                'checkout_expires_at',
            ]);
        } catch (Throwable) {
            return false;
        }
    }

    private function canAcquireRuntimeLock(): bool
    {
        $lock = null;

        try {
            $lock = Cache::lock('chambapp-production-preflight', 15);
            if (! $lock->get()) {
                return false;
            }

            $lock->release();

            return true;
        } catch (Throwable) {
            try {
                $lock?->release();
            } catch (Throwable) {
                // The failed runtime check below is enough; never expose driver details or secrets.
            }

            return false;
        }
    }

    private function isPublicHttpsUrl(mixed $value): bool
    {
        if (! is_string($value) || filter_var($value, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
        $host = strtolower((string) parse_url($value, PHP_URL_HOST));

        if ($scheme !== 'https' || $host === '' || filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return false;
        }

        return ! in_array($host, ['localhost', 'example.com', 'example.net', 'example.org'], true)
            && ! str_ends_with($host, '.localhost')
            && ! str_ends_with($host, '.local')
            && ! str_ends_with($host, '.test')
            && ! str_ends_with($host, '.example');
    }

    private function hasProductionDatabaseConfiguration(): bool
    {
        $connection = (string) config('database.default');
        $driver = (string) config("database.connections.{$connection}.driver");

        if (! in_array($driver, ['mysql', 'mariadb', 'pgsql'], true)) {
            return false;
        }

        $extension = match ($driver) {
            'mysql', 'mariadb' => 'pdo_mysql',
            'pgsql' => 'pdo_pgsql',
        };

        if (! extension_loaded($extension)) {
            return false;
        }

        if (filled(config("database.connections.{$connection}.url"))) {
            return true;
        }

        return filled(config("database.connections.{$connection}.database"))
            && filled(config("database.connections.{$connection}.username"))
            && filled(config("database.connections.{$connection}.password"));
    }

    private function hasProductionMailConfiguration(): bool
    {
        $mailer = (string) config('mail.default');
        $transport = (string) config("mail.mailers.{$mailer}.transport", $mailer);
        $from = (string) config('mail.from.address');
        $fromHost = strtolower((string) substr(strrchr($from, '@') ?: '', 1));

        if ($mailer === '' || in_array($transport, ['log', 'array', 'null'], true)) {
            return false;
        }

        if (filter_var($from, FILTER_VALIDATE_EMAIL) === false
            || in_array($fromHost, ['example.com', 'example.net', 'example.org'], true)) {
            return false;
        }

        if ($transport !== 'smtp') {
            return true;
        }

        $host = strtolower((string) config("mail.mailers.{$mailer}.host"));

        return $host !== ''
            && ! in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            && filled(config("mail.mailers.{$mailer}.username"))
            && filled(config("mail.mailers.{$mailer}.password"));
    }

    private function hasMercadoPagoConfiguration(): bool
    {
        return $this->isConfiguredValue(config('services.mercadopago.client_id'))
            && $this->isConfiguredValue(config('services.mercadopago.user_id'))
            && $this->isConfiguredSecret(config('services.mercadopago.client_secret'))
            && $this->isConfiguredSecret(config('services.mercadopago.webhook_secret'))
            && $this->isConfiguredSecret(config('services.mercadopago.access_token'));
    }

    private function hasOfficialMercadoPagoEndpoints(): bool
    {
        return $this->hasHttpsHost(config('services.mercadopago.api_url'), ['api.mercadopago.com'])
            && $this->hasHttpsHost(config('services.mercadopago.auth_url'), ['auth.mercadopago.com.mx']);
    }

    private function hasValidPaymentConfiguration(): bool
    {
        $fees = [
            config('chambapp.payments.platform_fee_percent'),
            config('chambapp.payments.client_service_fee_percent'),
            config('chambapp.payments.professional_commission_percent'),
        ];
        $timeout = config('chambapp.payments.checkout_timeout');
        $preferenceHours = config('chambapp.payments.preference_lifetime_hours');

        return config('chambapp.payments.currency') === 'MXN'
            && collect($fees)->every(fn (mixed $fee): bool => is_numeric($fee) && (float) $fee >= 0 && (float) $fee <= 100)
            && is_numeric($timeout)
            && (int) $timeout > 0
            && is_numeric($preferenceHours)
            && (int) $preferenceHours >= 1
            && (int) $preferenceHours <= 168;
    }

    private function hasSafeCorsOrigins(): bool
    {
        $origins = config('cors.allowed_origins');

        return is_array($origins)
            && $origins !== []
            && collect($origins)->every(fn (mixed $origin): bool => $origin !== '*' && $this->isPublicHttpsUrl($origin));
    }

    private function hasValidViteManifest(): bool
    {
        $manifestPath = public_path('build/manifest.json');

        if (! is_file($manifestPath) || ! is_readable($manifestPath)) {
            return false;
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), true);

        if (! is_array($manifest) || $manifest === []) {
            return false;
        }

        foreach ($manifest as $entry) {
            if (! is_array($entry) || ! isset($entry['file']) || ! $this->assetExists($entry['file'])) {
                return false;
            }

            foreach ($entry['css'] ?? [] as $css) {
                if (! $this->assetExists($css)) {
                    return false;
                }
            }
        }

        return true;
    }

    private function assetExists(mixed $relativePath): bool
    {
        return is_string($relativePath)
            && $relativePath !== ''
            && ! str_contains($relativePath, '..')
            && is_file(public_path('build/'.$relativePath));
    }

    private function isConfiguredValue(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        $value = trim($value);

        return $value !== ''
            && ! preg_match('/[<>]/', $value)
            && ! preg_match('/^(change[-_ ]?me|replace[-_ ]?me|todo|example|test|null|secret)$/i', $value);
    }

    private function isConfiguredSecret(mixed $value): bool
    {
        return $this->isConfiguredValue($value) && strlen(trim((string) $value)) >= 16;
    }

    /**
     * @param  list<string>  $allowedHosts
     */
    private function hasHttpsHost(mixed $url, array $allowedHosts): bool
    {
        if (! is_string($url) || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        return strtolower((string) parse_url($url, PHP_URL_SCHEME)) === 'https'
            && in_array(strtolower((string) parse_url($url, PHP_URL_HOST)), $allowedHosts, true);
    }
}
