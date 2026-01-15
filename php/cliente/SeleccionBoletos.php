<?php
/**
 * SeleccionBoletos
 * Sistema de Sorteos Web
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación para páginas protegidas
$protectedPages = ['DashboardCliente', 'AjustesPefilCliente', 'MisBoletosCliente', 'MisGanancias', 'SeleccionBoletos', 'SorteoClienteDetalles', 'FinalizarPagoBoletos'];
if (in_array('SeleccionBoletos', $protectedPages) && (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true)) {
    header('Location: InicioSesion.php');
    exit;
}
?>
<!DOCTYPE html>

<html class="dark" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Selección de Boletos - SorteosWeb</title>
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
<!-- Material Symbols -->
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {"DEFAULT": "0.5rem", "lg": "0.75rem", "xl": "1rem", "full": "9999px"},
                },
            },
        }
    </script>
<style>
        /* Custom scrollbar for dark mode feeling */
        ::-webkit-scrollbar {
            width: 8px;
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
        
        /* Animación de spin para el botón de asignar */
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-white overflow-hidden h-screen flex">
<!-- Sidebar -->
<aside class="w-72 hidden lg:flex flex-col border-r border-[#282d39] bg-[#111318] h-full">
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
<div class="flex items-center gap-3 p-3 rounded-lg bg-card-dark mb-6 border border-[#282d39]">
<div id="sidebar-user-avatar" class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10 ring-2 ring-primary/20" data-alt="User profile picture" style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuAscTJ1Xcq7edw4JqzzGbgOvjdyQ9_nDg7kkxtlCQw51-EJsv1RJyDd9OAZC89eniVl2ujzIik6wgxd5FTvho_ak6ccsWrWelinVwXj6yQUdpPUXYUTJN0pSvhRh-smWf81cMQz40x4U3setrSFDsyX4KkfxOsHc6PnTND68lGw6JkA9B0ag_4fNu5s0Z9OMbq83llAZUv3xuo3s6VI1no110ozE88mRALnX-rhgavHoJxmYpvBcUxV7BtrJr_9Q0BlgvZQL2BXCFg");'>
</div>
<div class="flex flex-col overflow-hidden">
<h1 id="sidebar-user-name" class="text-white text-sm font-semibold truncate">Juan Pérez</h1>
<p id="sidebar-user-type" class="text-text-secondary text-xs truncate">Usuario Premium</p>
</div>
</div>
<!-- Navigation -->
<nav class="flex flex-col gap-1.5">
<a id="nav-dashboard" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-text-secondary hover:text-white hover:bg-card-dark transition-colors group" href="DashboardCliente.php">
<span class="material-symbols-outlined text-[24px]">dashboard</span>
<p class="text-sm font-medium">Dashboard</p>
</a>
<a id="nav-sorteos" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary text-white group transition-colors" href="ListadoSorteosActivos.php">
<span class="material-symbols-outlined text-[24px]">local_activity</span>
<p class="text-sm font-medium">Sorteos</p>
</a>
<a id="nav-boletos" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-text-secondary hover:text-white hover:bg-card-dark transition-colors group" href="MisBoletosCliente.php">
<span class="material-symbols-outlined text-[24px]">confirmation_number</span>
<p class="text-sm font-medium">Mis Boletos</p>
</a>
<a id="nav-ganadores" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-text-secondary hover:text-white hover:bg-card-dark transition-colors group" href="MisGanancias.php">
<span class="material-symbols-outlined text-[24px]">emoji_events</span>
<p class="text-sm font-medium">Ganadores</p>
</a>
<a id="nav-perfil" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-text-secondary hover:text-white hover:bg-card-dark transition-colors group" href="AjustesPefilCliente.php">
<span class="material-symbols-outlined text-[24px]">person</span>
<p class="text-sm font-medium">Perfil</p>
</a>
<a id="nav-soporte" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-text-secondary hover:text-white hover:bg-card-dark transition-colors group" href="ContactoSoporteCliente.php">
<span class="material-symbols-outlined text-[24px]">support_agent</span>
<p class="text-sm font-medium">Soporte</p>
</a>
</nav>
</div>
<div class="mt-auto p-6">
<button id="logout-btn" class="flex w-full items-center justify-center gap-2 rounded-lg h-10 px-4 bg-card-dark hover:bg-[#3b4254] text-text-secondary hover:text-white text-sm font-bold transition-colors border border-transparent hover:border-[#4b5563]">
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
<div class="flex-1 overflow-y-auto overflow-x-hidden">
<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
<!-- Hero Section - Información del Sorteo -->
<div class="mb-8">
<div class="bg-gradient-to-br from-card-dark via-[#1f2530] to-card-dark rounded-2xl border border-[#282d39] p-6 md:p-8 shadow-xl">
<div class="flex flex-col lg:flex-row gap-6 lg:gap-8 items-start lg:items-center">
<!-- Imagen del Sorteo -->
<div id="sorteo-mini-image" class="w-full lg:w-48 h-48 lg:h-40 rounded-xl bg-[#111318] overflow-hidden flex-shrink-0 relative border-2 border-primary/20">
<div class="absolute inset-0 bg-cover bg-center" data-alt="Sorteo image"></div>
<div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
</div>
<!-- Información Principal -->
<div class="flex-1 space-y-4">
<div class="flex flex-wrap items-center gap-3">
<div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary/10 border border-primary/30 text-primary">
<span class="material-symbols-outlined text-[16px]">verified</span>
<span class="text-xs font-bold uppercase tracking-wide">Sorteo Verificado</span>
</div>
<div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400">
<span class="material-symbols-outlined text-[16px]">local_activity</span>
<span class="text-xs font-semibold">Activo</span>
</div>
</div>
<h1 id="sorteo-title" class="text-3xl md:text-4xl lg:text-5xl font-black leading-tight text-white">
<span class="text-primary" id="sorteo-title-text">Gran Sorteo Anual</span>
</h1>
<p id="sorteo-description" class="text-text-secondary text-base md:text-lg max-w-3xl leading-relaxed">
Selecciona la cantidad de boletos que deseas comprar. Los números se asignarán automáticamente de forma aleatoria.
</p>
<div class="flex flex-wrap items-center gap-6 pt-2">
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-primary text-xl">confirmation_number</span>
<div>
<p class="text-xs text-text-secondary uppercase tracking-wide">Precio por boleto</p>
<p id="precio-boleto-display" class="text-2xl font-bold text-white">$50.00 MXN</p>
</div>
</div>
</div>
</div>
<!-- Countdown Timer -->
<div class="lg:ml-auto">
<div class="bg-[#111318] border border-[#282d39] rounded-xl p-5 shadow-lg">
<p class="text-xs text-center text-text-secondary mb-3 font-semibold uppercase tracking-widest">Cierra en</p>
<div class="flex gap-2 justify-center">
<div class="flex flex-col items-center gap-1.5">
<div class="w-14 h-14 flex items-center justify-center rounded-lg bg-card-dark border border-[#282d39]">
<span id="timer-dias" class="text-xl font-bold text-primary">03</span>
</div>
<span class="text-[10px] text-text-secondary font-medium">Días</span>
</div>
<span class="text-xl font-bold text-gray-400 pt-3">:</span>
<div class="flex flex-col items-center gap-1.5">
<div class="w-14 h-14 flex items-center justify-center rounded-lg bg-card-dark border border-[#282d39]">
<span id="timer-horas" class="text-xl font-bold text-primary">12</span>
</div>
<span class="text-[10px] text-text-secondary font-medium">Horas</span>
</div>
<span class="text-xl font-bold text-gray-400 pt-3">:</span>
<div class="flex flex-col items-center gap-1.5">
<div class="w-14 h-14 flex items-center justify-center rounded-lg bg-card-dark border border-[#282d39]">
<span id="timer-minutos" class="text-xl font-bold text-primary">45</span>
</div>
<span class="text-[10px] text-text-secondary font-medium">Mins</span>
</div>
</div>
</div>
</div>
</div>
</div>
</div>

