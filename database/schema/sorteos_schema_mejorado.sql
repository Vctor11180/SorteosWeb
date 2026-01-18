-- ============================================================================
-- ESQUEMA COMPLETO DE BASE DE DATOS - SISTEMA DE SORTEOS WEB
-- VERSIÓN MEJORADA CON COMENTARIOS Y EXPLICACIONES
-- ============================================================================
-- 
-- Este esquema incluye todas las mejoras recomendadas basadas en:
-- - Análisis de rendimiento y consultas frecuentes
-- - Integridad de datos y prevención de duplicados
-- - Escalabilidad y mantenimiento
-- - Auditoría y trazabilidad
--
-- ============================================================================

-- ============================================================================
-- 1. GESTIÓN DE ACCESOS Y ROLES
-- ============================================================================

CREATE TABLE roles (
    id_rol INT PRIMARY KEY AUTO_INCREMENT,
    nombre_rol VARCHAR(20) NOT NULL UNIQUE, -- 'Administrador', 'Cliente'
    -- COMENTARIO: UNIQUE garantiza que no haya roles duplicados
    -- No se necesita índice adicional porque la tabla es pequeña (2-3 registros)
    
    -- CAMPOS DE AUDITORÍA (NUEVOS)
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- COMENTARIO: Permite saber cuándo se creó cada rol (útil para auditoría)
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    -- COMENTARIO: Se actualiza automáticamente cuando se modifica el registro
) ENGINE=InnoDB;

-- ============================================================================
-- 2. USUARIOS
-- ============================================================================

CREATE TABLE usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    primer_nombre VARCHAR(50) NOT NULL,
    segundo_nombre VARCHAR(50), -- Opcional, puede ser NULL
    apellido_paterno VARCHAR(50) NOT NULL,
    apellido_materno VARCHAR(50) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    
    email VARCHAR(100) UNIQUE NOT NULL,
    -- COMENTARIO: UNIQUE garantiza que no haya emails duplicados
    -- MySQL crea automáticamente un índice para columnas UNIQUE
    
    password_hash VARCHAR(255) NOT NULL,
    -- COMENTARIO: 255 caracteres es suficiente para bcrypt/argon2
    
    telefono VARCHAR(20), -- Sin restricción de formato (puede ser internacional)
    
    saldo_disponible DECIMAL(12, 2) DEFAULT 0.00,
    -- COMENTARIO: DECIMAL(12,2) permite hasta 9,999,999,999.99
    -- Se necesita CHECK constraint para evitar saldos negativos
    
    avatar_url VARCHAR(255) DEFAULT 'default_avatar.png',
    notif_email BOOLEAN DEFAULT TRUE,
    
    estado ENUM('Activo', 'Baneado', 'Inactivo') DEFAULT 'Activo',
    -- COMENTARIO: ENUM es eficiente y valida valores automáticamente
    
    id_rol INT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- CAMPOS DE AUDITORÍA (NUEVOS)
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- COMENTARIO: Rastrea cuándo se actualizó el usuario por última vez
    
    -- CLAVES FORÁNEAS
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol),
    -- COMENTARIO: Garantiza que el rol existe en la tabla roles
    
    -- CONSTRAINTS DE VALIDACIÓN (NUEVOS)
    CHECK (saldo_disponible >= 0),
    -- COMENTARIO: Previene saldos negativos que causarían problemas financieros
    -- IMPORTANTE: MySQL 8.0.16+ soporta CHECK constraints, versiones anteriores las ignoran
    
    CHECK (email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$'),
    -- COMENTARIO: Validación básica de formato de email
    -- NOTA: Esta validación es básica, la validación completa debe hacerse en la aplicación
    
    -- ÍNDICES ADICIONALES (NUEVOS)
    INDEX idx_estado (estado),
    -- COMENTARIO: Acelera consultas que filtran por estado (ej: usuarios activos)
    -- Consultas frecuentes: "SELECT * FROM usuarios WHERE estado = 'Activo'"
    
    INDEX idx_fecha_registro (fecha_registro),
    -- COMENTARIO: Acelera reportes y consultas ordenadas por fecha de registro
    -- Consultas frecuentes: "SELECT * FROM usuarios ORDER BY fecha_registro DESC"
    
    INDEX idx_rol_estado (id_rol, estado)
    -- COMENTARIO: Índice compuesto para consultas que filtran por rol Y estado
    -- Consultas frecuentes: "SELECT * FROM usuarios WHERE id_rol = 1 AND estado = 'Activo'"
    -- Este índice cubre ambas condiciones en una sola búsqueda
) ENGINE=InnoDB;

-- ============================================================================
-- 3. SORTEOS
-- ============================================================================

CREATE TABLE sorteos (
    id_sorteo INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT, -- TEXT permite descripciones largas
    
    precio_boleto DECIMAL(10, 2) NOT NULL,
    -- COMENTARIO: DECIMAL(10,2) permite hasta 99,999,999.99
    -- Se necesita CHECK constraint para evitar precios negativos o cero
    
    total_boletos_crear INT NOT NULL,
    -- COMENTARIO: INT permite hasta 2,147,483,647 boletos (suficiente)
    
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME NOT NULL,
    -- COMENTARIO: DATETIME permite fecha y hora específica
    -- Se necesita CHECK constraint para validar que fecha_fin > fecha_inicio
    
    imagen_url VARCHAR(255),
    
    -- CAMPO AGREGADO EN MIGRACIÓN
    caracteristicas JSON NULL,
    -- COMENTARIO: JSON permite almacenar características dinámicas sin modificar el esquema
    -- Ejemplo: {"velocidad_maxima": "320 km/h", "motorizacion": "V8 Biturbo"}
    -- Ventaja: Flexible para diferentes tipos de premios
    -- Desventaja: Más difícil de consultar que campos normalizados
    -- 
    -- ⚠️ DECISIÓN DE DISEÑO: JSON vs TABLA NORMALIZADA
    -- 
    -- ACTUAL: JSON (adecuado si):
    --   ✅ Solo muestras características (no filtras/buscas por ellas)
    --   ✅ Volumen < 10,000 sorteos
    --   ✅ Características varían mucho entre productos
    --   ✅ No necesitas reportes estadísticos por características
    --
    -- ALTERNATIVA: Tabla normalizada (mejor si):
    --   ❌ Necesitas filtrar: "Sorteos con garantía > 2 años"
    --   ❌ Necesitas reportes: "Categorías más frecuentes"
    --   ❌ Volumen > 100,000 sorteos
    --   ❌ Consultas frecuentes por características específicas
    --
    -- VER: Sección "ALTERNATIVA: TABLA NORMALIZADA" más abajo para estructura alternativa
    
    estado ENUM('Borrador', 'Activo', 'Finalizado', 'Pausado') DEFAULT 'Borrador',
    id_creador INT NOT NULL,
    
    -- CAMPOS DE AUDITORÍA (NUEVOS)
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- CLAVES FORÁNEAS
    FOREIGN KEY (id_creador) REFERENCES usuarios(id_usuario),
    -- COMENTARIO: Garantiza que el creador existe en la tabla usuarios
    
    -- CONSTRAINTS DE VALIDACIÓN (NUEVOS)
    CHECK (precio_boleto > 0),
    -- COMENTARIO: Previene precios negativos o cero que causarían errores en cálculos
    
    CHECK (total_boletos_crear > 0),
    -- COMENTARIO: Un sorteo debe tener al menos 1 boleto
    
    CHECK (fecha_fin > fecha_inicio),
    -- COMENTARIO: Previene errores lógicos donde el sorteo termina antes de empezar
    -- IMPORTANTE: MySQL 8.0.16+ soporta CHECK constraints
    
    -- ÍNDICES ADICIONALES (NUEVOS)
    INDEX idx_estado (estado),
    -- COMENTARIO: Acelera consultas que filtran por estado
    -- Consultas frecuentes: "SELECT * FROM sorteos WHERE estado = 'Activo'"
    
    INDEX idx_fecha_fin (fecha_fin),
    -- COMENTARIO: Acelera consultas que buscan sorteos por fecha de finalización
    -- Consultas frecuentes: "SELECT * FROM sorteos WHERE fecha_fin > NOW()"
    
    INDEX idx_estado_fecha (estado, fecha_fin),
    -- COMENTARIO: Índice compuesto para consultas que filtran por estado Y fecha
    -- Consultas frecuentes: "SELECT * FROM sorteos WHERE estado = 'Activo' AND fecha_fin > NOW()"
    -- Este índice cubre ambas condiciones eficientemente
    
    INDEX idx_creador (id_creador),
    -- COMENTARIO: Acelera consultas que buscan sorteos por creador
    -- Consultas frecuentes: "SELECT * FROM sorteos WHERE id_creador = X"
    -- Aunque hay FK, el índice explícito mejora JOINs y consultas de agrupación
) ENGINE=InnoDB;

