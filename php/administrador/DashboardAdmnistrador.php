<!DOCTYPE html>
<?php
// Conexión a la base de datos
require_once 'config.php';
$conn = getDBConnection();

/**
 * Obtiene los KPIs principales del dashboard
 */
function obtenerKPIs($conn) {
    $kpis = [
        'ingresos_totales' => 0,
        'boletos_vendidos' => 0,
        'sorteos_activos' => 0,
        'pagos_pendientes' => 0,
        'tendencia_ingresos' => 0,
        'tendencia_boletos' => 0
    ];
    
    // Validar conexión
    if (!$conn || $conn->connect_error) {
        error_log("Error: Conexión a base de datos no válida en obtenerKPIs()");
        return $kpis;
    }
    
    try {
        // Ingresos totales (pagos completados)
        $sql = "SELECT COALESCE(SUM(monto_total), 0) as total FROM transacciones WHERE estado_pago = 'Completado'";
        $result = $conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            $kpis['ingresos_totales'] = number_format($row['total'], 2);
        } else {
            error_log("Error en consulta de ingresos totales: " . $conn->error);
        }
        
        // Boletos vendidos
        $sql = "SELECT COUNT(*) as total FROM boletos WHERE estado = 'Vendido'";
        $result = $conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            $kpis['boletos_vendidos'] = $row['total'];
        } else {
            error_log("Error en consulta de boletos vendidos: " . $conn->error);
        }
        
        // Sorteos activos
        $sql = "SELECT COUNT(*) as total FROM sorteos WHERE estado = 'Activo'";
        $result = $conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            $kpis['sorteos_activos'] = $row['total'];
        } else {
            error_log("Error en consulta de sorteos activos: " . $conn->error);
        }
        
        // Pagos pendientes
        $sql = "SELECT COUNT(*) as total FROM transacciones WHERE estado_pago = 'Pendiente'";
        $result = $conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            $kpis['pagos_pendientes'] = $row['total'];
        } else {
            error_log("Error en consulta de pagos pendientes: " . $conn->error);
        }
        
        // Calcular tendencia de ingresos (comparar con mes anterior)
        $sql = "SELECT COALESCE(SUM(monto_total), 0) as total_mes_actual 
                FROM transacciones 
                WHERE estado_pago = 'Completado' 
                AND MONTH(fecha_creacion) = MONTH(CURRENT_DATE())
                AND YEAR(fecha_creacion) = YEAR(CURRENT_DATE())";
        $result = $conn->query($sql);
        $total_mes_actual = 0;
        if ($result && $row = $result->fetch_assoc()) {
            $total_mes_actual = $row['total_mes_actual'];
        }
        
        $sql = "SELECT COALESCE(SUM(monto_total), 0) as total_mes_anterior 
                FROM transacciones 
                WHERE estado_pago = 'Completado' 
                AND MONTH(fecha_creacion) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
                AND YEAR(fecha_creacion) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))";
        $result = $conn->query($sql);
        $total_mes_anterior = 1; // Evitar división por cero
        if ($result && $row = $result->fetch_assoc()) {
            $total_mes_anterior = $row['total_mes_anterior'] > 0 ? $row['total_mes_anterior'] : 1;
        }
        
        if ($total_mes_anterior > 0) {
            $kpis['tendencia_ingresos'] = round((($total_mes_actual - $total_mes_anterior) / $total_mes_anterior) * 100, 1);
        }
        
        // Calcular tendencia de boletos (comparar con mes anterior)
        // Usar fecha_creacion de transacciones ya que boletos no tiene fecha_venta
        $sql = "SELECT COUNT(DISTINCT b.id_boleto) as total_mes_actual 
                FROM boletos b
                INNER JOIN detalle_transaccion_boletos dtb ON b.id_boleto = dtb.id_boleto
                INNER JOIN transacciones t ON dtb.id_transaccion = t.id_transaccion
                WHERE b.estado = 'Vendido'
                AND t.estado_pago = 'Completado'
                AND MONTH(t.fecha_creacion) = MONTH(CURRENT_DATE())
                AND YEAR(t.fecha_creacion) = YEAR(CURRENT_DATE())";
        $result = $conn->query($sql);
        $boletos_mes_actual = 0;
        if ($result && $row = $result->fetch_assoc()) {
            $boletos_mes_actual = $row['total_mes_actual'];
        }
        
        $sql = "SELECT COUNT(DISTINCT b.id_boleto) as total_mes_anterior 
                FROM boletos b
                INNER JOIN detalle_transaccion_boletos dtb ON b.id_boleto = dtb.id_boleto
                INNER JOIN transacciones t ON dtb.id_transaccion = t.id_transaccion
                WHERE b.estado = 'Vendido'
                AND t.estado_pago = 'Completado'
                AND MONTH(t.fecha_creacion) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
                AND YEAR(t.fecha_creacion) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))";
        $result = $conn->query($sql);
        $boletos_mes_anterior = 1; // Evitar división por cero
        if ($result && $row = $result->fetch_assoc()) {
            $boletos_mes_anterior = $row['total_mes_anterior'] > 0 ? $row['total_mes_anterior'] : 1;
        }
        
        if ($boletos_mes_anterior > 0) {
            $kpis['tendencia_boletos'] = round((($boletos_mes_actual - $boletos_mes_anterior) / $boletos_mes_anterior) * 100, 1);
        }
        
    } catch (Exception $e) {
        error_log("Error obteniendo KPIs: " . $e->getMessage());
    }
    
    return $kpis;
}

/**
 * Obtiene sorteos próximos a finalizar
 */
function obtenerSorteosPorFinalizar($conn, $limit = 3) {
    $sorteos = [];
    
    // Validar conexión
    if (!$conn || $conn->connect_error) {
        error_log("Error: Conexión a base de datos no válida en obtenerSorteosPorFinalizar()");
        return $sorteos;
    }
    
    try {
        $sql = "SELECT id_sorteo, titulo, imagen_url, fecha_fin 
                FROM sorteos 
                WHERE estado = 'Activo' AND fecha_fin > NOW()
                ORDER BY fecha_fin ASC 
                LIMIT ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando consulta de sorteos por finalizar: " . $conn->error);
            return $sorteos;
        }
        
        $stmt->bind_param("i", $limit);
        
        if (!$stmt->execute()) {
            error_log("Error ejecutando consulta de sorteos por finalizar: " . $stmt->error);
            $stmt->close();
            return $sorteos;
        }
        
        $result = $stmt->get_result();
        
        if (!$result) {
            error_log("Error obteniendo resultados de sorteos por finalizar: " . $conn->error);
            $stmt->close();
            return $sorteos;
        }
        
        while ($row = $result->fetch_assoc()) {
            // Calcular tiempo restante
            $fecha_fin = new DateTime($row['fecha_fin']);
            $ahora = new DateTime();
            $diferencia = $ahora->diff($fecha_fin);
            
            $tiempo_restante = '';
            if ($diferencia->days > 0) {
                $tiempo_restante = $diferencia->days . 'd ' . $diferencia->h . 'h';
            } else if ($diferencia->h > 0) {
                $tiempo_restante = $diferencia->h . 'h ' . $diferencia->i . 'm';
            } else {
                $tiempo_restante = $diferencia->i . 'm';
            }
            
            $sorteos[] = [
                'id' => $row['id_sorteo'],
                'titulo' => $row['titulo'],
                'imagen_url' => $row['imagen_url'] ?: 'https://via.placeholder.com/150',
                'tiempo_restante' => $tiempo_restante,
                'urgente' => $diferencia->days == 0 && $diferencia->h < 6
            ];
        }
        
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error obteniendo sorteos por finalizar: " . $e->getMessage());
    }
    
    return $sorteos;
}

