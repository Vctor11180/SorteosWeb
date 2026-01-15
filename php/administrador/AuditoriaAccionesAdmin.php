<!DOCTYPE html>
<?php
// Conexión a la base de datos
require_once 'config.php';
$conn = getDBConnection();

// Obtener parámetros de filtrado y paginación
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$tipoFilter = isset($_GET['tipo']) ? trim($_GET['tipo']) : 'all';
$estadoFilter = isset($_GET['estado']) ? trim($_GET['estado']) : 'all';
$alertsOnly = isset($_GET['alerts']) && $_GET['alerts'] === '1';
$fechaInicio = isset($_GET['fecha_inicio']) ? trim($_GET['fecha_inicio']) : '';
$fechaFin = isset($_GET['fecha_fin']) ? trim($_GET['fecha_fin']) : '';
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$recordsPerPage = 10;
$offset = ($currentPage - 1) * $recordsPerPage;

// Intentar detectar la estructura de la tabla de auditoría
// Primero verificar auditoria_acciones (tabla principal)
$query = "SHOW TABLES LIKE 'auditoria_acciones'";
$tableCheck = $conn->query($query);
$tableName = 'auditoria_admin'; // Fallback por defecto
$hasExtendedFields = false;

if ($tableCheck && $tableCheck->num_rows > 0) {
    $tableName = 'auditoria_acciones';
    $hasExtendedFields = true;
} else {
    // Verificar logs_auditoria como segunda opción
    $query = "SHOW TABLES LIKE 'logs_auditoria'";
    $tableCheck = $conn->query($query);
    if ($tableCheck && $tableCheck->num_rows > 0) {
        $tableName = 'logs_auditoria';
        $hasExtendedFields = true;
    } else {
        // Verificar si auditoria_admin tiene campos extendidos
        $checkFields = "SHOW COLUMNS FROM auditoria_admin LIKE 'tipo_accion'";
        $fieldCheck = $conn->query($checkFields);
        if ($fieldCheck && $fieldCheck->num_rows > 0) {
            $hasExtendedFields = true;
        }
    }
}

// Construir consulta según la estructura detectada
if ($hasExtendedFields) {
    // Estructura extendida con tipo_accion, recurso, estado, es_alerta
    $query = "SELECT 
                a.id_log,
                a.id_usuario,
                a.tipo_accion,
                a.accion,
                a.recurso,
                a.estado,
                a.es_alerta,
                a.ip_address,
                a.fecha_hora,
                u.primer_nombre,
                u.apellido_paterno,
                u.email,
                r.nombre_rol
              FROM {$tableName} a
              LEFT JOIN usuarios u ON a.id_usuario = u.id_usuario
              LEFT JOIN roles r ON u.id_rol = r.id_rol
              WHERE 1=1";
} else {
    // Estructura básica de auditoria_admin
    $query = "SELECT 
                a.id_log,
                a.id_admin as id_usuario,
                a.accion,
                a.modulo as recurso,
                a.ip_address,
                a.fecha_hora,
                u.primer_nombre,
                u.apellido_paterno,
                u.email,
                r.nombre_rol
              FROM auditoria_admin a
              LEFT JOIN usuarios u ON a.id_admin = u.id_usuario
              LEFT JOIN roles r ON u.id_rol = r.id_rol
              WHERE 1=1";
}

$params = [];
$types = '';

// Aplicar filtros de búsqueda - buscar en todos los campos relevantes
if (!empty($searchTerm)) {
    if ($hasExtendedFields) {
        // Buscar en: accion, recurso, email, nombre completo, id_log, tipo_accion
        $query .= " AND (
            a.accion LIKE ? OR 
            a.recurso LIKE ? OR 
            a.tipo_accion LIKE ? OR
            u.email LIKE ? OR 
            CONCAT(u.primer_nombre, ' ', COALESCE(u.apellido_paterno, '')) LIKE ? OR
            CAST(a.id_log AS CHAR) LIKE ?
        )";
        $searchParam = '%' . $searchTerm . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= 'ssssss';
    } else {
        // Estructura básica - buscar en accion, modulo, email, nombre, id_log
        $query .= " AND (
            a.accion LIKE ? OR 
            a.modulo LIKE ? OR 
            u.email LIKE ? OR
            CONCAT(u.primer_nombre, ' ', COALESCE(u.apellido_paterno, '')) LIKE ? OR
            CAST(a.id_log AS CHAR) LIKE ?
        )";
        $searchParam = '%' . $searchTerm . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= 'sssss';
    }
}

// Mapeo de tipos de acción para el filtro
$tipoAccionMap = [
    'creacion_usuario' => 'creacion_usuario',
    'edicion_usuario' => 'edicion_usuario',
    'login_exitoso' => 'login_exitoso',
    'login_fallido' => 'login_fallido',
    'validacion_pago' => 'validacion_pago',
    'creacion_sorteo' => 'creacion_sorteo',
    'generador_ganador' => 'generador_ganador'
];

