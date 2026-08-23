@echo off
setlocal

set "PROJECT_ROOT=%~dp0"
set "LOCAL_CONFIG=%PROJECT_ROOT%local-config"
set "CA_BUNDLE=%LOCAL_CONFIG%\cacert.pem"

if not exist "%LOCAL_CONFIG%" mkdir "%LOCAL_CONFIG%"

if not exist "%CA_BUNDLE%" (
    echo Descargando certificados raiz para PHP...
    curl.exe -fsSL https://curl.se/ca/cacert.pem -o "%CA_BUNDLE%"
    if errorlevel 1 (
        echo No se pudo descargar el paquete de certificados.
        exit /b 1
    )
)

cd /d "%PROJECT_ROOT%public"

php -d "curl.cainfo=%CA_BUNDLE%" -d "openssl.cafile=%CA_BUNDLE%" -S 0.0.0.0:8000 "%PROJECT_ROOT%vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php"
