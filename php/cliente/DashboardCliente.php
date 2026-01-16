<?php
/**
 * DashboardCliente
 * Sistema de Sorteos Web
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación para páginas protegidas
$protectedPages = ['DashboardCliente', 'AjustesPefilCliente', 'MisBoletosCliente', 'MisGanancias', 'SeleccionBoletos', 'SorteoClienteDetalles', 'FinalizarPagoBoletos'];
if (in_array('DashboardCliente', $protectedPages) && (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true)) {
    header('Location: InicioSesion.php');
    exit;
}

// Obtener datos del usuario desde la base de datos
require_once __DIR__ . '/includes/user-data.php';
$datosUsuario = obtenerDatosUsuarioCompletos();

// Si no se pueden obtener los datos, redirigir al login
if (!$datosUsuario) {
    header('Location: InicioSesion.php');
    exit;
}

// Variables para usar en el HTML
$usuarioNombre = $datosUsuario['nombre'];
$usuarioEmail = $datosUsuario['email'];
$usuarioSaldo = $datosUsuario['saldo'];
$usuarioAvatar = $datosUsuario['avatar'];
$tipoUsuario = $datosUsuario['tipoUsuario'];
$usuarioId = $datosUsuario['id_usuario'];

// Obtener sorteos activos desde la base de datos (máximo 4 para el dashboard)
require_once __DIR__ . '/includes/sorteos-data.php';
$sorteosActivos = obtenerSorteosActivos(4);

// Obtener boletos activos del cliente para el dashboard
require_once __DIR__ . '/config/database.php';
$db = getDB();

// Función para obtener boletos activos del cliente (limitado para el dashboard)
function obtenerBoletosActivosDashboard($db, $usuarioId, $limite = 5) {
    try {
        // Obtener boletos del usuario que están en sorteos activos o finalizados recientemente
        $stmt = $db->prepare("
            SELECT 
                b.id_boleto,
                b.numero_boleto,
                b.estado as estado_boleto,
                b.fecha_reserva,
                s.id_sorteo,
                s.titulo as sorteo_titulo,
                s.imagen_url as sorteo_imagen,
                s.fecha_fin as sorteo_fecha_fin,
                s.estado as sorteo_estado,
                t.id_transaccion,
                t.estado_pago,
                t.fecha_creacion as transaccion_fecha,
                g.id_boleto as es_ganador
            FROM boletos b
            INNER JOIN sorteos s ON b.id_sorteo = s.id_sorteo
            LEFT JOIN detalle_transaccion_boletos dtb ON b.id_boleto = dtb.id_boleto
            LEFT JOIN transacciones t ON dtb.id_transaccion = t.id_transaccion
            LEFT JOIN ganadores g ON b.id_boleto = g.id_boleto AND s.id_sorteo = g.id_sorteo
            WHERE b.id_usuario_actual = :usuario_id
            AND b.estado IN ('Reservado', 'Vendido')
            AND s.estado IN ('Activo', 'Finalizado')
            ORDER BY t.fecha_creacion DESC, s.fecha_fin DESC, b.numero_boleto ASC
            LIMIT :limite
        ");
        
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        $boletos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Procesar boletos para formatear datos
        $boletosProcesados = [];
        foreach ($boletos as $boleto) {
            // Determinar estado del boleto
            $estadoDisplay = 'pending';
            $estadoTexto = 'Pendiente';
            $estadoColor = 'yellow';
            
            if ($boleto['estado_boleto'] === 'Vendido') {
                if ($boleto['es_ganador']) {
                    $estadoDisplay = 'winner';
                    $estadoTexto = 'Ganador';
                    $estadoColor = 'amber';
                } else if ($boleto['estado_pago'] === 'Completado') {
                    $estadoDisplay = 'approved';
                    $estadoTexto = 'Aprobado';
                    $estadoColor = 'green';
                } else if ($boleto['estado_pago'] === 'Fallido') {
                    $estadoDisplay = 'rejected';
                    $estadoTexto = 'Rechazado';
                    $estadoColor = 'red';
                } else {
                    $estadoDisplay = 'pending';
                    $estadoTexto = 'Pendiente';
                    $estadoColor = 'yellow';
                }
            } else if ($boleto['estado_boleto'] === 'Reservado') {
                $estadoDisplay = 'pending';
                $estadoTexto = 'Pendiente';
                $estadoColor = 'yellow';
            }
            
            // Formatear fecha del sorteo
            $fechaSorteoDisplay = '';
            if ($boleto['sorteo_fecha_fin']) {
                $fechaSorteo = new DateTime($boleto['sorteo_fecha_fin']);
                $fechaSorteoDisplay = $fechaSorteo->format('d M, Y');
            }
            
            // URL de imagen del sorteo
            $imagenSorteo = $boleto['sorteo_imagen'] ?? 'https://via.placeholder.com/150';
            
            $boletosProcesados[] = [
                'id_boleto' => $boleto['id_boleto'],
                'numero_boleto' => $boleto['numero_boleto'],
                'numero_boleto_int' => intval($boleto['numero_boleto']),
                'estado_display' => $estadoDisplay,
                'estado_texto' => $estadoTexto,
                'estado_color' => $estadoColor,
                'id_sorteo' => $boleto['id_sorteo'],
                'sorteo_titulo' => $boleto['sorteo_titulo'],
                'sorteo_imagen' => $imagenSorteo,
                'sorteo_fecha_fin' => $fechaSorteoDisplay,
                'sorteo_estado' => $boleto['sorteo_estado'],
                'es_ganador' => !empty($boleto['es_ganador'])
            ];
        }
        
        return $boletosProcesados;
        
    } catch (PDOException $e) {
        error_log("Error al obtener boletos activos del dashboard: " . $e->getMessage());
        return [];
    } catch (Exception $e) {
        error_log("Error general al obtener boletos activos del dashboard: " . $e->getMessage());
        return [];
    }
}

// Obtener boletos activos para el dashboard (máximo 5)
$boletosActivosDashboard = obtenerBoletosActivosDashboard($db, $usuarioId, 5);

// Contar total de boletos activos para estadísticas
function contarBoletosActivosTotal($db, $usuarioId) {
    try {
        $stmt = $db->prepare("
            SELECT COUNT(*) as total
            FROM boletos b
            INNER JOIN sorteos s ON b.id_sorteo = s.id_sorteo
            WHERE b.id_usuario_actual = :usuario_id
            AND b.estado IN ('Reservado', 'Vendido')
            AND s.estado IN ('Activo', 'Finalizado')
        ");
        
        $stmt->execute([':usuario_id' => $usuarioId]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return intval($resultado['total'] ?? 0);
    } catch (PDOException $e) {
        error_log("Error al contar boletos activos: " . $e->getMessage());
        return 0;
    }
}

$totalBoletosActivos = contarBoletosActivosTotal($db, $usuarioId);
?>
<!DOCTYPE html>

<html class="dark" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Dashboard Cliente - Sorteos Web</title>
<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
<!-- Material Icons -->
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#2463eb",
                        "background-light": "#f6f6f8",
                        "background-dark": "#111318", // Matching the component's dark bg
                        "card-dark": "#282d39", // Matching the component's card bg
                        "text-secondary": "#9da6b9",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.5rem",
                        "lg": "0.75rem",
                        "xl": "1rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
<style>
        /* Custom Scrollbar for dark theme */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #111318; 
        }
        ::-webkit-scrollbar-thumb {
            background: #282d39; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #3b4254; 
        }
        
        /* Transiciones suaves para contadores regresivos */
        [id*="countdown"] {
            transition: opacity 0.3s ease, color 0.2s ease;
            display: inline-block;
        }
        
        /* Estado cuando el contador termina */
        [id*="countdown"].opacity-50 {
            opacity: 0.5;
        }
        
        /* Ocultar scrollbar pero mantener funcionalidad */
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        
        /* Estilos para menú contextual */
        .ticket-context-menu {
            animation: slideDown 0.2s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Panel de notificaciones en header */
        #notifications-panel {
            max-height: 500px;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-white font-display overflow-hidden h-screen flex">
<!-- Sidebar -->
<aside class="w-72 hidden lg:flex flex-col border-r border-[#282d39]/50 bg-gradient-to-b from-[#111318] to-[#151a23] h-full shadow-2xl shadow-black/20">
<div class="p-6 pb-2">
<div class="flex items-center gap-3 mb-8">
<div class="size-8 text-primary">
<svg class="w-full h-full" fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
<path clip-rule="evenodd" d="M39.475 21.6262C40.358 21.4363 40.6863 21.5589 40.7581 21.5934C40.7876 21.655 40.8547 21.857 40.8082 22.3336C40.7408 23.0255 40.4502 24.0046 39.8572 25.2301C38.6799 27.6631 36.5085 30.6631 33.5858 33.5858C30.6631 36.5085 27.6632 38.6799 25.2301 39.8572C24.0046 40.4502 23.0255 40.7407 22.3336 40.8082C21.8571 40.8547 21.6551 40.7875 21.5934 40.7581C21.5589 40.6863 21.4363 40.358 21.6262 39.475C21.8562 38.4054 22.4689 36.9657 23.5038 35.2817C24.7575 33.2417 26.5497 30.9744 28.7621 28.762C30.9744 26.5497 33.2417 24.7574 35.2817 23.5037C36.9657 22.4689 38.4054 21.8562 39.475 21.6262ZM4.41189 29.2403L18.7597 43.5881C19.8813 44.7097 21.4027 44.9179 22.7217 44.7893C24.0585 44.659 25.5148 44.1631 26.9723 43.4579C29.9052 42.0387 33.2618 39.5667 36.4142 36.4142C39.5667 33.2618 42.0387 29.9052 43.4579 26.9723C44.1631 25.5148 44.659 24.0585 44.7893 22.7217C44.9179 21.4027 44.7097 19.8813 43.5881 18.7597L29.2403 4.41187C27.8527 3.02428 25.8765 3.02573 24.2861 3.36776C22.6081 3.72863 20.7334 4.58419 18.8396 5.74801C16.4978 7.18716 13.9881 9.18353 11.5858 11.5858C9.18354 13.988 7.18717 16.4978 5.74802 18.8396C4.58421 20.7334 3.72865 22.6081 3.36778 24.2861C3.02574 25.8765 3.02429 27.8527 4.41189 29.2403Z" fill="currentColor" fill-rule="evenodd"></path>
</svg>
</div>
<h2 class="text-white text-xl font-bold tracking-tight">Sorteos Web</h2>
</div>
<!-- User Mini Profile -->
<div class="flex items-center gap-3 p-4 rounded-xl bg-gradient-to-br from-card-dark/80 to-[#151a23] mb-6 border border-[#282d39]/50 shadow-lg">
<div class="relative">
<div id="sidebar-user-avatar" class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-12 ring-2 ring-primary/30 ring-offset-2 ring-offset-[#111318] shadow-lg" data-alt="User profile picture" style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuAscTJ1Xcq7edw4JqzzGbgOvjdyQ9_nDg7kkxtlCQw51-EJsv1RJyDd9OAZC89eniVl2ujzIik6wgxd5FTvho_ak6ccsWrWelinVwXj6yQUdpPUXYUTJN0pSvhRh-smWf81cMQz40x4U3setrSFDsyX4KkfxOsHc6PnTND68lGw6JkA9B0ag_4fNu5s0Z9OMbq83llAZUv3xuo3s6VI1no110ozE88mRALnX-rhgavHoJxmYpvBcUxV7BtrJr_9Q0BlgvZQL2BXCFg");'>
</div>
<div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-[#111318] shadow-lg"></div>
</div>
<div class="flex flex-col overflow-hidden">
<h1 id="sidebar-user-name" class="text-white text-sm font-bold truncate tracking-tight"><?php echo htmlspecialchars($usuarioNombre); ?></h1>
<p id="sidebar-user-type" class="text-primary/80 text-xs font-medium truncate"><?php echo htmlspecialchars($tipoUsuario); ?></p>
</div>
</div>
<!-- Navigation -->
<nav class="flex flex-col gap-2">
<a id="nav-dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-gradient-to-r from-primary to-blue-600 text-white shadow-lg shadow-primary/20 transition-all duration-200 hover:shadow-xl hover:shadow-primary/30" href="DashboardCliente.php">
<span class="material-symbols-outlined text-xl">dashboard</span>
<p class="text-sm font-bold">Dashboard</p>
</a>
<a id="nav-sorteos" class="flex items-center gap-3 px-4 py-3 rounded-xl text-[#9da6b9] hover:bg-gradient-to-r hover:from-[#282d39]/80 hover:to-[#323846]/50 hover:text-white transition-all duration-200 hover:shadow-md" href="ListadoSorteosActivos.php">
<span class="material-symbols-outlined text-xl">local_activity</span>
<p class="text-sm font-medium">Sorteos</p>
</a>
<a id="nav-boletos" class="flex items-center gap-3 px-4 py-3 rounded-xl text-[#9da6b9] hover:bg-gradient-to-r hover:from-[#282d39]/80 hover:to-[#323846]/50 hover:text-white transition-all duration-200 hover:shadow-md" href="MisBoletosCliente.php">
<span class="material-symbols-outlined text-xl">confirmation_number</span>
<p class="text-sm font-medium">Mis Boletos</p>
</a>
<a id="nav-ganadores" class="flex items-center gap-3 px-4 py-3 rounded-xl text-[#9da6b9] hover:bg-gradient-to-r hover:from-[#282d39]/80 hover:to-[#323846]/50 hover:text-white transition-all duration-200 hover:shadow-md" href="MisGanancias.php">
<span class="material-symbols-outlined text-xl">emoji_events</span>
<p class="text-sm font-medium">Ganadores</p>
</a>
<a id="nav-perfil" class="flex items-center gap-3 px-4 py-3 rounded-xl text-[#9da6b9] hover:bg-gradient-to-r hover:from-[#282d39]/80 hover:to-[#323846]/50 hover:text-white transition-all duration-200 hover:shadow-md" href="AjustesPefilCliente.php">
<span class="material-symbols-outlined text-xl">person</span>
<p class="text-sm font-medium">Perfil</p>
</a>
<a id="nav-soporte" class="flex items-center gap-3 px-4 py-3 rounded-xl text-[#9da6b9] hover:bg-gradient-to-r hover:from-[#282d39]/80 hover:to-[#323846]/50 hover:text-white transition-all duration-200 hover:shadow-md" href="ContactoSoporteCliente.php">
<span class="material-symbols-outlined text-xl">support_agent</span>
<p class="text-sm font-medium">Soporte</p>
</a>
</nav>
</div>
<div class="mt-auto p-6">
<button id="logout-btn" class="flex w-full items-center justify-center gap-2 rounded-xl h-11 px-4 bg-gradient-to-r from-[#282d39] to-[#323846] hover:from-[#323846] hover:to-[#3b4254] text-[#9da6b9] hover:text-white text-sm font-bold transition-all duration-200 border border-[#3e4552]/50 shadow-lg hover:shadow-xl">
<span class="material-symbols-outlined text-[20px]">logout</span>
<span>Cerrar Sesión</span>
</button>
</div>
</aside>
<!-- Mobile Menu Container -->
<div id="client-mobile-menu-container"></div>
<!-- Main Content -->
<main class="flex-1 flex flex-col min-w-0 bg-[#111318]">
<!-- Top Header -->
<header class="h-16 flex items-center justify-between px-6 lg:px-10 border-b border-[#282d39]/50 bg-gradient-to-r from-[#111318] via-[#151a23] to-[#111318] backdrop-blur-sm sticky top-0 z-20 shadow-lg shadow-black/10">
<!-- Mobile Menu Toggle (Visible only on small screens) -->
<button id="mobile-menu-toggle" class="lg:hidden text-white mr-4" aria-label="Abrir menú de navegación">
<span class="material-symbols-outlined">menu</span>
</button>
<!-- Search Bar -->
<div class="hidden md:flex max-w-md w-full">
<div class="relative w-full">
<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-secondary">
<span class="material-symbols-outlined">search</span>
</div>
<input id="search-input" class="block w-full pl-10 pr-3 py-2.5 border border-[#282d39]/50 rounded-xl leading-5 bg-gradient-to-r from-[#0d1117] to-[#151a23] text-white placeholder-[#566074] focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary sm:text-sm transition-all duration-200 shadow-inner" placeholder="Buscar sorteos, boletos..." type="text"/>
</div>
</div>
<!-- Right Actions -->
<div class="flex items-center gap-4 ml-auto relative">
<button id="deposit-funds-btn" class="flex items-center justify-center h-10 px-5 bg-gradient-to-r from-primary to-blue-600 hover:from-blue-600 hover:to-primary text-white text-sm font-bold rounded-xl transition-all duration-200 shadow-xl shadow-primary/30 hover:shadow-2xl hover:shadow-primary/40 transform hover:-translate-y-0.5 active:translate-y-0">
<span class="hidden sm:inline">Depositar Fondos</span>
<span class="sm:hidden">+</span>
</button>
<div class="h-6 w-px bg-[#282d39] mx-2"></div>
<button id="notifications-btn" class="relative flex items-center justify-center size-10 rounded-xl bg-gradient-to-br from-[#282d39] to-[#323846] hover:from-[#323846] hover:to-[#3b4254] text-white transition-all duration-200 shadow-md hover:shadow-lg">
<span class="material-symbols-outlined">notifications</span>
<span id="notifications-badge" class="absolute top-2.5 right-2.5 size-2.5 bg-red-500 rounded-full border-2 border-[#111318] shadow-lg"></span>
</button>
<!-- Notifications Dropdown Panel -->
<div id="notifications-panel" class="absolute top-full right-0 mt-2 w-80 bg-gradient-to-br from-card-dark to-[#151a23] rounded-2xl border border-[#282d39]/50 shadow-2xl shadow-black/30 backdrop-blur-sm z-50 hidden max-h-96 overflow-y-auto">
<div class="p-4 border-b border-[#282d39] flex items-center justify-between">
<h3 class="text-white font-bold text-sm">Notificaciones</h3>
<button id="close-notifications-panel" class="text-text-secondary hover:text-white transition-colors">
<span class="material-symbols-outlined text-[20px]">close</span>
</button>
</div>
<div id="notifications-list" class="p-2">
<!-- Las notificaciones se mostrarán aquí -->
</div>
</div>
<div class="flex items-center gap-3 pl-2">
<div class="text-right hidden sm:block">
<p class="text-xs text-text-secondary">Saldo Actual</p>
<p id="user-balance" class="text-sm font-bold text-[#0bda62]">$1,250.00</p>
</div>
</div>
</div>
</header>
<!-- Scrollable Content Area -->
<div class="flex-1 overflow-y-auto overflow-x-hidden p-6 lg:p-10 space-y-8">
<!-- Hero Section -->
<?php if (!empty($sorteosActivos)): 
    $sorteoHero = $sorteosActivos[0]; // Primer sorteo para el hero
    $imagenHero = !empty($sorteoHero['imagen_url']) 
        ? htmlspecialchars($sorteoHero['imagen_url']) 
        : 'https://via.placeholder.com/1200x400?text=Sorteo';
    $tiempoHero = $sorteoHero['tiempo_restante'];
    $segundosHero = $tiempoHero['total_segundos'];
    $diasHero = $tiempoHero['dias'];
    $horasHero = $tiempoHero['horas'];
    $minutosHero = $tiempoHero['minutos'];
    $segundosRestantes = $tiempoHero['segundos'];
    $formatoTiempoHero = sprintf("%02d:%02d:%02d", $horasHero, $minutosHero, $segundosRestantes);
    $mostrarTerminaPronto = $sorteoHero['esta_por_finalizar'];
?>
<section class="rounded-xl overflow-hidden relative min-h-[300px] flex items-end p-8 sm:p-12 group" data-alt="<?php echo htmlspecialchars($sorteoHero['titulo']); ?>" style='background-image: linear-gradient(180deg, rgba(17,19,24,0) 0%, rgba(17,19,24,0.8) 60%, rgba(17,19,24,1) 100%), url("<?php echo $imagenHero; ?>"); background-size: cover; background-position: center;'>
<div class="absolute inset-0 bg-primary/10 mix-blend-overlay group-hover:bg-primary/20 transition-all duration-500"></div>
<div class="relative z-10 max-w-2xl">
<?php if ($mostrarTerminaPronto): ?>
<div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-500/20 border border-red-500/30 text-red-400 text-xs font-bold uppercase tracking-wider mb-4 animate-pulse">
<span class="size-2 bg-red-500 rounded-full"></span>
                        Termina Pronto
                    </div>
<?php endif; ?>
<h1 class="text-4xl md:text-5xl font-black text-white leading-tight mb-4 tracking-tight">
                        ¡Gana <span class="text-primary"><?php echo htmlspecialchars($sorteoHero['titulo']); ?></span>!
                    </h1>
<p class="text-gray-300 text-lg mb-8 max-w-lg">
                        El sorteo cierra en <span id="countdown-hero" class="text-white font-mono font-bold bg-card-dark px-2 py-0.5 rounded" data-seconds="<?php echo $segundosHero; ?>"><?php echo $formatoTiempoHero; ?></span>. <?php echo htmlspecialchars($sorteoHero['descripcion'] ?: 'No te quedes sin tu oportunidad de ganar este increíble premio.'); ?>
                    </p>
<div class="flex flex-wrap gap-4">
<a href="SorteoClienteDetalles.php?id=<?php echo $sorteoHero['id_sorteo']; ?>" id="buy-tickets-hero-btn" class="bg-gradient-to-r from-primary to-blue-600 hover:from-blue-600 hover:to-primary text-white px-8 py-3.5 rounded-xl font-bold text-base shadow-xl shadow-primary/30 hover:shadow-2xl hover:shadow-primary/40 transition-all duration-200 transform hover:-translate-y-0.5 active:translate-y-0">
                            Comprar Boletos Ahora
                        </a>
<a href="SorteoClienteDetalles.php?id=<?php echo $sorteoHero['id_sorteo']; ?>" id="view-details-hero-btn" class="bg-gradient-to-r from-[#282d39] to-[#323846] hover:from-[#323846] hover:to-[#3b4254] text-white px-6 py-3.5 rounded-xl font-semibold text-base border border-[#3e4552]/50 shadow-lg hover:shadow-xl transition-all duration-200">
                            Ver Detalles
                        </a>
</div>
</div>
</section>
<?php endif; ?>
<!-- Stats Overview -->
<section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
<!-- Stat Card 1 -->
<div class="bg-gradient-to-br from-card-dark to-[#151a23] p-6 rounded-2xl border border-[#282d39]/50 flex flex-col gap-2 hover:border-[#3b4254]/50 hover:shadow-xl hover:shadow-black/20 transition-all duration-200">
<div class="flex items-center justify-between">
<p class="text-text-secondary font-medium">Boletos Activos</p>
<div class="size-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
<span class="material-symbols-outlined text-[20px]">confirmation_number</span>
</div>
</div>
<div class="flex items-end gap-3 mt-1">
<p id="active-tickets-count" class="text-3xl font-bold text-white"><?php echo $totalBoletosActivos; ?></p>
<?php if ($totalBoletosActivos > 0): ?>
<span class="text-[#0bda62] text-sm font-medium mb-1.5 flex items-center">
<span class="material-symbols-outlined text-[16px] mr-0.5">trending_up</span>
                            <span id="new-tickets-count"><?php echo min(count($boletosActivosDashboard), $totalBoletosActivos); ?> activos</span>
                        </span>
<?php else: ?>
<span class="text-text-secondary text-sm font-medium mb-1.5 flex items-center">
                            <span id="new-tickets-count">Sin boletos</span>
                        </span>
<?php endif; ?>
</div>
</div>
<!-- Stat Card 2 -->
<div class="bg-gradient-to-br from-card-dark to-[#151a23] p-6 rounded-2xl border border-[#282d39]/50 flex flex-col gap-2 hover:border-[#3b4254]/50 hover:shadow-xl hover:shadow-black/20 transition-all duration-200">
<div class="flex items-center justify-between">
<p class="text-text-secondary font-medium">Ganancias Totales</p>
<div class="size-8 rounded-lg bg-green-500/10 flex items-center justify-center text-green-500">
<span class="material-symbols-outlined text-[20px]">payments</span>
</div>
</div>
<div class="flex items-end gap-3 mt-1">
<p id="total-earnings" class="text-3xl font-bold text-white">$1,250.00</p>
<span class="text-[#0bda62] text-sm font-medium mb-1.5 flex items-center">
                            <span id="earnings-growth">+15% mes</span>
                        </span>
</div>
</div>
<!-- Stat Card 3 -->
<div class="bg-gradient-to-br from-card-dark to-[#151a23] p-6 rounded-2xl border border-[#282d39]/50 flex flex-col gap-2 hover:border-[#3b4254]/50 hover:shadow-xl hover:shadow-black/20 transition-all duration-200">
<div class="flex items-center justify-between">
<p class="text-text-secondary font-medium">Puntos de Lealtad</p>
<div class="size-8 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-500">
<span class="material-symbols-outlined text-[20px]">loyalty</span>
</div>
</div>
<div class="flex items-end gap-3 mt-1">
<p id="loyalty-points" class="text-3xl font-bold text-white">450 pts</p>
<span id="loyalty-level" class="text-purple-400 text-sm font-medium mb-1.5">Nivel Plata</span>
</div>
</div>
</section>
<!-- Main Dashboard Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
<!-- Left Column: Active Tickets (2/3 width) -->
<div class="lg:col-span-2 flex flex-col gap-6">
<div class="flex items-center justify-between">
<h3 class="text-xl font-bold text-white">Mis Boletos Activos</h3>
<a class="text-sm font-medium text-primary hover:text-blue-400 transition-colors" href="MisBoletosCliente.php">Ver todos</a>
</div>
<div class="bg-card-dark rounded-xl border border-[#282d39] overflow-hidden">
<div class="overflow-x-auto">
<table class="w-full text-left text-sm">
<thead class="bg-[#1e2330] text-text-secondary uppercase text-xs font-semibold">
<tr>
<th class="px-6 py-4">Sorteo</th>
<th class="px-6 py-4">Boleto #</th>
<th class="px-6 py-4">Fecha Sorteo</th>
<th class="px-6 py-4">Estado</th>
<th class="px-6 py-4 text-right">Acción</th>
</tr>
</thead>
<tbody class="divide-y divide-[#282d39]">
<?php if (empty($boletosActivosDashboard)): ?>
<!-- Mensaje cuando no hay boletos activos -->
<tr>
<td colspan="5" class="px-6 py-12 text-center">
<div class="flex flex-col items-center justify-center">
<div class="size-16 rounded-full bg-[#282d39] flex items-center justify-center mb-4">
<span class="material-symbols-outlined text-4xl text-text-secondary">confirmation_number</span>
</div>
<p class="text-white font-medium mb-2">No tienes boletos activos</p>
<p class="text-text-secondary text-sm mb-4">Participa en sorteos activos para ver tus boletos aquí</p>
<a href="ListadoSorteosActivos.php" class="text-sm font-semibold text-primary hover:text-blue-400 transition-colors">
Ver Sorteos Disponibles →
</a>
</div>
</td>
</tr>
<?php else: ?>
<?php foreach ($boletosActivosDashboard as $boleto): 
    $estadoTexto = $boleto['estado_texto'];
    $estadoColor = $boleto['estado_color'];
    $numeroBoleto = htmlspecialchars($boleto['numero_boleto']);
    $sorteoTitulo = htmlspecialchars($boleto['sorteo_titulo']);
    $sorteoImagen = htmlspecialchars($boleto['sorteo_imagen']);
    $sorteoFechaFin = htmlspecialchars($boleto['sorteo_fecha_fin']);
    $idBoleto = $boleto['id_boleto'];
    $idSorteo = $boleto['id_sorteo'];
    
    // Clases CSS según el estado
    $badgeClasses = [
        'green' => 'bg-[#0bda62]/10 text-[#0bda62]',
        'yellow' => 'bg-yellow-500/10 text-yellow-500',
        'red' => 'bg-red-500/10 text-red-500',
        'amber' => 'bg-amber-500/10 text-amber-500'
    ];
    $badgeClass = $badgeClasses[$estadoColor] ?? $badgeClasses['yellow'];
    $dotClasses = [
        'green' => 'bg-[#0bda62]',
        'yellow' => 'bg-yellow-500 animate-pulse',
        'red' => 'bg-red-500',
        'amber' => 'bg-amber-500'
    ];
    $dotClass = $dotClasses[$estadoColor] ?? $dotClasses['yellow'];
?>
<!-- Boleto Activo: <?php echo $estadoTexto; ?> -->
<tr class="boleto-activo-row hover:bg-[#353b4b] transition-colors group"
    data-boleto-id="<?php echo $idBoleto; ?>"
    data-sorteo-id="<?php echo $idSorteo; ?>"
    data-numero-boleto="<?php echo $boleto['numero_boleto_int']; ?>"
    data-sorteo-titulo="<?php echo $sorteoTitulo; ?>">
<td class="px-6 py-4 font-medium text-white flex items-center gap-3">
<div class="size-8 rounded bg-gray-700 bg-cover bg-center" data-alt="<?php echo $sorteoTitulo; ?>" style='background-image: url("<?php echo $sorteoImagen; ?>");' onerror="this.style.backgroundImage='url(\'https://via.placeholder.com/150\')'"></div>
<?php echo $sorteoTitulo; ?>
</td>
<td class="px-6 py-4 text-text-secondary font-mono">#<?php echo $numeroBoleto; ?></td>
<td class="px-6 py-4 text-text-secondary"><?php echo $sorteoFechaFin ?: 'N/A'; ?></td>
<td class="px-6 py-4">
<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold <?php echo $badgeClass; ?>">
<span class="size-1.5 rounded-full <?php echo $dotClass; ?>"></span>
<?php echo $estadoTexto; ?>
</span>
</td>
<td class="px-6 py-4 text-right relative">
<button class="ticket-menu-btn text-text-secondary hover:text-white transition-colors" 
        data-ticket-id="<?php echo $numeroBoleto; ?>" 
        data-ticket-name="<?php echo $sorteoTitulo; ?>"
        data-boleto-id="<?php echo $idBoleto; ?>"
        data-sorteo-id="<?php echo $idSorteo; ?>">
<span class="material-symbols-outlined text-[20px]">more_vert</span>
</button>
</td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>
<!-- Right Column: Notifications & Quick Profile (1/3 width) -->
<div class="flex flex-col gap-6">
<h3 class="text-xl font-bold text-white">Notificaciones</h3>
<div class="bg-card-dark rounded-xl border border-[#282d39] p-2">
<div class="flex flex-col gap-1">
<!-- Notification Item -->
<div class="notification-item flex gap-4 p-3 rounded-lg hover:bg-[#353b4b] transition-colors cursor-pointer relative" data-notification-id="sorteo-finalizado" data-notification-type="winner">
<div class="relative flex-shrink-0">
<div class="size-10 rounded-full bg-primary/20 flex items-center justify-center text-primary">
<span class="material-symbols-outlined text-[20px]">emoji_events</span>
</div>
<span class="absolute top-0 right-0 size-2.5 bg-primary border-2 border-card-dark rounded-full"></span>
</div>
<div>
<p class="text-sm font-medium text-white">¡Sorteo finalizado!</p>
<p class="text-xs text-text-secondary mt-0.5">El ganador del sorteo #502 ha sido anunciado. Revisa si ganaste.</p>
<p class="text-[10px] text-text-secondary mt-2">Hace 2 horas</p>
</div>
</div>
<!-- Notification Item -->
<div class="notification-item flex gap-4 p-3 rounded-lg hover:bg-[#353b4b] transition-colors cursor-pointer" data-notification-id="deposito-confirmado" data-notification-type="payment">
<div class="flex-shrink-0">
<div class="size-10 rounded-full bg-green-500/20 flex items-center justify-center text-green-500">
<span class="material-symbols-outlined text-[20px]">account_balance_wallet</span>
</div>
</div>
<div>
<p class="text-sm font-medium text-white">Depósito confirmado</p>
<p class="text-xs text-text-secondary mt-0.5">Tus $50.00 han sido acreditados a tu cuenta exitosamente.</p>
<p class="text-[10px] text-text-secondary mt-2">Hace 1 día</p>
</div>
</div>
<!-- Notification Item -->
<div class="notification-item flex gap-4 p-3 rounded-lg hover:bg-[#353b4b] transition-colors cursor-pointer" data-notification-id="boletos-pendientes" data-notification-type="ticket">
<div class="flex-shrink-0">
<div class="size-10 rounded-full bg-yellow-500/20 flex items-center justify-center text-yellow-500">
<span class="material-symbols-outlined text-[20px]">confirmation_number</span>
</div>
</div>
<div>
<p class="text-sm font-medium text-white">Boletos Pendientes</p>
<p class="text-xs text-text-secondary mt-0.5">Tienes 2 boletos esperando aprobación del administrador.</p>
<p class="text-[10px] text-text-secondary mt-2">Hace 2 días</p>
</div>
</div>
</div>
<button id="view-all-notifications-btn" class="w-full text-center py-3 text-xs font-semibold text-text-secondary hover:text-white transition-colors border-t border-[#282d39] mt-1">
                            Ver todas las notificaciones
                        </button>
</div>
<!-- Quick Help Card -->
<div class="bg-card-dark rounded-xl border border-[#282d39] p-4 mt-6">
<div class="flex items-center gap-3 mb-3">
<div class="size-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
<span class="material-symbols-outlined text-[20px]">help</span>
</div>
<div>
<h4 class="text-sm font-bold text-white">¿Necesitas Ayuda?</h4>
<p class="text-xs text-text-secondary">Encuentra respuestas rápidas</p>
</div>
</div>
<a href="FAQCliente.php" class="flex items-center justify-center gap-2 w-full py-2.5 rounded-lg bg-primary hover:bg-blue-600 text-white text-sm font-semibold transition-colors">
<span class="material-symbols-outlined text-[18px]">quiz</span>
Ver Preguntas Frecuentes
</a>
</div>
</div>
</div>
<!-- Other Active Raffles (Grid) -->
<div>
<div class="flex items-center justify-between mb-6">
<h3 class="text-xl font-bold text-white">Sorteos Destacados</h3>
<div class="flex items-center gap-4">
<a class="text-sm font-medium text-primary hover:text-blue-400 transition-colors" href="ListadoSorteosActivos.php">Ver todos los sorteos</a>
<div class="flex gap-2 lg:hidden">
<button id="prev-raffles-btn" class="size-8 flex items-center justify-center rounded-full bg-card-dark text-white hover:bg-primary transition-colors">
<span class="material-symbols-outlined text-[20px]">chevron_left</span>
</button>
<button id="next-raffles-btn" class="size-8 flex items-center justify-center rounded-full bg-card-dark text-white hover:bg-primary transition-colors">
<span class="material-symbols-outlined text-[20px]">chevron_right</span>
</button>
</div>
</div>
</div>
<div id="raffles-container" class="flex gap-6 overflow-x-auto scrollbar-hide lg:grid lg:grid-cols-4 lg:overflow-visible" style="scroll-behavior: smooth;">
<?php if (empty($sorteosActivos)): ?>
    <!-- Mensaje cuando no hay sorteos -->
    <div class="col-span-4 text-center py-12">
        <p class="text-text-secondary text-lg">No hay sorteos disponibles en este momento.</p>
    </div>
<?php else: ?>
    <?php 
    $cardIndex = 1;
    foreach ($sorteosActivos as $sorteo): 
        $imagenUrl = !empty($sorteo['imagen_url']) 
            ? htmlspecialchars($sorteo['imagen_url']) 
            : 'https://via.placeholder.com/400x200?text=Sorteo';
        $tiempoRestante = $sorteo['tiempo_restante'];
        $segundosTotales = $tiempoRestante['total_segundos'];
        $horasRestantes = floor($segundosTotales / 3600);
        $minutosRestantes = floor(($segundosTotales % 3600) / 60);
        $segundosRestantes = $segundosTotales % 60;
        $formatoTiempo = sprintf("%02d:%02d:%02d", $horasRestantes, $minutosRestantes, $segundosRestantes);
        $diasRestantes = $tiempoRestante['dias'];
        $textoTiempo = $diasRestantes > 0 ? $diasRestantes . ' Día' . ($diasRestantes > 1 ? 's' : '') : $formatoTiempo;
        $colorTiempo = $sorteo['esta_por_finalizar'] ? 'text-red-400 animate-pulse' : 'text-yellow-500';
        $boletosDisponibles = $sorteo['boletos_disponibles'];
        $porcentajeVendido = $sorteo['porcentaje_vendido'];
    ?>
    <!-- Raffle Card -->
    <div class="raffle-card flex-shrink-0 w-72 lg:w-auto lg:flex-shrink bg-card-dark rounded-xl border border-[#282d39] overflow-hidden group hover:-translate-y-1 transition-transform duration-300" data-raffle-id="<?php echo $sorteo['id_sorteo']; ?>">
        <div class="h-40 bg-cover bg-center relative" data-alt="<?php echo htmlspecialchars($sorteo['titulo']); ?>" style='background-image: url("<?php echo $imagenUrl; ?>");'>
            <div class="absolute top-3 right-3 bg-black/70 backdrop-blur-sm text-white text-xs font-bold px-2 py-1 rounded">
                $<?php echo number_format($sorteo['precio_boleto'], 2, '.', ''); ?> / boleto
            </div>
        </div>
        <div class="p-4">
            <h4 class="text-lg font-bold text-white mb-1 truncate"><?php echo htmlspecialchars($sorteo['titulo']); ?></h4>
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs text-text-secondary">Quedan <?php echo $boletosDisponibles; ?> boletos</span>
                <span id="countdown-card-<?php echo $cardIndex; ?>" class="text-xs font-mono <?php echo $colorTiempo; ?>" data-seconds="<?php echo $segundosTotales; ?>"><?php echo $textoTiempo; ?></span>
            </div>
            <div class="w-full bg-[#111318] rounded-full h-1.5 mb-4 overflow-hidden">
                <div class="bg-primary h-1.5 rounded-full" style="width: <?php echo $porcentajeVendido; ?>%"></div>
            </div>
            <a href="SorteoClienteDetalles.php?id=<?php echo $sorteo['id_sorteo']; ?>" class="participate-btn w-full py-2 rounded-lg bg-[#353b4b] hover:bg-primary text-white text-sm font-semibold transition-colors block text-center" data-raffle-id="<?php echo $sorteo['id_sorteo']; ?>" data-raffle-name="<?php echo htmlspecialchars($sorteo['titulo']); ?>">
                Participar
            </a>
        </div>
    </div>
    <?php 
    $cardIndex++;
    endforeach; 
    ?>
<?php endif; ?>
</div>
</div>
<!-- Footer Spacing -->
<div class="h-10"></div>
</div>
</main>

<!-- Custom Alerts Script (debe cargarse antes de client-layout.js) -->
<script src="js/custom-alerts.js"></script>
<!-- Client Layout Script -->
<script src="js/client-layout.js"></script>
<script>
// Datos del usuario desde PHP (sesión) - DEBE estar antes de inicializar ClientLayout
const userSessionData = {
    nombre: '<?php echo addslashes($usuarioNombre); ?>',
    tipoUsuario: '<?php echo addslashes($tipoUsuario); ?>',
    email: '<?php echo addslashes($usuarioEmail); ?>',
    saldo: <?php echo number_format($usuarioSaldo, 2, '.', ''); ?>,
    avatar: '<?php echo addslashes($usuarioAvatar); ?>'
};

// Actualizar localStorage con los datos de la sesión ANTES de inicializar ClientLayout
if (userSessionData.nombre && userSessionData.tipoUsuario) {
    const sessionClientData = {
        nombre: userSessionData.nombre,
        tipoUsuario: userSessionData.tipoUsuario,
        email: userSessionData.email,
        saldo: userSessionData.saldo,
        fotoPerfil: userSessionData.avatar || 'https://lh3.googleusercontent.com/aida-public/AB6AXuAscTJ1Xcq7edw4JqzzGbgOvjdyQ9_nDg7kkxtlCQw51-EJsv1RJyDd9OAZC89eniVl2ujzIik6wgxd5FTvho_ak6ccsWrWelinVwXj6yQUdpPUXYUTJN0pSvhRh-smWf81cMQz40x4U3setrSFDsyX4KkfxOsHc6PnTND68lGw6JkA9B0ag_4fNu5s0Z9OMbq83llAZUv3xuo3s6VI1no110ozE88mRALnX-rhgavHoJxmYpvBcUxV7BtrJr_9Q0BlgvZQL2BXCFg'
    };
    localStorage.setItem('clientData', JSON.stringify(sessionClientData));
    sessionStorage.setItem('clientData', JSON.stringify(sessionClientData));
}

// Inicializar layout del cliente DESPUÉS de actualizar localStorage
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar el layout con 'dashboard' como página activa
    if (window.ClientLayout) {
        ClientLayout.init('dashboard');
    }
});

// Función para cargar datos del cliente
function loadClientData() {
    // Usar datos de la sesión PHP como base
    let clientData = {
        nombre: userSessionData.nombre,
        tipoUsuario: userSessionData.tipoUsuario,
        fotoPerfil: userSessionData.avatar || 'https://lh3.googleusercontent.com/aida-public/AB6AXuAscTJ1Xcq7edw4JqzzGbgOvjdyQ9_nDg7kkxtlCQw51-EJsv1RJyDd9OAZC89eniVl2ujzIik6wgxd5FTvho_ak6ccsWrWelinVwXj6yQUdpPUXYUTJN0pSvhRh-smWf81cMQz40x4U3setrSFDsyX4KkfxOsHc6PnTND68lGw6JkA9B0ag_4fNu5s0Z9OMbq83llAZUv3xuo3s6VI1no110ozE88mRALnX-rhgavHoJxmYpvBcUxV7BtrJr_9Q0BlgvZQL2BXCFg',
        saldo: userSessionData.saldo,
        boletosActivos: <?php echo $totalBoletosActivos; ?>,
        boletosNuevos: <?php echo min(count($boletosActivosDashboard), 3); ?>,
        gananciasTotales: 1250.00, // Este valor puede calcularse desde la tabla de ganadores si es necesario
        crecimientoGanancias: '+15% mes', // Este valor puede calcularse comparando periodos
        puntosLealtad: 450, // Este valor puede venir de una tabla de puntos de lealtad
        nivelLealtad: 'Nivel Plata' // Este valor puede calcularse desde los puntos
    };

    // Intentar obtener datos adicionales desde localStorage (pero mantener nombre y tipoUsuario de la sesión)
    const storedData = localStorage.getItem('clientData');
    if (storedData) {
        try {
            const parsedData = JSON.parse(storedData);
            // Mantener nombre y tipoUsuario de la sesión, pero usar otros datos de localStorage si existen
            clientData = { 
                ...clientData, 
                ...parsedData,
                nombre: userSessionData.nombre, // Forzar nombre de sesión
                tipoUsuario: userSessionData.tipoUsuario // Forzar tipoUsuario de sesión
            };
        } catch (e) {
            console.error('Error al parsear datos del cliente:', e);
        }
    }
    
    // Guardar datos actualizados en localStorage
    localStorage.setItem('clientData', JSON.stringify(clientData));

    // Guardar también en sessionStorage
    sessionStorage.setItem('clientData', JSON.stringify(clientData));
    
    // Actualizar elementos del DOM con los datos del cliente
    updateDashboard(clientData);

    // También puedes hacer una petición al servidor para obtener datos actualizados
    // fetchClientDataFromServer();
}

// Función para actualizar el dashboard con los datos del cliente
function updateDashboard(data) {
    // Actualizar información del usuario en el sidebar
    const sidebarUserNameEl = document.getElementById('sidebar-user-name');
    const sidebarUserTypeEl = document.getElementById('sidebar-user-type');
    const sidebarUserAvatarEl = document.getElementById('sidebar-user-avatar');
    const userBalanceEl = document.getElementById('user-balance');

    if (sidebarUserNameEl && data.nombre) {
        sidebarUserNameEl.textContent = data.nombre;
    }

    if (sidebarUserTypeEl && data.tipoUsuario) {
        sidebarUserTypeEl.textContent = data.tipoUsuario;
    }

    if (sidebarUserAvatarEl && data.fotoPerfil) {
        sidebarUserAvatarEl.style.backgroundImage = `url("${data.fotoPerfil}")`;
    }

    if (userBalanceEl && data.saldo !== undefined) {
        userBalanceEl.textContent = `$${data.saldo.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    // Actualizar estadísticas
    const activeTicketsEl = document.getElementById('active-tickets-count');
    const newTicketsEl = document.getElementById('new-tickets-count');
    const totalEarningsEl = document.getElementById('total-earnings');
    const earningsGrowthEl = document.getElementById('earnings-growth');
    const loyaltyPointsEl = document.getElementById('loyalty-points');
    const loyaltyLevelEl = document.getElementById('loyalty-level');

    // Los datos de boletos activos se cargan desde PHP directamente en el HTML
    // Solo actualizar si los datos vienen de localStorage/sessionStorage
    if (activeTicketsEl && data.boletosActivos !== undefined && !activeTicketsEl.textContent.trim()) {
        activeTicketsEl.textContent = data.boletosActivos;
    }

    if (newTicketsEl && data.boletosNuevos !== undefined && !newTicketsEl.textContent.trim()) {
        newTicketsEl.textContent = `+${data.boletosNuevos} nuevos`;
    }

    if (totalEarningsEl && data.gananciasTotales !== undefined) {
        totalEarningsEl.textContent = `$${data.gananciasTotales.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    if (earningsGrowthEl && data.crecimientoGanancias) {
        earningsGrowthEl.textContent = data.crecimientoGanancias;
    }

    if (loyaltyPointsEl && data.puntosLealtad !== undefined) {
        loyaltyPointsEl.textContent = `${data.puntosLealtad} pts`;
    }

    if (loyaltyLevelEl && data.nivelLealtad) {
        loyaltyLevelEl.textContent = data.nivelLealtad;
    }
}

// Función para obtener datos del servidor (opcional)
async function fetchClientDataFromServer() {
    try {
        // Aquí puedes hacer una petición fetch a tu API
        // const response = await fetch('/api/cliente/dashboard', {
        //     method: 'GET',
        //     headers: {
        //         'Authorization': 'Bearer ' + getToken(),
        //         'Content-Type': 'application/json'
        //     }
        // });
        // const data = await response.json();
        // updateDashboard(data);
        
        // Por ahora, solo cargamos desde localStorage/sessionStorage
        console.log('Cargando datos del cliente desde almacenamiento local');
    } catch (error) {
        console.error('Error al cargar datos del cliente:', error);
    }
}

// Función para guardar datos del cliente (útil después de actualizaciones)
function saveClientData(data) {
    try {
        localStorage.setItem('clientData', JSON.stringify(data));
        sessionStorage.setItem('clientData', JSON.stringify(data));
    } catch (e) {
        console.error('Error al guardar datos del cliente:', e);
    }
}

// Función para establecer datos del cliente manualmente (útil para pruebas o después de login)
function setClientData(nombre, tipoUsuario, fotoPerfil, saldo, boletosActivos, gananciasTotales, puntosLealtad, nivelLealtad) {
    const clientData = {
        nombre: nombre || 'Usuario',
        tipoUsuario: tipoUsuario || 'Usuario Estándar',
        fotoPerfil: fotoPerfil || '',
        saldo: saldo || 0,
        boletosActivos: boletosActivos || 0,
        boletosNuevos: 0,
        gananciasTotales: gananciasTotales || 0,
        crecimientoGanancias: '+0% mes',
        puntosLealtad: puntosLealtad || 0,
        nivelLealtad: nivelLealtad || 'Nivel Bronce'
    };
    
    saveClientData(clientData);
    updateDashboard(clientData);
}

// Función para formatear segundos a formato HH:MM:SS
function formatTime(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
}

// Función para convertir formato HH:MM:SS a segundos
function timeToSeconds(timeString) {
    const parts = timeString.split(':');
    if (parts.length !== 3) return 0;
    
    const hours = parseInt(parts[0]) || 0;
    const minutes = parseInt(parts[1]) || 0;
    const seconds = parseInt(parts[2]) || 0;
    
    return hours * 3600 + minutes * 60 + seconds;
}

// Almacenar intervalos activos para poder limpiarlos si es necesario
const activeCountdownIntervals = new Map();

// Función para inicializar un contador regresivo
function initCountdown(elementId, initialSeconds) {
    const element = document.getElementById(elementId);
    if (!element) return;

    // Si ya existe un intervalo para este elemento, limpiarlo primero
    if (activeCountdownIntervals.has(elementId)) {
        clearInterval(activeCountdownIntervals.get(elementId));
        activeCountdownIntervals.delete(elementId);
    }

    // Si no se proporcionan segundos, intentar leer del atributo data-seconds
    let remainingSeconds = initialSeconds;
    if (remainingSeconds === undefined || remainingSeconds === null) {
        const dataSeconds = element.getAttribute('data-seconds');
        if (dataSeconds) {
            remainingSeconds = parseInt(dataSeconds);
        } else {
            // Si no hay data-seconds, leer del texto actual del elemento
            const currentText = element.textContent.trim();
            remainingSeconds = timeToSeconds(currentText);
        }
    }
    
    remainingSeconds = parseInt(remainingSeconds) || 0;
    
    // Función de actualización
    function updateCountdown() {
        if (remainingSeconds <= 0) {
            element.textContent = '00:00:00';
            // Agregar clase cuando termine
            if (!element.classList.contains('opacity-50')) {
                element.classList.add('opacity-50');
            }
            // Limpiar el intervalo cuando llegue a cero
            if (activeCountdownIntervals.has(elementId)) {
                clearInterval(activeCountdownIntervals.get(elementId));
                activeCountdownIntervals.delete(elementId);
            }
            return;
        }

        // Actualizar el texto con formato
        element.textContent = formatTime(remainingSeconds);
        remainingSeconds--;
    }

    // Actualizar inmediatamente
    updateCountdown();

    // Iniciar intervalo que se actualiza cada segundo
    const intervalId = setInterval(updateCountdown, 1000);
    activeCountdownIntervals.set(elementId, intervalId);
}

// Función para inicializar todos los contadores
function initAllCountdowns() {
    // Buscar todos los elementos con atributo data-seconds o con ID que contenga "countdown"
    const countdownElements = document.querySelectorAll('[data-seconds], [id*="countdown"]');
    
    countdownElements.forEach(element => {
        const elementId = element.id;
        if (!elementId) return;
        
        const dataSeconds = element.getAttribute('data-seconds');
        const initialSeconds = dataSeconds ? parseInt(dataSeconds) : null;
        
        initCountdown(elementId, initialSeconds);
    });
}

// Limpiar todos los intervalos cuando la página se descargue
window.addEventListener('beforeunload', function() {
    activeCountdownIntervals.forEach(intervalId => {
        clearInterval(intervalId);
    });
    activeCountdownIntervals.clear();
});

// ==================== FUNCIONALIDADES DE BOTONES ====================

// Función para manejar el botón de depositar fondos
function initDepositFunds() {
    const depositBtn = document.getElementById('deposit-funds-btn');
    if (depositBtn) {
        depositBtn.addEventListener('click', function() {
            // Aquí puedes redirigir a una página de depósito o abrir un modal
            customConfirm('¿Deseas ir a la página de depósito de fondos?', 'Depositar Fondos', 'help').then(confirmed => {
                if (confirmed) {
                    // Si tienes una página de depósito, redirige allí
                    // window.location.href = 'DepositarFondos.php';
                    customAlert('Funcionalidad de depósito: Aquí se abriría el formulario o página para depositar fondos.', 'Depositar Fondos', 'info');
                }
            });
        });
    }
}

// Función para manejar el panel de notificaciones
function initNotifications() {
    const notificationsBtn = document.getElementById('notifications-btn');
    const notificationsPanel = document.getElementById('notifications-panel');
    const closePanelBtn = document.getElementById('close-notifications-panel');
    const badge = document.getElementById('notifications-badge');

    if (notificationsBtn && notificationsPanel) {
        // Toggle panel al hacer clic en el botón
        notificationsBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isHidden = notificationsPanel.classList.contains('hidden');
            if (isHidden) {
                notificationsPanel.classList.remove('hidden');
                loadNotificationsIntoPanel();
                // Ocultar badge si hay notificaciones nuevas
                if (badge) {
                    badge.style.display = 'none';
                }
            } else {
                notificationsPanel.classList.add('hidden');
            }
        });

        // Cerrar panel con el botón de cerrar
        if (closePanelBtn) {
            closePanelBtn.addEventListener('click', function() {
                notificationsPanel.classList.add('hidden');
            });
        }

        // Cerrar panel al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!notificationsPanel.contains(e.target) && !notificationsBtn.contains(e.target)) {
                notificationsPanel.classList.add('hidden');
            }
        });

        // Cerrar con Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !notificationsPanel.classList.contains('hidden')) {
                notificationsPanel.classList.add('hidden');
            }
        });
    }

    // Cargar notificaciones en el panel
    function loadNotificationsIntoPanel() {
        const notificationsList = document.getElementById('notifications-list');
        if (!notificationsList) return;

        const notifications = [
            { id: 'sorteo-finalizado', type: 'winner', title: '¡Sorteo finalizado!', message: 'El ganador del sorteo #502 ha sido anunciado. Revisa si ganaste.', time: 'Hace 2 horas', icon: 'emoji_events', color: 'primary' },
            { id: 'deposito-confirmado', type: 'payment', title: 'Depósito confirmado', message: 'Tus $50.00 han sido acreditados a tu cuenta exitosamente.', time: 'Hace 1 día', icon: 'account_balance_wallet', color: 'green-500' },
            { id: 'boletos-pendientes', type: 'ticket', title: 'Boletos Pendientes', message: 'Tienes 2 boletos esperando aprobación del administrador.', time: 'Hace 2 días', icon: 'confirmation_number', color: 'yellow-500' }
        ];

        notificationsList.innerHTML = notifications.map(notif => `
            <div class="notification-item flex gap-4 p-3 rounded-lg hover:bg-[#353b4b] transition-colors cursor-pointer" 
                 data-notification-id="${notif.id}" data-notification-type="${notif.type}">
                <div class="flex-shrink-0">
                    <div class="size-10 rounded-full bg-${notif.color}/20 flex items-center justify-center text-${notif.color}">
                        <span class="material-symbols-outlined text-[20px]">${notif.icon}</span>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-white">${notif.title}</p>
                    <p class="text-xs text-text-secondary mt-0.5">${notif.message}</p>
                    <p class="text-[10px] text-text-secondary mt-2">${notif.time}</p>
                </div>
            </div>
        `).join('');

        // Agregar event listeners a las notificaciones del panel
        const notificationItems = notificationsList.querySelectorAll('.notification-item');
        notificationItems.forEach(item => {
            item.addEventListener('click', function() {
                handleNotificationClick(this.dataset.notificationId, this.dataset.notificationType);
            });
        });
    }
}

