# Análisis: Automatización de Asignación de Boletos

## 🎯 Objetivo
Cambiar de **selección manual** a **asignación automática/aleatoria** de boletos para evitar fraudes y manipulación.

---

## 📊 Flujo Actual (Selección Manual)

### 1. **Frontend (`SeleccionBoletos.php`)**
```
Usuario → Ve todos los boletos (1, 2, 3, ..., 100)
       → Hace CLICK en los que quiere (ej: 7, 13, 45, 88)
       → Cada click reserva ese boleto específico
       → Ve los números que seleccionó
       → Finaliza compra con esos números específicos
```

### 2. **Backend (`api_boletos.php`)**
```
- GET get_available → Devuelve TODOS los boletos con sus números
- POST reserve → Reserva los números específicos que el usuario eligió
- El usuario controla qué números recibe
```

### 3. **Problemas Actuales**
- ❌ Usuario puede elegir números "de la suerte"
- ❌ Posible coordinación entre usuarios
- ❌ Manipulación de números específicos
- ❌ No es justo para todos los participantes

---

## 🎲 Flujo Propuesto (Asignación Automática)

### 1. **Nuevo Flujo Frontend**
```
Usuario → Ve solo: "¿Cuántos boletos quieres comprar?" (input: 1-10)
       → Hace click en "Comprar X boletos"
       → Sistema asigna boletos ALEATORIOS automáticamente
       → Usuario ve los números que le tocaron (después de asignar)
       → Finaliza compra con esos números asignados
```

### 2. **Nuevo Flujo Backend**
```
- Usuario solo indica CANTIDAD de boletos
- Backend busca boletos DISPONIBLES aleatoriamente
- Asigna esos boletos al usuario
- Devuelve los números asignados
```

---

## 🔄 Cambios Necesarios

### **FASE 1: Backend - Nueva API de Asignación Automática**

#### 1.1. Nuevo Endpoint en `api_boletos.php`
```php
POST ?action=assign_random
Body: {
    "id_sorteo": 1,
    "cantidad": 3  // Usuario solo dice cuántos quiere
}

Response: {
    "success": true,
    "data": {
        "id_sorteo": 1,
        "boletos_asignados": [45, 12, 89],  // Números aleatorios
        "total": 3,
        "precio_total": 30.00
    }
}
```

**Lógica:**
- Buscar boletos disponibles (estado = 'Disponible')
- Seleccionar aleatoriamente la cantidad solicitada
- Reservarlos automáticamente para el usuario
- Devolver los números asignados

#### 1.2. Modificar Endpoint `reserve` (Opcional)
- **Opción A:** Eliminar completamente (ya no se necesita)
- **Opción B:** Mantener pero solo para uso interno/admin

#### 1.3. Nuevo Endpoint para Ver Boletos Asignados
```php
GET ?action=get_my_assigned&id_sorteo={id}
```
- Mostrar los boletos que el usuario ya tiene asignados en este sorteo
- Para que pueda verlos antes de finalizar compra

---

### **FASE 2: Frontend - Cambiar UI de Selección**

#### 2.1. Modificar `SeleccionBoletos.php`

**ANTES:**
```html
<!-- Grid con todos los boletos clickeables -->
<div id="tickets-grid">
  <button data-numero="0001">0001</button>
  <button data-numero="0002">0002</button>
  <!-- ... 100 botones ... -->
</div>
```

**DESPUÉS:**
```html
<!-- Solo selector de cantidad -->
<div class="ticket-selector">
  <label>¿Cuántos boletos quieres comprar?</label>
  <input type="number" id="cantidad-boletos" min="1" max="10" value="1">
  <button id="btn-asignar-boletos">Asignar Boletos</button>
</div>

<!-- Mostrar boletos asignados (después de asignar) -->
<div id="boletos-asignados" style="display: none;">
  <h3>Tus boletos asignados:</h3>
  <div id="assigned-tickets-list"></div>
  <button id="btn-finalizar-compra">Finalizar Compra</button>
</div>
```

