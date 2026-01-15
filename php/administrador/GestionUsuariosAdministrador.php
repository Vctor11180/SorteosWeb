<!DOCTYPE html>
<?php
// Conexión a la base de datos
require_once 'config.php';
$conn = getDBConnection();

// Obtener parámetros de filtrado (debe estar antes de usarse en el HTML)
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$estadoFilter = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$ordenFilter = isset($_GET['orden']) ? trim($_GET['orden']) : '';
?>


<html class="dark" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Gestión de Usuarios Admin</title>
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&amp;display=swap" rel="stylesheet"/>
<!-- Material Symbols -->
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<!-- Tailwind CSS -->
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
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary/10 text-primary font-medium" href="GestionUsuariosAdministrador.php">
<span class="material-symbols-outlined">group</span>
                    Usuarios
                </a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="AuditoriaAccionesAdmin.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">settings</span>
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
<h1 class="text-xl font-bold text-slate-900 dark:text-white hidden sm:block">Gestión de Usuarios</h1>
</div>
<div class="flex items-center gap-4">
<div class="relative hidden md:block w-64">
<span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
<span class="material-symbols-outlined text-[20px]">search</span>
</span>
<input id="headerSearchInput" class="w-full bg-gray-100 dark:bg-[#1e2433] border-none rounded-lg py-2 pl-10 pr-4 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary placeholder-gray-500" placeholder="Buscar sorteo, usuario..." type="text"/>
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
<span class="text-white text-sm font-medium leading-normal">Gestión de Usuarios</span>
</div>
<!-- Page Heading -->
<div class="flex flex-wrap justify-between items-end gap-4 px-4 py-6">
<div class="flex min-w-72 flex-col gap-2">
<h1 class="text-white text-3xl md:text-4xl font-black leading-tight tracking-[-0.033em]">Gestión de Usuarios</h1>
<p class="text-[#9da6b9] text-base font-normal leading-normal">Administra los usuarios registrados, sus estados y permisos en la plataforma.</p>
</div>
<div class="flex gap-3">
<button onclick="exportarUsuariosCSV()" class="flex items-center gap-2 bg-primary/10 hover:bg-primary/20 text-primary px-4 py-2.5 rounded-lg font-medium text-sm transition-colors border border-primary/20">
<span class="material-symbols-outlined !text-xl">download</span>
                                Exportar CSV
                            </button>
