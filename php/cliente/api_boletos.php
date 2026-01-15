<?php
/**
 * API de Boletos para Clientes
 * Sistema de Sorteos Web
 * 
 * Endpoints:
 * - GET ?action=get_available&id_sorteo={id} - Obtener estadísticas de boletos (sin números específicos)
 * - POST ?action=assign_random - Asignar boletos aleatorios automáticamente
 * - POST ?action=reserve - Reservar boletos temporalmente (15 min) [DEPRECATED - solo admin]
 * - POST ?action=release - Liberar boletos reservados
 * - GET ?action=check_reservation&id_sorteo={id} - Verificar reservas activas del usuario
 * - GET ?action=get_my_tickets - Obtener boletos comprados del usuario
 * - GET ?action=get_my_assigned&id_sorteo={id} - Obtener boletos asignados del usuario en un sorteo
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
        case 'get_available':
            $idSorteo = $_GET['id_sorteo'] ?? null;
            if ($idSorteo) {
                ob_clean();
                getAvailableTickets($db, intval($idSorteo));
            } else {
                ob_clean();
                sendError('ID de sorteo requerido', 400);
            }
            break;
            
        case 'reserve':
            if ($method === 'POST') {
                ob_clean();
                reserveTickets($db, $usuarioId);
            } else {
                ob_clean();
                sendError('Método no permitido. Solo se permite POST.', 405);
            }
            break;
            
        case 'release':
            if ($method === 'POST') {
                ob_clean();
                releaseTickets($db, $usuarioId);
            } else {
                ob_clean();
                sendError('Método no permitido. Solo se permite POST.', 405);
            }
            break;
            
        case 'check_reservation':
            $idSorteo = $_GET['id_sorteo'] ?? null;
            if ($idSorteo) {
                ob_clean();
                checkUserReservations($db, $usuarioId, intval($idSorteo));
            } else {
                ob_clean();
                sendError('ID de sorteo requerido', 400);
            }
            break;
            
        case 'get_my_tickets':
            ob_clean();
            getMyTickets($db, $usuarioId);
            break;
            
        case 'assign_random':
            if ($method === 'POST') {
                ob_clean();
                assignRandomTickets($db, $usuarioId);
            } else {
                ob_clean();
                sendError('Método no permitido. Solo se permite POST.', 405);
            }
            break;
            
        case 'get_my_assigned':
            $idSorteo = $_GET['id_sorteo'] ?? null;
            if ($idSorteo) {
                ob_clean();
                getMyAssignedTickets($db, $usuarioId, intval($idSorteo));
            } else {
                ob_clean();
                sendError('ID de sorteo requerido', 400);
            }
            break;
            
        default:
            ob_clean();
            sendError('Acción no válida', 400);
            break;
    }
    
} catch (PDOException $e) {
    ob_clean();
    error_log("Error PDO en api_boletos.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error de base de datos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Exception $e) {
    ob_clean();
    error_log("Error general en api_boletos.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error del servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Obtiene los boletos disponibles de un sorteo
 */
