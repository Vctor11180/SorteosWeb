# Script para traer archivos de feature/ismael sin pager
$ErrorActionPreference = "Stop"

# Deshabilitar pager
$env:GIT_PAGER = ""
$env:PAGER = ""

Write-Host "Trayendo archivos de feature/ismael..." -ForegroundColor Cyan

# Archivos nuevos a traer
$archivos = @(
    "CONFIGURACION_XAMPP.md",
    "GUIA_INICIO_RAPIDO.md",
    "php/.htaccess",
    "php/info.php",
    "php/test.php",
    "test_conexion.php"
)

foreach ($archivo in $archivos) {
    try {
        Write-Host "Trayendo: $archivo" -ForegroundColor Yellow
        $contenido = git show "origin/feature/ismael:$archivo" 2>&1 | Out-String
        if ($LASTEXITCODE -eq 0 -and $contenido -notmatch "fatal") {
            # Crear directorio si no existe
            $directorio = Split-Path $archivo -Parent
            if ($directorio -and -not (Test-Path $directorio)) {
                New-Item -ItemType Directory -Path $directorio -Force | Out-Null
            }
            # Escribir archivo
            $contenido | Out-File -FilePath $archivo -Encoding UTF8 -NoNewline
            Write-Host "  ✓ Creado: $archivo" -ForegroundColor Green
        } else {
            Write-Host "  ✗ Error al traer: $archivo" -ForegroundColor Red
        }
    } catch {
        Write-Host "  ✗ Error: $_" -ForegroundColor Red
    }
}

# Traer archivos JavaScript nuevos
$jsFiles = @(
    "php/cliente/js/ajustes-perfil-cliente.js",
    "php/cliente/js/client-layout.js",
    "php/cliente/js/custom-alerts.js"
)

foreach ($archivo in $jsFiles) {
    try {
        Write-Host "Trayendo: $archivo" -ForegroundColor Yellow
        $contenido = git show "origin/feature/ismael:$archivo" 2>&1 | Out-String
        if ($LASTEXITCODE -eq 0 -and $contenido -notmatch "fatal") {
            $directorio = Split-Path $archivo -Parent
            if ($directorio -and -not (Test-Path $directorio)) {
                New-Item -ItemType Directory -Path $directorio -Force | Out-Null
            }
            $contenido | Out-File -FilePath $archivo -Encoding UTF8 -NoNewline
            Write-Host "  ✓ Creado: $archivo" -ForegroundColor Green
        }
    } catch {
        Write-Host "  ✗ Error: $_" -ForegroundColor Red
    }
}

Write-Host "`nActualizando archivos modificados..." -ForegroundColor Cyan

# Actualizar archivos modificados usando checkout
git checkout origin/feature/ismael -- php/cliente/InicioSesion.php php/cliente/config/database.php 2>&1 | Out-Null

Write-Host "`n¡Proceso completado!" -ForegroundColor Green
Write-Host "`nArchivos listos para commit." -ForegroundColor Cyan