if ($tipoFilter !== 'all' && $hasExtendedFields && isset($tipoAccionMap[$tipoFilter])) {
    $query .= " AND a.tipo_accion = ?";
    $params[] = $tipoAccionMap[$tipoFilter];
    $types .= 's';
} elseif ($tipoFilter !== 'all' && $hasExtendedFields) {
    // Si no está en el mapa, buscar por el texto de la acción
    $query .= " AND a.accion LIKE ?";
    $params[] = '%' . $tipoFilter . '%';
    $types .= 's';
}

// Filtro de estado - mapear valores del frontend a valores de BD
if ($estadoFilter !== 'all' && $hasExtendedFields && !$alertsOnly) {
    // Mapear valores del filtro a estados de la BD
    $estadoMap = [
        'success' => 'success',
        'Exitoso' => 'success',
        'error' => 'error',
        'Fallido' => 'error'
    ];
    
    $estadoBD = isset($estadoMap[$estadoFilter]) ? $estadoMap[$estadoFilter] : $estadoFilter;
    $query .= " AND a.estado = ?";
    $params[] = $estadoBD;
    $types .= 's';
}

// Filtro de alertas (tiene prioridad sobre estado si está activo)
if ($alertsOnly && $hasExtendedFields) {
    $query .= " AND a.es_alerta = 1";
}

// Filtro de rango de fechas
if (!empty($fechaInicio)) {
    $query .= " AND DATE(a.fecha_hora) >= ?";
    $params[] = $fechaInicio;
    $types .= 's';
}

if (!empty($fechaFin)) {
    $query .= " AND DATE(a.fecha_hora) <= ?";
    $params[] = $fechaFin;
    $types .= 's';
}

// Ordenar y limitar
$query .= " ORDER BY a.fecha_hora DESC LIMIT ? OFFSET ?";
$params[] = $recordsPerPage;
$params[] = $offset;
$types .= 'ii';

// Ejecutar consulta
$stmt = $conn->prepare($query);
if ($stmt && !empty($params)) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}

$auditRecords = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $auditRecords[] = $row;
    }
}

// Contar total de registros (sin LIMIT y OFFSET)
$countQuery = "SELECT COUNT(*) as total FROM (" . preg_replace('/LIMIT \? OFFSET \?$/', '', $query) . ") as count_table";
$countParams = array_slice($params, 0, -2);
$countTypes = substr($types, 0, -2);

$countStmt = $conn->prepare($countQuery);
if ($countStmt && !empty($countParams)) {
    $countStmt->bind_param($countTypes, ...$countParams);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalRecords = $countResult->fetch_assoc()['total'];
    $countStmt->close();
} else if ($countStmt) {
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalRecords = $countResult->fetch_assoc()['total'];
    $countStmt->close();
} else {
    // Fallback: consulta simple
    $simpleCountQuery = str_replace("SELECT", "SELECT COUNT(*) as total", $query);
    $simpleCountQuery = preg_replace('/LIMIT.*$/', '', $simpleCountQuery);
    $countResult = $conn->query($simpleCountQuery);
    $totalRecords = $countResult ? $countResult->fetch_assoc()['total'] : 0;
}

$totalPages = ceil($totalRecords / $recordsPerPage);

// Función helper para construir URLs de paginación preservando filtros
function buildPaginationUrl($page) {
    global $searchTerm, $tipoFilter, $estadoFilter, $alertsOnly, $fechaInicio, $fechaFin;
    $params = [];
    if ($searchTerm) $params['search'] = $searchTerm;
    if ($tipoFilter !== 'all') $params['tipo'] = $tipoFilter;
    if ($estadoFilter !== 'all') $params['estado'] = $estadoFilter;
    if ($alertsOnly) $params['alerts'] = '1';
    if ($fechaInicio) $params['fecha_inicio'] = $fechaInicio;
    if ($fechaFin) $params['fecha_fin'] = $fechaFin;
    if ($page > 1) $params['page'] = $page;
    return '?' . http_build_query($params);
}

// Función helper para formatear fecha
function formatFechaHora($fechaHora) {
    if (empty($fechaHora)) return ['date' => 'N/A', 'time' => ''];
    $timestamp = strtotime($fechaHora);
    return [
        'date' => date('d M Y', $timestamp),
        'time' => date('H:i:s', $timestamp)
    ];
}

