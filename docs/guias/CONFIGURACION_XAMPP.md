# Guía de Configuración de Base de Datos con XAMPP

Esta guía te ayudará a configurar la conexión entre tu código PHP y la base de datos MySQL en XAMPP.

## Requisitos Previos

- XAMPP instalado y funcionando
- Servicios Apache y MySQL iniciados en el Panel de Control de XAMPP

## ⚠️ IMPORTANTE: Ubicación del Proyecto

**XAMPP solo puede ejecutar archivos PHP que estén dentro de la carpeta `htdocs`.**

Tu proyecto debe estar en: `C:\xampp\htdocs\SorteosWeb-main\`

Si tu proyecto está en otra ubicación (como `C:\codes\...`), tienes dos opciones:

### Opción A: Copiar el proyecto a htdocs (Recomendado)
1. Copia toda la carpeta `SorteosWeb-main` a `C:\xampp\htdocs\`
2. Accede a: `http://localhost/SorteosWeb-main/`

### Opción B: Crear un enlace simbólico
```powershell
# Ejecuta en PowerShell como Administrador
New-Item -ItemType SymbolicLink -Path "C:\xampp\htdocs\SorteosWeb-main" -Target "C:\codes\SorteosWeb-conLoDeJorge\SorteosWeb-main"
```

## Paso 1: Iniciar los Servicios de XAMPP

1. Abre el **Panel de Control de XAMPP**
2. Inicia los servicios:
   - **Apache** (para ejecutar PHP)
   - **MySQL** (para la base de datos)

## Paso 2: Crear la Base de Datos

1. Abre tu navegador y ve a: `http://localhost/phpmyadmin`
2. En el panel izquierdo, haz clic en **"Nueva"** o **"New"**
3. En el campo **"Nombre de la base de datos"**, escribe: `sorteos_schema`
4. Selecciona la intercalación: `utf8mb4_unicode_ci` (recomendado)
5. Haz clic en **"Crear"** o **"Create"**

## Paso 3: Importar el Esquema de la Base de Datos

1. En phpMyAdmin, selecciona la base de datos `sorteos_schema` que acabas de crear
2. Ve a la pestaña **"Importar"** o **"Import"**
3. Haz clic en **"Elegir archivo"** o **"Choose File"**
4. Selecciona el archivo: `database/schema/sorteos_schema.sql` (ubicado en la carpeta database/schema del proyecto)
5. Haz clic en **"Continuar"** o **"Go"** al final de la página
6. Espera a que se complete la importación. Deberías ver un mensaje de éxito.

## Paso 4: Verificar la Configuración de Conexión

Los archivos de configuración ya están preparados con los valores por defecto de XAMPP:

### Archivos de Configuración:

1. **Cliente**: `php/cliente/config/database.php`
2. **Administrador**: `php/administrador/config.php`

### Configuración Actual (por defecto de XAMPP):

```php
DB_HOST = 'localhost'
DB_USER = 'root'
DB_PASS = ''  (sin contraseña)
DB_NAME = 'sorteos_schema'
```

## Paso 5: Ajustar Credenciales (si es necesario)

Si has cambiado la contraseña del usuario `root` en MySQL, debes actualizar los archivos de configuración:

### Para el Cliente:
Edita: `php/cliente/config/database.php`

```php
define('DB_USER', 'root');        // Tu usuario de MySQL
define('DB_PASS', 'tu_password'); // Tu contraseña (si la tienes)
```

### Para el Administrador:
Edita: `php/administrador/config.php`

```php
define('DB_USER', 'root');        // Tu usuario de MySQL
define('DB_PASS', 'tu_password'); // Tu contraseña (si la tienes)
```

## Paso 6: Verificar la Conexión

### Opción 1: Probar desde el navegador
1. Coloca tu proyecto en la carpeta `htdocs` de XAMPP:
   - Ruta típica: `C:\xampp\htdocs\SorteosWeb-main\`
2. Abre tu navegador y accede a alguna página PHP del proyecto
3. Si hay un error de conexión, verás un mensaje específico

### Opción 2: Crear un archivo de prueba
Crea un archivo `test_conexion.php` en la raíz del proyecto:

```php
<?php
require_once 'php/cliente/config/database.php';

try {
    $db = getDB();
    echo "✅ Conexión exitosa a la base de datos!<br>";
    echo "Base de datos: sorteos_schema<br>";
    
    // Probar una consulta simple
    $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios");
    $result = $stmt->fetch();
    echo "Total de usuarios: " . $result['total'];
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage();
}
?>
```

Luego accede a: `http://localhost/SorteosWeb-main/test_conexion.php`

## Solución de Problemas Comunes

### Error: "Access denied for user 'root'@'localhost'"
- **Solución**: Verifica que la contraseña en los archivos de configuración coincida con la de MySQL, o deja `DB_PASS` vacío si no tienes contraseña.

### Error: "Unknown database 'sorteos_schema'"
- **Solución**: Asegúrate de haber creado la base de datos y haber importado el esquema SQL correctamente.

### Error: "Connection refused" o "Can't connect to MySQL server"
- **Solución**: Verifica que el servicio MySQL esté iniciado en el Panel de Control de XAMPP.

### Error: "Table doesn't exist"
- **Solución**: Asegúrate de haber importado correctamente el archivo `database/schema/sorteos_schema.sql` en phpMyAdmin.

## Notas Importantes

- Por defecto, XAMPP no tiene contraseña para el usuario `root`
- Si cambias la contraseña de MySQL, recuerda actualizar ambos archivos de configuración
- El puerto por defecto de MySQL en XAMPP es `3306` (no necesitas especificarlo si usas `localhost`)
- Si usas un puerto diferente, cambia `DB_HOST` a `'127.0.0.1:3307'` (o el puerto que uses)

## Estructura de Archivos de Configuración

```
SorteosWeb-main/
├── php/
│   ├── cliente/
│   │   └── config/
│   │       └── database.php      (Configuración para cliente - usa PDO)
│   └── administrador/
│       └── config.php            (Configuración para administrador - usa mysqli)
└── database/
    └── schema/
        └── sorteos_schema.sql    (Esquema de la base de datos)
```

¡Listo! Tu aplicación debería estar conectada a la base de datos de XAMPP.
