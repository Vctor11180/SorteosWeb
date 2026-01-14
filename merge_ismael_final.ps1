# Script para merge de feature/ismael sin pager
# Ejecutar en una nueva terminal de PowerShell

# Deshabilitar pager completamente
$env:GIT_PAGER = ""
$env:PAGER = ""

# Configurar git para no usar pager
git config --global core.pager ""
git config --local core.pager ""

Write-Host "Haciendo fetch de feature/ismael..." -ForegroundColor Cyan
git fetch origin feature/ismael

Write-Host "`nHaciendo merge..." -ForegroundColor Cyan
$mergeOutput = git merge origin/feature/ismael --no-edit 2>&1
Write-Host $mergeOutput

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n¡Merge completado exitosamente!" -ForegroundColor Green
} else {
    Write-Host "`nHubo conflictos o errores. Código: $LASTEXITCODE" -ForegroundColor Yellow
}

Write-Host "`nEstado del repositorio:" -ForegroundColor Cyan
git status --short

