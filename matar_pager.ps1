# Script para identificar y matar procesos de less/git bloqueados
# EJECUTAR EN UNA NUEVA TERMINAL DE POWERSHELL

Write-Host "=== IDENTIFICANDO PROCESOS BLOQUEADOS ===" -ForegroundColor Cyan
Write-Host ""

# Buscar procesos relacionados con less, git, bash
Write-Host "1. Buscando procesos de 'less'..." -ForegroundColor Yellow
$lessProcesses = Get-Process | Where-Object {$_.ProcessName -like "*less*"}
if ($lessProcesses) {
    Write-Host "   Procesos 'less' encontrados:" -ForegroundColor Red
    $lessProcesses | Format-Table ProcessName, Id, StartTime, Path -AutoSize
} else {
    Write-Host "   ✓ No se encontraron procesos 'less'" -ForegroundColor Green
}

Write-Host ""
Write-Host "2. Buscando procesos de 'git'..." -ForegroundColor Yellow
$gitProcesses = Get-Process | Where-Object {$_.ProcessName -like "*git*"}
if ($gitProcesses) {
    Write-Host "   Procesos 'git' encontrados:" -ForegroundColor Yellow
    $gitProcesses | Format-Table ProcessName, Id, StartTime, Path -AutoSize
} else {
    Write-Host "   ✓ No se encontraron procesos 'git' activos" -ForegroundColor Green
}

Write-Host ""
Write-Host "3. Buscando procesos de 'bash' o 'sh'..." -ForegroundColor Yellow
$bashProcesses = Get-Process | Where-Object {$_.ProcessName -like "*bash*" -or $_.ProcessName -like "*sh*"}
if ($bashProcesses) {
    Write-Host "   Procesos bash/sh encontrados:" -ForegroundColor Yellow
    $bashProcesses | Format-Table ProcessName, Id, StartTime, Path -AutoSize
} else {
    Write-Host "   ✓ No se encontraron procesos bash/sh" -ForegroundColor Green
}

Write-Host ""
Write-Host "4. Buscando procesos de PowerShell con ventanas relacionadas..." -ForegroundColor Yellow
$psProcesses = Get-Process powershell | Where-Object {$_.MainWindowTitle -ne ""}
if ($psProcesses) {
    Write-Host "   Procesos PowerShell con ventanas:" -ForegroundColor Yellow
    $psProcesses | Select-Object Id, MainWindowTitle | Format-Table -AutoSize
}

Write-Host ""
Write-Host "=== INTENTANDO MATAR PROCESOS BLOQUEADOS ===" -ForegroundColor Cyan
Write-Host ""

# Intentar matar procesos de less
if ($lessProcesses) {
    Write-Host "Matando procesos 'less'..." -ForegroundColor Yellow
    foreach ($proc in $lessProcesses) {
        try {
            Stop-Process -Id $proc.Id -Force -ErrorAction Stop
            Write-Host "   ✓ Proceso $($proc.ProcessName) (ID: $($proc.Id)) terminado" -ForegroundColor Green
        } catch {
            Write-Host "   ✗ Error al terminar proceso $($proc.ProcessName) (ID: $($proc.Id)): $_" -ForegroundColor Red
        }
    }
}

# Intentar matar procesos de bash/sh que puedan estar bloqueados
if ($bashProcesses) {
    Write-Host "Matando procesos bash/sh..." -ForegroundColor Yellow
    foreach ($proc in $bashProcesses) {
        try {
            Stop-Process -Id $proc.Id -Force -ErrorAction Stop
            Write-Host "   ✓ Proceso $($proc.ProcessName) (ID: $($proc.Id)) terminado" -ForegroundColor Green
        } catch {
            Write-Host "   ✗ Error al terminar proceso $($proc.ProcessName) (ID: $($proc.Id)): $_" -ForegroundColor Red
        }
    }
}

Write-Host ""
Write-Host "=== CONFIGURANDO GIT PARA EVITAR PAGER ===" -ForegroundColor Cyan
Write-Host ""

# Deshabilitar pager en Git
git config --global core.pager ""
git config --local core.pager ""
$env:GIT_PAGER = ""
$env:PAGER = ""

Write-Host "✓ Pager deshabilitado en Git" -ForegroundColor Green

Write-Host ""
Write-Host "=== LISTO ===" -ForegroundColor Green
Write-Host "Ahora puedes ejecutar:" -ForegroundColor Yellow
Write-Host "  git fetch origin feature/ismael" -ForegroundColor White
Write-Host "  git merge origin/feature/ismael --no-edit" -ForegroundColor White

