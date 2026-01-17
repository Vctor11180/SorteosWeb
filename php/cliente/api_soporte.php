<?php
/**
 * API de Soporte para Clientes
 * Sistema de Sorteos Web
 * 
 * Endpoints:
 * - POST ?action=create_ticket - Crear un nuevo ticket de soporte
 * - GET ?action=get_my_tickets - Obtener todos los tickets del usuario
 * - GET ?action=get_ticket&id={id} - Obtener detalles de un ticket específico
 * - POST ?action=reply_ticket - Responder a un ticket
 * - POST ?action=close_ticket - Cerrar un ticket (solo el usuario puede cerrar sus propios tickets)
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
    // Incluir configuración de base de datos
    if (!file_exists(__DIR__ . '/config/database.php')) {
        throw new Exception('Archivo de configuración de base de datos no encontrado');
    }
    
    require_once __DIR__ . '/config/database.php';
    
    if (!function_exists('getDB')) {
        throw new Exception('Función getDB no está disponible');
    }
    
    $db = getDB();
    
    if (!$db) {
        throw new Exception('No se pudo establecer conexión con la base de datos');
    }
    
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'create_ticket':
            if ($method === 'POST') {
                ob_clean();
                createTicket($db, $usuarioId);
            } else {
                ob_clean();
                sendError('Método no permitido. Solo se permite POST.', 405);
            }
            break;
            
        case 'get_my_tickets':
            ob_clean();
            getMyTickets($db, $usuarioId);
            break;
            
        case 'get_ticket':
            $id = $_GET['id'] ?? null;
            if ($id) {
                ob_clean();
                getTicketDetails($db, $usuarioId, intval($id));
            } else {
                ob_clean();
                sendError('ID de ticket requerido', 400);
            }
            break;
            
        case 'reply_ticket':
            if ($method === 'POST') {
                ob_clean();
                replyTicket($db, $usuarioId);
            } else {
                ob_clean();
                sendError('Método no permitido. Solo se permite POST.', 405);
            }
            break;
            
        case 'close_ticket':
            if ($method === 'POST') {
                ob_clean();
                closeTicket($db, $usuarioId);
            } else {
                ob_clean();
                sendError('Método no permitido. Solo se permite POST.', 405);
            }
            break;
            
        default:
            ob_clean();
            sendError('Acción no válida', 400);
            break;
    }
    
} catch (Exception $e) {
    ob_clean();
    error_log("Error general en api_soporte.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error del servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Crea un nuevo ticket de soporte
 */
