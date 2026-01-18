<?php
/**
 * DetalleTicketSoporte
 * Sistema de Sorteos Web
 * Página para ver detalles de un ticket de soporte y responder
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

// Obtener ID del ticket
$ticketId = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$ticketId) {
    header('Location: MisTicketsSoporte.php');
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
<title>Detalle de Ticket - Plataforma Sorteos</title>
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
        textarea::-webkit-scrollbar {
            width: 8px;
        }
        textarea::-webkit-scrollbar-track {
            background: #1c1f27; 
        }
        textarea::-webkit-scrollbar-thumb {
            background: #3b4354; 
            border-radius: 4px;
        }
        textarea::-webkit-scrollbar-thumb:hover {
            background: #4b5563; 
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
<h1 class="text-xl font-bold text-white hidden sm:block">Detalle de Ticket</h1>
<div class="ml-auto flex items-center gap-4">
<a href="MisTicketsSoporte.php" class="px-4 py-2 bg-[#282d39] hover:bg-[#323846] text-[#9da6b9] hover:text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
Volver
</a>
</div>
</header>
<!-- Scrollable Content Area -->
<div class="flex-1 overflow-y-auto overflow-x-hidden p-6 lg:p-10 space-y-6">
<!-- Loading State -->
<div id="loading-state" class="flex items-center justify-center py-12">
<div class="text-center">
<span class="material-symbols-outlined text-6xl text-[#9da6b9] mb-4 animate-spin">sync</span>
<p class="text-[#9da6b9] text-sm">Cargando ticket...</p>
</div>
</div>
<!-- Error State -->
<div id="error-state" class="hidden flex items-center justify-center py-12">
<div class="text-center">
<span class="material-symbols-outlined text-6xl text-red-500 mb-4">error</span>
<p class="text-white text-lg font-semibold mb-2">Error al cargar el ticket</p>
<p id="error-message" class="text-[#9da6b9] text-sm mb-4"></p>
<a href="MisTicketsSoporte.php" class="inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-blue-600 text-white text-sm font-semibold rounded-lg transition-colors">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
Volver a mis tickets
</a>
</div>
</div>
<!-- Ticket Content -->
<div id="ticket-content" class="hidden max-w-4xl mx-auto space-y-6">
<!-- Header del Ticket -->
<div class="bg-[#151a23] rounded-lg p-6 border border-[#282d39]">
<div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between mb-4">
<div class="flex-1">
<h2 id="ticket-asunto" class="text-white text-2xl font-bold mb-2"></h2>
<div class="flex flex-wrap items-center gap-3">
<span id="ticket-estado-badge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"></span>
<span id="ticket-prioridad-badge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"></span>
<span id="ticket-id" class="text-[#9da6b9] text-sm">Ticket #<span id="ticket-id-value"></span></span>
</div>
</div>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-[#282d39]">
<div>
<p class="text-[#9da6b9] text-xs mb-1">Fecha de creación</p>
<p id="ticket-fecha-creacion" class="text-white text-sm font-medium"></p>
</div>
<div id="ticket-responsable-container" class="hidden">
<p class="text-[#9da6b9] text-xs mb-1">Asignado a</p>
<p id="ticket-responsable" class="text-white text-sm font-medium"></p>
</div>
</div>
</div>
<!-- Mensaje del Ticket -->
<div class="bg-[#151a23] rounded-lg p-6 border border-[#282d39]">
<h3 class="text-white text-lg font-semibold mb-4">Mensaje</h3>
<div id="ticket-mensaje" class="text-[#9da6b9] whitespace-pre-wrap leading-relaxed"></div>
</div>
<!-- Formulario de Respuesta (solo si el ticket no está cerrado) -->
<div id="reply-form-container" class="bg-[#151a23] rounded-lg p-6 border border-[#282d39]">
<h3 class="text-white text-lg font-semibold mb-4">Responder al ticket</h3>
<form id="reply-form" class="space-y-4">
<label class="flex flex-col">
<p class="text-white text-sm font-medium mb-2">Tu respuesta</p>
<textarea id="reply-message" class="w-full bg-[#0d1117] border border-[#282d39] hover:border-[#3b4254] text-white text-sm rounded-xl focus:ring-2 focus:ring-primary focus:border-primary px-4 py-3.5 placeholder-[#566074] transition-all duration-200 shadow-inner resize-y" placeholder="Escribe tu respuesta aquí..." rows="5" required></textarea>
</label>
<div class="flex items-center gap-3">
<button type="submit" class="px-6 py-2.5 bg-primary hover:bg-blue-600 text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">send</span>
Enviar Respuesta
</button>
<button type="button" id="close-ticket-btn" class="px-6 py-2.5 bg-[#282d39] hover:bg-[#323846] text-[#9da6b9] hover:text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">close</span>
Cerrar Ticket
</button>
</div>
</form>
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

// Estado
const ticketId = <?php echo $ticketId; ?>;
let ticketData = null;

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    if (window.ClientLayout) {
        ClientLayout.init('soporte');
    }
    
    // Cargar ticket
    loadTicket();
    
    // Configurar formulario de respuesta
    setupReplyForm();
    
    // Configurar botón de cerrar ticket
    setupCloseTicket();
});

// Cargar ticket desde la API
async function loadTicket() {
    try {
        const response = await fetch(`api_soporte.php?action=get_ticket&id=${ticketId}`);
        const result = await response.json();
        
        if (result.success) {
            ticketData = result.data;
            renderTicket();
        } else {
            showError(result.error || 'Error desconocido');
        }
    } catch (error) {
        console.error('Error al cargar ticket:', error);
        showError('Error de conexión al cargar el ticket');
    }
}

// Renderizar ticket
function renderTicket() {
    if (!ticketData) return;
    
    // Ocultar loading
    document.getElementById('loading-state').classList.add('hidden');
    document.getElementById('ticket-content').classList.remove('hidden');
    
    // Llenar datos
    document.getElementById('ticket-asunto').textContent = ticketData.asunto;
    document.getElementById('ticket-id-value').textContent = ticketData.id_ticket;
    document.getElementById('ticket-mensaje').textContent = ticketData.mensaje;
    
    // Formatear fecha
    const fecha = new Date(ticketData.fecha_creacion);
    const fechaFormateada = fecha.toLocaleDateString('es-ES', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    document.getElementById('ticket-fecha-creacion').textContent = fechaFormateada;
    
    // Estado badge
    const estadoBadge = document.getElementById('ticket-estado-badge');
    let estadoClass = '';
    if (ticketData.estado === 'Abierto') {
        estadoClass = 'bg-green-500/10 text-green-500';
    } else if (ticketData.estado === 'En Proceso') {
        estadoClass = 'bg-yellow-500/10 text-yellow-500';
    } else {
        estadoClass = 'bg-gray-500/10 text-gray-400';
    }
    estadoBadge.className = `inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold ${estadoClass}`;
    estadoBadge.textContent = ticketData.estado;
    
    // Prioridad badge
    const prioridadBadge = document.getElementById('ticket-prioridad-badge');
    let prioridadClass = '';
    if (ticketData.prioridad === 'Alta') {
        prioridadClass = 'bg-red-500/10 text-red-500';
    } else if (ticketData.prioridad === 'Media') {
        prioridadClass = 'bg-yellow-500/10 text-yellow-500';
    } else {
        prioridadClass = 'bg-blue-500/10 text-blue-400';
    }
    prioridadBadge.className = `inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold ${prioridadClass}`;
    prioridadBadge.textContent = ticketData.prioridad;
    
    // Responsable
    if (ticketData.responsable) {
        document.getElementById('ticket-responsable-container').classList.remove('hidden');
        document.getElementById('ticket-responsable').textContent = ticketData.responsable.nombre;
    }
    
    // Mostrar/ocultar formulario de respuesta según el estado
    const replyFormContainer = document.getElementById('reply-form-container');
    if (ticketData.estado === 'Cerrado') {
        replyFormContainer.classList.add('hidden');
    } else {
        replyFormContainer.classList.remove('hidden');
    }
}

// Mostrar error
function showError(message) {
    document.getElementById('loading-state').classList.add('hidden');
    document.getElementById('error-state').classList.remove('hidden');
    document.getElementById('error-message').textContent = message;
}

// Configurar formulario de respuesta
function setupReplyForm() {
    const form = document.getElementById('reply-form');
    if (!form) return;
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const respuesta = document.getElementById('reply-message').value.trim();
        
        if (!respuesta || respuesta.length < 10) {
            if (typeof customToast === 'function') {
                customToast('La respuesta debe tener al menos 10 caracteres', 'error', 3000);
            }
            return;
        }
        
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="material-symbols-outlined text-[18px] animate-spin">sync</span> Enviando...';
        
        try {
            const response = await fetch('api_soporte.php?action=reply_ticket', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id_ticket: ticketId,
                    respuesta: respuesta
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                if (typeof customToast === 'function') {
                    customToast('Respuesta enviada exitosamente', 'success', 3000);
                }
                document.getElementById('reply-message').value = '';
                // Recargar ticket para mostrar la respuesta
                loadTicket();
            } else {
                if (typeof customToast === 'function') {
                    customToast('Error: ' + (result.error || 'Error desconocido'), 'error', 5000);
                }
            }
        } catch (error) {
            console.error('Error al enviar respuesta:', error);
            if (typeof customToast === 'function') {
                customToast('Error de conexión al enviar la respuesta', 'error', 5000);
            }
        } finally {
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }
    });
}

// Configurar botón de cerrar ticket
function setupCloseTicket() {
    const closeBtn = document.getElementById('close-ticket-btn');
    if (!closeBtn) return;
    
    closeBtn.addEventListener('click', async function() {
        if (!confirm('¿Estás seguro de que deseas cerrar este ticket? No podrás responder después de cerrarlo.')) {
            return;
        }
        
        closeBtn.disabled = true;
        const originalText = closeBtn.innerHTML;
        closeBtn.innerHTML = '<span class="material-symbols-outlined text-[18px] animate-spin">sync</span> Cerrando...';
        
        try {
            const response = await fetch('api_soporte.php?action=close_ticket', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id_ticket: ticketId
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                if (typeof customToast === 'function') {
                    customToast('Ticket cerrado exitosamente', 'success', 3000);
                }
                // Recargar ticket
                loadTicket();
            } else {
                if (typeof customToast === 'function') {
                    customToast('Error: ' + (result.error || 'Error desconocido'), 'error', 5000);
                }
            }
        } catch (error) {
            console.error('Error al cerrar ticket:', error);
            if (typeof customToast === 'function') {
                customToast('Error de conexión al cerrar el ticket', 'error', 5000);
            }
        } finally {
            closeBtn.disabled = false;
            closeBtn.innerHTML = originalText;
        }
    });
}
</script>
</body>
</html>
