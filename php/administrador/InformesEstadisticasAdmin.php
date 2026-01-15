<!DOCTYPE html>
<?php
// Conexión a la base de datos
require_once 'config.php';
$conn = getDBConnection();

/**
 * Obtiene los KPIs principales
 */
function obtenerKPIs($conn) {
    $kpis = [
        'ingresos_totales' => 0,
        'boletos_vendidos' => 0,
        'usuarios_activos' => 0,
        'tasa_conversion' => 0,
        'tendencia_ingresos' => 0,
        'tendencia_boletos' => 0,
        'tendencia_usuarios' => 0
    ];
    
    try {
        // Ingresos totales (pagos completados)
        $sql = "SELECT COALESCE(SUM(monto_total), 0) as total FROM transacciones WHERE estado_pago = 'Completado'";
        $result = $conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            $kpis['ingresos_totales'] = floatval($row['total']);
        }
        
        // Boletos vendidos
        $sql = "SELECT COUNT(*) as total FROM boletos WHERE estado = 'Vendido'";
        $result = $conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            $kpis['boletos_vendidos'] = intval($row['total']);
        }
        
        // Usuarios activos
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE estado = 'Activo'";
        $result = $conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            $kpis['usuarios_activos'] = intval($row['total']);
        }
        
        // Tasa de conversión (boletos vendidos / total boletos)
        $sql = "SELECT 
                    COUNT(CASE WHEN estado = 'Vendido' THEN 1 END) as vendidos,
                    COUNT(*) as total
                FROM boletos";
        $result = $conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            $total = $row['total'] > 0 ? $row['total'] : 1;
            $kpis['tasa_conversion'] = round(($row['vendidos'] / $total) * 100, 1);
        }
        
        // Calcular tendencias (comparar con mes anterior)
        // Ingresos
        $sql = "SELECT COALESCE(SUM(monto_total), 0) as total_mes_actual 
                FROM transacciones 
                WHERE estado_pago = 'Completado' 
                AND MONTH(fecha_creacion) = MONTH(CURRENT_DATE())
                AND YEAR(fecha_creacion) = YEAR(CURRENT_DATE())";
        $result = $conn->query($sql);
        $total_mes_actual = 0;
        if ($result && $row = $result->fetch_assoc()) {
            $total_mes_actual = floatval($row['total_mes_actual']);
        }
        
        $sql = "SELECT COALESCE(SUM(monto_total), 0) as total_mes_anterior 
                FROM transacciones 
                WHERE estado_pago = 'Completado' 
                AND MONTH(fecha_creacion) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
                AND YEAR(fecha_creacion) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))";
        $result = $conn->query($sql);
        $total_mes_anterior = 1;
        if ($result && $row = $result->fetch_assoc()) {
            $total_mes_anterior = floatval($row['total_mes_anterior']) > 0 ? floatval($row['total_mes_anterior']) : 1;
        }
        $kpis['tendencia_ingresos'] = round((($total_mes_actual - $total_mes_anterior) / $total_mes_anterior) * 100, 1);
        
        // Boletos
        $sql = "SELECT COUNT(*) as total_mes_actual 
                FROM boletos 
                WHERE estado = 'Vendido'
                AND MONTH(fecha_reserva) = MONTH(CURRENT_DATE())
                AND YEAR(fecha_reserva) = YEAR(CURRENT_DATE())";
        $result = $conn->query($sql);
        $boletos_mes_actual = 0;
        if ($result && $row = $result->fetch_assoc()) {
            $boletos_mes_actual = intval($row['total_mes_actual']);
        }
        
        $sql = "SELECT COUNT(*) as total_mes_anterior 
                FROM boletos 
                WHERE estado = 'Vendido'
                AND MONTH(fecha_reserva) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
                AND YEAR(fecha_reserva) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))";
        $result = $conn->query($sql);
        $boletos_mes_anterior = 1;
        if ($result && $row = $result->fetch_assoc()) {
            $boletos_mes_anterior = intval($row['total_mes_anterior']) > 0 ? intval($row['total_mes_anterior']) : 1;
        }
        $kpis['tendencia_boletos'] = round((($boletos_mes_actual - $boletos_mes_anterior) / $boletos_mes_anterior) * 100, 1);
        
    } catch (Exception $e) {
        error_log("Error obteniendo KPIs: " . $e->getMessage());
    }
    
    return $kpis;
}

/**
 * Obtiene datos de tendencia de ingresos (últimos 6 meses)
 */
