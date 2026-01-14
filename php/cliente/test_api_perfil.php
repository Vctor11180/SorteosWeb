<?php
/**
 * Script de prueba para verificar que el API de actualizar perfil funciona
 * Acceder desde: http://localhost/SorteosWeb-conLoDeJorge/SorteosWeb-main/php/cliente/test_api_perfil.php
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';

echo "<h1>Test API Actualizar Perfil</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #1a1a1a; color: #fff; }
    .success { color: #0bda62; }
    .error { color: #ff4444; }
    .info { color: #4da6ff; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #444; padding: 10px; text-align: left; }
    th { background: #333; }
    button { background: #0bda62; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
    button:hover { background: #0aa050; }
    pre { background: #222; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>";

try {
    $db = getDB();
    
    // Verificar sesión
    echo "<h2>1. Verificar Sesión</h2>";
    if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
        echo "<p class='success'>✅ Sesión activa</p>";
        echo "<p>Usuario ID: " . ($_SESSION['usuario_id'] ?? 'NO DEFINIDO') . "</p>";
        echo "<p>Usuario Email: " . ($_SESSION['usuario_email'] ?? 'NO DEFINIDO') . "</p>";
        echo "<p>Usuario Nombre: " . ($_SESSION['usuario_nombre'] ?? 'NO DEFINIDO') . "</p>";
    } else {
        echo "<p class='error'>❌ No hay sesión activa. Por favor, <a href='InicioSesion.php' style='color: #4da6ff;'>inicia sesión</a> primero.</p>";
        exit;
    }
    
    $usuarioId = $_SESSION['usuario_id'];
    
    // Obtener datos actuales del usuario
    echo "<h2>2. Datos Actuales del Usuario</h2>";
    $stmt = $db->prepare("
        SELECT 
            id_usuario,
            primer_nombre,
            segundo_nombre,
            apellido_paterno,
            apellido_materno,
            email,
            telefono
        FROM usuarios 
        WHERE id_usuario = :usuario_id
    ");
    $stmt->execute([':usuario_id' => $usuarioId]);
    $usuarioActual = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuarioActual) {
        echo "<table>";
        echo "<tr><th>Campo</th><th>Valor Actual</th></tr>";
        echo "<tr><td>ID Usuario</td><td>" . htmlspecialchars($usuarioActual['id_usuario']) . "</td></tr>";
        echo "<tr><td>Primer Nombre</td><td>" . htmlspecialchars($usuarioActual['primer_nombre']) . "</td></tr>";
        echo "<tr><td>Segundo Nombre</td><td>" . htmlspecialchars($usuarioActual['segundo_nombre'] ?? 'NULL') . "</td></tr>";
        echo "<tr><td>Apellido Paterno</td><td>" . htmlspecialchars($usuarioActual['apellido_paterno']) . "</td></tr>";
        echo "<tr><td>Apellido Materno</td><td>" . htmlspecialchars($usuarioActual['apellido_materno']) . "</td></tr>";
        echo "<tr><td>Email</td><td>" . htmlspecialchars($usuarioActual['email']) . "</td></tr>";
        echo "<tr><td>Teléfono</td><td>" . htmlspecialchars($usuarioActual['telefono'] ?? 'NULL') . "</td></tr>";
        echo "</table>";
    } else {
        echo "<p class='error'>❌ Usuario no encontrado en la base de datos</p>";
        exit;
    }
    
    // Probar actualización si se envía el formulario
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_update'])) {
        echo "<h2>3. Probar Actualización</h2>";
        
        $nombreTest = trim($_POST['nombre_test'] ?? '');
        $emailTest = trim($_POST['email_test'] ?? '');
        $telefonoTest = trim($_POST['telefono_test'] ?? '');
        
        if (empty($nombreTest) || empty($emailTest)) {
            echo "<p class='error'>❌ Nombre y email son requeridos</p>";
        } else {
            // Separar nombre
            $partesNombre = array_filter(explode(' ', $nombreTest), function($parte) {
                return trim($parte) !== '';
            });
            $partesNombre = array_values($partesNombre);
            
            $primerNombre = $partesNombre[0] ?? '';
            $segundoNombre = isset($partesNombre[1]) && !empty($partesNombre[1]) ? $partesNombre[1] : null;
            $apellidoPaterno = isset($partesNombre[2]) ? $partesNombre[2] : (isset($partesNombre[1]) ? $partesNombre[1] : '');
            $apellidoMaterno = isset($partesNombre[3]) ? $partesNombre[3] : '';
            
            if (count($partesNombre) == 2) {
                $primerNombre = $partesNombre[0];
                $apellidoPaterno = $partesNombre[1];
                $segundoNombre = null;
                $apellidoMaterno = '';
            } elseif (count($partesNombre) == 3) {
                $primerNombre = $partesNombre[0];
                $apellidoPaterno = $partesNombre[1];
                $apellidoMaterno = $partesNombre[2];
                $segundoNombre = null;
            }
            
            if (empty($apellidoPaterno)) {
                $apellidoPaterno = $primerNombre;
            }
            
            echo "<p class='info'>Intentando actualizar con:</p>";
            echo "<pre>";
            echo "Primer Nombre: $primerNombre\n";
            echo "Segundo Nombre: " . ($segundoNombre ?? 'NULL') . "\n";
            echo "Apellido Paterno: $apellidoPaterno\n";
            echo "Apellido Materno: $apellidoMaterno\n";
            echo "Email: $emailTest\n";
            echo "Teléfono: " . ($telefonoTest ?: 'NULL') . "\n";
            echo "</pre>";
            
            $stmt = $db->prepare("
                UPDATE usuarios 
                SET 
                    primer_nombre = :primer_nombre,
                    segundo_nombre = :segundo_nombre,
                    apellido_paterno = :apellido_paterno,
                    apellido_materno = :apellido_materno,
                    email = :email,
                    telefono = :telefono
                WHERE id_usuario = :usuario_id
            ");
            
            $resultado = $stmt->execute([
                ':primer_nombre' => $primerNombre,
                ':segundo_nombre' => $segundoNombre,
                ':apellido_paterno' => $apellidoPaterno,
                ':apellido_materno' => !empty($apellidoMaterno) ? $apellidoMaterno : '',
                ':email' => $emailTest,
                ':telefono' => !empty($telefonoTest) ? $telefonoTest : null,
                ':usuario_id' => $usuarioId
            ]);
            
            $filasAfectadas = $stmt->rowCount();
            
            if ($resultado && $filasAfectadas > 0) {
                echo "<p class='success'>✅ Actualización exitosa. Filas afectadas: $filasAfectadas</p>";
                
                // Mostrar datos actualizados
                $stmt = $db->prepare("SELECT * FROM usuarios WHERE id_usuario = :usuario_id");
                $stmt->execute([':usuario_id' => $usuarioId]);
                $usuarioActualizado = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo "<h3>Datos Actualizados:</h3>";
                echo "<table>";
                echo "<tr><th>Campo</th><th>Valor Nuevo</th></tr>";
                echo "<tr><td>Primer Nombre</td><td>" . htmlspecialchars($usuarioActualizado['primer_nombre']) . "</td></tr>";
                echo "<tr><td>Segundo Nombre</td><td>" . htmlspecialchars($usuarioActualizado['segundo_nombre'] ?? 'NULL') . "</td></tr>";
                echo "<tr><td>Apellido Paterno</td><td>" . htmlspecialchars($usuarioActualizado['apellido_paterno']) . "</td></tr>";
                echo "<tr><td>Apellido Materno</td><td>" . htmlspecialchars($usuarioActualizado['apellido_materno']) . "</td></tr>";
                echo "<tr><td>Email</td><td>" . htmlspecialchars($usuarioActualizado['email']) . "</td></tr>";
                echo "<tr><td>Teléfono</td><td>" . htmlspecialchars($usuarioActualizado['telefono'] ?? 'NULL') . "</td></tr>";
                echo "</table>";
            } else {
                echo "<p class='error'>❌ No se actualizó ninguna fila. Resultado: " . ($resultado ? 'true' : 'false') . ", Filas afectadas: $filasAfectadas</p>";
            }
        }
    }
    
    // Formulario de prueba
    echo "<h2>4. Formulario de Prueba</h2>";
    echo "<form method='POST' style='background: #222; padding: 20px; border-radius: 10px; max-width: 500px;'>";
    echo "<input type='hidden' name='test_update' value='1'>";
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label style='display: block; margin-bottom: 5px;'>Nombre Completo:</label>";
    echo "<input type='text' name='nombre_test' value='" . htmlspecialchars($usuarioActual['primer_nombre'] . ' ' . ($usuarioActual['segundo_nombre'] ?? '') . ' ' . $usuarioActual['apellido_paterno'] . ' ' . ($usuarioActual['apellido_materno'] ?? '')) . "' style='width: 100%; padding: 8px; background: #333; border: 1px solid #444; color: white; border-radius: 5px;'>";
    echo "</div>";
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label style='display: block; margin-bottom: 5px;'>Email:</label>";
    echo "<input type='email' name='email_test' value='" . htmlspecialchars($usuarioActual['email']) . "' style='width: 100%; padding: 8px; background: #333; border: 1px solid #444; color: white; border-radius: 5px;'>";
    echo "</div>";
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label style='display: block; margin-bottom: 5px;'>Teléfono:</label>";
    echo "<input type='text' name='telefono_test' value='" . htmlspecialchars($usuarioActual['telefono'] ?? '') . "' style='width: 100%; padding: 8px; background: #333; border: 1px solid #444; color: white; border-radius: 5px;'>";
    echo "</div>";
    echo "<button type='submit'>Probar Actualización</button>";
    echo "</form>";
    
    echo "<hr>";
    echo "<p><a href='AjustesPefilCliente.php' style='color: #4da6ff;'>← Volver a Ajustes de Perfil</a></p>";
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Error PDO: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
