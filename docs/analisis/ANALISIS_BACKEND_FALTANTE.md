# Análisis de Backend, Lógica y Funcionalidad Faltante

## 📋 Resumen Ejecutivo

Este documento identifica todas las funcionalidades de backend, lógica de negocio y APIs que faltan implementar en el sistema de sorteos.

---

## 🔴 CRÍTICO - APIs del Cliente Faltantes

### 1. **API de Boletos del Cliente**
**Archivo:** `php/cliente/api_boletos.php` (NO EXISTE)

**Endpoints necesarios:**
- `GET /api_boletos.php?action=get_available&id_sorteo={id}` - Obtener boletos disponibles de un sorteo
- `POST /api_boletos.php?action=reserve` - Reservar boletos temporalmente (15 minutos)
- `POST /api_boletos.php?action=release` - Liberar boletos reservados
- `GET /api_boletos.php?action=get_user_tickets` - Obtener boletos del usuario actual
- `GET /api_boletos.php?action=get_ticket_details&id_boleto={id}` - Detalles de un boleto específico

**Estado actual:** 
- ❌ No existe la API
- ⚠️ La selección de boletos se hace solo en frontend (localStorage)
- ⚠️ No hay validación en tiempo real de disponibilidad

---

### 2. **API de Transacciones/Pagos del Cliente**
**Archivo:** `php/cliente/api_transacciones.php` (NO EXISTE)

**Endpoints necesarios:**
- `POST /api_transacciones.php?action=create` - Crear una transacción de compra
- `POST /api_transacciones.php?action=upload_comprobante` - Subir comprobante de pago
- `GET /api_transacciones.php?action=get_user_transactions` - Obtener transacciones del usuario
- `GET /api_transacciones.php?action=get_transaction_details&id={id}` - Detalles de transacción

**Estado actual:**
- ❌ No existe la API
- ⚠️ `FinalizarPagoBoletos.php` solo simula el proceso (línea 690-703)
- ⚠️ No se crean transacciones en la BD
- ⚠️ No se suben comprobantes al servidor

---

### 3. **API de Sorteos del Cliente**
**Archivo:** `php/cliente/api_sorteos.php` (NO EXISTE)

**Endpoints necesarios:**
- `GET /api_sorteos.php?action=list_active` - Listar sorteos activos
- `GET /api_sorteos.php?action=get_details&id={id}` - Detalles de un sorteo
- `GET /api_sorteos.php?action=get_stats&id={id}` - Estadísticas de un sorteo (vendidos, disponibles, etc.)

**Estado actual:**
- ⚠️ Existe `includes/sorteos-data.php` pero no es una API REST
- ⚠️ No hay endpoints JSON para consumo desde frontend
- ⚠️ Los datos se cargan directamente en PHP, no vía AJAX

---

### 4. **API de Ganancias del Cliente**
**Archivo:** `php/cliente/api_ganancias.php` (NO EXISTE)

**Endpoints necesarios:**
- `GET /api_ganancias.php?action=get_user_winnings` - Obtener premios ganados por el usuario
- `POST /api_ganancias.php?action=claim_prize` - Reclamar un premio
- `GET /api_ganancias.php?action=get_claim_status&id_sorteo={id}` - Estado de reclamación

**Estado actual:**
- ❌ No existe la API
- ⚠️ `MisGanancias.php` muestra datos estáticos en HTML
- ⚠️ No hay conexión con la tabla `ganadores` de la BD

---

### 5. **API de Soporte del Cliente**
**Archivo:** `php/cliente/api_soporte.php` (NO EXISTE)

**Endpoints necesarios:**
- `POST /api_soporte.php?action=create_ticket` - Crear ticket de soporte
- `GET /api_soporte.php?action=get_user_tickets` - Obtener tickets del usuario
- `GET /api_soporte.php?action=get_ticket_details&id={id}` - Detalles de un ticket
- `POST /api_soporte.php?action=add_message` - Agregar mensaje a un ticket

**Estado actual:**
- ❌ No existe la API
- ⚠️ `ContactoSoporteCliente.php` usa EmailJS (línea 372-506) pero no guarda en BD
- ⚠️ No se utiliza la tabla `soporte_tickets` del schema
- ⚠️ No hay sistema de seguimiento de tickets

---

## 🟡 IMPORTANTE - Funcionalidades de Backend Faltantes

### 6. **Sistema de Reserva Temporal de Boletos**

**Problema:** Los boletos se "reservan" solo en localStorage del cliente, no en el servidor.

**Solución necesaria:**
- Actualizar tabla `boletos` con estado `'Reservado'` y `fecha_reserva`
- Job/cron que libere boletos reservados después de 15 minutos
- Validación en tiempo real al seleccionar boletos

**Archivos a crear/modificar:**
- `php/cliente/api_boletos.php` (función `reserveTickets()`)
- `php/cliente/cron_liberar_reservas.php` (script para ejecutar periódicamente)

