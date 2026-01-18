<?php
// Habilitar reporte de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
$conn = getDBConnection();

// Lógica de Auditoría (PHP)
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$itemsPerPage = 10;
$offset = ($currentPage - 1) * $itemsPerPage;

// Query robusta
// Query robusta con JOIN a tipos
$query = "SELECT 
            a.id_log,
            a.id_admin,
            COALESCE(t.descripcion, a.accion) as accion,
            a.modulo as recurso,
            a.ip_address,
            a.fecha_hora,
            u.primer_nombre,
            u.apellido_paterno,
            u.email,
            r.nombre_rol
          FROM auditoria_admin a
          LEFT JOIN auditoria_tipos t ON a.id_tipo = t.id_tipo
          LEFT JOIN usuarios u ON a.id_admin = u.id_usuario
          LEFT JOIN roles r ON u.id_rol = r.id_rol
          WHERE 1=1";

$params = [];
$types = '';

if (!empty($searchTerm)) {
    $query .= " AND (a.accion LIKE ? OR a.modulo LIKE ? OR u.email LIKE ? OR CONCAT(IFNULL(u.primer_nombre,''), ' ', IFNULL(u.apellido_paterno,'')) LIKE ?)";
    $like = "%$searchTerm%";
    $params = [$like, $like, $like, $like];
    $types = 'ssss';
}

$query .= " ORDER BY a.fecha_hora DESC LIMIT ? OFFSET ?";
$params[] = $itemsPerPage;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
if ($stmt) {
    if (!empty($params)) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $auditRecords = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
} else {
    $auditRecords = [];
    error_log("Error preparing audit query: " . $conn->error);
}

// Total para paginación
$countQuery = "SELECT COUNT(*) as total FROM auditoria_admin";
$totalRes = $conn->query($countQuery);
$totalItems = $totalRes ? $totalRes->fetch_assoc()['total'] : 0;
$totalPages = ceil($totalItems / $itemsPerPage);

