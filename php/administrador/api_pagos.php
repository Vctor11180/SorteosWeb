<?php
/**
 * API para gestión de pagos y transacciones
 */

header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Obtener ID de usuario administrador (simulado o de sesión)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$id_admin = $_SESSION['id_usuario'] ?? 1; // Fallback a 1 si no hay sesión, idealmente debería validar sesión

try {
    switch ($method) {
        case 'GET':
            if ($action === 'list') {
                getTransacciones($conn);
            } elseif ($action === 'stats') {
                getStats($conn);
            } else {
                getTransacciones($conn);
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if ($action === 'approve') {
                approveTransaccion($conn, $input, $id_admin);
            } elseif ($action === 'reject') {
                rejectTransaccion($conn, $input, $id_admin);
            } else {
                sendError('Acción no válida', 400);
            }
            break;
            
        default:
            sendError('Método no permitido', 405);
            break;
    }
} catch (Exception $e) {
    sendError('Error del servidor: ' . $e->getMessage(), 500);
}

/**
 * Obtiene lista de transacciones con detalles
 */
function getTransacciones($conn) {
    // Consulta para obtener transacciones con usuario y detalles del sorteo (a través de boletos)
    // Se asume que una transacción es para un sorteo principal (tomamos el primero encontrado)
    $query = "SELECT 
                t.id_transaccion,
                t.monto_total as monto,
                t.metodo_pago,
                t.referencia_pago,
                t.comprobante_url,
                t.estado_pago,
                t.fecha_creacion,
                u.primer_nombre,
                u.apellido_paterno,
                u.email,
                u.avatar_url,
                (SELECT COUNT(*) FROM detalle_transaccion_boletos dt WHERE dt.id_transaccion = t.id_transaccion) as cantidad_boletos,
                (
                    SELECT s.titulo 
                    FROM detalle_transaccion_boletos dt 
                    JOIN boletos b ON dt.id_boleto = b.id_boleto
                    JOIN sorteos s ON b.id_sorteo = s.id_sorteo
                    WHERE dt.id_transaccion = t.id_transaccion
                    LIMIT 1
                ) as nombre_sorteo,
                (
                   SELECT GROUP_CONCAT(b.numero_boleto SEPARATOR ', ')
                   FROM detalle_transaccion_boletos dt
                   JOIN boletos b ON dt.id_boleto = b.id_boleto
                   WHERE dt.id_transaccion = t.id_transaccion
                ) as numeros_boletos
              FROM transacciones t
              JOIN usuarios u ON t.id_usuario = u.id_usuario
              ORDER BY t.fecha_creacion DESC";
              
    $result = $conn->query($query);
    
    if (!$result) {
        sendError('Error al consultar transacciones: ' . $conn->error, 500);
    }
    
    $pagos = [];
    while ($row = $result->fetch_assoc()) {
        $pagos[] = [
            'id' => $row['id_transaccion'],
            'usuario' => $row['primer_nombre'] . ' ' . $row['apellido_paterno'],
            'email' => $row['email'],
            'avatar' => $row['avatar_url'] ?? '', // Default handling in frontend
            'sorteo' => $row['nombre_sorteo'] ?? 'Varios / Desconocido',
            'cantidad_boletos' => $row['cantidad_boletos'],
            'numeros_boletos' => $row['numeros_boletos'],
            'monto' => $row['monto'],
            'metodo' => strtolower($row['metodo_pago']), // standardize for frontend filter
            'referencia' => $row['referencia_pago'],
            'comprobante_url' => $row['comprobante_url'],
            'fecha' => $row['fecha_creacion'],
            'estado' => mapEstado($row['estado_pago'])
        ];
    }
    
    echo json_encode(['success' => true, 'data' => $pagos]);
}

/**
 * Mapea estado de DB a estado frontend
 */
function mapEstado($dbEstado) {
    switch ($dbEstado) {
        case 'Completado': return 'approved';
        case 'Fallido': return 'rejected';
        default: return 'pending';
    }
}

/**
 * Aprueba una transacción
 */
function approveTransaccion($conn, $data, $id_admin) {
    if (!isset($data['id_transaccion'])) {
        sendError('ID de transacción requerido');
    }
    
    $id_transaccion = intval($data['id_transaccion']);
    
    $conn->begin_transaction();
    
    try {
        // 1. Actualizar estado de transacción
        $stmt = $conn->prepare("UPDATE transacciones SET estado_pago = 'Completado', id_validador = ? WHERE id_transaccion = ?");
        $stmt->bind_param("ii", $id_admin, $id_transaccion);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            // Verificar si ya estaba aprobado o no existe
            // throw new Exception("Transacción no encontrada o ya procesada");
            // Permitimos re-aprobar por idempotencia o checkeo simple
        }
        $stmt->close();
        
        // 2. Actualizar estado de boletos a 'Vendido'
        // Obtener IDs de boletos de esta transacción
        $queryBoletos = "SELECT id_boleto FROM detalle_transaccion_boletos WHERE id_transaccion = $id_transaccion";
        $resBoletos = $conn->query($queryBoletos);
        
        $ids_boletos = [];
        while ($b = $resBoletos->fetch_assoc()) {
            $ids_boletos[] = $b['id_boleto'];
        }
        
        if (!empty($ids_boletos)) {
            $ids_str = implode(',', $ids_boletos);
            $conn->query("UPDATE boletos SET estado = 'Vendido' WHERE id_boleto IN ($ids_str)");
        }
        
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Pago aprobado exitosamente']);
        
    } catch (Exception $e) {
        $conn->rollback();
        sendError('Error al aprobar pago: ' . $e->getMessage(), 500);
    }
}