<button id="btnCrearUsuario" class="flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-primary hover:bg-blue-600 transition-colors text-white text-sm font-bold leading-normal tracking-[0.015em] shadow-lg shadow-blue-900/20">
<span class="material-symbols-outlined mr-2 !text-lg">add</span>
<span class="truncate">Crear Nuevo Usuario</span>
</button>
</div>
</div>
<!-- Filters & Search Toolbar -->
<div class="flex flex-col md:flex-row gap-4 px-4 py-4 bg-surface-dark/50 rounded-xl mb-4 border border-border-dark mx-4">
<div class="flex-1 relative">
<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
<span class="material-symbols-outlined text-[#9da6b9]">search</span>
</div>
<input id="searchInput" value="<?php echo htmlspecialchars($searchTerm); ?>" class="w-full bg-background-dark text-white border border-[#3b4354] rounded-lg pl-10 pr-4 py-2.5 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary placeholder-[#9da6b9] text-sm transition-all" placeholder="Buscar por nombre, email o ID..."/>
</div>
<div class="flex gap-4 w-full md:w-auto">
<div class="relative min-w-[160px] flex-1 md:flex-none">
<select id="estadoFilter" class="w-full bg-background-dark text-white border border-[#3b4354] rounded-lg px-4 py-2.5 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm appearance-none cursor-pointer">
<option value="" <?php echo empty($estadoFilter) ? 'selected' : ''; ?>>Estado: Todos</option>
<option value="active" <?php echo $estadoFilter === 'active' ? 'selected' : ''; ?>>Activo</option>
<option value="inactive" <?php echo $estadoFilter === 'inactive' ? 'selected' : ''; ?>>Inactivo</option>
<option value="pending" <?php echo $estadoFilter === 'pending' ? 'selected' : ''; ?>>Pendiente</option>
</select>
<div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#9da6b9]">
<span class="material-symbols-outlined !text-xl">expand_more</span>
</div>
</div>
<div class="relative min-w-[160px] flex-1 md:flex-none">
<select id="ordenFilter" class="w-full bg-background-dark text-white border border-[#3b4354] rounded-lg px-4 py-2.5 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm appearance-none cursor-pointer">
<option value="" <?php echo empty($ordenFilter) ? 'selected' : ''; ?>>Ordenar por: Recientes</option>
<option value="oldest" <?php echo $ordenFilter === 'oldest' ? 'selected' : ''; ?>>Antiguos</option>
<option value="name_asc" <?php echo $ordenFilter === 'name_asc' ? 'selected' : ''; ?>>Nombre (A-Z)</option>
<option value="name_desc" <?php echo $ordenFilter === 'name_desc' ? 'selected' : ''; ?>>Nombre (Z-A)</option>
</select>
<div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#9da6b9]">
<span class="material-symbols-outlined !text-xl">sort</span>
</div>
</div>
</div>
</div>
<!-- Data Table -->
<div class="px-4 py-2">
<div class="overflow-x-auto rounded-xl border border-border-dark bg-background-dark shadow-xl shadow-black/20">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-dark border-b border-border-dark">
<th class="p-4 text-xs font-medium uppercase tracking-wider text-[#9da6b9] w-[60px] text-center">
<input class="rounded bg-background-dark border-[#3b4354] text-primary focus:ring-offset-background-dark focus:ring-primary" type="checkbox"/>
</th>
<th class="p-4 text-xs font-medium uppercase tracking-wider text-[#9da6b9]">Usuario</th>
<th class="p-4 text-xs font-medium uppercase tracking-wider text-[#9da6b9]">Email / Teléfono</th>
<th class="p-4 text-xs font-medium uppercase tracking-wider text-[#9da6b9]">Registro</th>
<th class="p-4 text-xs font-medium uppercase tracking-wider text-[#9da6b9]">Estado</th>
<th class="p-4 text-xs font-medium uppercase tracking-wider text-[#9da6b9] text-right">Acciones</th>
</tr>
</thead>
<tbody class="divide-y divide-border-dark">
<?php
// Configuración de paginación
$itemsPerPage = 5;
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Las variables de filtrado ya están definidas arriba

// Construir la consulta base
$query = "SELECT u.id_usuario, u.primer_nombre, u.segundo_nombre, u.apellido_paterno, u.apellido_materno, 
                 u.email, u.telefono, u.estado, u.fecha_registro, u.avatar_url, r.nombre_rol
          FROM usuarios u
          INNER JOIN roles r ON u.id_rol = r.id_rol
          WHERE (r.nombre_rol = 'Cliente' OR u.id_rol = 2)";

$params = [];
$types = '';

// Aplicar filtro de búsqueda
if (!empty($searchTerm)) {
    $query .= " AND (CONCAT(u.primer_nombre, ' ', COALESCE(u.segundo_nombre, ''), ' ', u.apellido_paterno, ' ', COALESCE(u.apellido_materno, '')) LIKE ? 
                    OR u.email LIKE ? 
                    OR CAST(u.id_usuario AS CHAR) LIKE ?)";
    $searchParam = '%' . $searchTerm . '%';
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'sss';
}

// Aplicar filtro de estado
if (!empty($estadoFilter)) {
    // Mapear valores del filtro a estados de la BD
    $estadoMap = [
        'active' => 'Activo',
        'inactive' => 'Inactivo',
        'pending' => 'Pendiente'
    ];
    if (isset($estadoMap[$estadoFilter])) {
        $query .= " AND u.estado = ?";
        $params[] = $estadoMap[$estadoFilter];
        $types .= 's';
    }
}

// Aplicar ordenamiento
switch ($ordenFilter) {
    case 'oldest':
        $query .= " ORDER BY u.fecha_registro ASC";
        break;
    case 'name_asc':
        $query .= " ORDER BY u.primer_nombre ASC, u.apellido_paterno ASC";
        break;
    case 'name_desc':
        $query .= " ORDER BY u.primer_nombre DESC, u.apellido_paterno DESC";
        break;
    default:
        $query .= " ORDER BY u.fecha_registro DESC";
        break;
}

// Agregar límite y offset
$query .= " LIMIT ? OFFSET ?";
$params[] = $itemsPerPage;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
if ($stmt && !empty($params)) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else if ($stmt && empty($params)) {
    // Si no hay parámetros, ejecutar directamente
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Fallback: consulta sin paginación preparada (solo si hay error)
    error_log("Error preparando consulta: " . $conn->error);
    $result = false;
    $stmt = null;
}