function obtenerTendenciaIngresos($conn) {
    $datos = [];
    
    try {
        for ($i = 5; $i >= 0; $i--) {
            $fecha = date('Y-m', strtotime("-$i months"));
            $mes = date('M', strtotime("-$i months"));
            
            $sql = "SELECT COALESCE(SUM(monto_total), 0) as total 
                    FROM transacciones 
                    WHERE estado_pago = 'Completado' 
                    AND DATE_FORMAT(fecha_creacion, '%Y-%m') = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $fecha);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $total = 0;
            if ($result && $row = $result->fetch_assoc()) {
                $total = floatval($row['total']);
            }
            
            $datos[] = [
                'mes' => $mes,
                'total' => $total
            ];
            
            $stmt->close();
        }
    } catch (Exception $e) {
        error_log("Error obteniendo tendencia de ingresos: " . $e->getMessage());
    }
    
    return $datos;
}

/**
 * Obtiene estado de boletos (vendidos, disponibles)
 */
function obtenerEstadoBoletos($conn) {
    $estados = [
        'vendidos' => 0,
        'disponibles' => 0,
        'total' => 0
    ];
    
    try {
        $sql = "SELECT 
                    COUNT(CASE WHEN estado = 'Vendido' THEN 1 END) as vendidos,
                    COUNT(CASE WHEN estado = 'Disponible' THEN 1 END) as disponibles,
                    COUNT(*) as total
                FROM boletos";
        
        $result = $conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            $estados['vendidos'] = intval($row['vendidos']);
            $estados['disponibles'] = intval($row['disponibles']);
            $estados['total'] = intval($row['total']);
        }
    } catch (Exception $e) {
        error_log("Error obteniendo estado de boletos: " . $e->getMessage());
    }
    
    return $estados;
}

/**
 * Obtiene ventas por sorteo
 */
function obtenerVentasPorSorteo($conn) {
    $ventas = [];
    
    try {
        $sql = "SELECT 
                    s.id_sorteo,
                    s.titulo,
                    s.total_boletos_crear,
                    COUNT(CASE WHEN b.estado = 'Vendido' THEN 1 END) as vendidos,
                    COALESCE(SUM(CASE WHEN t.estado_pago = 'Completado' THEN t.monto_total ELSE 0 END), 0) as ingresos
                FROM sorteos s
                LEFT JOIN boletos b ON s.id_sorteo = b.id_sorteo
                LEFT JOIN detalle_transaccion_boletos dtb ON b.id_boleto = dtb.id_boleto
                LEFT JOIN transacciones t ON dtb.id_transaccion = t.id_transaccion
                GROUP BY s.id_sorteo, s.titulo, s.total_boletos_crear
                ORDER BY ingresos DESC
                LIMIT 5";
        
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $porcentaje = $row['total_boletos_crear'] > 0 
                    ? round(($row['vendidos'] / $row['total_boletos_crear']) * 100, 1) 
                    : 0;
                
                $ventas[] = [
                    'titulo' => $row['titulo'],
                    'vendidos' => intval($row['vendidos']),
                    'total' => intval($row['total_boletos_crear']),
                    'porcentaje' => $porcentaje,
                    'ingresos' => floatval($row['ingresos'])
                ];
            }
        }
    } catch (Exception $e) {
        error_log("Error obteniendo ventas por sorteo: " . $e->getMessage());
    }
    
    return $ventas;
}

/**
 * Obtiene información de campañas de marketing
 */
function obtenerCampanas($conn) {
    $campanas = [];
    
    try {
        $sql = "SELECT 
                    id_campana,
                    red_social,
                    empresa,
                    costo_inversion,
                    clics_generados,
                    estado,
                    fecha_inicio,
                    fecha_fin
                FROM campanas_marketing
                ORDER BY fecha_inicio DESC
                LIMIT 10";
        
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Calcular ROI aproximado basado en ingresos generados
                // Para simplificar, usamos un cálculo basado en clics
                $ingresos_estimados = $row['clics_generados'] * 10; // Estimación
                $roi = $row['costo_inversion'] > 0 
                    ? round((($ingresos_estimados - $row['costo_inversion']) / $row['costo_inversion']) * 100, 1)
                    : 0;
                
                $campanas[] = [
                    'id' => $row['id_campana'],
                    'nombre' => $row['red_social'] . ' - ' . $row['empresa'],
                    'red_social' => $row['red_social'],
                    'empresa' => $row['empresa'],
                    'inversion' => floatval($row['costo_inversion']),
                    'clics' => intval($row['clics_generados']),
                    'estado' => $row['estado'],
                    'roi' => $roi
                ];
            }
        }
    } catch (Exception $e) {
        error_log("Error obteniendo campañas: " . $e->getMessage());
    }
    
    return $campanas;
}

