<!DOCTYPE html>
<?php
// Conexión a la base de datos
require_once 'config.php';
$conn = getDBConnection();

// Si es una petición AJAX para obtener datos de auditoría
if (isset($_GET['action']) && $_GET['action'] === 'get_audit_data') {
    // Limpiar cualquier output previo para asegurar JSON puro
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    
    // Obtener parámetros de filtro
    $fechaDesde = $_GET['fechaDesde'] ?? '';
    $fechaHasta = $_GET['fechaHasta'] ?? '';
    $tipo = $_GET['tipo'] ?? 'all';
    $estado = $_GET['estado'] ?? 'all';
    $alertsOnly = isset($_GET['alertsOnly']) && $_GET['alertsOnly'] === 'true';
    $search = $_GET['search'] ?? '';
    
    error_log("Auditoría: Iniciando consulta con filtros - tipo: $tipo, estado: $estado, alertsOnly: " . ($alertsOnly ? 'true' : 'false'));
    
    // Construir consulta - Orden: id_log, id_usuario, tipo_accion, accion, recurso, estado, es_alerta, ip_address, fecha_hora
    $query = "SELECT 
                a.id_log,
                a.id_usuario,
                a.tipo_accion,
                a.recurso,
                a.estado,
                a.es_alerta,
                a.ip_address,
                DATE_FORMAT(a.fecha_hora, '%Y-%m-%d') as fecha,
                DATE_FORMAT(a.fecha_hora, '%H:%i:%s') as hora,
                a.fecha_hora,
                CASE 
                    WHEN a.id_usuario IS NULL THEN 'Sistema'
                    WHEN u.primer_nombre IS NULL THEN 'Usuario Desconocido'
                    ELSE CONCAT(u.primer_nombre, ' ', COALESCE(u.apellido_paterno, ''))
                END as actor_nombre,
                COALESCE(r.nombre_rol, 'Sistema') as rol
              FROM auditoria_acciones a
              LEFT JOIN usuarios u ON a.id_usuario = u.id_usuario
              LEFT JOIN roles r ON u.id_rol = r.id_rol
              WHERE 1=1";
    
    $params = [];
    $types = '';
    
    if ($fechaDesde) {
        $query .= " AND DATE(a.fecha_hora) >= ?";
        $params[] = $fechaDesde;
        $types .= 's';
    }
    
    if ($fechaHasta) {
        $query .= " AND DATE(a.fecha_hora) <= ?";
        $params[] = $fechaHasta;
        $types .= 's';
    }
    
    if ($tipo !== 'all') {
        $query .= " AND a.tipo_accion = ?";
        $params[] = $tipo;
        $types .= 's';
    }
    
    if ($estado !== 'all') {
        $query .= " AND a.estado = ?";
        $params[] = $estado;
        $types .= 's';
    }
    
    if ($alertsOnly) {
        $query .= " AND a.es_alerta = 1";
    }
    
    if ($search) {
        $query .= " AND (a.tipo_accion LIKE ? OR a.recurso LIKE ? OR u.email LIKE ? OR CONCAT(u.primer_nombre, ' ', u.apellido_paterno) LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= 'ssss';
    }
    
    $query .= " ORDER BY a.fecha_hora DESC LIMIT 1000";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Error preparando consulta de auditoría: " . $conn->error);
        echo json_encode(['success' => false, 'error' => 'Error al preparar consulta', 'data' => []]);
        exit;
    }
    
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        error_log("Error ejecutando consulta de auditoría: " . $stmt->error);
        $stmt->close();
        echo json_encode(['success' => false, 'error' => 'Error al consultar auditoría: ' . $stmt->error, 'data' => []]);
        exit;
    }
    
    $result = $stmt->get_result();
    
    if (!$result) {
        error_log("Error obteniendo resultados de auditoría: " . $conn->error);
        $stmt->close();
        echo json_encode(['success' => false, 'error' => 'Error al obtener resultados', 'data' => []]);
        exit;
    }
    
    $records = [];
    $rowCount = 0;
    while ($row = $result->fetch_assoc()) {
        $rowCount++;
        
        // Manejar es_alerta - puede ser 0, 1, o string "0 :: 1" (problema de phpMyAdmin)
        $es_alerta = $row['es_alerta'];
        if (is_string($es_alerta) && strpos($es_alerta, '::') !== false) {
            // Si viene como "0 :: 1", tomar el primer valor
            $es_alerta = (int)trim(explode('::', $es_alerta)[0]);
        }
        $es_alerta = (bool)(int)$es_alerta;
        
        // Manejar ip_address - puede venir como "0 :: 1" (problema de phpMyAdmin)
        $ip_address = $row['ip_address'];
        if (is_string($ip_address) && strpos($ip_address, '::') !== false) {
            // Si viene como "0 :: 1", usar valor por defecto
            $ip_address = '127.0.0.1';
        }
        if (empty($ip_address) || $ip_address === null) {
            $ip_address = '127.0.0.1';
        }
        
        // Validar que los campos requeridos existan
        if (!isset($row['id_log']) || !isset($row['tipo_accion']) || !isset($row['recurso'])) {
            error_log("Advertencia: Fila $rowCount tiene campos faltantes. id_log: " . ($row['id_log'] ?? 'NULL') . ", tipo_accion: " . ($row['tipo_accion'] ?? 'NULL'));
            continue; // Saltar esta fila si faltan campos críticos
        }
        
        $records[] = [
            'id_log' => (int)$row['id_log'],
            'id_usuario' => $row['id_usuario'] !== null ? (int)$row['id_usuario'] : null,
            'tipo_accion' => $row['tipo_accion'],
            'recurso' => $row['recurso'],
            'estado' => $row['estado'] ?? 'success',
            'es_alerta' => $es_alerta,
            'ip_address' => $ip_address,
            'fecha_hora' => $row['fecha_hora'],
            'fecha' => $row['fecha'] ?? null,
            'hora' => $row['hora'] ?? null,
            'actor' => $row['actor_nombre'] ?? 'Sistema',
            'rol' => $row['rol'] ?? 'Sistema'
        ];
    }
    
    $stmt->close();
    
    // Log para debugging
    error_log("Auditoría: Se encontraron " . count($records) . " registros válidos de $rowCount filas procesadas");
    if (count($records) > 0) {
        error_log("Auditoría: Primer registro - id_log: " . $records[0]['id_log'] . ", tipo_accion: " . $records[0]['tipo_accion']);
    }
    
    $response = ['success' => true, 'data' => $records, 'total' => count($records)];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}