// Función helper para obtener iniciales
function getIniciales($nombre, $apellido) {
    $iniciales = '';
    if (!empty($nombre)) $iniciales .= strtoupper(substr($nombre, 0, 1));
    if (!empty($apellido)) $iniciales .= strtoupper(substr($apellido, 0, 1));
    return $iniciales ?: '?';
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
<input id="filterSearchInput" class="w-full bg-transparent text-sm text-[#111318] dark:text-white placeholder:text-[#9da6b9] focus:outline-none" placeholder="Buscar por acción, recurso, email, nombre, ID..."/>
</label>
</div>
<!-- Filter Chips -->
<div class="flex flex-wrap gap-2 items-center">
<span class="text-xs font-semibold text-[#637588] dark:text-[#9da6b9] uppercase tracking-wider mr-1">Filtros:</span>
<!-- Filtro de Rango de Fechas -->
<div class="relative group">
<button id="fechaFilterButton" onclick="toggleFechaFilter()" class="flex h-8 items-center gap-x-2 rounded-md bg-[#f0f2f4] dark:bg-[#282d39] px-3 hover:bg-[#e5e7eb] dark:hover:bg-[#333a4a] transition-colors border border-transparent dark:border-[#3e4556]">
<span id="fechaFilterText" class="text-[#111318] dark:text-white text-xs font-medium"><?php echo !empty($fechaInicio) && !empty($fechaFin) ? date('d/m/Y', strtotime($fechaInicio)) . ' - ' . date('d/m/Y', strtotime($fechaFin)) : 'Rango de Fechas'; ?></span>
<span class="material-symbols-outlined text-[#111318] dark:text-white text-[16px]">calendar_today</span>
</button>
<!-- Dropdown de Fechas -->
<div id="fechaFilterDropdown" class="hidden absolute top-full left-0 mt-2 w-80 bg-white dark:bg-[#1e293b] rounded-lg shadow-lg border border-[#e5e7eb] dark:border-[#282d39] z-50 p-4">
<div class="space-y-3">
<div>
<label class="block text-xs font-medium text-[#637588] dark:text-[#9da6b9] mb-1">Fecha Inicio</label>
<input type="date" id="fechaInicio" value="<?php echo htmlspecialchars($fechaInicio); ?>" class="w-full bg-[#f0f2f4] dark:bg-[#111621] border border-[#e5e7eb] dark:border-[#3e4556] rounded-md px-3 py-2 text-sm text-[#111318] dark:text-white focus:outline-none focus:ring-1 focus:ring-primary"/>
</div>
<div>
<label class="block text-xs font-medium text-[#637588] dark:text-[#9da6b9] mb-1">Fecha Fin</label>
<input type="date" id="fechaFin" value="<?php echo htmlspecialchars($fechaFin); ?>" class="w-full bg-[#f0f2f4] dark:bg-[#111621] border border-[#e5e7eb] dark:border-[#3e4556] rounded-md px-3 py-2 text-sm text-[#111318] dark:text-white focus:outline-none focus:ring-1 focus:ring-primary"/>
</div>
<div class="flex gap-2 pt-2">
<button onclick="aplicarFiltroFechas()" class="flex-1 px-3 py-2 bg-primary text-white text-xs font-medium rounded-md hover:bg-blue-600 transition-colors">Aplicar</button>
<button onclick="limpiarFiltroFechas()" class="flex-1 px-3 py-2 bg-[#f0f2f4] dark:bg-[#282d39] text-[#111318] dark:text-white text-xs font-medium rounded-md hover:bg-[#e5e7eb] dark:hover:bg-[#333a4a] transition-colors">Limpiar</button>
</div>
</div>
</div>
</div>
<!-- Filtro de Tipo de Acción -->
<div class="relative">
<select id="tipoAccionFilter" class="flex h-8 items-center gap-x-2 rounded-md bg-[#f0f2f4] dark:bg-[#282d39] px-3 hover:bg-[#e5e7eb] dark:hover:bg-[#333a4a] transition-colors border border-transparent dark:border-[#3e4556] text-[#111318] dark:text-white text-xs font-medium cursor-pointer appearance-none pr-8 focus:outline-none focus:ring-1 focus:ring-primary">
<option value="all" <?php echo $tipoFilter === 'all' ? 'selected' : ''; ?>>Tipo: Todos</option>
<option value="creacion_usuario" <?php echo $tipoFilter === 'creacion_usuario' ? 'selected' : ''; ?>>Creación de Usuario</option>
<option value="edicion_usuario" <?php echo $tipoFilter === 'edicion_usuario' ? 'selected' : ''; ?>>Edición de Usuario</option>
<option value="login_exitoso" <?php echo $tipoFilter === 'login_exitoso' ? 'selected' : ''; ?>>Login Exitoso</option>
<option value="login_fallido" <?php echo $tipoFilter === 'login_fallido' ? 'selected' : ''; ?>>Login Fallido</option>
<option value="validacion_pago" <?php echo $tipoFilter === 'validacion_pago' ? 'selected' : ''; ?>>Validación de Pago</option>
<option value="creacion_sorteo" <?php echo $tipoFilter === 'creacion_sorteo' ? 'selected' : ''; ?>>Creación de Sorteo</option>
<option value="generador_ganador" <?php echo $tipoFilter === 'generador_ganador' ? 'selected' : ''; ?>>Generador de Ganador</option>
</select>
<div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none">
<span class="material-symbols-outlined text-[#111318] dark:text-white text-[16px]">expand_more</span>
</div>
</div>
<!-- Filtro de Estado -->
<div class="relative group">
<button id="estadoFilterButton" onclick="toggleEstadoFilter()" class="flex h-8 items-center gap-x-2 rounded-md bg-[#f0f2f4] dark:bg-[#282d39] px-3 hover:bg-[#e5e7eb] dark:hover:bg-[#333a4a] transition-colors border border-transparent dark:border-[#3e4556]">
<span id="estadoFilterText" class="text-[#111318] dark:text-white text-xs font-medium">
<?php 
$estadoText = 'Estado: Todos';
if ($estadoFilter === 'success' || $estadoFilter === 'Exitoso') {
    $estadoText = 'Estado: Exitoso';
} elseif ($estadoFilter === 'error' || $estadoFilter === 'Fallido') {
    $estadoText = 'Estado: Fallido';
}
echo $estadoText;
?>
</span>
<span class="material-symbols-outlined text-[#111318] dark:text-white text-[16px]">filter_list</span>
</button>
<!-- Dropdown de Estado -->
<div id="estadoFilterDropdown" class="hidden absolute top-full left-0 mt-2 w-48 bg-white dark:bg-[#1e293b] rounded-lg shadow-lg border border-[#e5e7eb] dark:border-[#282d39] z-50 py-2">
<button onclick="aplicarFiltroEstado('all')" class="w-full text-left px-4 py-2 text-sm text-[#111318] dark:text-white hover:bg-[#f0f2f4] dark:hover:bg-[#282d39] transition-colors <?php echo $estadoFilter === 'all' && !$alertsOnly ? 'bg-primary/10 text-primary' : ''; ?>">
Todos
</button>
<button onclick="aplicarFiltroEstado('success')" class="w-full text-left px-4 py-2 text-sm text-[#111318] dark:text-white hover:bg-[#f0f2f4] dark:hover:bg-[#282d39] transition-colors <?php echo ($estadoFilter === 'success' || $estadoFilter === 'Exitoso') && !$alertsOnly ? 'bg-primary/10 text-primary' : ''; ?>">
Exitoso
</button>
<button onclick="aplicarFiltroEstado('error')" class="w-full text-left px-4 py-2 text-sm text-[#111318] dark:text-white hover:bg-[#f0f2f4] dark:hover:bg-[#282d39] transition-colors <?php echo ($estadoFilter === 'error' || $estadoFilter === 'Fallido') && !$alertsOnly ? 'bg-primary/10 text-primary' : ''; ?>">
Fallido
</button>
</div>
</div>
<div class="h-6 w-px bg-[#e5e7eb] dark:bg-[#3e4556] mx-1"></div>
<button id="alertsOnlyButton" onclick="toggleAlertsOnly()" class="flex h-8 items-center gap-x-1 rounded-md <?php echo $alertsOnly ? 'bg-red-500/20 dark:bg-red-500/30' : 'bg-red-500/10 dark:bg-red-500/20'; ?> px-3 hover:bg-red-500/20 dark:hover:bg-red-500/30 transition-colors border border-red-500/20 dark:border-red-500/30">
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
<th class="px-6 py-4 whitespace-nowrap w-48" scope="col">Acción</th>
<th class="px-6 py-4 whitespace-nowrap" scope="col">Recurso Afectado</th>
<th class="px-6 py-4 whitespace-nowrap w-32 text-center" scope="col">Estado</th>
<th class="px-6 py-4 whitespace-nowrap w-24 text-right" scope="col"></th>
</tr>
</thead>
<tbody id="auditTableBody" class="divide-y divide-[#e5e7eb] dark:divide-[#282d39]">
<?php
if (empty($auditRecords)) {
    echo '<tr><td colspan="6" class="px-6 py-8 text-center text-[#637588] dark:text-[#9da6b9]">No hay registros de auditoría disponibles.</td></tr>';
} else {
    foreach ($auditRecords as $record) {
        $fechaFormateada = formatFechaHora($record['fecha_hora']);
        
        // Manejar id_usuario NULL - mostrar como Sistema
        $idUsuario = $record['id_usuario'] ?? null;
        if (empty($idUsuario) || is_null($idUsuario)) {
            $actor = 'Sistema';
            $rol = 'Automático';
            $iniciales = '';
        } else {
            $nombreCompleto = trim(($record['primer_nombre'] ?? '') . ' ' . ($record['apellido_paterno'] ?? ''));
            $actor = !empty($nombreCompleto) ? $nombreCompleto : (!empty($record['email']) ? $record['email'] : 'Usuario Desconocido');
            $rol = $record['nombre_rol'] ?? 'Usuario';
            $iniciales = getIniciales($record['primer_nombre'] ?? '', $record['apellido_paterno'] ?? '');
        }
        
        $accion = $record['accion'] ?? 'Acción desconocida';
        $recurso = $record['recurso'] ?? 'N/A';
        $estado = $hasExtendedFields ? ($record['estado'] ?? 'success') : 'success';
        $esAlerta = $hasExtendedFields ? ($record['es_alerta'] ?? 0) : 0;
        $ip = $record['ip_address'] ?? 'N/A';
        
        // Determinar clases según el estado
        $estadoClass = ($estado === 'success' || $estado === 'Exitoso') 
            ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'
            : 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400';
        $estadoIcon = ($estado === 'success' || $estado === 'Exitoso')
            ? '<span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>'
            : '<span class="material-symbols-outlined text-[14px]">close</span>';
        $estadoTexto = ($estado === 'success' || $estado === 'Exitoso') ? 'Exitoso' : 'Fallido';
        
        // Clase de fila según alerta
        $rowClass = $esAlerta 
            ? 'audit-row group bg-red-50/50 dark:bg-red-900/5 hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors border-l-2 border-l-red-500'
            : 'audit-row group hover:bg-[#f1f5f9] dark:hover:bg-[#282d39] transition-colors';
        
        // Badge de acción según tipo
        $accionLower = strtolower($accion);
        if (strpos($accionLower, 'validación') !== false || strpos($accionLower, 'pago') !== false) {
            $accionBadge = 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 ring-blue-700/10 dark:ring-blue-400/20';
        } elseif (strpos($accionLower, 'login') !== false || strpos($accionLower, 'fallido') !== false) {
            $accionBadge = 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 ring-red-600/10 dark:ring-red-400/20';
        } elseif (strpos($accionLower, 'edición') !== false || strpos($accionLower, 'editar') !== false) {
            $accionBadge = 'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 ring-yellow-600/20 dark:ring-yellow-400/20';
        } elseif (strpos($accionLower, 'creación') !== false || strpos($accionLower, 'crear') !== false) {
            $accionBadge = 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 ring-indigo-700/10 dark:ring-indigo-400/20';
        } else {
            $accionBadge = 'bg-gray-50 dark:bg-gray-900/30 text-gray-700 dark:text-gray-300 ring-gray-600/10 dark:ring-gray-400/20';
        }
        
        // Avatar - mostrar icono de sistema si id_usuario es NULL o actor es Sistema
        $avatarHtml = (empty($idUsuario) || is_null($idUsuario) || $actor === 'Sistema')
            ? '<div class="size-8 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400 font-bold"><span class="material-symbols-outlined text-[16px]">smart_toy</span></div>'
            : '<div class="size-8 rounded-full bg-[#e0e7ff] dark:bg-primary/20 flex items-center justify-center text-primary font-bold text-xs">' . htmlspecialchars($iniciales) . '</div>';
?>
<tr class="<?php echo $rowClass; ?>" 
    data-actor="<?php echo htmlspecialchars($actor); ?>" 
    data-action="<?php echo htmlspecialchars($accion); ?>" 
    data-resource="<?php echo htmlspecialchars($recurso); ?>" 
    data-status="<?php echo $estado; ?>" 
    data-date="<?php echo $fechaFormateada['date']; ?>" 
    data-time="<?php echo $fechaFormateada['time']; ?>" 
    data-ip="<?php echo htmlspecialchars($ip); ?>">
<td class="px-6 py-4 whitespace-nowrap font-medium text-[#111318] dark:text-white">
    <?php echo $fechaFormateada['date']; ?> <span class="text-[#9da6b9] ml-1 font-normal text-xs"><?php echo $fechaFormateada['time']; ?></span>
</td>
<td class="px-6 py-4 whitespace-nowrap">
    <div class="flex items-center gap-3">
        <?php echo $avatarHtml; ?>
        <div class="flex flex-col">
            <span class="text-[#111318] dark:text-white font-medium"><?php echo htmlspecialchars($actor); ?></span>
            <span class="text-xs"><?php echo htmlspecialchars($rol); ?></span>
        </div>
    </div>
</td>
<td class="px-6 py-4 whitespace-nowrap">
    <span class="inline-flex items-center rounded-md <?php echo $accionBadge; ?> px-2 py-1 text-xs font-medium ring-1 ring-inset">
        <?php echo htmlspecialchars($accion); ?>
    </span>
</td>
<td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-[#111318] dark:text-[#cbd5e1]">
    <?php echo htmlspecialchars($recurso); ?>
</td>
<td class="px-6 py-4 whitespace-nowrap text-center">
    <span class="inline-flex items-center gap-1 rounded-full <?php echo $estadoClass; ?> px-2 py-1 text-xs font-medium">
        <?php echo $estadoIcon; ?>
        <?php echo $estadoTexto; ?>
    </span>
</td>
<td class="px-6 py-4 whitespace-nowrap text-right">
    <button onclick="showDetailsModal(this)" class="<?php echo $esAlerta ? 'text-red-500 hover:text-red-600 dark:hover:text-red-400' : 'text-[#9da6b9] hover:text-primary'; ?> transition-colors">
        <span class="material-symbols-outlined text-[20px]"><?php echo $esAlerta ? 'warning' : 'visibility'; ?></span>
    </button>
</td>
</tr>
<?php
    }
}
?>
</tbody>
</table>
</div>
<!-- Pagination -->
<div class="flex items-center justify-between border-t border-[#e5e7eb] dark:border-[#282d39] bg-white dark:bg-[#1e293b] px-4 py-3 sm:px-6">
<div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
<div>
<p class="text-sm text-[#637588] dark:text-[#9da6b9]">
                                        Mostrando <span id="paginationInfo" class="font-medium text-[#111318] dark:text-white"><?php echo $totalRecords > 0 ? (($currentPage - 1) * $recordsPerPage) + 1 : 0; ?></span> a <span id="paginationEnd" class="font-medium text-[#111318] dark:text-white"><?php echo min($currentPage * $recordsPerPage, $totalRecords); ?></span> de <span id="paginationTotal" class="font-medium text-[#111318] dark:text-white"><?php echo $totalRecords; ?></span> resultados
                                    </p>
</div>
<div>
<nav aria-label="Pagination" class="isolate inline-flex -space-x-px rounded-md shadow-sm">
<a id="prevPage" href="<?php echo $currentPage > 1 ? buildPaginationUrl($currentPage - 1) : '#'; ?>" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 dark:ring-[#3e4556] hover:bg-gray-50 dark:hover:bg-[#333a4a] focus:z-20 focus:outline-offset-0 <?php echo $currentPage > 1 ? 'cursor-pointer' : 'opacity-50 cursor-not-allowed'; ?>">
<span class="sr-only">Previous</span>
<span class="material-symbols-outlined text-[20px]">chevron_left</span>
</a>
<div id="paginationNumbers"></div>
<a id="nextPage" href="<?php echo $currentPage < $totalPages ? buildPaginationUrl($currentPage + 1) : '#'; ?>" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 dark:ring-[#3e4556] hover:bg-gray-50 dark:hover:bg-[#333a4a] focus:z-20 focus:outline-offset-0 <?php echo $currentPage < $totalPages ? 'cursor-pointer' : 'opacity-50 cursor-not-allowed'; ?>">
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
// Estado global - los datos vienen del servidor
const currentPage = <?php echo $currentPage; ?>;
const recordsPerPage = <?php echo $recordsPerPage; ?>;
const totalRecords = <?php echo $totalRecords; ?>;
const totalPages = <?php echo $totalPages; ?>;

let currentFilters = {
    search: '<?php echo htmlspecialchars($searchTerm, ENT_QUOTES); ?>',
    tipo: '<?php echo htmlspecialchars($tipoFilter, ENT_QUOTES); ?>',
    status: '<?php echo htmlspecialchars($estadoFilter, ENT_QUOTES); ?>',
    alertsOnly: <?php echo $alertsOnly ? 'true' : 'false'; ?>,
    fechaInicio: '<?php echo htmlspecialchars($fechaInicio, ENT_QUOTES); ?>',
    fechaFin: '<?php echo htmlspecialchars($fechaFin, ENT_QUOTES); ?>'
};

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
    renderPagination();
});

function setupEventListeners() {
    const filterSearchInput = document.getElementById('filterSearchInput');
    const mainSearchInput = document.getElementById('mainSearchInput');
    const tipoAccionFilter = document.getElementById('tipoAccionFilter');
    
    // Búsqueda con debounce
    let searchTimeout;
    if (filterSearchInput) {
        filterSearchInput.value = currentFilters.search;
        filterSearchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                currentFilters.search = e.target.value.trim();
                applyFilters();
            }, 500);
        });
    }
    
    if (mainSearchInput) {
        mainSearchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                currentFilters.search = e.target.value.trim();
                applyFilters();
            }, 500);
        });
    }
    
    // Filtro de tipo de acción
    if (tipoAccionFilter) {
        tipoAccionFilter.addEventListener('change', function(e) {
            currentFilters.tipo = e.target.value;
            applyFilters();
        });
    }
    
    // Cerrar dropdowns al hacer click fuera
    document.addEventListener('click', function(e) {
        const fechaDropdown = document.getElementById('fechaFilterDropdown');
        const fechaButton = document.getElementById('fechaFilterButton');
        const estadoDropdown = document.getElementById('estadoFilterDropdown');
        const estadoButton = document.getElementById('estadoFilterButton');
        
        // Cerrar dropdown de fechas si se hace click fuera
        if (fechaDropdown && fechaButton && !fechaDropdown.contains(e.target) && !fechaButton.contains(e.target)) {
            fechaDropdown.classList.add('hidden');
        }
        
        // Cerrar dropdown de estado si se hace click fuera
        if (estadoDropdown && estadoButton && !estadoDropdown.contains(e.target) && !estadoButton.contains(e.target)) {
            estadoDropdown.classList.add('hidden');
        }
        
        // Cerrar otros dropdowns
        if (!e.target.closest('.relative.group')) {
            document.querySelectorAll('[id$="Dropdown"]').forEach(dropdown => {
                if (dropdown !== fechaDropdown && dropdown !== estadoDropdown) {
                    dropdown.classList.add('hidden');
                }
            });
        }
    });
}

