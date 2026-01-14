<?php
/**
 * Archivo de prueba para verificar la conexión a la base de datos
 * Accede a: http://localhost/SorteosWeb-main/test_conexion.php
 */

// Probar conexión del cliente (PDO)
echo "<h2>Prueba de Conexión - Cliente (PDO)</h2>";
try {
    require_once 'php/cliente/config/database.php';
    $db = getDB();
    echo "✅ <strong>Conexión exitosa</strong> usando PDO<br>";
    echo "Base de datos: sorteos_schema<br><br>";
    
    // Probar consulta
    $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios");
    $result = $stmt->fetch();
    echo "Total de usuarios en la base de datos: " . $result['total'] . "<br>";
    
    // Listar tablas
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<br>Tablas encontradas (" . count($tables) . "):<br>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . $table . "</li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "❌ <strong>Error de conexión (PDO):</strong> " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Probar conexión del administrador (mysqli)
echo "<h2>Prueba de Conexión - Administrador (mysqli)</h2>";
try {
    require_once 'php/administrador/config.php';
    $conn = getDBConnection();
    
    if ($conn && !$conn->connect_error) {
        echo "✅ <strong>Conexión exitosa</strong> usando mysqli<br>";
        echo "Base de datos: sorteos_schema<br><br>";
        
        // Probar consulta
        $result = $conn->query("SELECT COUNT(*) as total FROM usuarios");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "Total de usuarios en la base de datos: " . $row['total'] . "<br>";
        }
        
        // Listar tablas
        $result = $conn->query("SHOW TABLES");
        if ($result) {
            echo "<br>Tablas encontradas (" . $result->num_rows . "):<br>";
            echo "<ul>";
            while ($row = $result->fetch_array()) {
                echo "<li>" . $row[0] . "</li>";
            }
            echo "</ul>";
        }
        
    } else {
        echo "❌ <strong>Error de conexión (mysqli):</strong> " . ($conn ? $conn->connect_error : "No se pudo crear la conexión") . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ <strong>Error de conexión (mysqli):</strong> " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><strong>Nota:</strong> Si ves errores, revisa la guía en <code>CONFIGURACION_XAMPP.md</code></p>";
?>