<!-- Estadísticas del Sorteo -->
<div id="sorteo-stats" class="mb-8">
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
<div class="bg-card-dark rounded-xl p-5 border border-[#282d39] shadow-lg hover:shadow-xl transition-shadow">
<div class="flex items-center justify-between mb-2">
<p class="text-sm text-text-secondary font-medium">Total de Boletos</p>
<span class="material-symbols-outlined text-text-secondary text-lg">inventory</span>
</div>
<p id="stat-total-boletos" class="text-3xl font-bold text-white">-</p>
</div>
<div class="bg-card-dark rounded-xl p-5 border border-emerald-500/20 shadow-lg hover:shadow-xl transition-shadow">
<div class="flex items-center justify-between mb-2">
<p class="text-sm text-text-secondary font-medium">Disponibles</p>
<span class="material-symbols-outlined text-emerald-400 text-lg">check_circle</span>
</div>
<p id="stat-disponibles" class="text-3xl font-bold text-emerald-400">-</p>
</div>
<div class="bg-card-dark rounded-xl p-5 border border-red-500/20 shadow-lg hover:shadow-xl transition-shadow">
<div class="flex items-center justify-between mb-2">
<p class="text-sm text-text-secondary font-medium">Vendidos</p>
<span class="material-symbols-outlined text-red-400 text-lg">sell</span>
</div>
<p id="stat-vendidos" class="text-3xl font-bold text-red-400">-</p>
</div>
</div>
</div>

<!-- Selector de Cantidad de Boletos -->
<div class="sticky top-20 z-30 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-6 bg-[#111318] border-y border-[#282d39] shadow-lg mb-8">
<div class="max-w-4xl mx-auto">
<div class="bg-gradient-to-br from-card-dark to-[#1f2530] rounded-2xl p-6 md:p-8 border border-[#282d39] shadow-xl">
<div class="text-center mb-6">
<h2 class="text-2xl md:text-3xl font-bold text-white mb-2">Selecciona tu cantidad</h2>
<p class="text-text-secondary">Los boletos se asignarán automáticamente de forma aleatoria</p>
</div>
<div class="flex flex-col md:flex-row items-center justify-center gap-6 md:gap-8">
<!-- Selector de Cantidad -->
<div class="flex flex-col items-center gap-4 w-full md:w-auto">
<label for="cantidad-boletos" class="text-sm font-semibold text-text-secondary uppercase tracking-wide">Cantidad</label>
<div class="flex items-center gap-4">
<button id="btn-decrement" class="w-12 h-12 flex items-center justify-center rounded-xl bg-[#111318] border-2 border-[#282d39] text-white hover:bg-primary hover:border-primary hover:shadow-lg hover:shadow-primary/20 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-[#111318] disabled:hover:border-[#282d39]" type="button">
<span class="material-symbols-outlined text-[24px]">remove</span>
</button>
<div class="relative">
<input id="cantidad-boletos" type="number" min="1" max="10" value="1" class="w-24 h-12 text-center text-2xl font-black text-white bg-[#111318] border-2 border-[#282d39] rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all" />
<div class="absolute -bottom-6 left-1/2 transform -translate-x-1/2 text-[10px] text-text-secondary whitespace-nowrap">Máximo: 10</div>
</div>
<button id="btn-increment" class="w-12 h-12 flex items-center justify-center rounded-xl bg-[#111318] border-2 border-[#282d39] text-white hover:bg-primary hover:border-primary hover:shadow-lg hover:shadow-primary/20 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-[#111318] disabled:hover:border-[#282d39]" type="button">
<span class="material-symbols-outlined text-[24px]">add</span>
</button>
</div>
</div>
<!-- Separador Visual -->
<div class="hidden md:block w-px h-16 bg-[#282d39]"></div>
<!-- Botón Asignar -->
<div class="flex flex-col items-center gap-3 w-full md:w-auto">
<button id="btn-asignar-boletos" class="w-full md:w-auto bg-gradient-to-r from-primary to-blue-600 hover:from-blue-600 hover:to-primary text-white font-bold py-4 px-10 rounded-xl shadow-lg shadow-blue-500/30 transition-all transform active:scale-95 flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:from-primary disabled:hover:to-blue-600" type="button">
<span class="material-symbols-outlined text-[24px]">shuffle</span>
<span class="text-lg">Asignar Boletos</span>
</button>
<p class="text-xs text-text-secondary text-center max-w-xs">Asignación automática y aleatoria para mayor transparencia</p>
</div>
</div>
</div>
</div>
</div>
</main>
<!-- Sticky Cart Footer (solo se muestra si hay boletos asignados) -->
<div id="sticky-footer" class="fixed bottom-4 left-4 right-4 z-50" style="display: none;">
<div class="max-w-[1280px] mx-auto bg-card-dark rounded-xl shadow-[0_4px_25px_rgba(0,0,0,0.5)] border border-[#282d39] p-4 md:p-5 flex flex-col md:flex-row items-center justify-between gap-4 animate-slide-up">
<!-- Reservation Timer & Summary -->
<div class="flex flex-col md:flex-row items-center gap-6 w-full md:w-auto text-center md:text-left">
<div class="flex items-center gap-3 text-red-500 bg-red-500/10 px-3 py-1.5 rounded-lg border border-red-500/20">
<span class="material-symbols-outlined text-[20px] animate-pulse">timer</span>
<span id="reservation-timer" class="font-mono font-bold">14:59</span>
</div>
<div class="flex flex-col">
<span class="text-xs text-text-secondary uppercase font-semibold tracking-wider">Tus Boletos Asignados</span>
<div id="selected-tickets-container" class="flex flex-wrap gap-2 mt-1 justify-center md:justify-start">
<!-- Los boletos asignados se mostrarán aquí -->
</div>
</div>
</div>
<!-- Actions -->
<div class="flex items-center gap-6 w-full md:w-auto justify-between md:justify-end border-t md:border-t-0 border-[#282d39] pt-4 md:pt-0">
<div class="flex flex-col items-end">
<span class="text-xs text-text-secondary">Total a Pagar</span>
<span id="total-pagar" class="text-2xl font-bold text-white">$0.00</span>
</div>
<a href="FinalizarPagoBoletos.php" onclick="return handleProceedToPayment()" class="bg-primary hover:bg-blue-600 text-white font-bold py-3 px-8 rounded-lg shadow-lg shadow-blue-500/30 transition-all transform active:scale-95 flex items-center gap-2">
<span>Proceder al Pago</span>
<span class="material-symbols-outlined">arrow_forward</span>
</a>
</div>
</div>
</div>
</div>
</main>