function toggleFechaFilter() {
    const dropdown = document.getElementById('fechaFilterDropdown');
    if (dropdown) {
        dropdown.classList.toggle('hidden');
    }
}

function aplicarFiltroFechas() {
    const fechaInicio = document.getElementById('fechaInicio');
    const fechaFin = document.getElementById('fechaFin');
    const fechaFilterText = document.getElementById('fechaFilterText');
    const dropdown = document.getElementById('fechaFilterDropdown');
    
    if (fechaInicio && fechaFin) {
        currentFilters.fechaInicio = fechaInicio.value;
        currentFilters.fechaFin = fechaFin.value;
        
        // Actualizar texto del botón
        if (fechaFilterText) {
            if (fechaInicio.value && fechaFin.value) {
                const inicio = new Date(fechaInicio.value);
                const fin = new Date(fechaFin.value);
                const formatoInicio = inicio.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
                const formatoFin = fin.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
                fechaFilterText.textContent = formatoInicio + ' - ' + formatoFin;
            } else {
                fechaFilterText.textContent = 'Rango de Fechas';
            }
        }
        
        // Cerrar dropdown
        if (dropdown) {
            dropdown.classList.add('hidden');
        }
        
        // Aplicar filtros
        applyFilters();
    }
}

function limpiarFiltroFechas() {
    const fechaInicio = document.getElementById('fechaInicio');
    const fechaFin = document.getElementById('fechaFin');
    const fechaFilterText = document.getElementById('fechaFilterText');
    const dropdown = document.getElementById('fechaFilterDropdown');
    
    if (fechaInicio && fechaFin) {
        fechaInicio.value = '';
        fechaFin.value = '';
        currentFilters.fechaInicio = '';
        currentFilters.fechaFin = '';
        
        // Actualizar texto del botón
        if (fechaFilterText) {
            fechaFilterText.textContent = 'Rango de Fechas';
        }
        
        // Cerrar dropdown
        if (dropdown) {
            dropdown.classList.add('hidden');
        }
        
        // Aplicar filtros
        applyFilters();
    }
}

