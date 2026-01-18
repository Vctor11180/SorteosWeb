<?php
// Habilitar reporte de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
$conn = getDBConnection();

// Lógica de Paginación y Búsqueda
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$itemsPerPage = 10;
$offset = ($currentPage - 1) * $itemsPerPage;

// Query para contar total de clientes (Rol 2)
$countQuery = "SELECT COUNT(*) as total FROM usuarios u WHERE u.id_rol = 2";
if (!empty($searchTerm)) {
    $countQuery .= " AND (u.primer_nombre LIKE '%$searchTerm%' OR u.apellido_paterno LIKE '%$searchTerm%' OR u.email LIKE '%$searchTerm%')";
}
$totalRes = $conn->query($countQuery);
$totalItems = $totalRes ? $totalRes->fetch_assoc()['total'] : 0;
$totalPages = ceil($totalItems / $itemsPerPage);

// Query para obtener usuarios clientes
$query = "SELECT u.id_usuario, u.primer_nombre, u.apellido_paterno, u.email, u.telefono, u.estado, u.created_at, r.nombre_rol 
          FROM usuarios u 
          INNER JOIN roles r ON u.id_rol = r.id_rol 
          WHERE u.id_rol = 2";

if (!empty($searchTerm)) {
    $query .= " AND (u.primer_nombre LIKE ? OR u.apellido_paterno LIKE ? OR u.email LIKE ?)";
}

$query .= " ORDER BY u.created_at DESC LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
if ($stmt) {
    if (!empty($searchTerm)) {
        $like = "%$searchTerm%";
        $stmt->bind_param("sssii", $like, $like, $like, $itemsPerPage, $offset);
    } else {
        $stmt->bind_param("ii", $itemsPerPage, $offset);
    }
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $users = [];
    error_log("Error query usuarios: " . $conn->error);
}

