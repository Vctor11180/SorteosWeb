<?php
/**
 * API para Dashboard de Administrador
 */

header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

$conn = getDBConnection();
$action = $_GET['action'] ?? 'kpis';

try {
    switch ($action) {
        case 'kpis':
            getKPIs($conn);
            break;
        case 'chart_data':
            getChartData($conn);
            break;
        case 'recent_activity':
            getRecentActivity($conn);
            break;
        default:
            throw new Exception('Acción no válida');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

/**
 * Obtiene los KPIs principales
 */
function getKPIs($conn) {
    $kpis = [
        'ingresos_totales' => 0,
        'boletos_vendidos' => 0,
        'sorteos_activos' => 0,
        'pagos_pendientes' => 0,
        'tendencia_ingresos' => 0,
        'tendencia_boletos' => 0
    ];
    
    // Ingresos totales (pagos completados)
    $res = $conn->query("SELECT COALESCE(SUM(monto_total), 0) as total FROM transacciones WHERE estado_pago = 'Completado'");
    if ($row = $res->fetch_assoc()) $kpis['ingresos_totales'] = number_format($row['total'], 2, '.', ',');
    
    // Boletos vendidos
    $res = $conn->query("SELECT COUNT(*) as total FROM boletos WHERE estado = 'Vendido'");
    if ($row = $res->fetch_assoc()) $kpis['boletos_vendidos'] = number_format($row['total']);
    
    // Sorteos activos
    $res = $conn->query("SELECT COUNT(*) as total FROM sorteos WHERE estado = 'Activo'");
    if ($row = $res->fetch_assoc()) $kpis['sorteos_activos'] = $row['total'];
    
    // Pagos pendientes
    $res = $conn->query("SELECT COUNT(*) as total FROM transacciones WHERE estado_pago = 'Pendiente'");
    if ($row = $res->fetch_assoc()) $kpis['pagos_pendientes'] = $row['total'];
    
    // Tendencias (dummy por ahora si no hay suficientes datos históricos, o cálculo real simple)
    // ... Implementación real de tendencias requeriría más datos
    $kpis['tendencia_ingresos'] = 12.5; // Dummy positivo
    $kpis['tendencia_boletos'] = 5.3;   // Dummy positivo

    echo json_encode(['success' => true, 'data' => $kpis]);
}

/**
 * Obtiene datos para gráficas
 */
function getChartData($conn) {
    $period = $_GET['period'] ?? '30days';
    // Generar datos simulados o reales dependiendo del periodo
    // Aquí implementamos una lógica básica basada en transacciones reales
    
    $labels = [];
    $values = [];
    
    // Ejemplo para últimos 30 días agrupado por día
    $query = "SELECT DATE(fecha_creacion) as fecha, SUM(monto_total) as total 
              FROM transacciones 
              WHERE estado_pago = 'Completado' AND fecha_creacion >= DATE_SUB(NOW(), INTERVAL 30 DAY)
              GROUP BY DATE(fecha_creacion) 
              ORDER BY fecha_creacion ASC";
    $result = $conn->query($query);
    
    while($row = $result->fetch_assoc()) {
        $labels[] = date('d M', strtotime($row['fecha']));
        $values[] = floatval($row['total']);
    }
    
    // Si no hay datos, enviar dummy para que no se vea vacío
    if (empty($values)) {
        $labels = ['01', '05', '10', '15', '20', '25', '30'];
        $values = [0, 0, 0, 0, 0, 0, 0];
    }
    
    echo json_encode([
        'success' => true, 
        'data' => [
            'labels' => $labels,
            'values' => $values
        ]
    ]);
}

/**
 * Actividad reciente (pagos y sorteos por finalizar)
 */
function getRecentActivity($conn) {
    // Sorteos por finalizar
    $sorteos = [];
    $resSorteos = $conn->query("SELECT id_sorteo, titulo, imagen_url, fecha_fin FROM sorteos WHERE estado = 'Activo' ORDER BY fecha_fin ASC LIMIT 3");
    while($row = $resSorteos->fetch_assoc()) {
        // Calc tiempo restante
        $diff = (new DateTime($row['fecha_fin']))->diff(new DateTime());
        $restante = $diff->days . 'd ' . $diff->h . 'h';
        
        $sorteos[] = [
            'titulo' => $row['titulo'],
            'imagen' => $row['imagen_url'],
            'tiempo_restante' => $restante,
            'urgente' => $diff->days == 0
        ];
    }
    
    // Pagos pendientes
    $pagos = [];
    $resPagos = $conn->query("SELECT t.gateway_reference, t.monto_total, u.primer_nombre, u.apellido_paterno, u.email, s.titulo 
                             FROM transacciones t 
                             JOIN usuarios u ON t.id_usuario = u.id_usuario
                             LEFT JOIN detalle_transaccion_boletos dt ON t.id_transaccion = dt.id_transaccion
                             LEFT JOIN boletos b ON dt.id_boleto = b.id_boleto
                             LEFT JOIN sorteos s ON b.id_sorteo = s.id_sorteo
                             WHERE t.estado_pago = 'Pendiente' 
                             GROUP BY t.id_transaccion
                             ORDER BY t.fecha_creacion DESC LIMIT 5");
                             
    while($row = $resPagos->fetch_assoc()) {
        $pagos[] = [
            'usuario' => $row['primer_nombre'] . ' ' . $row['apellido_paterno'],
            'email' => $row['email'],
            'monto' => $row['monto_total'],
            'referencia' => $row['gateway_reference'] ?? 'N/A',
            'sorteo' => $row['titulo'] ?? 'Desconocido',
            'iniciales' => strtoupper(substr($row['primer_nombre'],0,1) . substr($row['apellido_paterno'],0,1))
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'sorteos_finalizando' => $sorteos,
            'pagos_pendientes' => $pagos
        ]
    ]);
}
?>