-- ============================================================================
-- 4. BOLETOS
-- ============================================================================

CREATE TABLE boletos (
    id_boleto INT PRIMARY KEY AUTO_INCREMENT,
    id_sorteo INT NOT NULL,
    numero_boleto VARCHAR(10) NOT NULL,
    -- COMENTARIO: VARCHAR(10) permite números como "0001", "0123", "9999"
    -- Alternativa: INT UNSIGNED sería más eficiente pero perdería el formato con ceros
    
    estado ENUM('Disponible', 'Reservado', 'Vendido') DEFAULT 'Disponible',
    -- COMENTARIO: ENUM valida automáticamente los valores permitidos
    
    id_usuario_actual INT NULL,
    -- COMENTARIO: NULL cuando está disponible, contiene ID de usuario cuando está reservado/vendido
    
    fecha_reserva TIMESTAMP NULL,
    -- COMENTARIO: NULL cuando está disponible, se establece cuando se reserva
    -- Se usa para calcular expiración de reservas (15 minutos)
    
    -- CAMPOS DE AUDITORÍA (NUEVOS)
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- CLAVES FORÁNEAS
    FOREIGN KEY (id_sorteo) REFERENCES sorteos(id_sorteo) ON DELETE CASCADE,
    -- COMENTARIO: ON DELETE CASCADE elimina automáticamente los boletos si se elimina el sorteo
    -- IMPORTANTE: Esto previene boletos huérfanos
    
    FOREIGN KEY (id_usuario_actual) REFERENCES usuarios(id_usuario),
    -- COMENTARIO: Garantiza que el usuario existe
    -- Sin ON DELETE CASCADE porque si se elimina el usuario, los boletos deben quedar disponibles
    
    -- CONSTRAINTS DE UNICIDAD (NUEVOS)
    UNIQUE KEY uk_sorteo_numero (id_sorteo, numero_boleto),
    -- COMENTARIO: CRÍTICO - Previene duplicados de números de boleto dentro del mismo sorteo
    -- Sin esto, podrían existir dos boletos #0001 en el mismo sorteo, causando errores
    -- Este constraint es esencial para la integridad del sistema
    
    -- CONSTRAINTS DE VALIDACIÓN (NUEVOS)
    CHECK (
        (estado = 'Disponible' AND id_usuario_actual IS NULL AND fecha_reserva IS NULL) OR
        (estado = 'Reservado' AND id_usuario_actual IS NOT NULL AND fecha_reserva IS NOT NULL) OR
        (estado = 'Vendido' AND id_usuario_actual IS NOT NULL)
    ),
    -- COMENTARIO: Garantiza consistencia entre estado, usuario y fecha de reserva
    -- Reglas:
    -- - Disponible: sin usuario, sin fecha
    -- - Reservado: con usuario, con fecha
    -- - Vendido: con usuario (fecha puede ser NULL si ya pasó tiempo)
    -- NOTA: Este CHECK es complejo, puede ser mejor manejarlo en la aplicación
    
    -- ÍNDICES EXISTENTES
    INDEX idx_sorteo_estado (id_sorteo, estado),
    -- COMENTARIO: Ya existía - útil para consultas que filtran por sorteo y estado
    -- Consultas frecuentes: "SELECT * FROM boletos WHERE id_sorteo = X AND estado = 'Disponible'"
    
    -- ÍNDICES ADICIONALES (NUEVOS)
    INDEX idx_fecha_reserva_estado (fecha_reserva, estado),
    -- COMENTARIO: CRÍTICO - Acelera la liberación de reservas expiradas
    -- Consultas frecuentes: "SELECT * FROM boletos WHERE estado = 'Reservado' AND fecha_reserva < DATE_SUB(NOW(), INTERVAL 15 MINUTE)"
    -- Este índice permite encontrar rápidamente reservas expiradas sin escanear toda la tabla
    
    INDEX idx_usuario_estado (id_usuario_actual, estado),
    -- COMENTARIO: Acelera consultas que buscan boletos de un usuario por estado
    -- Consultas frecuentes: "SELECT * FROM boletos WHERE id_usuario_actual = X AND estado = 'Reservado'"
    -- Muy usado en: MisBoletosCliente.php, DashboardCliente.php
    
    INDEX idx_usuario_sorteo (id_usuario_actual, id_sorteo),
    -- COMENTARIO: Acelera consultas que verifican cuántos boletos tiene un usuario en un sorteo
    -- Consultas frecuentes: "SELECT COUNT(*) FROM boletos WHERE id_usuario_actual = X AND id_sorteo = Y"
    -- Usado en: api_boletos.php para validar límite de 10 boletos por usuario
) ENGINE=InnoDB;

-- ============================================================================
-- 5. PAGOS Y TRANSACCIONES
-- ============================================================================

