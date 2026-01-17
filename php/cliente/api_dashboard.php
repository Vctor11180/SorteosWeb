<?php
/**
 * API de Dashboard para Clientes
 * Sistema de Sorteos Web
 * 
 * Endpoints:
 * - GET ?action=get_stats - Obtener todas las estadísticas del dashboard del cliente
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
    
    // Solo permitir métodos GET para esta API
    if ($method !== 'GET') {
        ob_clean();
        sendError('Método no permitido. Solo se permite GET.', 405);
        exit;
    }
    
    switch ($action) {
        case 'get_stats':
            ob_clean();
            getDashboardStats($db, $usuarioId);
            break;
            
        default:
            ob_clean();
            sendError('Acción no válida. Usa action=get_stats', 400);
            break;
    }
    
} catch (PDOException $e) {
    ob_clean();
    error_log("Error PDO en api_dashboard.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error de base de datos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Exception $e) {
    ob_clean();
    error_log("Error general en api_dashboard.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error del servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Obtiene todas las estadísticas del dashboard del cliente
 * 
 * @param PDO $db Conexión a la base de datos
 * @param int $usuarioId ID del usuario
 */
function getDashboardStats($db, $usuarioId) {
    try {
        // 1. Obtener boletos activos (ya reservados o vendidos)
        $stmt = $db->prepare("
            SELECT COUNT(*) as total
            FROM boletos b
            INNER JOIN sorteos s ON b.id_sorteo = s.id_sorteo
            WHERE b.id_usuario_actual = :usuario_id
            AND b.estado IN ('Reservado', 'Vendido')
            AND s.estado IN ('Activo', 'Finalizado')
        ");
        $stmt->execute([':usuario_id' => $usuarioId]);
        $boletosActivos = intval($stmt->fetchColumn());
        
        // 2. Obtener boletos nuevos (últimos 7 días)
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT b.id_boleto) as total
            FROM boletos b
            INNER JOIN detalle_transaccion_boletos dtb ON b.id_boleto = dtb.id_boleto
            INNER JOIN transacciones t ON dtb.id_transaccion = t.id_transaccion
            WHERE b.id_usuario_actual = :usuario_id
            AND t.fecha_creacion >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            AND b.estado IN ('Reservado', 'Vendido')
        ");
        $stmt->execute([':usuario_id' => $usuarioId]);
        $boletosNuevos = intval($stmt->fetchColumn());
        
        // 3. Obtener ganancias totales (desde tabla ganadores, solo entregados)
        $stmt = $db->prepare("
            SELECT 
                SUM(CAST(JSON_EXTRACT(premio_detalle, '$.valor') AS DECIMAL(10,2))) as total_ganancias
            FROM ganadores
            WHERE id_usuario = :usuario_id
            AND entregado = 1
        ");
        $stmt->execute([':usuario_id' => $usuarioId]);
        $resultGanancias = $stmt->fetch(PDO::FETCH_ASSOC);
        $gananciasTotales = floatval($resultGanancias['total_ganancias'] ?? 0);
        
        // 4. Calcular crecimiento de ganancias (mes actual vs mes anterior)
        // Ganancias del mes actual
        $stmt = $db->prepare("
            SELECT 
                SUM(CAST(JSON_EXTRACT(premio_detalle, '$.valor') AS DECIMAL(10,2))) as total
            FROM ganadores
            WHERE id_usuario = :usuario_id
            AND entregado = 1
            AND MONTH(fecha_anuncio) = MONTH(CURRENT_DATE())
            AND YEAR(fecha_anuncio) = YEAR(CURRENT_DATE())
        ");
        $stmt->execute([':usuario_id' => $usuarioId]);
        $gananciasMesActual = floatval($stmt->fetchColumn() ?? 0);
        
        // Ganancias del mes anterior
        $stmt = $db->prepare("
            SELECT 
                SUM(CAST(JSON_EXTRACT(premio_detalle, '$.valor') AS DECIMAL(10,2))) as total
            FROM ganadores
            WHERE id_usuario = :usuario_id
            AND entregado = 1
            AND MONTH(fecha_anuncio) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
            AND YEAR(fecha_anuncio) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
        ");
        $stmt->execute([':usuario_id' => $usuarioId]);
        $gananciasMesAnterior = floatval($stmt->fetchColumn() ?? 0);
        
        // Calcular porcentaje de crecimiento
        $crecimientoPorcentaje = 0;
        $crecimientoTexto = '0%';
        if ($gananciasMesAnterior > 0) {
            $crecimientoPorcentaje = (($gananciasMesActual - $gananciasMesAnterior) / $gananciasMesAnterior) * 100;
            $signo = $crecimientoPorcentaje >= 0 ? '+' : '';
            $crecimientoTexto = $signo . number_format($crecimientoPorcentaje, 1) . '%';
        } else if ($gananciasMesActual > 0) {
            $crecimientoTexto = '+100%';
            $crecimientoPorcentaje = 100;
        } else {
            $crecimientoTexto = '0%';
        }
        
        // Agregar "mes" al texto
        $crecimientoGanancias = $crecimientoTexto . ' mes';
        
        // 5. Calcular puntos de lealtad (10 puntos por boleto comprado/vendido)
        $stmt = $db->prepare("
            SELECT COUNT(*) as total_boletos_comprados
            FROM boletos
            WHERE id_usuario_actual = :usuario_id
            AND estado = 'Vendido'
        ");
        $stmt->execute([':usuario_id' => $usuarioId]);
        $totalBoletosComprados = intval($stmt->fetchColumn());
        
        // Calcular puntos base (10 puntos por boleto)
        $puntosBase = $totalBoletosComprados * 10;
        
        // Puntos adicionales por ganancias (1 punto por cada $10 en ganancias)
        $puntosPorGanancias = floor($gananciasTotales / 10);
        
        // Puntos totales
        $puntosLealtad = $puntosBase + $puntosPorGanancias;
        
        // 6. Determinar nivel de lealtad basado en puntos
        $nivelLealtad = 'Nivel Inicial';
        if ($puntosLealtad >= 1000) {
            $nivelLealtad = 'Nivel Oro';
        } else if ($puntosLealtad >= 500) {
            $nivelLealtad = 'Nivel Plata';
        } else if ($puntosLealtad >= 200) {
            $nivelLealtad = 'Nivel Bronce';
        }
        
        // 7. Obtener saldo disponible del usuario
        $stmt = $db->prepare("
            SELECT saldo_disponible
            FROM usuarios
            WHERE id_usuario = :usuario_id
        ");
        $stmt->execute([':usuario_id' => $usuarioId]);
        $saldoDisponible = floatval($stmt->fetchColumn() ?? 0);
        
        // 8. Obtener transacciones pendientes
        $stmt = $db->prepare("
            SELECT COUNT(*) as total
            FROM transacciones
            WHERE id_usuario = :usuario_id
            AND estado_pago = 'Pendiente'
        ");
        $stmt->execute([':usuario_id' => $usuarioId]);
        $transaccionesPendientes = intval($stmt->fetchColumn());
        
        // 9. Obtener sorteos en los que participa el usuario
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT b.id_sorteo) as total
            FROM boletos b
            INNER JOIN sorteos s ON b.id_sorteo = s.id_sorteo
            WHERE b.id_usuario_actual = :usuario_id
            AND b.estado IN ('Reservado', 'Vendido')
            AND s.estado = 'Activo'
        ");
        $stmt->execute([':usuario_id' => $usuarioId]);
        $sorteosParticipando = intval($stmt->fetchColumn());
        
        // Construir respuesta
        $stats = [
            'boletos_activos' => $boletosActivos,
            'boletos_nuevos' => $boletosNuevos,
            'ganancias_totales' => round($gananciasTotales, 2),
            'crecimiento_ganancias' => $crecimientoGanancias,
            'crecimiento_porcentaje' => round($crecimientoPorcentaje, 1),
            'puntos_lealtad' => $puntosLealtad,
            'nivel_lealtad' => $nivelLealtad,
            'saldo_disponible' => round($saldoDisponible, 2),
            'transacciones_pendientes' => $transaccionesPendientes,
            'sorteos_participando' => $sorteosParticipando,
            // Datos adicionales para cálculos futuros
            'ganancias_mes_actual' => round($gananciasMesActual, 2),
            'ganancias_mes_anterior' => round($gananciasMesAnterior, 2),
            'total_boletos_comprados' => $totalBoletosComprados,
            'fecha_actualizacion' => date('Y-m-d H:i:s')
        ];
        
        echo json_encode([
            'success' => true,
            'message' => 'Estadísticas obtenidas exitosamente',
            'data' => $stats
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        error_log("Error en getDashboardStats: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        sendError('Error al obtener estadísticas del dashboard: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        error_log("Error general en getDashboardStats: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        sendError('Error al obtener estadísticas del dashboard: ' . $e->getMessage(), 500);
    }
}

/**
 * Envía una respuesta de error
 * 
 * @param string $message Mensaje de error
 * @param int $code Código HTTP (por defecto 400)
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