function toggleEstadoFilter() {
    const dropdown = document.getElementById('estadoFilterDropdown');
    if (dropdown) {
        dropdown.classList.toggle('hidden');
    }
}

function aplicarFiltroEstado(estado) {
    const estadoFilterText = document.getElementById('estadoFilterText');
    const dropdown = document.getElementById('estadoFilterDropdown');
    const alertsButton = document.getElementById('alertsOnlyButton');
    
    // Actualizar estado y desactivar alertas
    currentFilters.status = estado;
    currentFilters.alertsOnly = false;
    
    // Actualizar texto del botón de estado
    if (estadoFilterText) {
        switch(estado) {
            case 'success':
                estadoFilterText.textContent = 'Estado: Exitoso';
                break;
            case 'error':
                estadoFilterText.textContent = 'Estado: Fallido';
                break;
            default:
                estadoFilterText.textContent = 'Estado: Todos';
        }
    }
    
    // Actualizar estilo del botón de alertas (desactivarlo)
    if (alertsButton) {
        alertsButton.classList.remove('bg-red-500/20', 'dark:bg-red-500/30');
        alertsButton.classList.add('bg-red-500/10', 'dark:bg-red-500/20');
    }
    
    // Cerrar dropdown
    if (dropdown) {
        dropdown.classList.add('hidden');
    }
    
    // Aplicar filtros
    applyFilters();
}