?>


<html class="dark" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Auditoría de Acciones Admin</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
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
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-spin-fast {
            animation: spin 0.5s linear infinite;
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
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary/10 text-primary font-medium" href="AuditoriaAccionesAdmin.php">
<span class="material-symbols-outlined">settings</span>
                    Auditoría
                </a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="InformesEstadisticasAdmin.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">analytics</span>
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
<!-- Mobile menu trigger (hidden on desktop) -->
<button class="lg:hidden text-gray-500">
<span class="material-symbols-outlined">menu</span>
</button>
<h1 class="text-xl font-bold text-slate-900 dark:text-white hidden sm:block">Auditoría de Acciones</h1>
</div>
<div class="flex items-center gap-4">
<div class="relative hidden md:block w-64">
<span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
<span class="material-symbols-outlined text-[20px]">search</span>
</span>
<input id="mainSearchInput" class="w-full bg-gray-100 dark:bg-[#1e2433] border-none rounded-lg py-2 pl-10 pr-4 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary placeholder-gray-500" placeholder="Buscar sorteo, usuario..." type="text"/>
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
<div class="flex flex-wrap items-center gap-2 px-4 py-2">
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
<span class="text-white text-sm font-medium leading-normal">Auditoría de Acciones</span>
</div>
<!-- Page Heading -->
<div class="flex flex-wrap justify-between items-end gap-3 px-4 py-6">
<div class="flex min-w-72 flex-col gap-2">
<h1 class="text-[#111318] dark:text-white text-3xl md:text-4xl font-black leading-tight tracking-[-0.033em]">Registro de Auditoría</h1>
<p class="text-[#637588] dark:text-[#9da6b9] text-base font-normal leading-normal">Supervisión y trazabilidad de eventos del sistema en tiempo real.</p>
</div>
<div class="flex gap-3">
<button id="refreshButton" onclick="refreshData()" class="flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-[#f0f2f4] dark:bg-[#282d39] text-[#111318] dark:text-white text-sm font-bold hover:bg-[#e5e7eb] dark:hover:bg-[#333a4a] transition-colors border border-transparent dark:border-[#3e4556]">
<span class="material-symbols-outlined text-[20px]">refresh</span>
<span class="hidden sm:inline">Actualizar</span>
</button>
<button id="exportCsvButton" onclick="exportToCsv()" class="flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold shadow-lg shadow-primary/30 hover:bg-blue-600 transition-all">
<span class="material-symbols-outlined text-[20px]">download</span>
<span class="truncate">Exportar CSV</span>
</button>
</div>
</div>
<!-- Filters & Search -->
<div class="px-4 py-2">
<div class="flex flex-col lg:flex-row gap-4 lg:items-center justify-between rounded-xl bg-white dark:bg-[#1e293b] p-4 shadow-sm border border-[#e5e7eb] dark:border-[#282d39]">
<!-- Search -->
<div class="flex-1 min-w-[300px]">
<label class="flex w-full items-center gap-2 rounded-lg border border-[#e5e7eb] dark:border-[#3e4556] bg-white dark:bg-[#111621] px-3 h-10 focus-within:border-primary focus-within:ring-1 focus-within:ring-primary transition-all">
<span class="material-symbols-outlined text-[#9da6b9]">search</span>
<input id="filterSearchInput" class="w-full bg-transparent text-sm text-[#111318] dark:text-white placeholder:text-[#9da6b9] focus:outline-none" placeholder="Buscar por ID de sorteo, email o recurso..."/>
</label>
</div>
<!-- Filter Chips -->
<div class="flex flex-wrap gap-2 items-center">
<span class="text-xs font-semibold text-[#637588] dark:text-[#9da6b9] uppercase tracking-wider mr-1">Filtros:</span>
<!-- Filtro de Fecha -->
<div class="relative group">
<button id="dateFilterButton" onclick="toggleDateFilter()" class="flex h-8 items-center gap-x-2 rounded-md bg-[#f0f2f4] dark:bg-[#282d39] px-3 hover:bg-[#e5e7eb] dark:hover:bg-[#333a4a] transition-colors border border-transparent dark:border-[#3e4556]">
<span class="text-[#111318] dark:text-white text-xs font-medium">Rango de Fechas</span>
<span class="material-symbols-outlined text-[#111318] dark:text-white text-[16px]">calendar_today</span>
</button>
<div id="dateFilterDropdown" class="hidden absolute z-50 mt-2 w-80 bg-white dark:bg-[#1e293b] rounded-lg shadow-xl border border-[#e5e7eb] dark:border-[#282d39] p-4">
<div class="space-y-3">
<div>
<label class="block text-xs font-semibold text-[#637588] dark:text-[#9da6b9] mb-1">Fecha Desde</label>
<input type="date" id="fechaDesde" class="w-full bg-white dark:bg-[#111621] border border-[#e5e7eb] dark:border-[#3e4556] rounded-md px-3 py-2 text-sm text-[#111318] dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" onchange="applyDateFilter()"/>
</div>
<div>
<label class="block text-xs font-semibold text-[#637588] dark:text-[#9da6b9] mb-1">Fecha Hasta</label>
<input type="date" id="fechaHasta" class="w-full bg-white dark:bg-[#111621] border border-[#e5e7eb] dark:border-[#3e4556] rounded-md px-3 py-2 text-sm text-[#111318] dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" onchange="applyDateFilter()"/>
</div>
<div class="flex gap-2 pt-2">
<button onclick="clearDateFilter()" class="flex-1 px-3 py-1.5 text-xs font-medium text-[#637588] dark:text-[#9da6b9] hover:text-[#111318] dark:hover:text-white bg-[#f0f2f4] dark:bg-[#282d39] rounded-md hover:bg-[#e5e7eb] dark:hover:bg-[#333a4a] transition-colors">Limpiar</button>
<button onclick="document.getElementById('dateFilterDropdown').classList.add('hidden')" class="flex-1 px-3 py-1.5 text-xs font-medium text-white bg-primary rounded-md hover:bg-blue-600 transition-colors">Aplicar</button>
</div>
</div>
</div>
</div>
<!-- Filtro de Tipo de Acción -->
<div class="relative group">
<button id="typeFilterButton" onclick="toggleTypeFilter()" class="flex h-8 items-center gap-x-2 rounded-md bg-[#f0f2f4] dark:bg-[#282d39] px-3 hover:bg-[#e5e7eb] dark:hover:bg-[#333a4a] transition-colors border border-transparent dark:border-[#3e4556]">
<span class="text-[#111318] dark:text-white text-xs font-medium">Tipo: Todos</span>
<span class="material-symbols-outlined text-[#111318] dark:text-white text-[16px]">expand_more</span>
</button>
<div id="typeFilterDropdown" class="hidden absolute z-50 mt-2 w-56 bg-white dark:bg-[#1e293b] rounded-lg shadow-xl border border-[#e5e7eb] dark:border-[#282d39] py-2">
<a onclick="selectTypeFilter('all')" class="block px-4 py-2 text-sm text-[#111318] dark:text-white hover:bg-[#f0f2f4] dark:hover:bg-[#282d39] cursor-pointer">Todos</a>
<a onclick="selectTypeFilter('creacion_usuario')" class="block px-4 py-2 text-sm text-[#111318] dark:text-white hover:bg-[#f0f2f4] dark:hover:bg-[#282d39] cursor-pointer">Creación de Usuario</a>
<a onclick="selectTypeFilter('login_exitoso')" class="block px-4 py-2 text-sm text-[#111318] dark:text-white hover:bg-[#f0f2f4] dark:hover:bg-[#282d39] cursor-pointer">Login Exitoso</a>
<a onclick="selectTypeFilter('login_fallido')" class="block px-4 py-2 text-sm text-[#111318] dark:text-white hover:bg-[#f0f2f4] dark:hover:bg-[#282d39] cursor-pointer">Login Fallido</a>
<a onclick="selectTypeFilter('edicion_usuario')" class="block px-4 py-2 text-sm text-[#111318] dark:text-white hover:bg-[#f0f2f4] dark:hover:bg-[#282d39] cursor-pointer">Edición de Usuario</a>
<a onclick="selectTypeFilter('creacion_sorteo')" class="block px-4 py-2 text-sm text-[#111318] dark:text-white hover:bg-[#f0f2f4] dark:hover:bg-[#282d39] cursor-pointer">Creación de Sorteo</a>
<a onclick="selectTypeFilter('generacion_ganador')" class="block px-4 py-2 text-sm text-[#111318] dark:text-white hover:bg-[#f0f2f4] dark:hover:bg-[#282d39] cursor-pointer">Generación de Ganador</a>
<a onclick="selectTypeFilter('validacion_pago')" class="block px-4 py-2 text-sm text-[#111318] dark:text-white hover:bg-[#f0f2f4] dark:hover:bg-[#282d39] cursor-pointer">Validación de Pago</a>
</div>
</div>
<!-- Filtro de Estado -->
<div class="relative group">
<button id="statusFilterButton" onclick="toggleStatusFilter()" class="flex h-8 items-center gap-x-2 rounded-md bg-[#f0f2f4] dark:bg-[#282d39] px-3 hover:bg-[#e5e7eb] dark:hover:bg-[#333a4a] transition-colors border border-transparent dark:border-[#3e4556]">
<span class="text-[#111318] dark:text-white text-xs font-medium">Estado: Todos</span>
<span class="material-symbols-outlined text-[#111318] dark:text-white text-[16px]">filter_list</span>
</button>
<div id="statusFilterDropdown" class="hidden absolute z-50 mt-2 w-40 bg-white dark:bg-[#1e293b] rounded-lg shadow-xl border border-[#e5e7eb] dark:border-[#282d39] py-2">
<a onclick="selectStatusFilter('all')" class="block px-4 py-2 text-sm text-[#111318] dark:text-white hover:bg-[#f0f2f4] dark:hover:bg-[#282d39] cursor-pointer">Todos</a>
<a onclick="selectStatusFilter('success')" class="block px-4 py-2 text-sm text-[#111318] dark:text-white hover:bg-[#f0f2f4] dark:hover:bg-[#282d39] cursor-pointer">Exitoso</a>
<a onclick="selectStatusFilter('error')" class="block px-4 py-2 text-sm text-[#111318] dark:text-white hover:bg-[#f0f2f4] dark:hover:bg-[#282d39] cursor-pointer">Fallido</a>
</div>
</div>
<div class="h-6 w-px bg-[#e5e7eb] dark:bg-[#3e4556] mx-1"></div>
<button id="alertsOnlyButton" onclick="toggleAlertsOnly()" class="flex h-8 items-center gap-x-1 rounded-md bg-red-500/10 dark:bg-red-500/20 px-3 hover:bg-red-500/20 dark:hover:bg-red-500/30 transition-colors border border-red-500/20 dark:border-red-500/30">
<span class="material-symbols-outlined text-red-600 dark:text-red-400 text-[16px]">warning</span>
<span class="text-red-600 dark:text-red-400 text-xs font-bold">Solo Alertas</span>
</button>
</div>
</div>
</div>
<!-- Data Table -->
<div class="px-4 py-4">
<div class="overflow-hidden rounded-xl border border-[#e5e7eb] dark:border-[#282d39] bg-white dark:bg-[#1e293b] shadow-sm">
<div class="overflow-x-auto">
<table class="w-full text-left text-sm text-[#637588] dark:text-[#9da6b9]">
<thead class="bg-[#f8fafc] dark:bg-[#111621] text-xs uppercase font-semibold text-[#111318] dark:text-white border-b border-[#e5e7eb] dark:border-[#282d39]">
<tr>
<th class="px-6 py-4 whitespace-nowrap w-48" scope="col">Fecha y Hora</th>
<th class="px-6 py-4 whitespace-nowrap w-56" scope="col">Actor</th>
<th class="px-6 py-4 whitespace-nowrap w-48" scope="col">Tipo de Acción</th>
<th class="px-6 py-4 whitespace-nowrap" scope="col">Recurso Afectado</th>
<th class="px-6 py-4 whitespace-nowrap w-32 text-center" scope="col">Estado</th>
<th class="px-6 py-4 whitespace-nowrap w-24 text-right" scope="col"></th>
</tr>
</thead>
<tbody id="auditTableBody" class="divide-y divide-[#e5e7eb] dark:divide-[#282d39]">
<!-- Los datos se cargan dinámicamente desde la base de datos -->
</tbody>
</table>
</div>
<!-- Pagination -->
<div class="flex items-center justify-between border-t border-[#e5e7eb] dark:border-[#282d39] bg-white dark:bg-[#1e293b] px-4 py-3 sm:px-6">
<div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
<div>
<p class="text-sm text-[#637588] dark:text-[#9da6b9]">
                                        Mostrando <span id="paginationInfo" class="font-medium text-[#111318] dark:text-white">1</span> a <span id="paginationEnd" class="font-medium text-[#111318] dark:text-white">5</span> de <span id="paginationTotal" class="font-medium text-[#111318] dark:text-white">128</span> resultados
                                    </p>
</div>
<div>
<nav aria-label="Pagination" class="isolate inline-flex -space-x-px rounded-md shadow-sm">
<a id="prevPage" onclick="changePage('prev')" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 dark:ring-[#3e4556] hover:bg-gray-50 dark:hover:bg-[#333a4a] focus:z-20 focus:outline-offset-0 cursor-pointer">
<span class="sr-only">Previous</span>
<span class="material-symbols-outlined text-[20px]">chevron_left</span>
</a>
<div id="paginationNumbers"></div>
<a id="nextPage" onclick="changePage('next')" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 dark:ring-[#3e4556] hover:bg-gray-50 dark:hover:bg-[#333a4a] focus:z-20 focus:outline-offset-0 cursor-pointer">
<span class="sr-only">Next</span>
<span class="material-symbols-outlined text-[20px]">chevron_right</span>
</a>
</nav>
</div>
</div>
</div>
</div>
</div>
</main>
</div>
<script>
// Datos de auditoría (se cargan desde la base de datos)
let auditRecords = [];

// Estado global
let currentPage = 1;
let recordsPerPage = 5;
let currentFilters = {
    search: '',
    fechaDesde: '',
    fechaHasta: '',
    type: 'all',
    status: 'all',
    alertsOnly: false
};

// Cargar datos de auditoría desde el servidor
async function loadAuditData() {
    try {
        const params = new URLSearchParams({
            action: 'get_audit_data',
            fechaDesde: currentFilters.fechaDesde || '',
            fechaHasta: currentFilters.fechaHasta || '',
            tipo: currentFilters.type || 'all',
            estado: currentFilters.status || 'all',
            alertsOnly: currentFilters.alertsOnly ? 'true' : 'false',
            search: currentFilters.search || ''
        });
        
        console.log('Cargando datos de auditoría con parámetros:', params.toString());
        
        const response = await fetch(`?${params.toString()}`);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error(`HTTP error! status: ${response.status}`, errorText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Respuesta no es JSON. Content-Type:', contentType);
            console.error('Contenido recibido:', text.substring(0, 500));
            throw new Error('La respuesta del servidor no es JSON válido');
        }
        
        const text = await response.text();
        console.log('Respuesta del servidor (texto, primeros 500 chars):', text.substring(0, 500));
        
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('Error parseando JSON:', e);
            console.error('Texto completo recibido:', text);
            throw new Error('Error al parsear respuesta del servidor como JSON');
        }
        
        console.log('Datos de auditoría recibidos (parseados):', result);
        
        if (!result || typeof result !== 'object') {
            console.error('Resultado no es un objeto válido:', result);
            throw new Error('La respuesta del servidor no es un objeto válido');
        }
        
        if (result.success === true) {
            // Validar que data sea un array
            if (!Array.isArray(result.data)) {
                console.error('result.data no es un array:', result.data);
                auditRecords = [];
            } else {
                auditRecords = result.data;
            }
            
            console.log(`Se cargaron ${auditRecords.length} registros de auditoría`);
            if (auditRecords.length > 0) {
                console.log('Primer registro completo:', auditRecords[0]);
                console.log('Campos del primer registro:', Object.keys(auditRecords[0]));
            } else {
                console.warn('No se recibieron registros aunque result.success es true');
            }
            renderFilteredData();
        } else {
            console.error('Error al cargar datos de auditoría. result.success es false:', result);
            auditRecords = [];
            renderFilteredData();
        }
    } catch (error) {
        console.error('Error al cargar datos de auditoría:', error);
        console.error('Stack trace:', error.stack);
        auditRecords = [];
        renderFilteredData();
    }
}

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
    loadAuditData();
});