---

### 7. **Sistema de Subida de Comprobantes**

**Problema:** Los comprobantes no se suben al servidor.

**Solución necesaria:**
- Endpoint para subir archivos (imágenes/PDFs)
- Validación de tipo y tamaño de archivo
- Almacenamiento seguro en carpeta `uploads/comprobantes/`
- Guardar ruta en campo `comprobante_url` de tabla `transacciones`

**Archivos a crear:**
- `php/cliente/api_upload.php` o incluir en `api_transacciones.php`
- Carpeta `php/cliente/uploads/comprobantes/` con permisos adecuados

---

### 8. **Proceso Completo de Compra**

**Flujo actual (INCOMPLETO):**
1. ✅ Cliente selecciona boletos (solo frontend)
2. ❌ No se reservan en BD
3. ❌ No se crea transacción
4. ❌ No se sube comprobante
5. ❌ No se validan pagos automáticamente

**Flujo necesario:**
1. Cliente selecciona boletos → **Reservar en BD (15 min)**
2. Cliente completa formulario → **Crear transacción con estado 'Pendiente'**
3. Cliente sube comprobante → **Guardar archivo y actualizar transacción**
4. Admin valida pago → **Aprobar transacción y marcar boletos como 'Vendido'**
5. Notificar al cliente → **Email de confirmación**

**Archivos a modificar:**
- `php/cliente/FinalizarPagoBoletos.php` (líneas 642-704) - Implementar llamadas a APIs reales
- `php/cliente/api_transacciones.php` - Crear endpoint completo

---

### 9. **Sistema de Notificaciones por Email**

**Problema:** No hay sistema de notificaciones.

**Funcionalidades necesarias:**
- Email de confirmación de compra
- Email cuando se aprueba/rechaza un pago
- Email cuando se gana un sorteo
- Email de recordatorio de boletos reservados

**Archivos a crear:**
- `php/includes/email_service.php` - Servicio de envío de emails
- Configuración de SMTP o servicio de email (PHPMailer, SendGrid, etc.)

**Integración necesaria:**
- Modificar `api_transacciones.php` para enviar emails
- Modificar `api_ganadores.php` (admin) para notificar ganadores
- Modificar `api_boletos.php` para recordatorios de reserva

---

### 10. **Gestión de Saldo Interno del Usuario**

**Problema:** El campo `saldo_disponible` existe pero no se utiliza.

**Funcionalidades necesarias:**
- Cargar saldo (depósito)
- Usar saldo para comprar boletos
- Historial de movimientos de saldo (tabla `historial_saldos`)

**Archivos a crear:**
- `php/cliente/api_saldo.php` - Gestión de saldo
- Endpoints:
  - `POST /api_saldo.php?action=deposit` - Cargar saldo
  - `POST /api_saldo.php?action=use` - Usar saldo en compra
  - `GET /api_saldo.php?action=get_history` - Historial de movimientos

---

### 11. **Sistema de Tickets de Soporte (CRUD Completo)**

**Problema:** La tabla `soporte_tickets` existe pero no se usa.

**Funcionalidades necesarias:**
- Crear tickets desde el cliente
- Ver tickets del usuario
- Responder a tickets
- Admin: Ver todos los tickets, cambiar estado, responder

**Archivos a crear:**
- `php/cliente/api_soporte.php` - API para clientes
- `php/administrador/api_soporte.php` - API para administradores
- Modificar `ContactoSoporteCliente.php` para usar la API en lugar de EmailJS

---

## 🟢 MEJORAS - Funcionalidades Adicionales

### 12. **Generación de Comprobantes PDF**

**Funcionalidad:** Generar PDFs de comprobantes de compra.

**Archivos a crear:**
- `php/cliente/generar_comprobante.php` - Generar PDF con FPDF o TCPDF
- Incluir: Datos del usuario, boletos comprados, fecha, monto, número de transacción

---

### 13. **Sistema de Actualización Automática de Estados**

**Problema:** Los sorteos no cambian de estado automáticamente.

**Solución:**
- Cron job que verifique fechas de sorteos
- Cambiar estado de 'Activo' a 'Finalizado' cuando `fecha_fin` expire
- Notificar a participantes cuando un sorteo finaliza

**Archivo a crear:**
- `php/cron_actualizar_estados_sorteos.php`

---

### 14. **API de Estadísticas para Dashboard Cliente**

**Endpoints necesarios:**
- `GET /api_estadisticas.php?action=get_user_stats` - Estadísticas del usuario
  - Total de boletos comprados
  - Total gastado
  - Premios ganados
  - Sorteos activos en los que participa

**Archivo a crear:**
- `php/cliente/api_estadisticas.php`

---

### 15. **Sistema de Búsqueda y Filtros Avanzados**

**Problema:** Los filtros en `MisBoletosCliente.php` son solo frontend.

