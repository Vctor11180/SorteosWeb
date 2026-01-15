<?php
/**
 * Configuración de conexión a la base de datos
 * Sistema de Sorteos Web
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'sorteo_schema');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Clase para manejar la conexión a la base de datos
 */
class Database {
    private static $instance = null;
    private $connection;
    
    /**
     * Constructor privado para implementar patrón Singleton
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // En producción, no mostrar el error completo por seguridad
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            die("Error de conexión a la base de datos. Por favor, contacte al administrador.");
        }
    }
    
    /**
     * Obtiene la instancia única de la conexión (Singleton)
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtiene la conexión PDO
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Previene la clonación de la instancia
     */
    private function __clone() {}
    
    /**
     * Previene la deserialización de la instancia
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Función helper para obtener la conexión rápidamente
 * @return PDO
 */
function getDB() {
    return Database::getInstance()->getConnection();
}

?>