function setupEventListeners() {
    const filterSearchInput = document.getElementById('filterSearchInput');
    const mainSearchInput = document.getElementById('mainSearchInput');
    
    if (filterSearchInput) {
        let searchTimeout;
        filterSearchInput.addEventListener('input', function(e) {
            currentFilters.search = e.target.value.trim();
            clearTimeout(searchTimeout);
            // Debounce: esperar 500ms después de que el usuario deje de escribir
            searchTimeout = setTimeout(() => {
                loadAuditData();
            }, 500);
        });
    }
    
    if (mainSearchInput) {
        let searchTimeout;
        mainSearchInput.addEventListener('input', function(e) {
            currentFilters.search = e.target.value.trim();
            clearTimeout(searchTimeout);
            // Debounce: esperar 500ms después de que el usuario deje de escribir
            searchTimeout = setTimeout(() => {
                loadAuditData();
            }, 500);
        });
    }
    
    // Cerrar dropdowns al hacer click fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.relative.group')) {
            document.querySelectorAll('[id$="Dropdown"]').forEach(dropdown => {
                dropdown.classList.add('hidden');
            });
        }
    });
}

function toggleDateFilter() {
    const dropdown = document.getElementById('dateFilterDropdown');
    document.querySelectorAll('[id$="Dropdown"]').forEach(d => {
        if (d !== dropdown) d.classList.add('hidden');
    });
    dropdown.classList.toggle('hidden');
}

