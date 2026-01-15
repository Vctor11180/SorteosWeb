# Orden de Implementación - Sistema de Sorteos

## ✅ Confirmación: Todo está alineado con la Base de Datos

El schema `sorteos_schema.sql` tiene todas las tablas necesarias:

### Tablas Principales:
- ✅ `sorteos` - Sorteos disponibles
- ✅ `boletos` - Boletos de cada sorteo (estado: Disponible/Reservado/Vendido)
- ✅ `transacciones` - Pagos realizados
- ✅ `detalle_transaccion_boletos` - Relación transacción ↔ boletos
- ✅ `ganadores` - Premios ganados
- ✅ `usuarios` - Ya existe y funciona

### Campos Clave para Implementación:
- `boletos.estado` → 'Disponible', 'Reservado', 'Vendido'
- `boletos.id_usuario_actual` → Usuario que reservó/compró
- `boletos.fecha_reserva` → Para expiración de reservas
- `transacciones.estado_pago` → 'Pendiente', 'Completado', 'Fallido'
- `transacciones.comprobante_url` → Ruta del archivo subido

---

## 📋 ORDEN DE IMPLEMENTACIÓN

### **FASE 1: APIs de Lectura (Sin Modificar BD)**

#### 1.1. API de Sorteos - Lectura
**Archivo:** `php/cliente/api_sorteos.php`

**Endpoints:**
- `GET ?action=list_active` - Listar sorteos activos
- `GET ?action=get_details&id={id}` - Detalles de un sorteo
- `GET ?action=get_stats&id={id}` - Estadísticas (vendidos, disponibles)

**Tablas usadas:** `sorteos`, `boletos` (solo lectura con COUNT)

**Dependencias:** Ninguna (solo SELECT)

**Prioridad:** ⭐⭐⭐ CRÍTICA

---

### **FASE 2: APIs de Boletos (Modifica Estado)**

#### 2.1. API de Boletos - Consulta y Reserva
**Archivo:** `php/cliente/api_boletos.php`

**Endpoints:**
- `GET ?action=get_available&id_sorteo={id}` - Boletos disponibles
- `POST ?action=reserve` - Reservar boletos (15 min)
- `POST ?action=release` - Liberar boletos reservados
- `GET ?action=check_reservation&id_sorteo={id}` - Verificar reservas activas

**Tablas usadas:** `boletos` (UPDATE estado, id_usuario_actual, fecha_reserva)

**Dependencias:** Requiere `api_sorteos.php` para validar que el sorteo existe

**Campos que modifica:**
- `boletos.estado` → 'Reservado'
- `boletos.id_usuario_actual` → ID del usuario
- `boletos.fecha_reserva` → TIMESTAMP actual

**Prioridad:** ⭐⭐⭐ CRÍTICA

---

#### 2.2. Script Cron para Liberar Reservas Expiradas
**Archivo:** `php/cliente/cron_liberar_reservas.php`

**Función:** Liberar boletos reservados hace más de 15 minutos

**Tablas usadas:** `boletos` (UPDATE)

**Query lógica:**
```sql
UPDATE boletos 
SET estado = 'Disponible', 
    id_usuario_actual = NULL, 
    fecha_reserva = NULL 
WHERE estado = 'Reservado' 
  AND fecha_reserva < NOW() - INTERVAL 15 MINUTE
```

**Dependencias:** Requiere `api_boletos.php` funcionando

**Prioridad:** ⭐⭐ IMPORTANTE (puede hacerse después de la reserva básica)

---

### **FASE 3: APIs de Transacciones (Crea Registros)**

#### 3.1. API de Subida de Archivos
**Archivo:** `php/cliente/api_upload.php` (o incluir en api_transacciones.php)

**Endpoints:**
- `POST ?action=upload_comprobante` - Subir comprobante de pago

**Tablas usadas:** Ninguna directamente (solo guarda archivo en servidor)

**Dependencias:** Ninguna

**Prioridad:** ⭐⭐⭐ CRÍTICA

---

#### 3.2. API de Transacciones
**Archivo:** `php/cliente/api_transacciones.php`

**Endpoints:**
- `POST ?action=create` - Crear transacción
- `POST ?action=upload_comprobante` - Subir comprobante (o usar api_upload.php)
- `GET ?action=get_user_transactions` - Transacciones del usuario
- `GET ?action=get_details&id={id}` - Detalles de transacción

**Tablas usadas:** 
- `transacciones` (INSERT)
- `detalle_transaccion_boletos` (INSERT)
- `boletos` (UPDATE estado a 'Reservado' - esperando validación)

**Dependencias:** 
- Requiere `api_boletos.php` (para validar que los boletos están reservados)
- Requiere `api_upload.php` (para subir comprobante)

