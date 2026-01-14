<?php
/**
 * Script para actualizar las contraseñas de los usuarios existentes
 * Convierte las contraseñas de texto plano a hashes reales
 * 
 * Acceder desde el navegador: http://localhost/SorteosWeb-conLoDeJorge/SorteosWeb-main/php/cliente/actualizar_contraseñas_usuarios.php
 */

require_once __DIR__ . '/config/database.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Actualización de Contraseñas de Usuarios</h1>";

try {
    $db = getDB();
    
    // Definir las contraseñas para cada usuario
    // IMPORTANTE: Estas son las contraseñas que podrás usar para hacer login
    $usuarios = [
        'admin@sorteos.com' => [
            'password' => 'admin123',
            'rol' => 'Administrador'
        ],
        'lucia.f@email.com' => [
            'password' => 'lucia123',
            'rol' => 'Cliente'
        ],
        'roberto.p@email.com' => [
            'password' => 'roberto123',
            'rol' => 'Cliente'
        ],
        'maria.l@email.com' => [
            'password' => 'maria123',
            'rol' => 'Cliente'
        ]
    ];
    
    echo "<h2>Actualizando contraseñas...</h2>";
    echo "<ul>";
    
    $actualizados = 0;
    $noEncontrados = [];
    
    foreach ($usuarios as $email => $datos) {
        $password = $datos['password'];
        $rolEsperado = $datos['rol'];
        
        // Verificar si el usuario existe
        $stmt = $db->prepare("SELECT id_usuario, email, primer_nombre, password_hash FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();
        
        if ($usuario) {
            // Generar hash real de la contraseña (cada vez genera uno diferente pero válido)
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            // Verificar el hash antes de guardarlo
            if (!password_verify($password, $passwordHash)) {
                echo "<li style='color: red;'>❌ Error generando hash para {$email}</li>";
                continue;
            }
            
            // Actualizar la contraseña
            $stmt = $db->prepare("UPDATE usuarios SET password_hash = :password_hash WHERE email = :email");
            $stmt->execute([
                ':password_hash' => $passwordHash,
                ':email' => $email
            ]);
            
            // Verificar que se actualizó correctamente
            $stmt = $db->prepare("SELECT password_hash FROM usuarios WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $usuarioActualizado = $stmt->fetch();
            
            if ($usuarioActualizado && password_verify($password, $usuarioActualizado['password_hash'])) {
                echo "<li style='color: green;'>✅ <strong>{$usuario['primer_nombre']}</strong> ({$email}) - Contraseña actualizada: <strong>{$password}</strong> (Rol: {$rolEsperado})</li>";
                $actualizados++;
            } else {
                echo "<li style='color: red;'>❌ Error verificando hash para {$email}</li>";
            }
        } else {
            $noEncontrados[] = $email;
            echo "<li style='color: orange;'>⚠️ Usuario no encontrado: {$email}</li>";
        }
    }
    
    echo "</ul>";
    
    echo "<hr>";
    echo "<h2>Resumen</h2>";
    echo "<p><strong>Usuarios actualizados:</strong> {$actualizados}</p>";
    
    if (!empty($noEncontrados)) {
        echo "<p style='color: orange;'><strong>Usuarios no encontrados:</strong> " . implode(', ', $noEncontrados) . "</p>";
    }
    
    echo "<hr>";
    echo "<h2>Credenciales para Login</h2>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%; max-width: 700px;'>";
    echo "<tr style='background-color: #f0f0f0;'><th>Email</th><th>Contraseña</th><th>Rol</th><th>Nota</th></tr>";
    
    $credenciales = [
        ['admin@sorteos.com', 'admin123', 'Administrador', 'Usa esta cuenta para acceder como administrador'],
        ['lucia.f@email.com', 'lucia123', 'Cliente', 'Lucía Fernández'],
        ['roberto.p@email.com', 'roberto123', 'Cliente', 'Roberto Pérez'],
        ['maria.l@email.com', 'maria123', 'Cliente', 'María López']
    ];
    
    foreach ($credenciales as $credencial) {
        echo "<tr>";
        echo "<td><strong>{$credencial[0]}</strong></td>";
        echo "<td><code style='background: #f5f5f5; padding: 3px 6px; border-radius: 3px;'>{$credencial[1]}</code></td>";
        echo "<td>{$credencial[2]}</td>";
        echo "<td style='font-size: 0.9em; color: #666;'>{$credencial[3]}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<hr>";
    echo "<p><a href='InicioSesion.php' style='display: inline-block; padding: 10px 20px; background-color: #2463eb; color: white; text-decoration: none; border-radius: 5px;'>Ir a Inicio de Sesión</a></p>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Error al actualizar contraseñas</h2>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Error inesperado</h2>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