function applyFilters() {
    // Construir URL con filtros
    const params = new URLSearchParams();
    if (currentFilters.search) params.append('search', currentFilters.search);
    if (currentFilters.tipo !== 'all') params.append('tipo', currentFilters.tipo);
    if (currentFilters.status !== 'all') params.append('estado', currentFilters.status);
    if (currentFilters.alertsOnly) params.append('alerts', '1');
    if (currentFilters.fechaInicio) params.append('fecha_inicio', currentFilters.fechaInicio);
    if (currentFilters.fechaFin) params.append('fecha_fin', currentFilters.fechaFin);
    params.append('page', '1'); // Resetear a página 1
    
    window.location.href = '?' + params.toString();
}

function toggleAlertsOnly() {
    const alertsButton = document.getElementById('alertsOnlyButton');
    const estadoFilterText = document.getElementById('estadoFilterText');
    
    // Toggle del filtro de alertas
    currentFilters.alertsOnly = !currentFilters.alertsOnly;
    
    // Si se activa alertas, desactivar filtro de estado
    if (currentFilters.alertsOnly) {
        currentFilters.status = 'all';
        if (estadoFilterText) {
            estadoFilterText.textContent = 'Estado: Todos';
        }
    }
    
    // Actualizar estilo del botón de alertas
    if (alertsButton) {
        if (currentFilters.alertsOnly) {
            alertsButton.classList.add('bg-red-500/20', 'dark:bg-red-500/30');
            alertsButton.classList.remove('bg-red-500/10', 'dark:bg-red-500/20');
        } else {
            alertsButton.classList.remove('bg-red-500/20', 'dark:bg-red-500/30');
            alertsButton.classList.add('bg-red-500/10', 'dark:bg-red-500/20');
        }
    }
    
    // Aplicar filtros
    applyFilters();
}

