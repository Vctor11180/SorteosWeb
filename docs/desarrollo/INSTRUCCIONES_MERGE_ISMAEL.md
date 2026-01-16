# Instrucciones para traer cambios de feature/ismael

## Problema actual
Hay un proceso de Git (pager/less) bloqueado que está interceptando los comandos.

## Solución

### Opción 1: Cerrar procesos bloqueados y ejecutar script

1. **Cierra TODAS las ventanas de terminal/PowerShell abiertas**
2. **Cierra cualquier proceso de Git/less que esté corriendo** (puedes usar el Administrador de Tareas)
3. **Abre una NUEVA terminal de PowerShell**
4. **Navega al directorio del proyecto:**
   ```powershell
   cd "D:\PROYECTOS\Pagina Sorteos"
   ```
5. **Ejecuta el script:**
   ```powershell
   .\merge_ismael_final.ps1
   ```

### Opción 2: Comandos manuales

Si prefieres hacerlo manualmente, ejecuta estos comandos en una **nueva terminal**:

```powershell
cd "D:\PROYECTOS\Pagina Sorteos"

# Deshabilitar pager
git config --global core.pager ""
git config --local core.pager ""

# Hacer fetch
git fetch origin feature/ismael

# Hacer merge
git merge origin/feature/ismael --no-edit

# Verificar resultado
git status
```

### Opción 3: Usar Git GUI

Si los comandos de línea siguen fallando, puedes usar Git GUI o GitHub Desktop para hacer el merge visualmente.

## Archivos que se traerán de feature/ismael

Según el diff anterior, la rama `feature/ismael` incluye:
- `CONFIGURACION_XAMPP.md`
- `GUIA_INICIO_RAPIDO.md`
- Varios archivos HTML en la raíz
- Archivos JavaScript adicionales
- Posibles actualizaciones a archivos existentes

## Después del merge

Una vez completado el merge:
1. Revisa los conflictos si los hay
2. Resuelve cualquier conflicto manualmente
3. Haz commit de los cambios
4. Sube los cambios a GitHub con `git push origin main`

