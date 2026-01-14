# Merge directo de feature/ismael sin pager
# Ejecutar en una NUEVA terminal de PowerShell

# Matar cualquier proceso de less/git pager
Get-Process | Where-Object {$_.ProcessName -like "*less*" -or $_.ProcessName -like "*git*"} | Stop-Process -Force -ErrorAction SilentlyContinue

# Deshabilitar pager completamente
$env:GIT_PAGER = "cat"
$env:PAGER = "cat"
git config --global core.pager "cat"
git config --local core.pager "cat"

Write-Host "=== MERGE DE FEATURE/ISMAEL ===" -ForegroundColor Cyan
Write-Host ""

# Fetch
Write-Host "1. Haciendo fetch..." -ForegroundColor Yellow
git fetch origin feature/ismael 2>&1 | Out-Null
if ($LASTEXITCODE -ne 0) {
    Write-Host "   ERROR en fetch" -ForegroundColor Red
    exit 1
}
Write-Host "   ✓ Fetch completado" -ForegroundColor Green

# Merge con estrategia
Write-Host "2. Haciendo merge..." -ForegroundColor Yellow
$mergeResult = git merge origin/feature/ismael --no-edit --no-verify 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "   ✓ Merge completado exitosamente" -ForegroundColor Green
} elseif ($LASTEXITCODE -eq 1) {
    Write-Host "   ⚠ Merge completado con conflictos o advertencias" -ForegroundColor Yellow
    Write-Host $mergeResult
} else {
    Write-Host "   ✗ Error en merge" -ForegroundColor Red
    Write-Host $mergeResult
    exit 1
}

# Verificar archivos nuevos
Write-Host "3. Verificando archivos..." -ForegroundColor Yellow
$archivosEsperados = @(
    "CONFIGURACION_XAMPP.md",
    "GUIA_INICIO_RAPIDO.md",
    "php/.htaccess"
)

foreach ($archivo in $archivosEsperados) {
    if (Test-Path $archivo) {
        Write-Host "   ✓ $archivo" -ForegroundColor Green
    } else {
        Write-Host "   ✗ Faltante: $archivo" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "=== ESTADO FINAL ===" -ForegroundColor Cyan
git status --short

Write-Host ""
Write-Host "Si hay archivos sin agregar, ejecuta:" -ForegroundColor Yellow
Write-Host "  git add -A" -ForegroundColor White
Write-Host "  git commit -m 'Merge feature/ismael'" -ForegroundColor White