$usuarios = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
}

if ($stmt) {
    $stmt->close();
}

// Función helper para construir URLs de paginación preservando filtros
function buildPaginationUrl($page) {
    global $searchTerm, $estadoFilter, $ordenFilter;
    $params = [];
    if ($searchTerm) $params['search'] = $searchTerm;
    if ($estadoFilter) $params['estado'] = $estadoFilter;
    if ($ordenFilter) $params['orden'] = $ordenFilter;
    if ($page > 1) $params['page'] = $page;
    return '?' . http_build_query($params);
}

// Contar total de usuarios para paginación (con los mismos filtros)
$countQuery = "SELECT COUNT(*) as total FROM usuarios u INNER JOIN roles r ON u.id_rol = r.id_rol WHERE (r.nombre_rol = 'Cliente' OR u.id_rol = 2)";
$countParams = [];
$countTypes = '';

if (!empty($searchTerm)) {
    $countQuery .= " AND (CONCAT(u.primer_nombre, ' ', COALESCE(u.segundo_nombre, ''), ' ', u.apellido_paterno, ' ', COALESCE(u.apellido_materno, '')) LIKE ? 
                        OR u.email LIKE ? 
                        OR CAST(u.id_usuario AS CHAR) LIKE ?)";
    $searchParam = '%' . $searchTerm . '%';
    $countParams[] = $searchParam;
    $countParams[] = $searchParam;
    $countParams[] = $searchParam;
    $countTypes .= 'sss';
}

if (!empty($estadoFilter)) {
    $estadoMap = [
        'active' => 'Activo',
        'inactive' => 'Inactivo',
        'pending' => 'Pendiente'
    ];
    if (isset($estadoMap[$estadoFilter])) {
        $countQuery .= " AND u.estado = ?";
        $countParams[] = $estadoMap[$estadoFilter];
        $countTypes .= 's';
    }
}

$countStmt = $conn->prepare($countQuery);
if ($countStmt && !empty($countParams)) {
    $countStmt->bind_param($countTypes, ...$countParams);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalUsuarios = $countResult ? $countResult->fetch_assoc()['total'] : 0;
    $countStmt->close();
} else if ($countStmt) {
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalUsuarios = $countResult ? $countResult->fetch_assoc()['total'] : 0;
    $countStmt->close();
} else {
    $countResult = $conn->query($countQuery);
    $totalUsuarios = $countResult ? $countResult->fetch_assoc()['total'] : 0;
}

$totalPages = ceil($totalUsuarios / $itemsPerPage);