**Flujo de creación:**
1. Validar que los boletos están reservados por el usuario actual
2. INSERT en `transacciones` (estado: 'Pendiente')
3. INSERT en `detalle_transaccion_boletos` (relacionar boletos)
4. Los boletos quedan como 'Reservado' hasta que admin apruebe

**Prioridad:** ⭐⭐⭐ CRÍTICA

---

### **FASE 4: Modificación de Páginas Frontend**

#### 4.1. ListadoSorteosActivos.php
**Modificaciones:**
- Eliminar carga PHP directa (línea 34: `obtenerSorteosActivos()`)
- Agregar JavaScript para cargar desde `api_sorteos.php?action=list_active`
- Implementar búsqueda y filtros dinámicos
- Agregar paginación o scroll infinito

**APIs usadas:** `api_sorteos.php`

**Dependencias:** Requiere FASE 1 completa

**Prioridad:** ⭐⭐⭐ CRÍTICA

---

#### 4.2. SorteoClienteDetalles.php
**Modificaciones:**
- Obtener `id_sorteo` de `$_GET['id']`
- Llamar a `api_sorteos.php?action=get_details&id={id}`
- Mostrar información dinámicamente
- Guardar datos en localStorage para `SeleccionBoletos.php`

**APIs usadas:** `api_sorteos.php`

**Dependencias:** Requiere FASE 1 completa

**Prioridad:** ⭐⭐⭐ CRÍTICA

---

#### 4.3. SeleccionBoletos.php
**Modificaciones:**
- Cargar boletos desde `api_boletos.php?action=get_available&id_sorteo={id}`
- Al seleccionar boleto → llamar a `api_boletos.php?action=reserve`
- Timer de 15 minutos → si expira, llamar a `api_boletos.php?action=release`
- Mostrar estados en tiempo real (colores: disponible/reservado/vendido)
- Al hacer clic en "Proceder al Pago" → validar que los boletos sigan reservados

**APIs usadas:** `api_boletos.php`, `api_sorteos.php`

**Dependencias:** Requiere FASE 1 y FASE 2 completas

**Prioridad:** ⭐⭐⭐ CRÍTICA

---

#### 4.4. FinalizarPagoBoletos.php
**Modificaciones:**
- Reemplazar simulación (línea 690) por llamadas reales
- Validar datos del formulario
- Subir comprobante → `api_upload.php` o `api_transacciones.php?action=upload_comprobante`
- Crear transacción → `api_transacciones.php?action=create`
- Manejar errores y validaciones
- Mostrar estado de la transacción
- Redirigir a "Mis Boletos" después de crear transacción

**APIs usadas:** `api_transacciones.php`, `api_upload.php`, `api_boletos.php`

**Dependencias:** Requiere FASE 1, FASE 2 y FASE 3 completas

**Prioridad:** ⭐⭐⭐ CRÍTICA

---

### **FASE 5: Visualización de Resultados**

#### 5.1. MisBoletosCliente.php
**Modificaciones:**
- Crear endpoint en `api_boletos.php`: `GET ?action=get_user_tickets`
- Cargar boletos del usuario desde API
- Mostrar estado: Pendiente (transacción pendiente), Aprobado (vendido), Rechazado

**APIs usadas:** `api_boletos.php`, `api_transacciones.php`

**Dependencias:** Requiere FASE 2 y FASE 3 completas

**Prioridad:** ⭐⭐ IMPORTANTE

---

#### 5.2. MisGanancias.php
**Modificaciones:**
- Crear endpoint en `api_ganancias.php`: `GET ?action=get_user_winnings`
- Cargar premios ganados desde tabla `ganadores`
- Mostrar estado de entrega

**APIs usadas:** `api_ganancias.php` (nueva)

**Tablas usadas:** `ganadores`, `sorteos`, `boletos`, `usuarios`

**Dependencias:** Requiere que admin ya genere ganadores

**Prioridad:** ⭐ BAJA (puede hacerse después)

---

## 📊 Resumen del Orden

### **ORDEN CRONOLÓGICO:**

```
1. api_sorteos.php (FASE 1)
   ↓
2. api_boletos.php (FASE 2.1)
   ↓
3. api_upload.php (FASE 3.1)
   ↓
4. api_transacciones.php (FASE 3.2)
   ↓
5. Modificar ListadoSorteosActivos.php (FASE 4.1)
   ↓
6. Modificar SorteoClienteDetalles.php (FASE 4.2)
   ↓
7. Modificar SeleccionBoletos.php (FASE 4.3)
   ↓
8. Modificar FinalizarPagoBoletos.php (FASE 4.4)
   ↓
9. cron_liberar_reservas.php (FASE 2.2) - Puede hacerse en paralelo
   ↓
10. Modificar MisBoletosCliente.php (FASE 5.1)
   ↓
11. api_ganancias.php + MisGanancias.php (FASE 5.2) - Opcional después
```

---

## 🔄 Flujo de Datos Completo (Según BD)

### **Flujo de Compra:**