/**
 * Obtiene lista de sorteos para el filtro
 */
function obtenerListaSorteos($conn) {
    $sorteos = [];
    
    try {
        $sql = "SELECT id_sorteo, titulo FROM sorteos ORDER BY titulo";
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $sorteos[] = [
                    'id' => $row['id_sorteo'],
                    'titulo' => $row['titulo']
                ];
            }
        }
    } catch (Exception $e) {
        error_log("Error obteniendo lista de sorteos: " . $e->getMessage());
    }
    
    return $sorteos;
}

// Obtener todos los datos
$kpis = obtenerKPIs($conn);
$tendencia_ingresos = obtenerTendenciaIngresos($conn);
$estado_boletos = obtenerEstadoBoletos($conn);
$ventas_sorteos = obtenerVentasPorSorteo($conn);
$campanas = obtenerCampanas($conn);
$lista_sorteos = obtenerListaSorteos($conn);
?>

<html class="dark" lang="es">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Informes y Estadísticas Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-full bg-cover bg-center" data-alt="User profile picture" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAfIzDdUJZk0e1bBHKOe7BG0HPanJ3nx8d9vtsJZZMiXM6ZJw9-oPch2DQWyWWrowTikKHJBUkhOyI6hUEiy_TgTGdRmm-4uDyO3KjasL500lcWogtry5HOXaJxBgDxpuT_8QBEVTnbuI4727c7c5qtPNid2CyQr0SnpyEcv2R9UEoiXiOVUH_g0RdYwYfb9u5EU5DkqEZl2oL9UW9s45D-zD3htPmEHk69TrCVPL50vnE6cDfTlcz9AJEZo7Hb8gpAhxwAxDP4SCs');"></div>
<div class="flex flex-col">
<span class="text-sm font-medium text-slate-900 dark:text-white">Admin User</span>
<span class="text-xs text-gray-500">admin@sorteos.web</span>
</div>
</div>
</div>
</aside>
<!-- Main Content -->
<main class="flex-1 flex flex-col h-full overflow-hidden bg-background-light dark:bg-background-dark relative">
<!-- Header -->
<header class="h-16 flex items-center justify-between px-6 border-b border-gray-200 dark:border-border-dark bg-white dark:bg-[#151a25]/80 backdrop-blur-md sticky top-0 z-20">
<div class="flex items-center gap-4">
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
<button type="button" onclick="abrirModalFechas()" class="flex items-center h-12 w-full rounded-lg border border-[#3b4354] bg-[#1c1f27] px-3 hover:border-primary transition-all text-left">
<span class="material-symbols-outlined text-text-secondary mr-2">date_range</span>
<span class="bg-transparent border-none text-white text-sm w-full focus:ring-0 placeholder-text-secondary" id="fechaRangeDisplay"><?php echo date('d M, Y', strtotime('-1 month')) . ' - ' . date('d M, Y'); ?></span>
</button>
</div>
<div class="relative group">
<label class="absolute -top-2.5 left-3 bg-background-dark px-1 text-xs font-medium text-text-secondary group-focus-within:text-primary transition-colors">Filtrar por Sorteo</label>
<div class="flex items-center h-12 w-full rounded-lg border border-[#3b4354] bg-[#1c1f27] px-3 focus-within:border-primary focus-within:ring-1 focus-within:ring-primary transition-all">
<span class="material-symbols-outlined text-text-secondary mr-2">confirmation_number</span>
<select class="bg-transparent border-none text-white text-sm w-full focus:ring-0 [&>option]:text-black" id="filtroSorteo">
<option value="">Todos los Sorteos</option>
<?php foreach ($lista_sorteos as $sorteo): ?>
<option value="<?php echo $sorteo['id']; ?>"><?php echo htmlspecialchars($sorteo['titulo']); ?></option>
<?php endforeach; ?>
</select>
</div>
</div>
<div class="relative group">
<label class="absolute -top-2.5 left-3 bg-background-dark px-1 text-xs font-medium text-text-secondary group-focus-within:text-primary transition-colors">Estado de Campaña</label>
<div class="flex items-center h-12 w-full rounded-lg border border-[#3b4354] bg-[#1c1f27] px-3 focus-within:border-primary focus-within:ring-1 focus-within:ring-primary transition-all">
<span class="material-symbols-outlined text-text-secondary mr-2">filter_alt</span>
<select class="bg-transparent border-none text-white text-sm w-full focus:ring-0 [&>option]:text-black" id="filtroCampana">
<option value="">Todas las campañas</option>
<option value="Activa">Activas</option>
<option value="Pausada">Pausadas</option>
<option value="Finalizada">Finalizadas</option>
</select>
</div>
</div>
</div>
<!-- Modal de Rango de Fechas -->
<div id="modalFechas" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm" onclick="cerrarModalFechas(event)">
<div class="relative bg-[#1c1f27] rounded-xl border border-[#282d39] shadow-2xl w-full max-w-md mx-4" onclick="event.stopPropagation()">
<!-- Header -->
<div class="flex items-center justify-between p-4 border-b border-[#282d39]">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-primary text-xl">calendar_today</span>
<h3 class="text-white text-base font-semibold">Rango de Fechas</h3>
</div>
<div class="relative">
<select id="tipoFiltro" class="bg-[#282d39] border border-[#3b4354] rounded-lg px-3 py-1.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-primary appearance-none pr-8 cursor-pointer">
<option value="Todos">Tipo: Todos</option>
<option value="Ingresos">Tipo: Ingresos</option>
<option value="Boletos">Tipo: Boletos</option>
<option value="Usuarios">Tipo: Usuarios</option>
</select>
<span class="absolute right-2 top-1/2 -translate-y-1/2 material-symbols-outlined text-text-secondary text-sm pointer-events-none">arrow_drop_down</span>
</div>
</div>
<!-- Contenido -->
<div class="p-4 space-y-4">
<!-- Fecha Inicio -->
<div>
<label class="block text-sm font-medium text-text-secondary mb-2">Fecha Inicio</label>
<div class="relative">
<input type="date" id="fechaInicio" class="w-full bg-[#282d39] border border-[#3b4354] rounded-lg px-3 py-2.5 pr-10 text-white text-sm focus:ring-2 focus:ring-primary focus:border-primary [&::-webkit-calendar-picker-indicator]:opacity-0 [&::-webkit-calendar-picker-indicator]:absolute [&::-webkit-calendar-picker-indicator]:right-0 [&::-webkit-calendar-picker-indicator]:w-full [&::-webkit-calendar-picker-indicator]:h-full [&::-webkit-calendar-picker-indicator]:cursor-pointer"/>
<button type="button" onclick="document.getElementById('fechaInicio').showPicker()" class="absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-text-secondary hover:text-white cursor-pointer text-lg pointer-events-auto">calendar_today</button>
</div>
</div>
<!-- Fecha Fin -->
<div>
<label class="block text-sm font-medium text-text-secondary mb-2">Fecha Fin</label>
<div class="relative">
<input type="date" id="fechaFin" class="w-full bg-[#282d39] border border-[#3b4354] rounded-lg px-3 py-2.5 pr-10 text-white text-sm focus:ring-2 focus:ring-primary focus:border-primary [&::-webkit-calendar-picker-indicator]:opacity-0 [&::-webkit-calendar-picker-indicator]:absolute [&::-webkit-calendar-picker-indicator]:right-0 [&::-webkit-calendar-picker-indicator]:w-full [&::-webkit-calendar-picker-indicator]:h-full [&::-webkit-calendar-picker-indicator]:cursor-pointer"/>
<button type="button" onclick="document.getElementById('fechaFin').showPicker()" class="absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-text-secondary hover:text-white cursor-pointer text-lg pointer-events-auto">calendar_today</button>
</div>
</div>
</div>
<!-- Botones -->
<div class="flex gap-3 p-4 border-t border-[#282d39]">
<button type="button" onclick="aplicarFiltroFechas()" class="flex-1 bg-primary text-white rounded-lg px-4 py-2.5 font-medium hover:bg-primary/90 transition-colors text-sm shadow-lg shadow-primary/20">
Aplicar
</button>
<button type="button" onclick="limpiarFiltroFechas()" class="flex-1 bg-[#282d39] text-text-secondary rounded-lg px-4 py-2.5 font-medium hover:bg-[#3b4354] hover:text-white transition-colors text-sm">
Limpiar
</button>
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
<h3 class="text-white text-2xl font-bold tracking-tight mb-2">$<?php echo number_format($kpis['ingresos_totales'], 2, '.', ','); ?></h3>
<div class="flex items-center gap-1.5">
<?php 
$tendencia_ingresos_class = $kpis['tendencia_ingresos'] >= 0 ? 'success' : 'danger';
$tendencia_ingresos_icon = $kpis['tendencia_ingresos'] >= 0 ? 'trending_up' : 'trending_down';
$tendencia_ingresos_sign = $kpis['tendencia_ingresos'] >= 0 ? '+' : '';
$tendencia_ingresos_bg = $kpis['tendencia_ingresos'] >= 0 ? 'bg-success/10' : 'bg-danger/10';
$tendencia_ingresos_text = $kpis['tendencia_ingresos'] >= 0 ? 'text-success' : 'text-danger';
?>
<div class="flex items-center justify-center <?php echo $tendencia_ingresos_bg; ?> rounded px-1.5 py-0.5">
<span class="material-symbols-outlined <?php echo $tendencia_ingresos_text; ?> text-xs mr-0.5"><?php echo $tendencia_ingresos_icon; ?></span>
<span class="<?php echo $tendencia_ingresos_text; ?> text-xs font-bold"><?php echo $tendencia_ingresos_sign . number_format($kpis['tendencia_ingresos'], 1); ?>%</span>
</div>
<span class="text-text-secondary text-xs">vs mes anterior</span>
</div>
</div>
<!-- Tickets -->
<div class="bg-[#1c1f27] rounded-xl p-5 border border-[#282d39] hover:border-[#3b4354] transition-colors relative overflow-hidden">
<div class="absolute top-0 right-0 p-4 opacity-5">
<span class="material-symbols-outlined text-6xl text-white">confirmation_number</span>
</div>
<p class="text-text-secondary text-sm font-medium mb-1">Boletos Vendidos</p>
<h3 class="text-white text-2xl font-bold tracking-tight mb-2"><?php echo number_format($kpis['boletos_vendidos'], 0, '.', ','); ?></h3>
<div class="flex items-center gap-1.5">
<?php 
$tendencia_boletos_class = $kpis['tendencia_boletos'] >= 0 ? 'success' : 'danger';
$tendencia_boletos_icon = $kpis['tendencia_boletos'] >= 0 ? 'trending_up' : 'trending_down';
$tendencia_boletos_sign = $kpis['tendencia_boletos'] >= 0 ? '+' : '';
$tendencia_boletos_bg = $kpis['tendencia_boletos'] >= 0 ? 'bg-success/10' : 'bg-danger/10';
$tendencia_boletos_text = $kpis['tendencia_boletos'] >= 0 ? 'text-success' : 'text-danger';
?>
<div class="flex items-center justify-center <?php echo $tendencia_boletos_bg; ?> rounded px-1.5 py-0.5">
<span class="material-symbols-outlined <?php echo $tendencia_boletos_text; ?> text-xs mr-0.5"><?php echo $tendencia_boletos_icon; ?></span>
<span class="<?php echo $tendencia_boletos_text; ?> text-xs font-bold"><?php echo $tendencia_boletos_sign . number_format($kpis['tendencia_boletos'], 1); ?>%</span>
</div>
<span class="text-text-secondary text-xs">vs mes anterior</span>
</div>
</div>
<!-- Active Users -->
<div class="bg-[#1c1f27] rounded-xl p-5 border border-[#282d39] hover:border-[#3b4354] transition-colors relative overflow-hidden">
<div class="absolute top-0 right-0 p-4 opacity-5">
<span class="material-symbols-outlined text-6xl text-white">group</span>
</div>
<p class="text-text-secondary text-sm font-medium mb-1">Usuarios Activos</p>
<h3 class="text-white text-2xl font-bold tracking-tight mb-2"><?php echo number_format($kpis['usuarios_activos'], 0, '.', ','); ?></h3>
<div class="flex items-center gap-1.5">
<div class="flex items-center justify-center bg-success/10 rounded px-1.5 py-0.5">
<span class="material-symbols-outlined text-success text-xs mr-0.5">group</span>
<span class="text-success text-xs font-bold">Activos</span>
</div>
<span class="text-text-secondary text-xs">en la plataforma</span>
</div>
</div>
<!-- Conversion -->
<div class="bg-[#1c1f27] rounded-xl p-5 border border-[#282d39] hover:border-[#3b4354] transition-colors relative overflow-hidden">
<div class="absolute top-0 right-0 p-4 opacity-5">
<span class="material-symbols-outlined text-6xl text-white">percent</span>
</div>
<p class="text-text-secondary text-sm font-medium mb-1">Tasa de Conversión</p>
<h3 class="text-white text-2xl font-bold tracking-tight mb-2"><?php echo number_format($kpis['tasa_conversion'], 1); ?>%</h3>
<div class="flex items-center gap-1.5">
<div class="flex items-center justify-center bg-primary/10 rounded px-1.5 py-0.5">
<span class="material-symbols-outlined text-primary text-xs mr-0.5">percent</span>
<span class="text-primary text-xs font-bold">Boletos</span>
</div>
<span class="text-text-secondary text-xs">vendidos / total</span>
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
<div class="relative h-64 w-full mt-auto">
<canvas id="chartIngresos"></canvas>
</div>
</div>
<!-- Secondary Chart: Donut (Ticket Status) -->
<div class="bg-[#1c1f27] rounded-xl border border-[#282d39] p-6 flex flex-col">
<h3 class="text-white text-lg font-bold mb-1">Estado de Boletos</h3>
<p class="text-text-secondary text-sm mb-6">Distribución actual del inventario</p>
<div class="flex-1 flex flex-col items-center justify-center relative">
<canvas id="chartBoletos" style="max-width: 200px; max-height: 200px;"></canvas>
</div>
<div class="mt-6 space-y-3">
<?php
$porcentaje_vendidos = $estado_boletos['total'] > 0 ? round(($estado_boletos['vendidos'] / $estado_boletos['total']) * 100, 1) : 0;
$porcentaje_disponibles = $estado_boletos['total'] > 0 ? round(($estado_boletos['disponibles'] / $estado_boletos['total']) * 100, 1) : 0;
?>
<div class="flex items-center justify-between">
<div class="flex items-center gap-2">
<span class="size-3 rounded-full bg-primary"></span>
<span class="text-text-secondary text-sm">Vendidos (<?php echo $porcentaje_vendidos; ?>%)</span>
</div>
<span class="text-white text-sm font-bold"><?php echo number_format($estado_boletos['vendidos'], 0, '.', ','); ?></span>
</div>
<div class="flex items-center justify-between">
<div class="flex items-center gap-2">
<span class="size-3 rounded-full bg-[#282d39]"></span>
<span class="text-text-secondary text-sm">Disponibles (<?php echo $porcentaje_disponibles; ?>%)</span>
</div>
<span class="text-white text-sm font-bold"><?php echo number_format($estado_boletos['disponibles'], 0, '.', ','); ?></span>
</div>
</div>
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
<div class="relative h-64 w-full">
<canvas id="chartVentasSorteos"></canvas>
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
<?php if (empty($campanas)): ?>
<tr>
<td colspan="4" class="py-4 px-6 text-center text-text-secondary text-sm">No hay campañas registradas</td>
</tr>
<?php else: ?>
<?php foreach ($campanas as $campana): ?>
<?php
$estado_class = [
    'Activa' => 'bg-success/10 text-success border-success/20',
    'Pausada' => 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20',
    'Finalizada' => 'bg-[#282d39] text-text-secondary border-[#3b4354]'
];
$estado_badge = $estado_class[$campana['estado']] ?? $estado_class['Finalizada'];
$roi_class = $campana['roi'] >= 0 ? 'text-success' : 'text-danger';
$roi_sign = $campana['roi'] >= 0 ? '+' : '';
$icon_map = [
    'Facebook' => 'thumb_up',
    'Instagram' => 'camera_alt',
    'Email' => 'mail',
    'Twitter' => 'chat',
    'Google' => 'ads_click'
];
$icon = 'campaign';
foreach ($icon_map as $key => $val) {
    if (stripos($campana['red_social'], $key) !== false) {
        $icon = $val;
        break;
    }
}
?>
<tr class="hover:bg-[#282d39]/50 transition-colors">
<td class="py-4 px-6">
<div class="flex items-center gap-3">
<div class="bg-[#282d39] p-1.5 rounded text-white">
<span class="material-symbols-outlined text-sm"><?php echo $icon; ?></span>
</div>
<span class="text-white text-sm font-medium"><?php echo htmlspecialchars($campana['nombre']); ?></span>
</div>
</td>
<td class="py-4 px-6">
<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?php echo $estado_badge; ?> border"><?php echo $campana['estado']; ?></span>
</td>
<td class="py-4 px-6 text-text-secondary text-sm text-right">$<?php echo number_format($campana['inversion'], 2, '.', ','); ?></td>
<td class="py-4 px-6 <?php echo $roi_class; ?> text-sm font-bold text-right"><?php echo $roi_sign . number_format($campana['roi'], 1); ?>%</td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>
</div>
</main>
</div>
</div>
</div>
<script>
// Datos desde PHP
const datosIngresos = <?php echo json_encode($tendencia_ingresos); ?>;
const datosBoletos = <?php echo json_encode($estado_boletos); ?>;
const datosVentasSorteos = <?php echo json_encode($ventas_sorteos); ?>;