/**
 * Rechaza una transacción
 */
function rejectTransaccion($conn, $data, $id_admin) {
    if (!isset($data['id_transaccion'])) {
        sendError('ID de transacción requerido');
    }
    
    $id_transaccion = intval($data['id_transaccion']);
    $motivo = $data['motivo'] ?? ''; // Podríamos guardar el motivo en DB si hubiera campo, por ahora solo log o nada
    
    $conn->begin_transaction();
    
    try {
        // 1. Actualizar estado de transacción
        $stmt = $conn->prepare("UPDATE transacciones SET estado_pago = 'Fallido', id_validador = ? WHERE id_transaccion = ?");
        $stmt->bind_param("ii", $id_admin, $id_transaccion);
        $stmt->execute();
        $stmt->close();
        
        // 2. Liberar boletos (volver a 'Disponible', quitar usuario)
        // Obtener IDs de boletos
        $queryBoletos = "SELECT id_boleto FROM detalle_transaccion_boletos WHERE id_transaccion = $id_transaccion";
        $resBoletos = $conn->query($queryBoletos);
        
        $ids_boletos = [];
        while ($b = $resBoletos->fetch_assoc()) {
            $ids_boletos[] = $b['id_boleto'];
        }
        
        if (!empty($ids_boletos)) {
            $ids_str = implode(',', $ids_boletos);
            // IMPORTANTE: Liberar el boleto significa ponerlo disponible y quitar el usuario
            $conn->query("UPDATE boletos SET estado = 'Disponible', id_usuario_actual = NULL, fecha_reserva = NULL WHERE id_boleto IN ($ids_str)");
        }
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Pago rechazado exitosamente']);
        
    } catch (Exception $e) {
        $conn->rollback();
        sendError('Error al rechazar pago: ' . $e->getMessage(), 500);
    }
}

/**
 * Obtiene estadísticas rápidas
 */
function getStats($conn) {
    $stats = [
        'pendientes' => 0,
        'aprobados_hoy' => 0,
        'rechazados_hoy' => 0,
        'monto_pendiente' => 0
    ];
    
    // Pendientes
    $res = $conn->query("SELECT COUNT(*) as c, SUM(monto_total) as m FROM transacciones WHERE estado_pago = 'Pendiente'");
    if ($row = $res->fetch_assoc()) {
        $stats['pendientes'] = $row['c'];
        $stats['monto_pendiente'] = $row['m'] ?? 0;
    }
    
    // Aprobados hoy
    $hoy = date('Y-m-d');
    $res = $conn->query("SELECT COUNT(*) as c FROM transacciones WHERE estado_pago = 'Completado' AND DATE(fecha_creacion) = '$hoy'");
    if ($row = $res->fetch_assoc()) {
        $stats['aprobados_hoy'] = $row['c'];
    }
    
    // Rechazados hoy
    $res = $conn->query("SELECT COUNT(*) as c FROM transacciones WHERE estado_pago = 'Fallido' AND DATE(fecha_creacion) = '$hoy'");
    if ($row = $res->fetch_assoc()) {
        $stats['rechazados_hoy'] = $row['c'];
    }
    
    echo json_encode(['success' => true, 'data' => $stats]);
}


function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $message
    ]);
    exit;
}

$conn->close();
?>