// Función para manejar clic en notificaciones
function handleNotificationClick(notificationId, notificationType) {
    switch(notificationType) {
        case 'winner':
            customAlert('Redirigiendo a página de ganadores...', 'Sorteo Finalizado', 'success').then(() => {
                window.location.href = 'MisGanancias.php';
            });
            break;
        case 'payment':
            customAlert('Puedes ver el detalle en tu historial de transacciones.', 'Depósito Confirmado', 'success');
            break;
        case 'ticket':
            customAlert('Redirigiendo a página de boletos...', 'Boletos Pendientes', 'warning').then(() => {
                window.location.href = 'MisBoletosCliente.php';
            });
            break;
        default:
            console.log('Notificación clickeada:', notificationId);
    }
}

// Función para inicializar búsqueda
function initSearch() {
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            
            if (query.length === 0) {
                return;
            }

            // Debounce: esperar 500ms después de que el usuario deje de escribir
            searchTimeout = setTimeout(function() {
                performSearch(query);
            }, 500);
        });

        // Búsqueda con Enter
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimeout);
                const query = e.target.value.trim();
                if (query.length > 0) {
                    performSearch(query);
                }
            }
        });
    }
}

function performSearch(query) {
    console.log('Buscando:', query);
    // Aquí puedes implementar la lógica de búsqueda
    // Por ejemplo, redirigir a una página de resultados o filtrar elementos en la página
    customAlert(`Buscando: "${query}"\n\nEsta funcionalidad buscaría en sorteos y boletos.`, 'Búsqueda', 'info');
    // Ejemplo: window.location.href = `Buscar.php?q=${encodeURIComponent(query)}`;
}

