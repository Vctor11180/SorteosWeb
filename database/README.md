# 🗄️ Scripts de Base de Datos - SorteosWeb

Este directorio contiene todos los scripts SQL organizados por categoría.

## 📁 Estructura

### 📋 [Schema](./schema/)
Esquema principal de la base de datos:
- **[sorteos_schema.sql](./schema/sorteos_schema.sql)** - Esquema completo de la base de datos (tablas, índices, relaciones)

### 🔄 [Migrations](./migrations/)
Scripts de migración y actualización de la base de datos:
- **[actualizar_fechas_sorteos.sql](./migrations/actualizar_fechas_sorteos.sql)** - Actualización de fechas en sorteos
- **[actualizar_usuarios.sql](./migrations/actualizar_usuarios.sql)** - Actualización de datos de usuarios
- **[agregar_campo_caracteristicas.sql](./migrations/agregar_campo_caracteristicas.sql)** - Agregar campo de características a sorteos
- **[corregir_caracteristicas_iphone.sql](./migrations/corregir_caracteristicas_iphone.sql)** - Corrección de características para iPhone

### 🌱 [Seeds](./seeds/)
Datos de prueba e inserts iniciales:
- **[inserts_sorteos.sql](./seeds/inserts_sorteos.sql)** - Datos de ejemplo para la tabla de sorteos

## 🚀 Uso

### Instalación Inicial

1. **Crear la base de datos:**
   ```sql
   CREATE DATABASE sorteos_schema CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Importar el esquema:**
   - Usa phpMyAdmin o MySQL CLI para importar `schema/sorteos_schema.sql`

3. **Importar datos de prueba (opcional):**
   - Ejecuta `seeds/inserts_sorteos.sql` si necesitas datos de ejemplo

### Aplicar Migraciones

1. Revisa el contenido de cada archivo en `migrations/`
2. Ejecuta las migraciones en orden cronológico si aplican
3. Verifica que no haya conflictos con tu base de datos actual

## 📝 Notas Importantes

- **Siempre haz un backup** antes de ejecutar migraciones en producción
- Revisa cada script SQL antes de ejecutarlo
- Algunas migraciones pueden requerir ajustes según tu entorno

## 📚 Documentación Relacionada

Para más información sobre la configuración de la base de datos, consulta:
- [Guía de Configuración de XAMPP](../docs/guias/CONFIGURACION_XAMPP.md)
- [Guía de Inicio Rápido](../docs/guias/GUIA_INICIO_RAPIDO.md)

---

**Última actualización:** Enero 2025

