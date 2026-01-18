<?php
/**
 * Archivo de prueba para verificar la conexión a la base de datos
 * Accede a: http://localhost/SorteosWeb/test_conexion.php
 */

// Probar conexión del cliente (PDO)
echo "<h2>Prueba de Conexión - Cliente (PDO)</h2>";
try {
    // Guardar estado de constantes antes de incluir
    $constantsBefore = get_defined_constants(true)['user'] ?? [];
    
    require_once 'php/cliente/config/database.php';
    $db = getDB();
    echo "✅ <strong>Conexión exitosa</strong> usando PDO<br>";
    echo "Base de datos: sorteo_schema<br><br>";
    
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
    // Usar las constantes ya definidas o definir nuevas si no existen
    $db_host = defined('DB_HOST') ? DB_HOST : 'localhost';
    $db_user = defined('DB_USER') ? DB_USER : 'root';
    $db_pass = defined('DB_PASS') ? DB_PASS : '';
    $db_name = defined('DB_NAME') ? DB_NAME : 'sorteo_schema';
    $db_charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
    
    // Crear conexión directa sin incluir el archivo de configuración completo
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn && !$conn->connect_error) {
        $conn->set_charset($db_charset);
        echo "✅ <strong>Conexión exitosa</strong> usando mysqli<br>";
        echo "Base de datos: sorteo_schema<br><br>";
        
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
        
        $conn->close();
    } else {
        echo "❌ <strong>Error de conexión (mysqli):</strong> " . ($conn ? $conn->connect_error : "No se pudo crear la conexión") . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ <strong>Error de conexión (mysqli):</strong> " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Sección de Credenciales de Acceso
echo "<h2>🔑 Credenciales de Acceso - Usuarios de Prueba</h2>";
try {
    $db_host = defined('DB_HOST') ? DB_HOST : 'localhost';
    $db_user = defined('DB_USER') ? DB_USER : 'root';
    $db_pass = defined('DB_PASS') ? DB_PASS : '';
    $db_name = defined('DB_NAME') ? DB_NAME : 'sorteo_schema';
    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn && !$conn->connect_error) {
        $conn->set_charset('utf8mb4');
        
        // Hash bcrypt conocido que corresponde a "password"
        $hashPrueba = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
        $passwordPrueba = 'password'; // Contraseña en texto plano para ese hash
        
        echo "<div style='background: #1e2433; padding: 20px; border-radius: 8px; margin: 20px 0; color: #fff;'>";
        echo "<h3 style='color: #2463eb; margin-top: 0;'>👤 Usuarios Específicos de Prueba</h3>";
        
        // Buscar usuarios específicos de prueba
        $usuariosPrueba = ['adminprueba@prueba.com', 'userprueba@prueba.com', 'maria@gmail.com', 'carlos@hotmail.com', 'luis.bn@email.com'];
        
        foreach ($usuariosPrueba as $emailPrueba) {
            $stmt = $conn->prepare("
                SELECT u.id_usuario, u.email, u.password_hash, u.primer_nombre, u.apellido_paterno, u.estado, r.nombre_rol
                FROM usuarios u
                INNER JOIN roles r ON u.id_rol = r.id_rol
                WHERE u.email = ?
            ");
            $stmt->bind_param("s", $emailPrueba);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $passwordTexto = 'Desconocida';
                // Verificar si el hash corresponde al hash de prueba conocido
                if ($row['password_hash'] === $hashPrueba || password_verify($passwordPrueba, $row['password_hash'])) {
                    $passwordTexto = $passwordPrueba;
                }
                
                echo "<div style='background: #282d39; padding: 15px; border-radius: 6px; margin: 10px 0; border-left: 4px solid #2463eb;'>";
                echo "<strong style='color: #2463eb;'>" . htmlspecialchars($row['nombre_rol']) . ":</strong><br>";
                echo "<strong>Email/Usuario:</strong> <code style='background: #161b26; padding: 4px 8px; border-radius: 4px; color: #4ade80;'>" . htmlspecialchars($row['email']) . "</code><br>";
                echo "<strong>Contraseña:</strong> <code style='background: #161b26; padding: 4px 8px; border-radius: 4px; color: #4ade80;'>" . htmlspecialchars($passwordTexto) . "</code><br>";
                echo "<strong>Nombre:</strong> " . htmlspecialchars($row['primer_nombre'] . ' ' . $row['apellido_paterno']) . "<br>";
                echo "<strong>Estado:</strong> <span style='color: " . ($row['estado'] === 'Activo' ? '#4ade80' : '#f87171') . ";'>" . htmlspecialchars($row['estado']) . "</span><br>";
                echo "</div>";
            } else {
                echo "<div style='color: #fbbf24; padding: 10px; background: #161b26; border-radius: 4px; margin: 10px 0;'>";
                echo "⚠️ Usuario <code>" . htmlspecialchars($emailPrueba) . "</code> no encontrado en la base de datos.";
                echo "</div>";
            }
            $stmt->close();
        }
        
        echo "</div>";
        
        // Mostrar todos los administradores disponibles
        echo "<div style='background: #1e2433; padding: 20px; border-radius: 8px; margin: 20px 0; color: #fff;'>";
        echo "<h3 style='color: #2463eb; margin-top: 0;'>👨‍💼 Todos los Administradores Disponibles</h3>";
        
        $stmt = $conn->prepare("
            SELECT u.id_usuario, u.email, u.password_hash, u.primer_nombre, u.apellido_paterno, u.estado, r.nombre_rol
            FROM usuarios u
            INNER JOIN roles r ON u.id_rol = r.id_rol
            WHERE r.nombre_rol = 'Administrador' AND u.estado = 'Activo'
            ORDER BY u.email
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $passwordTexto = 'Verificar manualmente';
                // Intentar verificar con password común
                $passwordsComunes = ['password', 'admin123', 'admin', '123456', 'password123'];
                foreach ($passwordsComunes as $pass) {
                    if (password_verify($pass, $row['password_hash'])) {
                        $passwordTexto = $pass;
                        break;
                    }
                }
                
                if ($passwordTexto === 'Verificar manualmente') {
                    // Si no coincide con ninguna común, verificar si es el hash conocido
                    if ($row['password_hash'] === $hashPrueba) {
                        $passwordTexto = $passwordPrueba;
                    } else {
                        $passwordTexto = '<span style="color: #fbbf24;">No identificada (revisar manualmente)</span>';
                    }
                }
                
                echo "<div style='background: #282d39; padding: 12px; border-radius: 6px; margin: 8px 0;'>";
                echo "<strong>Email:</strong> <code style='background: #161b26; padding: 3px 6px; border-radius: 3px; color: #4ade80;'>" . htmlspecialchars($row['email']) . "</code> | ";
                echo "<strong>Contraseña:</strong> <code style='background: #161b26; padding: 3px 6px; border-radius: 3px; color: #4ade80;'>" . $passwordTexto . "</code>";
                echo "</div>";
            }
        } else {
            echo "<p style='color: #fbbf24;'>No se encontraron administradores activos en la base de datos.</p>";
        }
        $stmt->close();
        
        echo "</div>";
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "<p style='color: #f87171;'>⚠️ Error al obtener credenciales: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><strong>💡 Nota:</strong> Las contraseñas mostradas son estimaciones basadas en hashes conocidos. Si no puedes iniciar sesión, verifica manualmente en la base de datos.</p>";
echo "<p><strong>Nota:</strong> Si ves errores, revisa la guía en <code>CONFIGURACION_XAMPP.md</code></p>";
?>
