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
