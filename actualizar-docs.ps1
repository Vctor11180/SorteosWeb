# Script para actualizar la carpeta docs con los prototipos HTML
# Ejecutar: .\actualizar-docs.ps1

Write-Host "🔄 Actualizando carpeta docs con los prototipos HTML..." -ForegroundColor Cyan

# Limpiar carpeta docs (excepto .nojekyll)
if (Test-Path "docs") {
    Get-ChildItem -Path "docs" -Exclude ".nojekyll" | Remove-Item -Recurse -Force
    Write-Host "✅ Carpeta docs limpiada" -ForegroundColor Green
} else {
    New-Item -ItemType Directory -Path "docs" | Out-Null
    Write-Host "✅ Carpeta docs creada" -ForegroundColor Green
}

# Copiar archivos de html-prototypes a docs
Copy-Item -Path "html-prototypes\*" -Destination "docs\" -Recurse -Force
Write-Host "✅ Archivos copiados a docs/" -ForegroundColor Green

# Asegurar que .nojekyll existe
if (-not (Test-Path "docs\.nojekyll")) {
    New-Item -ItemType File -Path "docs\.nojekyll" | Out-Null
    Write-Host "✅ Archivo .nojekyll creado" -ForegroundColor Green
}

Write-Host "`n✨ ¡Actualización completada!`n" -ForegroundColor Green
Write-Host "Ahora puedes hacer commit y push:" -ForegroundColor Yellow
Write-Host "  git add docs/" -ForegroundColor White
Write-Host "  git commit -m 'Actualizar prototipos HTML para GitHub Pages'" -ForegroundColor White
Write-Host "  git push origin main" -ForegroundColor White