CREATE TABLE transacciones (
    id_transaccion INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    
    monto_total DECIMAL(10, 2) NOT NULL,
    -- COMENTARIO: DECIMAL(10,2) permite hasta 99,999,999.99
    -- Se necesita CHECK constraint para evitar montos negativos o cero
    
    metodo_pago ENUM('PayPal', 'Transferencia', 'Visa', 'Saldo Interno'),
    -- COMENTARIO: ENUM valida los métodos de pago permitidos
    
    referencia_pago VARCHAR(100),
    -- COMENTARIO: Número de referencia del pago externo (ej: número de transferencia)
    
    comprobante_url VARCHAR(255),
    -- COMENTARIO: Ruta al archivo de comprobante subido por el usuario
    
    estado_pago ENUM('Pendiente', 'Completado', 'Fallido') DEFAULT 'Pendiente',
    -- COMENTARIO: Estado del proceso de pago
    
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    id_validador INT NULL,
    -- COMENTARIO: ID del administrador que validó el pago (NULL si aún no se valida)
    
    -- CAMPOS DE AUDITORÍA (NUEVOS)
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    fecha_validacion TIMESTAMP NULL,
    -- COMENTARIO: Fecha en que se validó el pago (se establece cuando estado_pago = 'Completado')
    -- Permite rastrear cuándo se procesó cada transacción
    
    -- CLAVES FORÁNEAS
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    -- COMENTARIO: Garantiza que el usuario existe
    
    FOREIGN KEY (id_validador) REFERENCES usuarios(id_usuario),
    -- COMENTARIO: Garantiza que el validador existe (debe ser administrador)
    
    -- CONSTRAINTS DE VALIDACIÓN (NUEVOS)
    CHECK (monto_total > 0),
    -- COMENTARIO: Previene transacciones con montos inválidos
    
    CHECK (
        (estado_pago = 'Completado' AND id_validador IS NOT NULL) OR
        (estado_pago != 'Completado')
    ),
    -- COMENTARIO: Garantiza que las transacciones completadas tienen un validador
    -- Previene transacciones "completadas" sin validación
    
    -- ÍNDICES ADICIONALES (NUEVOS)
    INDEX idx_usuario_estado (id_usuario, estado_pago),
    -- COMENTARIO: Acelera consultas que buscan transacciones de un usuario por estado
    -- Consultas frecuentes: "SELECT * FROM transacciones WHERE id_usuario = X AND estado_pago = 'Pendiente'"
    -- Muy usado en: DashboardCliente.php, MisBoletosCliente.php
    
    INDEX idx_estado_pago (estado_pago),
    -- COMENTARIO: Acelera consultas que filtran por estado de pago
    -- Consultas frecuentes: "SELECT * FROM transacciones WHERE estado_pago = 'Pendiente'"
    -- Muy usado en: ValidacionPagosAdministrador.php
    
    INDEX idx_fecha_creacion (fecha_creacion),
    -- COMENTARIO: Acelera reportes y consultas ordenadas por fecha
    -- Consultas frecuentes: "SELECT * FROM transacciones ORDER BY fecha_creacion DESC"
    -- Usado en: InformesEstadisticasAdmin.php
    
    INDEX idx_validador (id_validador),
    -- COMENTARIO: Acelera consultas que buscan transacciones validadas por un administrador específico
    -- Consultas frecuentes: "SELECT * FROM transacciones WHERE id_validador = X"
    -- Útil para auditoría de acciones de administradores
) ENGINE=InnoDB;

-- ============================================================================
-- 6. RELACIÓN DE BOLETOS POR TRANSACCIÓN
-- ============================================================================

CREATE TABLE detalle_transaccion_boletos (
    id_detalle INT PRIMARY KEY AUTO_INCREMENT,
    -- COMENTARIO: Clave primaria auto-incremental
    -- Alternativa: Podría usarse clave primaria compuesta (id_transaccion, id_boleto)
    -- pero mantener id_detalle facilita referencias en código
    
    id_transaccion INT NOT NULL,
    id_boleto INT NOT NULL,
    
    -- CAMPOS DE AUDITORÍA (NUEVOS)
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- COMENTARIO: Permite rastrear cuándo se asoció cada boleto a la transacción
    
    -- CLAVES FORÁNEAS
    FOREIGN KEY (id_transaccion) REFERENCES transacciones(id_transaccion) ON DELETE CASCADE,
    -- COMENTARIO: ON DELETE CASCADE elimina automáticamente los detalles si se elimina la transacción
    -- Esto mantiene la integridad referencial
    
    FOREIGN KEY (id_boleto) REFERENCES boletos(id_boleto),
    -- COMENTARIO: Garantiza que el boleto existe
    -- Sin ON DELETE CASCADE porque eliminar un boleto no debería eliminar la transacción
    
    -- CONSTRAINTS DE UNICIDAD (NUEVOS)
    UNIQUE KEY uk_transaccion_boleto (id_transaccion, id_boleto),
    -- COMENTARIO: CRÍTICO - Previene que un boleto se asocie múltiples veces a la misma transacción
    -- Sin esto, un boleto podría aparecer duplicado en una transacción, causando errores de cálculo
    
    -- ÍNDICES ADICIONALES (NUEVOS)
    INDEX idx_boleto (id_boleto),
    -- COMENTARIO: Acelera consultas inversas (buscar transacción desde boleto)
    -- Consultas frecuentes: "SELECT * FROM detalle_transaccion_boletos WHERE id_boleto = X"
    -- Útil para: verificar si un boleto ya está en una transacción
    
    INDEX idx_transaccion (id_transaccion),
    -- COMENTARIO: Aunque hay FK, este índice explícito mejora JOINs
    -- Consultas frecuentes: "SELECT b.* FROM boletos b JOIN detalle_transaccion_boletos dtb ON b.id_boleto = dtb.id_boleto WHERE dtb.id_transaccion = X"
    -- Muy usado en: FinalizarPagoBoletos.php, api_transacciones.php
) ENGINE=InnoDB;

-- ============================================================================
-- 7. HISTORIAL DE GANADORES
-- ============================================================================

CREATE TABLE ganadores (
    -- NOTA: Esta tabla NO tiene clave primaria auto-incremental
    -- Se usa UNIQUE constraint en lugar de PRIMARY KEY compuesta
    
    id_sorteo INT NOT NULL,
    id_usuario INT NOT NULL,
    id_boleto INT NOT NULL,
    
    premio_detalle VARCHAR(255),
    -- COMENTARIO: Descripción adicional del premio ganado
    
    fecha_anuncio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- COMENTARIO: Fecha en que se anunció al ganador
    
    entregado BOOLEAN DEFAULT FALSE,
    -- COMENTARIO: Indica si el premio ya fue entregado físicamente
    
    -- CAMPOS DE AUDITORÍA (NUEVOS)
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    fecha_entrega TIMESTAMP NULL,
    -- COMENTARIO: Fecha en que se entregó el premio (se establece cuando entregado = TRUE)
    
    -- CLAVES FORÁNEAS
    FOREIGN KEY (id_sorteo) REFERENCES sorteos(id_sorteo),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_boleto) REFERENCES boletos(id_boleto),
    
    -- CONSTRAINTS DE UNICIDAD
    UNIQUE (id_sorteo, id_boleto),
    -- COMENTARIO: Previene que un sorteo tenga múltiples ganadores con el mismo boleto
    -- NOTA: Esto permite múltiples ganadores por sorteo (premios secundarios)
    -- Si se requiere solo un ganador por sorteo, cambiar a: UNIQUE (id_sorteo)
    
    -- ÍNDICES ADICIONALES (NUEVOS)
    INDEX idx_sorteo (id_sorteo),
    -- COMENTARIO: Acelera consultas que buscan ganadores de un sorteo
    -- Consultas frecuentes: "SELECT * FROM ganadores WHERE id_sorteo = X"
    
    INDEX idx_usuario (id_usuario),
    -- COMENTARIO: Acelera consultas que buscan ganancias de un usuario
    -- Consultas frecuentes: "SELECT * FROM ganadores WHERE id_usuario = X"
    -- Muy usado en: MisGanancias.php
    
    INDEX idx_entregado (entregado),
    -- COMENTARIO: Acelera consultas que filtran por estado de entrega
    -- Consultas frecuentes: "SELECT * FROM ganadores WHERE entregado = FALSE"
    -- Útil para: seguimiento de premios pendientes de entrega
    
    INDEX idx_fecha_anuncio (fecha_anuncio),
    -- COMENTARIO: Acelera reportes ordenados por fecha de anuncio
    -- Consultas frecuentes: "SELECT * FROM ganadores ORDER BY fecha_anuncio DESC"
    
    -- CLAVE PRIMARIA ALTERNATIVA (RECOMENDADA)
    -- PRIMARY KEY (id_sorteo, id_boleto)
    -- COMENTARIO: Alternativa al UNIQUE constraint
    -- Ventaja: Más eficiente que UNIQUE + índice
    -- Desventaja: Requiere que (id_sorteo, id_boleto) sea siempre único
) ENGINE=InnoDB;