/**
 * Obtiene pagos pendientes de validación
 */
function obtenerPagosPendientes($conn, $limit = 4) {
    $pagos = [];
    
    // Validar conexión
    if (!$conn || $conn->connect_error) {
        error_log("Error: Conexión a base de datos no válida en obtenerPagosPendientes()");
        return $pagos;
    }
    
    try {
        // Usar GROUP BY en lugar de DISTINCT para obtener el primer sorteo de cada transacción
        $sql = "SELECT 
                    t.id_transaccion,
                    t.referencia_pago,
                    t.monto_total,
                    t.estado_pago,
                    t.fecha_creacion,
                    u.primer_nombre,
                    u.apellido_paterno,
                    u.email,
                    GROUP_CONCAT(DISTINCT s.titulo SEPARATOR ', ') as sorteos_titulos
                FROM transacciones t
                JOIN usuarios u ON t.id_usuario = u.id_usuario
                LEFT JOIN detalle_transaccion_boletos dtb ON t.id_transaccion = dtb.id_transaccion
                LEFT JOIN boletos b ON dtb.id_boleto = b.id_boleto
                LEFT JOIN sorteos s ON b.id_sorteo = s.id_sorteo
                GROUP BY t.id_transaccion, t.referencia_pago, t.monto_total, t.estado_pago, t.fecha_creacion, 
                         u.primer_nombre, u.apellido_paterno, u.email
                ORDER BY t.fecha_creacion DESC
                LIMIT ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando consulta de pagos pendientes: " . $conn->error);
            return $pagos;
        }
        
        $stmt->bind_param("i", $limit);
        
        if (!$stmt->execute()) {
            error_log("Error ejecutando consulta de pagos pendientes: " . $stmt->error);
            $stmt->close();
            return $pagos;
        }
        
        $result = $stmt->get_result();
        
        if (!$result) {
            error_log("Error obteniendo resultados de pagos pendientes: " . $conn->error);
            $stmt->close();
            return $pagos;
        }
        
        while ($row = $result->fetch_assoc()) {
            $sorteo_titulo = $row['sorteos_titulos'] ?: 'Sin sorteo asignado';
            // Si hay múltiples sorteos, tomar solo el primero
            if (strpos($sorteo_titulo, ',') !== false) {
                $sorteo_titulo = explode(',', $sorteo_titulo)[0] . ' (+' . (substr_count($sorteo_titulo, ',') + 1) . ')';
            }
            
            $pagos[] = [
                'id' => $row['id_transaccion'],
                'referencia' => $row['referencia_pago'] ?: 'REF-' . str_pad($row['id_transaccion'], 6, '0', STR_PAD_LEFT),
                'usuario_nombre' => $row['primer_nombre'] . ' ' . $row['apellido_paterno'],
                'usuario_email' => $row['email'],
                'sorteo' => $sorteo_titulo,
                'monto' => number_format($row['monto_total'], 2),
                'estado' => $row['estado_pago'],
                'iniciales' => strtoupper(substr($row['primer_nombre'], 0, 1) . substr($row['apellido_paterno'], 0, 1))
            ];
        }
        
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error obteniendo pagos pendientes: " . $e->getMessage());
    }
    
    return $pagos;
}

/**
 * Obtiene datos de ventas diarias para el gráfico
 */
function obtenerDatosGraficoVentas($conn, $dias = 30) {
    $datos = [];
    
    // Validar conexión
    if (!$conn || $conn->connect_error) {
        error_log("Error: Conexión a base de datos no válida en obtenerDatosGraficoVentas()");
        return $datos;
    }
    
    try {
        // Obtener ventas diarias de los últimos N días
        // Contar boletos vendidos por día basado en fecha_creacion de transacciones
        $sql = "SELECT 
                    DATE(t.fecha_creacion) as fecha,
                    COUNT(DISTINCT b.id_boleto) as cantidad
                FROM boletos b
                INNER JOIN detalle_transaccion_boletos dtb ON b.id_boleto = dtb.id_boleto
                INNER JOIN transacciones t ON dtb.id_transaccion = t.id_transaccion
                WHERE b.estado = 'Vendido'
                AND t.estado_pago = 'Completado'
                AND DATE(t.fecha_creacion) >= DATE_SUB(CURRENT_DATE(), INTERVAL ? DAY)
                GROUP BY DATE(t.fecha_creacion)
                ORDER BY fecha ASC";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando consulta de gráfico: " . $conn->error);
            return $datos;
        }
        
        $stmt->bind_param("i", $dias);
        
        if (!$stmt->execute()) {
            error_log("Error ejecutando consulta de gráfico: " . $stmt->error);
            $stmt->close();
            return $datos;
        }
        
        $result = $stmt->get_result();
        
        if (!$result) {
            error_log("Error obteniendo resultados de gráfico: " . $conn->error);
            $stmt->close();
            return $datos;
        }
        
        // Crear array asociativo fecha => cantidad
        while ($row = $result->fetch_assoc()) {
            $datos[$row['fecha']] = (int)$row['cantidad'];
        }
        
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error obteniendo datos del gráfico: " . $e->getMessage());
    }
    
    return $datos;
}

/**
 * Genera el SVG del gráfico de ventas basado en datos reales
 */
