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
    header('Location: ../auth/InicioSesion.php');
    exit;
}

// Obtener datos del usuario desde la base de datos
require_once __DIR__ . '/../includes/user-data.php';
$datosUsuario = obtenerDatosUsuarioCompletos();
if (!$datosUsuario) {
    header('Location: ../auth/InicioSesion.php');
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
<?php include '../includes/sidebar.php'; ?>
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
<script src="../assets/assets/js/custom-alerts.js"></script>
<script src="../assets/assets/js/client-layout.js"></script>
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
            ? `../api/api_soporte.php?action=get_my_tickets&estado=${estado}`
            : '../api/api_soporte.php?action=get_my_tickets';
        
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
