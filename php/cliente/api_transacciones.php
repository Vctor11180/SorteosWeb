<?php
/**
 * API de Transacciones para Clientes
 * Sistema de Sorteos Web
 * 
 * Endpoints:
 * - POST ?action=create - Crear una nueva transacción
 * - GET ?action=get_my_transactions - Obtener transacciones del usuario
 * - GET ?action=get_transaction&id={id} - Obtener detalles de una transacción
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
        case 'create':
            if ($method === 'POST') {
                ob_clean();
                createTransaction($db, $usuarioId);
            } else {
                ob_clean();
                sendError('Método no permitido. Solo se permite POST.', 405);
            }
            break;
            
        case 'get_my_transactions':
            ob_clean();
            getMyTransactions($db, $usuarioId);
            break;
            
        case 'get_transaction':
            $id = $_GET['id'] ?? null;
            if ($id) {
                ob_clean();
                getTransactionDetails($db, $usuarioId, intval($id));
            } else {
                ob_clean();
                sendError('ID de transacción requerido', 400);
            }
            break;
            
        default:
            ob_clean();
            sendError('Acción no válida', 400);
            break;
    }
    
} catch (PDOException $e) {
    ob_clean();
    error_log("Error PDO en api_transacciones.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error de base de datos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Exception $e) {
    ob_clean();
    error_log("Error general en api_transacciones.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error del servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Crea una nueva transacción
 */
