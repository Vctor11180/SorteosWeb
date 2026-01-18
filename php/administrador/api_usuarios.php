<?php
/**
 * API para gestionar acciones de usuarios (bloquear temporal, banear permanente, crear, editar)
 */

header('Content-Type: application/json');
require_once 'config.php';
require_once 'audit_helper.php';

$conn = getDBConnection();

// Solo permitir métodos POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos del cuerpo de la petición
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Acción requerida']);
    exit;
}

$action = $input['action'];
$userId = isset($input['userId']) ? intval($input['userId']) : 0;

// Validar userId solo si la acción no es crear_usuario
if ($action !== 'crear_usuario' && $userId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de usuario requerido']);
    exit;
}

// Validar que el usuario existe (si no es crear)
if ($action !== 'crear_usuario') {
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
    $stmt->close();
}

try {
    switch ($action) {
        case 'bloquear_temporal':
            $duracion = $input['duracion'] ?? null;
            $razon = $input['razon'] ?? null;
            
            $updateQuery = "UPDATE usuarios SET estado = 'Inactivo' WHERE id_usuario = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param('i', $userId);
            
            if ($stmt->execute()) {
                // Auditoría
                registrarAuditoria($conn, 'BANEAR_USUARIO', 'Usuarios', [
                    'id_usuario_afectado' => $userId,
                    'tipo' => 'Temporal',
                    'duracion' => $duracion,
                    'razon' => $razon
                ]);
                
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
                // Auditoría
                registrarAuditoria($conn, 'BANEAR_USUARIO', 'Usuarios', [
                    'id_usuario_afectado' => $userId,
                    'tipo' => 'Permanente',
                    'razon' => $razon
                ]);
                
                $stmt->close();
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuario baneado permanentemente',
                    'razon' => $razon
                ]);
            } else {
                $stmt->close();
                throw new Exception('Error al banear al usuario: ' . $conn->error);
            }
            break;
            
        case 'crear_usuario':
            $primer_nombre = trim($input['primer_nombre'] ?? '');
            $apellido_paterno = trim($input['apellido_paterno'] ?? '');
            $email = trim($input['email'] ?? '');
            $password = trim($input['password'] ?? '');
            $id_rol = intval($input['rol'] ?? 2);
            
            if (empty($primer_nombre) || empty($email) || empty($password)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
                exit;
            }
            
            // Verificar email único
            $check = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'El email ya existe']);
                exit;
            }
            
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $estado = 'Activo';
            
            $stmt = $conn->prepare("INSERT INTO usuarios (primer_nombre, apellido_paterno, apellido_materno, email, password_hash, id_rol, estado) VALUES (?, ?, '', ?, ?, ?, ?)");
            $stmt->bind_param("ssssis", $primer_nombre, $apellido_paterno, $email, $hash, $id_rol, $estado);
            
            if ($stmt->execute()) {
                $newId = $stmt->insert_id;
                // Auditoría
                registrarAuditoria($conn, 'CREAR_USUARIO', 'Usuarios', [
                    'id_nuevo' => $newId,
                    'email' => $email,
                    'rol' => $id_rol
                ]);

                echo json_encode(['success' => true, 'message' => 'Usuario creado exitosamente']);
            } else {
                throw new Exception("Error al crear usuario: " . $conn->error);
            }
            break;

        case 'editar_usuario':
            $nombre = trim($input['nombre'] ?? '');
            $email = trim($input['email'] ?? '');
            $telefono = trim($input['telefono'] ?? '');
            
            if (empty($nombre) || empty($email)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Nombre y email son obligatorios']);
                exit;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'El formato del email no es válido']);
                exit;
            }
            
            // Verificar que el email no esté en uso por otro
            $checkEmailQuery = "SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario != ?";
            $stmt = $conn->prepare($checkEmailQuery);
            $stmt->bind_param('si', $email, $userId);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows > 0) {
                $stmt->close();
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Este email ya está en uso por otro usuario']);
                exit;
            }
            $stmt->close();
            
            // Separar nombre
            $nombreParts = explode(' ', $nombre, 4);
            $primer_nombre = $nombreParts[0] ?? '';
            // Nota: segundo_nombre NO existe en esquema según fixes previos, así que lo ignoramos o unimos
            // Pero en api anterior trataba de usarlo. Vamos a simplificar usando solo primer y apellidos.
            // Si el esquema no tiene segundo_nombre, no debemos intentar guardarlo.
            // Asumiremos: primer nombre es part 0, apellido es resto.
            
            // Revisando schema: usuarios tiene primer_nombre, apellido_paterno, apellido_materno.
            // Lógica simple:
            $primer_nombre = $nombreParts[0];
            $apellido_paterno = $nombreParts[1] ?? '';
            $apellido_materno = isset($nombreParts[2]) ? implode(' ', array_slice($nombreParts, 2)) : '';

            $updateQuery = "UPDATE usuarios SET 
                            primer_nombre = ?, 
                            apellido_paterno = ?, 
                            apellido_materno = ?, 
                            email = ?, 
                            telefono = ?
                            WHERE id_usuario = ?";
            
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param('sssssi', 
                $primer_nombre,
                $apellido_paterno,
                $apellido_materno,
                $email,
                $telefono,
                $userId
            );
            
            if ($stmt->execute()) {
                // Auditoría
                registrarAuditoria($conn, 'EDITAR_USUARIO', 'Usuarios', [
                    'id_usuario_afectado' => $userId,
                    'cambios' => ['nombre' => $nombre, 'email' => $email]
                ]);

                $stmt->close();
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuario actualizado exitosamente'
                ]);
            } else {
                $stmt->close();
                throw new Exception('Error al actualizar el usuario: ' . $conn->error);
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