function formatFecha($fecha) {
    return date('d M Y, H:i', strtotime($fecha));
}
?>
<!DOCTYPE html>
<html class="dark" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Auditoría - Sorteos Admin</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script>
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
                    },
                    fontFamily: { "display": ["Inter", "sans-serif"] },
                },
            },
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #111621; }
        ::-webkit-scrollbar-thumb { background: #2a3241; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #3b4657; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white antialiased overflow-hidden">
<div class="flex h-screen w-full">
    <!-- Sidebar -->
    <aside id="sidebar" class="w-64 flex-shrink-0 flex flex-col border-r border-gray-200 dark:border-border-dark bg-white dark:bg-[#151a25] lg:translate-x-0 -translate-x-full lg:static fixed inset-y-0 left-0 z-30 transition-transform duration-300">
        <div class="h-16 flex items-center px-6 border-b border-gray-200 dark:border-border-dark">
            <div class="flex items-center gap-2 text-primary">
                <span class="material-symbols-outlined text-3xl">confirmation_number</span>
                <span class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">Sorteos<span class="text-primary">Admin</span></span>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-2">Principal</p>
            <a class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors group" href="DashboardAdmnistrador.php">
                <span class="material-symbols-outlined group-hover:text-primary transition-colors">dashboard</span> Dashboard
            </a>
            <a class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors group" href="CrudGestionSorteo.php">
                <span class="material-symbols-outlined group-hover:text-primary transition-colors">confirmation_number</span> Gestión de Sorteos
            </a>
            <a class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors group" href="ValidacionPagosAdministrador.php">
                <span class="material-symbols-outlined group-hover:text-primary transition-colors">payments</span> Validación de Pagos
            </a>
             <a class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors group" href="GeneradorGanadoresAdminstradores.php">
                <span class="material-symbols-outlined group-hover:text-primary transition-colors">emoji_events</span> Generación de Ganadores
            </a>
            <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-6">Administración</p>
            <a class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors group" href="GestionUsuariosAdministrador.php">
                <span class="material-symbols-outlined group-hover:text-primary transition-colors">group</span> Usuarios
            </a>
            <!-- Active State for Auditoria -->
            <a class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary/10 text-primary font-medium group" href="AuditoriaAccionesAdmin.php">
                <span class="material-symbols-outlined">settings</span> Auditoría
            </a>
            <a class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors group" href="InformesEstadisticasAdmin.php">
                <span class="material-symbols-outlined group-hover:text-primary transition-colors">analytics</span> Informes
            </a>
        </div>
        <div class="p-4 border-t border-gray-200 dark:border-border-dark relative">
            <div id="admin-user-menu-trigger" class="flex items-center gap-3 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg p-2 transition-colors">
                <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                    <span class="material-symbols-outlined text-gray-500">person</span>
                </div>
                <div class="flex flex-col flex-1">
                    <span class="text-sm font-medium text-slate-900 dark:text-white">Admin</span>
                    <span class="text-xs text-gray-500">admin@sorteos.web</span>
                </div>
                <span class="material-symbols-outlined text-gray-500 text-lg">arrow_drop_down</span>
            </div>
            <!-- Dropdown Menu -->
            <div id="admin-user-menu" class="hidden absolute bottom-full left-0 right-0 mb-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 overflow-hidden">
                <button id="admin-logout-btn" onclick="handleLogoutAdmin()" class="w-full flex items-center gap-3 px-4 py-3 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <span class="material-symbols-outlined text-[20px]">logout</span>
                    <span>Cerrar Sesión</span>
                </button>
            </div>
        </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden bg-background-light dark:bg-background-dark relative">
        <!-- Header -->
        <header class="h-16 flex items-center justify-between px-6 border-b border-gray-200 dark:border-border-dark bg-white dark:bg-[#151a25]/80 backdrop-blur-md sticky top-0 z-20">
            <div class="flex items-center gap-4">
                <button id="mobileMenuToggle" class="lg:hidden text-gray-500 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white hidden sm:block">Auditoría del Sistema</h1>
            </div>
            <div class="flex items-center gap-4">
                <button class="relative p-2 text-gray-500 hover:text-primary transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-white/5">
                    <span class="material-symbols-outlined">notifications</span>
                </button>
            </div>
        </header>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <div class="max-w-[1400px] mx-auto w-full">
                <!-- Breadcrumbs -->
                <div class="flex flex-wrap items-center gap-2 px-4 py-2 mb-4">
                     <button onclick="history.back()" class="text-[#9da6b9] hover:text-white transition-colors text-sm font-medium leading-normal flex items-center gap-1">
                        <span class="material-symbols-outlined !text-lg">arrow_back</span> Atrás
                    </button>
                    <span class="text-[#9da6b9] text-sm font-medium leading-normal">|</span>
                    <a class="text-[#9da6b9] hover:text-white transition-colors text-sm font-medium leading-normal flex items-center gap-1" href="DashboardAdmnistrador.php">
                        <span class="material-symbols-outlined !text-lg">dashboard</span> Dashboard
                    </a>
                    <span class="text-[#9da6b9] text-sm font-medium leading-normal">/</span>
                    <span class="text-white text-sm font-medium leading-normal">Auditoría</span>
                </div>

                <!-- Page Header -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-8">
                    <div class="flex flex-col gap-2">
                        <h1 class="text-3xl md:text-4xl font-black tracking-tight text-slate-900 dark:text-white">Registro de Auditoría</h1>
                        <p class="text-gray-500 dark:text-gray-400 text-base">Monitorea todas las acciones y cambios realizados en la plataforma.</p>
                    </div>
                </div>

                <!-- Search -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6">
                    <div class="md:col-span-5 lg:col-span-4 relative group">
                        <form method="GET" class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary transition-colors">
                                <span class="material-symbols-outlined">search</span>
                            </div>
                            <input name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" class="block w-full pl-10 pr-3 py-3 border border-gray-200 dark:border-border-dark rounded-lg leading-5 bg-white dark:bg-[#1e2433] placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary sm:text-sm transition-shadow text-slate-900 dark:text-white" placeholder="Buscar acción, usuario..." type="text"/>
                        </form>
                    </div>
                </div>

                <!-- Table -->
                <div class="flex-1 bg-white dark:bg-[#151a23] rounded-xl border border-gray-200 dark:border-border-dark overflow-hidden flex flex-col shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-border-dark">
                            <thead class="bg-gray-50 dark:bg-[#111621]">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha / Hora</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Usuario</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acción</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Recurso / Módulo</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">IP</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-border-dark bg-white dark:bg-[#151a23]">
                                <?php if (empty($auditRecords)): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            No se encontraron registros de auditoría.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($auditRecords as $r): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-[#1e2433] transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                            <?php echo formatFecha($r['fecha_hora']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-slate-900 dark:text-white">
                                                <?php echo htmlspecialchars($r['primer_nombre'] . ' ' . $r['apellido_paterno'] ?? ''); ?>
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($r['email'] ?? ''); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                                <?php echo htmlspecialchars($r['accion'] ?? ''); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo htmlspecialchars($r['recurso'] ?? ''); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-500 dark:text-gray-400">
                                            <?php echo htmlspecialchars($r['ip_address'] ?? ''); ?>
                                        </td>
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
</body>
</html>