function createTransaction($db, $usuarioId) {
    try {
        // Iniciar transacción de BD para asegurar atomicidad
        $db->beginTransaction();
        
        // Obtener datos del POST
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        
        if (!$input) {
            $input = $_POST;
        }
        
        $idSorteo = $input['id_sorteo'] ?? null;
        $numerosBoletos = $input['numeros_boletos'] ?? [];
        $metodoPago = $input['metodo_pago'] ?? 'Transferencia';
        $referenciaPago = $input['referencia_pago'] ?? null;
        $comprobanteUrl = $input['comprobante_url'] ?? null;
        $montoTotal = $input['monto_total'] ?? null;
        
        // Validaciones básicas
        if (!$idSorteo) {
            $db->rollBack();
            sendError('ID de sorteo requerido', 400);
            return;
        }
        
        if (empty($numerosBoletos) || !is_array($numerosBoletos)) {
            $db->rollBack();
            sendError('Lista de números de boletos requerida', 400);
            return;
        }
        
        // Validar método de pago
        $metodosPermitidos = ['PayPal', 'Transferencia', 'Visa', 'Saldo Interno'];
        if (!in_array($metodoPago, $metodosPermitidos)) {
            $db->rollBack();
            sendError('Método de pago no válido', 400);
            return;
        }
        
        // Verificar que el usuario existe y está activo
        $stmt = $db->prepare("SELECT id_usuario, estado FROM usuarios WHERE id_usuario = :usuario_id");
        $stmt->execute([':usuario_id' => $usuarioId]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            $db->rollBack();
            sendError('Usuario no encontrado', 404);
            return;
        }
        
        if ($usuario['estado'] !== 'Activo') {
            $db->rollBack();
            sendError('Tu cuenta no está activa. Contacta al administrador.', 403);
            return;
        }
        
        // Verificar que el sorteo existe y está activo
        $stmt = $db->prepare("
            SELECT id_sorteo, estado, precio_boleto, total_boletos_crear
            FROM sorteos
            WHERE id_sorteo = :id_sorteo
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
        
        // Normalizar números de boletos
        $numerosBoletosNormalizados = array_map(function($num) {
            return str_pad($num, 4, '0', STR_PAD_LEFT);
        }, $numerosBoletos);
        
        // Obtener boletos reservados por el usuario
        $placeholders = str_repeat('?,', count($numerosBoletosNormalizados) - 1) . '?';
        $stmt = $db->prepare("
            SELECT id_boleto, numero_boleto, estado, id_usuario_actual, fecha_reserva
            FROM boletos
            WHERE id_sorteo = ? AND numero_boleto IN ($placeholders)
        ");
        
        $params = array_merge([$idSorteo], $numerosBoletosNormalizados);
        $stmt->execute($params);
        $boletos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Verificar que todos los boletos existen y están reservados por el usuario
        $boletosEncontrados = [];
        foreach ($boletos as $boleto) {
            $boletosEncontrados[$boleto['numero_boleto']] = $boleto;
        }
        
        $boletosValidos = [];
        $errores = [];
        
        foreach ($numerosBoletosNormalizados as $numeroBoleto) {
            if (!isset($boletosEncontrados[$numeroBoleto])) {
                // Si el boleto no existe, crearlo como reservado
                $stmt = $db->prepare("
                    INSERT INTO boletos (id_sorteo, numero_boleto, estado, id_usuario_actual, fecha_reserva)
                    VALUES (:id_sorteo, :numero_boleto, 'Reservado', :id_usuario_actual, NOW())
                ");
                $stmt->execute([
                    ':id_sorteo' => $idSorteo,
                    ':numero_boleto' => $numeroBoleto,
                    ':id_usuario_actual' => $usuarioId
                ]);
                $boletosValidos[] = [
                    'id_boleto' => $db->lastInsertId(),
                    'numero_boleto' => $numeroBoleto
                ];
            } else {
                $boleto = $boletosEncontrados[$numeroBoleto];
                
                // Verificar que el boleto está reservado por este usuario
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
                        
                        if ($diferencia > 900) { // Más de 15 minutos
                            // Reserva expirada, actualizar a reservado por este usuario
                            $stmt = $db->prepare("
                                UPDATE boletos
                                SET estado = 'Reservado',
                                    id_usuario_actual = :usuario_id,
                                    fecha_reserva = NOW()
                                WHERE id_boleto = :id_boleto
                            ");
                            $stmt->execute([
                                ':usuario_id' => $usuarioId,
                                ':id_boleto' => $boleto['id_boleto']
                            ]);
                            $boletosValidos[] = [
                                'id_boleto' => $boleto['id_boleto'],
                                'numero_boleto' => $numeroBoleto
                            ];
                        } elseif ($boleto['id_usuario_actual'] != $usuarioId) {
                            $errores[] = "El boleto #$numeroBoleto está reservado por otro usuario";
                            continue;
                        } else {
                            $boletosValidos[] = [
                                'id_boleto' => $boleto['id_boleto'],
                                'numero_boleto' => $numeroBoleto
                            ];
                        }
                    } else {
                        // Sin fecha de reserva, actualizar
                        $stmt = $db->prepare("
                            UPDATE boletos
                            SET id_usuario_actual = :usuario_id,
                                fecha_reserva = NOW()
                            WHERE id_boleto = :id_boleto
                        ");
                        $stmt->execute([
                            ':usuario_id' => $usuarioId,
                            ':id_boleto' => $boleto['id_boleto']
                        ]);
                        $boletosValidos[] = [
                            'id_boleto' => $boleto['id_boleto'],
                            'numero_boleto' => $numeroBoleto
                        ];
                    }
                } else {
                    // Estado 'Disponible', actualizar a reservado
                    $stmt = $db->prepare("
                        UPDATE boletos
                        SET estado = 'Reservado',
                            id_usuario_actual = :usuario_id,
                            fecha_reserva = NOW()
                        WHERE id_boleto = :id_boleto
                    ");
                    $stmt->execute([
                        ':usuario_id' => $usuarioId,
                        ':id_boleto' => $boleto['id_boleto']
                    ]);
                    $boletosValidos[] = [
                        'id_boleto' => $boleto['id_boleto'],
                        'numero_boleto' => $numeroBoleto
                    ];
                }
            }
        }
        
        if (!empty($errores)) {
            $db->rollBack();
            sendError('Algunos boletos no están disponibles: ' . implode(', ', $errores), 400);
            return;
        }
        
        if (empty($boletosValidos)) {
            $db->rollBack();
            sendError('No hay boletos válidos para procesar', 400);
            return;
        }
        
        // Calcular monto total
        $precioBoleto = floatval($sorteo['precio_boleto']);
        $cantidadBoletos = count($boletosValidos);
        $montoCalculado = $precioBoleto * $cantidadBoletos;
        
        // Validar monto si se proporciona
        if ($montoTotal !== null) {
            $montoTotal = floatval($montoTotal);
            $diferencia = abs($montoCalculado - $montoTotal);
            // Permitir pequeña diferencia por redondeo (0.01)
            if ($diferencia > 0.01) {
                $db->rollBack();
                sendError("El monto total no coincide. Esperado: $montoCalculado, Recibido: $montoTotal", 400);
                return;
            }
        } else {
            $montoTotal = $montoCalculado;
        }
        
        // Crear transacción
        $stmt = $db->prepare("
            INSERT INTO transacciones (
                id_usuario,
                monto_total,
                metodo_pago,
                referencia_pago,
                comprobante_url,
                estado_pago
            ) VALUES (
                :id_usuario,
                :monto_total,
                :metodo_pago,
                :referencia_pago,
                :comprobante_url,
                'Pendiente'
            )
        ");
        
        $stmt->execute([
            ':id_usuario' => $usuarioId,
            ':monto_total' => $montoTotal,
            ':metodo_pago' => $metodoPago,
            ':referencia_pago' => $referenciaPago ?: null,
            ':comprobante_url' => $comprobanteUrl ?: null
        ]);
        
        $idTransaccion = $db->lastInsertId();
        
        // Asociar boletos a la transacción
        $stmt = $db->prepare("
            INSERT INTO detalle_transaccion_boletos (id_transaccion, id_boleto)
            VALUES (:id_transaccion, :id_boleto)
        ");
        
        foreach ($boletosValidos as $boleto) {
            $stmt->execute([
                ':id_transaccion' => $idTransaccion,
                ':id_boleto' => $boleto['id_boleto']
            ]);
        }
        
        // Confirmar transacción
        $db->commit();
        
        // Obtener información completa de la transacción
        $stmt = $db->prepare("
            SELECT 
                t.id_transaccion,
                t.monto_total,
                t.metodo_pago,
                t.referencia_pago,
                t.comprobante_url,
                t.estado_pago,
                t.fecha_creacion,
                s.titulo as sorteo_titulo,
                s.id_sorteo
            FROM transacciones t
            LEFT JOIN detalle_transaccion_boletos dtb ON t.id_transaccion = dtb.id_transaccion
            LEFT JOIN boletos b ON dtb.id_boleto = b.id_boleto
            LEFT JOIN sorteos s ON b.id_sorteo = s.id_sorteo
            WHERE t.id_transaccion = :id_transaccion
            LIMIT 1
        ");
        $stmt->execute([':id_transaccion' => $idTransaccion]);
        $transaccion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Obtener números de boletos
        $stmt = $db->prepare("
            SELECT b.numero_boleto
            FROM detalle_transaccion_boletos dtb
            INNER JOIN boletos b ON dtb.id_boleto = b.id_boleto
            WHERE dtb.id_transaccion = :id_transaccion
            ORDER BY CAST(b.numero_boleto AS UNSIGNED) ASC
        ");
        $stmt->execute([':id_transaccion' => $idTransaccion]);
        $numerosBoletosTransaccion = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode([
            'success' => true,
            'message' => 'Transacción creada exitosamente',
            'data' => [
                'id_transaccion' => $idTransaccion,
                'id_sorteo' => $idSorteo,
                'sorteo_titulo' => $transaccion['sorteo_titulo'] ?? '',
                'monto_total' => floatval($montoTotal),
                'metodo_pago' => $metodoPago,
                'referencia_pago' => $referenciaPago,
                'comprobante_url' => $comprobanteUrl,
                'estado_pago' => 'Pendiente',
                'fecha_creacion' => $transaccion['fecha_creacion'] ?? date('Y-m-d H:i:s'),
                'boletos' => $numerosBoletosTransaccion,
                'total_boletos' => count($numerosBoletosTransaccion)
            ]
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        error_log("Error en createTransaction: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        sendError('Error al crear la transacción: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        error_log("Error general en createTransaction: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        sendError('Error al crear la transacción: ' . $e->getMessage(), 500);
    }
}

/**
 * Obtiene las transacciones del usuario
 */
function getMyTransactions($db, $usuarioId) {
    try {
        $stmt = $db->prepare("
            SELECT DISTINCT
                t.id_transaccion,
                t.monto_total,
                t.metodo_pago,
                t.referencia_pago,
                t.comprobante_url,
                t.estado_pago,
                t.fecha_creacion,
                s.titulo as sorteo_titulo,
                s.id_sorteo
            FROM transacciones t
            LEFT JOIN detalle_transaccion_boletos dtb ON t.id_transaccion = dtb.id_transaccion
            LEFT JOIN boletos b ON dtb.id_boleto = b.id_boleto
            LEFT JOIN sorteos s ON b.id_sorteo = s.id_sorteo
            WHERE t.id_usuario = :usuario_id
            ORDER BY t.fecha_creacion DESC
        ");
        $stmt->execute([':usuario_id' => $usuarioId]);
        $transacciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener boletos para cada transacción
        $transaccionesCompletas = [];
        foreach ($transacciones as $transaccion) {
            $stmt = $db->prepare("
                SELECT b.numero_boleto
                FROM detalle_transaccion_boletos dtb
                INNER JOIN boletos b ON dtb.id_boleto = b.id_boleto
                WHERE dtb.id_transaccion = :id_transaccion
                ORDER BY CAST(b.numero_boleto AS UNSIGNED) ASC
            ");
            $stmt->execute([':id_transaccion' => $transaccion['id_transaccion']]);
            $boletos = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $transaccionesCompletas[] = [
                'id_transaccion' => $transaccion['id_transaccion'],
                'id_sorteo' => $transaccion['id_sorteo'],
                'sorteo_titulo' => $transaccion['sorteo_titulo'] ?? '',
                'monto_total' => floatval($transaccion['monto_total']),
                'metodo_pago' => $transaccion['metodo_pago'],
                'referencia_pago' => $transaccion['referencia_pago'],
                'comprobante_url' => $transaccion['comprobante_url'],
                'estado_pago' => $transaccion['estado_pago'],
                'fecha_creacion' => $transaccion['fecha_creacion'],
                'boletos' => $boletos,
                'total_boletos' => count($boletos)
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'transacciones' => $transaccionesCompletas,
                'total' => count($transaccionesCompletas)
            ]
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        error_log("Error en getMyTransactions: " . $e->getMessage());
        sendError('Error al obtener transacciones: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        error_log("Error general en getMyTransactions: " . $e->getMessage());
        sendError('Error al obtener transacciones: ' . $e->getMessage(), 500);
    }
}

/**
 * Obtiene los detalles de una transacción específica
 */
function getTransactionDetails($db, $usuarioId, $idTransaccion) {
    try {
        $stmt = $db->prepare("
            SELECT DISTINCT
                t.id_transaccion,
                t.monto_total,
                t.metodo_pago,
                t.referencia_pago,
                t.comprobante_url,
                t.estado_pago,
                t.fecha_creacion,
                s.titulo as sorteo_titulo,
                s.id_sorteo
            FROM transacciones t
            LEFT JOIN detalle_transaccion_boletos dtb ON t.id_transaccion = dtb.id_transaccion
            LEFT JOIN boletos b ON dtb.id_boleto = b.id_boleto
            LEFT JOIN sorteos s ON b.id_sorteo = s.id_sorteo
            WHERE t.id_transaccion = :id_transaccion
            AND t.id_usuario = :usuario_id
            LIMIT 1
        ");
        $stmt->execute([
            ':id_transaccion' => $idTransaccion,
            ':usuario_id' => $usuarioId
        ]);
        $transaccion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$transaccion) {
            sendError('Transacción no encontrada', 404);
            return;
        }
        
        // Obtener boletos
        $stmt = $db->prepare("
            SELECT b.numero_boleto, b.estado
            FROM detalle_transaccion_boletos dtb
            INNER JOIN boletos b ON dtb.id_boleto = b.id_boleto
            WHERE dtb.id_transaccion = :id_transaccion
            ORDER BY CAST(b.numero_boleto AS UNSIGNED) ASC
        ");
        $stmt->execute([':id_transaccion' => $idTransaccion]);
        $boletos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'id_transaccion' => $transaccion['id_transaccion'],
                'id_sorteo' => $transaccion['id_sorteo'],
                'sorteo_titulo' => $transaccion['sorteo_titulo'] ?? '',
                'monto_total' => floatval($transaccion['monto_total']),
                'metodo_pago' => $transaccion['metodo_pago'],
                'referencia_pago' => $transaccion['referencia_pago'],
                'comprobante_url' => $transaccion['comprobante_url'],
                'estado_pago' => $transaccion['estado_pago'],
                'fecha_creacion' => $transaccion['fecha_creacion'],
                'boletos' => $boletos,
                'total_boletos' => count($boletos)
            ]
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        error_log("Error en getTransactionDetails: " . $e->getMessage());
        sendError('Error al obtener detalles de la transacción: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        error_log("Error general en getTransactionDetails: " . $e->getMessage());
        sendError('Error al obtener detalles de la transacción: ' . $e->getMessage(), 500);
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