// Función para botones del Hero (Comprar Boletos y Ver Detalles)
function initHeroButtons() {
    const buyTicketsBtn = document.getElementById('buy-tickets-hero-btn');
    const viewDetailsBtn = document.getElementById('view-details-hero-btn');

    if (buyTicketsBtn) {
        buyTicketsBtn.addEventListener('click', function() {
            // Redirigir a la página de selección de boletos del sorteo destacado
            window.location.href = 'SeleccionBoletos.php?sorteo=iphone-15-pro';
        });
    }

    if (viewDetailsBtn) {
        viewDetailsBtn.addEventListener('click', function() {
            // Redirigir a la página de detalles del sorteo
            window.location.href = 'SorteoClienteDetalles.php?sorteo=iphone-15-pro';
        });
    }
}

// Función para menús contextuales de boletos (botones more_vert)
function initTicketMenus() {
    const ticketMenuBtns = document.querySelectorAll('.ticket-menu-btn');
    
    ticketMenuBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const ticketId = this.dataset.ticketId;
            const ticketName = this.dataset.ticketName;
            showTicketContextMenu(this, ticketId, ticketName);
        });
    });

    // Cerrar menú al hacer clic fuera
    document.addEventListener('click', function() {
        closeAllContextMenus();
    });
}

function showTicketContextMenu(button, ticketId, ticketName) {
    // Cerrar otros menús abiertos
    closeAllContextMenus();

    // Crear menú contextual
    const menu = document.createElement('div');
    menu.className = 'ticket-context-menu absolute right-0 mt-2 w-48 bg-card-dark rounded-lg border border-[#282d39] shadow-xl z-50';
    menu.innerHTML = `
        <div class="py-1">
            <button class="context-menu-item w-full text-left px-4 py-2 text-sm text-white hover:bg-[#353b4b] transition-colors flex items-center gap-2" data-action="view">
                <span class="material-symbols-outlined text-[18px]">visibility</span>
                <span>Ver Detalles</span>
            </button>
            <button class="context-menu-item w-full text-left px-4 py-2 text-sm text-white hover:bg-[#353b4b] transition-colors flex items-center gap-2" data-action="share">
                <span class="material-symbols-outlined text-[18px]">share</span>
                <span>Compartir</span>
            </button>
            <div class="border-t border-[#282d39] my-1"></div>
            <button class="context-menu-item w-full text-left px-4 py-2 text-sm text-red-400 hover:bg-[#353b4b] transition-colors flex items-center gap-2" data-action="delete">
                <span class="material-symbols-outlined text-[18px]">delete</span>
                <span>Eliminar</span>
            </button>
        </div>
    `;

    // Posicionar el menú
    const rect = button.getBoundingClientRect();
    const parentCell = button.closest('td');
    if (parentCell) {
        parentCell.style.position = 'relative';
        parentCell.appendChild(menu);

        // Event listeners para acciones del menú
        const menuItems = menu.querySelectorAll('.context-menu-item');
        menuItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.stopPropagation();
                handleTicketMenuAction(this.dataset.action, ticketId, ticketName);
                closeAllContextMenus();
            });
        });
    }
}