// Configuración de Chart.js para modo oscuro
Chart.defaults.color = '#9da6b9';
Chart.defaults.borderColor = '#3b4354';
Chart.defaults.backgroundColor = '#1c1f27';

// Gráfico de línea - Tendencia de Ingresos
const ctxIngresos = document.getElementById('chartIngresos').getContext('2d');
const chartIngresos = new Chart(ctxIngresos, {
    type: 'line',
    data: {
        labels: datosIngresos.map(d => d.mes),
        datasets: [{
            label: 'Ingresos',
            data: datosIngresos.map(d => d.total),
            borderColor: '#2463eb',
            backgroundColor: 'rgba(36, 99, 235, 0.1)',
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointBackgroundColor: '#2463eb',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: '#1c1f27',
                titleColor: '#ffffff',
                bodyColor: '#9da6b9',
                borderColor: '#3b4354',
                borderWidth: 1,
                callbacks: {
                    label: function(context) {
                        return '$' + context.parsed.y.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(59, 67, 84, 0.3)'
                },
                ticks: {
                    callback: function(value) {
                        return '$' + (value / 1000).toFixed(0) + 'k';
                    }
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Gráfico de dona - Estado de Boletos
const ctxBoletos = document.getElementById('chartBoletos').getContext('2d');
const chartBoletos = new Chart(ctxBoletos, {
    type: 'doughnut',
    data: {
        labels: ['Vendidos', 'Disponibles'],
        datasets: [{
            data: [datosBoletos.vendidos, datosBoletos.disponibles],
            backgroundColor: ['#2463eb', '#282d39'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: '#1c1f27',
                titleColor: '#ffffff',
                bodyColor: '#9da6b9',
                borderColor: '#3b4354',
                borderWidth: 1
            }
        }
    }
});

// Gráfico de barras - Ventas por Sorteo
const ctxVentasSorteos = document.getElementById('chartVentasSorteos').getContext('2d');
const chartVentasSorteos = new Chart(ctxVentasSorteos, {
    type: 'bar',
    data: {
        labels: datosVentasSorteos.map(d => d.titulo.length > 15 ? d.titulo.substring(0, 15) + '...' : d.titulo),
        datasets: [{
            label: 'Porcentaje Vendido',
            data: datosVentasSorteos.map(d => d.porcentaje),
            backgroundColor: datosVentasSorteos.map((d, i) => {
                const colors = ['#2463eb', 'rgba(36, 99, 235, 0.6)', 'rgba(36, 99, 235, 0.8)', 'rgba(36, 99, 235, 0.4)', 'rgba(36, 99, 235, 1)'];
                return colors[i % colors.length];
            }),
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: '#1c1f27',
                titleColor: '#ffffff',
                bodyColor: '#9da6b9',
                borderColor: '#3b4354',
                borderWidth: 1,
                callbacks: {
                    label: function(context) {
                        const index = context.dataIndex;
                        const data = datosVentasSorteos[index];
                        return [
                            'Vendidos: ' + data.vendidos.toLocaleString(),
                            'Total: ' + data.total.toLocaleString(),
                            'Porcentaje: ' + data.porcentaje + '%'
                        ];
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                grid: {
                    color: 'rgba(59, 67, 84, 0.3)'
                },
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Función de navegación
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

function viewSalesDetails() {
    // Implementar navegación a detalles de ventas si es necesario
    console.log('Ver detalles de ventas');
}

// ========== FUNCIONES DEL MODAL DE FECHAS ==========

/**
 * Abre el modal de selección de rango de fechas
 */
function abrirModalFechas() {
    const modal = document.getElementById('modalFechas');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Cargar fechas actuales si existen
        const fechaDisplay = document.getElementById('fechaRangeDisplay').textContent.trim();
        if (fechaDisplay && fechaDisplay.includes(' - ')) {
            const fechas = fechaDisplay.split(' - ');
            if (fechas.length === 2) {
                // Convertir formato "d M, Y" a formato de fecha para input type="date"
                const fechaInicio = convertirFechaParaInput(fechas[0].trim());
                const fechaFin = convertirFechaParaInput(fechas[1].trim());
                
                if (fechaInicio) document.getElementById('fechaInicio').value = fechaInicio;
                if (fechaFin) document.getElementById('fechaFin').value = fechaFin;
            }
        }
    }
}

/**
 * Cierra el modal de fechas
 */
function cerrarModalFechas(event) {
    if (event && event.target.id === 'modalFechas') {
        const modal = document.getElementById('modalFechas');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }
}

/**
 * Aplica el filtro de fechas seleccionado
 */
function aplicarFiltroFechas() {
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;
    const tipoFiltro = document.getElementById('tipoFiltro').value;
    
    if (!fechaInicio || !fechaFin) {
        alert('Por favor selecciona ambas fechas');
        return;
    }
    
    if (new Date(fechaInicio) > new Date(fechaFin)) {
        alert('La fecha de inicio no puede ser posterior a la fecha de fin');
        return;
    }
    
    // Formatear fechas para mostrar
    const fechaInicioFormateada = formatearFecha(fechaInicio);
    const fechaFinFormateada = formatearFecha(fechaFin);
    
    // Actualizar el texto del botón
    document.getElementById('fechaRangeDisplay').textContent = fechaInicioFormateada + ' - ' + fechaFinFormateada;
    
    // Cerrar el modal
    const modal = document.getElementById('modalFechas');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    
    // Aquí puedes agregar la lógica para filtrar los datos
    console.log('Filtrar por:', {
        fechaInicio: fechaInicio,
        fechaFin: fechaFin,
        tipo: tipoFiltro
    });
    
    // TODO: Implementar recarga de datos con filtros
    // recargarDatosConFiltros(fechaInicio, fechaFin, tipoFiltro);
}

/**
 * Limpia el filtro de fechas
 */
function limpiarFiltroFechas() {
    document.getElementById('fechaInicio').value = '';
    document.getElementById('fechaFin').value = '';
    document.getElementById('tipoFiltro').value = 'Todos';
    
    // Restaurar fecha por defecto (último mes)
    const hoy = new Date();
    const haceUnMes = new Date();
    haceUnMes.setMonth(haceUnMes.getMonth() - 1);
    
    const fechaInicioFormateada = formatearFecha(haceUnMes.toISOString().split('T')[0]);
    const fechaFinFormateada = formatearFecha(hoy.toISOString().split('T')[0]);
    
    document.getElementById('fechaRangeDisplay').textContent = fechaInicioFormateada + ' - ' + fechaFinFormateada;
    
    // Cerrar el modal
    const modal = document.getElementById('modalFechas');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    
    // TODO: Recargar datos sin filtros
    // recargarDatosConFiltros(null, null, 'Todos');
}

/**
 * Convierte fecha del formato "d M, Y" (ej: "01 Ene, 2023") a formato "YYYY-MM-DD"
 */
function convertirFechaParaInput(fechaTexto) {
    if (!fechaTexto) return '';
    
    const meses = {
        'Ene': '01', 'Feb': '02', 'Mar': '03', 'Abr': '04',
        'May': '05', 'Jun': '06', 'Jul': '07', 'Ago': '08',
        'Sep': '09', 'Oct': '10', 'Nov': '11', 'Dic': '12'
    };
    
    const partes = fechaTexto.split(' ');
    if (partes.length === 3) {
        const dia = partes[0].padStart(2, '0');
        const mes = meses[partes[1].replace(',', '')] || '01';
        const año = partes[2];
        return `${año}-${mes}-${dia}`;
    }
    
    return '';
}

/**
 * Formatea fecha de "YYYY-MM-DD" a "d M, Y" (ej: "01 Ene, 2023")
 */
function formatearFecha(fecha) {
    if (!fecha) return '';
    
    const fechaObj = new Date(fecha + 'T00:00:00');
    const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    
    const dia = fechaObj.getDate();
    const mes = meses[fechaObj.getMonth()];
    const año = fechaObj.getFullYear();
    
    return `${dia} ${mes}, ${año}`;
}

// Cerrar modal con ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('modalFechas');
        if (modal && !modal.classList.contains('hidden')) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }
});
</script>
</body>
</html>
