<?php
/**
 * API de Sorteos para Clientes
 * Sistema de Sorteos Web
 * 
 * Endpoints:
 * - GET ?action=list_active - Listar sorteos activos
 * - GET ?action=get_details&id={id} - Detalles de un sorteo
 * - GET ?action=get_stats&id={id} - Estadísticas de un sorteo
 */

// Desactivar display_errors para evitar output antes del JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Iniciar output buffering para capturar cualquier output inesperado
ob_start();

// Configurar headers JSON primero
header('Content-Type: application/json; charset=utf-8');

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    ob_clean(); // Limpiar cualquier output
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'No autorizado. Debes iniciar sesión.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Incluir configuración de base de datos
    if (!file_exists(__DIR__ . '/config/database.php')) {
        throw new Exception('Archivo de configuración de base de datos no encontrado');
    }
    
    require_once __DIR__ . '/config/database.php';
    
    // Verificar que la función getDB existe
    if (!function_exists('getDB')) {
        throw new Exception('Función getDB no está disponible');
    }
    
    $db = getDB();
    
    // Verificar que la conexión se estableció correctamente
    if (!$db) {
        throw new Exception('No se pudo establecer conexión con la base de datos');
    }
    
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $action = $_GET['action'] ?? '';
    
    // Solo permitir métodos GET para esta API
    if ($method !== 'GET') {
        ob_clean();
        sendError('Método no permitido. Solo se permite GET.', 405);
        exit;
    }
    
    switch ($action) {
        case 'list_active':
            ob_clean(); // Limpiar buffer antes de enviar respuesta
            listActiveSorteos($db);
            break;
            
        case 'get_details':
            $id = $_GET['id'] ?? null;
            if ($id) {
                ob_clean();
                getSorteoDetails($db, intval($id));
            } else {
                ob_clean();
                sendError('ID de sorteo requerido', 400);
            }
            break;
            
        case 'get_stats':
            $id = $_GET['id'] ?? null;
            if ($id) {
                ob_clean();
                getSorteoStats($db, intval($id));
            } else {
                ob_clean();
                sendError('ID de sorteo requerido', 400);
            }
            break;
            
        default:
            // Si no se especifica acción, listar sorteos activos por defecto
            ob_clean();
            listActiveSorteos($db);
            break;
    }
    
} catch (PDOException $e) {
    ob_clean();
    error_log("Error PDO en api_sorteos.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error de base de datos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Exception $e) {
    ob_clean();
    error_log("Error general en api_sorteos.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error del servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Lista todos los sorteos activos
 */
function listActiveSorteos($db) {
    try {
        // Obtener parámetros opcionales
        $search = $_GET['search'] ?? '';
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 0;
        
        // Construir consulta base
        $sql = "
            SELECT 
                s.id_sorteo,
                s.titulo,
                s.descripcion,
                s.precio_boleto,
                s.total_boletos_crear,
                s.fecha_inicio,
                s.fecha_fin,
                s.imagen_url,
                s.caracteristicas,
                s.estado,
                s.id_creador
            FROM sorteos s
            WHERE s.estado = 'Activo'
        ";
        
        $params = [];
        
        // Agregar búsqueda si existe
        if (!empty($search)) {
            $sql .= " AND (s.titulo LIKE :search OR s.descripcion LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        
        $sql .= " ORDER BY s.fecha_fin ASC";
        
        // Agregar límite si existe
        if ($limit > 0) {
            $sql .= " LIMIT :limit";
            $params[':limit'] = $limit;
        }
        
        $stmt = $db->prepare($sql);
        
        // Bind de parámetros
        foreach ($params as $key => $value) {
            if ($key === ':limit') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }
        
        $stmt->execute();
        $sorteos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Procesar cada sorteo para agregar información calculada
        $sorteosProcesados = [];
        foreach ($sorteos as $sorteo) {
            try {
                $sorteoProcesado = processSorteoData($db, $sorteo);
                if ($sorteoProcesado) {
                    $sorteosProcesados[] = $sorteoProcesado;
                }
            } catch (Exception $e) {
                error_log("Error procesando sorteo ID {$sorteo['id_sorteo']}: " . $e->getMessage());
                // Continuar con el siguiente sorteo en lugar de fallar todo
            }
        }
        
        echo json_encode([
            'success' => true,
            'data' => $sorteosProcesados,
            'total' => count($sorteosProcesados)
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        error_log("Error en listActiveSorteos: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        sendError('Error al obtener sorteos activos: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        error_log("Error general en listActiveSorteos: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        sendError('Error al obtener sorteos activos: ' . $e->getMessage(), 500);
    }
}

/**
 * Obtiene los detalles de un sorteo específico
 */
function getSorteoDetails($db, $idSorteo) {
    try {
        $stmt = $db->prepare("
            SELECT 
                s.id_sorteo,
                s.titulo,
                s.descripcion,
                s.precio_boleto,
                s.total_boletos_crear,
                s.fecha_inicio,
                s.fecha_fin,
                s.imagen_url,
                s.caracteristicas,
                s.estado,
                s.id_creador
            FROM sorteos s
            WHERE s.id_sorteo = :id_sorteo
        ");
        
        $stmt->execute([':id_sorteo' => $idSorteo]);
        $sorteo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$sorteo) {
            sendError('Sorteo no encontrado', 404);
            return;
        }
        
        // Verificar que el sorteo esté activo (opcional: permitir ver detalles de sorteos finalizados también)
        // Por ahora solo mostramos activos
        if ($sorteo['estado'] !== 'Activo') {
            sendError('Este sorteo no está disponible', 404);
            return;
        }
        
        $sorteoProcesado = processSorteoData($db, $sorteo);
        
        if (!$sorteoProcesado) {
            sendError('Error al procesar datos del sorteo', 500);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $sorteoProcesado
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        error_log("Error en getSorteoDetails: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        sendError('Error al obtener detalles del sorteo: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        error_log("Error general en getSorteoDetails: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        sendError('Error al obtener detalles del sorteo: ' . $e->getMessage(), 500);
    }
}

/**
 * Obtiene estadísticas de un sorteo
 */
function getSorteoStats($db, $idSorteo) {
    try {
        // Verificar que el sorteo existe
        $stmt = $db->prepare("SELECT id_sorteo, estado FROM sorteos WHERE id_sorteo = :id_sorteo");
        $stmt->execute([':id_sorteo' => $idSorteo]);
        $sorteo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$sorteo) {
            sendError('Sorteo no encontrado', 404);
            return;
        }
        
        // Liberar reservas expiradas (más de 15 minutos) antes de contar
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
        
        // Obtener estadísticas de boletos
        $stmt = $db->prepare("
            SELECT 
                COUNT(CASE WHEN estado = 'Vendido' THEN 1 END) as boletos_vendidos,
                COUNT(CASE WHEN estado = 'Reservado' THEN 1 END) as boletos_reservados,
                COUNT(CASE WHEN estado = 'Disponible' THEN 1 END) as boletos_disponibles,
                COUNT(*) as total_boletos
            FROM boletos
            WHERE id_sorteo = :id_sorteo
        ");
        
        $stmt->execute([':id_sorteo' => $idSorteo]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $boletosVendidos = intval($stats['boletos_vendidos'] ?? 0);
        $boletosReservados = intval($stats['boletos_reservados'] ?? 0);
        $boletosDisponibles = intval($stats['boletos_disponibles'] ?? 0);
        $totalBoletos = intval($stats['total_boletos'] ?? 0);
        
        // Obtener total de boletos del sorteo
        $stmt = $db->prepare("SELECT total_boletos_crear FROM sorteos WHERE id_sorteo = :id_sorteo");
        $stmt->execute([':id_sorteo' => $idSorteo]);
        $sorteoInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalBoletosCrear = intval($sorteoInfo['total_boletos_crear'] ?? 0);
        
        // Si no hay boletos creados aún, los disponibles son el total
        if ($totalBoletos == 0 && $totalBoletosCrear > 0) {
            $boletosDisponibles = $totalBoletosCrear;
            $totalBoletos = $totalBoletosCrear;
        }
        
        // Calcular boletos ocupados (vendidos + reservados) para el contador
        $boletosOcupados = $boletosVendidos + $boletosReservados;
        
        // Calcular porcentajes (usando ocupados para mostrar progreso real)
        $porcentajeVendido = $totalBoletosCrear > 0 
            ? round(($boletosOcupados / $totalBoletosCrear) * 100, 1) 
            : 0;
        
        $porcentajeReservado = $totalBoletosCrear > 0 
            ? round(($boletosReservados / $totalBoletosCrear) * 100, 1) 
            : 0;
        
        $porcentajeDisponible = $totalBoletosCrear > 0 
            ? round(($boletosDisponibles / $totalBoletosCrear) * 100, 1) 
            : 0;
        
        echo json_encode([
            'success' => true,
            'data' => [
                'id_sorteo' => $idSorteo,
                'total_boletos' => $totalBoletosCrear,
                'boletos_vendidos' => $boletosOcupados, // Incluye vendidos + reservados para el contador
                'boletos_reservados' => $boletosReservados,
                'boletos_disponibles' => $boletosDisponibles,
                'porcentaje_vendido' => $porcentajeVendido,
                'porcentaje_reservado' => $porcentajeReservado,
                'porcentaje_disponible' => $porcentajeDisponible
            ]
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        error_log("Error en getSorteoStats: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        sendError('Error al obtener estadísticas del sorteo: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        error_log("Error general en getSorteoStats: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        sendError('Error al obtener estadísticas del sorteo: ' . $e->getMessage(), 500);
    }
}

/**
 * Procesa los datos de un sorteo agregando información calculada
 */
function processSorteoData($db, $sorteo) {
    try {
        $idSorteo = $sorteo['id_sorteo'];
        
        // Contar boletos por estado
        $stmt = $db->prepare("
            SELECT 
                COUNT(CASE WHEN estado = 'Vendido' THEN 1 END) as boletos_vendidos,
                COUNT(CASE WHEN estado = 'Reservado' THEN 1 END) as boletos_reservados,
                COUNT(CASE WHEN estado = 'Disponible' THEN 1 END) as boletos_disponibles
            FROM boletos
            WHERE id_sorteo = :id_sorteo
        ");
        $stmt->execute([':id_sorteo' => $idSorteo]);
        $boletosInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $boletosVendidos = intval($boletosInfo['boletos_vendidos'] ?? 0);
        $boletosReservados = intval($boletosInfo['boletos_reservados'] ?? 0);
        $boletosDisponibles = intval($boletosInfo['boletos_disponibles'] ?? 0);
        $boletosTotales = intval($sorteo['total_boletos_crear'] ?? 0);
        
        // Liberar reservas expiradas (más de 15 minutos) antes de contar
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
        
        // Volver a contar después de liberar reservas expiradas
        $stmt = $db->prepare("
            SELECT 
                COUNT(CASE WHEN estado = 'Vendido' THEN 1 END) as boletos_vendidos,
                COUNT(CASE WHEN estado = 'Reservado' THEN 1 END) as boletos_reservados,
                COUNT(CASE WHEN estado = 'Disponible' THEN 1 END) as boletos_disponibles
            FROM boletos
            WHERE id_sorteo = :id_sorteo
        ");
        $stmt->execute([':id_sorteo' => $idSorteo]);
        $boletosInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $boletosVendidos = intval($boletosInfo['boletos_vendidos'] ?? 0);
        $boletosReservados = intval($boletosInfo['boletos_reservados'] ?? 0);
        $boletosDisponibles = intval($boletosInfo['boletos_disponibles'] ?? 0);
        
        // Si no hay boletos creados aún, los disponibles son el total
        if ($boletosDisponibles == 0 && $boletosVendidos == 0 && $boletosReservados == 0 && $boletosTotales > 0) {
            $boletosDisponibles = $boletosTotales;
        }
        
        // Calcular boletos ocupados (vendidos + reservados) para el contador
        // Los boletos reservados también están ocupados, así que los contamos
        $boletosOcupados = $boletosVendidos + $boletosReservados;
        
        // Calcular porcentaje vendido (usando ocupados para mostrar progreso real)
        $porcentajeVendido = $boletosTotales > 0 
            ? round(($boletosOcupados / $boletosTotales) * 100, 1) 
            : 0;
        
        // Calcular tiempo restante
        $fechaFin = new DateTime($sorteo['fecha_fin']);
        $ahora = new DateTime();
        $totalSegundos = $fechaFin->getTimestamp() - $ahora->getTimestamp();
        
        // Si la fecha ya pasó, mostrar tiempo 0
        if ($totalSegundos < 0) {
            $totalSegundos = 0;
            $tiempoRestante = [
                'dias' => 0,
                'horas' => 0,
                'minutos' => 0,
                'segundos' => 0,
                'total_segundos' => 0
            ];
            $estaPorFinalizar = true;
        } else {
            $diferencia = $ahora->diff($fechaFin);
            $tiempoRestante = [
                'dias' => intval($diferencia->format('%a')),
                'horas' => intval($diferencia->format('%h')),
                'minutos' => intval($diferencia->format('%i')),
                'segundos' => intval($diferencia->format('%s')),
                'total_segundos' => $totalSegundos
            ];
            // Determinar si está por finalizar (menos de 24 horas)
            $estaPorFinalizar = $totalSegundos < 86400;
        }
        
        // Procesar características JSON
        $caracteristicas = null;
        // Verificar si el campo existe y no es NULL
        if (isset($sorteo['caracteristicas']) && $sorteo['caracteristicas'] !== null) {
            // Si es string JSON (puede ser 'null' como string o JSON válido), intentar decodificar
            if (is_string($sorteo['caracteristicas'])) {
                // Si es la cadena 'null', dejar como null
                if (strtolower(trim($sorteo['caracteristicas'])) === 'null') {
                    $caracteristicas = null;
                } else {
                    $caracteristicasDecoded = json_decode($sorteo['caracteristicas'], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($caracteristicasDecoded)) {
                        $caracteristicas = $caracteristicasDecoded;
                    }
                }
            } elseif (is_array($sorteo['caracteristicas'])) {
                // Si ya es array (MySQL 5.7+ puede retornar JSON como array nativo)
                $caracteristicas = $sorteo['caracteristicas'];
            }
        }
        
        return [
            'id_sorteo' => $idSorteo,
            'titulo' => $sorteo['titulo'],
            'descripcion' => $sorteo['descripcion'] ?? '',
            'precio_boleto' => floatval($sorteo['precio_boleto']),
            'total_boletos' => $boletosTotales,
            'boletos_vendidos' => $boletosOcupados, // Incluye vendidos + reservados para el contador
            'boletos_reservados' => $boletosReservados,
            'boletos_disponibles' => $boletosDisponibles,
            'porcentaje_vendido' => $porcentajeVendido,
            'fecha_inicio' => $sorteo['fecha_inicio'],
            'fecha_fin' => $sorteo['fecha_fin'],
            'imagen_url' => $sorteo['imagen_url'] ?? '',
            'caracteristicas' => $caracteristicas,
            'estado' => $sorteo['estado'],
            'tiempo_restante' => $tiempoRestante,
            'esta_por_finalizar' => $estaPorFinalizar,
            'id_creador' => $sorteo['id_creador']
        ];
        
    } catch (Exception $e) {
        error_log("Error en processSorteoData: " . $e->getMessage());
        return null;
    }
}

/**
 * Envía una respuesta de error
 */
function sendError($message, $code = 400) {
    ob_clean(); // Limpiar cualquier output previo
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

?>