<!-- Client Layout Script -->
<script src="js/custom-alerts.js"></script>
<script src="js/client-layout.js"></script>
<script>
// Inicializar layout del cliente
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar el layout con 'sorteos' como página activa
    // Esperar un momento para asegurar que el script se haya cargado completamente
    setTimeout(function() {
        if (window.ClientLayout && typeof ClientLayout.init === 'function') {
            try {
                ClientLayout.init('sorteos');
            } catch (error) {
                console.error('Error al inicializar ClientLayout:', error);
            }
        }
    }, 100);
    
    // Cargar información del sorteo seleccionado
    loadSorteoData().then(() => {
        // Esperar un poco para asegurar que el DOM esté completamente renderizado
        setTimeout(() => {
            // Inicializar funcionalidad de asignación automática
            initTicketAssignment();
            // Verificar si hay boletos ya asignados
            checkMyAssignedTickets();
        }, 300);
    }).catch(error => {
        console.error('Error al inicializar:', error);
        // Intentar inicializar de todas formas
        setTimeout(() => {
            initTicketAssignment();
        }, 500);
    });
});

// Variable global para almacenar el ID del sorteo
let currentSorteoId = null;
let currentSorteoData = null;

// Función para cargar los datos del sorteo desde la API
async function loadSorteoData() {
    try {
        // Obtener ID del sorteo desde localStorage o URL
        const sorteoDataFromStorage = JSON.parse(localStorage.getItem('selectedSorteo') || '{}');
        const urlParams = new URLSearchParams(window.location.search);
        const sorteoIdFromUrl = urlParams.get('id');
        const sorteoId = sorteoIdFromUrl || sorteoDataFromStorage.id || null;
        
        if (!sorteoId) {
            console.error('No se encontró ID del sorteo');
            showError('No se encontró información del sorteo. Por favor, selecciona un sorteo primero.');
            return;
        }
        
        currentSorteoId = parseInt(sorteoId);
        
        // Cargar detalles del sorteo desde la API
        const response = await fetch(`api_sorteos.php?action=get_details&id=${currentSorteoId}`);
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status} ${response.statusText}`);
        }
        
        const text = await response.text();
        const data = JSON.parse(text);
        
        if (!data.success || !data.data) {
            throw new Error(data.error || 'Error al cargar el sorteo');
        }
        
        currentSorteoData = data.data;
        
        // Actualizar título
        const titleElement = document.getElementById('sorteo-title-text');
        if (titleElement) {
            titleElement.textContent = currentSorteoData.titulo || 'Gran Sorteo Anual';
        }
        
        // Actualizar descripción
        const descElement = document.getElementById('sorteo-description');
        if (descElement) {
            const descripcion = currentSorteoData.descripcion || '';
            descElement.textContent = descripcion || 'Selecciona la cantidad de boletos que deseas comprar. Los números se asignarán automáticamente de forma aleatoria.';
        }
        
        // Actualizar imagen mini
        const miniImage = document.querySelector('#sorteo-mini-image div');
        if (miniImage && currentSorteoData.imagen_url) {
            miniImage.style.backgroundImage = `url('${currentSorteoData.imagen_url}')`;
        }
        
        // Actualizar precio
        const precioElement = document.getElementById('precio-boleto-display');
        if (precioElement) {
            const precio = parseFloat(currentSorteoData.precio_boleto) || 0;
            precioElement.textContent = `$${precio.toFixed(2)} MXN`;
        }
        
        // Inicializar contador regresivo del sorteo
        if (currentSorteoData.tiempo_restante) {
            initSorteoCountdown(currentSorteoData.tiempo_restante);
        } else {
            initSorteoCountdown({ dias: 3, horas: 12, minutos: 45, segundos: 0 });
        }
        
        // Guardar el precio del boleto para calcular el total
        window.currentTicketPrice = parseFloat(currentSorteoData.precio_boleto) || 50.00;
        
        // Cargar estadísticas de boletos (sin números específicos)
        await loadTicketStats();
        
    } catch (error) {
        console.error('Error al cargar datos del sorteo:', error);
        showError('Error al cargar la información del sorteo: ' + error.message);
    }
}

// Función para cargar estadísticas de boletos (sin números específicos)
async function loadTicketStats() {
    if (!currentSorteoId) {
        console.error('No hay ID de sorteo disponible');
        return;
    }
    
    try {
        const response = await fetch(`api_boletos.php?action=get_available&id_sorteo=${currentSorteoId}`);
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status} ${response.statusText}`);
        }
        
        const text = await response.text();
        const data = JSON.parse(text);
        
        if (!data.success || !data.data) {
            throw new Error(data.error || 'Error al cargar las estadísticas');
        }
        
        // Actualizar estadísticas en la UI
        const statTotal = document.getElementById('stat-total-boletos');
        const statDisponibles = document.getElementById('stat-disponibles');
        const statVendidos = document.getElementById('stat-vendidos');
        
        if (statTotal) statTotal.textContent = data.data.total_boletos || 0;
        if (statDisponibles) statDisponibles.textContent = data.data.total_disponibles || 0;
        if (statVendidos) statVendidos.textContent = data.data.total_vendidos || 0;
        
    } catch (error) {
        console.error('Error al cargar estadísticas:', error);
    }
}

// ============================================
// NUEVAS FUNCIONES PARA ASIGNACIÓN AUTOMÁTICA
// ============================================

// Variable global para almacenar boletos asignados
let assignedTickets = [];

// Inicializar funcionalidad de asignación automática
function initTicketAssignment() {
    console.log('Inicializando asignación de boletos...');
    
    const btnAsignar = document.getElementById('btn-asignar-boletos');
    const btnIncrement = document.getElementById('btn-increment');
    const btnDecrement = document.getElementById('btn-decrement');
    const cantidadInput = document.getElementById('cantidad-boletos');
    
    console.log('Elementos encontrados:', {
        btnAsignar: !!btnAsignar,
        btnIncrement: !!btnIncrement,
        btnDecrement: !!btnDecrement,
        cantidadInput: !!cantidadInput
    });
    
    if (!btnAsignar || !btnIncrement || !btnDecrement || !cantidadInput) {
        console.error('Error: No se encontraron todos los elementos necesarios');
        setTimeout(initTicketAssignment, 500); // Reintentar después de 500ms
        return;
    }
    
    // Botón Asignar
    btnAsignar.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Click en botón asignar');
        handleAssignTickets();
    });
    
    // Botón Incrementar
    btnIncrement.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const current = parseInt(cantidadInput.value) || 1;
        console.log('Incrementar desde:', current);
        if (current < 10) {
            cantidadInput.value = current + 1;
            updateButtonsState();
        }
    });
    
    // Botón Decrementar
    btnDecrement.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const current = parseInt(cantidadInput.value) || 1;
        console.log('Decrementar desde:', current);
        if (current > 1) {
            cantidadInput.value = current - 1;
            updateButtonsState();
        }
    });
    
    // Input de cantidad
    cantidadInput.addEventListener('input', function() {
        let value = parseInt(this.value) || 1;
        if (value < 1) value = 1;
        if (value > 10) value = 10;
        this.value = value;
        updateButtonsState();
    });
    
    cantidadInput.addEventListener('change', function() {
        let value = parseInt(this.value) || 1;
        if (value < 1) value = 1;
        if (value > 10) value = 10;
        this.value = value;
        updateButtonsState();
    });
    
    updateButtonsState();
    console.log('Inicialización de asignación completada');
}

