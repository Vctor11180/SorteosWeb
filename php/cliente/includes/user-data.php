<?php
/**
 * Helper para obtener datos del usuario desde la base de datos
 * Sistema de Sorteos Web
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Obtiene todos los datos del usuario actual desde la base de datos
 * @return array|null Array con los datos del usuario o null si no está autenticado
 */
function obtenerDatosUsuarioCompletos() {
    // Verificar que el usuario esté autenticado
    if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
        error_log("obtenerDatosUsuarioCompletos - ERROR: Usuario no autenticado");
        return null;
    }

    // Verificar que tengamos el ID del usuario
    if (!isset($_SESSION['usuario_id'])) {
        error_log("obtenerDatosUsuarioCompletos - ERROR: No hay usuario_id en la sesión");
        return null;
    }

    try {
        // Intentar cargar desde config/database.php primero
        if (file_exists(__DIR__ . '/../config/database.php')) {
            require_once __DIR__ . '/../config/database.php';
        } else {
            // Si no existe, usar la ruta alternativa
            require_once __DIR__ . '/../../config/database.php';
        }
        $db = getDB();
        
        $usuarioId = $_SESSION['usuario_id'];
        error_log("obtenerDatosUsuarioCompletos - Consultando datos para usuario_id: " . $usuarioId);
        
        // Obtener todos los datos del usuario desde la base de datos
        $stmt = $db->prepare("
            SELECT 
                u.id_usuario,
                u.email,
                u.password_hash,
                u.primer_nombre,
                u.segundo_nombre,
                u.apellido_paterno,
                u.apellido_materno,
                u.fecha_nacimiento,
                u.telefono,
                u.saldo_disponible,
                u.avatar_url,
                u.estado,
                u.fecha_registro,
                r.id_rol,
                r.nombre_rol
            FROM usuarios u
            INNER JOIN roles r ON u.id_rol = r.id_rol
            WHERE u.id_usuario = :usuario_id
        ");
        
        $stmt->execute([':usuario_id' => $usuarioId]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            error_log("obtenerDatosUsuarioCompletos - ERROR: No se encontró usuario con ID: " . $usuarioId);
            return null;
        }
        
        error_log("obtenerDatosUsuarioCompletos - Usuario encontrado - ID: " . $usuario['id_usuario'] . ", Email: " . $usuario['email'] . ", Nombre: " . $usuario['primer_nombre']);
        
        // Construir nombre completo
        $nombreCompleto = trim(
            $usuario['primer_nombre'] . ' ' . 
            ($usuario['segundo_nombre'] ?? '') . ' ' . 
            $usuario['apellido_paterno'] . ' ' . 
            ($usuario['apellido_materno'] ?? '')
        );
        
        // Determinar tipo de usuario
        $tipoUsuario = ($usuario['nombre_rol'] === 'Administrador') ? 'Administrador' : 'Usuario Premium';
        
        // Determinar URL del avatar (usar placeholder si no existe)
        $avatarUrl = $usuario['avatar_url'] ?? null;
        if (empty($avatarUrl) || $avatarUrl === 'default_avatar.png') {
            // Usar un placeholder online en lugar de un archivo local
            $avatarUrl = 'https://lh3.googleusercontent.com/aida-public/AB6AXuAscTJ1Xcq7edw4JqzzGbgOvjdyQ9_nDg7kkxtlCQw51-EJsv1RJyDd9OAZC89eniVl2ujzIik6wgxd5FTvho_ak6ccsWrWelinVwXj6yQUdpPUXYUTJN0pSvhRh-smWf81cMQz40x4U3setrSFDsyX4KkfxOsHc6PnTND68lGw6JkA9B0ag_4fNu5s0Z9OMbq83llAZUv3xuo3s6VI1no110ozE88mRALnX-rhgavHoJxmYpvBcUxV7BtrJr_9Q0BlgvZQL2BXCFg';
        }
        
        // Actualizar la sesión con los datos más recientes
        $_SESSION['usuario_nombre'] = $nombreCompleto;
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_saldo'] = floatval($usuario['saldo_disponible']);
        $_SESSION['usuario_avatar'] = $avatarUrl;
        $_SESSION['usuario_rol'] = $usuario['nombre_rol'];
        $_SESSION['usuario_estado'] = $usuario['estado'];
        
        // Retornar array con todos los datos
        return [
            'id_usuario' => $usuario['id_usuario'],
            'nombre' => $nombreCompleto,
            'primer_nombre' => $usuario['primer_nombre'],
            'segundo_nombre' => $usuario['segundo_nombre'],
            'apellido_paterno' => $usuario['apellido_paterno'],
            'apellido_materno' => $usuario['apellido_materno'],
            'email' => $usuario['email'],
            'telefono' => $usuario['telefono'],
            'fecha_nacimiento' => $usuario['fecha_nacimiento'],
            'saldo' => floatval($usuario['saldo_disponible']),
            'avatar' => $avatarUrl,
            'estado' => $usuario['estado'],
            'rol' => $usuario['nombre_rol'],
            'tipoUsuario' => $tipoUsuario,
            'fecha_registro' => $usuario['fecha_registro']
        ];
        
    } catch (PDOException $e) {
        error_log("Error al obtener datos del usuario: " . $e->getMessage());
        return null;
    } catch (Exception $e) {
        error_log("Error general al obtener datos del usuario: " . $e->getMessage());
        return null;
    }
}

/**
 * Obtiene los datos del usuario de forma simplificada (para uso en JavaScript)
 * @return array Array con los datos básicos del usuario
 */
function obtenerDatosUsuarioParaJS() {
    $datos = obtenerDatosUsuarioCompletos();
    
    if (!$datos) {
        // Retornar valores por defecto si no hay datos
        return [
            'nombre' => 'Usuario',
            'tipoUsuario' => 'Usuario Premium',
            'email' => '',
            'saldo' => 0.00,
            'avatar' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAscTJ1Xcq7edw4JqzzGbgOvjdyQ9_nDg7kkxtlCQw51-EJsv1RJyDd9OAZC89eniVl2ujzIik6wgxd5FTvho_ak6ccsWrWelinVwXj6yQUdpPUXYUTJN0pSvhRh-smWf81cMQz40x4U3setrSFDsyX4KkfxOsHc6PnTND68lGw6JkA9B0ag_4fNu5s0Z9OMbq83llAZUv3xuo3s6VI1no110ozE88mRALnX-rhgavHoJxmYpvBcUxV7BtrJr_9Q0BlgvZQL2BXCFg'
        ];
    }
    
    return [
        'nombre' => $datos['nombre'],
        'tipoUsuario' => $datos['tipoUsuario'],
        'email' => $datos['email'],
        'saldo' => $datos['saldo'],
        'avatar' => $datos['avatar']
    ];
}
?>