function generarGraficoSVG($datos_grafico, $dias = 30) {
    // Generar array de fechas para los últimos N días
    $fechas = [];
    $valores = [];
    $fecha_actual = new DateTime();
    
    for ($i = $dias - 1; $i >= 0; $i--) {
        $fecha = clone $fecha_actual;
        $fecha->modify("-$i days");
        $fecha_str = $fecha->format('Y-m-d');
        $fechas[] = $fecha_str;
        $valores[] = isset($datos_grafico[$fecha_str]) ? (int)$datos_grafico[$fecha_str] : 0;
    }
    
    // Si no hay datos, mostrar gráfico vacío
    if (empty($valores) || max($valores) == 0) {
        return generarGraficoVacio();
    }
    
    // Calcular dimensiones del gráfico
    $ancho = 800;
    $alto = 300;
    $margen_x = 40;
    $margen_y = 30;
    $ancho_util = $ancho - ($margen_x * 2);
    $alto_util = $alto - ($margen_y * 2);
    
    // Encontrar valor máximo para escalar
    $max_valor = max($valores);
    $max_valor = $max_valor > 0 ? $max_valor : 1; // Evitar división por cero
    
    // Calcular puntos del gráfico
    $puntos = [];
    $puntos_area = [];
    $num_puntos = count($valores);
    
    for ($i = 0; $i < $num_puntos; $i++) {
        $divisor = ($num_puntos > 1) ? ($num_puntos - 1) : 1;
        $x = $margen_x + ($i / $divisor) * $ancho_util;
        $y_normalizado = $valores[$i] / $max_valor;
        $y = $alto - $margen_y - ($y_normalizado * $alto_util);
        
        $puntos[] = ['x' => $x, 'y' => $y];
        $puntos_area[] = ['x' => $x, 'y' => $y];
    }
    
    // Generar path para el área (con curva suave)
    $path_area = "M{$puntos_area[0]['x']}," . ($alto - $margen_y);
    for ($i = 0; $i < count($puntos_area); $i++) {
        if ($i == 0) {
            $path_area .= " L{$puntos_area[$i]['x']},{$puntos_area[$i]['y']}";
        } else {
            $x_medio = ($puntos_area[$i-1]['x'] + $puntos_area[$i]['x']) / 2;
            $path_area .= " C{$x_medio},{$puntos_area[$i-1]['y']} {$x_medio},{$puntos_area[$i]['y']} {$puntos_area[$i]['x']},{$puntos_area[$i]['y']}";
        }
    }
    $path_area .= " L{$puntos_area[count($puntos_area)-1]['x']}," . ($alto - $margen_y) . " Z";
    
    // Generar path para la línea (con curva suave)
    $path_linea = "M{$puntos[0]['x']},{$puntos[0]['y']}";
    for ($i = 1; $i < count($puntos); $i++) {
        $x_medio = ($puntos[$i-1]['x'] + $puntos[$i]['x']) / 2;
        $path_linea .= " C{$x_medio},{$puntos[$i-1]['y']} {$x_medio},{$puntos[$i]['y']} {$puntos[$i]['x']},{$puntos[$i]['y']}";
    }
    
    // Generar líneas de la grilla
    $lineas_grilla = '';
    $num_lineas = 4;
    for ($i = 0; $i <= $num_lineas; $i++) {
        $y = $margen_y + ($i / $num_lineas) * $alto_util;
        $es_base = ($i == $num_lineas);
        $stroke_dash = $es_base ? '' : 'stroke-dasharray="4"';
        $lineas_grilla .= "<line stroke=\"#2a3241\" stroke-width=\"1\" x1=\"{$margen_x}\" x2=\"" . ($ancho - $margen_x) . "\" y1=\"{$y}\" y2=\"{$y}\" {$stroke_dash}></line>\n                ";
    }
    
    return <<<SVG
            <svg class="w-full h-full" preserveAspectRatio="none" viewBox="0 0 {$ancho} {$alto}">
                <defs>
                    <linearGradient id="gradient" x1="0" x2="0" y1="0" y2="1">
                        <stop offset="0%" stop-color="#2463eb" stop-opacity="0.2"></stop>
                        <stop offset="100%" stop-color="#2463eb" stop-opacity="0"></stop>
                    </linearGradient>
                </defs>
                <!-- Grid Lines -->
                {$lineas_grilla}
                <!-- Area Path -->
                <path d="{$path_area}" fill="url(#gradient)"></path>
                <!-- Line Path -->
                <path d="{$path_linea}" fill="none" stroke="#2463eb" stroke-linecap="round" stroke-width="3"></path>
            </svg>
SVG;
}

/**
 * Genera un gráfico vacío cuando no hay datos
 */
function generarGraficoVacio() {
    return <<<SVG
            <svg class="w-full h-full" preserveAspectRatio="none" viewBox="0 0 800 300">
                <defs>
                    <linearGradient id="gradient" x1="0" x2="0" y1="0" y2="1">
                        <stop offset="0%" stop-color="#2463eb" stop-opacity="0.2"></stop>
                        <stop offset="100%" stop-color="#2463eb" stop-opacity="0"></stop>
                    </linearGradient>
                </defs>
                <!-- Grid Lines -->
                <line stroke="#2a3241" stroke-width="1" x1="0" x2="800" y1="250" y2="250"></line>
                <line stroke="#2a3241" stroke-dasharray="4" stroke-width="1" x1="0" x2="800" y1="190" y2="190"></line>
                <line stroke="#2a3241" stroke-dasharray="4" stroke-width="1" x1="0" x2="800" y1="130" y2="130"></line>
                <line stroke="#2a3241" stroke-dasharray="4" stroke-width="1" x1="0" x2="800" y1="70" y2="70"></line>
                <!-- Mensaje de sin datos -->
                <text x="400" y="150" text-anchor="middle" fill="#9da6b9" font-size="14" font-family="Inter, sans-serif">No hay datos de ventas disponibles</text>
            </svg>
SVG;
}

// Validar conexión antes de obtener datos
if (!$conn || $conn->connect_error) {
    die("Error de conexión a la base de datos. Por favor, contacta al administrador.");
}

// Obtener datos para el dashboard
$kpis = obtenerKPIs($conn);
$sorteos_finalizando = obtenerSorteosPorFinalizar($conn);
$pagos_pendientes = obtenerPagosPendientes($conn);
$datos_grafico = obtenerDatosGraficoVentas($conn, 30);
?>


<html class="dark" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Dashboard Administrador - Sorteos Web</title>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
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
                        "card-dark": "#1e2433", // Slightly lighter than bg-dark for contrast
                        "border-dark": "#2a3241",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
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
<aside id="sidebar" class="w-64 flex-shrink-0 flex flex-col border-r border-gray-200 dark:border-border-dark bg-white dark:bg-[#151a25] lg:translate-x-0 -translate-x-full lg:static fixed inset-y-0 left-0 z-30 transition-transform duration-300">
<!-- Mobile overlay -->
<div id="mobileOverlay" onclick="toggleMobileMenu()" class="hidden lg:hidden fixed inset-0 bg-black/50 z-20"></div>
<div class="h-16 flex items-center px-6 border-b border-gray-200 dark:border-border-dark">
<div class="flex items-center gap-2 text-primary">
<span class="material-symbols-outlined text-3xl">confirmation_number</span>
<span class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">Sorteos<span class="text-primary">Admin</span></span>
</div>
</div>
<div class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
<p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-2">Principal</p>
<a class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="DashboardAdmnistrador.php" data-page="DashboardAdmnistrador.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">dashboard</span>
                    Dashboard
                </a>