function toggleTypeFilter() {
    const dropdown = document.getElementById('typeFilterDropdown');
    document.querySelectorAll('[id$="Dropdown"]').forEach(d => {
        if (d !== dropdown) d.classList.add('hidden');
    });
    dropdown.classList.toggle('hidden');
}

function toggleStatusFilter() {
    const dropdown = document.getElementById('statusFilterDropdown');
    document.querySelectorAll('[id$="Dropdown"]').forEach(d => {
        if (d !== dropdown) d.classList.add('hidden');
    });
    dropdown.classList.toggle('hidden');
}

function applyDateFilter() {
    const fechaDesde = document.getElementById('fechaDesde').value;
    const fechaHasta = document.getElementById('fechaHasta').value;
    
    currentFilters.fechaDesde = fechaDesde;
    currentFilters.fechaHasta = fechaHasta;
    
    // Actualizar texto del botón
    const button = document.getElementById('dateFilterButton');
    if (fechaDesde && fechaHasta) {
        const desde = new Date(fechaDesde).toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
        const hasta = new Date(fechaHasta).toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
        button.querySelector('span').textContent = `${desde} - ${hasta}`;
    } else if (fechaDesde || fechaHasta) {
        const fecha = fechaDesde || fechaHasta;
        const fechaFormateada = new Date(fecha).toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
        button.querySelector('span').textContent = fechaDesde ? `Desde ${fechaFormateada}` : `Hasta ${fechaFormateada}`;
    } else {
        button.querySelector('span').textContent = 'Rango de Fechas';
    }
    
    loadAuditData();
}

