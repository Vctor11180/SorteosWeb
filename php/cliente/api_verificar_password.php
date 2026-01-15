<?php
/**
 * API para verificar la contraseña actual del usuario
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
        'valid' => false,
        'message' => 'No estás autenticado. Por favor, inicia sesión.'
    ]);
    exit;
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'valid' => false,
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
    
    $password = $input['password'] ?? '';
    
    // Validación básica
    if (empty($password)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'valid' => false,
            'message' => 'La contraseña es requerida'
        ]);
        exit;
    }
    
    // Obtener el hash de la contraseña del usuario
    $stmt = $db->prepare("SELECT password_hash FROM usuarios WHERE id_usuario = :usuario_id");
    $stmt->execute([':usuario_id' => $usuarioId]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'valid' => false,
            'message' => 'Usuario no encontrado'
        ]);
        exit;
    }
    
    // Verificar la contraseña
    $isValid = password_verify($password, $usuario['password_hash']);
    
    if ($isValid) {
        echo json_encode([
            'success' => true,
            'valid' => true,
            'message' => 'Contraseña correcta'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'valid' => false,
            'message' => 'La contraseña actual es incorrecta'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Error al verificar contraseña (PDO): " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'valid' => false,
        'message' => 'Error al verificar la contraseña. Inténtalo más tarde.'
    ]);
} catch (Exception $e) {
    error_log("Error general al verificar contraseña: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'valid' => false,
        'message' => 'Error inesperado. Inténtalo más tarde.'
    ]);
}
?>
