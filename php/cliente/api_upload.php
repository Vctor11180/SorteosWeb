<?php
/**
 * API de Subida de Archivos (Comprobantes de Pago)
 * Sistema de Sorteos Web
 * 
 * Endpoints:
 * - POST ?action=upload_comprobante - Subir comprobante de pago
 */

// Desactivar display_errors para evitar output antes del JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Iniciar output buffering
ob_start();

// Configurar headers JSON primero
header('Content-Type: application/json; charset=utf-8');

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    ob_clean();
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'No autorizado. Debes iniciar sesión.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Obtener ID de usuario de la sesión
$usuarioId = $_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? null;
if (!$usuarioId) {
    ob_clean();
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'ID de usuario no encontrado en la sesión.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $action = $_GET['action'] ?? '';
    
    // Solo permitir métodos POST para subida de archivos
    if ($method !== 'POST') {
        ob_clean();
        sendError('Método no permitido. Solo se permite POST.', 405);
        exit;
    }
    
    switch ($action) {
        case 'upload_comprobante':
            ob_clean();
            uploadComprobante($usuarioId);
            break;
            
        default:
            ob_clean();
            sendError('Acción no válida', 400);
            break;
    }
    
} catch (Exception $e) {
    ob_clean();
    error_log("Error general en api_upload.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error del servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Sube un comprobante de pago
 */
function uploadComprobante($usuarioId) {
    try {
        // Verificar que se envió un archivo
        if (!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] !== UPLOAD_ERR_OK) {
            $errorMessage = 'No se recibió ningún archivo.';
            
            if (isset($_FILES['comprobante']['error'])) {
                switch ($_FILES['comprobante']['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $errorMessage = 'El archivo excede el tamaño máximo permitido.';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $errorMessage = 'El archivo se subió parcialmente.';
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $errorMessage = 'No se seleccionó ningún archivo.';
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $errorMessage = 'Error del servidor: falta carpeta temporal.';
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $errorMessage = 'Error del servidor: no se pudo escribir el archivo.';
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $errorMessage = 'Error del servidor: extensión bloqueada.';
                        break;
                }
            }
            
            sendError($errorMessage, 400);
            return;
        }
        
        $file = $_FILES['comprobante'];
        
        // Validar tipo de archivo
        $allowedTypes = [
            'image/png',
            'image/jpeg',
            'image/jpg',
            'application/pdf'
        ];
        
        $allowedExtensions = ['png', 'jpg', 'jpeg', 'pdf'];
        
        // Obtener tipo MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        // Obtener extensión del archivo original
        $originalName = $file['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        
        // Validar tipo MIME y extensión
        if (!in_array($mimeType, $allowedTypes) || !in_array($extension, $allowedExtensions)) {
            sendError('Tipo de archivo no permitido. Solo se permiten archivos PNG, JPG o PDF.', 400);
            return;
        }
        
        // Validar tamaño (2MB máximo)
        $maxSize = 2 * 1024 * 1024; // 2MB en bytes
        if ($file['size'] > $maxSize) {
            sendError('El archivo excede el tamaño máximo de 2MB.', 400);
            return;
        }
        
        // Validar tamaño mínimo (1KB)
        $minSize = 1024; // 1KB
        if ($file['size'] < $minSize) {
            sendError('El archivo es demasiado pequeño. Debe tener al menos 1KB.', 400);
            return;
        }
        
        // Crear directorio de uploads si no existe
        $uploadDir = __DIR__ . '/uploads/comprobantes/';
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                error_log("Error: No se pudo crear el directorio de uploads: $uploadDir");
                sendError('Error del servidor: no se pudo crear el directorio de almacenamiento.', 500);
                return;
            }
        }
        
        // Generar nombre único para el archivo
        // Formato: usuario_{id}_{timestamp}_{random}.{extension}
        $timestamp = time();
        $random = bin2hex(random_bytes(4));
        $uniqueFileName = "usuario_{$usuarioId}_{$timestamp}_{$random}.{$extension}";
        $filePath = $uploadDir . $uniqueFileName;
        
        // Mover archivo a la carpeta de destino
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            error_log("Error: No se pudo mover el archivo subido a: $filePath");
            sendError('Error del servidor: no se pudo guardar el archivo.', 500);
            return;
        }
        
        // Verificar que el archivo se guardó correctamente
        if (!file_exists($filePath)) {
            sendError('Error del servidor: el archivo no se guardó correctamente.', 500);
            return;
        }
        
        // Ruta relativa para guardar en BD (sin la ruta completa del servidor)
        $relativePath = 'uploads/comprobantes/' . $uniqueFileName;
        
        // Retornar éxito con información del archivo
        echo json_encode([
            'success' => true,
            'message' => 'Comprobante subido exitosamente',
            'data' => [
                'file_name' => $uniqueFileName,
                'original_name' => $originalName,
                'file_path' => $relativePath,
                'file_size' => $file['size'],
                'file_type' => $mimeType,
                'file_extension' => $extension,
                'upload_date' => date('Y-m-d H:i:s')
            ]
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        error_log("Error en uploadComprobante: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        sendError('Error al subir el comprobante: ' . $e->getMessage(), 500);
    }
}

/**
 * Envía una respuesta de error
 */
function sendError($message, $code = 400) {
    ob_clean();
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

?>