function handleTicketMenuAction(action, ticketId, ticketName) {
    switch(action) {
        case 'view':
            customAlert(`Boleto: #${ticketId}\nSorteo: ${ticketName}`, 'Detalles del Boleto', 'info');
            // window.location.href = `DetalleBoleto.php?id=${ticketId}`;
            break;
        case 'share':
            if (navigator.share) {
                navigator.share({
                    title: `Mi boleto: ${ticketName}`,
                    text: `Tengo un boleto #${ticketId} para el sorteo ${ticketName}`,
                }).catch(console.error);
            } else {
                // Fallback: copiar al portapapeles
                navigator.clipboard.writeText(`Boleto #${ticketId} - ${ticketName}`);
                customToast('Información del boleto copiada al portapapeles', 'success');
            }
            break;
        case 'delete':
            customConfirm(`¿Estás seguro de que deseas eliminar el boleto #${ticketId}?`, 'Eliminar Boleto', 'warning').then(confirmed => {
                if (confirmed) {
                    customAlert(`Boleto #${ticketId} eliminado (simulado)`, 'Boleto Eliminado', 'success');
                    // Aquí implementarías la lógica de eliminación real
                }
            });
            break;
    }
}

function closeAllContextMenus() {
    const menus = document.querySelectorAll('.ticket-context-menu');
    menus.forEach(menu => menu.remove());
}