function clearDateFilter() {
    document.getElementById('fechaDesde').value = '';
    document.getElementById('fechaHasta').value = '';
    currentFilters.fechaDesde = '';
    currentFilters.fechaHasta = '';
    const button = document.getElementById('dateFilterButton');
    button.querySelector('span').textContent = 'Rango de Fechas';
    loadAuditData();
}

function selectTypeFilter(value) {
    currentFilters.type = value;
    const button = document.getElementById('typeFilterButton');
    const labels = { 
        'all': 'Todos', 
        'creacion_usuario': 'Creación de Usuario',
        'login_exitoso': 'Login Exitoso',
        'login_fallido': 'Login Fallido',
        'edicion_usuario': 'Edición de Usuario',
        'creacion_sorteo': 'Creación de Sorteo',
        'generacion_ganador': 'Generación de Ganador',
        'validacion_pago': 'Validación de Pago'
    };
    button.querySelector('span').textContent = `Tipo: ${labels[value] || 'Todos'}`;
    document.getElementById('typeFilterDropdown').classList.add('hidden');
    loadAuditData();
}

function selectStatusFilter(value) {
    currentFilters.status = value;
    const button = document.getElementById('statusFilterButton');
    const labels = { 'all': 'Todos', 'success': 'Exitoso', 'error': 'Fallido' };
    button.querySelector('span').textContent = `Estado: ${labels[value] || 'Todos'}`;
    document.getElementById('statusFilterDropdown').classList.add('hidden');
    loadAuditData();
}

