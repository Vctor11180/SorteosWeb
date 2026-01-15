<?php
/**
 * API para gestionar acciones de usuarios (bloquear temporal, banear permanente)
 */

header('Content-Type: application/json');
require_once 'config.php';

$conn = getDBConnection();

// Solo permitir métodos POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos del cuerpo de la petición
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['action']) || !isset($input['userId'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$action = $input['action'];
$userId = intval($input['userId']);

// Validar que el usuario existe
$checkQuery = "SELECT id_usuario, estado FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

try {
    switch ($action) {
        case 'bloquear_temporal':
            // Bloquear temporalmente (cambiar estado a Inactivo)
            $duracion = $input['duracion'] ?? null;
            $razon = $input['razon'] ?? null;
            
            $updateQuery = "UPDATE usuarios SET estado = 'Inactivo' WHERE id_usuario = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param('i', $userId);
            
            if ($stmt->execute()) {
                // Registrar en auditoría si existe la tabla
                if ($razon) {
                    // Aquí podrías insertar en una tabla de historial de bloqueos
                    // Por ahora solo actualizamos el estado
                }
                
                $stmt->close();
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuario bloqueado temporalmente',
                    'duracion' => $duracion
                ]);
            } else {
                $stmt->close();
                throw new Exception('Error al actualizar el estado del usuario');
            }
            break;
            
        case 'banear_permanente':
            // Banear permanentemente (cambiar estado a Baneado)
            $razon = $input['razon'] ?? '';
            
            if (empty($razon)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'La razón del baneo es obligatoria']);
                exit;
            }
            
            $updateQuery = "UPDATE usuarios SET estado = 'Baneado' WHERE id_usuario = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param('i', $userId);
            
            if ($stmt->execute()) {
                // Registrar en auditoría si existe la tabla
                // Aquí podrías insertar en una tabla de historial de baneos con la razón
                
                $stmt->close();
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuario baneado permanentemente',
                    'razon' => $razon
                ]);
            } else {
                $stmt->close();
                throw new Exception('Error al banear al usuario');
            }
            break;
            
        case 'editar_usuario':
            // Editar información del usuario
            $nombre = trim($input['nombre'] ?? '');
            $email = trim($input['email'] ?? '');
            $telefono = trim($input['telefono'] ?? '');
            
            if (empty($nombre) || empty($email)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Nombre y email son obligatorios']);
                exit;
            }
            
            // Validar formato de email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'El formato del email no es válido']);
                exit;
            }
            
            // Verificar que el email no esté en uso por otro usuario
            $checkEmailQuery = "SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario != ?";
            $stmt = $conn->prepare($checkEmailQuery);
            $stmt->bind_param('si', $email, $userId);
            $stmt->execute();
            $emailResult = $stmt->get_result();
            
            if ($emailResult->num_rows > 0) {
                $stmt->close();
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Este email ya está en uso por otro usuario']);
                exit;
            }
            $stmt->close();
            
            // Separar nombre completo en partes
            $nombreParts = explode(' ', $nombre, 4);
            $primer_nombre = $nombreParts[0] ?? '';
            $segundo_nombre = $nombreParts[1] ?? null;
            $apellido_paterno = $nombreParts[2] ?? '';
            $apellido_materno = $nombreParts[3] ?? '';
            
            // Si solo hay 2 partes, asumir que es nombre y apellido
            if (count($nombreParts) == 2) {
                $primer_nombre = $nombreParts[0];
                $apellido_paterno = $nombreParts[1];
                $segundo_nombre = null;
                $apellido_materno = '';
            }
            
            // Actualizar usuario
            $updateQuery = "UPDATE usuarios SET 
                            primer_nombre = ?, 
                            segundo_nombre = ?, 
                            apellido_paterno = ?, 
                            apellido_materno = ?, 
                            email = ?, 
                            telefono = ?
                            WHERE id_usuario = ?";
            
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param('ssssssi', 
                $primer_nombre,
                $segundo_nombre,
                $apellido_paterno,
                $apellido_materno,
                $email,
                $telefono,
                $userId
            );
            
            if ($stmt->execute()) {
                $stmt->close();
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuario actualizado exitosamente'
                ]);
            } else {
                $stmt->close();
                throw new Exception('Error al actualizar el usuario');
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en el servidor: ' . $e->getMessage()
    ]);
}

$conn->close();
?>