function getAvailableTickets($db, $idSorteo) {
    try {
        // Verificar que el sorteo existe y está activo
        $stmt = $db->prepare("
            SELECT id_sorteo, estado, precio_boleto, total_boletos_crear
            FROM sorteos
            WHERE id_sorteo = :id_sorteo
        ");
        $stmt->execute([':id_sorteo' => $idSorteo]);
        $sorteo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$sorteo) {
            sendError('Sorteo no encontrado', 404);
            return;
        }
        
        if ($sorteo['estado'] !== 'Activo') {
            sendError('Este sorteo no está disponible', 400);
            return;
        }
        
        $totalBoletos = intval($sorteo['total_boletos_crear']);
        
        // Obtener todos los boletos físicos de la BD
        $stmt = $db->prepare("
            SELECT 
                id_boleto,
                numero_boleto,
                estado,
                id_usuario_actual,
                fecha_reserva
            FROM boletos
            WHERE id_sorteo = :id_sorteo
            ORDER BY CAST(numero_boleto AS UNSIGNED) ASC
        ");
        $stmt->execute([':id_sorteo' => $idSorteo]);
        $boletos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Verificar que todos los boletos estén creados físicamente en la BD
        // Si faltan boletos, crearlos ahora (esto puede pasar si se creó el sorteo antes de esta implementación)
        $boletosExistentes = count($boletos);
        if ($boletosExistentes < $totalBoletos) {
            // Crear un mapa de números de boletos existentes
            $boletosMap = [];
            foreach ($boletos as $boleto) {
                $numeroInt = intval($boleto['numero_boleto']);
                $boletosMap[$numeroInt] = true;
            }
            
            // Insertar boletos faltantes físicamente en la BD
            $db->beginTransaction();
            try {
                for ($i = 1; $i <= $totalBoletos; $i++) {
                    if (!isset($boletosMap[$i])) {
                        $numeroBoleto = str_pad($i, 4, '0', STR_PAD_LEFT);
                        $stmtInsert = $db->prepare("
                            INSERT INTO boletos (id_sorteo, numero_boleto, estado)
                            VALUES (:id_sorteo, :numero_boleto, 'Disponible')
                        ");
                        $stmtInsert->execute([
                            ':id_sorteo' => $idSorteo,
                            ':numero_boleto' => $numeroBoleto
                        ]);
                    }
                }
                $db->commit();
                
                // Volver a obtener todos los boletos después de crearlos
                $stmt->execute([':id_sorteo' => $idSorteo]);
                $boletos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $db->rollBack();
                error_log("Error al crear boletos faltantes: " . $e->getMessage());
                // Continuar con los boletos que sí existen
            }
        }
        
        // Verificar que tenemos todos los boletos esperados
        if (count($boletos) < $totalBoletos) {
            error_log("Advertencia: El sorteo #$idSorteo tiene " . count($boletos) . " boletos pero debería tener $totalBoletos");
        }
        
        // Separar boletos por estado
        $disponibles = [];
        $reservados = [];
        $vendidos = [];
        
        foreach ($boletos as $boleto) {
            $numero = $boleto['numero_boleto'];
            $estado = $boleto['estado'];
            
            // Verificar si la reserva expiró (más de 15 minutos)
            if ($estado === 'Reservado' && $boleto['fecha_reserva']) {
                $fechaReserva = new DateTime($boleto['fecha_reserva']);
                $ahora = new DateTime();
                $diferencia = $ahora->getTimestamp() - $fechaReserva->getTimestamp();
                
                // Si pasaron más de 15 minutos (900 segundos), considerar como disponible
                if ($diferencia > 900) {
                    $estado = 'Disponible';
                }
            }
            
            $boletoInfo = [
                'id_boleto' => $boleto['id_boleto'],
                'numero_boleto' => $numero,
                'estado' => $estado,
                'id_usuario_actual' => $boleto['id_usuario_actual'],
                'fecha_reserva' => $boleto['fecha_reserva']
            ];
            
            if ($estado === 'Disponible') {
                $disponibles[] = $boletoInfo;
            } elseif ($estado === 'Reservado') {
                $reservados[] = $boletoInfo;
            } else {
                $vendidos[] = $boletoInfo;
            }
        }
        
        // Modificado: Solo devolver estadísticas, no los números específicos
        // Esto previene que el usuario vea qué números están disponibles
        echo json_encode([
            'success' => true,
            'data' => [
                'id_sorteo' => $idSorteo,
                'precio_boleto' => floatval($sorteo['precio_boleto']),
                'total_boletos' => intval($sorteo['total_boletos_crear']),
                'total_disponibles' => count($disponibles),
                'total_reservados' => count($reservados),
                'total_vendidos' => count($vendidos),
                'porcentaje_disponible' => $totalBoletos > 0 ? round((count($disponibles) / $totalBoletos) * 100, 2) : 0
            ]
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        error_log("Error en getAvailableTickets: " . $e->getMessage());
        sendError('Error al obtener boletos disponibles: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        error_log("Error general en getAvailableTickets: " . $e->getMessage());
        sendError('Error al obtener boletos disponibles: ' . $e->getMessage(), 500);
    }
}

/**
 * Reserva boletos temporalmente (15 minutos)
 */
function reserveTickets($db, $usuarioId) {
    try {
        // Obtener datos del POST
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        
        if (!$input) {
            $input = $_POST;
        }
        
        $idSorteo = $input['id_sorteo'] ?? null;
        $numerosBoletos = $input['numeros_boletos'] ?? [];
        
        if (!$idSorteo) {
            sendError('ID de sorteo requerido', 400);
            return;
        }
        
        if (empty($numerosBoletos) || !is_array($numerosBoletos)) {
            sendError('Lista de números de boletos requerida', 400);
            return;
        }
        
        // Verificar que el sorteo existe y está activo
        $stmt = $db->prepare("
            SELECT id_sorteo, estado, precio_boleto
            FROM sorteos
            WHERE id_sorteo = :id_sorteo
        ");
        $stmt->execute([':id_sorteo' => $idSorteo]);
        $sorteo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$sorteo) {
            sendError('Sorteo no encontrado', 404);
            return;
        }
        
        if ($sorteo['estado'] !== 'Activo') {
            sendError('Este sorteo no está disponible', 400);
            return;
        }
        
        // Normalizar números de boletos
        $numerosBoletosNormalizados = array_map(function($num) {
            return str_pad($num, 4, '0', STR_PAD_LEFT);
        }, $numerosBoletos);
        
        // Verificar que los boletos existen y están disponibles
        $placeholders = str_repeat('?,', count($numerosBoletosNormalizados) - 1) . '?';
        $stmt = $db->prepare("
            SELECT id_boleto, numero_boleto, estado, id_usuario_actual, fecha_reserva
            FROM boletos
            WHERE id_sorteo = ? AND numero_boleto IN ($placeholders)
        ");
        
        $params = array_merge([$idSorteo], $numerosBoletosNormalizados);
        $stmt->execute($params);
        $boletos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Si no hay boletos en BD, crear los que faltan
        $boletosExistentes = [];
        foreach ($boletos as $boleto) {
            $boletosExistentes[$boleto['numero_boleto']] = $boleto;
        }
        
        // Verificar disponibilidad y crear boletos si no existen
        $boletosParaReservar = [];
        $errores = [];
        
        foreach ($numerosBoletosNormalizados as $numeroBoleto) {
            
            if (isset($boletosExistentes[$numeroBoleto])) {
                $boleto = $boletosExistentes[$numeroBoleto];
                
                // Verificar si está disponible o si la reserva expiró
                if ($boleto['estado'] === 'Vendido') {
                    $errores[] = "El boleto #$numeroBoleto ya está vendido";
                    continue;
                }
                
                if ($boleto['estado'] === 'Reservado') {
                    // Verificar si la reserva expiró
                    if ($boleto['fecha_reserva']) {
                        $fechaReserva = new DateTime($boleto['fecha_reserva']);
                        $ahora = new DateTime();
                        $diferencia = $ahora->getTimestamp() - $fechaReserva->getTimestamp();
                        
                        if ($diferencia <= 900) { // Menos de 15 minutos
                            if ($boleto['id_usuario_actual'] != $usuarioId) {
                                $errores[] = "El boleto #$numeroBoleto está reservado por otro usuario";
                                continue;
                            }
                        }
                    }
                }
                
                $boletosParaReservar[] = $boleto['id_boleto'];
            } else {
                // Crear el boleto si no existe
                $stmt = $db->prepare("
                    INSERT INTO boletos (id_sorteo, numero_boleto, estado, id_usuario_actual, fecha_reserva)
                    VALUES (:id_sorteo, :numero_boleto, 'Reservado', :id_usuario_actual, NOW())
                ");
                $stmt->execute([
                    ':id_sorteo' => $idSorteo,
                    ':numero_boleto' => $numeroBoleto,
                    ':id_usuario_actual' => $usuarioId
                ]);
                $boletosParaReservar[] = $db->lastInsertId();
            }
        }
        
        if (!empty($errores)) {
            sendError('Algunos boletos no están disponibles: ' . implode(', ', $errores), 400);
            return;
        }
        
        // Actualizar boletos existentes a reservado
        if (!empty($boletosParaReservar)) {
            $placeholders = str_repeat('?,', count($boletosParaReservar) - 1) . '?';
            $stmt = $db->prepare("
                UPDATE boletos
                SET estado = 'Reservado',
                    id_usuario_actual = ?,
                    fecha_reserva = NOW()
                WHERE id_boleto IN ($placeholders)
                AND (estado = 'Disponible' OR (estado = 'Reservado' AND id_usuario_actual = ?))
            ");
            
            $params = array_merge([$usuarioId], $boletosParaReservar, [$usuarioId]);
            $stmt->execute($params);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Boletos reservados exitosamente',
            'data' => [
                'id_sorteo' => $idSorteo,
                'numeros_boletos' => $numerosBoletos,
                'total_reservados' => count($boletosParaReservar),
                'tiempo_expiracion' => 900 // 15 minutos en segundos
            ]
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        error_log("Error en reserveTickets: " . $e->getMessage());
        sendError('Error al reservar boletos: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        error_log("Error general en reserveTickets: " . $e->getMessage());
        sendError('Error al reservar boletos: ' . $e->getMessage(), 500);
    }
}

/**
 * Libera boletos reservados por el usuario
 */
function releaseTickets($db, $usuarioId) {
    try {
        // Obtener datos del POST
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        
        if (!$input) {
            $input = $_POST;
        }
        
        $idSorteo = $input['id_sorteo'] ?? null;
        $numerosBoletos = $input['numeros_boletos'] ?? [];
        
        if (!$idSorteo) {
            sendError('ID de sorteo requerido', 400);
            return;
        }
        
        if (empty($numerosBoletos) || !is_array($numerosBoletos)) {
            sendError('Lista de números de boletos requerida', 400);
            return;
        }
        
        // Normalizar números de boletos
        $numerosBoletos = array_map(function($num) {
            return str_pad($num, 4, '0', STR_PAD_LEFT);
        }, $numerosBoletos);
        
        // Liberar boletos (solo los que están reservados por este usuario)
        $placeholders = str_repeat('?,', count($numerosBoletos) - 1) . '?';
        $stmt = $db->prepare("
            UPDATE boletos
            SET estado = 'Disponible',
                id_usuario_actual = NULL,
                fecha_reserva = NULL
            WHERE id_sorteo = ?
            AND numero_boleto IN ($placeholders)
            AND estado = 'Reservado'
            AND id_usuario_actual = ?
        ");
        
        $params = array_merge([$idSorteo], $numerosBoletos, [$usuarioId]);
        $stmt->execute($params);
        $filasAfectadas = $stmt->rowCount();
        
        echo json_encode([
            'success' => true,
            'message' => 'Boletos liberados exitosamente',
            'data' => [
                'id_sorteo' => $idSorteo,
                'numeros_boletos' => $numerosBoletos,
                'total_liberados' => $filasAfectadas
            ]
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        error_log("Error en releaseTickets: " . $e->getMessage());
        sendError('Error al liberar boletos: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        error_log("Error general en releaseTickets: " . $e->getMessage());
        sendError('Error al liberar boletos: ' . $e->getMessage(), 500);
    }
}

/**
 * Verifica las reservas activas del usuario en un sorteo
 */
function checkUserReservations($db, $usuarioId, $idSorteo) {
    try {
        $stmt = $db->prepare("
            SELECT 
                id_boleto,
                numero_boleto,
                estado,
                fecha_reserva,
                TIMESTAMPDIFF(SECOND, fecha_reserva, NOW()) as segundos_transcurridos
            FROM boletos
            WHERE id_sorteo = :id_sorteo
            AND id_usuario_actual = :usuario_id
            AND estado = 'Reservado'
        ");
        $stmt->execute([
            ':id_sorteo' => $idSorteo,
            ':usuario_id' => $usuarioId
        ]);
        $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Filtrar reservas que aún no han expirado (menos de 15 minutos)
        $reservasActivas = [];
        foreach ($reservas as $reserva) {
            $segundosTranscurridos = intval($reserva['segundos_transcurridos']);
            if ($segundosTranscurridos < 900) { // Menos de 15 minutos
                $reservasActivas[] = [
                    'id_boleto' => $reserva['id_boleto'],
                    'numero_boleto' => $reserva['numero_boleto'],
                    'tiempo_restante' => 900 - $segundosTranscurridos,
                    'fecha_reserva' => $reserva['fecha_reserva']
                ];
            }
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'id_sorteo' => $idSorteo,
                'reservas_activas' => $reservasActivas,
                'total_reservas' => count($reservasActivas)
            ]
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        error_log("Error en checkUserReservations: " . $e->getMessage());
        sendError('Error al verificar reservas: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        error_log("Error general en checkUserReservations: " . $e->getMessage());
        sendError('Error al verificar reservas: ' . $e->getMessage(), 500);
    }
}

/**
 * Obtiene los boletos comprados del usuario
 */
function getMyTickets($db, $usuarioId) {
    try {
        // Obtener boletos vendidos del usuario
        $stmt = $db->prepare("
            SELECT 
                b.id_boleto,
                b.numero_boleto,
                b.estado,
                s.id_sorteo,
                s.titulo as sorteo_titulo,
                s.precio_boleto,
                s.fecha_fin,
                s.imagen_url as sorteo_imagen
            FROM boletos b
            INNER JOIN sorteos s ON b.id_sorteo = s.id_sorteo
            WHERE b.id_usuario_actual = :usuario_id
            AND b.estado = 'Vendido'
            ORDER BY s.fecha_fin DESC, b.numero_boleto ASC
        ");
        $stmt->execute([':usuario_id' => $usuarioId]);
        $boletos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'boletos' => $boletos,
                'total' => count($boletos)
            ]
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        error_log("Error en getMyTickets: " . $e->getMessage());
        sendError('Error al obtener tus boletos: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        error_log("Error general en getMyTickets: " . $e->getMessage());
        sendError('Error al obtener tus boletos: ' . $e->getMessage(), 500);
    }
}

/**
 * Asigna boletos aleatorios automáticamente al usuario
 */
function assignRandomTickets($db, $usuarioId) {
    try {
        // Obtener datos del POST
        $rawInput = file_get_contents('php://input');
        error_log("Raw input recibido: " . $rawInput);
        
        $input = json_decode($rawInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Error al decodificar JSON: " . json_last_error_msg());
            $input = $_POST;
        }
        
        if (!$input || empty($input)) {
            error_log("Input vacío, intentando con _POST");
            $input = $_POST;
        }
        
        error_log("Input decodificado: " . print_r($input, true));
        
        $idSorteo = isset($input['id_sorteo']) ? intval($input['id_sorteo']) : null;
        $cantidad = isset($input['cantidad']) ? intval($input['cantidad']) : 0;
        
        error_log("ID Sorteo: $idSorteo, Cantidad: $cantidad");
        
        // Validaciones
        if (!$idSorteo || $idSorteo <= 0) {
            error_log("Error: ID de sorteo inválido o faltante");
            sendError('ID de sorteo requerido y debe ser un número válido', 400);
            return;
        }
        
        if ($cantidad <= 0 || $cantidad > 10) {
            error_log("Error: Cantidad inválida: $cantidad");
            sendError('La cantidad debe estar entre 1 y 10 boletos. Recibido: ' . $cantidad, 400);
            return;
        }
        
        // Iniciar transacción para evitar condiciones de carrera
        $db->beginTransaction();
        
        try {
            // Verificar que el sorteo existe y está activo
            $stmt = $db->prepare("
                SELECT id_sorteo, estado, precio_boleto, total_boletos_crear
                FROM sorteos
                WHERE id_sorteo = :id_sorteo
                FOR UPDATE
            ");
            $stmt->execute([':id_sorteo' => $idSorteo]);
            $sorteo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$sorteo) {
                $db->rollBack();
                sendError('Sorteo no encontrado', 404);
                return;
            }
            
            if ($sorteo['estado'] !== 'Activo') {
                $db->rollBack();
                sendError('Este sorteo no está disponible', 400);
                return;
            }
            
            // Verificar límite de boletos por usuario en este sorteo
            $stmt = $db->prepare("
                SELECT COUNT(*) as total_reservados
                FROM boletos
                WHERE id_sorteo = :id_sorteo
                AND id_usuario_actual = :usuario_id
                AND estado IN ('Reservado', 'Vendido')
            ");
            $stmt->execute([
                ':id_sorteo' => $idSorteo,
                ':usuario_id' => $usuarioId
            ]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            $boletosYaReservados = intval($resultado['total_reservados']);
            
            // Límite máximo de 10 boletos por usuario por sorteo
            if ($boletosYaReservados + $cantidad > 10) {
                $db->rollBack();
                $disponibles = 10 - $boletosYaReservados;
                sendError("Solo puedes tener máximo 10 boletos en este sorteo. Ya tienes $boletosYaReservados, puedes asignar hasta $disponibles más.", 400);
                return;
            }
            
            // Buscar boletos disponibles aleatoriamente
            // Excluir boletos ya reservados o vendidos por este usuario
            $stmt = $db->prepare("
                SELECT id_boleto, numero_boleto
                FROM boletos
                WHERE id_sorteo = :id_sorteo
                AND estado = 'Disponible'
                AND (id_usuario_actual IS NULL OR id_usuario_actual != :usuario_id)
                ORDER BY RAND()
                LIMIT :cantidad
                FOR UPDATE
            ");
            $stmt->bindValue(':id_sorteo', $idSorteo, PDO::PARAM_INT);
            $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->bindValue(':cantidad', $cantidad, PDO::PARAM_INT);
            $stmt->execute();
            $boletosDisponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Si no hay suficientes boletos disponibles, verificar reservas expiradas
            if (count($boletosDisponibles) < $cantidad) {
                // Liberar reservas expiradas (más de 15 minutos)
                $stmt = $db->prepare("
                    UPDATE boletos
                    SET estado = 'Disponible',
                        id_usuario_actual = NULL,
                        fecha_reserva = NULL
                    WHERE id_sorteo = :id_sorteo
                    AND estado = 'Reservado'
                    AND fecha_reserva IS NOT NULL
                    AND TIMESTAMPDIFF(SECOND, fecha_reserva, NOW()) > 900
                ");
                $stmt->execute([':id_sorteo' => $idSorteo]);
                
                // Volver a buscar boletos disponibles
                $stmt = $db->prepare("
                    SELECT id_boleto, numero_boleto
                    FROM boletos
                    WHERE id_sorteo = :id_sorteo
                    AND estado = 'Disponible'
                    AND (id_usuario_actual IS NULL OR id_usuario_actual != :usuario_id)
                    ORDER BY RAND()
                    LIMIT :cantidad
                    FOR UPDATE
                ");
                $stmt->bindValue(':id_sorteo', $idSorteo, PDO::PARAM_INT);
                $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
                $stmt->bindValue(':cantidad', $cantidad, PDO::PARAM_INT);
                $stmt->execute();
                $boletosDisponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            // Verificar que tenemos suficientes boletos
            if (count($boletosDisponibles) < $cantidad) {
                $db->rollBack();
                sendError("No hay suficientes boletos disponibles. Solo hay " . count($boletosDisponibles) . " disponible(s).", 400);
                return;
            }
            
            // Reservar los boletos asignados
            $idBoletos = array_column($boletosDisponibles, 'id_boleto');
            $numerosBoletos = array_column($boletosDisponibles, 'numero_boleto');
            $placeholders = str_repeat('?,', count($idBoletos) - 1) . '?';
            
            $stmt = $db->prepare("
                UPDATE boletos
                SET estado = 'Reservado',
                    id_usuario_actual = ?,
                    fecha_reserva = NOW()
                WHERE id_boleto IN ($placeholders)
                AND estado = 'Disponible'
            ");
            $params = array_merge([$usuarioId], $idBoletos);
            $stmt->execute($params);
            
            // Verificar que se actualizaron todos los boletos
            if ($stmt->rowCount() !== count($idBoletos)) {
                $db->rollBack();
                sendError('Error al asignar boletos. Algunos boletos ya no están disponibles.', 500);
                return;
            }
            
            // Commit de la transacción
            $db->commit();
            
            // Convertir números de boletos a enteros para la respuesta
            $numerosBoletosInt = array_map(function($num) {
                return intval($num);
            }, $numerosBoletos);
            
            $precioTotal = floatval($sorteo['precio_boleto']) * $cantidad;
            
            echo json_encode([
                'success' => true,
                'message' => 'Boletos asignados exitosamente',
                'data' => [
                    'id_sorteo' => $idSorteo,
                    'boletos_asignados' => $numerosBoletosInt,
                    'numeros_boletos' => $numerosBoletos, // Formato con ceros (0001, 0002, etc.)
                    'total' => $cantidad,
                    'precio_boleto' => floatval($sorteo['precio_boleto']),
                    'precio_total' => $precioTotal,
                    'tiempo_expiracion' => 900 // 15 minutos en segundos
                ]
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
        
    } catch (PDOException $e) {
        error_log("Error en assignRandomTickets: " . $e->getMessage());
        sendError('Error al asignar boletos: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        error_log("Error general en assignRandomTickets: " . $e->getMessage());
        sendError('Error al asignar boletos: ' . $e->getMessage(), 500);
    }
}

/**
 * Obtiene los boletos asignados/reservados del usuario en un sorteo específico
 */
function getMyAssignedTickets($db, $usuarioId, $idSorteo) {
    try {
        $stmt = $db->prepare("
            SELECT 
                id_boleto,
                numero_boleto,
                estado,
                fecha_reserva,
                TIMESTAMPDIFF(SECOND, fecha_reserva, NOW()) as segundos_transcurridos
            FROM boletos
            WHERE id_sorteo = :id_sorteo
            AND id_usuario_actual = :usuario_id
            AND estado IN ('Reservado', 'Vendido')
            ORDER BY numero_boleto ASC
        ");
        $stmt->execute([
            ':id_sorteo' => $idSorteo,
            ':usuario_id' => $usuarioId
        ]);
        $boletos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Procesar boletos y calcular tiempo restante para reservas
        $boletosProcesados = [];
        foreach ($boletos as $boleto) {
            $segundosTranscurridos = intval($boleto['segundos_transcurridos']);
            $tiempoRestante = null;
            
            if ($boleto['estado'] === 'Reservado' && $boleto['fecha_reserva']) {
                if ($segundosTranscurridos < 900) {
                    $tiempoRestante = 900 - $segundosTranscurridos;
                } else {
                    // Reserva expirada, pero aún está en BD
                    $tiempoRestante = 0;
                }
            }
            
            $boletosProcesados[] = [
                'id_boleto' => $boleto['id_boleto'],
                'numero_boleto' => $boleto['numero_boleto'],
                'numero_boleto_int' => intval($boleto['numero_boleto']),
                'estado' => $boleto['estado'],
                'tiempo_restante' => $tiempoRestante,
                'fecha_reserva' => $boleto['fecha_reserva']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'id_sorteo' => $idSorteo,
                'boletos' => $boletosProcesados,
                'total' => count($boletosProcesados)
            ]
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        error_log("Error en getMyAssignedTickets: " . $e->getMessage());
        sendError('Error al obtener boletos asignados: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        error_log("Error general en getMyAssignedTickets: " . $e->getMessage());
        sendError('Error al obtener boletos asignados: ' . $e->getMessage(), 500);
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