// Función para notificaciones clicables en el sidebar
function initNotificationItems() {
    const notificationItems = document.querySelectorAll('.notification-item[data-notification-id]');
    notificationItems.forEach(item => {
        item.addEventListener('click', function() {
            const notificationId = this.dataset.notificationId;
            const notificationType = this.dataset.notificationType;
            handleNotificationClick(notificationId, notificationType);
        });
    });
}

// Función para botón "Ver todas las notificaciones"
function initViewAllNotifications() {
    const viewAllBtn = document.getElementById('view-all-notifications-btn');
    if (viewAllBtn) {
        viewAllBtn.addEventListener('click', function() {
            // Redirigir a página de todas las notificaciones
            customAlert('Redirigiendo a página de todas las notificaciones...', 'Notificaciones', 'info');
            // window.location.href = 'NotificacionesCliente.php';
        });
    }
}

// Función para navegación de carrusel de sorteos
function initRafflesCarousel() {
    const prevBtn = document.getElementById('prev-raffles-btn');
    const nextBtn = document.getElementById('next-raffles-btn');
    const container = document.getElementById('raffles-container');
    
    if (!container) return;

    function scrollCarousel(direction) {
        const cardWidth = 288; // w-72 = 288px + gap-6 = 24px = 312px por tarjeta
        const scrollAmount = cardWidth * 1.5; // Scroll por 1.5 tarjetas
        
        if (direction === 'prev') {
            container.scrollBy({
                left: -scrollAmount,
                behavior: 'smooth'
            });
        } else {
            container.scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            });
        }
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            scrollCarousel('prev');
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            scrollCarousel('next');
        });
    }
}

