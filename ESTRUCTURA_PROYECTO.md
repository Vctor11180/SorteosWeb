# 📁 Estructura Propuesta del Proyecto

## Estructura Actual vs Propuesta

### 📋 ESTRUCTURA ACTUAL:
```
php/
├── index.php
├── LandingPage.php
├── test.php, info.php
├── actualizar_contraseñas.php
├── corregir_hashes.php
├── administrador/
│   ├── [TODO mezclado: páginas, APIs, config, JS]
├── cliente/
│   ├── [TODO mezclado: páginas, APIs, config, JS, tests, uploads]
```

### ✨ ESTRUCTURA PROPUESTA:
```
php/
├── config/                      # Configuraciones compartidas
│   └── database.php             # Conexión BD compartida (unificar)
│
├── shared/                      # Utilidades compartidas
│   └── helpers/                 # Funciones helper comunes
│
├── public/                      # Archivos públicos/raíz
│   ├── index.php                # Punto de entrada principal
│   └── LandingPage.php          # Landing page
│
├── administrador/
│   ├── pages/                   # Páginas principales
│   │   ├── DashboardAdmnistrador.php
│   │   ├── GestionUsuariosAdministrador.php
│   │   ├── CrudGestionSorteo.php
│   │   ├── ValidacionPagosAdministrador.php
│   │   ├── AuditoriaAccionesAdmin.php
│   │   ├── InformesEstadisticasAdmin.php
│   │   ├── GeneradorGanadoresAdminstradores.php
│   │   ├── DetallesUsuarioAdmin.php
│   │   └── ZonaPeligroUsuario.php
│   │
│   ├── api/                     # APIs REST
│   │   ├── dashboard.php
│   │   ├── usuarios.php
│   │   ├── sorteos.php
│   │   ├── pagos.php
│   │   ├── ganadores.php
│   │   ├── crear_usuario.php
│   │   ├── exportar_usuarios.php
│   │   └── exportar_auditoria.php
│   │
│   ├── auth/                    # Autenticación
│   │   ├── Login.php
│   │   └── logout.php
│   │
│   ├── config/                  # Configuración específica admin
│   │   └── config.php
│   │
│   ├── assets/                  # Recursos estáticos
│   │   ├── js/
│   │   │   ├── admin-logout.js
│   │   │   └── custom-alerts.js
│   │   └── css/                 # Si hay CSS personalizado
│   │
│   └── includes/                # Helpers/funciones específicas admin
│
├── cliente/
│   ├── pages/                   # Páginas principales
│   │   ├── DashboardCliente.php
│   │   ├── AjustesPefilCliente.php
│   │   ├── MisBoletosCliente.php
│   │   ├── MisGanancias.php
│   │   ├── SeleccionBoletos.php
│   │   ├── SorteoClienteDetalles.php
│   │   ├── FinalizarPagoBoletos.php
│   │   ├── ListadoSorteosActivos.php
│   │   ├── ContactoSoporteCliente.php
│   │   ├── DetalleTicketSoporte.php
│   │   ├── MisTicketsSoporte.php
│   │   ├── FAQCliente.php
│   │   └── TerminosCondicionesCliente.php
│   │
│   ├── api/                     # APIs REST
│   │   ├── dashboard.php
│   │   ├── sorteos.php
│   │   ├── boletos.php
│   │   ├── transacciones.php
│   │   ├── ganancias.php
│   │   ├── soporte.php
│   │   ├── actualizar_perfil.php
│   │   ├── actualizar_password.php
│   │   ├── verificar_password.php
│   │   └── upload.php
│   │
│   ├── auth/                    # Autenticación
│   │   ├── InicioSesion.php
│   │   ├── CrearCuenta.php
│   │   └── logout.php
│   │
│   ├── config/                  # Configuración específica cliente
│   │   └── database.php         # Mover aquí o unificar con /config/
│   │
│   ├── assets/                  # Recursos estáticos
│   │   └── js/
│   │       ├── ajustes-perfil-cliente.js
│   │       ├── client-layout.js
│   │       └── custom-alerts.js
│   │
│   ├── includes/                # Helpers/funciones específicas cliente
│   │   ├── user-data.php
│   │   └── sorteos-data.php
│   │
│   └── uploads/                 # Archivos subidos por usuarios
│       └── comprobantes/
│
└── tools/                       # Scripts de utilidad, tests, debug
    ├── tests/                   # Scripts de prueba
    ├── debug/                   # Scripts de depuración
    ├── migrations/              # Scripts de migración/actualización
    └── maintenance/             # Scripts de mantenimiento
```

## 🎯 Ventajas de esta estructura:

1. ✅ **Separación clara** de páginas, APIs, auth, config
2. ✅ **Organización por funcionalidad** (pages/, api/, auth/)
3. ✅ **Recursos estáticos agrupados** (assets/)
4. ✅ **Tools separados** del código de producción
5. ✅ **Configuración centralizada** cuando sea posible
6. ✅ **Fácil mantenimiento** y navegación

## 📝 Plan de Migración:

1. Crear las nuevas carpetas
2. Mover archivos según la estructura propuesta
3. Actualizar rutas en `require_once` y `include`
4. Actualizar URLs en enlaces HTML
5. Probar que todo funciona
6. Eliminar archivos antiguos

## ❓ ¿Quieres que proceda con la reorganización?

---

## 🎨 Prototipos HTML Implementados

El proyecto incluye prototipos HTML estáticos basados en las páginas PHP, diseñados para demostración y prototipado rápido.

### 📁 Estructura de HTML Prototypes

