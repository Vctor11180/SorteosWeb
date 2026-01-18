<?php
/**
 * MisTicketsSoporte
 * Sistema de Sorteos Web
 * Página para listar y gestionar tickets de soporte del usuario
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: InicioSesion.php');
    exit;
}

// Obtener datos del usuario desde la base de datos
require_once __DIR__ . '/includes/user-data.php';
$datosUsuario = obtenerDatosUsuarioCompletos();
if (!$datosUsuario) {
    header('Location: InicioSesion.php');
    exit;
}
$usuarioNombre = $datosUsuario['nombre'];
$usuarioEmail = $datosUsuario['email'];
$usuarioSaldo = $datosUsuario['saldo'];
$usuarioAvatar = $datosUsuario['avatar'];
$tipoUsuario = $datosUsuario['tipoUsuario'];
$usuarioId = $datosUsuario['id_usuario'];
?>
<!DOCTYPE html>
<html class="dark" lang="es">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Mis Tickets de Soporte - Plataforma Sorteos</title>
<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<!-- Google Fonts: Inter -->
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&amp;display=swap" rel="stylesheet"/>
<!-- Material Symbols -->
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<!-- Theme Configuration -->
<script>
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "primary": "#2463eb",
              "background-light": "#f6f6f8",
              "background-dark": "#111318",
              "card-dark": "#282d39",
              "text-secondary": "#9da6b9"
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
        body { font-family: 'Inter', sans-serif; }
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
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white overflow-hidden h-screen flex">
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
<div id="sidebar-user-avatar" class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-12 ring-2 ring-primary/30 ring-offset-2 ring-offset-[#111318] shadow-lg" data-alt="User profile picture" style='background-image: url("<?php echo htmlspecialchars($usuarioAvatar); ?>");'>
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
<a id="nav-dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl text-[#9da6b9] hover:bg-gradient-to-r hover:from-[#282d39]/80 hover:to-[#323846]/50 hover:text-white transition-all duration-200 hover:shadow-md" href="DashboardCliente.php">
<span class="material-symbols-outlined text-xl">dashboard</span>
<p class="text-sm font-medium">Dashboard</p>
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
<a id="nav-soporte" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-gradient-to-r from-primary to-blue-600 text-white shadow-lg shadow-primary/20 transition-all duration-200 hover:shadow-xl hover:shadow-primary/30" href="ContactoSoporteCliente.php">
<span class="material-symbols-outlined text-xl">support_agent</span>
<p class="text-sm font-bold">Soporte</p>
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
<!-- Mobile Menu Toggle -->
<button id="mobile-menu-toggle" class="lg:hidden text-white mr-4" aria-label="Abrir menú de navegación">
<span class="material-symbols-outlined">menu</span>
</button>
<!-- Page Title -->
<h1 class="text-xl font-bold text-white hidden sm:block">Mis Tickets de Soporte</h1>
<div class="ml-auto flex items-center gap-4">
<a href="ContactoSoporteCliente.php" class="px-4 py-2 bg-primary hover:bg-blue-600 text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">add</span>
Nuevo Ticket
</a>
</div>
</header>
<!-- Scrollable Content Area -->
<div class="flex-1 overflow-y-auto overflow-x-hidden p-6 lg:p-10 space-y-6">
<!-- Filtros -->
<div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
<div class="flex flex-wrap gap-3">
<button id="filter-all" class="filter-btn px-4 py-2 rounded-lg bg-primary text-white text-sm font-semibold transition-colors" data-estado="all">
Todos
</button>
<button id="filter-abierto" class="filter-btn px-4 py-2 rounded-lg bg-[#282d39] hover:bg-[#323846] text-[#9da6b9] hover:text-white text-sm font-semibold transition-colors" data-estado="Abierto">
Abiertos
</button>
<button id="filter-proceso" class="filter-btn px-4 py-2 rounded-lg bg-[#282d39] hover:bg-[#323846] text-[#9da6b9] hover:text-white text-sm font-semibold transition-colors" data-estado="En Proceso">
En Proceso
</button>
<button id="filter-cerrado" class="filter-btn px-4 py-2 rounded-lg bg-[#282d39] hover:bg-[#323846] text-[#9da6b9] hover:text-white text-sm font-semibold transition-colors" data-estado="Cerrado">
Cerrados
</button>
</div>
</div>
<!-- Lista de Tickets -->
<div id="tickets-container" class="space-y-4">
<!-- Los tickets se cargarán aquí dinámicamente -->
<div class="flex items-center justify-center py-12">
<div class="text-center">
<span class="material-symbols-outlined text-6xl text-[#9da6b9] mb-4">sync</span>
<p class="text-[#9da6b9] text-sm">Cargando tickets...</p>
</div>
</div>
</div>
<!-- Mensaje cuando no hay tickets -->
<div id="no-tickets" class="hidden flex items-center justify-center py-12">
<div class="text-center">
<span class="material-symbols-outlined text-6xl text-[#9da6b9] mb-4">inbox</span>
<p class="text-white text-lg font-semibold mb-2">No hay tickets</p>
<p class="text-[#9da6b9] text-sm">Aún no has creado ningún ticket de soporte.</p>
<a href="ContactoSoporteCliente.php" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-primary hover:bg-blue-600 text-white text-sm font-semibold rounded-lg transition-colors">
<span class="material-symbols-outlined text-[18px]">add</span>
Crear primer ticket
</a>
</div>
</div>
</div>
</main>
<!-- Scripts -->
<script src="js/custom-alerts.js"></script>
<script src="js/client-layout.js"></script>
<script>
// Datos del usuario desde PHP
const userSessionData = {
    nombre: '<?php echo addslashes($usuarioNombre); ?>',
    tipoUsuario: '<?php echo addslashes($tipoUsuario); ?>',
    email: '<?php echo addslashes($usuarioEmail); ?>',
    saldo: <?php echo number_format($usuarioSaldo, 2, '.', ''); ?>,
    avatar: '<?php echo addslashes($usuarioAvatar); ?>'
};

// Actualizar localStorage
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

// Estado de la aplicación
let currentFilter = 'all';
let tickets = [];

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    if (window.ClientLayout) {
        ClientLayout.init('soporte');
    }
    
    // Cargar tickets
    loadTickets();
    
    // Configurar filtros
    setupFilters();
});

// Cargar tickets desde la API
async function loadTickets(estado = null) {
    try {
        const url = estado && estado !== 'all' 
            ? `api_soporte.php?action=get_my_tickets&estado=${estado}`
            : 'api_soporte.php?action=get_my_tickets';
        
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success) {
            tickets = result.data || [];
            renderTickets();
        } else {
            if (typeof customToast === 'function') {
                customToast('Error al cargar tickets: ' + (result.error || 'Error desconocido'), 'error', 5000);
            }
            renderTickets([]);
        }
    } catch (error) {
        console.error('Error al cargar tickets:', error);
        if (typeof customToast === 'function') {
            customToast('Error de conexión al cargar tickets', 'error', 5000);
        }
        renderTickets([]);
    }
}

// Renderizar tickets
function renderTickets(ticketsToRender = tickets) {
    const container = document.getElementById('tickets-container');
    const noTickets = document.getElementById('no-tickets');
    
    if (!container) return;
    
    if (ticketsToRender.length === 0) {
        container.innerHTML = '';
        noTickets.classList.remove('hidden');
        return;
    }
    
    noTickets.classList.add('hidden');
    
    container.innerHTML = ticketsToRender.map(ticket => {
        const fecha = new Date(ticket.fecha_creacion);
        const fechaFormateada = fecha.toLocaleDateString('es-ES', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        // Determinar colores según estado y prioridad
        let estadoClass = '';
        let prioridadClass = '';
        
        if (ticket.estado === 'Abierto') {
            estadoClass = 'bg-green-500/10 text-green-500';
        } else if (ticket.estado === 'En Proceso') {
            estadoClass = 'bg-yellow-500/10 text-yellow-500';
        } else if (ticket.estado === 'Cerrado') {
            estadoClass = 'bg-gray-500/10 text-gray-400';
        }
        
        if (ticket.prioridad === 'Alta') {
            prioridadClass = 'bg-red-500/10 text-red-500';
        } else if (ticket.prioridad === 'Media') {
            prioridadClass = 'bg-yellow-500/10 text-yellow-500';
        } else {
            prioridadClass = 'bg-blue-500/10 text-blue-400';
        }
        
        return `
            <div class="ticket-item bg-[#151a23] rounded-lg p-5 border border-[#282d39] hover:border-primary/50 transition-colors cursor-pointer" data-ticket-id="${ticket.id_ticket}" onclick="window.location.href='DetalleTicketSoporte.php?id=${ticket.id_ticket}'">
                <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-white font-semibold text-lg truncate">${escapeHtml(ticket.asunto)}</h3>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold ${estadoClass}">
                                ${ticket.estado}
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold ${prioridadClass}">
                                ${ticket.prioridad}
                            </span>
                        </div>
                        <p class="text-[#9da6b9] text-sm mb-3 line-clamp-2">${escapeHtml(ticket.mensaje)}</p>
                        <div class="flex items-center gap-4 text-xs text-[#9da6b9]">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">schedule</span>
                                ${fechaFormateada}
                            </span>
                            ${ticket.responsable ? `
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">person</span>
                                    ${escapeHtml(ticket.responsable)}
                                </span>
                            ` : ''}
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="DetalleTicketSoporte.php?id=${ticket.id_ticket}" class="px-4 py-2 bg-primary hover:bg-blue-600 text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                            Ver Detalles
                        </a>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

// Configurar filtros
function setupFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const estado = this.getAttribute('data-estado');
            currentFilter = estado;
            
            // Actualizar estilos de botones
            filterButtons.forEach(b => {
                if (b === this) {
                    b.className = 'filter-btn px-4 py-2 rounded-lg bg-primary text-white text-sm font-semibold transition-colors';
                } else {
                    b.className = 'filter-btn px-4 py-2 rounded-lg bg-[#282d39] hover:bg-[#323846] text-[#9da6b9] hover:text-white text-sm font-semibold transition-colors';
                }
            });
            
            // Cargar tickets filtrados
            loadTickets(estado === 'all' ? null : estado);
        });
    });
}

// Función auxiliar para escapar HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
</body>
</html>