// Mostrar usuarios
if (!empty($usuarios)) {
    foreach ($usuarios as $row) {
        $userId = $row['id_usuario'];
        $nombreCompleto = trim(($row['primer_nombre'] ?? '') . ' ' . ($row['segundo_nombre'] ?? '') . ' ' . ($row['apellido_paterno'] ?? '') . ' ' . ($row['apellido_materno'] ?? ''));
        $nombreCompleto = trim($nombreCompleto) ?: 'Sin nombre';
        $iniciales = strtoupper(substr($row['primer_nombre'] ?? 'U', 0, 1) . substr($row['apellido_paterno'] ?? 'U', 0, 1));
        $email = $row['email'] ?? 'Sin email';
        $telefono = !empty($row['telefono']) ? $row['telefono'] : '--';
        $estado = $row['estado'] ?? 'Inactivo';
        $fechaRegistro = !empty($row['fecha_registro']) ? date('d M Y', strtotime($row['fecha_registro'])) : 'N/A';
        $avatarUrl = !empty($row['avatar_url']) ? $row['avatar_url'] : 'https://ui-avatars.com/api/?name=' . urlencode($nombreCompleto) . '&background=2463eb&color=fff';
        
        // Determinar clases de estado
        $estadoClasses = [
            'Activo' => 'bg-green-500/10 text-green-400 border-green-500/20',
            'Inactivo' => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
            'Baneado' => 'bg-red-500/10 text-red-400 border-red-500/20',
            'Pendiente' => 'bg-amber-500/10 text-amber-400 border-amber-500/20'
        ];
        $estadoClass = $estadoClasses[$estado] ?? $estadoClasses['Inactivo'];
        $estadoDot = [
            'Activo' => 'bg-green-400',
            'Inactivo' => 'bg-gray-400',
            'Baneado' => 'bg-red-400',
            'Pendiente' => 'bg-amber-400'
        ];
        $estadoDotClass = $estadoDot[$estado] ?? 'bg-gray-400';
?>
<tr class="hover:bg-surface-dark/50 transition-colors group cursor-pointer" data-user-id="<?php echo $userId; ?>" onclick="window.location.href='DetallesUsuarioAdmin.php?userId=<?php echo $userId; ?>'">
<td class="p-4 text-center" onclick="event.stopPropagation()">
<input class="rounded bg-background-dark border-[#3b4354] text-primary focus:ring-offset-background-dark focus:ring-primary opacity-50 group-hover:opacity-100 transition-opacity" type="checkbox"/>
</td>
<td class="p-4">
<div class="flex items-center gap-3">
<?php if ($row['avatar_url']): ?>
<div class="h-10 w-10 rounded-full bg-cover bg-center border border-border-dark" data-alt="Avatar de <?php echo htmlspecialchars($nombreCompleto); ?>" style='background-image: url("<?php echo htmlspecialchars($avatarUrl); ?>");'></div>
<?php else: ?>
<div class="h-10 w-10 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold border border-primary/20"><?php echo htmlspecialchars($iniciales); ?></div>
<?php endif; ?>
<div class="flex flex-col">
<span class="text-white text-sm font-semibold"><?php echo htmlspecialchars($nombreCompleto); ?></span>
<span class="text-[#9da6b9] text-xs">ID: #<?php echo $userId; ?></span>
</div>
</div>
</td>
<td class="p-4">
<div class="flex flex-col">
<span class="text-[#9da6b9] text-sm"><?php echo htmlspecialchars($email); ?></span>
<span class="text-[#6b7280] text-xs"><?php echo htmlspecialchars($telefono); ?></span>
</div>
</td>
<td class="p-4 text-sm text-[#9da6b9]">
<?php echo $fechaRegistro; ?>
</td>
<td class="p-4">
<span class="inline-flex items-center gap-1.5 rounded-full <?php echo $estadoClass; ?> px-2.5 py-1 text-xs font-medium">
<span class="h-1.5 w-1.5 rounded-full <?php echo $estadoDotClass; ?>"></span>
<?php echo htmlspecialchars($estado); ?>
</span>
</td>
<td class="p-4 text-right">
<div class="flex items-center justify-end gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity" onclick="event.stopPropagation()">
<button class="p-2 text-[#9da6b9] hover:text-white hover:bg-surface-dark rounded-lg transition-colors" title="Editar" onclick="event.stopPropagation(); window.location.href='DetallesUsuarioAdmin.php?userId=<?php echo $userId; ?>'">
<span class="material-symbols-outlined !text-[20px]">edit</span>
</button>
</div>
</td>
</tr>
<?php
    }
} else {
?>
<tr>
<td colspan="6" class="p-8 text-center text-[#9da6b9]">
No hay usuarios clientes registrados.
</td>
</tr>
<?php
}
?>
</tbody>
</table>
</div>
</div>
<!-- Pagination -->
<div class="px-4 py-4 flex flex-col sm:flex-row items-center justify-between gap-4 mt-2">
<p class="text-sm text-[#9da6b9]">
                            <?php
                            $inicio = $totalUsuarios > 0 ? (($currentPage - 1) * $itemsPerPage) + 1 : 0;
                            $fin = min($currentPage * $itemsPerPage, $totalUsuarios);
                            ?>
                            Mostrando <span class="font-medium text-white"><?php echo $inicio; ?></span> a <span class="font-medium text-white"><?php echo $fin; ?></span> de <span class="font-medium text-white"><?php echo $totalUsuarios; ?></span> usuarios
                        </p>