#### 2.2. Eliminar Funcionalidad de Selección Manual
- ❌ Eliminar `toggleTicketSelection()`
- ❌ Eliminar `renderTickets()` (grid de boletos)
- ❌ Eliminar filtros de búsqueda por número
- ❌ Eliminar botones individuales de boletos

#### 2.3. Nueva Funcionalidad JavaScript
```javascript
// Nueva función para asignar boletos aleatorios
async function assignRandomTickets(cantidad) {
    const response = await fetch('api_boletos.php?action=assign_random', {
        method: 'POST',
        body: JSON.stringify({
            id_sorteo: currentSorteoId,
            cantidad: cantidad
        })
    });
    
    const data = await response.json();
    
    if (data.success) {
        // Mostrar los boletos asignados
        displayAssignedTickets(data.data.boletos_asignados);
        // Guardar para finalizar compra
        window.assignedTickets = data.data.boletos_asignados;
    }
}
```

---

### **FASE 3: Validaciones y Seguridad**

#### 3.1. Límites de Compra
- ✅ Máximo de boletos por usuario por sorteo (ej: 10)
- ✅ Verificar que hay suficientes boletos disponibles
- ✅ Validar que el sorteo está activo

#### 3.2. Transacciones Atómicas
- ✅ Asignar boletos en una transacción de BD
- ✅ Si falla, rollback completo
- ✅ Evitar condiciones de carrera (dos usuarios asignando al mismo tiempo)

#### 3.3. Logs y Auditoría
- ✅ Registrar quién asignó qué boletos
- ✅ Timestamp de asignación
- ✅ Para auditoría futura

---

## 📋 Cambios Específicos por Archivo

### **1. `php/cliente/api_boletos.php`**

#### Agregar:
```php
case 'assign_random':
    if ($method === 'POST') {
        assignRandomTickets($db, $usuarioId);
    }
    break;
```

#### Nueva función:
```php
function assignRandomTickets($db, $usuarioId) {
    // 1. Leer cantidad del body
    // 2. Validar sorteo activo
    // 3. Buscar boletos disponibles (ORDER BY RAND())
    // 4. Reservar esos boletos
    // 5. Devolver números asignados
}
```

#### Modificar/Eliminar:
- `get_available`: Ya no necesita devolver todos los números (solo estadísticas)
- `reserve`: Eliminar o mantener solo para admin

---

### **2. `php/cliente/SeleccionBoletos.php`**

#### Eliminar:
- ❌ Grid completo de boletos (`renderTickets()`)
- ❌ Búsqueda por número
- ❌ Filtros (Disponible, Seleccionado, etc.)
- ❌ `toggleTicketSelection()`
- ❌ `markTicketAsSelected()`
- ❌ `markTicketAsAvailable()`
- ❌ Variables: `selectedTickets`, `maxTickets`

#### Agregar:
- ✅ Selector de cantidad (input number)
- ✅ Botón "Asignar Boletos"
- ✅ Sección para mostrar boletos asignados
- ✅ `assignRandomTickets(cantidad)`
- ✅ `displayAssignedTickets(numeros)`

#### Modificar:
- ✅ `loadSorteoData()`: Solo cargar info del sorteo, no boletos
- ✅ Botón "Finalizar Compra": Usar boletos asignados automáticamente

---

### **3. `php/cliente/api_transacciones.php`**

#### Modificar:
- ✅ `createTransaction()`: Recibir boletos ya asignados (no seleccionados)
- ✅ Validar que los boletos estén reservados por el usuario actual

---

## 🎨 Cambios en la UI

### **ANTES:**
```
┌─────────────────────────────────────┐
│ Selecciona tus boletos:             │
│                                     │
│ [Buscar número...] [Filtros]       │
│                                     │
│ [0001] [0002] [0003] [0004] ...    │
│ [0005] [0006] [0007] [0008] ...    │
│ ... (100 botones)                   │
│                                     │
│ Boletos seleccionados: 3            │
│ Total: $30.00                       │
│ [Finalizar Compra]                  │
└─────────────────────────────────────┘
```