-- ============================================================================
-- 8. SOPORTE Y AYUDA
-- ============================================================================

CREATE TABLE soporte_tickets (
    id_ticket INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    
    asunto VARCHAR(150) NOT NULL,
    -- COMENTARIO: Título breve del ticket
    
    mensaje TEXT NOT NULL,
    -- COMENTARIO: TEXT permite mensajes largos sin límite de caracteres
    
    prioridad ENUM('Baja', 'Media', 'Alta') DEFAULT 'Media',
    estado ENUM('Abierto', 'En Proceso', 'Cerrado') DEFAULT 'Abierto',
    
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- CAMPOS ADICIONALES (NUEVOS)
    id_responsable INT NULL,
    -- COMENTARIO: ID del administrador asignado para resolver el ticket
    -- NULL si aún no se ha asignado
    
    fecha_cierre TIMESTAMP NULL,
    -- COMENTARIO: Fecha en que se cerró el ticket (se establece cuando estado = 'Cerrado')
    -- Permite calcular tiempo de resolución
    
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- CLAVES FORÁNEAS
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_responsable) REFERENCES usuarios(id_usuario),
    -- COMENTARIO: Garantiza que el responsable existe (debe ser administrador)
    
    -- CONSTRAINTS DE VALIDACIÓN (NUEVOS)
    CHECK (
        (estado = 'Cerrado' AND fecha_cierre IS NOT NULL) OR
        (estado != 'Cerrado')
    ),
    -- COMENTARIO: Garantiza que los tickets cerrados tienen fecha de cierre
    -- Mejora la consistencia de datos
    
    -- ÍNDICES ADICIONALES (NUEVOS)
    INDEX idx_usuario_estado (id_usuario, estado),
    -- COMENTARIO: Acelera consultas que buscan tickets de un usuario por estado
    -- Consultas frecuentes: "SELECT * FROM soporte_tickets WHERE id_usuario = X AND estado = 'Abierto'"
    
    INDEX idx_estado_prioridad (estado, prioridad),
    -- COMENTARIO: Índice compuesto para consultas que filtran por estado Y prioridad
    -- Consultas frecuentes: "SELECT * FROM soporte_tickets WHERE estado = 'Abierto' AND prioridad = 'Alta'"
    -- Útil para: dashboard de soporte que muestra tickets urgentes pendientes
    
    INDEX idx_responsable (id_responsable),
    -- COMENTARIO: Acelera consultas que buscan tickets asignados a un administrador
    -- Consultas frecuentes: "SELECT * FROM soporte_tickets WHERE id_responsable = X"
    
    INDEX idx_fecha_creacion (fecha_creacion),
    -- COMENTARIO: Acelera reportes y consultas ordenadas por fecha
    -- Consultas frecuentes: "SELECT * FROM soporte_tickets ORDER BY fecha_creacion DESC"
) ENGINE=InnoDB;

-- ============================================================================
-- 9. SEGURIDAD Y AUDITORÍA
-- ============================================================================

CREATE TABLE auditoria_admin (
    id_log INT PRIMARY KEY AUTO_INCREMENT,
    id_admin INT NOT NULL,
    -- COMENTARIO: ID del administrador que realizó la acción
    
    accion VARCHAR(255) NOT NULL,
    -- COMENTARIO: Descripción de la acción realizada (ej: "Crear sorteo", "Aprobar pago")
    
    modulo VARCHAR(50),
    -- COMENTARIO: Módulo del sistema donde se realizó la acción (ej: "Sorteos", "Pagos")
    
    ip_address VARCHAR(45),
    -- COMENTARIO: IPv4 (15 caracteres) o IPv6 (45 caracteres)
    -- Permite rastrear desde dónde se realizó la acción
    
    user_agent VARCHAR(255),
    -- COMENTARIO: Información del navegador/cliente usado
    -- NOTA: Algunos user agents pueden ser más largos, considerar TEXT si es necesario
    
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- CAMPOS ADICIONALES (NUEVOS - OPCIONALES)
    tipo_accion ENUM('Crear', 'Modificar', 'Eliminar', 'Consultar', 'Aprobar', 'Rechazar') NULL,
    -- COMENTARIO: Categoriza el tipo de acción para facilitar filtros
    -- NULL para mantener compatibilidad con registros antiguos
    
    recurso VARCHAR(100) NULL,
    -- COMENTARIO: Recurso afectado (ej: "Sorteo #123", "Usuario #456")
    -- NULL para mantener compatibilidad
    
    estado ENUM('success', 'error', 'warning') NULL,
    -- COMENTARIO: Resultado de la acción (éxito, error, advertencia)
    -- NULL para mantener compatibilidad
    
    es_alerta BOOLEAN DEFAULT FALSE,
    -- COMENTARIO: Indica si esta acción requiere atención (ej: intentos de acceso no autorizados)
    
    -- CLAVES FORÁNEAS
    FOREIGN KEY (id_admin) REFERENCES usuarios(id_usuario),
    -- COMENTARIO: Garantiza que el administrador existe
    
    -- ÍNDICES ADICIONALES (NUEVOS)
    INDEX idx_fecha_hora (fecha_hora),
    -- COMENTARIO: CRÍTICO - Acelera consultas por rango de fechas
    -- Consultas frecuentes: "SELECT * FROM auditoria_admin WHERE fecha_hora BETWEEN X AND Y"
    -- Muy usado en: AuditoriaAccionesAdmin.php con filtros de fecha
    
    INDEX idx_admin_fecha (id_admin, fecha_hora),
    -- COMENTARIO: Índice compuesto para consultas que buscan acciones de un admin por fecha
    -- Consultas frecuentes: "SELECT * FROM auditoria_admin WHERE id_admin = X ORDER BY fecha_hora DESC"
    -- Útil para: auditoría de acciones específicas de un administrador
    
    INDEX idx_modulo (modulo),
    -- COMENTARIO: Acelera consultas que filtran por módulo
    -- Consultas frecuentes: "SELECT * FROM auditoria_admin WHERE modulo = 'Sorteos'"
    
    INDEX idx_tipo_accion (tipo_accion),
    -- COMENTARIO: Acelera consultas que filtran por tipo de acción (si se agrega el campo)
    -- Consultas frecuentes: "SELECT * FROM auditoria_admin WHERE tipo_accion = 'Eliminar'"
    
    INDEX idx_es_alerta (es_alerta),
    -- COMENTARIO: Acelera consultas que buscan solo alertas
    -- Consultas frecuentes: "SELECT * FROM auditoria_admin WHERE es_alerta = TRUE"
    
    -- NOTA SOBRE ESCALABILIDAD
    -- Esta tabla crece rápidamente. Considerar:
    -- 1. Particionado por fecha (PARTITION BY RANGE)
    -- 2. Archivo de logs antiguos (> 1 año) a tabla separada
    -- 3. Limpieza automática de registros muy antiguos
) ENGINE=InnoDB;