function toggleAlertsOnly() {
    currentFilters.alertsOnly = !currentFilters.alertsOnly;
    const button = document.getElementById('alertsOnlyButton');
    if (currentFilters.alertsOnly) {
        button.classList.add('bg-red-500/20', 'dark:bg-red-500/30');
    } else {
        button.classList.remove('bg-red-500/20', 'dark:bg-red-500/30');
    }
    loadAuditData();
}

function applyFilters() {
    // Recargar datos desde el servidor con los filtros aplicados
    loadAuditData();
}

function renderFilteredData() {
    // Los datos ya vienen filtrados del servidor, solo renderizar
    currentPage = 1; // Resetear a la primera página
    renderTable(auditRecords);
    renderPagination(auditRecords.length);
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) {
            console.warn('Fecha inválida:', dateString);
            return dateString;
        }
        const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
    } catch (e) {
        console.error('Error formateando fecha:', dateString, e);
        return dateString;
    }
}

function getTipoAccionBadge(tipoAccion) {
    const badges = {
        'creacion_usuario': '<span class="inline-flex items-center rounded-md bg-purple-50 dark:bg-purple-900/30 px-2 py-1 text-xs font-medium text-purple-700 dark:text-purple-300 ring-1 ring-inset ring-purple-600/20 dark:ring-purple-400/20">Creación de Usuario</span>',
        'login_exitoso': '<span class="inline-flex items-center rounded-md bg-green-50 dark:bg-green-900/30 px-2 py-1 text-xs font-medium text-green-700 dark:text-green-300 ring-1 ring-inset ring-green-600/10 dark:ring-green-400/20">Login Exitoso</span>',
        'login_fallido': '<span class="inline-flex items-center rounded-md bg-red-50 dark:bg-red-900/30 px-2 py-1 text-xs font-medium text-red-700 dark:text-red-300 ring-1 ring-inset ring-red-600/10 dark:ring-red-400/20">Login Fallido</span>',
        'edicion_usuario': '<span class="inline-flex items-center rounded-md bg-yellow-50 dark:bg-yellow-900/30 px-2 py-1 text-xs font-medium text-yellow-700 dark:text-yellow-300 ring-1 ring-inset ring-yellow-600/20 dark:ring-yellow-400/20">Edición de Usuario</span>',
        'creacion_sorteo': '<span class="inline-flex items-center rounded-md bg-indigo-50 dark:bg-indigo-900/30 px-2 py-1 text-xs font-medium text-indigo-700 dark:text-indigo-300 ring-1 ring-inset ring-indigo-700/10 dark:ring-indigo-400/20">Creación de Sorteo</span>',
        'generacion_ganador': '<span class="inline-flex items-center rounded-md bg-purple-50 dark:bg-purple-900/30 px-2 py-1 text-xs font-medium text-purple-700 dark:text-purple-300 ring-1 ring-inset ring-purple-700/10 dark:ring-purple-400/20">Generación de Ganador</span>',
        'validacion_pago': '<span class="inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-900/30 px-2 py-1 text-xs font-medium text-blue-700 dark:text-blue-300 ring-1 ring-inset ring-blue-700/10 dark:ring-blue-400/20">Validación de Pago</span>'
    };
    
    const labels = {
        'creacion_usuario': 'Creación de Usuario',
        'login_exitoso': 'Login Exitoso',
        'login_fallido': 'Login Fallido',
        'edicion_usuario': 'Edición de Usuario',
        'creacion_sorteo': 'Creación de Sorteo',
        'generacion_ganador': 'Generación de Ganador',
        'validacion_pago': 'Validación de Pago'
    };
    
    return badges[tipoAccion] || `<span class="inline-flex items-center rounded-md bg-gray-50 dark:bg-gray-900/30 px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-300 ring-1 ring-inset ring-gray-600/20 dark:ring-gray-400/20">${labels[tipoAccion] || tipoAccion}</span>`;
}