<a class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="CrudGestionSorteo.php" data-page="CrudGestionSorteo.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">confirmation_number</span>
                    Gestión de Sorteos
                </a>
<a class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="ValidacionPagosAdministrador.php" data-page="ValidacionPagosAdministrador.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">payments</span>
                    Validación de Pagos
                    <span class="ml-auto bg-yellow-500/20 text-yellow-500 text-xs font-bold px-2 py-0.5 rounded-full">3</span>
</a>
<a class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="GeneradorGanadoresAdminstradores.php" data-page="GeneradorGanadoresAdminstradores.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">emoji_events</span>
                    Generación de Ganadores
                </a>
<p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-6">Administración</p>
<a class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="GestionUsuariosAdministrador.php" data-page="GestionUsuariosAdministrador.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">group</span>
                    Usuarios
                </a>
<a class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="AuditoriaAccionesAdmin.php" data-page="AuditoriaAccionesAdmin.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">settings</span>
                    Auditoría
                </a>
<a class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="InformesEstadisticasAdmin.php" data-page="InformesEstadisticasAdmin.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">analytics</span>
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
<button id="mobileMenuToggle" onclick="toggleMobileMenu()" class="lg:hidden text-gray-500 hover:text-primary transition-colors">
<span class="material-symbols-outlined">menu</span>
</button>
<h1 class="text-xl font-bold text-slate-900 dark:text-white hidden sm:block">Dashboard</h1>
</div>
<div class="flex items-center gap-4">
<div class="relative hidden md:block w-64">
<span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
<span class="material-symbols-outlined text-[20px]">search</span>
</span>
<input id="headerSearchInput" class="w-full bg-gray-100 dark:bg-[#1e2433] border-none rounded-lg py-2 pl-10 pr-4 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary placeholder-gray-500" placeholder="Buscar sorteo, usuario..." type="text"/>
</div>
<button id="notificationsButton" onclick="showNotifications()" class="relative p-2 text-gray-500 hover:text-primary transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-white/5">
<span class="material-symbols-outlined">notifications</span>
<span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
</button>
</div>
</header>
<!-- Scrollable Content -->
<div class="flex-1 overflow-y-auto p-6 space-y-6">
<div class="max-w-[1400px] mx-auto w-full">
<!-- Breadcrumbs -->
<div class="flex flex-wrap gap-2 px-4 py-2 mb-4">
<span class="text-white text-sm font-medium leading-normal flex items-center gap-1">
<span class="material-symbols-outlined !text-lg">dashboard</span>
                            Dashboard
                        </span>
</div>
<!-- Action Bar -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
<div>
<h2 class="text-2xl font-bold text-slate-900 dark:text-white">Resumen General</h2>
<p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Bienvenido de nuevo. Aquí tienes lo que está pasando hoy.</p>
</div>
<button onclick="window.location.href='CrudGestionSorteo.php'" class="bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-lg font-medium flex items-center gap-2 transition-all shadow-lg shadow-primary/20">
<span class="material-symbols-outlined text-[20px]">add</span>
                        Crear Nuevo Sorteo
                    </button>
</div>
</div>
<!-- KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Total Ingresos -->
    <div class="bg-white dark:bg-card-dark p-5 rounded-xl border border-gray-200 dark:border-border-dark flex flex-col justify-between h-32 relative overflow-hidden group">
        <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
            <span class="material-symbols-outlined text-6xl text-primary">attach_money</span>
        </div>
        <div class="flex justify-between items-start">
            <div class="p-2 bg-primary/10 rounded-lg text-primary">
                <span class="material-symbols-outlined">attach_money</span>
            </div>
            <span class="flex items-center text-xs font-medium <?php echo $kpis['tendencia_ingresos'] >= 0 ? 'text-green-500 bg-green-500/10' : 'text-red-500 bg-red-500/10'; ?> px-2 py-1 rounded-full">
                <span class="material-symbols-outlined text-[14px] mr-1"><?php echo $kpis['tendencia_ingresos'] >= 0 ? 'trending_up' : 'trending_down'; ?></span> <?php echo ($kpis['tendencia_ingresos'] >= 0 ? '+' : '') . $kpis['tendencia_ingresos']; ?>%
            </span>
        </div>
        <div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Ingresos Totales</p>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-1">$<?php echo $kpis['ingresos_totales']; ?></h3>
        </div>
    </div>
    <!-- Boletos Vendidos -->
    <div class="bg-white dark:bg-card-dark p-5 rounded-xl border border-gray-200 dark:border-border-dark flex flex-col justify-between h-32 relative overflow-hidden group">
        <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
            <span class="material-symbols-outlined text-6xl text-blue-400">confirmation_number</span>
        </div>
        <div class="flex justify-between items-start">
            <div class="p-2 bg-blue-500/10 rounded-lg text-blue-500">
                <span class="material-symbols-outlined">confirmation_number</span>
            </div>
            <span class="flex items-center text-xs font-medium <?php echo $kpis['tendencia_boletos'] >= 0 ? 'text-green-500 bg-green-500/10' : 'text-red-500 bg-red-500/10'; ?> px-2 py-1 rounded-full">
                <span class="material-symbols-outlined text-[14px] mr-1"><?php echo $kpis['tendencia_boletos'] >= 0 ? 'trending_up' : 'trending_down'; ?></span> <?php echo ($kpis['tendencia_boletos'] >= 0 ? '+' : '') . $kpis['tendencia_boletos']; ?>%
            </span>
        </div>
        <div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Boletos Vendidos</p>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-1"><?php echo number_format($kpis['boletos_vendidos']); ?></h3>
        </div>
    </div>
    <!-- Sorteos Activos -->
    <div class="bg-white dark:bg-card-dark p-5 rounded-xl border border-gray-200 dark:border-border-dark flex flex-col justify-between h-32 relative overflow-hidden group">
        <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
            <span class="material-symbols-outlined text-6xl text-purple-400">casino</span>
        </div>
        <div class="flex justify-between items-start">
            <div class="p-2 bg-purple-500/10 rounded-lg text-purple-500">
                <span class="material-symbols-outlined">casino</span>
            </div>
        </div>
        <div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Sorteos Activos</p>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-1"><?php echo $kpis['sorteos_activos']; ?></h3>
        </div>
    </div>
    <!-- Pagos Pendientes -->
    <div class="bg-white dark:bg-card-dark p-5 rounded-xl border border-yellow-500/30 flex flex-col justify-between h-32 relative overflow-hidden group">
        <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
            <span class="material-symbols-outlined text-6xl text-yellow-500">pending_actions</span>
        </div>
        <div class="flex justify-between items-start">
            <div class="p-2 bg-yellow-500/10 rounded-lg text-yellow-500">
                <span class="material-symbols-outlined">pending_actions</span>
            </div>
            <?php if ($kpis['pagos_pendientes'] > 0): ?>
            <span class="flex items-center text-xs font-bold text-yellow-500 bg-yellow-500/10 px-2 py-1 rounded-full animate-pulse">
                Acción Requerida
            </span>
            <?php endif; ?>
        </div>
        <div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Pagos Pendientes</p>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-1"><?php echo $kpis['pagos_pendientes']; ?></h3>
        </div>
    </div>