?>
<!DOCTYPE html>
<html class="dark" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Usuarios - Sorteos Admin</title>
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
            <!-- Active State for Usuarios -->
            <a class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary/10 text-primary font-medium group" href="GestionUsuariosAdministrador.php">
                <span class="material-symbols-outlined">group</span> Usuarios
            </a>
            <a class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors group" href="AuditoriaAccionesAdmin.php">
                <span class="material-symbols-outlined group-hover:text-primary transition-colors">settings</span> Auditoría
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
                <h1 class="text-xl font-bold text-slate-900 dark:text-white hidden sm:block">Gestión de Usuarios</h1>
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
                    <span class="text-white text-sm font-medium leading-normal">Usuarios</span>
                </div>

                <!-- Page Header -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-8">
                    <div class="flex flex-col gap-2">
                        <h1 class="text-3xl md:text-4xl font-black tracking-tight text-slate-900 dark:text-white">Lista de Clientes</h1>
                        <p class="text-gray-500 dark:text-gray-400 text-base">Administra las cuentas de los usuarios registrados en la plataforma.</p>
                    </div>
                    <button onclick="openCreateUserModal()" class="flex items-center justify-center gap-2 h-11 px-5 bg-primary hover:bg-blue-600 text-white text-sm font-bold rounded-lg shadow-lg shadow-blue-900/20 transition-all active:scale-95">
                        <span class="material-symbols-outlined text-[20px]">person_add</span>
                        <span>Crear Nuevo Usuario</span>
                    </button>
                </div>

                <!-- Search -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6">
                    <div class="md:col-span-5 lg:col-span-4 relative group">
                        <form method="GET" class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary transition-colors">
                                <span class="material-symbols-outlined">search</span>
                            </div>
                            <input name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" class="block w-full pl-10 pr-3 py-3 border border-gray-200 dark:border-border-dark rounded-lg leading-5 bg-white dark:bg-[#1e2433] placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary sm:text-sm transition-shadow text-slate-900 dark:text-white" placeholder="Buscar usuario..." type="text"/>
                        </form>
                    </div>
                </div>

                <!-- Table -->
                <div class="flex-1 bg-white dark:bg-[#151a23] rounded-xl border border-gray-200 dark:border-border-dark overflow-hidden flex flex-col shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-border-dark">
                            <thead class="bg-gray-50 dark:bg-[#111621]">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Usuario</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Contacto</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Registro</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-border-dark bg-white dark:bg-[#151a23]">
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <span class="material-symbols-outlined text-4xl text-gray-300">person_off</span>
                                                <p>No se encontraron usuarios clientes en la base de datos.</p>
                                                <?php /* DEBUG INFO SOLO SI VACIÓ */ ?>
                                                <p class="text-xs text-red-400">Debug: Buscando Rol ID 2 en tabla usuarios.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-[#1e2433] transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                                                    <?php echo strtoupper(substr($user['primer_nombre'] ?? 'U', 0, 1)); ?>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-slate-900 dark:text-white">
                                                        <?php echo htmlspecialchars(($user['primer_nombre'] ?? '') . ' ' . ($user['apellido_paterno'] ?? '')); ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500">ID: <?php echo $user['id_usuario']; ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-300"><?php echo htmlspecialchars($user['email'] ?? ''); ?></div>
                                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($user['telefono'] ?? 'Sin teléfono'); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php 
                                            $estadoClass = match($user['estado']) {
                                                'Activo' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                                'Baneado' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                                'Inactivo' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                            ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $estadoClass; ?>">
                                                <?php echo htmlspecialchars($user['estado']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="DetallesUsuarioAdmin.php?userId=<?php echo $user['id_usuario']; ?>" class="text-primary hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mr-3" title="Ver Detalles">
                                                <span class="material-symbols-outlined text-[20px]">visibility</span>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Info Paginación -->
                <div class="mt-4 flex items-center justify-between">
                    <p class="text-sm text-gray-700 dark:text-gray-400">
                        Total de usuarios: <span class="font-bold"><?php echo $totalItems; ?></span>
                    </p>
                    <div class="flex gap-2">
                        <?php if ($currentPage > 1): ?>
                            <a href="?page=<?php echo $currentPage - 1; ?>&search=<?php echo urlencode($searchTerm); ?>" class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Anterior</a>
                        <?php endif; ?>
                        <?php if ($currentPage < $totalPages): ?>
                            <a href="?page=<?php echo $currentPage + 1; ?>&search=<?php echo urlencode($searchTerm); ?>" class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Siguiente</a>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>

<!-- Modal Crear Usuario -->
<div id="createUserModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeCreateUserModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-[#1c212c] rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-border-dark">
            <div class="bg-white dark:bg-[#1c212c] px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <span class="material-symbols-outlined text-blue-600">person_add</span>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-slate-900 dark:text-white" id="modal-title">Crear Nuevo Usuario</h3>
                        <div class="mt-4">
                            <form id="createUserForm" onsubmit="submitCreateUser(event)" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                                    <input type="text" name="primer_nombre" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary bg-white dark:bg-[#111621] dark:text-white sm:text-sm px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Apellido</label>
                                    <input type="text" name="apellido_paterno" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary bg-white dark:bg-[#111621] dark:text-white sm:text-sm px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                    <input type="email" name="email" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary bg-white dark:bg-[#111621] dark:text-white sm:text-sm px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contraseña</label>
                                    <input type="password" name="password" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary bg-white dark:bg-[#111621] dark:text-white sm:text-sm px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Rol</label>
                                    <select name="rol" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary bg-white dark:bg-[#111621] dark:text-white sm:text-sm px-3 py-2 cursor-pointer">
                                        <option value="2">Cliente</option>
                                        <option value="1">Administrador</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-[#151a23] px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                <button type="button" onclick="document.getElementById('createUserForm').requestSubmit()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                    Crear Usuario
                </button>
                <button type="button" onclick="closeCreateUserModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-[#1c212c] text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Functions
    function openCreateUserModal() {
        document.getElementById('createUserModal').classList.remove('hidden');
    }

    function closeCreateUserModal() {
        document.getElementById('createUserModal').classList.add('hidden');
    }

    async function submitCreateUser(e) {
        e.preventDefault();
        const form = document.getElementById('createUserForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        data.action = 'crear_usuario';

        try {
            const response = await fetch('api_usuarios.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            
            if (result.success) {
                alert('Usuario creado exitosamente');
                window.location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (err) {
            console.error(err);
            alert('Error al conectar con servidor');
        }
    }

    async function toggleStatus(userId, currentStatus) {
        if (!confirm('¿Estás seguro de cambiar el estado de este usuario?')) return;
        
        const newAction = currentStatus === 'Activo' ? 'bloquear_temporal' : 'editar_usuario'; // Simplificado para demo, idealmente action especifica
        // Nota: Por simplicidad, aquí usaremos la API existente de toggleStatus si existiera,
        // o adaptamos a lo que vimos en api_usuarios.php. 
        // Como 'bloquear_temporal' era lo que vimos, usaremos eso para 'banear'.
        
        // Mejor implementación directa:
        try {
             // To be implemented fully based on specific requirements, currently just an alert placeholder
             alert('Funcionalidad de cambio de estado pendiente de conectar con API específica.');
        } catch (e) {
            console.error(e);
        }
    }
</script>
</body>
</html>