// Función para botones "Participar" en tarjetas de sorteos
function initParticipateButtons() {
    const participateBtns = document.querySelectorAll('.participate-btn');
    
    participateBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const raffleId = this.dataset.raffleId;
            const raffleName = this.dataset.raffleName;
            
            // Confirmar participación
            customConfirm(`¿Deseas participar en el sorteo "${raffleName}"?`, 'Participar en Sorteo', 'help').then(confirmed => {
                if (confirmed) {
                    // Redirigir a la página de selección de boletos para este sorteo
                    window.location.href = `SeleccionBoletos.php?sorteo=${raffleId}`;
                }
            });
        });
    });
}

// Cargar datos cuando la página esté lista
document.addEventListener('DOMContentLoaded', function() {
    loadClientData();
    
    // Inicializar todos los contadores regresivos
    initAllCountdowns();
    
    // Inicializar todas las funcionalidades de botones
    initDepositFunds();
    initNotifications();
    initSearch();
    initHeroButtons();
    initTicketMenus();
    initNotificationItems();
    initViewAllNotifications();
    initRafflesCarousel();
    initParticipateButtons();
    
    // Escuchar eventos de actualización de datos (si se disparan desde otras partes de la app)
    window.addEventListener('clientDataUpdated', function(event) {
        if (event.detail) {
            updateDashboard(event.detail);
            saveClientData(event.detail);
        }
    });
    
    // El logout se maneja automáticamente por ClientLayout.init() en client-layout.js
    // No es necesario agregar código adicional aquí
});

// Ejemplo de uso: Para actualizar datos desde otras partes de la aplicación, puedes usar:
// window.dispatchEvent(new CustomEvent('clientDataUpdated', { 
//     detail: { nombre: 'Nuevo Nombre', saldo: 2000, ... } 
// }));
</script>

</body></html>
