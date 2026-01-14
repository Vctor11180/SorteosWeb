<?php
/**
 * Configuración de conexión a la base de datos
 * Base de datos: sorteos_schema
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Cambiar según tu configuración
define('DB_PASS', ''); // Cambiar según tu configuración
define('DB_NAME', 'sorteos_schema');
define('DB_CHARSET', 'utf8mb4');

/**
 * Establece la conexión a la base de datos
 * @return mysqli|false Retorna la conexión o false en caso de error
 */
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($conn->connect_error) {
                error_log("Error de conexión: " . $conn->connect_error);
                die("Error de conexión a la base de datos. Por favor, contacta al administrador.");
            }
            
            $conn->set_charset(DB_CHARSET);
        } catch (Exception $e) {
            error_log("Excepción de conexión: " . $e->getMessage());
            die("Error de conexión a la base de datos. Por favor, contacta al administrador.");
        }
    }
    
    return $conn;
}

/**
 * Cierra la conexión a la base de datos
 */
function closeDBConnection() {
    global $conn;
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de zona horaria
date_default_timezone_set('America/Mexico_City'); // Ajustar según tu zona horaria

/**
 * Registra una acción en la auditoría
 * @param mysqli $conn Conexión a la base de datos
 * @param int|null $id_usuario ID del usuario que realiza la acción (null para sistema o desconocido)
 * @param string $tipo_accion Tipo de acción: 'creacion_usuario', 'login_exitoso', 'login_fallido', 'edicion_usuario', 'creacion_sorteo', 'generacion_ganador', 'validacion_pago'
 * @param string $accion Descripción de la acción
 * @param string $recurso Recurso afectado (ej: "Sorteo #123", "user: email@example.com")
 * @param string $estado 'success' o 'error'
 * @param bool $es_alerta Si es true, marca como alerta (para login fallido, etc)
 * @return bool True si se registró correctamente, false en caso contrario
 */
function registrarAuditoria($conn, $id_usuario, $tipo_accion, $accion, $recurso, $estado = 'success', $es_alerta = false) {
    try {
        // Verificar si la tabla existe
        $checkTable = $conn->query("SHOW TABLES LIKE 'auditoria_acciones'");
        if ($checkTable->num_rows == 0) {
            // Crear la tabla si no existe
            $createTableSQL = "CREATE TABLE auditoria_acciones (
                id_log INT PRIMARY KEY AUTO_INCREMENT,
                id_usuario INT NULL,
                tipo_accion VARCHAR(50) NOT NULL,
                accion VARCHAR(255) NOT NULL,
                recurso VARCHAR(255) NOT NULL,
                estado ENUM('success', 'error') DEFAULT 'success',
                es_alerta BOOLEAN DEFAULT FALSE,
                ip_address VARCHAR(45),
                fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tipo_accion (tipo_accion),
                INDEX idx_estado (estado),
                INDEX idx_fecha_hora (fecha_hora),
                INDEX idx_es_alerta (es_alerta),
                FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if (!$conn->query($createTableSQL)) {
                error_log("Error creando tabla de auditoría: " . $conn->error);
                return false;
            }
        } else {
            // Verificar que la columna id_usuario permita NULL
            $checkColumn = $conn->query("SHOW COLUMNS FROM auditoria_acciones WHERE Field = 'id_usuario' AND Null = 'YES'");
            if ($checkColumn->num_rows == 0) {
                // Modificar la columna para permitir NULL
                $conn->query("ALTER TABLE auditoria_acciones MODIFY id_usuario INT NULL");
            }
        }
        
        // Obtener IP del cliente
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip_address = trim($ips[0]);
        }
        
        // Validar que id_usuario sea un entero válido o NULL
        if ($id_usuario !== null) {
            $id_usuario = intval($id_usuario);
            if ($id_usuario <= 0) {
                $id_usuario = null;
            }
        }
        
        // Insertar registro de auditoría
        $stmt = $conn->prepare("INSERT INTO auditoria_acciones 
            (id_usuario, tipo_accion, accion, recurso, estado, es_alerta, ip_address) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            error_log("Error preparando consulta de auditoría: " . $conn->error);
            return false;
        }
        
        // Convertir boolean a int para MySQL
        $es_alerta_int = $es_alerta ? 1 : 0;
        
        $stmt->bind_param("issssis", 
            $id_usuario, 
            $tipo_accion, 
            $accion, 
            $recurso, 
            $estado, 
            $es_alerta_int, 
            $ip_address
        );
        
        $result = $stmt->execute();
        
        if (!$result) {
            error_log("Error insertando en auditoría: " . $stmt->error);
            error_log("Datos: id_usuario=$id_usuario, tipo_accion=$tipo_accion, accion=$accion, recurso=$recurso");
        } else {
            error_log("Auditoría registrada exitosamente: $tipo_accion - $accion");
        }
        
        $stmt->close();
        return $result;
    } catch (Exception $e) {
        error_log("Excepción en registrarAuditoria: " . $e->getMessage());
        return false;
    }
}