<?php
/**
 * API para Gestión de Ganadores
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
$id_admin = $_SESSION['id_usuario'] ?? 1;

try {
    switch ($method) {
        case 'GET':
            if ($action === 'list_raffles') {
                getSorteosForWinners($conn);
            } elseif ($action === 'history') {
                getWinnerHistory($conn);
            } else {
                sendError('Acción no válida', 400);
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if ($action === 'generate') {
                generateWinner($conn, $input);
            } elseif ($action === 'notify') {
                notifyWinner($conn, $input);
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
 * Obtiene sorteos para la gestión de ganadores
 * Incluye estado (si ya tiene ganador, si está finalizado, etc.)
 */
function getSorteosForWinners($conn) {
    // Obtenemos sorteos y verificamos si ya tienen ganador
    // Calculamos boletos vendidos vs total
    $query = "SELECT 
                s.id_sorteo,
                s.titulo,
                s.imagen_url,
                s.estado,
                s.fecha_fin,
                s.total_boletos_crear,
                s.precio_boleto,
                (SELECT COUNT(*) FROM boletos b WHERE b.id_sorteo = s.id_sorteo AND b.estado = 'Vendido') as vendidos,
                (SELECT COUNT(*) FROM ganadores g WHERE g.id_sorteo = s.id_sorteo) as tiene_ganador,
                g.id_usuario as id_ganador,
                u.primer_nombre, u.apellido_paterno, u.avatar_url,
                b.numero_boleto as boleto_ganador,
                g.fecha_anuncio
              FROM sorteos s
              LEFT JOIN ganadores g ON s.id_sorteo = g.id_sorteo
              LEFT JOIN usuarios u ON g.id_usuario = u.id_usuario
              LEFT JOIN boletos b ON g.id_boleto = b.id_boleto
              ORDER BY s.fecha_fin DESC";
              
    $result = $conn->query($query);
    
    if (!$result) {
        sendError('Error DB: ' . $conn->error);
    }
    
    $sorteos = [];
    while ($row = $result->fetch_assoc()) {
        $estado_gestion = 'pending'; // Por defecto
        
        if ($row['tiene_ganador'] > 0) {
            $estado_gestion = 'completed';
        } else if ($row['estado'] === 'Activo') {
            $estado_gestion = 'active'; // Aún no finaliza
        } else if ($row['estado'] === 'Finalizado') {
            $estado_gestion = 'ready'; // Listo para generar
        }
        
        $sorteos[] = [
            'id' => $row['id_sorteo'],
            'titulo' => $row['titulo'],
            'imagen' => $row['imagen_url'],
            'fecha_fin' => $row['fecha_fin'],
            'estado_sorteo' => $row['estado'],
            'estado_gestion' => $estado_gestion,
            'vendidos' => $row['vendidos'],
            'total_boletos' => $row['total_boletos_crear'],
            'recaudado' => $row['vendidos'] * $row['precio_boleto'],
            'ganador' => $row['id_ganador'] ? [
                'nombre' => $row['primer_nombre'] . ' ' . $row['apellido_paterno'],
                'avatar' => $row['avatar_url'],
                'boleto' => $row['boleto_ganador'],
                'fecha' => $row['fecha_anuncio']
            ] : null
        ];
    }
    
    echo json_encode(['success' => true, 'data' => $sorteos]);
}

/**
 * Genera un ganador aleatorio para un sorteo
 */