function createTicket($db, $usuarioId) {
    try {
        // Obtener datos del POST
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        
        if (!$input) {
            $input = $_POST;
        }
        
        $asunto = trim($input['asunto'] ?? '');
        $mensaje = trim($input['mensaje'] ?? '');
        $prioridad = trim($input['prioridad'] ?? 'Media');
        $categoria = trim($input['categoria'] ?? '');
        
        // Validaciones
        if (empty($asunto)) {
            sendError('El asunto es requerido', 400);
            return;
        }
        
        if (strlen($asunto) > 150) {
            sendError('El asunto no puede exceder 150 caracteres', 400);
            return;
        }
        
        if (empty($mensaje)) {
            sendError('El mensaje es requerido', 400);
            return;
        }
        
        if (strlen($mensaje) < 10) {
            sendError('El mensaje debe tener al menos 10 caracteres', 400);
            return;
        }
        
        // Validar prioridad
        $prioridadesPermitidas = ['Baja', 'Media', 'Alta'];
        if (!in_array($prioridad, $prioridadesPermitidas)) {
            $prioridad = 'Media';
        }
        
        // Determinar prioridad automática según categoría
        if (empty($categoria)) {
            $categoria = $asunto; // Usar asunto como categoría si no se proporciona
        }
        
        // Si el asunto contiene palabras clave, ajustar prioridad
        $asuntoLower = strtolower($asunto);
        if (stripos($asuntoLower, 'urgente') !== false || 
            stripos($asuntoLower, 'crítico') !== false ||
            stripos($asuntoLower, 'pago') !== false) {
            $prioridad = 'Alta';
        }
        
        // Verificar que el usuario existe y está activo
        $stmt = $db->prepare("SELECT id_usuario, estado FROM usuarios WHERE id_usuario = :usuario_id");
        $stmt->execute([':usuario_id' => $usuarioId]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            sendError('Usuario no encontrado', 404);
            return;
        }
        
        if ($usuario['estado'] !== 'Activo') {
            sendError('Tu cuenta está inactiva. Contacta al administrador.', 403);
            return;
        }
        
        // Crear el ticket
        $stmt = $db->prepare("
            INSERT INTO soporte_tickets (
                id_usuario,
                asunto,
                mensaje,
                prioridad,
                estado
            ) VALUES (
                :id_usuario,
                :asunto,
                :mensaje,
                :prioridad,
                'Abierto'
            )
        ");
        
        $stmt->execute([
            ':id_usuario' => $usuarioId,
            ':asunto' => $asunto,
            ':mensaje' => $mensaje,
            ':prioridad' => $prioridad
        ]);
        
        $idTicket = $db->lastInsertId();
        
        // Obtener el ticket creado con información adicional
        $stmt = $db->prepare("
            SELECT 
                t.id_ticket,
                t.id_usuario,
                t.asunto,
                t.mensaje,
                t.prioridad,
                t.estado,
                t.fecha_creacion,
                t.fecha_actualizacion,
                u.primer_nombre,
                u.apellido_paterno,
                u.email
            FROM soporte_tickets t
            INNER JOIN usuarios u ON t.id_usuario = u.id_usuario
            WHERE t.id_ticket = :id_ticket
        ");
        $stmt->execute([':id_ticket' => $idTicket]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$ticket) {
            sendError('Error al crear el ticket', 500);
            return;
        }
        
        // Retornar éxito
        echo json_encode([
            'success' => true,
            'message' => 'Ticket creado exitosamente',
            'data' => [
                'id_ticket' => intval($ticket['id_ticket']),
                'asunto' => $ticket['asunto'],
                'mensaje' => $ticket['mensaje'],
                'prioridad' => $ticket['prioridad'],
                'estado' => $ticket['estado'],
                'fecha_creacion' => $ticket['fecha_creacion'],
                'fecha_actualizacion' => $ticket['fecha_actualizacion'],
                'usuario_nombre' => trim($ticket['primer_nombre'] . ' ' . $ticket['apellido_paterno']),
                'usuario_email' => $ticket['email']
            ]
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        error_log("Error PDO en createTicket: " . $e->getMessage());
        sendError('Error de base de datos al crear el ticket: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        error_log("Error general en createTicket: " . $e->getMessage());
        sendError('Error al crear el ticket: ' . $e->getMessage(), 500);
    }
}

/**
 * Obtiene todos los tickets del usuario
 */
function getMyTickets($db, $usuarioId) {
    try {
        // Parámetros opcionales
        $estado = $_GET['estado'] ?? null;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
        
        // Construir query
        $query = "
            SELECT 
                t.id_ticket,
                t.asunto,
                t.mensaje,
                t.prioridad,
                t.estado,
                t.fecha_creacion,
                t.fecha_actualizacion,
                t.fecha_cierre,
                t.id_responsable,
                u_responsable.primer_nombre as responsable_nombre,
                u_responsable.apellido_paterno as responsable_apellido
            FROM soporte_tickets t
            LEFT JOIN usuarios u_responsable ON t.id_responsable = u_responsable.id_usuario
            WHERE t.id_usuario = :usuario_id
        ";
        
        $params = [':usuario_id' => $usuarioId];
        
        // Agregar filtro de estado si se proporciona
        if ($estado && in_array($estado, ['Abierto', 'En Proceso', 'Cerrado'])) {
            $query .= " AND t.estado = :estado";
            $params[':estado'] = $estado;
        }
        
        $query .= " ORDER BY t.fecha_creacion DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener total de tickets para paginación
        $countQuery = "
            SELECT COUNT(*) as total
            FROM soporte_tickets
            WHERE id_usuario = :usuario_id
        ";
        $countParams = [':usuario_id' => $usuarioId];
        
        if ($estado && in_array($estado, ['Abierto', 'En Proceso', 'Cerrado'])) {
            $countQuery .= " AND estado = :estado";
            $countParams[':estado'] = $estado;
        }
        
        $countStmt = $db->prepare($countQuery);
        $countStmt->execute($countParams);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Procesar tickets
        $ticketsProcesados = [];
        foreach ($tickets as $ticket) {
            $ticketsProcesados[] = [
                'id_ticket' => intval($ticket['id_ticket']),
                'asunto' => $ticket['asunto'],
                'mensaje' => substr($ticket['mensaje'], 0, 200) . (strlen($ticket['mensaje']) > 200 ? '...' : ''), // Preview
                'mensaje_completo' => $ticket['mensaje'],
                'prioridad' => $ticket['prioridad'],
                'estado' => $ticket['estado'],
                'fecha_creacion' => $ticket['fecha_creacion'],
                'fecha_actualizacion' => $ticket['fecha_actualizacion'],
                'fecha_cierre' => $ticket['fecha_cierre'],
                'responsable' => $ticket['id_responsable'] ? trim($ticket['responsable_nombre'] . ' ' . $ticket['responsable_apellido']) : null
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $ticketsProcesados,
            'pagination' => [
                'total' => intval($total),
                'limit' => $limit,
                'offset' => $offset,
                'has_more' => ($offset + $limit) < $total
            ]
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        error_log("Error PDO en getMyTickets: " . $e->getMessage());
        sendError('Error de base de datos al obtener tickets: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        error_log("Error general en getMyTickets: " . $e->getMessage());
        sendError('Error al obtener tickets: ' . $e->getMessage(), 500);
    }
}

/**
 * Obtiene los detalles de un ticket específico
 */
function getTicketDetails($db, $usuarioId, $idTicket) {
    try {
        // Obtener el ticket
        $stmt = $db->prepare("
            SELECT 
                t.id_ticket,
                t.id_usuario,
                t.asunto,
                t.mensaje,
                t.prioridad,
                t.estado,
                t.fecha_creacion,
                t.fecha_actualizacion,
                t.fecha_cierre,
                t.id_responsable,
                u.primer_nombre,
                u.apellido_paterno,
                u.email,
                u_responsable.primer_nombre as responsable_nombre,
                u_responsable.apellido_paterno as responsable_apellido,
                u_responsable.email as responsable_email
            FROM soporte_tickets t
            INNER JOIN usuarios u ON t.id_usuario = u.id_usuario
            LEFT JOIN usuarios u_responsable ON t.id_responsable = u_responsable.id_usuario
            WHERE t.id_ticket = :id_ticket
        ");
        $stmt->execute([':id_ticket' => $idTicket]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$ticket) {
            sendError('Ticket no encontrado', 404);
            return;
        }
        
        // Verificar que el ticket pertenece al usuario
        if (intval($ticket['id_usuario']) !== intval($usuarioId)) {
            sendError('No tienes permiso para ver este ticket', 403);
            return;
        }
        
        // Obtener respuestas del ticket (si existe tabla de respuestas)
        // Por ahora, solo retornamos el ticket principal
        // En el futuro se puede agregar tabla soporte_respuestas
        
        echo json_encode([
            'success' => true,
            'data' => [
                'id_ticket' => intval($ticket['id_ticket']),
                'asunto' => $ticket['asunto'],
                'mensaje' => $ticket['mensaje'],
                'prioridad' => $ticket['prioridad'],
                'estado' => $ticket['estado'],
                'fecha_creacion' => $ticket['fecha_creacion'],
                'fecha_actualizacion' => $ticket['fecha_actualizacion'],
                'fecha_cierre' => $ticket['fecha_cierre'],
                'usuario' => [
                    'id' => intval($ticket['id_usuario']),
                    'nombre' => trim($ticket['primer_nombre'] . ' ' . $ticket['apellido_paterno']),
                    'email' => $ticket['email']
                ],
                'responsable' => $ticket['id_responsable'] ? [
                    'id' => intval($ticket['id_responsable']),
                    'nombre' => trim($ticket['responsable_nombre'] . ' ' . $ticket['responsable_apellido']),
                    'email' => $ticket['responsable_email']
                ] : null
            ]
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        error_log("Error PDO en getTicketDetails: " . $e->getMessage());
        sendError('Error de base de datos al obtener el ticket: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        error_log("Error general en getTicketDetails: " . $e->getMessage());
        sendError('Error al obtener el ticket: ' . $e->getMessage(), 500);
    }
}

/**
 * Responde a un ticket (actualiza el mensaje del ticket)
 * NOTA: Por ahora actualiza el mensaje original. En el futuro se puede crear tabla soporte_respuestas
 */
function replyTicket($db, $usuarioId) {
    try {
        // Obtener datos del POST
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        
        if (!$input) {
            $input = $_POST;
        }
        
        $idTicket = isset($input['id_ticket']) ? intval($input['id_ticket']) : null;
        $respuesta = trim($input['respuesta'] ?? '');
        
        // Validaciones
        if (!$idTicket) {
            sendError('ID de ticket requerido', 400);
            return;
        }
        
        if (empty($respuesta)) {
            sendError('La respuesta es requerida', 400);
            return;
        }
        
        if (strlen($respuesta) < 10) {
            sendError('La respuesta debe tener al menos 10 caracteres', 400);
            return;
        }
        
        // Verificar que el ticket existe y pertenece al usuario
        $stmt = $db->prepare("
            SELECT id_ticket, id_usuario, estado, mensaje
            FROM soporte_tickets
            WHERE id_ticket = :id_ticket
        ");
        $stmt->execute([':id_ticket' => $idTicket]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$ticket) {
            sendError('Ticket no encontrado', 404);
            return;
        }
        
        if (intval($ticket['id_usuario']) !== intval($usuarioId)) {
            sendError('No tienes permiso para responder este ticket', 403);
            return;
        }
        
        if ($ticket['estado'] === 'Cerrado') {
            sendError('No puedes responder a un ticket cerrado', 400);
            return;
        }
        
        // Actualizar el mensaje del ticket (agregar la respuesta al mensaje original)
        $nuevoMensaje = $ticket['mensaje'] . "\n\n--- Respuesta del usuario (" . date('Y-m-d H:i:s') . ") ---\n" . $respuesta;
        
        $stmt = $db->prepare("
            UPDATE soporte_tickets
            SET mensaje = :mensaje,
                fecha_actualizacion = NOW(),
                estado = 'Abierto'
            WHERE id_ticket = :id_ticket
        ");
        $stmt->execute([
            ':mensaje' => $nuevoMensaje,
            ':id_ticket' => $idTicket
        ]);
        
        // Obtener el ticket actualizado
        $stmt = $db->prepare("
            SELECT 
                t.id_ticket,
                t.asunto,
                t.mensaje,
                t.estado,
                t.fecha_actualizacion
            FROM soporte_tickets t
            WHERE t.id_ticket = :id_ticket
        ");
        $stmt->execute([':id_ticket' => $idTicket]);
        $ticketActualizado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Respuesta enviada exitosamente',
            'data' => [
                'id_ticket' => intval($ticketActualizado['id_ticket']),
                'estado' => $ticketActualizado['estado'],
                'fecha_actualizacion' => $ticketActualizado['fecha_actualizacion']
            ]
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        error_log("Error PDO en replyTicket: " . $e->getMessage());
        sendError('Error de base de datos al responder el ticket: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        error_log("Error general en replyTicket: " . $e->getMessage());
        sendError('Error al responder el ticket: ' . $e->getMessage(), 500);
    }
}

/**
 * Cierra un ticket (solo el usuario puede cerrar sus propios tickets)
 */
function closeTicket($db, $usuarioId) {
    try {
        // Obtener datos del POST
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        
        if (!$input) {
            $input = $_POST;
        }
        
        $idTicket = isset($input['id_ticket']) ? intval($input['id_ticket']) : null;
        
        // Validaciones
        if (!$idTicket) {
            sendError('ID de ticket requerido', 400);
            return;
        }
        
        // Verificar que el ticket existe y pertenece al usuario
        $stmt = $db->prepare("
            SELECT id_ticket, id_usuario, estado
            FROM soporte_tickets
            WHERE id_ticket = :id_ticket
        ");
        $stmt->execute([':id_ticket' => $idTicket]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$ticket) {
            sendError('Ticket no encontrado', 404);
            return;
        }
        
        if (intval($ticket['id_usuario']) !== intval($usuarioId)) {
            sendError('No tienes permiso para cerrar este ticket', 403);
            return;
        }
        
        if ($ticket['estado'] === 'Cerrado') {
            sendError('El ticket ya está cerrado', 400);
            return;
        }
        
        // Cerrar el ticket
        $stmt = $db->prepare("
            UPDATE soporte_tickets
            SET estado = 'Cerrado',
                fecha_cierre = NOW(),
                fecha_actualizacion = NOW()
            WHERE id_ticket = :id_ticket
        ");
        $stmt->execute([':id_ticket' => $idTicket]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Ticket cerrado exitosamente',
            'data' => [
                'id_ticket' => intval($idTicket),
                'estado' => 'Cerrado'
            ]
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        error_log("Error PDO en closeTicket: " . $e->getMessage());
        sendError('Error de base de datos al cerrar el ticket: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        error_log("Error general en closeTicket: " . $e->getMessage());
        sendError('Error al cerrar el ticket: ' . $e->getMessage(), 500);
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