// Actualizar estado de botones según disponibilidad
function updateButtonsState() {
    const cantidadInput = document.getElementById('cantidad-boletos');
    const btnAsignar = document.getElementById('btn-asignar-boletos');
    const btnIncrement = document.getElementById('btn-increment');
    const btnDecrement = document.getElementById('btn-decrement');
    
    if (!cantidadInput) return;
    
    const cantidad = parseInt(cantidadInput.value) || 1;
    
    if (btnIncrement) {
        btnIncrement.disabled = cantidad >= 10;
    }
    
    if (btnDecrement) {
        btnDecrement.disabled = cantidad <= 1;
    }
    
    if (btnAsignar) {
        btnAsignar.disabled = cantidad < 1 || cantidad > 10;
    }
}

// Asignar boletos aleatoriamente
async function handleAssignTickets() {
    console.log('handleAssignTickets llamado');
    
    const cantidadInput = document.getElementById('cantidad-boletos');
    const btnAsignar = document.getElementById('btn-asignar-boletos');
    
    console.log('Estado actual:', {
        cantidadInput: !!cantidadInput,
        currentSorteoId: currentSorteoId,
        cantidad: cantidadInput ? cantidadInput.value : 'N/A'
    });
    
    if (!cantidadInput) {
        console.error('Error: No se encontró el input de cantidad');
        if (typeof customAlert === 'function') {
            customAlert('Error: No se encontró el campo de cantidad. Por favor, recarga la página.', 'Error', 'error');
        } else {
            alert('Error: No se encontró el campo de cantidad. Por favor, recarga la página.');
        }
        return;
    }
    
    if (!currentSorteoId) {
        console.error('Error: No hay ID de sorteo disponible');
        if (typeof customAlert === 'function') {
            customAlert('Error: No se encontró información del sorteo. Por favor, selecciona un sorteo primero.', 'Error', 'error');
        } else {
            alert('Error: No se encontró información del sorteo. Por favor, selecciona un sorteo primero.');
        }
        return;
    }
    
    const cantidad = parseInt(cantidadInput.value) || 1;
    console.log('Cantidad a asignar:', cantidad);
    
    if (cantidad < 1 || cantidad > 10) {
        if (typeof customAlert === 'function') {
            customAlert('La cantidad debe estar entre 1 y 10 boletos.', 'Cantidad Inválida', 'warning');
        } else {
            alert('La cantidad debe estar entre 1 y 10 boletos.');
        }
        return;
    }
    
    // Deshabilitar botón mientras se procesa
    if (btnAsignar) {
        btnAsignar.disabled = true;
        const originalHTML = btnAsignar.innerHTML;
        btnAsignar.innerHTML = '<span class="material-symbols-outlined" style="animation: spin 1s linear infinite;">sync</span><span>Asignando...</span>';
        
        try {
            console.log('Enviando petición a API...');
            console.log('Datos a enviar:', {
                id_sorteo: currentSorteoId,
                cantidad: cantidad
            });
            
            const requestBody = {
                id_sorteo: currentSorteoId,
                cantidad: cantidad
            };
            
            console.log('Body JSON:', JSON.stringify(requestBody));
            
            const response = await fetch('api_boletos.php?action=assign_random', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(requestBody)
            });
            
            console.log('Respuesta recibida, status:', response.status);
            
            const text = await response.text();
            console.log('Respuesta texto completa:', text);
            
            let data;
            try {
                data = JSON.parse(text);
            } catch (parseError) {
                console.error('Error al parsear JSON:', parseError);
                console.error('Texto completo:', text);
                throw new Error('Error al procesar la respuesta del servidor: ' + text.substring(0, 100));
            }
            
            console.log('Datos parseados:', data);
            
            if (!response.ok || !data.success) {
                const errorMessage = data.error || data.message || `Error HTTP: ${response.status} ${response.statusText}`;
                console.error('Error del servidor:', errorMessage);
                throw new Error(errorMessage);
            }
            
            // Guardar boletos asignados
            assignedTickets = data.data.boletos_asignados || [];
            console.log('Boletos asignados:', assignedTickets);
            
            // Mostrar boletos asignados
            displayAssignedTickets(data.data);
            
            // Actualizar estadísticas
            await loadTicketStats();
            
            // Iniciar timer de reserva
            if (data.data.tiempo_expiracion) {
                startReservationTimerWithSeconds(data.data.tiempo_expiracion);
            }
            
            if (typeof customAlert === 'function') {
                customAlert(`¡${cantidad} boleto(s) asignado(s) exitosamente!`, 'Boletos Asignados', 'success');
            } else {
                alert(`¡${cantidad} boleto(s) asignado(s) exitosamente!`);
            }
            
        } catch (error) {
            console.error('Error completo al asignar boletos:', error);
            console.error('Stack:', error.stack);
            
            if (typeof customAlert === 'function') {
                customAlert('Error al asignar boletos: ' + error.message, 'Error', 'error');
            } else {
                alert('Error al asignar boletos: ' + error.message);
            }
        } finally {
            // Restaurar botón
            btnAsignar.disabled = false;
            btnAsignar.innerHTML = originalHTML;
        }
    } else {
        console.error('Error: No se encontró el botón de asignar');
    }
}

// Mostrar boletos asignados en la UI (solo en el footer sticky)
function displayAssignedTickets(data) {
    const stickyFooter = document.getElementById('sticky-footer');
    const selectedContainer = document.querySelector('#selected-tickets-container');
    const totalPagar = document.getElementById('total-pagar');
    
    const boletos = data.boletos_asignados || [];
    const numeros = data.numeros_boletos || [];
    
    if (boletos.length === 0) {
        if (stickyFooter) stickyFooter.style.display = 'none';
        return;
    }
    
    // Mostrar footer sticky
    if (stickyFooter) {
        stickyFooter.style.display = 'flex';
    }
    
    // Actualizar precio total en el footer
    if (totalPagar) {
        totalPagar.textContent = `$${data.precio_total.toFixed(2)}`;
    }
    
    // Actualizar contenedor de boletos en el footer
    if (selectedContainer) {
        selectedContainer.innerHTML = numeros.map(num => 
            `<span class="inline-flex items-center px-2 py-0.5 rounded text-sm font-medium bg-primary/10 text-primary border border-primary/20">#${num}</span>`
        ).join('');
    }
    
    // Guardar en localStorage para FinalizarPagoBoletos.php
    localStorage.setItem('selectedTickets', JSON.stringify(boletos));
    localStorage.setItem('assignedTicketsData', JSON.stringify(data));
}

