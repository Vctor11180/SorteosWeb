<?php
/**
 * API para actualizar la contraseña del usuario
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
    
    // Obtener datos del POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Si no hay JSON, intentar obtener de POST normal
    if (!$input) {
        $input = $_POST;
    }
    
    $passwordActual = $input['password_actual'] ?? '';
    $passwordNueva = $input['password_nueva'] ?? '';
    
    // Validaciones básicas
    if (empty($passwordActual)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'La contraseña actual es requerida'
        ]);
        exit;
    }
    
    if (empty($passwordNueva)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'La nueva contraseña es requerida'
        ]);
        exit;
    }
    
    if (strlen($passwordNueva) < 6) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'La nueva contraseña debe tener al menos 6 caracteres'
        ]);
        exit;
    }
    
    // Obtener el hash de la contraseña actual del usuario
    $stmt = $db->prepare("SELECT password_hash FROM usuarios WHERE id_usuario = :usuario_id");
    $stmt->execute([':usuario_id' => $usuarioId]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Usuario no encontrado'
        ]);
        exit;
    }
    
    // Verificar la contraseña actual
    if (!password_verify($passwordActual, $usuario['password_hash'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'La contraseña actual es incorrecta'
        ]);
        exit;
    }
    
    // Verificar que la nueva contraseña sea diferente a la actual
    if (password_verify($passwordNueva, $usuario['password_hash'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'La nueva contraseña debe ser diferente a la actual'
        ]);
        exit;
    }
    
    // Generar nuevo hash para la contraseña
    $nuevoHash = password_hash($passwordNueva, PASSWORD_DEFAULT);
    
    // Actualizar la contraseña en la base de datos
    $stmt = $db->prepare("UPDATE usuarios SET password_hash = :password_hash WHERE id_usuario = :usuario_id");
    $stmt->execute([
        ':password_hash' => $nuevoHash,
        ':usuario_id' => $usuarioId
    ]);
    
    $filasAfectadas = $stmt->rowCount();
    error_log("API actualizar password - Filas afectadas: $filasAfectadas");
    
    if ($filasAfectadas === 0) {
        error_log("API actualizar password - ADVERTENCIA: No se actualizó ninguna fila. Usuario ID: $usuarioId");
    }
    
    // Retornar éxito
    echo json_encode([
        'success' => true,
        'message' => 'Contraseña actualizada exitosamente'
    ]);
    
} catch (PDOException $e) {
    error_log("Error al actualizar contraseña (PDO): " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar la contraseña. Inténtalo más tarde.'
    ]);
} catch (Exception $e) {
    error_log("Error general al actualizar contraseña: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error inesperado. Inténtalo más tarde.'
    ]);
}
?>