-- ============================================================================
-- 10. MARKETING Y REPORTES
-- ============================================================================

CREATE TABLE campanas_marketing (
    id_campana INT PRIMARY KEY AUTO_INCREMENT,
    
    red_social VARCHAR(100),
    -- COMENTARIO: Plataforma de marketing (ej: "Facebook", "Instagram", "Google Ads")
    
    empresa VARCHAR(100),
    -- COMENTARIO: Empresa o agencia que gestiona la campaña
    
    costo_inversion DECIMAL(10, 2),
    -- COMENTARIO: Costo total de la campaña
    -- Se necesita CHECK constraint para evitar costos negativos
    
    clics_generados INT DEFAULT 0,
    -- COMENTARIO: Contador de clics generados por la campaña
    
    estado ENUM('Activa', 'Pausada', 'Finalizada') DEFAULT 'Activa',
    
    fecha_inicio DATE,
    fecha_fin DATE,
    
    -- CAMPOS ADICIONALES (NUEVOS)
    id_creador INT NULL,
    -- COMENTARIO: ID del administrador que creó la campaña
    -- Permite rastrear quién gestiona cada campaña
    
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- CLAVES FORÁNEAS (NUEVAS)
    FOREIGN KEY (id_creador) REFERENCES usuarios(id_usuario),
    -- COMENTARIO: Garantiza que el creador existe
    
    -- CONSTRAINTS DE VALIDACIÓN (NUEVOS)
    CHECK (costo_inversion >= 0),
    -- COMENTARIO: Previene costos negativos
    
    CHECK (clics_generados >= 0),
    -- COMENTARIO: Previene contadores negativos
    
    CHECK (fecha_fin >= fecha_inicio OR fecha_fin IS NULL OR fecha_inicio IS NULL),
    -- COMENTARIO: Valida que la fecha de fin sea posterior a la de inicio
    -- Permite NULLs para campañas sin fecha definida
    
    -- ÍNDICES ADICIONALES (NUEVOS)
    INDEX idx_estado (estado),
    -- COMENTARIO: Acelera consultas que filtran por estado
    -- Consultas frecuentes: "SELECT * FROM campanas_marketing WHERE estado = 'Activa'"
    
    INDEX idx_fechas (fecha_inicio, fecha_fin),
    -- COMENTARIO: Índice compuesto para consultas por rango de fechas
    -- Consultas frecuentes: "SELECT * FROM campanas_marketing WHERE fecha_inicio <= NOW() AND fecha_fin >= NOW()"
    -- Útil para: encontrar campañas activas en un período específico
    
    INDEX idx_creador (id_creador),
    -- COMENTARIO: Acelera consultas que buscan campañas de un creador específico
    -- Consultas frecuentes: "SELECT * FROM campanas_marketing WHERE id_creador = X"
) ENGINE=InnoDB;

-- ============================================================================
-- 11. HISTORIAL DE MOVIMIENTOS DE SALDO (AUDITORÍA FINANCIERA)
-- ============================================================================

CREATE TABLE historial_saldos (
    id_historial INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    
    monto DECIMAL(12, 2) NOT NULL,
    -- COMENTARIO: DECIMAL(12,2) permite hasta 9,999,999,999.99
    -- Puede ser positivo (carga, premio) o negativo (compra, retiro)
    
    tipo_movimiento ENUM('Carga', 'Compra', 'Premio', 'Retiro') NOT NULL,
    -- COMENTARIO: Tipo de movimiento de saldo
    -- Carga: Usuario deposita dinero
    -- Compra: Usuario compra boletos
    -- Premio: Usuario gana un premio en efectivo
    -- Retiro: Usuario retira dinero
    
    id_referencia_transaccion INT NULL,
    -- COMENTARIO: Opcional: link a la tabla transacciones para rastrear origen
    -- NULL para movimientos que no provienen de transacciones (ej: ajustes manuales)
    
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- CAMPOS ADICIONALES (NUEVOS)
    descripcion VARCHAR(255) NULL,
    -- COMENTARIO: Descripción adicional del movimiento (ej: "Compra de 5 boletos - Sorteo #123")
    -- Facilita la comprensión del historial para el usuario
    
    saldo_anterior DECIMAL(12, 2) NULL,
    saldo_nuevo DECIMAL(12, 2) NULL,
    -- COMENTARIO: Snapshot del saldo antes y después del movimiento
    -- Permite verificar integridad: saldo_nuevo = saldo_anterior + monto
    -- Útil para auditoría y detección de inconsistencias
    
    -- CLAVES FORÁNEAS
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    
    FOREIGN KEY (id_referencia_transaccion) REFERENCES transacciones(id_transaccion) ON DELETE SET NULL,
    -- COMENTARIO: NUEVO - Garantiza que la transacción existe si se referencia
    -- ON DELETE SET NULL: Si se elimina la transacción, el historial se mantiene pero sin referencia
    -- Esto preserva el historial financiero incluso si se eliminan transacciones
    
    -- CONSTRAINTS DE VALIDACIÓN (NUEVOS)
    CHECK (monto != 0),
    -- COMENTARIO: Previene movimientos de $0.00 que no tienen sentido
    
    CHECK (
        (tipo_movimiento IN ('Carga', 'Premio') AND monto > 0) OR
        (tipo_movimiento IN ('Compra', 'Retiro') AND monto < 0) OR
        monto = 0
    ),
    -- COMENTARIO: Valida que el signo del monto coincida con el tipo de movimiento
    -- Carga/Premio: deben ser positivos
    -- Compra/Retiro: deben ser negativos
    -- NOTA: Este CHECK puede ser restrictivo, considerar manejarlo en la aplicación
    
    -- ÍNDICES ADICIONALES (NUEVOS)
    INDEX idx_usuario_fecha (id_usuario, fecha),
    -- COMENTARIO: CRÍTICO - Acelera consultas que buscan historial de un usuario ordenado por fecha
    -- Consultas frecuentes: "SELECT * FROM historial_saldos WHERE id_usuario = X ORDER BY fecha DESC"
    -- Muy usado en: DashboardCliente.php, reportes de saldo
    
    INDEX idx_tipo_movimiento (tipo_movimiento),
    -- COMENTARIO: Acelera consultas que filtran por tipo de movimiento
    -- Consultas frecuentes: "SELECT * FROM historial_saldos WHERE tipo_movimiento = 'Compra'"
    -- Útil para: reportes financieros por tipo de movimiento
    
    INDEX idx_fecha (fecha),
    -- COMENTARIO: Acelera reportes y consultas por rango de fechas
    -- Consultas frecuentes: "SELECT * FROM historial_saldos WHERE fecha BETWEEN X AND Y"
    -- Útil para: reportes mensuales, anuales
    
    INDEX idx_referencia_transaccion (id_referencia_transaccion),
    -- COMENTARIO: Acelera consultas que buscan movimientos relacionados con una transacción
    -- Consultas frecuentes: "SELECT * FROM historial_saldos WHERE id_referencia_transaccion = X"
    -- Útil para: rastrear todos los movimientos de una transacción específica
) ENGINE=InnoDB;