```
1. Usuario ve sorteos activos
   → api_sorteos.php (SELECT sorteos WHERE estado='Activo')
   
2. Usuario ve detalles del sorteo
   → api_sorteos.php (SELECT sorteos WHERE id_sorteo=X)
   
3. Usuario selecciona boletos
   → api_boletos.php (SELECT boletos WHERE estado='Disponible')
   → api_boletos.php (UPDATE boletos SET estado='Reservado', id_usuario_actual=Y, fecha_reserva=NOW())
   
4. Usuario completa pago
   → api_upload.php (Guardar archivo en servidor)
   → api_transacciones.php (INSERT transacciones)
   → api_transacciones.php (INSERT detalle_transaccion_boletos)
   → Boletos siguen como 'Reservado' (esperando validación admin)
   
5. Admin aprueba pago
   → api_pagos.php (UPDATE transacciones SET estado_pago='Completado')
   → api_pagos.php (UPDATE boletos SET estado='Vendido')
   
6. Usuario ve sus boletos
   → api_boletos.php (SELECT boletos WHERE id_usuario_actual=Y)
```

---

## ⚠️ Validaciones Importantes (Según BD)

### **En api_boletos.php (reserve):**
- ✅ Verificar que `id_sorteo` existe en `sorteos`
- ✅ Verificar que `sorteos.estado = 'Activo'`
- ✅ Verificar que `boletos.estado = 'Disponible'`
- ✅ Verificar que `boletos.id_sorteo = {id_sorteo}`
- ✅ No permitir reservar boletos ya 'Vendido' o 'Reservado' por otro usuario

### **En api_transacciones.php (create):**
- ✅ Verificar que los boletos están 'Reservado' por el usuario actual
- ✅ Verificar que `id_usuario` existe en `usuarios`
- ✅ Verificar que `usuarios.estado = 'Activo'`
- ✅ Calcular `monto_total` = cantidad_boletos × precio_boleto
- ✅ Validar que `monto_total` coincide con el monto enviado

### **En api_upload.php:**
- ✅ Validar tipo de archivo (PNG, JPG, PDF)
- ✅ Validar tamaño máximo (2MB)
- ✅ Generar nombre único para evitar conflictos
- ✅ Guardar en carpeta segura (`uploads/comprobantes/`)

---

## 📝 Notas de Implementación

### **Estructura de Carpetas:**
```
php/cliente/
  ├── api_sorteos.php
  ├── api_boletos.php
  ├── api_transacciones.php
  ├── api_upload.php
  ├── uploads/
  │   └── comprobantes/
  │       └── (archivos subidos aquí)
  └── cron_liberar_reservas.php
```

### **Configuración de Base de Datos:**
- Usar `php/cliente/config/database.php` (PDO)
- O `php/administrador/config.php` (mysqli) según corresponda
- Mantener consistencia en el proyecto

### **Seguridad:**
- ✅ Validar sesión en todas las APIs
- ✅ Verificar que `$_SESSION['id_usuario']` existe
- ✅ Sanitizar todas las entradas
- ✅ Usar prepared statements
- ✅ Validar permisos (solo el usuario puede ver/modificar sus datos)

---

## ✅ Checklist de Implementación

### FASE 1: APIs de Lectura
- [ ] Crear `api_sorteos.php` con `list_active`
- [ ] Crear `api_sorteos.php` con `get_details`
- [ ] Crear `api_sorteos.php` con `get_stats`
- [ ] Probar endpoints con Postman/Thunder Client

### FASE 2: APIs de Boletos
- [ ] Crear `api_boletos.php` con `get_available`
- [ ] Crear `api_boletos.php` con `reserve`
- [ ] Crear `api_boletos.php` con `release`
- [ ] Crear `cron_liberar_reservas.php`
- [ ] Probar reserva y liberación

### FASE 3: APIs de Transacciones
- [ ] Crear `api_upload.php` con `upload_comprobante`
- [ ] Crear carpeta `uploads/comprobantes/` con permisos
- [ ] Crear `api_transacciones.php` con `create`
- [ ] Crear `api_transacciones.php` con `upload_comprobante`
- [ ] Probar creación de transacción completa

### FASE 4: Modificar Páginas
- [ ] Modificar `ListadoSorteosActivos.php`
- [ ] Modificar `SorteoClienteDetalles.php`
- [ ] Modificar `SeleccionBoletos.php`
- [ ] Modificar `FinalizarPagoBoletos.php`
- [ ] Probar flujo completo de compra

### FASE 5: Visualización
- [ ] Agregar `get_user_tickets` a `api_boletos.php`
- [ ] Modificar `MisBoletosCliente.php`
- [ ] Crear `api_ganancias.php` (opcional)
- [ ] Modificar `MisGanancias.php` (opcional)

---

**Fecha:** $(date)
**Versión:** 1.0
**Estado:** Listo para implementación
