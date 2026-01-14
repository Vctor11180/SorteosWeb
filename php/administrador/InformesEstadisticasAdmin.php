<!DOCTYPE html>
<?php
// Conexión a la base de datos
require_once 'config.php';
$conn = getDBConnection();

// Validar conexión
if (!$conn || $conn->connect_error) {
    die("Error de conexión a la base de datos. Por favor, contacta al administrador.");
}

// Obtener parámetros de filtro
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01'); // Primer día del mes actual
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-t'); // Último día del mes actual
$id_sorteo_filtro = isset($_GET['sorteo']) && $_GET['sorteo'] != 'todos' ? intval($_GET['sorteo']) : null;
$estado_campana = isset($_GET['estado_campana']) ? $_GET['estado_campana'] : 'todas';

/**
 * Obtiene KPIs para la página de informes
 */
function obtenerKPIsInformes($conn, $fecha_inicio, $fecha_fin, $id_sorteo = null) {
    $kpis = [
        'ingresos_totales' => 0,
        'boletos_vendidos' => 0,
        'usuarios_activos' => 0,
        'tasa_conversion' => 0,
        'tendencia_ingresos' => 0,
        'tendencia_boletos' => 0,
        'tendencia_usuarios' => 0,
        'tendencia_conversion' => 0
    ];
    
    if (!$conn || $conn->connect_error) {
        return $kpis;
    }
    
    try {
        // Construir condición de filtro por sorteo
        $condicion_sorteo = '';
        if ($id_sorteo) {
            $condicion_sorteo = " AND s.id_sorteo = " . intval($id_sorteo);
        }
        
        // Ingresos totales en el rango de fechas
        $sql = "SELECT COALESCE(SUM(t.monto_total), 0) as total 
                FROM transacciones t
                LEFT JOIN detalle_transaccion_boletos dtb ON t.id_transaccion = dtb.id_transaccion
                LEFT JOIN boletos b ON dtb.id_boleto = b.id_boleto
                LEFT JOIN sorteos s ON b.id_sorteo = s.id_sorteo
                WHERE t.estado_pago = 'Completado'
                AND DATE(t.fecha_creacion) BETWEEN ? AND ?" . $condicion_sorteo;
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $kpis['ingresos_totales'] = $row['total'];
        }
        $stmt->close();
        
        // Boletos vendidos en el rango
        $sql = "SELECT COUNT(DISTINCT b.id_boleto) as total 
                FROM boletos b
                INNER JOIN detalle_transaccion_boletos dtb ON b.id_boleto = dtb.id_boleto
                INNER JOIN transacciones t ON dtb.id_transaccion = t.id_transaccion
                WHERE b.estado = 'Vendido'
                AND t.estado_pago = 'Completado'
                AND DATE(t.fecha_creacion) BETWEEN ? AND ?" . $condicion_sorteo;
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $kpis['boletos_vendidos'] = $row['total'];
        }
        $stmt->close();
        
        // Usuarios activos (usuarios que han comprado en el rango)
        $sql = "SELECT COUNT(DISTINCT t.id_usuario) as total 
                FROM transacciones t
                LEFT JOIN detalle_transaccion_boletos dtb ON t.id_transaccion = dtb.id_transaccion
                LEFT JOIN boletos b ON dtb.id_boleto = b.id_boleto
                LEFT JOIN sorteos s ON b.id_sorteo = s.id_sorteo
                WHERE t.estado_pago = 'Completado'
                AND DATE(t.fecha_creacion) BETWEEN ? AND ?" . $condicion_sorteo;
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $kpis['usuarios_activos'] = $row['total'];
        }
        $stmt->close();
        
        // Tasa de conversión (usuarios que compraron / total usuarios)
        $total_usuarios = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE estado = 'Activo'")->fetch_assoc()['total'];
        if ($total_usuarios > 0) {
            $kpis['tasa_conversion'] = round(($kpis['usuarios_activos'] / $total_usuarios) * 100, 1);
        }
        
        // Calcular tendencias (comparar con período anterior de misma duración)
        $dias_periodo = (strtotime($fecha_fin) - strtotime($fecha_inicio)) / 86400;
        $fecha_inicio_anterior = date('Y-m-d', strtotime($fecha_inicio . " -$dias_periodo days"));
        $fecha_fin_anterior = date('Y-m-d', strtotime($fecha_inicio . " -1 day"));
        
        // Tendencias de ingresos
        $sql = "SELECT COALESCE(SUM(t.monto_total), 0) as total 
                FROM transacciones t
                LEFT JOIN detalle_transaccion_boletos dtb ON t.id_transaccion = dtb.id_transaccion
                LEFT JOIN boletos b ON dtb.id_boleto = b.id_boleto
                LEFT JOIN sorteos s ON b.id_sorteo = s.id_sorteo
                WHERE t.estado_pago = 'Completado'
                AND DATE(t.fecha_creacion) BETWEEN ? AND ?" . $condicion_sorteo;
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $fecha_inicio_anterior, $fecha_fin_anterior);
        $stmt->execute();
        $result = $stmt->get_result();
        $ingresos_anterior = 0;
        if ($row = $result->fetch_assoc()) {
            $ingresos_anterior = $row['total'] > 0 ? $row['total'] : 1;
        }
        $stmt->close();
        
        if ($ingresos_anterior > 0) {
            $kpis['tendencia_ingresos'] = round((($kpis['ingresos_totales'] - $ingresos_anterior) / $ingresos_anterior) * 100, 1);
        }
        
        // Tendencias de boletos
        $sql = "SELECT COUNT(DISTINCT b.id_boleto) as total 
                FROM boletos b
                INNER JOIN detalle_transaccion_boletos dtb ON b.id_boleto = dtb.id_boleto
                INNER JOIN transacciones t ON dtb.id_transaccion = t.id_transaccion
                WHERE b.estado = 'Vendido'
                AND t.estado_pago = 'Completado'
                AND DATE(t.fecha_creacion) BETWEEN ? AND ?" . $condicion_sorteo;
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $fecha_inicio_anterior, $fecha_fin_anterior);
        $stmt->execute();
        $result = $stmt->get_result();
        $boletos_anterior = 1;
        if ($row = $result->fetch_assoc()) {
            $boletos_anterior = $row['total'] > 0 ? $row['total'] : 1;
        }
        $stmt->close();
        
        if ($boletos_anterior > 0) {
            $kpis['tendencia_boletos'] = round((($kpis['boletos_vendidos'] - $boletos_anterior) / $boletos_anterior) * 100, 1);
        }
        
        // Tendencias de usuarios
        $sql = "SELECT COUNT(DISTINCT t.id_usuario) as total 
                FROM transacciones t
                LEFT JOIN detalle_transaccion_boletos dtb ON t.id_transaccion = dtb.id_transaccion
                LEFT JOIN boletos b ON dtb.id_boleto = b.id_boleto
                LEFT JOIN sorteos s ON b.id_sorteo = s.id_sorteo
                WHERE t.estado_pago = 'Completado'
                AND DATE(t.fecha_creacion) BETWEEN ? AND ?" . $condicion_sorteo;
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $fecha_inicio_anterior, $fecha_fin_anterior);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuarios_anterior = 1;
        if ($row = $result->fetch_assoc()) {
            $usuarios_anterior = $row['total'] > 0 ? $row['total'] : 1;
        }
        $stmt->close();
        
        if ($usuarios_anterior > 0) {
            $kpis['tendencia_usuarios'] = round((($kpis['usuarios_activos'] - $usuarios_anterior) / $usuarios_anterior) * 100, 1);
        }
        
        // Tendencias de conversión
        $conversion_anterior = $usuarios_anterior > 0 && $total_usuarios > 0 ? round(($usuarios_anterior / $total_usuarios) * 100, 1) : 0;
        if ($conversion_anterior > 0) {
            $kpis['tendencia_conversion'] = round($kpis['tasa_conversion'] - $conversion_anterior, 1);
        }
        
    } catch (Exception $e) {
        error_log("Error obteniendo KPIs de informes: " . $e->getMessage());
    }
    
    return $kpis;
}

