<?php
/**
 * ListadoSorteosActivos
 * Sistema de Sorteos Web
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación para páginas protegidas
$protectedPages = ['DashboardCliente', 'AjustesPefilCliente', 'MisBoletosCliente', 'MisGanancias', 'SeleccionBoletos', 'SorteoClienteDetalles', 'FinalizarPagoBoletos'];
if (in_array('ListadoSorteosActivos', $protectedPages) && (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true)) {
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

// Los sorteos se cargarán dinámicamente desde la API
// Ya no se cargan desde PHP para permitir actualización en tiempo real
?>
<!DOCTYPE html>

<html class="dark" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Sorteos Web - Listado de Sorteos Activos</title>
<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;family=Noto+Sans:wght@400;500;700&amp;display=swap" rel="stylesheet"/>
<!-- Material Symbols -->
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<!-- Theme Configuration -->
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#2463eb",
                        "background-light": "#f6f6f8",
                        "background-dark": "#111318",
                        "card-dark": "#282d39",
                        "text-secondary": "#9da6b9",
                    },
                    fontFamily: {
                        "display": ["Inter", "Noto Sans", "sans-serif"]
                    },
                    borderRadius: {"DEFAULT": "0.5rem", "lg": "0.75rem", "xl": "1rem", "full": "9999px"},
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
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white font-display overflow-x-hidden h-screen flex">
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
<a id="nav-dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl text-[#9da6b9] hover:bg-gradient-to-r hover:from-[#282d39]/80 hover:to-[#323846]/50 hover:text-white transition-all duration-200 hover:shadow-md" href="DashboardCliente.php">
<span class="material-symbols-outlined text-xl">dashboard</span>
<p class="text-sm font-medium">Dashboard</p>
</a>
<a id="nav-sorteos" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-gradient-to-r from-primary to-blue-600 text-white shadow-lg shadow-primary/20 transition-all duration-200 hover:shadow-xl hover:shadow-primary/30" href="ListadoSorteosActivos.php">
<span class="material-symbols-outlined text-xl">local_activity</span>
<p class="text-sm font-bold">Sorteos</p>
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
<header class="h-16 flex items-center justify-between px-6 lg:px-10 border-b border-[#282d39] bg-[#111318] sticky top-0 z-20">
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
<input class="block w-full pl-10 pr-3 py-2 border-none rounded-lg leading-5 bg-card-dark text-white placeholder-text-secondary focus:outline-none focus:ring-1 focus:ring-primary sm:text-sm" placeholder="Buscar sorteos..." type="text"/>
</div>
</div>
<!-- Right Actions -->
<div class="flex items-center gap-4 ml-auto">
<button class="flex items-center justify-center h-10 px-4 bg-primary hover:bg-primary/90 text-white text-sm font-bold rounded-lg transition-colors shadow-[0_0_15px_rgba(36,99,235,0.3)]">
<span class="hidden sm:inline">Depositar Fondos</span>
<span class="sm:hidden">+</span>
</button>
<div class="h-6 w-px bg-[#282d39] mx-2"></div>
<button class="relative flex items-center justify-center size-10 rounded-lg bg-card-dark hover:bg-[#353b4b] text-white transition-colors">
<span class="material-symbols-outlined">notifications</span>
<span class="absolute top-2.5 right-2.5 size-2 bg-red-500 rounded-full border border-card-dark"></span>
</button>
</div>
</header>
<!-- Scrollable Content Area -->
<div class="flex-1 overflow-y-auto overflow-x-hidden p-6 lg:p-10 space-y-8">
<!-- Hero Section (se llenará dinámicamente con JavaScript) -->
<div id="hero-section-container"></div>
<!-- Main Content Area -->
<div class="w-full flex flex-col gap-8">
<!-- Page Heading & Search -->
<div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-6">
<div class="flex flex-col gap-2">
<h2 class="text-white text-4xl font-black leading-tight tracking-[-0.033em]">Sorteos Activos</h2>
<p class="text-[#9da6b9] text-base font-normal leading-normal">Encuentra tu próximo gran premio y participa.</p>
</div>
<div class="w-full lg:w-auto lg:min-w-[400px]">
<label class="flex flex-col h-12 w-full">
<div class="flex w-full flex-1 items-stretch rounded-lg h-full bg-card-dark border border-[#282d39] focus-within:border-primary transition-colors">
<div class="text-[#9da6b9] flex items-center justify-center pl-4 pr-2">
<span class="material-symbols-outlined">search</span>
</div>
<input id="search-sorteos-input" class="flex w-full min-w-0 flex-1 resize-none bg-transparent text-white focus:outline-0 placeholder:text-[#9da6b9]/70 px-2 text-base font-normal leading-normal" placeholder="Buscar sorteos por nombre o premio..." value=""/>
</div>
</label>
</div>
</div>
<!-- Filters / Chips -->
<div class="flex flex-wrap gap-3 pb-2 overflow-x-auto no-scrollbar">
<button class="flex h-9 shrink-0 items-center justify-center gap-x-2 rounded-full bg-primary pl-4 pr-4 transition-transform active:scale-95">
<p class="text-white text-sm font-medium leading-normal">Todos</p>
</button>
<button class="flex h-9 shrink-0 items-center justify-center gap-x-2 rounded-full bg-[#282d39] hover:bg-[#323846] pl-4 pr-3 border border-transparent hover:border-white/10 transition-all">
<p class="text-white text-sm font-medium leading-normal">Electrónica</p>
<span class="material-symbols-outlined text-[#9da6b9] text-[20px]">expand_more</span>
</button>
<button class="flex h-9 shrink-0 items-center justify-center gap-x-2 rounded-full bg-[#282d39] hover:bg-[#323846] pl-4 pr-3 border border-transparent hover:border-white/10 transition-all">
<p class="text-white text-sm font-medium leading-normal">Vehículos</p>
<span class="material-symbols-outlined text-[#9da6b9] text-[20px]">expand_more</span>
</button>
<button class="flex h-9 shrink-0 items-center justify-center gap-x-2 rounded-full bg-[#282d39] hover:bg-[#323846] pl-4 pr-3 border border-transparent hover:border-white/10 transition-all">
<p class="text-white text-sm font-medium leading-normal">Efectivo</p>
<span class="material-symbols-outlined text-[#9da6b9] text-[20px]">expand_more</span>
</button>
<button class="flex h-9 shrink-0 items-center justify-center gap-x-2 rounded-full bg-[#282d39] hover:bg-[#323846] pl-4 pr-3 border border-transparent hover:border-white/10 transition-all">
<p class="text-white text-sm font-medium leading-normal">Inmuebles</p>
<span class="material-symbols-outlined text-[#9da6b9] text-[20px]">expand_more</span>
</button>
</div>
<!-- Raffles Grid (se llenará dinámicamente con JavaScript) -->
<div id="sorteos-container">
    <div id="sorteos-loading" class="text-center py-12">
        <p class="text-[#9da6b9] text-lg">Cargando sorteos...</p>
    </div>
    <div id="sorteos-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" style="display: none;"></div>
    <div id="sorteos-empty" class="text-center py-12" style="display: none;">
        <p class="text-[#9da6b9] text-lg">No hay sorteos disponibles en este momento.</p>
    </div>
</div>
<!-- Pagination / Load More -->
<div class="flex justify-center mt-8 mb-8">
<button class="flex items-center justify-center gap-2 px-6 py-3 rounded-lg border border-[#282d39] bg-card-dark text-white hover:bg-[#282d39] transition-colors font-medium text-sm">
<span class="material-symbols-outlined text-xl">refresh</span>
                            Cargar más sorteos
                        </button>
</div>
</div>
</div>
</div>
</main>

<!-- Client Layout Script -->
<script src="js/custom-alerts.js"></script>
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

// Los sorteos se cargarán dinámicamente desde la API
let sorteosDataFromDB = [];

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

// Inicializar layout del cliente
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar el layout con 'sorteos' como página activa (con manejo de errores)
    try {
        if (window.ClientLayout && typeof ClientLayout.init === 'function') {
            ClientLayout.init('sorteos');
        }
    } catch (error) {
        console.warn('Error al inicializar ClientLayout:', error);
        // Continuar aunque falle el layout
    }
    
    // Cargar sorteos desde la API (esto debe ejecutarse siempre)
    loadSorteosFromAPI();
    
    // Inicializar funcionalidades de botones
    try {
        initListadoButtons();
    } catch (error) {
        console.warn('Error al inicializar botones:', error);
    }
});

// Función para cargar sorteos desde la API
async function loadSorteosFromAPI() {
    try {
        console.log('Cargando sorteos desde API...');
        const response = await fetch('api_sorteos.php?action=list_active');
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status} ${response.statusText}`);
        }
        
        const text = await response.text();
        console.log('Respuesta recibida:', text.substring(0, 200));
        
        let data;
        try {
            data = JSON.parse(text);
        } catch (parseError) {
            console.error('Error al parsear JSON:', parseError);
            console.error('Texto recibido:', text);
            showError('Error en la respuesta del servidor. Por favor, recarga la página.');
            return;
        }
        
        console.log('Datos parseados:', data);
        
        if (data.success && data.data && Array.isArray(data.data)) {
            sorteosDataFromDB = data.data;
            console.log(`Se cargaron ${sorteosDataFromDB.length} sorteos`);
            console.log('Primer sorteo:', sorteosDataFromDB[0]);
            
            // Renderizar hero section con el primer sorteo
            if (sorteosDataFromDB.length > 0) {
                console.log('Renderizando hero section...');
                renderHeroSection(sorteosDataFromDB[0]);
            }
            
            // Renderizar grid de sorteos
            console.log('Renderizando grid de sorteos...');
            renderSorteosGrid(sorteosDataFromDB);
            
            // Inicializar contadores de tiempo después de renderizar
            setTimeout(() => {
                console.log('Inicializando contadores de tiempo...');
                initRaffleCardTimers();
            }, 100);
        } else {
            console.error('Error en la respuesta:', data);
            console.error('data.success:', data.success);
            console.error('data.data:', data.data);
            console.error('Es array?', Array.isArray(data.data));
            showError(data.error || 'No se pudieron cargar los sorteos. Por favor, recarga la página.');
        }
    } catch (error) {
        console.error('Error al cargar sorteos:', error);
        console.error('Stack:', error.stack);
        showError('Error al cargar los sorteos: ' + error.message);
    }
}

// Función para renderizar el hero section
function renderHeroSection(sorteo) {
    console.log('renderHeroSection llamado con:', sorteo);
    const container = document.getElementById('hero-section-container');
    if (!container) {
        console.error('No se encontró el elemento hero-section-container');
        return;
    }
    console.log('Container encontrado, renderizando hero section...');
    
    const imagenHero = sorteo.imagen_url || 'https://via.placeholder.com/800x400?text=Sorteo';
    const tiempoRestante = sorteo.tiempo_restante;
    const diasHero = tiempoRestante.dias || 0;
    const horasHero = tiempoRestante.horas || 0;
    const minutosHero = tiempoRestante.minutos || 0;
    const textoTiempoHero = diasHero > 0 
        ? `${diasHero}d ${horasHero}h ${minutosHero}m`
        : `${horasHero}h ${minutosHero}m`;
    
    container.innerHTML = `
        <div class="w-full bg-gradient-to-b from-[#111318] to-[#161b26] rounded-xl overflow-hidden relative min-h-[300px] flex items-end p-8 sm:p-12">
            <div class="layout-content-container flex flex-col max-w-[1200px] mx-auto w-full">
                <div class="@container">
                    <div class="flex flex-col gap-6 rounded-xl bg-card-dark p-6 shadow-lg border border-[#282d39] @[864px]:flex-row @[864px]:items-center">
                        <div class="w-full bg-center bg-no-repeat bg-cover rounded-lg aspect-video @[864px]:w-1/2 min-h-[250px]" style='background-image: url("${imagenHero}");'>
                            <div class="w-full h-full flex items-start justify-end p-4">
                                <span class="bg-primary/90 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider backdrop-blur-sm shadow-md">Destacado</span>
                            </div>
                        </div>
                        <div class="flex flex-col gap-6 @[864px]:w-1/2 @[864px]:pl-6">
                            <div class="flex flex-col gap-3 text-left">
                                <h1 class="text-white text-3xl font-black leading-tight tracking-[-0.033em] md:text-5xl">${escapeHtml(sorteo.titulo)}</h1>
                                <p class="text-[#9da6b9] text-base font-normal leading-relaxed">${escapeHtml(sorteo.descripcion || 'Participa y gana este increíble premio. El tiempo se acaba, ¡asegura tu boleto hoy!')}</p>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="material-symbols-outlined text-primary">timer</span>
                                    <span class="text-white font-mono font-medium" id="hero-countdown-${sorteo.id_sorteo}">Cierra en: ${textoTiempoHero}</span>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-4">
                                <a href="SorteoClienteDetalles.php?id=${sorteo.id_sorteo}" class="flex min-w-[140px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-12 px-6 bg-primary text-white text-base font-bold leading-normal tracking-[0.015em] hover:bg-blue-600 transition-all shadow-lg shadow-blue-900/20">
                                    <span class="truncate">Participar Ahora</span>
                                </a>
                                <a href="SorteoClienteDetalles.php?id=${sorteo.id_sorteo}" class="flex min-w-[140px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-12 px-6 bg-[#282d39] text-white text-base font-medium leading-normal hover:bg-[#343a49] transition-all border border-white/5">
                                    <span class="truncate">Ver Detalles</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Función para renderizar el grid de sorteos
function renderSorteosGrid(sorteos) {
    console.log('renderSorteosGrid llamado con:', sorteos);
    
    const loadingEl = document.getElementById('sorteos-loading');
    const gridEl = document.getElementById('sorteos-grid');
    const emptyEl = document.getElementById('sorteos-empty');
    
    console.log('Elementos encontrados:', {
        loading: !!loadingEl,
        grid: !!gridEl,
        empty: !!emptyEl
    });
    
    if (loadingEl) {
        loadingEl.style.display = 'none';
    }
    
    if (!sorteos || sorteos.length === 0) {
        console.log('No hay sorteos para renderizar');
        if (emptyEl) emptyEl.style.display = 'block';
        if (gridEl) gridEl.style.display = 'none';
        return;
    }
    
    if (emptyEl) emptyEl.style.display = 'none';
    if (gridEl) {
        console.log(`Renderizando ${sorteos.length} sorteos`);
        gridEl.style.display = 'grid';
        gridEl.innerHTML = '';
        
        sorteos.forEach((sorteo, index) => {
            const imagenUrl = sorteo.imagen_url || 'https://via.placeholder.com/400x200?text=Sorteo';
            const tiempoRestante = sorteo.tiempo_restante;
            const diasRestantes = tiempoRestante.dias || 0;
            const horasRestantes = tiempoRestante.horas || 0;
            const minutosRestantes = tiempoRestante.minutos || 0;
            
            // Formatear tiempo restante
            let textoTiempo, colorTiempo;
            if (diasRestantes > 0) {
                textoTiempo = `${diasRestantes}d ${horasRestantes}h ${minutosRestantes}m`;
                colorTiempo = sorteo.esta_por_finalizar ? 'text-red-400' : 'text-orange-400';
            } else {
                textoTiempo = `${String(horasRestantes).padStart(2, '0')}h ${String(minutosRestantes).padStart(2, '0')}m`;
                colorTiempo = 'text-red-500 animate-pulse';
            }
            
            const porcentajeVendido = sorteo.porcentaje_vendido || 0;
            const colorBarra = porcentajeVendido >= 90 ? 'bg-red-500' : 'bg-primary';
            
            // Badge según estado
            let badge = '', badgeColor = '';
            if (porcentajeVendido >= 90) {
                badge = 'ÚLTIMOS BOLETOS';
                badgeColor = 'bg-red-500/90';
            } else if (porcentajeVendido >= 50) {
                badge = 'POPULAR';
                badgeColor = 'bg-primary/90';
            } else {
                badge = 'NUEVO';
                badgeColor = 'bg-green-500/90';
            }
            
            const card = document.createElement('div');
            card.className = 'group flex flex-col bg-card-dark rounded-xl overflow-hidden border border-[#282d39] hover:border-primary/50 transition-all hover:shadow-lg hover:-translate-y-1 cursor-pointer raffle-card';
            card.setAttribute('data-raffle-id', sorteo.id_sorteo);
            card.onclick = () => window.location.href = `SorteoClienteDetalles.php?id=${sorteo.id_sorteo}`;
            
            card.innerHTML = `
                <div class="relative h-48 bg-cover bg-center" style='background-image: url("${imagenUrl}");'>
                    ${badge ? `<div class="absolute top-3 left-3 ${badgeColor} text-white text-xs font-bold px-2 py-1 rounded shadow-sm backdrop-blur-sm">${badge}</div>` : ''}
                    <div class="absolute bottom-3 right-3 bg-black/70 text-white text-xs font-bold px-2 py-1 rounded backdrop-blur-sm flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">confirmation_number</span>
                        $${parseFloat(sorteo.precio_boleto).toFixed(2)} / boleto
                    </div>
                </div>
                <div class="flex flex-col p-5 gap-4 flex-1">
                    <div>
                        <h3 class="text-white text-lg font-bold mb-1 group-hover:text-primary transition-colors">${escapeHtml(sorteo.titulo)}</h3>
                        <p class="text-[#9da6b9] text-sm line-clamp-2">${escapeHtml(sorteo.descripcion || 'Participa y gana este increíble premio.')}</p>
                    </div>
                    <div class="mt-auto flex flex-col gap-2">
                        <div class="flex justify-between text-xs font-medium text-[#9da6b9]">
                            <span>Boletos vendidos</span>
                            <span class="text-white">${sorteo.boletos_vendidos} / ${sorteo.total_boletos}</span>
                        </div>
                        <div class="w-full bg-[#282d39] rounded-full h-2 overflow-hidden">
                            <div class="${colorBarra} h-2 rounded-full" style="width: ${porcentajeVendido}%"></div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t border-[#282d39]">
                        <div class="flex items-center gap-1.5 ${colorTiempo}">
                            <span class="material-symbols-outlined text-[18px]">timer</span>
                            <span class="text-sm font-medium tabular-nums" id="countdown-card-${sorteo.id_sorteo}" data-seconds="${tiempoRestante.total_segundos || 0}">${textoTiempo}</span>
                        </div>
                        <a href="SorteoClienteDetalles.php?id=${sorteo.id_sorteo}" onclick="event.stopPropagation();" class="text-primary text-sm font-bold hover:underline cursor-pointer z-10 relative">Ver Detalles</a>
                    </div>
                </div>
            `;
            
            gridEl.appendChild(card);
        });
        
        console.log('Sorteos renderizados exitosamente. Total de cards:', gridEl.children.length);
    } else {
        console.error('No se encontró el elemento sorteos-grid');
    }
}

// Función para escapar HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Función para mostrar error
function showError(message) {
    const loadingEl = document.getElementById('sorteos-loading');
    const gridEl = document.getElementById('sorteos-grid');
    const emptyEl = document.getElementById('sorteos-empty');
    
    if (loadingEl) {
        loadingEl.innerHTML = `<p class="text-red-400 text-lg">${escapeHtml(message)}</p>`;
        loadingEl.style.display = 'block';
    }
    if (gridEl) gridEl.style.display = 'none';
    if (emptyEl) emptyEl.style.display = 'none';
}

// Función para convertir datos de BD a formato esperado por JavaScript
function convertirSorteoParaJS(sorteo) {
    return {
        id: sorteo.id_sorteo.toString(),
        titulo: sorteo.titulo,
        descripcion: sorteo.descripcion || '',
        precio: sorteo.precio_boleto,
        boletosVendidos: sorteo.boletos_vendidos,
        boletosTotales: sorteo.total_boletos,
        tiempoRestante: sorteo.tiempo_restante,
        imagen: sorteo.imagen_url || ''
    };
}

// Datos de los sorteos disponibles (convertidos desde BD)
const sorteosData = {};
if (sorteosDataFromDB && Array.isArray(sorteosDataFromDB)) {
    sorteosDataFromDB.forEach(sorteo => {
        sorteosData[sorteo.id_sorteo.toString()] = convertirSorteoParaJS(sorteo);
    });
}

// Función para ver detalles del sorteo
function viewSorteoDetails(sorteoId) {
    // sorteoId puede ser un número (ID) o un string
    const sorteo = sorteosData[sorteoId.toString()];
    if (sorteo) {
        localStorage.setItem('selectedSorteo', JSON.stringify(sorteo));
    }
}

// Función para inicializar contadores de tiempo
function initRaffleCardTimers() {
    // Buscar todos los elementos con data-seconds
    const countdownElements = document.querySelectorAll('[id^="countdown-card-"]');
    
    countdownElements.forEach((element, index) => {
        const seconds = parseInt(element.getAttribute('data-seconds')) || 0;
        
        if (seconds <= 0) {
            element.textContent = 'Finalizado';
            element.classList.add('text-red-500');
            return;
        }
        
        // Calcular tiempo de finalización basado en los datos del sorteo
        const sorteoId = element.closest('.raffle-card')?.getAttribute('data-raffle-id');
        if (sorteoId && sorteosData[sorteoId]) {
            const sorteo = sorteosData[sorteoId];
            const tiempoRestante = sorteo.tiempoRestante;
            const totalSegundos = tiempoRestante.total_segundos || 
                (tiempoRestante.dias * 86400 + tiempoRestante.horas * 3600 + 
                 tiempoRestante.minutos * 60 + tiempoRestante.segundos);
            
            const endTime = Math.floor(Date.now() / 1000) + totalSegundos;
            element.setAttribute('data-end-time', endTime.toString());
        } else {
            // Fallback: usar los segundos del atributo
            const endTime = Math.floor(Date.now() / 1000) + seconds;
            element.setAttribute('data-end-time', endTime.toString());
        }
        
        // Función para actualizar el contador
        const updateCountdown = () => {
            const endTime = parseInt(element.getAttribute('data-end-time')) || 0;
            const now = Math.floor(Date.now() / 1000);
            const remaining = endTime - now;
            
            if (remaining <= 0) {
                element.textContent = 'Finalizado';
                element.classList.add('text-red-500');
                element.classList.remove('text-yellow-500', 'text-orange-400');
                return;
            }
            
            const days = Math.floor(remaining / 86400);
            const hours = Math.floor((remaining % 86400) / 3600);
            const minutes = Math.floor((remaining % 3600) / 60);
            const secs = remaining % 60;
            
            // Actualizar texto
            if (days > 0) {
                element.textContent = `${days}d ${hours}h ${minutes}m`;
            } else if (hours > 0) {
                element.textContent = `${hours}h ${minutes}m ${secs}s`;
            } else {
                element.textContent = `${minutes}m ${secs}s`;
            }
            
            // Cambiar color si está por finalizar
            if (remaining < 86400) {
                element.classList.remove('text-yellow-500', 'text-orange-400');
                element.classList.add('text-red-400', 'animate-pulse');
            }
        };
        
        // Actualizar inmediatamente
        updateCountdown();
        
        // Actualizar cada segundo
        setInterval(updateCountdown, 1000);
    });
}

// Función para inicializar funcionalidades de botones
function initListadoButtons() {
    // Buscador
    initSorteosSearch();
    
    // Filtros
    initFilters();
    
    // Botón Depositar Fondos
    initDepositFunds();
    
    // Botón Notificaciones
    initNotificationsButton();
    
    // Botón Cargar más sorteos
    initLoadMoreButton();
    
    // Botón Ver Ganador (para sorteos finalizados)
    initViewWinnerButton();
}

// Función para inicializar búsqueda de sorteos
function initSorteosSearch() {
    const searchInput = document.getElementById('search-sorteos-input');
    if (!searchInput) return;
    
    let searchTimeout;
    
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();
        
        searchTimeout = setTimeout(() => {
            if (query) {
                // Buscar usando la API
                searchSorteosAPI(query);
            } else {
                // Mostrar todos los sorteos
                renderSorteosGrid(sorteosDataFromDB);
                setTimeout(() => initRaffleCardTimers(), 100);
            }
        }, 300);
    });
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            if (query) {
                searchSorteosAPI(query);
            } else {
                renderSorteosGrid(sorteosDataFromDB);
                setTimeout(() => initRaffleCardTimers(), 100);
            }
        }
    });
}

// Función para buscar sorteos usando la API
async function searchSorteosAPI(query) {
    try {
        const response = await fetch(`api_sorteos.php?action=list_active&search=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (data.success && data.data) {
            renderSorteosGrid(data.data);
            setTimeout(() => initRaffleCardTimers(), 100);
        }
    } catch (error) {
        console.error('Error al buscar sorteos:', error);
    }
}

// Función para buscar sorteos
function searchSorteos(query) {
    const gridEl = document.getElementById('sorteos-grid');
    if (!gridEl) return;
    
    const sorteoCards = gridEl.querySelectorAll('.raffle-card');
    
    if (!query) {
        // Mostrar todos
        sorteoCards.forEach(card => {
            card.style.display = '';
        });
        return;
    }
    
    sorteoCards.forEach(card => {
        const title = card.querySelector('h3')?.textContent.toLowerCase() || '';
        const description = card.querySelector('p')?.textContent.toLowerCase() || '';
        
        if (title.includes(query) || description.includes(query)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

// Función para inicializar filtros
function initFilters() {
    const filterButtons = document.querySelectorAll('.flex-wrap.gap-3 button');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remover clase activa de todos
            filterButtons.forEach(btn => {
                btn.classList.remove('bg-primary');
                btn.classList.add('bg-[#282d39]', 'hover:bg-[#323846]');
            });
            
            // Agregar clase activa al clickeado
            this.classList.remove('bg-[#282d39]', 'hover:bg-[#323846]');
            this.classList.add('bg-primary');
            
            // Filtrar sorteos
            const filterText = this.textContent.trim().toLowerCase();
            filterSorteos(filterText);
        });
    });
}

// Función para filtrar sorteos
function filterSorteos(filter) {
    const sorteoCards = document.querySelectorAll('.grid > div');
    
    if (filter === 'todos') {
        sorteoCards.forEach(card => {
            card.style.display = '';
        });
        return;
    }
    
    // Filtrar por categoría
    sorteoCards.forEach(card => {
        const title = card.querySelector('h3')?.textContent.toLowerCase() || '';
        const description = card.querySelector('p')?.textContent.toLowerCase() || '';
        const text = title + ' ' + description;
        
        let show = false;
        
        if (filter === 'electrónica' && (text.includes('iphone') || text.includes('ps5') || text.includes('tv') || text.includes('smartphone') || text.includes('gamer'))) {
            show = true;
        } else if (filter === 'vehículos' && (text.includes('auto') || text.includes('moto') || text.includes('vehículo'))) {
            show = true;
        } else if (filter === 'efectivo' && (text.includes('efectivo') || text.includes('premio') || text.includes('$'))) {
            show = true;
        } else if (filter === 'inmuebles' && (text.includes('inmueble') || text.includes('casa') || text.includes('apartamento'))) {
            show = true;
        }
        
        card.style.display = show ? '' : 'none';
    });
}

// Función para botón Depositar Fondos
function initDepositFunds() {
    const depositBtn = Array.from(document.querySelectorAll('button')).find(
        btn => btn.textContent.includes('Depositar Fondos') || (btn.textContent.includes('+') && btn.closest('header'))
    );
    
    if (depositBtn) {
        depositBtn.addEventListener('click', function() {
            customAlert('Funcionalidad de depósito: Aquí se abriría el formulario o página para depositar fondos.\n\nPuedes depositar fondos desde tu dashboard principal.', 'Depositar Fondos', 'info');
        });
    }
}

// Función para botón Notificaciones
function initNotificationsButton() {
    const notificationsBtn = Array.from(document.querySelectorAll('button')).find(
        btn => btn.querySelector('.material-symbols-outlined')?.textContent === 'notifications'
    );
    
    if (notificationsBtn) {
        notificationsBtn.addEventListener('click', function() {
            customAlert('Panel de notificaciones: Aquí se mostrarían tus notificaciones recientes.\n\nPuedes ver todas tus notificaciones desde el dashboard.', 'Notificaciones', 'info');
        });
    }
}

// Función para botón Cargar más sorteos
function initLoadMoreButton() {
    const loadMoreBtn = Array.from(document.querySelectorAll('button')).find(
        btn => btn.textContent.includes('Cargar más sorteos')
    );
    
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            // Simular carga de más sorteos
            this.disabled = true;
            const originalHTML = this.innerHTML;
            this.innerHTML = '<span class="material-symbols-outlined text-xl animate-spin">refresh</span> Cargando...';
            
            setTimeout(() => {
                customToast('Se cargaron más sorteos. En una implementación real, esto cargaría sorteos adicionales desde el servidor.', 'success', 3000);
                this.disabled = false;
                this.innerHTML = originalHTML;
            }, 1500);
        });
    }
}

// Función para botón Ver Ganador
function initViewWinnerButton() {
    const viewWinnerBtn = Array.from(document.querySelectorAll('button')).find(
        btn => btn.textContent.includes('Ver Ganador')
    );
    
    if (viewWinnerBtn && !viewWinnerBtn.classList.contains('cursor-not-allowed')) {
        viewWinnerBtn.addEventListener('click', function() {
            const card = this.closest('.grid > div');
            const sorteoName = card?.querySelector('h3')?.textContent || 'Sorteo';
            
            customAlert(`Ganador del sorteo "${sorteoName}"\n\nBoleto ganador: #245\nGanador: Juan Pérez\nFecha del sorteo: 15 Oct, 2023\n\n¡Felicidades al ganador!`, 'Ganador del Sorteo', 'success');
        });
    }
}
</script>

</body></html>

<!-- Página para ver los sorteos activos como cliente -->