<nav aria-label="Pagination" class="isolate inline-flex -space-x-px rounded-md shadow-sm">
<?php if ($currentPage > 1): ?>
<a class="relative inline-flex items-center rounded-l-md px-2 py-2 text-[#9da6b9] ring-1 ring-inset ring-[#3b4354] hover:bg-surface-dark focus:z-20 focus:outline-offset-0 transition-colors" href="<?php echo htmlspecialchars(buildPaginationUrl($currentPage - 1)); ?>">
<span class="sr-only">Previous</span>
<span class="material-symbols-outlined !text-sm">chevron_left</span>
</a>
<?php else: ?>
<span class="relative inline-flex items-center rounded-l-md px-2 py-2 text-[#6b7280] ring-1 ring-inset ring-[#3b4354] cursor-not-allowed">
<span class="sr-only">Previous</span>
<span class="material-symbols-outlined !text-sm">chevron_left</span>
</span>
<?php endif; ?>

<?php
// Mostrar páginas
$startPage = max(1, $currentPage - 2);
$endPage = min($totalPages, $currentPage + 2);

if ($startPage > 1): ?>
<a class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-[#9da6b9] ring-1 ring-inset ring-[#3b4354] hover:bg-surface-dark focus:z-20 focus:outline-offset-0 transition-colors" href="<?php echo htmlspecialchars(buildPaginationUrl(1)); ?>">1</a>
<?php if ($startPage > 2): ?>
<span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-[#9da6b9] ring-1 ring-inset ring-[#3b4354] focus:outline-offset-0">...</span>
<?php endif; ?>
<?php endif; ?>

<?php for ($i = $startPage; $i <= $endPage; $i++): ?>
<?php if ($i == $currentPage): ?>
<a aria-current="page" class="relative z-10 inline-flex items-center bg-primary px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" href="<?php echo htmlspecialchars(buildPaginationUrl($i)); ?>"><?php echo $i; ?></a>
<?php else: ?>
<a class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-[#9da6b9] ring-1 ring-inset ring-[#3b4354] hover:bg-surface-dark focus:z-20 focus:outline-offset-0 transition-colors" href="<?php echo htmlspecialchars(buildPaginationUrl($i)); ?>"><?php echo $i; ?></a>
<?php endif; ?>
<?php endfor; ?>

<?php if ($endPage < $totalPages): ?>
<?php if ($endPage < $totalPages - 1): ?>
<span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-[#9da6b9] ring-1 ring-inset ring-[#3b4354] focus:outline-offset-0">...</span>
<?php endif; ?>
<a class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-[#9da6b9] ring-1 ring-inset ring-[#3b4354] hover:bg-surface-dark focus:z-20 focus:outline-offset-0 transition-colors" href="<?php echo htmlspecialchars(buildPaginationUrl($totalPages)); ?>"><?php echo $totalPages; ?></a>
<?php endif; ?>

<?php if ($currentPage < $totalPages): ?>
<a class="relative inline-flex items-center rounded-r-md px-2 py-2 text-[#9da6b9] ring-1 ring-inset ring-[#3b4354] hover:bg-surface-dark focus:z-20 focus:outline-offset-0 transition-colors" href="<?php echo htmlspecialchars(buildPaginationUrl($currentPage + 1)); ?>">
<span class="sr-only">Next</span>
<span class="material-symbols-outlined !text-sm">chevron_right</span>
</a>
<?php else: ?>
<span class="relative inline-flex items-center rounded-r-md px-2 py-2 text-[#6b7280] ring-1 ring-inset ring-[#3b4354] cursor-not-allowed">
<span class="sr-only">Next</span>
<span class="material-symbols-outlined !text-sm">chevron_right</span>
</span>
<?php endif; ?>
</nav>
</div>
</div>
</main>
</div>
<script>
/**
 * GESTIÓN DE USUARIOS ADMINISTRADOR - Funcionalidades JavaScript
 * Los datos se obtienen directamente de la base de datos mediante PHP
 */

// ========== INICIALIZACIÓN ==========
document.addEventListener('DOMContentLoaded', function() {
    const btnCrearUsuario = document.getElementById('btnCrearUsuario');
    if (btnCrearUsuario) {
        btnCrearUsuario.addEventListener('click', showCreateUserModal);
    }
    
    const selectAllCheckbox = document.querySelector('thead input[type="checkbox"]');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', toggleSelectAllUsers);
    }
    
    // Conectar event listeners para filtros
    const searchInput = document.getElementById('searchInput');
    const estadoFilter = document.getElementById('estadoFilter');
    const ordenFilter = document.getElementById('ordenFilter');
    
    // Búsqueda con debounce
    let searchTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                filterUsers();
            }, 500); // Esperar 500ms después de que el usuario deje de escribir
        });
    }
    
    // Filtro de estado
    if (estadoFilter) {
        estadoFilter.addEventListener('change', filterUsers);
    }
    
    // Filtro de ordenamiento
    if (ordenFilter) {
        ordenFilter.addEventListener('change', filterUsers);
    }
});