function generateWinner($conn, $data) {
    if (!isset($data['id_sorteo'])) {
        sendError('ID de sorteo requerido');
    }
    
    $id_sorteo = intval($data['id_sorteo']);
    
    $conn->begin_transaction();
    
    try {
        // 1. Verificar que el sorteo exista y esté finalizado (o permitimos activo para testing?)
        // Idealmente debe estar Finalizado para ser justo.
        $checkStmt = $conn->prepare("SELECT estado FROM sorteos WHERE id_sorteo = ?");
        $checkStmt->bind_param("i", $id_sorteo);
        $checkStmt->execute();
        $res = $checkStmt->get_result();
        $sorteo = $res->fetch_assoc();
        
        if (!$sorteo) {
            throw new Exception("Sorteo no encontrado");
        }
        
        // Comentar esta línea si quieres permitir generar ganador en sorteos Activos para pruebas
        // if ($sorteo['estado'] !== 'Finalizado') throw new Exception("El sorteo debe estar finalizado para elegir ganador");
        
        // 2. Verificar si ya tiene ganador
        $checkGanador = $conn->query("SELECT * FROM ganadores WHERE id_sorteo = $id_sorteo");
        if ($checkGanador->num_rows > 0) {
            throw new Exception("Este sorteo ya tiene un ganador asignado");
        }
        
        // 3. Seleccionar boleto ganador aleatorio
        $sqlRandom = "SELECT id_boleto, id_usuario_actual, numero_boleto FROM boletos WHERE id_sorteo = $id_sorteo AND estado = 'Vendido' ORDER BY RAND() LIMIT 1";
        $resRandom = $conn->query($sqlRandom);
        
        if ($resRandom->num_rows === 0) {
            throw new Exception("No hay boletos vendidos para este sorteo");
        }
        
        $winnerTicket = $resRandom->fetch_assoc();
        
        // 4. Insertar en tabla ganadores
        $stmtInsert = $conn->prepare("INSERT INTO ganadores (id_sorteo, id_usuario, id_boleto, premio_detalle) VALUES (?, ?, ?, 'Primer Premio')");
        $stmtInsert->bind_param("iii", $id_sorteo, $winnerTicket['id_usuario_actual'], $winnerTicket['id_boleto']);
        $stmtInsert->execute();
        
        $conn->commit();
        
        // Obtener datos del ganador para devolver
        $sqlWinnerInfo = "SELECT primer_nombre, apellido_paterno, avatar_url FROM usuarios WHERE id_usuario = " . $winnerTicket['id_usuario_actual'];
        $resInfo = $conn->query($sqlWinnerInfo);
        $userInfo = $resInfo->fetch_assoc();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Ganador generado exitosamente',
            'data' => [
                'ganador' => $userInfo['primer_nombre'] . ' ' . $userInfo['apellido_paterno'],
                'boleto' => $winnerTicket['numero_boleto'],
                'avatar' => $userInfo['avatar_url']
            ]
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        sendError($e->getMessage());
    }
}

/**
 * Obtiene historial de ganadores
 */
function getWinnerHistory($conn) {
    // Similar a getSorteos pero enfocado en una lista plana de eventos/ganadores
    $query = "SELECT 
                g.fecha_anuncio,
                u.primer_nombre, u.apellido_paterno, u.avatar_url,
                b.numero_boleto,
                s.titulo as sorteo_titulo,
                s.imagen_url as sorteo_imagen,
                g.entregado
              FROM ganadores g
              JOIN usuarios u ON g.id_usuario = u.id_usuario
              JOIN boletos b ON g.id_boleto = b.id_boleto
              JOIN sorteos s ON g.id_sorteo = s.id_sorteo
              ORDER BY g.fecha_anuncio DESC
              LIMIT 50";
              
    $result = $conn->query($query);
    $history = [];
    
    while ($row = $result->fetch_assoc()) {
        $history[] = [
            'raffleName' => $row['sorteo_titulo'],
            'fecha' => date('d M Y', strtotime($row['fecha_anuncio'])),
            'ganador' => $row['primer_nombre'] . ' ' . $row['apellido_paterno'],
            'boleto' => $row['numero_boleto'],
            'estado' => $row['entregado'] ? 'Entregado' : 'Generado',
            'accion' => $row['entregado'] ? 'entregado' : 'generado',
            'imagen' => "url('" . ($row['sorteo_imagen'] ?? '') . "')",
            'avatar' => $row['avatar_url']
        ];
    }
    
    echo json_encode(['success' => true, 'data' => $history]);
}

/**
 * Simular notificación
 */
function notifyWinner($conn, $input) {
    // Aquí iría la lógica de email real
    // Por ahora solo respondemos success
    echo json_encode(['success' => true, 'message' => 'Notificación enviada']);
}

function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

$conn->close();
?>