### **DESPUÉS:**
```
┌─────────────────────────────────────┐
│ ¿Cuántos boletos quieres comprar?  │
│                                     │
│ Cantidad: [1] [▼]                   │
│ (Máximo: 10 boletos)                │
│                                     │
│ [Asignar Boletos Aleatoriamente]   │
│                                     │
│ ─────────────────────────────────   │
│                                     │
│ Tus boletos asignados:              │
│ ✓ 0045  ✓ 0012  ✓ 0089             │
│                                     │
│ Total: $30.00                       │
│ [Finalizar Compra]                  │
└─────────────────────────────────────┘
```

---

## ⚠️ Consideraciones Importantes

### **1. Experiencia de Usuario**
- ✅ Más simple (solo elige cantidad)
- ✅ Más rápido (no necesita buscar números)
- ⚠️ Usuario no puede elegir números específicos (puede ser pro o contra)

### **2. Justicia y Transparencia**
- ✅ Todos tienen las mismas probabilidades
- ✅ No hay manipulación posible
- ✅ Más justo para todos

### **3. Implementación Técnica**
- ✅ Más simple (menos código frontend)
- ✅ Menos carga en el servidor (no renderizar 100+ botones)
- ⚠️ Necesita validación robusta de concurrencia

### **4. Migración de Datos**
- ⚠️ ¿Qué pasa con boletos ya reservados manualmente?
- ✅ Opción: Mantener reservas existentes, solo nuevos sorteos usan asignación automática
- ✅ Opción: Migrar todos a asignación automática

---

## 🔒 Seguridad Adicional

### **1. Prevenir Asignaciones Múltiples**
```php
// Usar transacciones de BD
$db->beginTransaction();
try {
    // 1. Bloquear filas (SELECT ... FOR UPDATE)
    // 2. Verificar disponibilidad
    // 3. Asignar boletos
    // 4. Commit
} catch {
    // Rollback si falla
}
```

### **2. Límite de Tiempo**
- ✅ Asignación válida por 15 minutos (igual que reserva actual)
- ✅ Si no finaliza compra, liberar boletos

### **3. Rate Limiting**
- ✅ Máximo X asignaciones por usuario por hora
- ✅ Prevenir abuso del sistema

---

## 📝 Plan de Implementación

### **Paso 1: Backend (1-2 días)**
1. Crear endpoint `assign_random` en `api_boletos.php`
2. Implementar lógica de asignación aleatoria
3. Validaciones y transacciones
4. Testing

### **Paso 2: Frontend (1-2 días)**
1. Modificar `SeleccionBoletos.php` (nueva UI)
2. Eliminar código de selección manual
3. Implementar `assignRandomTickets()`
4. Testing

### **Paso 3: Integración (1 día)**
1. Ajustar `api_transacciones.php` si es necesario
2. Testing end-to-end
3. Validar flujo completo

### **Paso 4: Deploy (1 día)**
1. Migrar datos existentes (si aplica)
2. Deploy a producción
3. Monitoreo

**Total estimado: 4-6 días**

---

## ❓ Preguntas para Decidir

1. **¿Eliminar completamente la selección manual?**
   - ✅ Sí: Más simple, más seguro
   - ⚠️ No: Mantener como opción admin

2. **¿Qué hacer con boletos ya reservados manualmente?**
   - ✅ Mantenerlos (solo nuevos sorteos usan auto)
   - ✅ Migrar todos a auto

3. **¿Límite de boletos por usuario?**
   - Recomendado: 10 boletos máximo por sorteo

4. **¿Mostrar números antes o después de pagar?**
   - ✅ Antes: Usuario ve qué le tocó
   - ⚠️ Después: Más sorpresa (menos común)

---

## ✅ Resumen de Cambios

| Componente | Cambio | Complejidad |
|------------|--------|-------------|
| **Backend API** | Nuevo endpoint `assign_random` | Media |
| **Frontend UI** | Eliminar grid, agregar selector cantidad | Alta |
| **Lógica Reserva** | Cambiar de manual a automática | Media |
| **Validaciones** | Agregar límites y transacciones | Media |
| **Testing** | Probar asignación aleatoria | Baja |

**¿Procedemos con la implementación?**