</div>
<!-- Main Grid: Charts & Sidebar List -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Chart Section -->
    <div class="lg:col-span-2 bg-white dark:bg-card-dark rounded-xl border border-gray-200 dark:border-border-dark p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Ventas de Boletos</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Últimos 30 días</p>
            </div>
            <select id="chartPeriodSelect" onchange="updateChartPeriod(this.value)" class="bg-gray-100 dark:bg-[#111621] border-none text-xs rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:ring-0">
                <option value="30days">Últimos 30 días</option>
                <option value="week">Esta semana</option>
                <option value="year">Este año</option>
            </select>
        </div>
        <div class="h-64 w-full">
            <?php echo generarGraficoSVG($datos_grafico, 30); ?>
        </div>
        <div class="flex justify-between text-xs text-gray-500 mt-2 px-2">
            <?php
            // Generar etiquetas de fechas dinámicas
            $fecha_actual = new DateTime();
            $num_etiquetas = 5;
            for ($i = 0; $i < $num_etiquetas; $i++) {
                $dias_atras = round(($i / ($num_etiquetas - 1)) * 29);
                $fecha = clone $fecha_actual;
                $fecha->modify("-$dias_atras days");
                echo "<span>" . $fecha->format('d M') . "</span>\n            ";
            }
            ?>
        </div>
    </div>
    <!-- Closing Soon List -->
    <div class="bg-white dark:bg-card-dark rounded-xl border border-gray-200 dark:border-border-dark flex flex-col">
        <div class="p-6 border-b border-gray-200 dark:border-border-dark flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Sorteos por Finalizar</h3>
            <a class="text-xs text-primary font-medium hover:underline" href="CrudGestionSorteo.php">Ver todos</a>
        </div>
        <div class="flex-1 overflow-y-auto p-2">
            <div class="flex flex-col gap-2">
                <?php if (empty($sorteos_finalizando)): ?>
                    <div class="p-6 text-center">
                        <p class="text-gray-500 dark:text-gray-400 text-sm">No hay sorteos próximos a finalizar</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($sorteos_finalizando as $sorteo): ?>
                    <div class="p-3 hover:bg-gray-50 dark:hover:bg-white/5 rounded-lg flex items-center gap-3 transition-colors cursor-pointer group" onclick="viewRaffleDetails('<?php echo htmlspecialchars($sorteo['titulo']); ?>')">
                        <div class="w-12 h-12 rounded-lg bg-cover bg-center shrink-0" style="background-image: url('<?php echo htmlspecialchars($sorteo['imagen_url']); ?>');"></div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-slate-900 dark:text-white truncate group-hover:text-primary transition-colors"><?php echo htmlspecialchars($sorteo['titulo']); ?></h4>
                            <p class="text-xs text-gray-500">Cierra en: <span class="<?php echo $sorteo['urgente'] ? 'text-orange-500' : 'text-gray-400'; ?> font-bold"><?php echo $sorteo['tiempo_restante']; ?></span></p>
                        </div>
                        <button onclick="event.stopPropagation(); viewRaffleDetails('<?php echo htmlspecialchars($sorteo['titulo']); ?>')" class="text-gray-400 hover:text-primary">
                            <span class="material-symbols-outlined text-[20px]">chevron_right</span>
                        </button>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="p-4 border-t border-gray-200 dark:border-border-dark">
            <button onclick="window.location.href='GeneradorGanadoresAdminstradores.php'" class="w-full py-2 text-sm text-center text-slate-900 dark:text-white bg-gray-100 dark:bg-[#111621] hover:bg-gray-200 dark:hover:bg-[#2a3241] rounded-lg transition-colors font-medium">
                Gestionar Ganadores
            </button>
        </div>
    </div>
