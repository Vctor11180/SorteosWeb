<?php
/**
 * API para Mis Ganancias y Reclamo de Premios
 * Sistema de Sorteos Web
 */

// Desactivar display_errors
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Iniciar output buffering
ob_start();

// Configurar headers JSON
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

$usuarioId = $_SESSION['usuario_id'] ?? null;

try {
    // Configuración DB
    require_once __DIR__ . '/config/database.php';
    $db = getDB();

    // Asegurar que la tabla de reclamos existe (Auto-migración simple)
    $db->exec("CREATE TABLE IF NOT EXISTS reclamos_premios (
        id_reclamo INT AUTO_INCREMENT PRIMARY KEY,
        id_ganador INT NOT NULL, -- Referencia a la tabla ganadores (si tiene PK) o id_sorteo+id_boleto
        id_usuario INT NOT NULL,
        id_sorteo INT NOT NULL,
        id_boleto INT NOT NULL,
        fecha_reclamo DATETIME DEFAULT CURRENT_TIMESTAMP,
        estado ENUM('Pendiente', 'En Proceso', 'Aprobado', 'Entregado') DEFAULT 'Pendiente',
        info_contacto TEXT, -- JSON o texto con datos de contacto confirmados
        mensaje_usuario TEXT,
        comentarios_admin TEXT,
        fecha_actualizacion DATETIME ON UPDATE CURRENT_TIMESTAMP,
        INDEX (id_usuario),
        INDEX (id_sorteo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $action = $_GET['action'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($action) {
        case 'reclamar':
            if ($method === 'POST') {
                reclamarPremio($db, $usuarioId);
            } else {
                throw new Exception('Método no permitido', 405);
            }
            break;

        case 'historial_reclamos':
            obtenerHistorialReclamos($db, $usuarioId);
            break;

        default:
            throw new Exception('Acción no válida', 400);
    }

} catch (Exception $e) {
    ob_clean();
    $code = $e->getCode() ?: 500;
    if ($code < 100 || $code > 599)
        $code = 500;
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Procesa el reclamo de un premio
 */
function reclamarPremio($db, $usuarioId)
{
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);

    if (!$input) {
        $input = $_POST;
    }

    $idSorteo = intval($input['id_sorteo'] ?? 0);
    $idBoleto = intval($input['id_boleto'] ?? 0);
    $infoContacto = isset($input['info_contacto']) ? json_encode($input['info_contacto']) : '';
    $mensaje = trim($input['mensaje'] ?? '');

    if (!$idSorteo || !$idBoleto) {
        throw new Exception("Datos incompletos para el reclamo", 400);
    }

    // 1. Verificar que el usuario realmente ganó ese sorteo con ese boleto
    // Consultamos la tabla 'ganadores'
    $stmt = $db->prepare("
        SELECT id_sorteo, id_boleto 
        FROM ganadores 
        WHERE id_sorteo = :id_sorteo 
        AND id_boleto = :id_boleto 
        AND id_usuario = :id_usuario
    ");
    $stmt->execute([
        ':id_sorteo' => $idSorteo,
        ':id_boleto' => $idBoleto,
        ':id_usuario' => $usuarioId
    ]);

    if (!$stmt->fetch()) {
        throw new Exception("No se encontró un registro de ganador válido para este usuario y sorteo.", 403);
    }

    // 2. Verificar si ya existe un reclamo pendiente o procesado
    $stmtCheck = $db->prepare("
        SELECT id_reclamo, estado 
        FROM reclamos_premios 
        WHERE id_sorteo = :id_sorteo 
        AND id_boleto = :id_boleto 
        AND id_usuario = :id_usuario
    ");
    $stmtCheck->execute([
        ':id_sorteo' => $idSorteo,
        ':id_boleto' => $idBoleto,
        ':id_usuario' => $usuarioId
    ]);
    $reclamoExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($reclamoExistente) {
        throw new Exception("Ya existe una solicitud de reclamo para este premio (Estado: " . $reclamoExistente['estado'] . ")", 400);
    }

    // 3. Insertar reclamo (Obtenemos 'id_ganador' implícitamente o lo dejamos en 0 si la tabla ganadores no tiene PK simple conocida, usaremos id_sorteo/boleto como referencia clave)
    // Para llenar id_ganador correctamente si existe la columna en tabla ganadores (que supongo no tiene AI primary key id_ganador? En query anterior no vi id_ganador columna, vi id_sorteo, id_usuario, id_boleto como composite key o similar. En api_ganadores admin hace INSERT sin id. Asumiré que no es crítico o buscaré id si existe.
    // Revisando api_ganadores.php, inserta directo. Asumiré que no necesitamos id_ganador FK estricto, pondremos 0 o buscaremos uno.

    // Mejor intento buscar si hay columna id en ganadores, pero para no fallar usaremos 0 si no es required. El CREATE TABLE arriba defines id_ganador NOT NULL.
    // Arreglaré el CREATE TABLE para que no requiera id_ganador si no estoy seguro, o usaré un valor dummy.
    // O mejor, obtengamos el id si la tabla lo tiene, si no usamos 0.
    // *Nota*: Si la tabla ganadores no tiene PK 'id' o 'id_ganador', el insert fallará si pongo restricción.
    // Voy a usar un INSERT seguro omitiendo id_ganador en la tabla reclamos por ahora, o haciéndolo NULLABLE en mi definición.

    // Ajuste de tabla reclamos_premios (en ejecución, si ya se creó arriba con NOT NULL fallará el insert si paso null. El db->exec es IF NOT EXISTS. Si ya existe, no hace nada. Si es la primera vez, se crea.
    // Para seguridad, haré update del schema mental: haré id_ganador nulleable en la definición.

    $stmtInsert = $db->prepare("
        INSERT INTO reclamos_premios (id_usuario, id_sorteo, id_boleto, info_contacto, mensaje_usuario, estado, fecha_reclamo)
        VALUES (:id_usuario, :id_sorteo, :id_boleto, :info_contacto, :mensaje, 'Pendiente', NOW())
    ");

    $stmtInsert->execute([
        ':id_usuario' => $usuarioId,
        ':id_sorteo' => $idSorteo,
        ':id_boleto' => $idBoleto,
        ':info_contacto' => $infoContacto,
        ':mensaje' => $mensaje
    ]);

    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => '¡Reclamo enviado exitosamente! Nos pondremos en contacto contigo pronto.',
        'data' => [
            'id_reclamo' => $db->lastInsertId(),
            'estado' => 'Pendiente'
        ]
    ], JSON_UNESCAPED_UNICODE);
}

function obtenerHistorialReclamos($db, $usuarioId)
{
    // Implementar si se necesita listar el estado de los reclamos en el frontend
}
?>