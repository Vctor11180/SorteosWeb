# Configuración de GitHub Pages

Este repositorio está configurado para mostrar los prototipos HTML en GitHub Pages.

## 📋 Pasos para activar GitHub Pages

1. **Ve a la configuración del repositorio en GitHub:**
   - Navega a tu repositorio en GitHub
   - Haz clic en **Settings** (Configuración)
   - En el menú lateral, busca **Pages**

2. **Configura la fuente:**
   - En **Source** (Fuente), selecciona:
     - **Branch:** `main` (o la rama que uses)
     - **Folder:** `/docs`
   - Haz clic en **Save** (Guardar)

3. **Espera a que se publique:**
   - GitHub procesará tu sitio (puede tardar unos minutos)
   - Verás un mensaje verde indicando que tu sitio está publicado
   - La URL será: `https://tu-usuario.github.io/SorteosWeb/`

## 🎯 Estructura del sitio

El sitio comenzará desde `LandingPage.html` (gracias al redirect en `index.html`).

**Flujo de navegación:**
- `index.html` → Redirige a `LandingPage.html`
- `LandingPage.html` → Página principal
- `InicioSesion.html` → Página de inicio de sesión
- `cliente/DashboardCliente.html` → Dashboard del cliente
- `administrador/DashboardAdmnistrador.html` → Dashboard del administrador

## 📝 Notas importantes

- El archivo `.nojekyll` en la carpeta `docs` asegura que GitHub Pages no procese los archivos con Jekyll
- Todos los enlaces están configurados para funcionar correctamente en GitHub Pages
- Los archivos se actualizan automáticamente cuando haces push a la rama `main`

## 🔄 Actualizar el sitio

Cada vez que hagas cambios en `html-prototypes/`, necesitas copiarlos a `docs/`:

```bash
# En Windows (PowerShell)
Copy-Item -Path "html-prototypes\*" -Destination "docs\" -Recurse -Force

# Luego hacer commit y push
git add docs/
git commit -m "Actualizar prototipos HTML"
git push origin main
```

## 🌐 URL del sitio

Una vez configurado, tu sitio estará disponible en:
```
https://Vctor11180.github.io/SorteosWeb/
```

(Reemplaza `Vctor11180` con tu nombre de usuario de GitHub si es diferente)