**Solución:**
- Implementar filtros en el backend
- Búsqueda por número de boleto, sorteo, estado
- Paginación real desde el servidor

**Archivo a modificar:**
- `php/cliente/api_boletos.php` - Agregar parámetros de filtro y búsqueda

---

## 📊 Resumen de Archivos a Crear

### APIs del Cliente (NUEVOS):
1. ❌ `php/cliente/api_boletos.php`
2. ❌ `php/cliente/api_transacciones.php`
3. ❌ `php/cliente/api_sorteos.php`
4. ❌ `php/cliente/api_ganancias.php`
5. ❌ `php/cliente/api_soporte.php`
6. ❌ `php/cliente/api_saldo.php`
7. ❌ `php/cliente/api_estadisticas.php`
8. ❌ `php/cliente/api_upload.php`

### Servicios y Utilidades (NUEVOS):
9. ❌ `php/includes/email_service.php`
10. ❌ `php/cliente/generar_comprobante.php`
11. ❌ `php/cliente/cron_liberar_reservas.php`
12. ❌ `php/cron_actualizar_estados_sorteos.php`

### APIs del Administrador (FALTANTES):
13. ❌ `php/administrador/api_soporte.php` (para gestionar tickets)

---

## 🔧 Archivos a Modificar

### Cliente:
1. ⚠️ `php/cliente/FinalizarPagoBoletos.php` - Implementar llamadas reales a APIs (líneas 642-704)
2. ⚠️ `php/cliente/SeleccionBoletos.php` - Conectar con API de reserva de boletos
3. ⚠️ `php/cliente/MisBoletosCliente.php` - Cargar datos desde API
4. ⚠️ `php/cliente/MisGanancias.php` - Cargar datos desde API
5. ⚠️ `php/cliente/ContactoSoporteCliente.php` - Usar API en lugar de EmailJS
6. ⚠️ `php/cliente/DashboardCliente.php` - Cargar estadísticas desde API

### Administrador:
7. ⚠️ `php/administrador/api_ganadores.php` - Agregar notificación por email al generar ganador

---

## 🎯 Prioridades de Implementación

### PRIORIDAD ALTA (Crítico para funcionamiento básico):
1. ✅ API de Boletos (reserva y consulta)
2. ✅ API de Transacciones (crear y subir comprobante)
3. ✅ Sistema de reserva temporal con expiración
4. ✅ Proceso completo de compra

### PRIORIDAD MEDIA (Importante para UX):
5. ✅ API de Sorteos (para consumo desde frontend)
6. ✅ Sistema de notificaciones por email
7. ✅ API de Ganancias
8. ✅ Sistema de tickets de soporte

### PRIORIDAD BAJA (Mejoras y optimizaciones):
9. ✅ Gestión de saldo interno
10. ✅ Generación de comprobantes PDF
11. ✅ Actualización automática de estados
12. ✅ API de estadísticas

---

## 📝 Notas Técnicas

### Base de Datos:
- ✅ El schema está completo y bien diseñado
- ⚠️ Algunas tablas no se están utilizando: `soporte_tickets`, `historial_saldos`, `campanas_marketing`

### Seguridad:
- ⚠️ Validar permisos en todas las APIs (verificar sesión y rol)
- ⚠️ Sanitizar todas las entradas
- ⚠️ Validar tipos de archivo en subida de comprobantes
- ⚠️ Implementar rate limiting para prevenir abusos

### Performance:
- ⚠️ Implementar caché para listados de sorteos activos
- ⚠️ Optimizar consultas de boletos (usar índices)
- ⚠️ Considerar paginación en todas las listas

---

## ✅ Checklist de Implementación

### Fase 1: Funcionalidad Básica de Compra
- [ ] Crear `api_boletos.php` con reserva temporal
- [ ] Crear `api_transacciones.php` con creación y subida de comprobante
- [ ] Implementar cron para liberar reservas
- [ ] Modificar `FinalizarPagoBoletos.php` para usar APIs reales
- [ ] Modificar `SeleccionBoletos.php` para reservar en BD

### Fase 2: Visualización y Consulta
- [ ] Crear `api_sorteos.php` para frontend
- [ ] Modificar `MisBoletosCliente.php` para cargar desde API
- [ ] Modificar `MisGanancias.php` para cargar desde API
- [ ] Crear `api_estadisticas.php` para dashboard

### Fase 3: Notificaciones y Soporte
- [ ] Crear `email_service.php`
- [ ] Integrar emails en proceso de compra
- [ ] Crear `api_soporte.php` (cliente y admin)
- [ ] Modificar `ContactoSoporteCliente.php`

### Fase 4: Mejoras y Optimizaciones
- [ ] Crear `api_saldo.php`
- [ ] Crear `generar_comprobante.php`
- [ ] Crear cron para actualizar estados
- [ ] Implementar caché y optimizaciones

---

**Fecha de análisis:** $(date)
**Versión del sistema:** 1.0
**Estado:** En desarrollo