/**
 * Obtiene datos de tendencia de ingresos para los últimos 6 meses
 */
function obtenerTendenciaIngresos($conn, $id_sorteo = null) {
    $datos = [];
    
    if (!$conn || $conn->connect_error) {
        return $datos;
    }
    
    try {
        $condicion_sorteo = '';
        if ($id_sorteo) {
            $condicion_sorteo = " AND s.id_sorteo = " . intval($id_sorteo);
        }
        
        // Obtener ingresos por mes de los últimos 6 meses
        $sql = "SELECT 
                    DATE_FORMAT(t.fecha_creacion, '%Y-%m') as mes,
                    DATE_FORMAT(t.fecha_creacion, '%b') as mes_nombre,
                    COALESCE(SUM(t.monto_total), 0) as total
                FROM transacciones t
                LEFT JOIN detalle_transaccion_boletos dtb ON t.id_transaccion = dtb.id_transaccion
                LEFT JOIN boletos b ON dtb.id_boleto = b.id_boleto
                LEFT JOIN sorteos s ON b.id_sorteo = s.id_sorteo
                WHERE t.estado_pago = 'Completado'
                AND t.fecha_creacion >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)" . $condicion_sorteo . "
                GROUP BY DATE_FORMAT(t.fecha_creacion, '%Y-%m'), DATE_FORMAT(t.fecha_creacion, '%b')
                ORDER BY mes ASC";
        
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $datos[$row['mes']] = [
                'nombre' => $row['mes_nombre'],
                'total' => (float)$row['total']
            ];
        }
    } catch (Exception $e) {
        error_log("Error obteniendo tendencia de ingresos: " . $e->getMessage());
    }
    
    return $datos;
}

/**
 * Obtiene distribución de estado de boletos
 */
function obtenerEstadoBoletos($conn) {
    $estados = [
        'vendidos' => 0,
        'reservados' => 0,
        'disponibles' => 0,
        'total' => 0
    ];
    
    if (!$conn || $conn->connect_error) {
        return $estados;
    }
    
    try {
        $sql = "SELECT estado, COUNT(*) as total FROM boletos GROUP BY estado";
        $result = $conn->query($sql);
        
        while ($row = $result->fetch_assoc()) {
            $estados[strtolower($row['estado'])] = (int)$row['total'];
            $estados['total'] += (int)$row['total'];
        }
    } catch (Exception $e) {
        error_log("Error obteniendo estado de boletos: " . $e->getMessage());
    }
    
    return $estados;
}

/**
 * Obtiene ventas por sorteo (top sorteos)
 */
function obtenerVentasPorSorteo($conn, $fecha_inicio, $fecha_fin, $limit = 5) {
    $ventas = [];
    
    if (!$conn || $conn->connect_error) {
        return $ventas;
    }
    
    try {
        $sql = "SELECT 
                    s.id_sorteo,
                    s.titulo,
                    COUNT(DISTINCT b.id_boleto) as boletos_vendidos,
                    COALESCE(SUM(t.monto_total), 0) as ingresos
                FROM sorteos s
                LEFT JOIN boletos b ON s.id_sorteo = b.id_sorteo
                LEFT JOIN detalle_transaccion_boletos dtb ON b.id_boleto = dtb.id_boleto
                LEFT JOIN transacciones t ON dtb.id_transaccion = t.id_transaccion
                WHERE (t.estado_pago = 'Completado' OR t.estado_pago IS NULL)
                AND (DATE(t.fecha_creacion) BETWEEN ? AND ? OR t.fecha_creacion IS NULL)
                GROUP BY s.id_sorteo, s.titulo
                HAVING boletos_vendidos > 0
                ORDER BY ingresos DESC
                LIMIT ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $fecha_inicio, $fecha_fin, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $ventas[] = [
                'id' => $row['id_sorteo'],
                'titulo' => $row['titulo'],
                'boletos' => (int)$row['boletos_vendidos'],
                'ingresos' => (float)$row['ingresos']
            ];
        }
        
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error obteniendo ventas por sorteo: " . $e->getMessage());
    }
    
    return $ventas;
}

/**
 * Obtiene efectividad de campañas de marketing
 */