// ========== CREAR USUARIO ==========
/**
 * Muestra modal para crear nuevo usuario
 */
function showCreateUserModal() {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-50 overflow-y-auto';
    modal.onclick = function(e) { if (e.target === modal) modal.remove(); };
    
    modal.innerHTML = `
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm"></div>
            <div class="inline-block align-bottom bg-white dark:bg-[#1c212c] rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-border-dark">
                <div class="px-4 pt-5 pb-4 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-slate-900 dark:text-white mb-4">Crear Nuevo Usuario</h3>
                    <form onsubmit="createUser(event)" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre Completo <span class="text-red-500">*</span></label>
                            <input type="text" name="nombre" required class="w-full bg-white dark:bg-[#111621] border border-gray-300 dark:border-[#3b4354] rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary" minlength="3" maxlength="100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" required class="w-full bg-white dark:bg-[#111621] border border-gray-300 dark:border-[#3b4354] rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Teléfono</label>
                            <input type="tel" name="telefono" class="w-full bg-white dark:bg-[#111621] border border-gray-300 dark:border-[#3b4354] rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary" pattern="[\d\s\-\+\(\)]+" minlength="8">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado Inicial</label>
                            <select name="estado" class="w-full bg-white dark:bg-[#111621] border border-gray-300 dark:border-[#3b4354] rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary">
                                <option value="active">Activo</option>
                                <option value="inactive">Inactivo</option>
                                <option value="pending">Pendiente</option>
                            </select>
                        </div>
                        <div class="flex justify-end gap-2 pt-4">
                            <button type="button" onclick="this.closest('.fixed').remove()" class="px-4 py-2 bg-gray-200 dark:bg-[#282d39] text-gray-700 dark:text-white rounded-lg hover:bg-gray-300 dark:hover:bg-[#3b4354] text-sm font-medium">
                                Cancelar
                            </button>
                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-600 text-sm font-medium">
                                Crear Usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

/**
 * Crea un nuevo usuario
 * @param {Event} event - Evento del formulario
 */
function createUser(event) {
    event.preventDefault();
    
    // Validar formulario
    if (!validarFormularioUsuario(event.target)) {
        return;
    }
    
    const formData = new FormData(event.target);
    
    // Enviar datos al servidor
    fetch('api_crear_usuario.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            event.target.closest('.fixed').remove();
            showNotification('Usuario creado exitosamente', 'success');
            // Recargar la página para mostrar el nuevo usuario
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            mostrarErrorCampo(event.target.querySelector('input[name="email"]'), data.message || 'Error al crear usuario');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al crear usuario', 'error');
    });
}

/**
 * Valida el formulario de usuario
 */
function validarFormularioUsuario(form) {
    let esValido = true;
    
    // Limpiar errores previos
    const formInputs = form.querySelectorAll('input, select');
    formInputs.forEach(input => {
        limpiarErrorCampo(input);
    });
    
    // Validar nombre
    const nombreInput = form.querySelector('input[name="nombre"]');
    const nombre = nombreInput.value.trim();
    if (!nombre || nombre.length < 3) {
        mostrarErrorCampo(nombreInput, 'El nombre debe tener al menos 3 caracteres');
        esValido = false;
    } else if (nombre.length > 100) {
        mostrarErrorCampo(nombreInput, 'El nombre no puede exceder 100 caracteres');
        esValido = false;
    }
    
    // Validar email
    const emailInput = form.querySelector('input[name="email"]');
    const email = emailInput.value.trim().toLowerCase();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email) {
        mostrarErrorCampo(emailInput, 'El email es requerido');
        esValido = false;
    } else if (!emailRegex.test(email)) {
        mostrarErrorCampo(emailInput, 'El formato del email no es válido');
        esValido = false;
    }
    
    // Validar teléfono (opcional)
    const telefonoInput = form.querySelector('input[name="telefono"]');
    const telefono = telefonoInput?.value.trim();
    if (telefono && telefono.length > 0) {
        const telefonoRegex = /^[\d\s\-\+\(\)]+$/;
        if (!telefonoRegex.test(telefono) || telefono.length < 8) {
            mostrarErrorCampo(telefonoInput, 'El formato del teléfono no es válido');
            esValido = false;
        }
    }
    
    // Validar estado
    const estadoInput = form.querySelector('select[name="estado"]');
    const estado = estadoInput.value;
    if (!estado) {
        mostrarErrorCampo(estadoInput, 'El estado es requerido');
        esValido = false;
    }
    
    if (!esValido) {
        showNotification('Por favor corrige los errores en el formulario', 'error');
    }
    
    return esValido;
}

/**
 * Muestra un error en un campo específico
 */
function mostrarErrorCampo(campo, mensaje) {
    if (!campo) return;
    
    // Agregar clase de error
    campo.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
    campo.classList.remove('border-gray-300', 'dark:border-[#3b4354]', 'focus:ring-primary');
    
    // Crear o actualizar mensaje de error
    let errorMsg = campo.parentElement.querySelector('.error-message');
    if (!errorMsg) {
        errorMsg = document.createElement('p');
        errorMsg.className = 'error-message text-red-500 text-xs mt-1';
        campo.parentElement.appendChild(errorMsg);
    }
    errorMsg.textContent = mensaje;
}

/**
 * Limpia el error de un campo
 */
function limpiarErrorCampo(campo) {
    if (!campo) return;
    
    campo.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
    campo.classList.add('border-gray-300', 'dark:border-[#3b4354]', 'focus:ring-primary');
    
    const errorMsg = campo.parentElement.querySelector('.error-message');
    if (errorMsg) {
        errorMsg.remove();
    }
}

// ========== FILTRAR Y BUSCAR ==========
/**
 * Filtra usuarios mediante recarga de página con parámetros GET
 */
function filterUsers() {
    const searchTerm = document.getElementById('searchInput')?.value || '';
    const estadoFilter = document.getElementById('estadoFilter')?.value || '';
    const ordenFilter = document.getElementById('ordenFilter')?.value || '';
    
    const params = new URLSearchParams();
    if (searchTerm) params.append('search', searchTerm);
    if (estadoFilter) params.append('estado', estadoFilter);
    if (ordenFilter) params.append('orden', ordenFilter);
    // Resetear a página 1 cuando se aplican filtros
    // params.append('page', '1');
    
    window.location.href = '?' + params.toString();
}

// ========== SELECCIÓN MÚLTIPLE ==========
/**
 * Selecciona/deselecciona todos los usuarios
 */
function toggleSelectAllUsers() {
    const selectAllCheckbox = document.querySelector('thead input[type="checkbox"]');
    const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
    
    checkboxes.forEach(cb => {
        cb.checked = selectAllCheckbox.checked;
    });
}

// La tabla se renderiza directamente desde PHP, no se necesita renderTable()

// ========== EDITAR USUARIO ==========
/**
 * Redirige a la página de edición del usuario
 * @param {string} userId - ID del usuario
 * @param {string} userName - Nombre del usuario
 */
function editUser(userId, userName) {
    window.location.href = `DetallesUsuarioAdmin.php?userId=${userId}`;
}

// ========== CAMBIAR ESTADO ==========
/**
 * Cambia el estado del usuario (activo/inactivo)
 * @param {string} userId - ID del usuario
 * @param {string} userName - Nombre del usuario
 * @param {string} currentStatus - Estado actual
 */
async function toggleUserStatus(userId, userName, currentStatus) {
    const newStatus = currentStatus === 'Activo' ? 'Inactivo' : 'Activo';
    const action = newStatus === 'Activo' ? 'activar' : 'desactivar';
    
    const confirmado = await mostrarModalConfirmacion(
        `¿Deseas ${action} la cuenta de ${userName}?`,
        `Confirmar ${action === 'activar' ? 'activación' : 'desactivación'}`,
        'info'
    );
    
    if (!confirmado) {
        return;
    }
    
    // Llamada API para cambiar estado
    fetch('api_usuarios.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            action: 'toggleStatus', 
            userId: userId, 
            newStatus: newStatus 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`Usuario ${action}do exitosamente`, 'success');
            // Recargar la página para reflejar el cambio
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Error al cambiar el estado', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al comunicarse con el servidor', 'error');
    });
}

// ========== NOTIFICACIONES ==========
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

// ========== EXPORTACIÓN DE DATOS ==========

/**
 * Exporta los usuarios a CSV desde la base de datos
 */
function exportarUsuariosCSV() {
    // Obtener parámetros de filtro actuales
    const params = new URLSearchParams(window.location.search);
    const search = params.get('search') || '';
    const estado = params.get('estado') || '';
    
    // Construir URL para exportar
    const exportUrl = 'api_exportar_usuarios.php';
    const exportParams = new URLSearchParams();
    if (search) exportParams.append('search', search);
    if (estado) exportParams.append('estado', estado);
    
    const url = exportParams.toString() ? `${exportUrl}?${exportParams.toString()}` : exportUrl;
    
    // Descargar CSV
    const link = document.createElement('a');
    link.href = url;
    link.download = `usuarios_${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showNotification('Exportando usuarios...', 'info');
}

// ========== NAVEGACIÓN CON HISTORIAL Y CONTEXTUAL ==========

/**
 * Navega hacia atrás usando el historial del navegador
 * Si no hay historial, redirige a la página padre según el contexto
 */
function navegarAtras() {
    try {
        // Intentar usar el historial del navegador
        if (window.history.length > 1) {
            window.history.back();
        } else {
            // Fallback: redirigir a Dashboard
            window.location.href = 'DashboardAdmnistrador.php';
        }
    } catch (error) {
        console.error('Error al navegar atrás:', error);
        window.location.href = 'DashboardAdmnistrador.php';
    }
}

/**
 * Navega a una página de detalle guardando el contexto de origen
 * @param {string} url - URL de destino
 * @param {string} origen - Página de origen (opcional)
 */
function navegarConContexto(url, origen = null) {
    try {
        // Guardar la página actual como origen si no se especifica
        const paginaOrigen = origen || window.location.pathname.split('/').pop();
        
        // Agregar parámetro de origen a la URL
        const separador = url.includes('?') ? '&' : '?';
        const urlConContexto = `${url}${separador}origen=${encodeURIComponent(paginaOrigen)}`;
        
        // Guardar en sessionStorage para referencia
        sessionStorage.setItem('ultimaPagina', paginaOrigen);
        
        window.location.href = urlConContexto;
    } catch (error) {
        console.error('Error al navegar con contexto:', error);
        window.location.href = url;
    }
}

/**
 * Obtiene la página de origen desde los parámetros o sessionStorage
 * @returns {string|null} Página de origen o null
 */
function obtenerPaginaOrigen() {
    try {
        // Buscar en parámetros de URL
        const params = new URLSearchParams(window.location.search);
        const origen = params.get('origen');
        if (origen) {
            return decodeURIComponent(origen);
        }
        
        // Buscar en sessionStorage
        const ultimaPagina = sessionStorage.getItem('ultimaPagina');
        if (ultimaPagina) {
            return ultimaPagina;
        }
        
        return null;
    } catch (error) {
        console.error('Error al obtener página de origen:', error);
        return null;
    }
}

// Mejorar navegación a detalles de usuario
document.addEventListener('DOMContentLoaded', function() {
    // Interceptar clics en enlaces a DetallesUsuarioAdmin
    document.querySelectorAll('a[href*="DetallesUsuarioAdmin"], button[onclick*="DetallesUsuarioAdmin"]').forEach(elemento => {
        elemento.addEventListener('click', function(e) {
            const href = this.getAttribute('href') || this.getAttribute('onclick');
            if (href && href.includes('DetallesUsuarioAdmin')) {
                // Extraer URL completa
                const match = href.match(/DetallesUsuarioAdmin\.php[^'"]*/);
                if (match) {
                    e.preventDefault();
                    navegarConContexto(match[0], 'GestionUsuariosAdministrador.php');
                }
            }
    });
    });
    
    // Mejorar onclick handlers
    document.querySelectorAll('[onclick*="DetallesUsuarioAdmin"]').forEach(btn => {
        const onclickOriginal = btn.getAttribute('onclick');
        if (onclickOriginal) {
            btn.removeAttribute('onclick');
            btn.addEventListener('click', function() {
                const match = onclickOriginal.match(/DetallesUsuarioAdmin\.php[^'"]*/);
                if (match) {
                    navegarConContexto(match[0], 'GestionUsuariosAdministrador.php');
                } else {
                    eval(onclickOriginal);
                }
            });
        }
    });
});
</script>
</body></html>