// Verificar boletos ya asignados del usuario
async function checkMyAssignedTickets() {
    if (!currentSorteoId) return;
    
    try {
        const response = await fetch(`api_boletos.php?action=get_my_assigned&id_sorteo=${currentSorteoId}`);
        const text = await response.text();
        const data = JSON.parse(text);
        
        if (data.success && data.data && data.data.boletos && data.data.boletos.length > 0) {
            // Hay boletos ya asignados, mostrarlos
            const boletos = data.data.boletos;
            const numeros = boletos.map(b => b.numero_boleto);
            const numerosInt = boletos.map(b => b.numero_boleto_int);
            
            // Calcular precio total
            const precioBoleto = window.currentTicketPrice || 0;
            const precioTotal = precioBoleto * boletos.length;
            
            // Mostrar boletos asignados
            displayAssignedTickets({
                boletos_asignados: numerosInt,
                numeros_boletos: numeros,
                precio_total: precioTotal
            });
            
            // Iniciar timer si hay reservas activas
            const reservaActiva = boletos.find(b => b.estado === 'Reservado' && b.tiempo_restante);
            if (reservaActiva && reservaActiva.tiempo_restante > 0) {
                startReservationTimerWithSeconds(reservaActiva.tiempo_restante);
            }
        }
    } catch (error) {
        console.error('Error al verificar boletos asignados:', error);
    }
}

// ============================================
// FUNCIONES OBSOLETAS (MANTENIDAS POR COMPATIBILIDAD)
// ============================================

// Función para renderizar los boletos dinámicamente (OBSOLETA - ya no se usa)
function renderTickets(boletosData) {
    const gridEl = document.getElementById('tickets-grid');
    const loadingEl = document.getElementById('tickets-loading');
    
    if (!gridEl) {
        console.error('Contenedor de boletos no encontrado');
        return;
    }
    
    // Ocultar mensaje de carga
    if (loadingEl) {
        loadingEl.style.display = 'none';
    }
    
    // Limpiar el grid
    gridEl.innerHTML = '';
    
    if (!boletosData) {
        console.error('No hay datos de boletos para renderizar');
        gridEl.innerHTML = '<div class="col-span-full text-center py-12"><p class="text-text-secondary text-lg">No se recibieron datos de boletos.</p></div>';
        return;
    }
    
    const { total_boletos, disponibles, reservados, vendidos } = boletosData;
    
    if (!total_boletos || total_boletos <= 0) {
        console.error('Total de boletos inválido:', total_boletos);
        gridEl.innerHTML = '<div class="col-span-full text-center py-12"><p class="text-text-secondary text-lg">No hay boletos disponibles para este sorteo.</p></div>';
        return;
    }
    
    // Crear un mapa de todos los boletos
    const allTickets = new Map();
    
    // Agregar boletos disponibles
    disponibles.forEach(boleto => {
        allTickets.set(parseInt(boleto.numero_boleto), {
            ...boleto,
            estado: 'Disponible'
        });
    });
    
    // Agregar boletos reservados
    reservados.forEach(boleto => {
        allTickets.set(parseInt(boleto.numero_boleto), {
            ...boleto,
            estado: 'Reservado'
        });
    });
    
    // Agregar boletos vendidos
    vendidos.forEach(boleto => {
        allTickets.set(parseInt(boleto.numero_boleto), {
            ...boleto,
            estado: 'Vendido'
        });
    });
    
    // Generar todos los boletos del 1 al total_boletos
    for (let i = 1; i <= total_boletos; i++) {
        const numeroBoleto = String(i).padStart(4, '0');
        const boleto = allTickets.get(i);
        
        const button = document.createElement('button');
        button.className = 'h-12 rounded-lg transition-all duration-200 flex items-center justify-center relative';
        button.dataset.numeroBoleto = numeroBoleto;
        button.dataset.numeroBoletoInt = i;
        
        const span = document.createElement('span');
        span.className = 'font-mono font-bold';
        span.textContent = numeroBoleto;
        button.appendChild(span);
        
        // Aplicar estilos según el estado
        if (!boleto || boleto.estado === 'Disponible') {
            button.className += ' group border border-emerald-500/30 bg-card-dark hover:bg-emerald-500/10 hover:border-emerald-500';
            span.className += ' text-emerald-400 group-hover:scale-110 transition-transform';
            button.disabled = false;
            button.dataset.estado = 'disponible';
            button.addEventListener('click', () => toggleTicketSelection(i, button));
        } else if (boleto.estado === 'Reservado') {
            // Verificar si está reservado por el usuario actual (esto se verificará después)
            button.className += ' border border-yellow-700/30 bg-yellow-900/10 opacity-80';
            span.className += ' text-yellow-600 relative z-10';
            button.dataset.estado = 'reservado';
            const lockIcon = document.createElement('span');
            lockIcon.className = 'material-symbols-outlined text-yellow-600 text-lg absolute opacity-20';
            lockIcon.textContent = 'lock';
            button.appendChild(lockIcon);
            button.disabled = true;
        } else if (boleto.estado === 'Vendido') {
            button.className += ' border border-red-900/30 bg-red-900/10 opacity-60';
            span.className += ' text-red-700 decoration-red-500/50 line-through';
            button.dataset.estado = 'vendido';
            button.disabled = true;
        }
        
        gridEl.appendChild(button);
    }
    
    console.log(`✅ Renderizados ${total_boletos} boletos: ${disponibles.length} disponibles, ${reservados.length} reservados, ${vendidos.length} vendidos`);
    
    // Reinicializar la funcionalidad de selección
    initTicketSelection();
    
    // Restaurar boletos previamente seleccionados
    const savedTickets = JSON.parse(localStorage.getItem('selectedTickets') || '[]');
    if (savedTickets.length > 0) {
        savedTickets.forEach(num => {
            const button = document.querySelector(`button[data-numero-boleto-int="${num}"]`);
            if (button && !button.disabled) {
                toggleTicketSelection(num, button);
            }
        });
    }
}

