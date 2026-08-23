$ErrorActionPreference = 'Stop'

$projectRoot = $PSScriptRoot
$archive = Join-Path $projectRoot '.codex-local\m8-production-update.tgz'
$remoteArchive = '/home/u291776795/m8-production-update.tgz'
$sshTarget = 'u291776795@217.21.76.75'
$files = @(
    'routes/api.php',
    'routes/web.php',
    'bootstrap/app.php',
    'app/Http/Controllers/Api/V1/AdminController.php',
    'app/Http/Controllers/Api/V1/AdminOperationsController.php',
    'app/Http/Controllers/Api/V1/AuthController.php',
    'app/Http/Resources/Api/V1/UserResource.php',
    'database/seeders/CategorySeeder.php'
)

Set-Location $projectRoot
foreach ($file in $files) {
    if (-not (Test-Path -LiteralPath (Join-Path $projectRoot $file))) {
        throw "Falta el archivo requerido: $file"
    }
}

try {
    tar.exe -czf $archive @files
    if ($LASTEXITCODE -ne 0) { throw 'No se pudo crear el paquete de despliegue.' }

    Write-Host ''
    Write-Host '1/2 Escribe tu contrasena SSH de Hostinger para subir la actualizacion.' -ForegroundColor Cyan
    scp.exe -P 65002 $archive "${sshTarget}:$remoteArchive"
    if ($LASTEXITCODE -ne 0) { throw 'No se pudo subir el paquete.' }

    Write-Host ''
    Write-Host '2/2 Escribe nuevamente tu contrasena SSH para instalar y verificar.' -ForegroundColor Cyan
    $remoteCommand = "set -e; cd /home/u291776795/chambapp; tar -xzf $remoteArchive; php artisan optimize:clear; php artisan db:seed --class=CategorySeeder --force; php artisan route:list --path=api/v1/admin; rm -f $remoteArchive"
    ssh.exe -p 65002 $sshTarget $remoteCommand
    if ($LASTEXITCODE -ne 0) { throw 'Hostinger no pudo instalar o verificar la actualizacion.' }

    Write-Host ''
    Write-Host 'ACTUALIZACION M8 INSTALADA CORRECTAMENTE.' -ForegroundColor Green
}
finally {
    if (Test-Path -LiteralPath $archive) {
        Remove-Item -LiteralPath $archive -Force
    }
}

Read-Host 'Presiona Enter para cerrar esta ventana'