function renderTable(filteredRecords) {
    const tbody = document.getElementById('auditTableBody');
    
    if (!tbody) {
        console.error('Error: No se encontró el elemento auditTableBody');
        return;
    }
    
    console.log('Renderizando tabla con', filteredRecords?.length || 0, 'registros');
    
    if (!filteredRecords || filteredRecords.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-[#637588] dark:text-[#9da6b9]">
                    <span class="material-symbols-outlined text-4xl mb-2 block">search_off</span>
                    <p class="text-sm">No se encontraron registros de auditoría</p>
                    <p class="text-xs mt-1 text-[#9da6b9]">Los registros aparecerán aquí cuando se realicen acciones en el sistema</p>
                </td>
            </tr>
        `;
        return;
    }
    
    const start = (currentPage - 1) * recordsPerPage;
    const end = start + recordsPerPage;
    const pageRecords = filteredRecords.slice(start, end);
    
    if (pageRecords.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-[#637588] dark:text-[#9da6b9]">
                    <span class="material-symbols-outlined text-4xl mb-2 block">search_off</span>
                    <p class="text-sm">No se encontraron registros con los filtros aplicados</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = pageRecords.map((record, index) => {
        // Validar que los campos requeridos existan
        if (!record) {
            console.error(`Registro ${index} es null o undefined`);
            return '';
        }
        
        if (!record.tipo_accion) {
            console.warn(`Registro ${index} no tiene tipo_accion:`, record);
        }
        
        if (!record.recurso) {
            console.warn(`Registro ${index} no tiene recurso:`, record);
        }
        
        // Validar y obtener valores con defaults
        const estado = record.estado || 'success';
        const tipoAccion = record.tipo_accion || 'desconocido';
        const recurso = record.recurso || 'N/A';
        const actor = record.actor || 'Sistema';
        const fecha = record.fecha || (record.fecha_hora ? record.fecha_hora.split(' ')[0] : null);
        const hora = record.hora || (record.fecha_hora ? record.fecha_hora.split(' ')[1] : null);
        const ipAddress = record.ip_address || '127.0.0.1';
        const esAlerta = record.es_alerta !== undefined ? record.es_alerta : false;
        
        const statusBadge = estado === 'success' 
            ? '<span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 dark:bg-emerald-900/30 px-2 py-1 text-xs font-medium text-emerald-700 dark:text-emerald-400"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Exitoso</span>'
            : '<span class="inline-flex items-center gap-1 rounded-full bg-red-50 dark:bg-red-900/30 px-2 py-1 text-xs font-medium text-red-700 dark:text-red-400"><span class="material-symbols-outlined text-[14px]">close</span>Fallido</span>';
        
        const tipoAccionBadge = getTipoAccionBadge(tipoAccion);
        const formattedDate = formatDate(fecha || record.fecha_hora);
        
        const rowClass = esAlerta 
            ? 'audit-row group bg-red-50/50 dark:bg-red-900/5 hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors border-l-2 border-l-red-500'
            : 'audit-row group hover:bg-[#f1f5f9] dark:hover:bg-[#282d39] transition-colors';
        
        // Escapar valores para prevenir XSS
        const escapeHtml = (text) => {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };
        
        return `
            <tr class="${rowClass}" data-actor="${escapeHtml(actor)}" data-tipo-accion="${escapeHtml(tipoAccion)}" data-recurso="${escapeHtml(recurso)}" data-estado="${escapeHtml(estado)}" data-fecha="${escapeHtml(fecha || '')}" data-hora="${escapeHtml(hora || '')}" data-ip="${escapeHtml(ipAddress)}" data-alert="${esAlerta}">
                <td class="px-6 py-4 whitespace-nowrap font-medium text-[#111318] dark:text-white">
                    ${formattedDate} <span class="text-[#9da6b9] ml-1 font-normal text-xs">${hora || 'N/A'}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-3">
                        <div class="size-8 rounded-full bg-[#e0e7ff] dark:bg-primary/20 flex items-center justify-center text-primary font-bold text-xs">
                            ${actor === 'Sistema' ? '<span class="material-symbols-outlined text-[16px]">smart_toy</span>' : actor.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()}
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[#111318] dark:text-white font-medium">${escapeHtml(actor)}</span>
                            <span class="text-xs">${actor === 'Sistema' ? 'Sistema' : actor.includes('Desconocido') || actor === 'Usuario Desconocido' ? 'IP: ' + escapeHtml(ipAddress) : 'Usuario'}</span>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">${tipoAccionBadge}</td>
                <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-[#111318] dark:text-[#cbd5e1]">${escapeHtml(recurso)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">${statusBadge}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                    <button onclick="showDetailsModal(this)" class="${esAlerta ? 'text-red-500 hover:text-red-600 dark:hover:text-red-400' : 'text-[#9da6b9] hover:text-primary'} transition-colors">
                        <span class="material-symbols-outlined text-[20px]">${esAlerta ? 'warning' : 'visibility'}</span>
                    </button>
                </td>
            </tr>
        `;
    }).filter(row => row !== '').join('');
}

function renderPagination(totalRecords) {
    const totalPages = Math.ceil(totalRecords / recordsPerPage);
    const paginationNumbers = document.getElementById('paginationNumbers');
    
    let paginationHTML = '';
    for (let i = 1; i <= Math.min(totalPages, 5); i++) {
        if (i === currentPage) {
            paginationHTML += `<a aria-current="page" class="relative z-10 inline-flex items-center bg-primary px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 cursor-pointer">${i}</a>`;
        } else {
            paginationHTML += `<a onclick="changePage(${i})" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-[#111318] dark:text-white ring-1 ring-inset ring-gray-300 dark:ring-[#3e4556] hover:bg-gray-50 dark:hover:bg-[#333a4a] focus:z-20 focus:outline-offset-0 cursor-pointer">${i}</a>`;
        }
    }
    
    if (totalPages > 5) {
        paginationHTML = `<a onclick="changePage(1)" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-[#111318] dark:text-white ring-1 ring-inset ring-gray-300 dark:ring-[#3e4556] hover:bg-gray-50 dark:hover:bg-[#333a4a] focus:z-20 focus:outline-offset-0 cursor-pointer">1</a>
        <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-400 ring-1 ring-inset ring-gray-300 dark:ring-[#3e4556] focus:outline-offset-0">...</span>
        <a onclick="changePage(${totalPages})" class="relative hidden items-center px-4 py-2 text-sm font-semibold text-[#111318] dark:text-white ring-1 ring-inset ring-gray-300 dark:ring-[#3e4556] hover:bg-gray-50 dark:hover:bg-[#333a4a] focus:z-20 focus:outline-offset-0 md:inline-flex cursor-pointer">${totalPages}</a>`;
    }
    
    paginationNumbers.innerHTML = paginationHTML;
    
    // Actualizar información de paginación
    const start = (currentPage - 1) * recordsPerPage + 1;
    const end = Math.min(currentPage * recordsPerPage, totalRecords);
    document.getElementById('paginationInfo').textContent = start;
    document.getElementById('paginationEnd').textContent = end;
    document.getElementById('paginationTotal').textContent = totalRecords;
    
    // Actualizar botones prev/next
    document.getElementById('prevPage').classList.toggle('opacity-50', currentPage === 1);
    document.getElementById('nextPage').classList.toggle('opacity-50', currentPage === totalPages);
}

function changePage(direction) {
    const totalPages = Math.ceil(auditRecords.length / recordsPerPage);
    if (direction === 'prev' && currentPage > 1) {
        currentPage--;
    } else if (direction === 'next' && currentPage < totalPages) {
        currentPage++;
    } else if (typeof direction === 'number') {
        currentPage = direction;
    }
    renderTable(auditRecords);
    renderPagination(auditRecords.length);
}

function showDetailsModal(button) {
    const row = button.closest('tr');
    const actor = row.getAttribute('data-actor');
    const tipoAccion = row.getAttribute('data-tipo-accion');
    const resource = row.getAttribute('data-recurso');
    const status = row.getAttribute('data-estado');
    const date = row.getAttribute('data-fecha');
    const time = row.getAttribute('data-hora');
    const ip = row.getAttribute('data-ip');
    
    // Mapear tipo_accion a nombre legible
    const tipoAccionLabels = {
        'creacion_usuario': 'Creación de Usuario',
        'login_exitoso': 'Login Exitoso',
        'login_fallido': 'Login Fallido',
        'edicion_usuario': 'Edición de Usuario',
        'creacion_sorteo': 'Creación de Sorteo',
        'generacion_ganador': 'Generación de Ganador',
        'validacion_pago': 'Validación de Pago'
    };
    const actionLabel = tipoAccionLabels[tipoAccion] || tipoAccion;
    
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-50 overflow-y-auto';
    modal.onclick = function(e) {
        if (e.target === modal) modal.remove();
    };
    
    modal.innerHTML = `
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity"></div>
            <div class="inline-block align-bottom bg-white dark:bg-[#1e293b] rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-[#e5e7eb] dark:border-[#282d39]">
                <div class="px-4 pt-5 pb-4 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-[#111318] dark:text-white mb-4">Detalles de Auditoría</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-[#637588] dark:text-[#9da6b9]">Actor:</p>
                            <p class="text-base font-medium text-[#111318] dark:text-white">${actor}</p>
                        </div>
                        <div>
                            <p class="text-sm text-[#637588] dark:text-[#9da6b9]">Tipo de Acción:</p>
                            <p class="text-base font-medium text-[#111318] dark:text-white">${actionLabel}</p>
                        </div>
                        <div>
                            <p class="text-sm text-[#637588] dark:text-[#9da6b9]">Recurso:</p>
                            <p class="text-base font-mono text-[#111318] dark:text-white">${resource}</p>
                        </div>
                        <div>
                            <p class="text-sm text-[#637588] dark:text-[#9da6b9]">Estado:</p>
                            <p class="text-base font-medium text-[#111318] dark:text-white">${status === 'success' ? 'Exitoso' : 'Fallido'}</p>
                        </div>
                        <div>
                            <p class="text-sm text-[#637588] dark:text-[#9da6b9]">Fecha y Hora:</p>
                            <p class="text-base text-[#111318] dark:text-white">${date} ${time}</p>
                        </div>
                        <div>
                            <p class="text-sm text-[#637588] dark:text-[#9da6b9]">IP:</p>
                            <p class="text-base font-mono text-[#111318] dark:text-white">${ip}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-[#f8fafc] dark:bg-[#111621] px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button onclick="this.closest('.fixed').remove()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

function refreshData() {
    const button = document.getElementById('refreshButton');
    const icon = button.querySelector('.material-symbols-outlined');
    icon.classList.add('animate-spin-fast');
    button.disabled = true;
    
    loadAuditData().then(() => {
        icon.classList.remove('animate-spin-fast');
        button.disabled = false;
        showNotification('Datos actualizados correctamente', 'success');
    }).catch(() => {
        icon.classList.remove('animate-spin-fast');
        button.disabled = false;
        showNotification('Error al actualizar datos', 'error');
    });
}

function exportToCsv() {
    // Los datos ya están filtrados en auditRecords
    if (auditRecords.length === 0) {
        showNotification('No hay datos para exportar', 'error');
        return;
    }
    
    const tipoAccionLabels = {
        'creacion_usuario': 'Creación de Usuario',
        'login_exitoso': 'Login Exitoso',
        'login_fallido': 'Login Fallido',
        'edicion_usuario': 'Edición de Usuario',
        'creacion_sorteo': 'Creación de Sorteo',
        'generacion_ganador': 'Generación de Ganador',
        'validacion_pago': 'Validación de Pago'
    };
    
    const headers = ['Fecha', 'Hora', 'Actor', 'Tipo de Acción', 'Recurso', 'Estado', 'IP', 'Alerta'];
    const rows = auditRecords.map(r => [
        formatDate(r.fecha),
        r.hora,
        r.actor,
        tipoAccionLabels[r.tipo_accion] || r.tipo_accion,
        r.recurso,
        r.estado === 'success' ? 'Exitoso' : 'Fallido',
        r.ip_address,
        r.es_alerta ? 'Sí' : 'No'
    ]);
    
    // Agregar BOM para UTF-8 (Excel)
    const BOM = '\uFEFF';
    const csv = BOM + [headers, ...rows].map(row => 
        row.map(cell => {
            // Escapar comillas y envolver en comillas si contiene comas o saltos de línea
            const cellStr = String(cell).replace(/"/g, '""');
            return `"${cellStr}"`;
        }).join(',')
    ).join('\n');
    
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    const fechaExport = new Date().toISOString().split('T')[0];
    link.download = `auditoria_${fechaExport}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showNotification(`CSV exportado correctamente (${auditRecords.length} registros)`, 'success');
}

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

function navegarAtras() {
    window.history.back();
}
</script>
</body></html>

//pagina para ver la auditoria de acciones como administrador despues de iniciar sesion
//Se ve esta pagina para ver la auditoria de acciones de la plataforma y los usuarios.