function obtenerEfectividadCampanas($conn, $estado = 'todas') {
    $campanas = [];
    
    if (!$conn || $conn->connect_error) {
        return $campanas;
    }
    
    try {
        // Verificar si existe la tabla
        $table_check = $conn->query("SHOW TABLES LIKE 'campanas_marketing'");
        if ($table_check->num_rows == 0) {
            return $campanas; // Tabla no existe
        }
        
        $condicion_estado = '';
        if ($estado != 'todas') {
            $estado_escaped = $conn->real_escape_string($estado);
            $condicion_estado = " WHERE estado = '$estado_escaped'";
        }
        
        $sql = "SELECT 
                    id_campana,
                    red_social,
                    empresa,
                    costo_inversion,
                    clics_generados,
                    estado
                FROM campanas_marketing" . $condicion_estado . "
                ORDER BY fecha_inicio DESC
                LIMIT 10";
        
        $result = $conn->query($sql);
        
        while ($row = $result->fetch_assoc()) {
            // Calcular ROI aproximado (simplificado)
            $roi = 0;
            if ($row['costo_inversion'] > 0) {
                // ROI = (ingresos estimados - inversión) / inversión * 100
                // Estimación: cada clic genera $X en ingresos (valor ficticio para ejemplo)
                $ingresos_estimados = $row['clics_generados'] * 2.5; // $2.5 por clic estimado
                $roi = (($ingresos_estimados - $row['costo_inversion']) / $row['costo_inversion']) * 100;
            }
            
            $campanas[] = [
                'id' => $row['id_campana'],
                'nombre' => $row['red_social'] . ($row['empresa'] ? ' - ' . $row['empresa'] : ''),
                'estado' => $row['estado'],
                'inversion' => (float)$row['costo_inversion'],
                'roi' => round($roi, 1)
            ];
        }
    } catch (Exception $e) {
        error_log("Error obteniendo efectividad de campañas: " . $e->getMessage());
    }
    
    return $campanas;
}

/**
 * Obtiene lista de sorteos para el filtro
 */
function obtenerListaSorteos($conn) {
    $sorteos = [];
    
    if (!$conn || $conn->connect_error) {
        return $sorteos;
    }
    
    try {
        $sql = "SELECT id_sorteo, titulo FROM sorteos ORDER BY titulo ASC";
        $result = $conn->query($sql);
        
        while ($row = $result->fetch_assoc()) {
            $sorteos[] = [
                'id' => $row['id_sorteo'],
                'titulo' => $row['titulo']
            ];
        }
    } catch (Exception $e) {
        error_log("Error obteniendo lista de sorteos: " . $e->getMessage());
    }
    
    return $sorteos;
}

/**
 * Genera SVG del gráfico de tendencia de ingresos
 */
function generarGraficoTendenciaIngresos($datos_tendencia) {
    // Generar últimos 6 meses
    $meses = [];
    $valores = [];
    $fecha_actual = new DateTime();
    
    for ($i = 5; $i >= 0; $i--) {
        $fecha = clone $fecha_actual;
        $fecha->modify("-$i months");
        $mes_key = $fecha->format('Y-m');
        $mes_nombre = $fecha->format('M');
        $meses[] = ['key' => $mes_key, 'nombre' => $mes_nombre];
        $valores[] = isset($datos_tendencia[$mes_key]) ? $datos_tendencia[$mes_key]['total'] : 0;
    }
    
    if (empty($valores) || max($valores) == 0) {
        return '<div class="text-text-secondary text-center py-8">No hay datos disponibles</div>';
    }
    
    $max_valor = max($valores);
    $max_valor = $max_valor > 0 ? $max_valor : 1;
    
    // Calcular puntos
    $puntos = [];
    $num_puntos = count($valores);
    for ($i = 0; $i < $num_puntos; $i++) {
        $x = ($i / max(($num_puntos - 1), 1)) * 100;
        $y = 100 - (($valores[$i] / $max_valor) * 80); // 80% del alto para dejar margen
        $puntos[] = ['x' => $x, 'y' => $y];
    }
    
    // Generar path
    $path_area = "M{$puntos[0]['x']},100";
    $path_linea = "M{$puntos[0]['x']},{$puntos[0]['y']}";
    
    for ($i = 1; $i < $num_puntos; $i++) {
        $x_medio = ($puntos[$i-1]['x'] + $puntos[$i]['x']) / 2;
        $path_area .= " C{$x_medio},{$puntos[$i-1]['y']} {$x_medio},{$puntos[$i]['y']} {$puntos[$i]['x']},{$puntos[$i]['y']}";
        $path_linea .= " C{$x_medio},{$puntos[$i-1]['y']} {$x_medio},{$puntos[$i]['y']} {$puntos[$i]['x']},{$puntos[$i]['y']}";
    }
    $path_area .= " L{$puntos[$num_puntos-1]['x']},100 Z";
    
    $etiquetas_meses = '';
    foreach ($meses as $mes) {
        $etiquetas_meses .= '<span>' . $mes['nombre'] . '</span>';
    }
    
    return <<<SVG
<div class="relative h-64 w-full mt-auto">
<div class="absolute inset-0 flex flex-col justify-between text-text-secondary text-xs font-medium">
<div class="border-b border-[#3b4354]/30 w-full h-0 flex items-center"><span class="-mt-6">50k</span></div>
<div class="border-b border-[#3b4354]/30 w-full h-0 flex items-center"><span class="-mt-6">40k</span></div>
<div class="border-b border-[#3b4354]/30 w-full h-0 flex items-center"><span class="-mt-6">30k</span></div>
<div class="border-b border-[#3b4354]/30 w-full h-0 flex items-center"><span class="-mt-6">20k</span></div>
<div class="border-b border-[#3b4354]/30 w-full h-0 flex items-center"><span class="-mt-6">10k</span></div>
</div>
<svg class="absolute inset-0 h-full w-full overflow-visible" preserveAspectRatio="none" viewBox="0 0 100 100">
<defs>
<linearGradient id="gradientLine" x1="0" x2="0" y1="0" y2="1">
<stop offset="0%" stop-color="#2463eb" stop-opacity="0.5"></stop>
<stop offset="100%" stop-color="#2463eb" stop-opacity="0"></stop>
</linearGradient>
</defs>
<path d="$path_area" fill="url(#gradientLine)"></path>
<path d="$path_linea" fill="none" stroke="#2463eb" stroke-linecap="round" stroke-width="2.5" vector-effect="non-scaling-stroke"></path>
SVG;
}

/**
 * Genera gráfico de dona para estado de boletos
 */
