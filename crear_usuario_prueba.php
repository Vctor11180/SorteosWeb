<?php
/**
 * Script para crear un usuario de prueba
 * Ejecutar una vez para crear un usuario de prueba en la base de datos
 * 
 * Acceder desde el navegador: http://localhost/SorteosWeb/crear_usuario_prueba.php
 */

require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');

try {
    $db = getDB();
    
    // Verificar si ya existe el usuario de prueba
    $stmt = $db->prepare("SELECT id_usuario FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => 'cliente@test.com']);
    $usuarioExistente = $stmt->fetch();
    
    if ($usuarioExistente) {
        echo "<h2>El usuario de prueba ya existe</h2>";
        echo "<p>Usuario ID: " . $usuarioExistente['id_usuario'] . "</p>";
        echo "<p>Email: cliente@test.com</p>";
        echo "<p>Contraseña: password123</p>";
        exit;
    }
    
    // Verificar que existan los roles
    $stmt = $db->prepare("SELECT id_rol FROM roles WHERE nombre_rol = 'Cliente'");
    $stmt->execute();
    $rolCliente = $stmt->fetch();
    
    if (!$rolCliente) {
        // Crear roles si no existen
        $db->exec("INSERT INTO roles (nombre_rol) VALUES ('Administrador'), ('Cliente')");
        $stmt = $db->prepare("SELECT id_rol FROM roles WHERE nombre_rol = 'Cliente'");
        $stmt->execute();
        $rolCliente = $stmt->fetch();
    }
    
    $idRolCliente = $rolCliente['id_rol'];
    
    // Crear usuario de prueba
    // Contraseña: "password123"
    $passwordHash = password_hash('password123', PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("
        INSERT INTO usuarios (
            primer_nombre,
            apellido_paterno,
            apellido_materno,
            fecha_nacimiento,
            email,
            password_hash,
            id_rol,
            estado,
            saldo_disponible
        ) VALUES (
            :primer_nombre,
            :apellido_paterno,
            :apellido_materno,
            :fecha_nacimiento,
            :email,
            :password_hash,
            :id_rol,
            :estado,
            :saldo_disponible
        )
    ");
    
    $stmt->execute([
        ':primer_nombre' => 'Juan',
        ':apellido_paterno' => 'Pérez',
        ':apellido_materno' => 'García',
        ':fecha_nacimiento' => '1990-01-01',
        ':email' => 'cliente@test.com',
        ':password_hash' => $passwordHash,
        ':id_rol' => $idRolCliente,
        ':estado' => 'Activo',
        ':saldo_disponible' => 1250.00
    ]);
    
    $idUsuario = $db->lastInsertId();
    
    echo "<h2 style='color: green;'>Usuario de prueba creado exitosamente</h2>";
    echo "<h3>Credenciales:</h3>";
    echo "<ul>";
    echo "<li><strong>Email:</strong> cliente@test.com</li>";
    echo "<li><strong>Contraseña:</strong> password123</li>";
    echo "<li><strong>ID Usuario:</strong> " . $idUsuario . "</li>";
    echo "<li><strong>Nombre:</strong> Juan Pérez García</li>";
    echo "<li><strong>Saldo:</strong> $1,250.00</li>";
    echo "</ul>";
    echo "<p><a href='InicioSesion.php'>Ir a Inicio de Sesión</a></p>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Error al crear usuario de prueba</h2>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Error inesperado</h2>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