function renderPagination() {
    const paginationNumbers = document.getElementById('paginationNumbers');
    
    let paginationHTML = '';
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);
    
    if (startPage > 1) {
        paginationHTML += `<a href="${buildPaginationUrl(1)}" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-[#111318] dark:text-white ring-1 ring-inset ring-gray-300 dark:ring-[#3e4556] hover:bg-gray-50 dark:hover:bg-[#333a4a] focus:z-20 focus:outline-offset-0 cursor-pointer">1</a>`;
        if (startPage > 2) {
            paginationHTML += `<span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-400 ring-1 ring-inset ring-gray-300 dark:ring-[#3e4556] focus:outline-offset-0">...</span>`;
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        if (i === currentPage) {
            paginationHTML += `<a aria-current="page" class="relative z-10 inline-flex items-center bg-primary px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 cursor-pointer">${i}</a>`;
        } else {
            paginationHTML += `<a href="${buildPaginationUrl(i)}" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-[#111318] dark:text-white ring-1 ring-inset ring-gray-300 dark:ring-[#3e4556] hover:bg-gray-50 dark:hover:bg-[#333a4a] focus:z-20 focus:outline-offset-0 cursor-pointer">${i}</a>`;
        }
    }
    
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            paginationHTML += `<span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-400 ring-1 ring-inset ring-gray-300 dark:ring-[#3e4556] focus:outline-offset-0">...</span>`;
        }
        paginationHTML += `<a href="${buildPaginationUrl(totalPages)}" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-[#111318] dark:text-white ring-1 ring-inset ring-gray-300 dark:ring-[#3e4556] hover:bg-gray-50 dark:hover:bg-[#333a4a] focus:z-20 focus:outline-offset-0 cursor-pointer">${totalPages}</a>`;
    }
    
    paginationNumbers.innerHTML = paginationHTML;
    
    // Actualizar botones prev/next
    const prevUrl = currentPage > 1 ? buildPaginationUrl(currentPage - 1) : '#';
    const nextUrl = currentPage < totalPages ? buildPaginationUrl(currentPage + 1) : '#';
    document.getElementById('prevPage').href = prevUrl;
    document.getElementById('prevPage').classList.toggle('opacity-50', currentPage === 1);
    document.getElementById('prevPage').classList.toggle('cursor-not-allowed', currentPage === 1);
    document.getElementById('nextPage').href = nextUrl;
    document.getElementById('nextPage').classList.toggle('opacity-50', currentPage === totalPages);
    document.getElementById('nextPage').classList.toggle('cursor-not-allowed', currentPage === totalPages);
}

