-- Base de datos para sistema de sorteos

-- 1. Gestión de Accesos y Roles
CREATE TABLE roles (
    id_rol INT PRIMARY KEY AUTO_INCREMENT,
    nombre_rol VARCHAR(20) NOT NULL UNIQUE -- 'Administrador', 'Cliente'
);

-- 2. Usuarios 
CREATE TABLE usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    primer_nombre VARCHAR(50) NOT NULL,
    segundo_nombre VARCHAR(50),
    apellido_paterno VARCHAR(50) NOT NULL,
    apellido_materno VARCHAR(50) NOT NULL,
    fecha_nacimiento DATE NOT NULL, 
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    saldo_disponible DECIMAL(12, 2) DEFAULT 0.00,
    avatar_url VARCHAR(255) DEFAULT 'default_avatar.png',
    notif_email BOOLEAN DEFAULT TRUE,
    estado ENUM('Activo', 'Baneado', 'Inactivo') DEFAULT 'Activo',
    id_rol INT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
) ENGINE=InnoDB;

-- 3. Sorteos
CREATE TABLE sorteos (
    id_sorteo INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT,
    precio_boleto DECIMAL(10, 2) NOT NULL,
    total_boletos_crear INT NOT NULL,
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME NOT NULL,
    imagen_url VARCHAR(255),
    estado ENUM('Borrador', 'Activo', 'Finalizado', 'Pausado') DEFAULT 'Borrador',
    id_creador INT NOT NULL,
    FOREIGN KEY (id_creador) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

-- 4. Boletos
CREATE TABLE boletos (
    id_boleto INT PRIMARY KEY AUTO_INCREMENT,
    id_sorteo INT NOT NULL,
    numero_boleto VARCHAR(10) NOT NULL,
    estado ENUM('Disponible', 'Reservado', 'Vendido') DEFAULT 'Disponible',
    id_usuario_actual INT NULL,
    fecha_reserva TIMESTAMP NULL,
    INDEX idx_sorteo_estado (id_sorteo, estado),
    FOREIGN KEY (id_sorteo) REFERENCES sorteos(id_sorteo) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario_actual) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

-- 5. Pagos y Transacciones
CREATE TABLE transacciones (
    id_transaccion INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    monto_total DECIMAL(10, 2) NOT NULL,
    metodo_pago ENUM('PayPal', 'Transferencia', 'Visa', 'Saldo Interno'),
    referencia_pago VARCHAR(100), 
    comprobante_url VARCHAR(255), 
    estado_pago ENUM('Pendiente', 'Completado', 'Fallido') DEFAULT 'Pendiente',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_validador INT NULL, 
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_validador) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

-- Relación de boletos por transacción
CREATE TABLE detalle_transaccion_boletos (
    id_detalle INT PRIMARY KEY AUTO_INCREMENT,
    id_transaccion INT NOT NULL,
    id_boleto INT NOT NULL,
    FOREIGN KEY (id_transaccion) REFERENCES transacciones(id_transaccion) ON DELETE CASCADE,
    FOREIGN KEY (id_boleto) REFERENCES boletos(id_boleto)
) ENGINE=InnoDB;

-- 6. Historial de Ganadores
CREATE TABLE ganadores (
    id_sorteo INT NOT NULL,
    id_usuario INT NOT NULL,
    id_boleto INT NOT NULL,
    premio_detalle VARCHAR(255),
    fecha_anuncio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    entregado BOOLEAN DEFAULT FALSE,
    UNIQUE (id_sorteo, id_boleto),
    FOREIGN KEY (id_sorteo) REFERENCES sorteos(id_sorteo),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_boleto) REFERENCES boletos(id_boleto)
) ENGINE=InnoDB;

-- 7. Soporte y Ayuda
CREATE TABLE soporte_tickets (
    id_ticket INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    asunto VARCHAR(150) NOT NULL,
    mensaje TEXT NOT NULL,
    prioridad ENUM('Baja', 'Media', 'Alta') DEFAULT 'Media',
    estado ENUM('Abierto', 'En Proceso', 'Cerrado') DEFAULT 'Abierto',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

-- 8. Seguridad y Auditoría
CREATE TABLE auditoria_admin (
    id_log INT PRIMARY KEY AUTO_INCREMENT,
    id_admin INT NOT NULL,
    accion VARCHAR(255) NOT NULL,
    modulo VARCHAR(50),
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_admin) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

-- 9. Marketing y Reportes
CREATE TABLE campanas_marketing (
    id_campana INT PRIMARY KEY AUTO_INCREMENT,
    red_social VARCHAR(100), 
    empresa VARCHAR(100),
    costo_inversion DECIMAL(10, 2),
    clics_generados INT DEFAULT 0,
    estado ENUM('Activa', 'Pausada', 'Finalizada') DEFAULT 'Activa',
    fecha_inicio DATE,
    fecha_fin DATE
) ENGINE=InnoDB;

-- 10. Historial de Movimientos de Saldo (Auditoría Financiera)
CREATE TABLE historial_saldos (
    id_historial INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    monto DECIMAL(12, 2) NOT NULL,
    tipo_movimiento ENUM('Carga', 'Compra', 'Premio', 'Retiro') NOT NULL,
    id_referencia_transaccion INT NULL, -- Opcional: link a la tabla transacciones
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;