-- ============================================================================
-- RESUMEN DE MEJORAS IMPLEMENTADAS
-- ============================================================================
--
-- 1. ÍNDICES AGREGADOS:
--    - Índices en campos de filtrado frecuente (estado, fecha_*)
--    - Índices compuestos para consultas complejas
--    - Índices en claves foráneas para mejorar JOINs
--
-- 2. CONSTRAINTS DE UNICIDAD:
--    - UNIQUE en boletos(id_sorteo, numero_boleto) - Previene duplicados
--    - UNIQUE en detalle_transaccion_boletos(id_transaccion, id_boleto) - Previene duplicados
--
-- 3. CONSTRAINTS DE VALIDACIÓN:
--    - CHECK para montos positivos/no negativos
--    - CHECK para validación de fechas
--    - CHECK para consistencia de estados
--
-- 4. CAMPOS DE AUDITORÍA:
--    - fecha_creacion en todas las tablas principales
--    - fecha_actualizacion en todas las tablas principales
--    - Campos adicionales según necesidad (fecha_validacion, fecha_cierre, etc.)
--
-- 5. CLAVES FORÁNEAS:
--    - FK agregada en historial_saldos.id_referencia_transaccion
--    - FK agregada en campanas_marketing.id_creador
--    - FK agregada en soporte_tickets.id_responsable
--
-- 6. CAMPOS ADICIONALES:
--    - Campos para mejorar trazabilidad y funcionalidad
--    - Campos para soporte de nuevas funcionalidades
--
-- ============================================================================
-- ⚠️ PROBLEMAS QUE OCURRIRÁN SI NO INCLUYES ESTAS MEJORAS
-- ============================================================================
--
-- Esta sección documenta los problemas reales que enfrentarás si decides
-- NO implementar las mejoras propuestas. Cada problema está basado en
-- escenarios reales que ocurren en sistemas de producción.
--
-- ============================================================================
-- PROBLEMA 1: FALTA DE ÍNDICES
-- ============================================================================
--
-- ❌ SIN ÍNDICES EN 'estado':
--    Problema: Consultas como "SELECT * FROM sorteos WHERE estado = 'Activo'"
--              escanean TODA la tabla (table scan completo).
--    Impacto:
--      - Con 1,000 sorteos: 0.5 segundos (aceptable)
--      - Con 10,000 sorteos: 5 segundos (lento)
--      - Con 100,000 sorteos: 50+ segundos (INACEPTABLE)
--    Escenario real:
--      - Usuario intenta ver sorteos activos → página tarda 30+ segundos
--      - Usuario cierra la página → pérdida de conversión
--      - Servidor sobrecargado → otros usuarios también se ven afectados
--
-- ❌ SIN ÍNDICES EN 'fecha_reserva' (boletos):
--    Problema: Liberar reservas expiradas requiere escanear TODOS los boletos.
--    Impacto:
--      - Script de limpieza tarda horas en ejecutarse
--      - Bloquea la tabla durante la ejecución
--      - Otros usuarios no pueden reservar boletos mientras corre
--    Escenario real:
--      - Cron job corre cada 15 minutos para liberar reservas
--      - Sin índice: tarda 10 minutos en ejecutarse
--      - Tabla bloqueada → usuarios no pueden comprar boletos
--      - Sistema se vuelve inutilizable durante la limpieza
--
-- ❌ SIN ÍNDICES COMPUESTOS (id_usuario, estado):
--    Problema: Consultas como "SELECT * FROM boletos WHERE id_usuario = X AND estado = 'Reservado'"
--              requieren dos búsquedas separadas.
--    Impacto:
--      - Consulta tarda 10x más tiempo
--      - Dashboard del usuario se carga lentamente
--      - Múltiples usuarios consultando simultáneamente → servidor colapsa
--    Escenario real:
--      - 100 usuarios abren "Mis Boletos" simultáneamente
--      - Cada consulta tarda 2 segundos sin índice
--      - Servidor procesa 100 consultas × 2 segundos = 200 segundos totales
--      - Algunos usuarios esperan 3+ minutos → abandonan el sitio
--
-- ============================================================================
-- PROBLEMA 2: FALTA DE UNIQUE CONSTRAINTS
-- ============================================================================
--
-- ❌ SIN UNIQUE EN boletos(id_sorteo, numero_boleto):
--    Problema: Pueden existir múltiples boletos con el mismo número en un sorteo.
--    Impacto:
--      - Dos usuarios pueden tener el boleto #0001 del mismo sorteo
--      - Al momento del sorteo, ¿quién gana?
--      - Conflicto legal y pérdida de confianza
--    Escenario real:
--      - Usuario A compra boleto #0001 del Sorteo #5
--      - Bug en el código permite crear otro boleto #0001
--      - Usuario B también tiene boleto #0001 del Sorteo #5
--      - Al sortear, sale el número 0001
--      - ¿Quién gana? → Disputa legal
--      - Ambos usuarios demandan → pérdida de dinero y reputación
--
-- ❌ SIN UNIQUE EN detalle_transaccion_boletos(id_transaccion, id_boleto):
--    Problema: Un boleto puede aparecer múltiples veces en la misma transacción.
--    Impacto:
--      - Cálculo de monto_total incorrecto
--      - Usuario paga menos de lo que debería
--      - Pérdida de dinero para la empresa
--    Escenario real:
--      - Usuario compra 5 boletos a $50 cada uno = $250
--      - Bug permite que el boleto #123 aparezca 3 veces en la transacción
--      - Sistema calcula: 7 boletos pero solo cobra $250
--      - Usuario recibió 2 boletos extra gratis
--      - Empresa perdió $100 en esa transacción
--
-- ============================================================================
-- PROBLEMA 3: FALTA DE CHECK CONSTRAINTS
-- ============================================================================
--
-- ❌ SIN CHECK (saldo_disponible >= 0):
--    Problema: Usuarios pueden tener saldo negativo.
--    Impacto:
--      - Usuario con saldo -$500 puede comprar boletos
--      - Sistema permite compras sin dinero
--      - Pérdida financiera directa
--    Escenario real:
--      - Bug en código permite saldo negativo
--      - Usuario tiene saldo -$1,000
--      - Usuario compra 20 boletos a $50 = $1,000
--      - Sistema permite la compra porque no valida en BD
--      - Empresa perdió $1,000
--
-- ❌ SIN CHECK (precio_boleto > 0):
--    Problema: Sorteos pueden crearse con precio $0.00 o negativo.
--    Impacto:
--      - Administrador comete error y crea sorteo con precio $0
--      - Usuarios compran boletos gratis
--      - Empresa no genera ingresos
--    Escenario real:
--      - Admin crea sorteo de iPhone 15 Pro
--      - Error de tipeo: precio_boleto = 0 en lugar de 50
--      - 1,000 usuarios compran boletos a $0
--      - Empresa perdió $50,000 en ingresos potenciales
--
-- ❌ SIN CHECK (fecha_fin > fecha_inicio):
--    Problema: Sorteos pueden crearse con fecha de fin anterior a inicio.
--    Impacto:
--      - Sorteo "activo" pero ya terminó según fechas
--      - Usuarios confundidos
--      - Sistema muestra sorteos inválidos
--    Escenario real:
--      - Admin crea sorteo con fecha_inicio = 2024-12-31, fecha_fin = 2024-01-01
--      - Sistema muestra sorteo como "activo"
--      - Usuarios intentan comprar pero el sorteo ya "terminó"
--      - Confusión y pérdida de confianza
--
-- ============================================================================
-- PROBLEMA 4: FALTA DE CAMPOS DE AUDITORÍA
-- ============================================================================
--
-- ❌ SIN fecha_creacion y fecha_actualizacion:
--    Problema: No puedes rastrear cuándo se crearon o modificaron registros.
--    Impacto:
--      - Imposible depurar problemas
--      - No puedes cumplir con requisitos legales (GDPR, etc.)
--      - No puedes hacer análisis de negocio
--    Escenario real:
--      - Usuario reporta: "Mi saldo cambió sin razón"
--      - Sin fecha_actualizacion: no sabes cuándo cambió
--      - No puedes revisar logs del servidor (demasiados datos)
--      - No puedes identificar el problema
--      - Usuario insatisfecho → abandona el servicio
--
-- ❌ SIN fecha_validacion (transacciones):
--    Problema: No puedes medir tiempo de procesamiento de pagos.
--    Impacto:
--      - No sabes cuánto tarda un admin en validar pagos
--      - No puedes optimizar el proceso
--      - Usuarios esperan días sin saber por qué
--    Escenario real:
--      - Usuario paga $500 el lunes
--      - Pago sigue "Pendiente" el viernes
--      - Usuario llama a soporte: "¿Por qué no validaron mi pago?"
--      - Sin fecha_validacion: no sabes si el admin ya lo validó o no
--      - No puedes medir SLA de validación
--
-- ❌ SIN saldo_anterior y saldo_nuevo (historial_saldos):
--    Problema: No puedes verificar integridad de saldos.
--    Impacto:
--      - Si hay bug, no puedes detectarlo
--      - Saldos incorrectos se propagan
--      - Pérdida de confianza del usuario
--    Escenario real:
--      - Usuario tiene saldo $100
--      - Compra boleto de $50
--      - Bug calcula mal: nuevo saldo = $60 (debería ser $50)
--      - Sin saldo_anterior/saldo_nuevo: no puedes detectar el error
--      - Usuario tiene $10 extra que no debería tener
--      - Problema se acumula → sistema financiero corrupto
--
-- ============================================================================
-- PROBLEMA 5: FALTA DE CLAVES FORÁNEAS
-- ============================================================================
--
-- ❌ SIN FK en historial_saldos.id_referencia_transaccion:
--    Problema: Puedes referenciar transacciones que no existen.
--    Impacto:
--      - Datos huérfanos
--      - Imposible rastrear origen del movimiento
--      - Auditoría financiera incompleta
--    Escenario real:
--      - Movimiento de saldo referencia transacción #999
--      - Admin elimina transacción #999 (por error)
--      - Movimiento queda huérfano
--      - Auditoría financiera: "¿De dónde vino este movimiento?"
--      - No puedes rastrear el origen → problema legal
--
-- ❌ SIN FK en campanas_marketing.id_creador:
--    Problema: No puedes rastrear quién creó cada campaña.
--    Impacto:
--      - Imposible asignar responsabilidad
--      - No puedes medir productividad de marketing
--      - No puedes auditar quién gasta el presupuesto
--    Escenario real:
--      - Campaña de $10,000 genera 0 clics
--      - Jefe pregunta: "¿Quién creó esta campaña?"
--      - Sin id_creador: no sabes quién fue
--      - No puedes identificar problemas de gestión
--
-- ============================================================================
-- PROBLEMA 6: FALTA DE ÍNDICES EN CLAVES FORÁNEAS
-- ============================================================================
--
-- ❌ SIN ÍNDICE en id_usuario_actual (boletos):
--    Problema: JOINs con tabla usuarios son extremadamente lentos.
--    Impacto:
--      - Consulta "boletos del usuario X" tarda minutos
--      - Dashboard del usuario no carga
--      - Sistema se vuelve inutilizable
--    Escenario real:
--      - Usuario abre "Mis Boletos"
--      - Query: SELECT b.*, u.nombre FROM boletos b JOIN usuarios u ON b.id_usuario_actual = u.id_usuario
--      - Sin índice: MySQL escanea TODOS los boletos para cada usuario
--      - Con 100,000 boletos: consulta tarda 30+ segundos
--      - Usuario cierra la página → pérdida de usuario
--
-- ============================================================================
-- RESUMEN DE RIESGOS
-- ============================================================================
--
-- RIESGO CRÍTICO (Puede causar pérdida de dinero o problemas legales):
--   1. Falta de UNIQUE en boletos(id_sorteo, numero_boleto)
--   2. Falta de UNIQUE en detalle_transaccion_boletos(id_transaccion, id_boleto)
--   3. Falta de CHECK (saldo_disponible >= 0)
--   4. Falta de CHECK (precio_boleto > 0)
--
-- RIESGO ALTO (Puede causar problemas de rendimiento severos):
--   5. Falta de índices en campos de filtrado frecuente
--   6. Falta de índices en fecha_reserva (liberación de reservas)
--   7. Falta de índices compuestos para consultas complejas
--
-- RIESGO MEDIO (Puede causar problemas operativos):
--   8. Falta de campos de auditoría
--   9. Falta de claves foráneas en relaciones importantes
--   10. Falta de CHECK constraints de validación
--
-- ============================================================================
-- NOTAS IMPORTANTES
-- ============================================================================
--
-- 1. CHECK CONSTRAINTS:
--    - MySQL 8.0.16+ soporta CHECK constraints completamente
--    - Versiones anteriores (5.7, 8.0.15) las crean pero no las validan
--    - Si usas versión antigua, implementar validación en la aplicación
--
-- 2. ÍNDICES:
--    - Los índices mejoran SELECT pero ralentizan INSERT/UPDATE ligeramente
--    - Balance: más índices = consultas más rápidas, escrituras más lentas
--    - Los índices aquí propuestos están basados en consultas frecuentes del código
--
-- 3. PARTICIONADO:
--    - Considerar particionado en auditoria_admin si crece mucho (> 1M registros)
--    - Particionado por fecha: PARTITION BY RANGE (YEAR(fecha_hora))
--
-- 4. ARCHIVO DE DATOS:
--    - Considerar archivar registros antiguos de auditoria_admin (> 1 año)
--    - Crear tabla auditoria_admin_archivo para datos históricos
--
-- ============================================================================
-- ALTERNATIVA: TABLA NORMALIZADA PARA CARACTERÍSTICAS (OPCIONAL)
-- ============================================================================
--
-- Si decides normalizar caracteristicas JSON a una tabla separada, aquí está
-- la estructura recomendada. Úsala SOLO si realmente necesitas consultas
-- complejas y reportes por características.
--
-- ⚠️ IMPORTANTE: Esta normalización es OPCIONAL. Solo normaliza si:
--    1. Necesitas filtrar/buscar sorteos por características específicas
--    2. Necesitas reportes estadísticos (ej: "¿Cuántos sorteos tienen garantía X?")
--    3. Tienes volumen muy alto (> 100,000 sorteos)
--    4. Consultas por características son frecuentes en tu aplicación
--
-- Si solo muestras características, MANTÉN JSON (más simple y eficiente).
--
-- ============================================================================
-- ESTRUCTURA PROPUESTA (SOLO SI NORMALIZAS)
-- ============================================================================
--
-- -- Primero, decidir si eliminar o mantener JSON según estrategia:
-- -- OPCIÓN 1: Eliminar JSON completamente (solo tabla normalizada)
-- -- ALTER TABLE sorteos DROP COLUMN caracteristicas;
--
-- -- OPCIÓN 2: Mantener ambos (JSON para mostrar, tabla para consultar)
-- -- No eliminar JSON, mantener ambos campos (ESTRATEGIA HÍBRIDA)
--
-- -- Crear tabla normalizada
-- CREATE TABLE sorteos_caracteristicas (
--     id_caracteristica INT PRIMARY KEY AUTO_INCREMENT,
--     id_sorteo INT NOT NULL,
--     nombre_caracteristica VARCHAR(100) NOT NULL,
--     -- COMENTARIO: Nombre de la característica (ej: "velocidad_maxima", "garantia")
--     
--     valor_caracteristica VARCHAR(255) NOT NULL,
--     -- COMENTARIO: Valor como string (ej: "320 km/h", "2 años")
--     
--     tipo_dato ENUM('string', 'number', 'boolean') DEFAULT 'string',
--     -- COMENTARIO: Tipo de dato para validación y conversión
--     
--     valor_numerico DECIMAL(10, 2) NULL,
--     -- COMENTARIO: Opcional: valor numérico extraído para comparaciones
--     -- Ejemplo: "320" de "320 km/h" para poder hacer WHERE valor_numerico > 300
--     
--     orden INT DEFAULT 0,
--     -- COMENTARIO: Orden de visualización de las características
--     
--     fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--     
--     -- CLAVES FORÁNEAS
--     FOREIGN KEY (id_sorteo) REFERENCES sorteos(id_sorteo) ON DELETE CASCADE,
--     
--     -- CONSTRAINTS
--     UNIQUE KEY uk_sorteo_nombre (id_sorteo, nombre_caracteristica),
--     -- COMENTARIO: Un sorteo no puede tener dos características con el mismo nombre
--     
--     -- ÍNDICES
--     INDEX idx_sorteo (id_sorteo),
--     INDEX idx_nombre_caracteristica (nombre_caracteristica),
--     INDEX idx_nombre_valor (nombre_caracteristica, valor_caracteristica),
--     INDEX idx_valor_numerico (nombre_caracteristica, valor_numerico),
--     INDEX idx_sorteo_orden (id_sorteo, orden)
-- ) ENGINE=InnoDB;
--
-- ============================================================================
-- COMPARACIÓN: JSON vs TABLA NORMALIZADA (PROS Y CONTRAS)
-- ============================================================================
--
-- ESCENARIO 1: Mostrar características de un sorteo
-- --------------------------------------------------
-- JSON: ⚡ MUY RÁPIDO (1 query, sin JOIN)
-- Tabla: 🐢 MÁS LENTO (JOIN, múltiples filas)
-- VEREDICTO: JSON GANA
--
-- ESCENARIO 2: Buscar sorteos por característica
-- --------------------------------------------------
-- JSON: 🐢 LENTO (no indexable, table scan)
-- Tabla: ⚡ RÁPIDO (usando índices)
-- VEREDICTO: TABLA NORMALIZADA GANA
--
-- ESCENARIO 3: Reportes estadísticos
-- --------------------------------------------------
-- JSON: 🐢 MUY LENTO (table scan completo)
-- Tabla: ⚡ MUY RÁPIDO (índice, agregación eficiente)
-- VEREDICTO: TABLA NORMALIZADA GANA
--
-- ESCENARIO 4: Insertar/Actualizar características
-- --------------------------------------------------
-- JSON: ✅ SIMPLE (1 query)
-- Tabla: ❌ MÁS COMPLEJO (múltiples queries)
-- VEREDICTO: JSON GANA
--
-- ============================================================================
-- ESTRATEGIA HÍBRIDA (LO MEJOR DE AMBOS MUNDOS)
-- ============================================================================
--
-- Mantener JSON Y tabla normalizada:
--   - JSON: Para mostrar rápidamente
--   - Tabla: Para consultas y reportes eficientes
--   - Lógica: Guardar en ambas estructuras al crear/actualizar
--
-- VENTAJAS: Lo mejor de ambos mundos
-- DESVENTAJAS: Duplicación de datos, más complejidad, posibilidad de inconsistencias
--
-- ============================================================================
-- RECOMENDACIÓN FINAL BASADA EN TU CÓDIGO ACTUAL
-- ============================================================================
--
-- Análisis de tu código actual:
--   ✅ NO usas JSON_EXTRACT ni JSON_CONTAINS (no consultas por características)
--   ✅ Solo muestras características (no filtras/buscas)
--   ✅ Volumen actual: Bajo/Medio (< 10,000 sorteos probablemente)
--
-- RECOMENDACIÓN: MANTENER JSON
--
-- Razones:
--   1. Tu código actual NO necesita consultas complejas
--   2. JSON es más simple para tu caso de uso actual
--   3. Si en el FUTURO necesitas filtrar/reportes, puedes migrar entonces
--   4. "Premature optimization is the root of all evil" - No optimices antes de necesitarlo
--
-- Cuándo cambiar a tabla normalizada:
--   - Cuando implementes búsqueda por características
--   - Cuando necesites reportes estadísticos por características
--   - Cuando el volumen sea muy alto (> 100,000 sorteos)
--   - Cuando las consultas JSON sean lentas (mide primero)
--
-- ============================================================================
