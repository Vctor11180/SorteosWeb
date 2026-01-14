<?php
/**
 * API para actualizar el perfil del usuario
 * Sistema de Sorteos Web
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurar headers para JSON
header('Content-Type: application/json; charset=utf-8');

// Verificar autenticación
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'No estás autenticado. Por favor, inicia sesión.'
    ]);
    exit;
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit;
}

// Incluir archivos necesarios
require_once __DIR__ . '/config/database.php';

try {
    $db = getDB();
    $usuarioId = $_SESSION['usuario_id'];
    
    // Log para debugging
    error_log("API actualizar perfil - Usuario ID: " . $usuarioId);
    error_log("API actualizar perfil - Raw input: " . file_get_contents('php://input'));
    
    // Obtener datos del POST
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    // Si no hay JSON, intentar obtener de POST normal
    if (!$input) {
        $input = $_POST;
        error_log("API actualizar perfil - Usando POST normal");
    } else {
        error_log("API actualizar perfil - Usando JSON");
    }
    
    error_log("API actualizar perfil - Input decodificado: " . print_r($input, true));
    
    $nombre = trim($input['nombre'] ?? '');
    $email = trim($input['email'] ?? '');
    $telefono = trim($input['telefono'] ?? '');
    // Nota: La dirección no se guarda en la BD porque no existe ese campo en la tabla usuarios
    
    error_log("API actualizar perfil - Datos recibidos - Nombre: $nombre, Email: $email, Teléfono: $telefono");
    
    // Validaciones básicas
    if (empty($nombre)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'El nombre es requerido'
        ]);
        exit;
    }
    
    if (empty($email)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'El email es requerido'
        ]);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'El formato del email no es válido'
        ]);
        exit;
    }
    
    // Verificar si el email ya está en uso por otro usuario
    $stmt = $db->prepare("SELECT id_usuario FROM usuarios WHERE email = :email AND id_usuario != :usuario_id");
    $stmt->execute([
        ':email' => $email,
        ':usuario_id' => $usuarioId
    ]);
    $emailExistente = $stmt->fetch();
    
    if ($emailExistente) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Este email ya está registrado por otro usuario'
        ]);
        exit;
    }
    
    // Separar nombre completo en partes
    $partesNombre = array_filter(explode(' ', $nombre), function($parte) {
        return trim($parte) !== '';
    });
    $partesNombre = array_values($partesNombre); // Reindexar array
    
    $primerNombre = $partesNombre[0] ?? '';
    $segundoNombre = isset($partesNombre[1]) && !empty($partesNombre[1]) ? $partesNombre[1] : null;
    $apellidoPaterno = isset($partesNombre[2]) ? $partesNombre[2] : (isset($partesNombre[1]) ? $partesNombre[1] : '');
    $apellidoMaterno = isset($partesNombre[3]) ? $partesNombre[3] : '';
    
    // Si solo hay 2 partes, asumir que es nombre y apellido
    if (count($partesNombre) == 2) {
        $primerNombre = $partesNombre[0];
        $apellidoPaterno = $partesNombre[1];
        $segundoNombre = null;
        $apellidoMaterno = '';
    } elseif (count($partesNombre) == 3) {
        // Si hay 3 partes: nombre, apellido paterno, apellido materno
        $primerNombre = $partesNombre[0];
        $apellidoPaterno = $partesNombre[1];
        $apellidoMaterno = $partesNombre[2];
        $segundoNombre = null;
    }
    
    // Asegurar que apellido_paterno no esté vacío
    if (empty($apellidoPaterno)) {
        $apellidoPaterno = $primerNombre;
    }
    
    error_log("API actualizar perfil - Nombre separado - Primer: $primerNombre, Segundo: " . ($segundoNombre ?? 'null') . ", Paterno: $apellidoPaterno, Materno: $apellidoMaterno");
    
    // Actualizar datos del usuario en la base de datos
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
    
    $params = [
        ':primer_nombre' => $primerNombre,
        ':segundo_nombre' => $segundoNombre,
        ':apellido_paterno' => $apellidoPaterno,
        ':apellido_materno' => !empty($apellidoMaterno) ? $apellidoMaterno : '',
        ':email' => $email,
        ':telefono' => !empty($telefono) ? $telefono : null,
        ':usuario_id' => $usuarioId
    ];
    
    error_log("API actualizar perfil - Parámetros del UPDATE: " . print_r($params, true));
    
    $resultado = $stmt->execute($params);
    
    $filasAfectadas = $stmt->rowCount();
    error_log("API actualizar perfil - Resultado execute: " . ($resultado ? 'true' : 'false'));
    error_log("API actualizar perfil - Filas afectadas: $filasAfectadas");
    
    if ($filasAfectadas === 0) {
        // Verificar si el usuario existe
        $stmtCheck = $db->prepare("SELECT id_usuario, primer_nombre, email FROM usuarios WHERE id_usuario = :usuario_id");
        $stmtCheck->execute([':usuario_id' => $usuarioId]);
        $usuarioExiste = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuarioExiste) {
            error_log("API actualizar perfil - ERROR: El usuario con ID $usuarioId no existe en la base de datos");
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Usuario no encontrado en la base de datos'
            ]);
            exit;
        } else {
            error_log("API actualizar perfil - ADVERTENCIA: No se actualizó ninguna fila aunque el usuario existe. Datos actuales: " . print_r($usuarioExiste, true));
            // Aún así retornar éxito si los datos son idénticos
        }
    } else {
        error_log("API actualizar perfil - ÉXITO: Se actualizaron $filasAfectadas fila(s)");
    }
    
    // Actualizar la sesión con los nuevos datos
    $nombreCompleto = trim(
        $primerNombre . ' ' . 
        ($segundoNombre ?? '') . ' ' . 
        $apellidoPaterno . ' ' . 
        ($apellidoMaterno ?? '')
    );
    $nombreCompleto = preg_replace('/\s+/', ' ', $nombreCompleto); // Limpiar espacios múltiples
    
    $_SESSION['usuario_nombre'] = $nombreCompleto;
    $_SESSION['usuario_email'] = $email;
    
    // Retornar éxito
    echo json_encode([
        'success' => true,
        'message' => 'Perfil actualizado exitosamente',
        'data' => [
            'nombre' => $nombreCompleto,
            'email' => $email,
            'telefono' => $telefono
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Error al actualizar perfil (PDO): " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar el perfil: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error general al actualizar perfil: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error inesperado: ' . $e->getMessage()
    ]);
}
?>