```
html-prototypes/
├── index.html                      # Redirige a LandingPage.html
├── LandingPage.html                # Página principal de entrada
├── InicioSesion.html              # Página de inicio de sesión (idéntica a PHP)
├── CrearCuenta.html               # Página de creación de cuenta
│
├── cliente/                        # Páginas del cliente
│   ├── DashboardCliente.html      # Dashboard principal
│   ├── AjustesPefilCliente.html   # Ajustes de perfil
│   ├── MisBoletosCliente.html     # Mis boletos
│   ├── MisGanancias.html          # Mis ganancias
│   ├── ListadoSorteosActivos.html # Listado de sorteos
│   ├── SorteoClienteDetalles.html # Detalles de sorteo
│   ├── SeleccionBoletos.html      # Selección de boletos
│   ├── FinalizarPagoBoletos.html  # Finalizar pago
│   ├── ContactoSoporteCliente.html # Contacto y soporte
│   ├── FAQCliente.html            # Preguntas frecuentes
│   ├── TerminosCondicionesCliente.html # Términos y condiciones
│   └── js/                        # JavaScript del cliente
│       ├── client-layout.js       # Layout reutilizable con sidebar
│       ├── custom-alerts.js       # Alertas personalizadas
│       └── ajustes-perfil-cliente.js # Funcionalidades de perfil
│
└── administrador/                  # Páginas del administrador
    ├── DashboardAdmnistrador.html # Dashboard principal
    ├── GestionUsuariosAdministrador.html # Gestión de usuarios
    ├── CrudGestionSorteo.html     # CRUD de sorteos
    ├── ValidacionPagosAdministrador.html # Validación de pagos
    ├── AuditoriaAccionesAdmin.html # Auditoría de acciones
    ├── InformesEstadisticasAdmin.html # Informes y estadísticas
    ├── GeneradorGanadoresAdminstradores.html # Generador de ganadores
    ├── DetallesUsuarioAdmin.html  # Detalles de usuario
    └── ZonaPeligroUsuario.html    # Zona de peligro de usuario
```

### ✨ Características Implementadas

#### 🔐 Autenticación y Navegación
- **InicioSesion.html**: Idéntico a `InicioSesion.php`, incluye:
  - Formulario de inicio de sesión funcional
  - Integración con Google Sign-In
  - Botones de acceso directo a prototipos (Cliente/Admin)
  - Manejo de mensajes de error/éxito desde URL
  
- **Navegación Flujo**:
  - `index.html` → Redirige automáticamente a `LandingPage.html`
  - `LandingPage.html` → Página principal con enlaces a `InicioSesion.html`
  - Todos los enlaces internos configurados para funcionar correctamente

#### 👤 Cliente - Layout y Funcionalidades
- **ClientLayout Module** (`client-layout.js`):
  - Sidebar reutilizable con navegación
  - Menú móvil responsivo
  - Gestión de estado de usuario (localStorage/sessionStorage)
  - Sistema de logout funcional:
    - Limpieza de datos de sesión
    - Redirección a `LandingPage.html`
    - Confirmación antes de cerrar sesión
  
- **Páginas Cliente Implementadas**:
  - ✅ Dashboard con estadísticas y sorteos destacados
  - ✅ Ajustes de perfil con actualización de datos
  - ✅ Gestión de boletos con vista detallada
  - ✅ Historial de ganancias
  - ✅ Exploración de sorteos activos
  - ✅ Detalles y compra de boletos
  - ✅ Sistema de soporte y FAQ

#### 👨‍💼 Administrador - Dashboard
- Dashboard completo con métricas y gráficos
- Gestión de usuarios, sorteos y pagos
- Sistemas de auditoría y generación de ganadores

### 🔧 Funcionalidades Técnicas

#### Logout Implementado
- **En todas las páginas del cliente**: El botón de logout funciona correctamente
  - Event listeners explícitos en cada página
  - Integración con `ClientLayout.handleLogout()`
  - Confirmación con `customConfirm` o `confirm` nativo
  - Limpieza completa de datos de sesión

#### JavaScript Modular
- **client-layout.js**: Módulo reutilizable que maneja:
  - Renderizado de sidebar y menú móvil
  - Actualización de información de usuario
  - Navegación activa automática
  - Event listeners centralizados

#### Integración PHP/HTML
- Los prototipos HTML mantienen la misma estructura visual que las páginas PHP
- Formularios pueden enviar datos al backend PHP cuando esté disponible
- Fallback automático a prototipos HTML si PHP no está disponible

### 📦 GitHub Pages

Los prototipos están configurados para desplegarse en GitHub Pages:

- **Carpeta `docs/`**: Contiene copias de `html-prototypes/` para GitHub Pages
- **Script de actualización**: `actualizar-docs.ps1` copia automáticamente los cambios
- **Archivo `.nojekyll`**: Asegura que GitHub Pages no procese con Jekyll

Ver [README_GITHUB_PAGES.md](./README_GITHUB_PAGES.md) para más detalles sobre el despliegue.

### 🚀 Uso de los Prototipos

1. **Desarrollo Local**:
   - Abrir directamente los archivos HTML en un navegador
   - O servir con un servidor local (ej: `php -S localhost:8000`)

2. **Actualizar para GitHub Pages**:
   ```powershell
   .\actualizar-docs.ps1
   git add docs/
   git commit -m "Actualizar prototipos HTML"
   git push origin main
   ```

3. **Flujo de Demostración**:
   - Inicia en `LandingPage.html`
   - Navega a `InicioSesion.html`
   - Accede directamente a dashboards usando los botones de demo
   - Explora todas las funcionalidades del cliente

### 📝 Notas Importantes

- Los prototipos HTML son **estáticos** y no requieren backend
- Los datos se simulan con valores por defecto o localStorage
- El diseño visual es **idéntico** a las páginas PHP
- Todos los enlaces están configurados para funcionar en GitHub Pages
- El sistema de logout está completamente funcional en todas las páginas cliente

