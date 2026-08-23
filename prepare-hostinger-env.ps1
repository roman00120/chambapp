$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$localConfigDirectory = Join-Path $projectRoot 'local-config'
$downloadsDirectory = Join-Path ([Environment]::GetFolderPath('UserProfile')) 'Downloads'
$outputPath = Join-Path $localConfigDirectory 'hostinger.env'

$googleConfigFile = Get-ChildItem -LiteralPath $downloadsDirectory -Filter 'client_secret*.json' -File |
    Sort-Object LastWriteTime -Descending |
    Select-Object -First 1

if (-not $googleConfigFile) {
    throw 'No se encontró un archivo client_secret*.json en Descargas.'
}

$googleConfig = Get-Content -LiteralPath $googleConfigFile.FullName -Raw | ConvertFrom-Json
$googleClient = $googleConfig.web
$productionCallback = 'https://chambapp.com.mx/auth/google/callback'
$productionOrigin = 'https://chambapp.com.mx'

if (-not $googleClient.client_id -or -not $googleClient.client_secret) {
    throw 'El JSON de Google no contiene un cliente web válido.'
}

if ($productionCallback -notin $googleClient.redirect_uris -or $productionOrigin -notin $googleClient.javascript_origins) {
    throw 'El cliente de Google no contiene el origen y callback de producción de Chambapp.'
}

function ConvertTo-DotEnvSingleQuoted([string]$value) {
    $escaped = $value.Replace('\', '\\').Replace("'", "\'")

    return "'$escaped'"
}

$databasePasswordSecure = Read-Host 'Contraseña MySQL de Hostinger' -AsSecureString
$databasePasswordPointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($databasePasswordSecure)

try {
    $databasePassword = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($databasePasswordPointer)

    if ([string]::IsNullOrWhiteSpace($databasePassword)) {
        throw 'La contraseña MySQL no puede estar vacía.'
    }

    $databasePasswordEnv = ConvertTo-DotEnvSingleQuoted $databasePassword
    $googleClientIdEnv = ConvertTo-DotEnvSingleQuoted ([string] $googleClient.client_id)
    $googleClientSecretEnv = ConvertTo-DotEnvSingleQuoted ([string] $googleClient.client_secret)

    $environment = @"
APP_NAME="Chambapp"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://chambapp.com.mx

APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_MX
APP_TIMEZONE=America/Mexico_City

APP_MAINTENANCE_DRIVER=file
BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=daily
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u291776795_chambapp
DB_USERNAME=u291776795_chambapp
DB_PASSWORD=$databasePasswordEnv

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=public
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=log
MAIL_FROM_ADDRESS=soporte@chambapp.com.mx
MAIL_FROM_NAME="`${APP_NAME}"

VITE_APP_NAME="`${APP_NAME}"

CHAMBAPP_PLATFORM_FEE_PERCENT=15
CHAMBAPP_PAYMENT_CURRENCY=MXN
CHAMBAPP_PAYMENT_TIMEOUT=10
CHAMBAPP_PAYMENT_PREFERENCE_HOURS=24
CHAMBAPP_IMMEDIATE_TIMEOUT=5
CHAMBAPP_INVITATION_TIMEOUT=3
CHAMBAPP_LOCATION_FRESHNESS=30
CHAMBAPP_CORS_ALLOWED_ORIGINS=https://chambapp.com.mx

MERCADOPAGO_API_URL=https://api.mercadopago.com
MERCADOPAGO_AUTH_URL=https://auth.mercadopago.com.mx/authorization
MERCADOPAGO_CLIENT_ID=
MERCADOPAGO_CLIENT_SECRET=
MERCADOPAGO_WEBHOOK_SECRET=
MERCADOPAGO_ACCESS_TOKEN=
MERCADOPAGO_USER_ID=

GOOGLE_CLIENT_ID=$googleClientIdEnv
GOOGLE_CLIENT_SECRET=$googleClientSecretEnv
GOOGLE_REDIRECT_URI=$productionCallback
"@

    New-Item -ItemType Directory -Path $localConfigDirectory -Force | Out-Null
    [IO.File]::WriteAllText($outputPath, $environment, [Text.UTF8Encoding]::new($false))

    Write-Host ''
    Write-Host "Archivo preparado: $outputPath" -ForegroundColor Green
    Write-Host 'No lo subas a GitHub. Súbelo al directorio privado de Hostinger y renómbralo como .env.' -ForegroundColor Yellow
} finally {
    if ($databasePasswordPointer -ne [IntPtr]::Zero) {
        [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($databasePasswordPointer)
    }

    $databasePassword = $null
}