</div>
<!-- Recent Payments Table -->
<div class="bg-white dark:bg-card-dark rounded-xl border border-gray-200 dark:border-border-dark overflow-hidden">
    <div class="p-6 border-b border-gray-200 dark:border-border-dark flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Validación de Pagos Recientes</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Pagos que requieren tu atención inmediata.</p>
        </div>
        <div class="flex gap-2">
            <button onclick="showPaymentFilters()" class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-[#111621] rounded-lg hover:bg-gray-200 dark:hover:bg-[#2a3241]">Filtrar</button>
            <button onclick="exportPaymentsTable()" class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-[#111621] rounded-lg hover:bg-gray-200 dark:hover:bg-[#2a3241]">Exportar</button>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-[#151a25] border-b border-gray-200 dark:border-border-dark">
                    <th class="py-3 px-6 text-xs font-semibold uppercase tracking-wider text-gray-500">Usuario</th>
                    <th class="py-3 px-6 text-xs font-semibold uppercase tracking-wider text-gray-500">Sorteo</th>
                    <th class="py-3 px-6 text-xs font-semibold uppercase tracking-wider text-gray-500">Referencia</th>
                    <th class="py-3 px-6 text-xs font-semibold uppercase tracking-wider text-gray-500">Monto</th>
                    <th class="py-3 px-6 text-xs font-semibold uppercase tracking-wider text-gray-500">Estado</th>
                    <th class="py-3 px-6 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-border-dark">
                <?php if (empty($pagos_pendientes)): ?>
                    <tr>
                        <td colspan="6" class="py-8 text-center">
                            <p class="text-gray-500 dark:text-gray-400 text-sm">No hay pagos registrados</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $colores_avatar = ['bg-gray-700', 'bg-blue-600', 'bg-purple-600', 'bg-pink-600', 'bg-green-600', 'bg-yellow-600', 'bg-red-600'];
                    foreach ($pagos_pendientes as $index => $pago): 
                        $color_avatar = $colores_avatar[$index % count($colores_avatar)];
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full <?php echo $color_avatar; ?> flex items-center justify-center text-xs font-bold text-white"><?php echo $pago['iniciales']; ?></div>
                                <div>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white"><?php echo htmlspecialchars($pago['usuario_nombre']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($pago['usuario_email']); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-6 text-sm text-gray-600 dark:text-gray-300"><?php echo htmlspecialchars($pago['sorteo']); ?></td>
                        <td class="py-4 px-6 text-sm text-gray-500 font-mono"><?php echo htmlspecialchars($pago['referencia']); ?></td>
                        <td class="py-4 px-6 text-sm font-medium text-slate-900 dark:text-white">$<?php echo $pago['monto']; ?></td>
                        <td class="py-4 px-6">
                            <?php 
                            $estado_class = '';
                            $estado_texto = '';
                            switch($pago['estado']) {
                                case 'Pendiente':
                                    $estado_class = 'bg-yellow-500/10 text-yellow-500';
                                    $estado_texto = 'Pendiente';
                                    break;
                                case 'Completado':
                                    $estado_class = 'bg-green-500/10 text-green-500';
                                    $estado_texto = 'Aprobado';
                                    break;
                                case 'Fallido':
                                    $estado_class = 'bg-red-500/10 text-red-500';
                                    $estado_texto = 'Rechazado';
                                    break;
                            }
                            ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $estado_class; ?>">
                                <?php echo $estado_texto; ?>
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <?php if ($pago['estado'] == 'Pendiente'): ?>
                                <button onclick="validatePayment('<?php echo htmlspecialchars($pago['referencia']); ?>', '<?php echo htmlspecialchars($pago['usuario_nombre']); ?>')" class="text-primary hover:text-primary/80 text-sm font-medium mr-3">Validar</button>
                                <button onclick="rejectPayment('<?php echo htmlspecialchars($pago['referencia']); ?>', '<?php echo htmlspecialchars($pago['usuario_nombre']); ?>')" class="text-gray-400 hover:text-red-500 transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">block</span>
                                </button>
                            <?php else: ?>
                                <button onclick="viewPaymentDetails('<?php echo htmlspecialchars($pago['referencia']); ?>')" class="text-gray-400 hover:text-slate-900 dark:hover:text-white text-sm font-medium">Ver</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-200 dark:border-border-dark flex items-center justify-between">
        <p class="text-xs text-gray-500">Mostrando <?php echo count($pagos_pendientes); ?> de <?php echo $kpis['pagos_pendientes']; ?> pendientes</p>
        <div class="flex gap-2">
            <button id="prevPaymentsBtn" onclick="changePaymentsPage('prev')" class="px-3 py-1 text-xs rounded border border-gray-300 dark:border-gray-700 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5 disabled:opacity-50">Anterior</button>
            <button id="nextPaymentsBtn" onclick="changePaymentsPage('next')" class="px-3 py-1 text-xs rounded border border-gray-300 dark:border-gray-700 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5">Siguiente</button>
        </div>
    </div>
</div>
</main>
</div>
<script>
/**
 * DASHBOARD ADMINISTRADOR - Funcionalidades JavaScript
 * Todas las funciones están documentadas para facilitar la migración a otra arquitectura
 */

// ========== BÚSQUEDA GLOBAL ==========
document.addEventListener('DOMContentLoaded', function() {
    const headerSearchInput = document.getElementById('headerSearchInput');
    if (headerSearchInput) {
        headerSearchInput.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase().trim();
            if (query.length > 2) {
                performGlobalSearch(query);
            }
        });
        
        headerSearchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = e.target.value.toLowerCase().trim();
                if (query.length > 0) {
                    performGlobalSearch(query);
                }
            }
        });
    }
});

/**
 * Realiza búsqueda global en la página actual
 * @param {string} query - Término de búsqueda
 */