function generarGraficoDonaBoletos($estado_boletos) {
    $total = $estado_boletos['total'];
    if ($total == 0) {
        return '<div class="text-text-secondary text-center py-8">No hay boletos disponibles</div>';
    }
    
    $vendidos = $estado_boletos['vendidos'] ?? 0;
    $reservados = $estado_boletos['reservados'] ?? 0;
    $disponibles = $estado_boletos['disponibles'] ?? 0;
    
    $porc_vendidos = ($vendidos / $total) * 100;
    $porc_reservados = ($reservados / $total) * 100;
    $porc_disponibles = ($disponibles / $total) * 100;
    
    $offset_reservados = $porc_vendidos;
    $offset_disponibles = $porc_vendidos + $porc_reservados;
    $fin_reservados = $offset_reservados + $porc_reservados;
    
    $total_display = $total >= 1000 ? number_format($total / 1000, 1) . 'k' : number_format($total);
    
    return '<div class="flex-1 flex flex-col items-center justify-center relative">
<div class="size-48 rounded-full relative" style="background: conic-gradient(#2463eb 0% ' . $porc_vendidos . '%, #fa6538 ' . $offset_reservados . '% ' . $fin_reservados . '%, #282d39 ' . $offset_disponibles . '% 100%);">
<div class="absolute inset-4 bg-[#1c1f27] rounded-full flex flex-col items-center justify-center">
<span class="text-text-secondary text-xs font-medium">Total</span>
<span class="text-white text-2xl font-bold">' . $total_display . '</span>
</div>
</div>
</div>
<div class="mt-6 space-y-3">
<div class="flex items-center justify-between">
<div class="flex items-center gap-2">
<span class="size-3 rounded-full bg-primary"></span>
<span class="text-text-secondary text-sm">Vendidos (' . round($porc_vendidos) . '%)</span>
</div>
<span class="text-white text-sm font-bold">' . number_format($vendidos) . '</span>
</div>
<div class="flex items-center justify-between">
<div class="flex items-center gap-2">
<span class="size-3 rounded-full bg-danger"></span>
<span class="text-text-secondary text-sm">Reservados (' . round($porc_reservados) . '%)</span>
</div>
<span class="text-white text-sm font-bold">' . number_format($reservados) . '</span>
</div>
<div class="flex items-center justify-between">
<div class="flex items-center gap-2">
<span class="size-3 rounded-full bg-[#282d39]"></span>
<span class="text-text-secondary text-sm">Disponibles (' . round($porc_disponibles) . '%)</span>
</div>
<span class="text-white text-sm font-bold">' . number_format($disponibles) . '</span>
</div>
</div>';
}

// Obtener datos
$kpis = obtenerKPIsInformes($conn, $fecha_inicio, $fecha_fin, $id_sorteo_filtro);
$tendencia_ingresos = obtenerTendenciaIngresos($conn, $id_sorteo_filtro);
$estado_boletos = obtenerEstadoBoletos($conn);
$ventas_sorteos = obtenerVentasPorSorteo($conn, $fecha_inicio, $fecha_fin);
$campanas = obtenerEfectividadCampanas($conn, $estado_campana);
$lista_sorteos = obtenerListaSorteos($conn);
?>


<html class="dark" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Informes y Estadísticas Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#2463eb",
                        "background-light": "#f6f6f8",
                        "background-dark": "#111621",
                        "card-dark": "#1e2433",
                        "border-dark": "#2a3241",
                        "text-secondary": "#9da6b9",
                        "success": "#0bda62",
                        "danger": "#fa6538",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"],
                        "body": ["Inter", "sans-serif"],
                    },
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" },
                },
            },
        }
    </script>