// Función para verificar reservas activas del usuario
async function checkUserReservations() {
    if (!currentSorteoId) return;
    
    try {
        const response = await fetch(`api_boletos.php?action=check_reservation&id_sorteo=${currentSorteoId}`);
        const text = await response.text();
        const data = JSON.parse(text);
        
        if (data.success && data.data && data.data.reservas_activas && data.data.reservas_activas.length > 0) {
            // Marcar boletos reservados por el usuario como seleccionados
            data.data.reservas_activas.forEach(reserva => {
                const numeroBoleto = parseInt(reserva.numero_boleto);
                selectedTickets.add(numeroBoleto);
                
                const button = document.querySelector(`button[data-numero-boleto-int="${numeroBoleto}"]`);
                if (button && !button.disabled) {
                    markTicketAsSelected(button);
                }
            });
            
            updateSelectedTicketsDisplay();
            updateTotalPrice();
            
            // Actualizar el timer de reserva basado en la reserva más antigua
            const oldestReservation = data.data.reservas_activas.reduce((oldest, current) => {
                const oldestTime = new Date(oldest.fecha_reserva).getTime();
                const currentTime = new Date(current.fecha_reserva).getTime();
                return currentTime < oldestTime ? current : oldest;
            });
            
            const reservationTime = new Date(oldestReservation.fecha_reserva).getTime();
            const now = new Date().getTime();
            const elapsed = Math.floor((now - reservationTime) / 1000);
            const remaining = 900 - elapsed; // 15 minutos = 900 segundos
            
            if (remaining > 0) {
                // Reiniciar el timer con el tiempo restante
                startReservationTimerWithSeconds(remaining);
            }
        }
    } catch (error) {
        console.error('Error al verificar reservas:', error);
    }
}

// Función para alternar selección de boleto
function toggleTicketSelection(numeroBoleto, button) {
    if (!button || button.disabled) return;
    
    if (selectedTickets.has(numeroBoleto)) {
        selectedTickets.delete(numeroBoleto);
        markTicketAsAvailable(button);
    } else {
        if (selectedTickets.size >= maxTickets) {
            customAlert(`Solo puedes seleccionar hasta ${maxTickets} boletos a la vez.`, 'Límite alcanzado', 'warning');
            return;
        }
        selectedTickets.add(numeroBoleto);
        markTicketAsSelected(button);
        
        // Reservar el boleto automáticamente en el servidor
        reserveTicketOnServer(numeroBoleto);
    }
    
    updateSelectedTicketsDisplay();
    updateTotalPrice();
    
    // Guardar en localStorage
    const ticketsArray = Array.from(selectedTickets).sort((a, b) => a - b);
    localStorage.setItem('selectedTickets', JSON.stringify(ticketsArray));
}

// Función para reservar un boleto en el servidor
async function reserveTicketOnServer(numeroBoleto) {
    if (!currentSorteoId) return;
    
    try {
        const response = await fetch('api_boletos.php?action=reserve', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id_sorteo: currentSorteoId,
                numeros_boletos: [numeroBoleto]
            })
        });
        
        const text = await response.text();
        const data = JSON.parse(text);
        
        if (!data.success) {
            console.error('Error al reservar boleto:', data.error);
            // Remover del Set local si falló la reserva
            selectedTickets.delete(numeroBoleto);
            updateSelectedTicketsDisplay();
            updateTotalPrice();
            customAlert(`Error al reservar el boleto ${String(numeroBoleto).padStart(4, '0')}: ${data.error}`, 'Error de Reserva', 'error');
        }
    } catch (error) {
        console.error('Error al reservar boleto:', error);
    }
}