function performGlobalSearch(query) {
    try {
        if (!query || query.length < 2) {
            return;
        }
        
        const queryLower = query.toLowerCase().trim();
        let resultados = [];
        
        // Buscar en la tabla de pagos recientes
        const paymentRows = document.querySelectorAll('tbody tr');
        paymentRows.forEach(row => {
            const texto = row.textContent.toLowerCase();
            if (texto.includes(queryLower)) {
                resultados.push({
                    tipo: 'Pago',
                    texto: row.querySelector('td')?.textContent || '',
                    elemento: row
                });
            }
        });
        
        // Buscar en sorteos por finalizar
        const raffleCards = document.querySelectorAll('[data-raffle-name], .raffle-card');
        raffleCards.forEach(card => {
            const texto = card.textContent.toLowerCase();
            if (texto.includes(queryLower)) {
                resultados.push({
                    tipo: 'Sorteo',
                    texto: card.getAttribute('data-raffle-name') || card.textContent.substring(0, 50),
                    elemento: card
                });
            }
        });
        
        // Si hay resultados, resaltarlos
        if (resultados.length > 0) {
            // Remover resaltados previos
            document.querySelectorAll('.search-highlight').forEach(el => {
                el.classList.remove('search-highlight', 'bg-yellow-500/20');
            });
            
            // Resaltar resultados
            resultados.forEach(resultado => {
                if (resultado.elemento) {
                    resultado.elemento.classList.add('search-highlight', 'bg-yellow-500/20');
                    resultado.elemento.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });
            
            showNotification(`Se encontraron ${resultados.length} resultado(s)`, 'success');
        } else {
            showNotification('No se encontraron resultados', 'info');
        }
    } catch (error) {
        console.error('Error en búsqueda global:', error);
        showNotification('Error al realizar la búsqueda', 'error');
    }
}

// ========== NOTIFICACIONES ==========
/**
 * Muestra panel de notificaciones
 */
function showNotifications() {
    const notifications = [
        { id: 1, type: 'payment', message: '3 pagos pendientes de validación', time: 'Hace 5 min', action: () => window.location.href = 'ValidacionPagosAdministrador.php' },
        { id: 2, type: 'raffle', message: 'Sorteo "iPhone 15 Pro Max" finaliza en 2 horas', time: 'Hace 15 min', action: () => window.location.href = 'CrudGestionSorteo.php' },
        { id: 3, type: 'winner', message: 'Ganador generado para sorteo #8820', time: 'Hace 1 hora', action: () => window.location.href = 'GeneradorGanadoresAdminstradores.php' }
    ];
    
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-50 overflow-y-auto';
    modal.onclick = function(e) { if (e.target === modal) modal.remove(); };
    
    modal.innerHTML = `
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm"></div>
            <div class="inline-block align-bottom bg-white dark:bg-[#1c212c] rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-200 dark:border-border-dark">
                <div class="px-4 pt-5 pb-4 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-slate-900 dark:text-white mb-4">Notificaciones</h3>
                    <div class="space-y-3">
                        ${notifications.map(n => `
                            <div onclick="${n.action.toString().replace('function', '')}" class="p-3 hover:bg-gray-50 dark:hover:bg-white/5 rounded-lg cursor-pointer border border-gray-200 dark:border-border-dark">
                                <p class="text-sm font-medium text-slate-900 dark:text-white">${n.message}</p>
                                <p class="text-xs text-gray-500 mt-1">${n.time}</p>
                            </div>
                        `).join('')}
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-[#151a23] px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button onclick="this.closest('.fixed').remove()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-blue-600 sm:ml-3 sm:w-auto sm:text-sm">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

// ========== GRÁFICO DE VENTAS ==========
/**
 * Actualiza el período del gráfico de ventas
 * @param {string} period - Período seleccionado (30days, week, year)
 */
function updateChartPeriod(period) {
    console.log('Actualizando gráfico para período:', period);
    showNotification(`Gráfico actualizado: ${period}`, 'success');
    // En producción, esto haría una llamada API para obtener nuevos datos
    // y actualizaría el SVG del gráfico
}

// ========== SORTEOS POR FINALIZAR ==========
/**
 * Muestra detalles de un sorteo
 * @param {string} raffleName - Nombre del sorteo
 */
function viewRaffleDetails(raffleName) {
    console.log('Ver detalles de sorteo:', raffleName);
    window.location.href = `CrudGestionSorteo.php?raffle=${encodeURIComponent(raffleName)}`;
}

// ========== VALIDACIÓN DE PAGOS ==========
/**
 * Valida un pago pendiente
 * @param {string} reference - Referencia del pago
 * @param {string} userName - Nombre del usuario
 */
async function validatePayment(reference, userName) {
    const confirmado = await mostrarModalConfirmacion(
        `¿Deseas aprobar el pago ${reference} de ${userName}?`,
        'Confirmar aprobación',
        'info'
    );
    
    if (!confirmado) {
        return;
    }
    
    // Simular validación
    showNotification(`Pago ${reference} aprobado exitosamente`, 'success');
    
    // En producción: llamada API
    // fetch(`/api/payments/${reference}/approve`, { method: 'POST' })
    //     .then(response => response.json())
    //     .then(data => {
    //         showNotification('Pago aprobado', 'success');
    //         location.reload();
    //     });
}

/**
 * Rechaza un pago pendiente
 * @param {string} reference - Referencia del pago
 * @param {string} userName - Nombre del usuario
 */
async function rejectPayment(reference, userName) {
    const motivo = await mostrarModalInput(
        `¿Por qué deseas rechazar el pago ${reference} de ${userName}?`,
        'Motivo del rechazo',
        'Ingresa el motivo del rechazo...',
        ''
    );
    
    if (!motivo || motivo.trim() === '') {
        return;
    }
    
    // Simular rechazo
    showNotification(`Pago ${reference} rechazado`, 'success');
    
    // En producción: llamada API
    // fetch(`/api/payments/${reference}/reject`, { 
    //     method: 'POST',
    //     body: JSON.stringify({ motivo })
    // })
}

/**
 * Muestra detalles de un pago
 * @param {string} reference - Referencia del pago
 */
function viewPaymentDetails(reference) {
    window.location.href = `ValidacionPagosAdministrador.php?payment=${reference}`;
}

/**
 * Muestra filtros para la tabla de pagos
 */
function showPaymentFilters() {
    showNotification('Funcionalidad de filtros próximamente', 'info');
    // En producción, mostraría un modal con opciones de filtrado
}

/**
 * Exporta la tabla de pagos a CSV
 */
function exportPaymentsTable() {
    const data = [
        ['Usuario', 'Sorteo', 'Referencia', 'Monto', 'Estado'],
        ['Juan Pérez', 'Sorteo Zapatillas Jordan', 'REF-982342', '$25.00', 'Pendiente'],
        ['María Rodríguez', 'iPhone 15 Pro Max', 'REF-982343', '$50.00', 'Aprobado'],
        ['Luis González', 'Gran Premio $50,000', 'REF-982344', '$100.00', 'Rechazado'],
        ['Ana Sánchez', 'Sorteo Zapatillas Jordan', 'REF-982345', '$25.00', 'Pendiente']
    ];
    
    const csv = data.map(row => row.map(cell => `"${cell}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `pagos_${new Date().toISOString().split('T')[0]}.csv`;
    link.click();
    
    showNotification('Tabla exportada exitosamente', 'success');
}

// ========== PAGINACIÓN DE PAGOS ==========
let currentPaymentsPage = 1;
const totalPaymentsPages = 6; // 23 pagos / 4 por página

/**
 * Cambia la página de la tabla de pagos
 * @param {string} direction - 'prev' o 'next'
 */
function changePaymentsPage(direction) {
    if (direction === 'prev' && currentPaymentsPage > 1) {
        currentPaymentsPage--;
    } else if (direction === 'next' && currentPaymentsPage < totalPaymentsPages) {
        currentPaymentsPage++;
    }
    
    // Actualizar botones
    document.getElementById('prevPaymentsBtn').disabled = currentPaymentsPage === 1;
    document.getElementById('nextPaymentsBtn').disabled = currentPaymentsPage === totalPaymentsPages;
    
    // En producción, cargaría los datos de la página
    showNotification(`Página ${currentPaymentsPage} de ${totalPaymentsPages}`, 'info');
}

// ========== NOTIFICACIONES TOAST ==========
/**
 * Muestra una notificación toast
 * @param {string} message - Mensaje a mostrar
 * @param {string} type - Tipo: 'success', 'error', 'info'
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    notification.style.opacity = '0';
    notification.style.transform = 'translateX(100%)';
    notification.style.transition = 'all 0.3s ease-in-out';
    
    notification.innerHTML = `
        <span class="material-symbols-outlined">${type === 'success' ? 'check_circle' : type === 'error' ? 'error' : 'info'}</span>
        <span class="font-medium">${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// ========== NAVEGACIÓN DINÁMICA ==========
/**
 * Inicializa la navegación dinámica
 */
document.addEventListener('DOMContentLoaded', function() {
    setActiveMenuItem();
    initMobileMenu();
});

/**
 * Establece el estado activo del menú según la página actual
 */
function setActiveMenuItem() {
    const currentPage = window.location.pathname.split('/').pop() || window.location.href.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const linkPage = link.getAttribute('data-page') || link.getAttribute('href');
        if (linkPage === currentPage || link.getAttribute('href') === currentPage) {
            link.className = 'nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary/10 text-primary font-medium';
            const icon = link.querySelector('.material-symbols-outlined');
            if (icon) {
                icon.classList.remove('group-hover:text-primary', 'transition-colors');
            }
        } else {
            link.className = 'nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group';
            const icon = link.querySelector('.material-symbols-outlined');
            if (icon && !icon.classList.contains('group-hover:text-primary')) {
                icon.classList.add('group-hover:text-primary', 'transition-colors');
            }
        }
    });
}

