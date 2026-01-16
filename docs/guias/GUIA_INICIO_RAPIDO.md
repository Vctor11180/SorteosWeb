# 🚀 Guía de Inicio Rápido - SorteosWeb

## 📍 Ubicación del Proyecto

Tu proyecto está en: `C:\xampp\htdocs\SorteosWeb-conLoDeJorge\SorteosWeb-main\php\`

## ✅ Paso 1: Verificar que XAMPP esté funcionando

1. Abre el **Panel de Control de XAMPP**
2. Asegúrate de que estos servicios estén **iniciados** (botón verde):
   - ✅ **Apache** (puerto 80)
   - ✅ **MySQL** (puerto 3306)

## ✅ Paso 2: Probar que PHP funciona

Abre tu navegador y accede a:

```
http://localhost/SorteosWeb-conLoDeJorge/SorteosWeb-main/php/test.php
```

**Si ves un mensaje de éxito**, PHP está funcionando. Si no:
- Verifica que Apache esté iniciado
- Verifica que la ruta sea correcta
- Revisa los errores en el navegador (F12)

## ✅ Paso 3: Crear la Base de Datos

1. Abre: `http://localhost/phpmyadmin`
2. Haz clic en **"Nueva"** o **"New"** en el panel izquierdo
3. Nombre de la base de datos: `sorteos_schema`
4. Intercalación: `utf8mb4_unicode_ci`
5. Haz clic en **"Crear"**

## ✅ Paso 4: Importar el Esquema SQL

1. En phpMyAdmin, selecciona la base de datos `sorteos_schema`
2. Ve a la pestaña **"Importar"**
3. Haz clic en **"Elegir archivo"**
4. Selecciona: `C:\xampp\htdocs\SorteosWeb-conLoDeJorge\SorteosWeb-main\database\schema\sorteos_schema.sql`
5. Haz clic en **"Continuar"** al final
6. Espera el mensaje de éxito

## ✅ Paso 5: Verificar la Conexión a la Base de Datos

Accede a:

```
http://localhost/SorteosWeb-conLoDeJorge/SorteosWeb-main/test_conexion.php
```

**Si ves ✅ Conexión exitosa**, todo está bien configurado.

## ✅ Paso 6: Acceder al Proyecto

### Opción A: Página Principal
```
http://localhost/SorteosWeb-conLoDeJorge/SorteosWeb-main/php/index.php
```

O simplemente:
```
http://localhost/SorteosWeb-conLoDeJorge/SorteosWeb-main/php/
```

### Opción B: Login de Cliente
```
http://localhost/SorteosWeb-conLoDeJorge/SorteosWeb-main/php/cliente/InicioSesion.php
```

### Opción C: Dashboard de Administrador
```
http://localhost/SorteosWeb-conLoDeJorge/SorteosWeb-main/php/administrador/DashboardAdmnistrador.php
```

## 🔧 Solución de Problemas

### ❌ Error 404 - Página no encontrada

**Problema:** La URL no es correcta o el archivo no existe.

**Solución:**
1. Verifica que la ruta sea exactamente: `http://localhost/SorteosWeb-conLoDeJorge/SorteosWeb-main/php/`
2. Verifica que los archivos estén en: `C:\xampp\htdocs\SorteosWeb-conLoDeJorge\SorteosWeb-main\php\`

### ❌ Error de conexión a la base de datos

**Problema:** La base de datos no existe o las credenciales son incorrectas.

**Solución:**
1. Verifica que MySQL esté iniciado en XAMPP
2. Verifica que la base de datos `sorteos_schema` exista
3. Verifica que el usuario `root` no tenga contraseña (o actualiza `database.php`)

### ❌ Página en blanco

**Problema:** Error de PHP que no se muestra.

**Solución:**
1. Abre `php/info.php` para ver si PHP funciona
2. Revisa los logs de Apache en: `C:\xampp\apache\logs\error.log`
3. Activa la visualización de errores en PHP

### ❌ Apache no inicia

**Problema:** Puerto 80 ocupado.

**Solución:**
1. Cierra programas que usen el puerto 80 (Skype, IIS, etc.)
2. O cambia el puerto de Apache en XAMPP

## 📝 Notas Importantes

- **Ruta base del proyecto:** `C:\xampp\htdocs\SorteosWeb-conLoDeJorge\SorteosWeb-main\`
- **Carpeta PHP:** `php/`
- **Base de datos:** `sorteos_schema`
- **Usuario MySQL:** `root` (sin contraseña por defecto)

## 🎯 URLs Principales

| Página | URL |
|--------|-----|
| Inicio | `http://localhost/SorteosWeb-conLoDeJorge/SorteosWeb-main/php/` |
| Login Cliente | `http://localhost/SorteosWeb-conLoDeJorge/SorteosWeb-main/php/cliente/InicioSesion.php` |
| Dashboard Cliente | `http://localhost/SorteosWeb-conLoDeJorge/SorteosWeb-main/php/cliente/DashboardCliente.php` |
| Dashboard Admin | `http://localhost/SorteosWeb-conLoDeJorge/SorteosWeb-main/php/administrador/DashboardAdmnistrador.php` |
| Test PHP | `http://localhost/SorteosWeb-conLoDeJorge/SorteosWeb-main/php/test.php` |
| Test BD | `http://localhost/SorteosWeb-conLoDeJorge/SorteosWeb-main/test_conexion.php` |

¡Listo! Tu proyecto debería estar funcionando. 🎉