// Función para iniciar timer de reserva con segundos específicos
function startReservationTimerWithSeconds(seconds) {
    const timerElement = document.getElementById('reservation-timer');
    if (!timerElement) return;
    
    if (activeCountdownIntervals.has('reservation-timer')) {
        clearInterval(activeCountdownIntervals.get('reservation-timer'));
        activeCountdownIntervals.delete('reservation-timer');
    }
    
    let remainingSeconds = Math.max(0, seconds);
    
    function updateReservationTimer() {
        if (remainingSeconds <= 0) {
            timerElement.textContent = '00:00';
            if (activeCountdownIntervals.has('reservation-timer')) {
                clearInterval(activeCountdownIntervals.get('reservation-timer'));
                activeCountdownIntervals.delete('reservation-timer');
            }
            customAlert('El tiempo de reserva ha expirado. Los boletos han sido liberados.', 'Tiempo Expirado', 'warning');
            window.location.href = 'ListadoSorteosActivos.php';
            return;
        }
        
        const minutes = Math.floor(remainingSeconds / 60);
        const secs = remainingSeconds % 60;
        timerElement.textContent = `${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        
        remainingSeconds--;
    }
    
    updateReservationTimer();
    const intervalId = setInterval(updateReservationTimer, 1000);
    activeCountdownIntervals.set('reservation-timer', intervalId);
}

// Función para mostrar errores
function showError(message) {
    if (typeof customAlert === 'function') {
        customAlert(message, 'Error', 'error');
    } else {
        console.error(message);
        alert(message);
    }
}

// Función para obtener datos por defecto
function getDefaultSorteoData() {
    return {
        id: 'default',
        titulo: 'Gran Sorteo Anual',
        subtitulo: 'Automóvil 0km - Modelo 2024',
        precio: 50.00,
        tiempoRestante: { dias: 3, horas: 12, minutos: 45 }
    };
}

// Almacenar intervalos activos para poder limpiarlos si es necesario
const activeCountdownIntervals = new Map();

// Función para inicializar el contador regresivo del sorteo (EXACTO COMO DashboardCliente)
function initSorteoCountdown(tiempo) {
    // Obtener elementos del DOM
    const diasElement = document.getElementById('timer-dias');
    const horasElement = document.getElementById('timer-horas');
    const minutosElement = document.getElementById('timer-minutos');
    
    if (!diasElement || !horasElement || !minutosElement) {
        console.warn('Elementos del timer no encontrados, reintentando...');
        setTimeout(function() {
            initSorteoCountdown(tiempo);
        }, 200);
        return;
    }
    
    // Si ya existe un intervalo para este elemento, limpiarlo primero
    if (activeCountdownIntervals.has('sorteo-timer')) {
        clearInterval(activeCountdownIntervals.get('sorteo-timer'));
        activeCountdownIntervals.delete('sorteo-timer');
    }
    
    if (!tiempo) {
        tiempo = { dias: 3, horas: 12, minutos: 45, segundos: 0 };
    }
    
    // Convertir tiempo a segundos totales (variable local en closure, EXACTO COMO DashboardCliente)
    let remainingSeconds = (tiempo.dias || 0) * 86400 + 
                          (tiempo.horas || 0) * 3600 + 
                          (tiempo.minutos || 0) * 60 + 
                          (tiempo.segundos || 0);
    
    remainingSeconds = parseInt(remainingSeconds) || 0;
    
    // Función de actualización (EXACTO COMO DashboardCliente)
    function updateCountdown() {
        if (remainingSeconds <= 0) {
            diasElement.textContent = '00';
            horasElement.textContent = '00';
            minutosElement.textContent = '00';
            // Limpiar el intervalo cuando llegue a cero
            if (activeCountdownIntervals.has('sorteo-timer')) {
                clearInterval(activeCountdownIntervals.get('sorteo-timer'));
                activeCountdownIntervals.delete('sorteo-timer');
            }
            return;
        }
        
        // Calcular días, horas, minutos con el tiempo actual
        const dias = Math.floor(remainingSeconds / 86400);
        const horas = Math.floor((remainingSeconds % 86400) / 3600);
        const minutos = Math.floor((remainingSeconds % 3600) / 60);
        
        // Actualizar la visualización
        diasElement.textContent = String(dias).padStart(2, '0');
        horasElement.textContent = String(horas).padStart(2, '0');
        minutosElement.textContent = String(minutos).padStart(2, '0');
        
        remainingSeconds--;
    }
    
    // Actualizar inmediatamente (EXACTO COMO DashboardCliente)
    updateCountdown();
    
    // Iniciar intervalo que se actualiza cada segundo (EXACTO COMO DashboardCliente)
    const intervalId = setInterval(updateCountdown, 1000);
    activeCountdownIntervals.set('sorteo-timer', intervalId);
}

// Función para actualizar el timer (compatibilidad con código existente)
function updateTimer(tiempo) {
    if (tiempo) {
        initSorteoCountdown(tiempo);
    }
}

// Timer de reserva (14:59 minutos) - EXACTO COMO DashboardCliente
function startReservationTimer() {
    const timerElement = document.getElementById('reservation-timer');
    if (!timerElement) return;
    
    // Si ya existe un intervalo para este elemento, limpiarlo primero
    if (activeCountdownIntervals.has('reservation-timer')) {
        clearInterval(activeCountdownIntervals.get('reservation-timer'));
        activeCountdownIntervals.delete('reservation-timer');
    }
    
    // Tiempo inicial: 14 minutos y 59 segundos (variable local en closure, EXACTO COMO DashboardCliente)
    let remainingSeconds = 14 * 60 + 59;
    remainingSeconds = parseInt(remainingSeconds) || 0;
    
    // Función de actualización (EXACTO COMO DashboardCliente)
    function updateReservationTimer() {
        if (remainingSeconds <= 0) {
            timerElement.textContent = '00:00';
            // Limpiar el intervalo cuando llegue a cero
            if (activeCountdownIntervals.has('reservation-timer')) {
                clearInterval(activeCountdownIntervals.get('reservation-timer'));
                activeCountdownIntervals.delete('reservation-timer');
            }
            // Aquí se podría liberar los boletos o mostrar un mensaje
            customAlert('El tiempo de reserva ha expirado. Los boletos han sido liberados.', 'Tiempo Expirado', 'warning');
            window.location.href = 'ListadoSorteosActivos.php';
            return;
        }
        
        const minutes = Math.floor(remainingSeconds / 60);
        const seconds = remainingSeconds % 60;
        timerElement.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        
        remainingSeconds--;
    }
    
    // Actualizar inmediatamente (EXACTO COMO DashboardCliente)
    updateReservationTimer();
    
    // Iniciar intervalo que se actualiza cada segundo (EXACTO COMO DashboardCliente)
    const intervalId = setInterval(updateReservationTimer, 1000);
    activeCountdownIntervals.set('reservation-timer', intervalId);
}

// Función para actualizar el precio total basado en boletos seleccionados
function updateTotalPrice() {
    const totalElement = document.getElementById('total-pagar');
    if (totalElement && window.currentTicketPrice) {
        const selectedCount = selectedTickets.size;
        const total = window.currentTicketPrice * selectedCount;
        totalElement.textContent = `$${total.toFixed(2)}`;
    }
}

// Variables globales para la selección de boletos
let selectedTickets = new Set(); // Usar Set para evitar duplicados
const maxTickets = 100; // Límite máximo de boletos

// Función para inicializar la funcionalidad de selección de boletos
function initTicketSelection() {
    // Los event listeners ya se agregan en renderTickets() cuando se crean los botones dinámicamente
    // Esta función solo se mantiene para compatibilidad con código existente
    console.log('Inicialización de selección de boletos completada');
}

// Función para marcar un boleto como seleccionado
function markTicketAsSelected(button) {
    button.classList.remove('border-emerald-500/30', 'bg-card-dark', 'bg-[#1a202c]', 'hover:bg-emerald-500/10', 'hover:border-emerald-500');
    button.classList.add('border-primary', 'bg-primary', 'shadow-[0_0_15px_rgba(36,99,235,0.4)]', 'relative', 'overflow-hidden');
    button.dataset.estado = 'seleccionado';
    
    const span = button.querySelector('span');
    if (span) {
        span.classList.remove('text-emerald-400');
        span.classList.add('text-white', 'z-10');
    }
    
    // Agregar checkmark si no existe
    if (!button.querySelector('.checkmark-icon')) {
        const checkmark = document.createElement('span');
        checkmark.className = 'checkmark-icon absolute -top-1 -right-1 material-symbols-outlined text-[10px] text-white bg-black/20 rounded-bl p-0.5';
        checkmark.textContent = 'check';
        button.appendChild(checkmark);
    }
    
    // Agregar overlay si no existe
    if (!button.querySelector('.ticket-overlay')) {
        const overlay = document.createElement('div');
        overlay.className = 'ticket-overlay absolute inset-0 bg-white/10';
        button.appendChild(overlay);
    }
    
    // Aplicar filtro actual si no es "all"
    if (currentFilter !== 'all') {
        applyFilter(currentFilter);
    }
}

// Función para marcar un boleto como disponible
function markTicketAsAvailable(button) {
    button.classList.remove('border-primary', 'bg-primary', 'shadow-[0_0_15px_rgba(36,99,235,0.4)]', 'relative', 'overflow-hidden');
    button.classList.add('border-emerald-500/30', 'bg-card-dark', 'hover:bg-emerald-500/10', 'hover:border-emerald-500', 'group');
    button.dataset.estado = 'disponible';
    
    const span = button.querySelector('span');
    if (span) {
        span.classList.remove('text-white', 'z-10');
        span.classList.add('text-emerald-400', 'group-hover:scale-110', 'transition-transform');
    }
    
    // Remover checkmark y overlay
    const checkmark = button.querySelector('.checkmark-icon');
    const overlay = button.querySelector('.ticket-overlay');
    if (checkmark) checkmark.remove();
    if (overlay) overlay.remove();
    
    // Aplicar filtro actual si no es "all"
    if (currentFilter !== 'all') {
        applyFilter(currentFilter);
    }
}

// Función para actualizar la visualización de boletos seleccionados
function updateSelectedTicketsDisplay() {
    const container = document.getElementById('selected-tickets-container');
    if (!container) return;
    
    if (selectedTickets.size === 0) {
        container.innerHTML = '<span class="text-xs text-text-secondary">No hay boletos seleccionados</span>';
        return;
    }
    
    // Ordenar los boletos y mostrar solo los primeros 5, si hay más mostrar "+X más"
    const sortedTickets = Array.from(selectedTickets).sort((a, b) => a - b);
    const displayTickets = sortedTickets.slice(0, 5);
    const remaining = sortedTickets.length - 5;
    
    container.innerHTML = displayTickets.map(num => 
        `<span class="inline-flex items-center px-2 py-0.5 rounded text-sm font-medium bg-primary/10 text-primary border border-primary/20">#${String(num).padStart(3, '0')}</span>`
    ).join('');
    
    if (remaining > 0) {
        const moreBadge = document.createElement('span');
        moreBadge.className = 'inline-flex items-center px-2 py-0.5 rounded text-sm font-medium bg-primary/10 text-primary border border-primary/20';
        moreBadge.textContent = `+${remaining} más`;
        container.appendChild(moreBadge);
    }
}