function buildPaginationUrl(page) {
    const params = new URLSearchParams();
    if (currentFilters.search) params.append('search', currentFilters.search);
    if (currentFilters.tipo !== 'all') params.append('tipo', currentFilters.tipo);
    if (currentFilters.status !== 'all') params.append('estado', currentFilters.status);
    if (currentFilters.alertsOnly) params.append('alerts', '1');
    if (currentFilters.fechaInicio) params.append('fecha_inicio', currentFilters.fechaInicio);
    if (currentFilters.fechaFin) params.append('fecha_fin', currentFilters.fechaFin);
    if (page > 1) params.append('page', page);
    return '?' + params.toString();
}

function changePage(direction) {
    let newPage = currentPage;
    if (direction === 'prev' && currentPage > 1) {
        newPage = currentPage - 1;
    } else if (direction === 'next' && currentPage < totalPages) {
        newPage = currentPage + 1;
    } else if (typeof direction === 'number') {
        newPage = direction;
    }
    
    if (newPage !== currentPage) {
        window.location.href = buildPaginationUrl(newPage);
    }
}

function showDetailsModal(button) {
    const row = button.closest('tr');
    const actor = row.getAttribute('data-actor');
    const action = row.getAttribute('data-action');
    const resource = row.getAttribute('data-resource');
    const status = row.getAttribute('data-status');
    const date = row.getAttribute('data-date');
    const time = row.getAttribute('data-time');
    const ip = row.getAttribute('data-ip');
    
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
                            <p class="text-sm text-[#637588] dark:text-[#9da6b9]">Acción:</p>
                            <p class="text-base font-medium text-[#111318] dark:text-white">${action}</p>
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
    
    setTimeout(() => {
        window.location.reload();
    }, 500);
}

function exportToCsv() {
    // Construir URL para exportar con los mismos filtros
    const params = new URLSearchParams();
    if (currentFilters.search) params.append('search', currentFilters.search);
    if (currentFilters.tipo !== 'all') params.append('tipo', currentFilters.tipo);
    if (currentFilters.status !== 'all') params.append('estado', currentFilters.status);
    if (currentFilters.alertsOnly) params.append('alerts', '1');
    if (currentFilters.fechaInicio) params.append('fecha_inicio', currentFilters.fechaInicio);
    if (currentFilters.fechaFin) params.append('fecha_fin', currentFilters.fechaFin);
    
    const exportUrl = 'api_exportar_auditoria.php' + (params.toString() ? '?' + params.toString() : '');
    const link = document.createElement('a');
    link.href = exportUrl;
    link.download = `auditoria_${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showNotification('CSV exportado correctamente', 'success');
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
</script>
</body></html>

//pagina para ver la auditoria de acciones como administrador despues de iniciar sesion
//Se ve esta pagina para ver la auditoria de acciones de la plataforma y los usuarios.