/**
 * Inicializa el menú móvil
 */
function initMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('mobileOverlay');
    
    // Cerrar menú al hacer clic en un enlace (móvil)
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 1024) {
                toggleMobileMenu();
            }
        });
    });
    
    // Cerrar menú al redimensionar a desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) {
            if (sidebar) sidebar.classList.remove('-translate-x-full');
            if (overlay) overlay.classList.add('hidden');
        }
    });
}

/**
 * Toggle del menú móvil
 */
function toggleMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('mobileOverlay');
    
    if (sidebar && overlay) {
        const isOpen = !sidebar.classList.contains('-translate-x-full');
        
        if (isOpen) {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        } else {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        }
    }
}

/**
 * Navega hacia atrás en el historial
 */
function goBack() {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        // Fallback a página padre
        window.location.href = 'GestionUsuariosAdministrador.php';
    }
}

// ========== SISTEMA DE MODALES ==========
/**
 * Muestra un modal de confirmación (reemplazo de confirm())
 */
function mostrarModalConfirmacion(mensaje, titulo = 'Confirmar acción', tipo = 'warning') {
    return new Promise((resolve) => {
        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 z-50 overflow-y-auto modal-overlay';
        overlay.style.opacity = '0';
        overlay.style.transition = 'opacity 0.2s ease-in-out';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-labelledby', 'confirm-modal-title');
        overlay.onclick = function(e) {
            if (e.target === overlay) {
                cerrarModal(overlay);
                resolve(false);
            }
        };
        
        const iconos = {
            warning: { icon: 'warning', color: 'text-yellow-400', bg: 'bg-yellow-500/10' },
            danger: { icon: 'error', color: 'text-red-400', bg: 'bg-red-500/10' },
            info: { icon: 'info', color: 'text-blue-400', bg: 'bg-blue-500/10' }
        };
        
        const config = iconos[tipo] || iconos.warning;
        
        const modal = document.createElement('div');
        modal.className = 'flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0';
        
        modal.innerHTML = `
            <div aria-hidden="true" class="fixed inset-0 transition-opacity">
                <div class="absolute inset-0 bg-gray-900/75 backdrop-blur-sm"></div>
            </div>
            <span aria-hidden="true" class="hidden sm:inline-block sm:align-middle sm:h-screen">​</span>
            <div class="inline-block align-bottom bg-white dark:bg-[#1c212c] rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-border-dark">
                <div class="px-4 pt-5 pb-4 sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full ${config.bg} sm:mx-0 sm:h-10 sm:w-10">
                            <span class="material-symbols-outlined ${config.color}">${config.icon}</span>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 id="confirm-modal-title" class="text-lg leading-6 font-medium text-slate-900 dark:text-white">${titulo}</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">${mensaje}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-[#151a23] px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                    <button id="confirmBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Confirmar
                    </button>
                    <button id="cancelBtn" onclick="cerrarModal(this.closest('.modal-overlay')); window.modalConfirmResolve(false);" class="w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-[#1c212c] text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        `;
        
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        
        window.modalConfirmResolve = resolve;
        
        const confirmBtn = overlay.querySelector('#confirmBtn');
        confirmBtn.onclick = () => {
            cerrarModal(overlay);
            resolve(true);
        };
        
        setTimeout(() => {
            overlay.style.opacity = '1';
        }, 10);
        
        const escHandler = (e) => {
            if (e.key === 'Escape') {
                cerrarModal(overlay);
                resolve(false);
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
        
        setTimeout(() => confirmBtn.focus(), 100);
    });
}

/**
 * Muestra un modal de entrada de texto (reemplazo de prompt())
 */
function mostrarModalInput(mensaje, titulo = 'Ingresar información', placeholder = '', valorInicial = '') {
    return new Promise((resolve) => {
        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 z-50 overflow-y-auto modal-overlay';
        overlay.style.opacity = '0';
        overlay.style.transition = 'opacity 0.2s ease-in-out';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-labelledby', 'input-modal-title');
        overlay.onclick = function(e) {
            if (e.target === overlay) {
                cerrarModal(overlay);
                resolve(null);
            }
        };
        
        const modal = document.createElement('div');
        modal.className = 'flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0';
        
        modal.innerHTML = `
            <div aria-hidden="true" class="fixed inset-0 transition-opacity">
                <div class="absolute inset-0 bg-gray-900/75 backdrop-blur-sm"></div>
            </div>
            <span aria-hidden="true" class="hidden sm:inline-block sm:align-middle sm:h-screen">​</span>
            <div class="inline-block align-bottom bg-white dark:bg-[#1c212c] rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-border-dark">
                <div class="px-4 pt-5 pb-4 sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-primary/10 sm:mx-0 sm:h-10 sm:w-10">
                            <span class="material-symbols-outlined text-primary">edit</span>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 id="input-modal-title" class="text-lg leading-6 font-medium text-slate-900 dark:text-white">${titulo}</h3>
                            <div class="mt-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">${mensaje}</p>
                                <input id="modalInput" type="text" value="${valorInicial}" placeholder="${placeholder}" class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm bg-white dark:bg-[#111621] dark:text-white px-3 py-2" autofocus/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-[#151a23] px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                    <button id="submitBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Aceptar
                    </button>
                    <button id="cancelInputBtn" onclick="cerrarModal(this.closest('.modal-overlay')); window.modalInputResolve(null);" class="w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-[#1c212c] text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        `;
        
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        
        window.modalInputResolve = resolve;
        
        const input = overlay.querySelector('#modalInput');
        const submitBtn = overlay.querySelector('#submitBtn');
        
        submitBtn.onclick = () => {
            const valor = input.value.trim();
            cerrarModal(overlay);
            resolve(valor || null);
        };
        
        input.onkeydown = (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitBtn.click();
            }
        };
        
        setTimeout(() => {
            overlay.style.opacity = '1';
            input.focus();
            input.select();
        }, 10);
        
        const escHandler = (e) => {
            if (e.key === 'Escape') {
                cerrarModal(overlay);
                resolve(null);
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
    });
}

/**
 * Cierra un modal
 */
function cerrarModal(overlay) {
    if (overlay) {
        overlay.style.opacity = '0';
        setTimeout(() => {
            overlay.remove();
        }, 200);
    }
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
<!-- Cargar custom-alerts.js ANTES de que se ejecute handleLogoutAdmin -->
<script src="custom-alerts.js"></script>
</body></html>

//pagina para ver el dashboard como administrador despues de iniciar sesion
//Se ve esta pagina para ver el dashboard de la plataforma y los datos de los sorteos, los boletos vendidos, los pagos pendientes, los sorteos activos y los sorteos por finalizar.