// Variable global para el filtro activo
let currentFilter = 'all';

// Función para inicializar la búsqueda de boletos
function initTicketSearch() {
    const searchInput = document.getElementById('ticket-search-input');
    if (!searchInput) return;
    
    let searchTimeout;
    
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();
        
        searchTimeout = setTimeout(() => {
            searchTickets(query);
        }, 300);
    });
    
    // Buscar con Enter
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimeout);
            searchTickets(e.target.value.trim());
        }
    });
    
    // Inicializar botones de filtro
    initFilterButtons();
}

// Función para inicializar los botones de filtro
function initFilterButtons() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.dataset.filter;
            
            // Actualizar estado activo visual
            filterButtons.forEach(btn => {
                btn.classList.remove('active', 'bg-primary/20', 'text-white', 'border-primary');
                btn.classList.add('bg-card-dark', 'text-text-secondary');
            });
            
            this.classList.add('active', 'bg-primary/20', 'text-white', 'border-primary');
            this.classList.remove('bg-card-dark', 'text-text-secondary');
            
            // Limpiar búsqueda
            const searchInput = document.getElementById('ticket-search-input');
            if (searchInput) {
                searchInput.value = '';
            }
            
            // Aplicar filtro
            currentFilter = filter;
            applyFilter(filter);
        });
    });
}

// Función para aplicar filtro a los boletos
function applyFilter(filter) {
    const allButtons = document.querySelectorAll('#tickets-grid button[data-numero-boleto]');
    let visibleCount = 0;
    
    allButtons.forEach(button => {
        const estado = button.dataset.estado || 'disponible';
        let shouldShow = false;
        
        switch(filter) {
            case 'all':
                shouldShow = true;
                break;
            case 'disponible':
                shouldShow = estado === 'disponible' && !button.disabled;
                break;
            case 'seleccionado':
                shouldShow = estado === 'seleccionado' || button.classList.contains('bg-primary');
                break;
            case 'reservado':
                shouldShow = estado === 'reservado' || 
                           (button.disabled && button.classList.contains('border-yellow-700'));
                break;
            case 'vendido':
                shouldShow = estado === 'vendido' || 
                           (button.disabled && button.classList.contains('border-red-900'));
                break;
        }
        
        if (shouldShow) {
            button.style.display = '';
            visibleCount++;
        } else {
            button.style.display = 'none';
        }
    });
    
    console.log(`Filtro "${filter}" aplicado: ${visibleCount} boletos visibles`);
}

// Función para buscar boletos
function searchTickets(query) {
    if (!query) {
        // Si no hay búsqueda, aplicar solo el filtro
        applyFilter(currentFilter);
        return;
    }
    
    const ticketNumber = parseInt(query);
    const allButtons = document.querySelectorAll('#tickets-grid button[data-numero-boleto]');
    
    if (isNaN(ticketNumber)) {
        // Si no es un número, buscar por texto en los números de boleto
        allButtons.forEach(btn => {
            const btnText = btn.textContent.trim();
            const btnNumber = btn.dataset.numeroBoleto || '';
            const matches = btnText.includes(query) || btnNumber.includes(query);
            const estado = btn.dataset.estado || 'disponible';
            
            let matchesFilter = true;
            if (currentFilter !== 'all') {
                matchesFilter = shouldShowByFilter(btn, currentFilter, estado);
            }
            
            btn.style.display = (matches && matchesFilter) ? '' : 'none';
        });
        return;
    }
    
    // Buscar el boleto específico
    let found = false;
    
    allButtons.forEach(btn => {
        const btnNumber = parseInt(btn.dataset.numeroBoletoInt);
        const matches = btnNumber === ticketNumber;
        const estado = btn.dataset.estado || 'disponible';
        
        let matchesFilter = true;
        if (currentFilter !== 'all') {
            matchesFilter = shouldShowByFilter(btn, currentFilter, estado);
        }
        
        if (matches && matchesFilter) {
            btn.style.display = '';
            if (!found) {
                btn.scrollIntoView({ behavior: 'smooth', block: 'center' });
                // Resaltar temporalmente
                btn.style.animation = 'pulse 0.5s ease-in-out 3';
                setTimeout(() => {
                    btn.style.animation = '';
                }, 1500);
                found = true;
            }
        } else {
            btn.style.display = 'none';
        }
    });
    
    if (!found) {
        console.log(`Boleto #${ticketNumber} no encontrado o no coincide con el filtro activo`);
    }
}

// Función auxiliar para verificar si un boleto debe mostrarse según el filtro
function shouldShowByFilter(button, filter, estado) {
    switch(filter) {
        case 'disponible':
            return estado === 'disponible' && !button.disabled;
        case 'seleccionado':
            return estado === 'seleccionado' || button.classList.contains('bg-primary');
        case 'reservado':
            return estado === 'reservado' || 
                   (button.disabled && button.classList.contains('border-yellow-700'));
        case 'vendido':
            return estado === 'vendido' || 
                   (button.disabled && button.classList.contains('border-red-900'));
        default:
            return true;
    }
}

// Función para manejar el botón "Proceder al Pago" (MODIFICADA)
function handleProceedToPayment() {
    // Verificar si hay boletos asignados
    if (assignedTickets.length === 0) {
        // Intentar obtener de localStorage
        const savedData = localStorage.getItem('assignedTicketsData');
        if (savedData) {
            const data = JSON.parse(savedData);
            assignedTickets = data.boletos_asignados || [];
        }
    }
    
    if (assignedTickets.length === 0) {
        customAlert('Por favor asigna boletos antes de proceder al pago.', 'Boletos Requeridos', 'warning');
        return false;
    }
    
    // Guardar también información del sorteo actual antes de redirigir
    if (currentSorteoData) {
        const sorteoData = {
            id: currentSorteoData.id_sorteo.toString(),
            titulo: currentSorteoData.titulo,
            subtitulo: currentSorteoData.descripcion ? currentSorteoData.descripcion.substring(0, 50) + '...' : '',
            descripcion: currentSorteoData.descripcion || '',
            descripcionCompleta: currentSorteoData.descripcion || '',
            imagen: currentSorteoData.imagen_url || '',
            precio: parseFloat(currentSorteoData.precio_boleto) || 0,
            boletosVendidos: currentSorteoData.boletos_vendidos || 0,
            boletosTotales: currentSorteoData.total_boletos || 0,
            tiempoRestante: currentSorteoData.tiempo_restante || {}
        };
        localStorage.setItem('selectedSorteo', JSON.stringify(sorteoData));
    }
    
    // Guardar los boletos asignados (ya están en localStorage desde displayAssignedTickets)
    // Redirigir a FinalizarPagoBoletos.php
    window.location.href = 'FinalizarPagoBoletos.php';
    return false;
}

// Función para guardar los boletos seleccionados (compatibilidad)
function saveSelectedTickets() {
    return handleProceedToPayment();
}

</script>

</body></html>

<!-- Luego del sorteo -->

<!-- Luego del sorteo -->
