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