<style>
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Custom scrollbar for dark theme */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #111621; 
        }
        ::-webkit-scrollbar-thumb {
            background: #2a3241; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #3b4657; 
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white antialiased overflow-hidden">
<div class="flex h-screen w-full">
<!-- Sidebar -->
<aside class="w-64 flex-shrink-0 flex flex-col border-r border-gray-200 dark:border-border-dark bg-white dark:bg-[#151a25]">
<div class="h-16 flex items-center px-6 border-b border-gray-200 dark:border-border-dark">
<div class="flex items-center gap-2 text-primary">
<span class="material-symbols-outlined text-3xl">confirmation_number</span>
<span class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">Sorteos<span class="text-primary">Admin</span></span>
</div>
</div>
<div class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
<p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-2">Principal</p>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="DashboardAdmnistrador.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">dashboard</span>
                    Dashboard
                </a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="CrudGestionSorteo.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">confirmation_number</span>
                    Gestión de Sorteos
                </a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="ValidacionPagosAdministrador.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">payments</span>
                    Validación de Pagos
                    <span class="ml-auto bg-yellow-500/20 text-yellow-500 text-xs font-bold px-2 py-0.5 rounded-full">3</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="GeneradorGanadoresAdminstradores.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">emoji_events</span>
                    Generación de Ganadores
                </a>
<p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-6">Administración</p>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="GestionUsuariosAdministrador.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">group</span>
                    Usuarios
                </a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="AuditoriaAccionesAdmin.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">settings</span>
                    Auditoría
                </a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary/10 text-primary font-medium" href="InformesEstadisticasAdmin.php">
<span class="material-symbols-outlined">analytics</span>
                    Informes
                </a>
</div>
<div class="p-4 border-t border-gray-200 dark:border-border-dark">
<div class="flex items-center gap-3 mb-3">
<div class="w-10 h-10 rounded-full bg-cover bg-center" data-alt="User profile picture" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAfIzDdUJZk0e1bBHKOe7BG0HPanJ3nx8d9vtsJZZMiXM6ZJw9-oPch2DQWyWWrowTikKHJBUkhOyI6hUEiy_TgTGdRmm-4uDyO3KjasL500lcWogtry5HOXaJxBgDxpuT_8QBEVTnbuI4727c7c5qtPNid2CyQr0SnpyEcv2R9UEoiXiOVUH_g0RdYwYfb9u5EU5DkqEZl2oL9UW9s45D-zD3htPmEHk69TrCVPL50vnE6cDfTlcz9AJEZo7Hb8gpAhxwAxDP4SCs');"></div>
<div class="flex flex-col">
<span class="text-sm font-medium text-slate-900 dark:text-white">Admin User</span>
<span class="text-xs text-gray-500">admin@sorteos.web</span>
</div>
</div>
<button id="logout-btn-admin" onclick="handleLogoutAdmin()" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors text-sm font-medium">
<span class="material-symbols-outlined text-[20px]">logout</span>
Cerrar Sesión
</button>
</div>
</aside>
<!-- Main Content -->
<main class="flex-1 flex flex-col h-full overflow-hidden bg-background-light dark:bg-background-dark relative">
<!-- Header -->
<header class="h-16 flex items-center justify-between px-6 border-b border-gray-200 dark:border-border-dark bg-white dark:bg-[#151a25]/80 backdrop-blur-md sticky top-0 z-20">
<div class="flex items-center gap-4">
<!-- Mobile menu trigger (hidden on desktop) -->
<button class="lg:hidden text-gray-500">
<span class="material-symbols-outlined">menu</span>
</button>
<h1 class="text-xl font-bold text-slate-900 dark:text-white hidden sm:block">Informes y Estadísticas</h1>
</div>
<div class="flex items-center gap-4">
<div class="relative hidden md:block w-64">
<span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
<span class="material-symbols-outlined text-[20px]">search</span>
</span>
<input class="w-full bg-gray-100 dark:bg-[#1e2433] border-none rounded-lg py-2 pl-10 pr-4 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary placeholder-gray-500" placeholder="Buscar sorteo, usuario..." type="text"/>
</div>
<button class="relative p-2 text-gray-500 hover:text-primary transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-white/5">
<span class="material-symbols-outlined">notifications</span>
<span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
</button>
</div>
</header>
<!-- Scrollable Content -->
<div class="flex-1 overflow-y-auto p-6 space-y-6">
<div class="max-w-[1200px] mx-auto w-full">
<!-- Breadcrumbs -->
<div class="flex flex-wrap items-center gap-2 px-4 py-2 mb-4">
<button onclick="navegarAtras()" class="text-[#9da6b9] hover:text-white transition-colors text-sm font-medium leading-normal flex items-center gap-1" title="Volver">
<span class="material-symbols-outlined !text-lg">arrow_back</span>
                            Atrás
                        </button>
<span class="text-[#9da6b9] text-sm font-medium leading-normal">|</span>
<a class="text-[#9da6b9] hover:text-white transition-colors text-sm font-medium leading-normal flex items-center gap-1" href="DashboardAdmnistrador.php">
<span class="material-symbols-outlined !text-lg">dashboard</span>
                            Dashboard
                        </a>
<span class="text-[#9da6b9] text-sm font-medium leading-normal">/</span>
<span class="text-white text-sm font-medium leading-normal">Informes y Estadísticas</span>
</div>
<!-- Scrollable Content -->
<main class="flex-1 overflow-y-auto p-6 scrollbar-thin scrollbar-thumb-[#3b4354] scrollbar-track-transparent">
<div class="max-w-[1200px] mx-auto flex flex-col gap-6">
<!-- Filters -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
<div class="relative group">
<label class="absolute -top-2.5 left-3 bg-background-dark px-1 text-xs font-medium text-text-secondary group-focus-within:text-primary transition-colors">Rango de Fecha</label>
<div class="flex items-center h-12 w-full rounded-lg border border-[#3b4354] bg-[#1c1f27] px-3 focus-within:border-primary focus-within:ring-1 focus-within:ring-primary transition-all">
<span class="material-symbols-outlined text-text-secondary mr-2">date_range</span>
<input id="dateRangeInput" class="bg-transparent border-none text-white text-sm w-full focus:ring-0 placeholder-text-secondary" placeholder="Seleccionar fechas" type="date" value="<?php echo $fecha_inicio; ?>" onchange="actualizarFiltros()"/>
<span class="text-text-secondary mx-2">-</span>
<input id="dateRangeInputFin" class="bg-transparent border-none text-white text-sm w-full focus:ring-0 placeholder-text-secondary" placeholder="Seleccionar fechas" type="date" value="<?php echo $fecha_fin; ?>" onchange="actualizarFiltros()"/>
</div>
</div>
<div class="relative group">
<label class="absolute -top-2.5 left-3 bg-background-dark px-1 text-xs font-medium text-text-secondary group-focus-within:text-primary transition-colors">Filtrar por Sorteo</label>
<div class="flex items-center h-12 w-full rounded-lg border border-[#3b4354] bg-[#1c1f27] px-3 focus-within:border-primary focus-within:ring-1 focus-within:ring-primary transition-all">
<span class="material-symbols-outlined text-text-secondary mr-2">confirmation_number</span>
<select id="sorteoFilter" class="bg-transparent border-none text-white text-sm w-full focus:ring-0 [&amp;&gt;option]:text-black" onchange="actualizarFiltros()">
<option value="todos">Todos los Sorteos</option>
<?php foreach ($lista_sorteos as $sorteo): ?>
<option value="<?php echo $sorteo['id']; ?>" <?php echo ($id_sorteo_filtro == $sorteo['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($sorteo['titulo']); ?></option>
<?php endforeach; ?>
</select>
</div>
</div>
<div class="relative group">
<label class="absolute -top-2.5 left-3 bg-background-dark px-1 text-xs font-medium text-text-secondary group-focus-within:text-primary transition-colors">Estado de Campaña</label>
<div class="flex items-center h-12 w-full rounded-lg border border-[#3b4354] bg-[#1c1f27] px-3 focus-within:border-primary focus-within:ring-1 focus-within:ring-primary transition-all">
<span class="material-symbols-outlined text-text-secondary mr-2">filter_alt</span>
<select id="campanaFilter" class="bg-transparent border-none text-white text-sm w-full focus:ring-0 [&amp;&gt;option]:text-black" onchange="actualizarFiltros()">
<option value="todas" <?php echo $estado_campana == 'todas' ? 'selected' : ''; ?>>Todas las campañas</option>
<option value="Activa" <?php echo $estado_campana == 'Activa' ? 'selected' : ''; ?>>Activas</option>
<option value="Finalizada" <?php echo $estado_campana == 'Finalizada' ? 'selected' : ''; ?>>Finalizadas</option>
</select>
</div>
</div>
</div>
<!-- KPI Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
<!-- Revenue -->
<div class="bg-[#1c1f27] rounded-xl p-5 border border-[#282d39] hover:border-[#3b4354] transition-colors relative overflow-hidden">
<div class="absolute top-0 right-0 p-4 opacity-5">
<span class="material-symbols-outlined text-6xl text-white">payments</span>
</div>
<p class="text-text-secondary text-sm font-medium mb-1">Ingresos Totales</p>
<h3 class="text-white text-2xl font-bold tracking-tight mb-2">$<?php echo number_format($kpis['ingresos_totales'], 2); ?></h3>
<div class="flex items-center gap-1.5">
<div class="flex items-center justify-center <?php echo $kpis['tendencia_ingresos'] >= 0 ? 'bg-success/10' : 'bg-danger/10'; ?> rounded px-1.5 py-0.5">
<span class="material-symbols-outlined <?php echo $kpis['tendencia_ingresos'] >= 0 ? 'text-success' : 'text-danger'; ?> text-xs mr-0.5"><?php echo $kpis['tendencia_ingresos'] >= 0 ? 'trending_up' : 'trending_down'; ?></span>
<span class="<?php echo $kpis['tendencia_ingresos'] >= 0 ? 'text-success' : 'text-danger'; ?> text-xs font-bold"><?php echo ($kpis['tendencia_ingresos'] >= 0 ? '+' : '') . $kpis['tendencia_ingresos']; ?>%</span>
</div>
<span class="text-text-secondary text-xs">vs período anterior</span>
</div>
</div>
<!-- Tickets -->
<div class="bg-[#1c1f27] rounded-xl p-5 border border-[#282d39] hover:border-[#3b4354] transition-colors relative overflow-hidden">
<div class="absolute top-0 right-0 p-4 opacity-5">
<span class="material-symbols-outlined text-6xl text-white">confirmation_number</span>
</div>
<p class="text-text-secondary text-sm font-medium mb-1">Boletos Vendidos</p>
<h3 class="text-white text-2xl font-bold tracking-tight mb-2"><?php echo number_format($kpis['boletos_vendidos']); ?></h3>
<div class="flex items-center gap-1.5">
<div class="flex items-center justify-center <?php echo $kpis['tendencia_boletos'] >= 0 ? 'bg-success/10' : 'bg-danger/10'; ?> rounded px-1.5 py-0.5">
<span class="material-symbols-outlined <?php echo $kpis['tendencia_boletos'] >= 0 ? 'text-success' : 'text-danger'; ?> text-xs mr-0.5"><?php echo $kpis['tendencia_boletos'] >= 0 ? 'trending_up' : 'trending_down'; ?></span>
<span class="<?php echo $kpis['tendencia_boletos'] >= 0 ? 'text-success' : 'text-danger'; ?> text-xs font-bold"><?php echo ($kpis['tendencia_boletos'] >= 0 ? '+' : '') . $kpis['tendencia_boletos']; ?>%</span>
</div>
<span class="text-text-secondary text-xs">vs período anterior</span>
</div>
</div>
<!-- Active Users -->
<div class="bg-[#1c1f27] rounded-xl p-5 border border-[#282d39] hover:border-[#3b4354] transition-colors relative overflow-hidden">
<div class="absolute top-0 right-0 p-4 opacity-5">
<span class="material-symbols-outlined text-6xl text-white">group</span>
</div>
<p class="text-text-secondary text-sm font-medium mb-1">Usuarios Activos</p>
<h3 class="text-white text-2xl font-bold tracking-tight mb-2"><?php echo number_format($kpis['usuarios_activos']); ?></h3>
<div class="flex items-center gap-1.5">
<div class="flex items-center justify-center <?php echo $kpis['tendencia_usuarios'] >= 0 ? 'bg-success/10' : 'bg-danger/10'; ?> rounded px-1.5 py-0.5">
<span class="material-symbols-outlined <?php echo $kpis['tendencia_usuarios'] >= 0 ? 'text-success' : 'text-danger'; ?> text-xs mr-0.5"><?php echo $kpis['tendencia_usuarios'] >= 0 ? 'trending_up' : 'trending_down'; ?></span>
<span class="<?php echo $kpis['tendencia_usuarios'] >= 0 ? 'text-success' : 'text-danger'; ?> text-xs font-bold"><?php echo ($kpis['tendencia_usuarios'] >= 0 ? '+' : '') . $kpis['tendencia_usuarios']; ?>%</span>
</div>
<span class="text-text-secondary text-xs">vs período anterior</span>
</div>
</div>
<!-- Conversion -->
<div class="bg-[#1c1f27] rounded-xl p-5 border border-[#282d39] hover:border-[#3b4354] transition-colors relative overflow-hidden">
<div class="absolute top-0 right-0 p-4 opacity-5">
<span class="material-symbols-outlined text-6xl text-white">percent</span>
</div>
<p class="text-text-secondary text-sm font-medium mb-1">Tasa de Conversión</p>
<h3 class="text-white text-2xl font-bold tracking-tight mb-2"><?php echo $kpis['tasa_conversion']; ?>%</h3>
<div class="flex items-center gap-1.5">
<div class="flex items-center justify-center <?php echo $kpis['tendencia_conversion'] >= 0 ? 'bg-success/10' : 'bg-danger/10'; ?> rounded px-1.5 py-0.5">
<span class="material-symbols-outlined <?php echo $kpis['tendencia_conversion'] >= 0 ? 'text-success' : 'text-danger'; ?> text-xs mr-0.5"><?php echo $kpis['tendencia_conversion'] >= 0 ? 'trending_up' : 'trending_down'; ?></span>
<span class="<?php echo $kpis['tendencia_conversion'] >= 0 ? 'text-success' : 'text-danger'; ?> text-xs font-bold"><?php echo ($kpis['tendencia_conversion'] >= 0 ? '+' : '') . $kpis['tendencia_conversion']; ?>%</span>
</div>
<span class="text-text-secondary text-xs">vs período anterior</span>
</div>
</div>
</div>
<!-- Charts Row 1 -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
<!-- Main Line Chart -->
<div class="lg:col-span-2 bg-[#1c1f27] rounded-xl border border-[#282d39] p-6 flex flex-col">
<div class="flex justify-between items-start mb-6">
<div>
<h3 class="text-white text-lg font-bold">Tendencia de Ingresos</h3>
<p class="text-text-secondary text-sm">Rendimiento en los últimos 6 meses</p>
</div>
<div class="flex gap-2">
<span class="size-3 rounded-full bg-primary mt-1.5"></span>
<span class="text-text-secondary text-sm">Ventas Netas</span>
</div>
</div>
<?php
// Generar gráfico de tendencia de ingresos
$meses_grafico = [];
$valores_grafico = [];
$fecha_actual_grafico = new DateTime();
$max_valor_grafico = 0;

for ($i = 5; $i >= 0; $i--) {
    $fecha = clone $fecha_actual_grafico;
    $fecha->modify("-$i months");
    $mes_key = $fecha->format('Y-m');
    $mes_nombre = $fecha->format('M');
    $meses_grafico[] = $mes_nombre;
    $valor = isset($tendencia_ingresos[$mes_key]) ? $tendencia_ingresos[$mes_key]['total'] : 0;
    $valores_grafico[] = $valor;
    if ($valor > $max_valor_grafico) $max_valor_grafico = $valor;
}

if ($max_valor_grafico == 0) $max_valor_grafico = 1;

// Calcular puntos
$puntos_grafico = [];
for ($i = 0; $i < count($valores_grafico); $i++) {
    $x = ($i / max((count($valores_grafico) - 1), 1)) * 100;
    $y = 100 - (($valores_grafico[$i] / $max_valor_grafico) * 80);
    $puntos_grafico[] = ['x' => $x, 'y' => $y];
}

// Generar paths
$path_area_grafico = "M{$puntos_grafico[0]['x']},100";
$path_linea_grafico = "M{$puntos_grafico[0]['x']},{$puntos_grafico[0]['y']}";

for ($i = 1; $i < count($puntos_grafico); $i++) {
    $x_medio = ($puntos_grafico[$i-1]['x'] + $puntos_grafico[$i]['x']) / 2;
    $path_area_grafico .= " C{$x_medio},{$puntos_grafico[$i-1]['y']} {$x_medio},{$puntos_grafico[$i]['y']} {$puntos_grafico[$i]['x']},{$puntos_grafico[$i]['y']}";
    $path_linea_grafico .= " C{$x_medio},{$puntos_grafico[$i-1]['y']} {$x_medio},{$puntos_grafico[$i]['y']} {$puntos_grafico[$i]['x']},{$puntos_grafico[$i]['y']}";
}
$path_area_grafico .= " L{$puntos_grafico[count($puntos_grafico)-1]['x']},100 Z";
?>
<div class="relative h-64 w-full mt-auto">
<div class="absolute inset-0 flex flex-col justify-between text-text-secondary text-xs font-medium">
<div class="border-b border-[#3b4354]/30 w-full h-0 flex items-center"><span class="-mt-6"><?php echo number_format($max_valor_grafico / 1000, 0); ?>k</span></div>
<div class="border-b border-[#3b4354]/30 w-full h-0 flex items-center"><span class="-mt-6"><?php echo number_format($max_valor_grafico * 0.8 / 1000, 0); ?>k</span></div>
<div class="border-b border-[#3b4354]/30 w-full h-0 flex items-center"><span class="-mt-6"><?php echo number_format($max_valor_grafico * 0.6 / 1000, 0); ?>k</span></div>
<div class="border-b border-[#3b4354]/30 w-full h-0 flex items-center"><span class="-mt-6"><?php echo number_format($max_valor_grafico * 0.4 / 1000, 0); ?>k</span></div>
<div class="border-b border-[#3b4354]/30 w-full h-0 flex items-center"><span class="-mt-6"><?php echo number_format($max_valor_grafico * 0.2 / 1000, 0); ?>k</span></div>
</div>
<svg class="absolute inset-0 h-full w-full overflow-visible" preserveAspectRatio="none" viewBox="0 0 100 100">
<defs>
<linearGradient id="gradientLine" x1="0" x2="0" y1="0" y2="1">
<stop offset="0%" stop-color="#2463eb" stop-opacity="0.5"></stop>
<stop offset="100%" stop-color="#2463eb" stop-opacity="0"></stop>
</linearGradient>
</defs>
<path d="<?php echo $path_area_grafico; ?>" fill="url(#gradientLine)"></path>
<path d="<?php echo $path_linea_grafico; ?>" fill="none" stroke="#2463eb" stroke-linecap="round" stroke-width="2.5" vector-effect="non-scaling-stroke"></path>
<?php foreach ($puntos_grafico as $punto): ?>
<circle cx="<?php echo $punto['x']; ?>" cy="<?php echo $punto['y']; ?>" fill="#2463eb" r="1.5" stroke="white" stroke-width="0.5" vector-effect="non-scaling-stroke"></circle>
<?php endforeach; ?>
</svg>
</div>
<div class="flex justify-between w-full mt-2 text-text-secondary text-xs font-medium px-1">
<?php foreach ($meses_grafico as $mes): ?>
<span><?php echo $mes; ?></span>
<?php endforeach; ?>
</div>
</div>
<!-- Secondary Chart: Donut (Ticket Status) -->
<div class="bg-[#1c1f27] rounded-xl border border-[#282d39] p-6 flex flex-col">
<h3 class="text-white text-lg font-bold mb-1">Estado de Boletos</h3>
<p class="text-text-secondary text-sm mb-6">Distribución actual del inventario</p>
<?php echo generarGraficoDonaBoletos($estado_boletos); ?>
</div>
</div>
<!-- Charts Row 2: Bar Chart & Table -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pb-6">
<!-- Bar Chart: Sales by Raffle -->
<div class="bg-[#1c1f27] rounded-xl border border-[#282d39] p-6">
<div class="flex justify-between items-center mb-6">
<h3 class="text-white text-lg font-bold">Ventas por Sorteo</h3>
<button onclick="viewSalesDetails()" class="text-primary text-sm font-medium hover:text-white transition-colors">Ver Detalles</button>
</div>
<div class="h-64 flex items-end justify-between gap-4">
<?php
if (empty($ventas_sorteos)) {
    echo '<div class="w-full text-center text-text-secondary py-8">No hay ventas de sorteos en el período seleccionado</div>';
} else {
    // Calcular máximo para escalar las barras
    $max_ingresos = 0;
    foreach ($ventas_sorteos as $venta) {
        if ($venta['ingresos'] > $max_ingresos) {
            $max_ingresos = $venta['ingresos'];
        }
    }
    if ($max_ingresos == 0) $max_ingresos = 1;
    
    foreach ($ventas_sorteos as $venta) {
        $porcentaje = ($venta['ingresos'] / $max_ingresos) * 100;
        $titulo_corto = strlen($venta['titulo']) > 10 ? substr($venta['titulo'], 0, 10) . '...' : $venta['titulo'];
        echo '<div class="flex flex-col items-center flex-1 gap-2 group">
<div class="w-full bg-[#282d39] rounded-t-lg relative h-40 group-hover:bg-[#3b4354] transition-colors">
<div class="absolute bottom-0 w-full bg-primary rounded-t-lg transition-all duration-500" style="height: ' . $porcentaje . '%;"></div>
<div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-white text-slate-900 text-xs font-bold px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity">$' . number_format($venta['ingresos'], 0) . '</div>
</div>
<span class="text-text-secondary text-xs font-medium text-center truncate w-full" title="' . htmlspecialchars($venta['titulo']) . '">' . htmlspecialchars($titulo_corto) . '</span>
</div>';
    }
}
?>
</div>
</div>
<!-- Campaign Table -->
<div class="bg-[#1c1f27] rounded-xl border border-[#282d39] overflow-hidden flex flex-col">
<div class="p-6 border-b border-[#282d39] flex justify-between items-center">
<h3 class="text-white text-lg font-bold">Efectividad de Campañas</h3>
<button class="text-text-secondary hover:text-white transition-colors">
<span class="material-symbols-outlined">more_horiz</span>
</button>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-[#1c1f27]">
<th class="py-3 px-6 text-xs font-medium text-text-secondary uppercase tracking-wider">Campaña</th>
<th class="py-3 px-6 text-xs font-medium text-text-secondary uppercase tracking-wider">Estado</th>
<th class="py-3 px-6 text-xs font-medium text-text-secondary uppercase tracking-wider text-right">Inversión</th>
<th class="py-3 px-6 text-xs font-medium text-text-secondary uppercase tracking-wider text-right">Retorno (ROI)</th>
</tr>
</thead>
<tbody class="divide-y divide-[#282d39]">
<?php
if (empty($campanas)) {
    echo '<tr><td colspan="4" class="py-8 text-center text-text-secondary">No hay campañas de marketing registradas</td></tr>';
} else {
    $iconos = ['thumb_up', 'mail', 'camera_alt', 'campaign', 'trending_up'];
    foreach ($campanas as $index => $campana) {
        $icono = $iconos[$index % count($iconos)];
        $estado_class = $campana['estado'] == 'Activa' ? 'bg-success/10 text-success border-success/20' : 
                       ($campana['estado'] == 'Pausada' ? 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20' : 
                       'bg-[#282d39] text-text-secondary border-[#3b4354]');
        $roi_class = $campana['roi'] >= 0 ? 'text-success' : 'text-danger';
        $roi_signo = $campana['roi'] >= 0 ? '+' : '';
        echo '<tr class="hover:bg-[#282d39]/50 transition-colors">
<td class="py-4 px-6">
<div class="flex items-center gap-3">
<div class="bg-[#282d39] p-1.5 rounded text-white">
<span class="material-symbols-outlined text-sm">' . $icono . '</span>
</div>
<span class="text-white text-sm font-medium">' . htmlspecialchars($campana['nombre']) . '</span>
</div>
</td>
<td class="py-4 px-6">
<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ' . $estado_class . ' border">' . htmlspecialchars($campana['estado']) . '</span>
</td>
<td class="py-4 px-6 text-text-secondary text-sm text-right">$' . number_format($campana['inversion'], 2) . '</td>
<td class="py-4 px-6 ' . $roi_class . ' text-sm font-bold text-right">' . $roi_signo . $campana['roi'] . '%</td>
</tr>';
    }
}
?>
</tbody>
</table>
</div>
</div>
</div>
</div>
</main>
</div>
</div>
<script>
// ========== NAVEGACIÓN CON HISTORIAL ==========

/**
 * Navega hacia atrás usando el historial del navegador
 */
function navegarAtras() {
    try {
        if (window.history.length > 1) {
            window.history.back();
        } else {
            window.location.href = 'DashboardAdmnistrador.php';
        }
    } catch (error) {
        console.error('Error al navegar atrás:', error);
        window.location.href = 'DashboardAdmnistrador.php';
    }
}

// ========== ACTUALIZACIÓN DE FILTROS ==========

/**
 * Actualiza los filtros y recarga la página con los nuevos parámetros
 */
function actualizarFiltros() {
    const fechaInicio = document.getElementById('dateRangeInput').value;
    const fechaFin = document.getElementById('dateRangeInputFin').value;
    const sorteo = document.getElementById('sorteoFilter').value;
    const campana = document.getElementById('campanaFilter').value;
    
    const params = new URLSearchParams();
    if (fechaInicio) params.append('fecha_inicio', fechaInicio);
    if (fechaFin) params.append('fecha_fin', fechaFin);
    if (sorteo && sorteo !== 'todos') params.append('sorteo', sorteo);
    if (campana && campana !== 'todas') params.append('estado_campana', campana);
    
    window.location.href = 'InformesEstadisticasAdmin.php?' + params.toString();
}

/**
 * Función para ver detalles de ventas (placeholder)
 */
function viewSalesDetails() {
    // Redirigir a página de gestión de sorteos o mostrar modal
    window.location.href = 'CrudGestionSorteo.php';
}
// Función para manejar el logout del administrador
function handleLogoutAdmin() {
    // Usar customConfirm para mantener consistencia con el resto de la aplicación
    if (typeof customConfirm === 'function') {
        customConfirm('¿Estás seguro de que deseas cerrar sesión?', 'Cerrar Sesión', 'warning').then(confirmed => {
            if (confirmed) {
                // Redirigir al logout.php que destruye la sesión del servidor
                window.location.href = 'logout.php';
            }
        });
    } else {
        // Si customConfirm no está disponible, esperar a que se cargue
        setTimeout(() => {
            if (typeof customConfirm === 'function') {
                handleLogoutAdmin();
            } else {
                // Fallback si customConfirm no se carga
                if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                    window.location.href = 'logout.php';
                }
            }
        }, 200);
    }
}
</script>
<!-- Cargar custom-alerts.js para usar alertas personalizadas -->
<script src="custom-alerts.js"></script>
</body></html>

//pagina para ver los informes y estadisticas como administrador despues de iniciar sesion
//Se ve esta pagina para ver los informes y estadisticas de la plataforma y los sorteos.