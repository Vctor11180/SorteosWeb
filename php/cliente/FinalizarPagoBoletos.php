<?php
/**
 * FinalizarPagoBoletos
 * Sistema de Sorteos Web
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación para páginas protegidas
$protectedPages = ['DashboardCliente', 'AjustesPefilCliente', 'MisBoletosCliente', 'MisGanancias', 'SeleccionBoletos', 'SorteoClienteDetalles', 'FinalizarPagoBoletos'];
if (in_array('FinalizarPagoBoletos', $protectedPages) && (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true)) {
    header('Location: InicioSesion.php');
    exit;
}
?>
<!DOCTYPE html>

<html class="dark" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Formulario de Compra - Sorteos Premium</title>
<!-- Fonts -->
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&amp;family=Noto+Sans:wght@400;500;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<!-- Theme Config -->
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
              "border-dark": "#282d39",
              "input-border": "#3b4354",
              "text-secondary": "#9da6b9",
            },
            fontFamily: {
              "display": ["Inter", "sans-serif"],
              "body": ["Noto Sans", "sans-serif"],
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
</head>
<body class="bg-background-light dark:bg-background-dark text-white font-display overflow-hidden h-screen flex selection:bg-primary selection:text-white">
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
<a id="nav-sorteos" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-text-secondary hover:text-white hover:bg-card-dark transition-colors group" href="ListadoSorteosActivos.php">
<span class="material-symbols-outlined text-[24px]">local_activity</span>
<p class="text-sm font-medium">Sorteos</p>
</a>
<a id="nav-boletos" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary text-white group transition-colors" href="MisBoletosCliente.php">
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
<div class="flex-1 overflow-y-auto overflow-x-hidden p-6 lg:p-10">
<div class="w-full max-w-7xl mx-auto">
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
<!-- Left Column: Form Area (8 cols) -->
<div class="lg:col-span-7 xl:col-span-8 flex flex-col gap-8">
<!-- Page Heading -->
<div class="flex flex-col gap-2">
<h1 class="text-white text-3xl md:text-4xl font-black leading-tight tracking-[-0.033em]">Finalizar Compra</h1>
<p class="text-text-secondary text-base font-normal">Completa el formulario para asegurar tus boletos seleccionados.</p>
<p id="sorteo-name-summary" class="text-primary text-sm font-medium mt-1">Sorteo: <span id="sorteo-name-text">Gran Sorteo Anual</span></p>
</div>
<!-- Step 1: Contact Info -->
<div class="bg-card-dark border border-[#282d39] rounded-xl p-6 md:p-8">
<div class="flex items-center gap-3 mb-6">
<div class="flex items-center justify-center size-8 rounded-full bg-primary/20 text-primary font-bold">1</div>
<h3 class="text-xl font-bold text-white">Información de Contacto</h3>
</div>
<div class="flex flex-col gap-5">
    <label class="flex flex-col flex-1">
    <p class="text-white text-sm font-medium leading-normal pb-2">Nombre Completo</p>
    <input id="input-nombre" class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-white focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-input-border bg-[#111318] focus:border-primary h-12 placeholder:text-text-secondary px-4 text-base font-normal leading-normal transition-all" placeholder="Ej. Juan Pérez"/>
    </label>
    <div class="flex flex-col md:flex-row gap-5">
    <label class="flex flex-col flex-1">
    <p class="text-white text-sm font-medium leading-normal pb-2">Correo Electrónico</p>
    <input id="input-email" class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-white focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-input-border bg-[#111318] focus:border-primary h-12 placeholder:text-text-secondary px-4 text-base font-normal leading-normal transition-all" placeholder="ejemplo@email.com" type="email"/>
    </label>
    <label class="flex flex-col flex-1">
    <p class="text-white text-sm font-medium leading-normal pb-2">Teléfono</p>
    <input id="input-telefono" class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-white focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-input-border bg-[#111318] focus:border-primary h-12 placeholder:text-text-secondary px-4 text-base font-normal leading-normal transition-all" placeholder="+58 412 1234567" type="tel"/>
    </label>
</div>
</div>
</div>
<!-- Step 2: Payment Instructions -->
<div class="bg-card-dark border border-[#282d39] rounded-xl p-6 md:p-8">
<div class="flex items-center gap-3 mb-6">
<div class="flex items-center justify-center size-8 rounded-full bg-primary/20 text-primary font-bold">2</div>
<h3 class="text-xl font-bold text-white">Método de Pago</h3>
</div>
<div class="bg-[#111318] rounded-lg p-5 border border-input-border mb-6">
<div class="flex justify-between items-start mb-4">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-primary">account_balance</span>
<span class="font-bold text-white">Transferencia Bancaria / Pago Móvil</span>
</div>
<span class="bg-green-500/20 text-green-400 text-xs font-bold px-2 py-1 rounded">Activo</span>
</div>
<div class="grid md:grid-cols-2 gap-4 text-sm text-text-secondary">
<div>
<p class="mb-1 text-xs uppercase tracking-wider font-semibold text-gray-500">Banco</p>
<p class="text-white font-medium">Banco Nacional de Crédito (BNC)</p>
</div>
<div>
<p class="mb-1 text-xs uppercase tracking-wider font-semibold text-gray-500">Titular</p>
<p class="text-white font-medium">Inversiones Sorteos Premium C.A.</p>
</div>
<div>
<p class="mb-1 text-xs uppercase tracking-wider font-semibold text-gray-500">Número de Cuenta</p>
<p class="text-white font-medium tracking-wide">0191-0001-22-1234567890</p>
</div>
<div>
<p class="mb-1 text-xs uppercase tracking-wider font-semibold text-gray-500">Documento / RIF</p>
<p class="text-white font-medium">J-12345678-9</p>
</div>
</div>
</div>
<!-- Step 3: Upload Proof -->
<div class="flex flex-col gap-3">
<label class="text-white text-sm font-medium leading-normal">Comprobante de Pago</label>
<div class="relative flex flex-col items-center justify-center w-full h-48 rounded-xl border-2 border-dashed border-input-border bg-[#111318] hover:bg-[#161920] hover:border-primary transition-all cursor-pointer group">
<div class="flex flex-col items-center justify-center pt-5 pb-6">
<span class="material-symbols-outlined text-4xl text-text-secondary mb-3 group-hover:text-primary transition-colors">cloud_upload</span>
<p class="mb-2 text-sm text-text-secondary"><span class="font-semibold text-primary">Haz clic para subir</span> o arrastra y suelta</p>
<p class="text-xs text-gray-500">PNG, JPG o PDF (MAX. 2MB)</p>
</div>
<input class="hidden" id="dropzone-file" type="file"/>
</div>
<p class="text-xs text-text-secondary mt-1 flex items-center gap-1">
<span class="material-symbols-outlined text-sm">info</span>
                            Asegúrate que el número de referencia sea visible.
                        </p>
</div>
</div>
</div>
<!-- Right Column: Summary & Timer (4 cols) -->
<div class="lg:col-span-5 xl:col-span-4 w-full">
<div class="sticky top-6 flex flex-col gap-6">
<!-- Timer Card -->
<div class="bg-card-dark border border-[#282d39] rounded-xl p-5 shadow-lg relative overflow-hidden">
<!-- Gradient accent top -->
<div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-purple-500"></div>
<div class="flex items-center justify-between mb-4">
<span class="text-sm font-medium text-text-secondary uppercase tracking-wider">Tiempo Restante</span>
<span class="material-symbols-outlined text-text-secondary animate-pulse">timer</span>
</div>
<div class="flex gap-3 justify-center">
<div class="flex flex-col items-center gap-1">
<div class="flex h-12 w-14 items-center justify-center rounded-lg bg-[#111318] border border-[#282d39]">
<p id="timer-minutos" class="text-white text-2xl font-bold font-mono">09</p>
</div>
<span class="text-[10px] text-text-secondary uppercase">Min</span>
</div>
<div class="flex items-center pb-5 text-text-secondary text-xl font-bold">:</div>
<div class="flex flex-col items-center gap-1">
<div class="flex h-12 w-14 items-center justify-center rounded-lg bg-[#111318] border border-[#282d39]">
<p id="timer-segundos" class="text-white text-2xl font-bold font-mono text-primary">55</p>
</div>
<span class="text-[10px] text-text-secondary uppercase">Seg</span>
</div>
</div>
<p class="text-center text-xs text-text-secondary mt-3">Tus boletos se liberarán si el tiempo expira.</p>
</div>
<!-- Summary Card -->
<div class="bg-card-dark border border-[#282d39] rounded-xl overflow-hidden shadow-lg">
<div class="p-6 border-b border-[#282d39]">
<h3 class="text-lg font-bold text-white mb-4">Resumen del Pedido</h3>
<!-- Ticket List -->
<div id="tickets-container" class="flex flex-col gap-3 mb-6 max-h-48 overflow-y-auto pr-2 custom-scrollbar">
<!-- Los boletos se cargarán dinámicamente aquí -->
<div class="flex justify-between items-center p-3 rounded-lg bg-[#111318] border border-[#282d39]">
<div class="flex items-center gap-3">
<div class="bg-primary/20 p-2 rounded text-primary">
<span class="material-symbols-outlined text-lg">confirmation_number</span>
</div>
<div>
<p class="text-white font-medium text-sm">Boleto #004</p>
<p id="sorteo-name-1" class="text-xs text-text-secondary">Sorteo Gran Premio</p>
</div>
</div>
<p id="ticket-price-1" class="text-white font-bold text-sm">$50.00</p>
</div>
<div class="flex justify-between items-center p-3 rounded-lg bg-[#111318] border border-[#282d39]">
<div class="flex items-center gap-3">
<div class="bg-primary/20 p-2 rounded text-primary">
<span class="material-symbols-outlined text-lg">confirmation_number</span>
</div>
<div>
<p class="text-white font-medium text-sm">Boleto #013</p>
<p id="sorteo-name-2" class="text-xs text-text-secondary">Sorteo Gran Premio</p>
</div>
</div>
<p id="ticket-price-2" class="text-white font-bold text-sm">$50.00</p>
</div>
</div>
<!-- Calculations -->
<div class="flex flex-col gap-2 pt-2 border-t border-[#282d39]/50">
<div class="flex justify-between text-sm">
<span class="text-text-secondary">Subtotal</span>
<span id="subtotal-amount" class="text-white">$100.00</span>
</div>
<div class="flex justify-between text-sm">
<span class="text-text-secondary">Comisión de servicio</span>
<span class="text-white">$0.00</span>
</div>
</div>
</div>
<!-- Total & Action -->
<div class="p-6 bg-[#161920]">
<div class="flex justify-between items-end mb-6">
<span class="text-white font-medium">Total a Pagar</span>
<span id="total-amount" class="text-3xl font-black text-white tracking-tight">$100.00</span>
</div>
<button class="w-full flex items-center justify-center gap-2 bg-primary hover:bg-blue-600 active:scale-[0.98] transition-all text-white font-bold py-4 rounded-lg shadow-lg shadow-primary/25">
<span class="material-symbols-outlined">payments</span>
                                Finalizar Compra
                            </button>
<div class="mt-4 flex justify-center items-center gap-2 text-text-secondary">
<span class="material-symbols-outlined text-green-500 text-sm">lock</span>
<span class="text-xs font-medium">Pagos procesados de forma segura</span>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
<!-- Optional CSS for scrollbar in summary list -->
<style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #111318; 
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #282d39; 
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #3b4254; 
        }
        
        /* Custom scrollbar for main body */
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
    </style>

<!-- Client Layout Script -->
<script src="js/custom-alerts.js"></script>
<script src="js/client-layout.js"></script>
<script>
// Inicializar layout del cliente
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar el layout con 'sorteos' como página activa
    if (window.ClientLayout) {
        ClientLayout.init('sorteos');
    }
    
    // Cargar información del sorteo y boletos seleccionados desde APIs
    loadRealPaymentData();
    
    // Asegurar que el timer de reserva se inicialice (igual que DashboardCliente)
    setTimeout(function() {
        startReservationTimer();
    }, 100);
    
    // Inicializar funcionalidades de botones y formularios
    initPaymentPageFunctionality();
});

// Función para cargar los datos del sorteo y boletos desde APIs (REAL)
async function loadRealPaymentData() {
    try {
        // Obtener ID del sorteo desde localStorage
        const sorteoDataFromStorage = JSON.parse(localStorage.getItem('selectedSorteo') || '{}');
        const idSorteo = sorteoDataFromStorage.id_sorteo || sorteoDataFromStorage.id;
        
        if (!idSorteo) {
            customAlert('No hay sorteo seleccionado. Serás redirigido.', 'Sorteo No Encontrado', 'warning').then(() => {
                window.location.href = 'ListadoSorteosActivos.php';
            });
            return;
        }
        
        // 1. Cargar datos del sorteo desde API
        const sorteoResponse = await fetch(`api_sorteos.php?action=get_details&id=${idSorteo}`);
        
        if (!sorteoResponse.ok) {
            throw new Error(`Error HTTP al cargar sorteo: ${sorteoResponse.status}`);
        }
        
        const sorteoText = await sorteoResponse.text();
        let sorteoData;
        
        try {
            sorteoData = JSON.parse(sorteoText);
        } catch (parseError) {
            console.error('Error al parsear JSON del sorteo:', parseError);
            console.error('Respuesta recibida:', sorteoText.substring(0, 200));
            throw new Error('Error en la respuesta del servidor (formato inválido)');
        }
        
        if (!sorteoData.success || !sorteoData.data) {
            throw new Error(sorteoData.error || 'No se pudieron cargar los datos del sorteo');
        }
        
        const sorteo = sorteoData.data;
        
        // 2. Cargar boletos asignados desde API
        const boletosResponse = await fetch(`api_boletos.php?action=get_my_assigned&id_sorteo=${idSorteo}`);
        
        if (!boletosResponse.ok) {
            throw new Error(`Error HTTP al cargar boletos: ${boletosResponse.status}`);
        }
        
        const boletosText = await boletosResponse.text();
        let boletosData;
        
        try {
            boletosData = JSON.parse(boletosText);
        } catch (parseError) {
            console.error('Error al parsear JSON de boletos:', parseError);
            console.error('Respuesta recibida:', boletosText.substring(0, 200));
            throw new Error('Error en la respuesta del servidor (formato inválido)');
        }
        
        if (!boletosData.success || !boletosData.data) {
            throw new Error(boletosData.error || 'No se pudieron cargar los boletos asignados');
        }
        
        const assignedBoletos = boletosData.data.boletos || [];
        
        if (assignedBoletos.length === 0) {
            customAlert('No tienes boletos asignados. Serás redirigido a la selección de boletos.', 'Sin Boletos', 'warning').then(() => {
                window.location.href = 'SeleccionBoletos.php';
            });
            return;
        }
        
        // 3. Actualizar UI con datos reales
        updateUIWithRealData(sorteo, assignedBoletos);
        
        // 4. Guardar datos en window para usar en handleFinalizarCompra
        window.currentSorteoData = sorteo;
        window.currentAssignedBoletos = assignedBoletos;
        window.currentTicketPrice = parseFloat(sorteo.precio_boleto || 0);
        window.currentTicketCount = assignedBoletos.length;
        
        // 5. Actualizar timer si hay tiempo restante
        if (assignedBoletos.length > 0 && assignedBoletos[0].tiempo_restante !== null) {
            const tiempoRestante = assignedBoletos[0].tiempo_restante;
            if (tiempoRestante > 0) {
                startReservationTimerWithSeconds(tiempoRestante);
            }
        }
        
    } catch (error) {
        console.error('Error al cargar datos de pago:', error);
        customAlert('Error al cargar los datos: ' + error.message + '\n\nSerás redirigido a la página anterior.', 'Error de Carga', 'error').then(() => {
            window.location.href = 'SeleccionBoletos.php';
        });
    }
}

// Función para actualizar UI con datos reales
function updateUIWithRealData(sorteo, assignedBoletos) {
    // Actualizar nombre del sorteo
    const sorteoNameElement = document.getElementById('sorteo-name-text');
    if (sorteoNameElement) {
        sorteoNameElement.textContent = sorteo.titulo || 'Gran Sorteo Anual';
    }
    
    // Renderizar boletos dinámicamente
    renderRealTickets(sorteo, assignedBoletos);
    
    // Calcular y actualizar totales
    const ticketPrice = parseFloat(sorteo.precio_boleto || 0);
    const ticketCount = assignedBoletos.length;
    const subtotal = ticketPrice * ticketCount;
    const total = subtotal;
    
    const subtotalElement = document.getElementById('subtotal-amount');
    const totalElement = document.getElementById('total-amount');
    if (subtotalElement) subtotalElement.textContent = `$${subtotal.toFixed(2)}`;
    if (totalElement) totalElement.textContent = `$${total.toFixed(2)}`;
    
    // Guardar IDs de boletos en localStorage para la transacción
    const ticketIds = assignedBoletos.map(b => b.numero_boleto_int || parseInt(b.numero_boleto));
    localStorage.setItem('selectedTickets', JSON.stringify(ticketIds));
}

// Función para renderizar boletos dinámicamente en el DOM
function renderRealTickets(sorteo, assignedBoletos) {
    const ticketsContainer = document.getElementById('tickets-container');
    if (!ticketsContainer) return;
    
    if (assignedBoletos.length === 0) {
        ticketsContainer.innerHTML = '<p class="text-text-secondary text-sm text-center">No hay boletos asignados</p>';
        return;
    }
    
    const ticketPrice = parseFloat(sorteo.precio_boleto || 0);
    
    // Crear HTML para cada boleto
    ticketsContainer.innerHTML = assignedBoletos.map((boleto, index) => {
        const numeroFormateado = String(boleto.numero_boleto_int || parseInt(boleto.numero_boleto)).padStart(4, '0');
        return `
            <div class="flex justify-between items-center p-3 rounded-lg bg-[#111318] border border-[#282d39]">
                <div class="flex items-center gap-3">
                    <div class="bg-primary/20 p-2 rounded text-primary">
                        <span class="material-symbols-outlined text-lg">confirmation_number</span>
                    </div>
                    <div>
                        <p class="text-white font-medium text-sm">Boleto #${numeroFormateado}</p>
                        <p class="text-xs text-text-secondary">${sorteo.titulo || 'Sorteo'}</p>
                    </div>
                </div>
                <p class="text-white font-bold text-sm">$${ticketPrice.toFixed(2)}</p>
            </div>
        `;
    }).join('');
}

// Función para obtener datos por defecto del sorteo
function getDefaultSorteoData() {
    return {
        id: 'default',
        titulo: 'Gran Sorteo Anual',
        precio: 50.00
    };
}

// Función para obtener boletos por defecto
function getDefaultTickets() {
    return [4, 13]; // Boletos #004 y #013 como ejemplo
}

// Almacenar intervalos activos para poder limpiarlos si es necesario
const activeCountdownIntervals = new Map();

// Timer de reserva (14:59 minutos) - EXACTO COMO DashboardCliente
function startReservationTimer() {
    // Por defecto, usar 14:59 si no hay tiempo específico
    startReservationTimerWithSeconds(14 * 60 + 59);
}

// Función para iniciar timer con segundos específicos (para usar con datos reales)
function startReservationTimerWithSeconds(initialSeconds) {
    const timerElement = document.getElementById('reservation-timer');
    const minutosElement = document.getElementById('timer-minutos');
    const segundosElement = document.getElementById('timer-segundos');
    
    if (!timerElement && !minutosElement && !segundosElement) return;
    
    // Si ya existe un intervalo para este elemento, limpiarlo primero
    if (activeCountdownIntervals.has('reservation-timer')) {
        clearInterval(activeCountdownIntervals.get('reservation-timer'));
        activeCountdownIntervals.delete('reservation-timer');
    }
    
    // Tiempo inicial desde parámetro (variable local en closure)
    let remainingSeconds = parseInt(initialSeconds) || (14 * 60 + 59);
    remainingSeconds = Math.max(0, remainingSeconds); // No permitir negativos
    
    // Función de actualización (EXACTO COMO DashboardCliente)
    function updateReservationTimer() {
        if (remainingSeconds <= 0) {
            if (timerElement) timerElement.textContent = '00:00';
            if (minutosElement) minutosElement.textContent = '00';
            if (segundosElement) segundosElement.textContent = '00';
            // Limpiar el intervalo cuando llegue a cero
            if (activeCountdownIntervals.has('reservation-timer')) {
                clearInterval(activeCountdownIntervals.get('reservation-timer'));
                activeCountdownIntervals.delete('reservation-timer');
            }
            // Aquí se podría liberar los boletos o mostrar un mensaje
            customAlert('El tiempo de reserva ha expirado. Los boletos han sido liberados.', 'Tiempo Expirado', 'warning').then(() => {
                window.location.href = 'ListadoSorteosActivos.php';
            });
            return;
        }
        
        const minutes = Math.floor(remainingSeconds / 60);
        const seconds = remainingSeconds % 60;
        
        if (timerElement) {
            timerElement.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }
        if (minutosElement) minutosElement.textContent = String(minutes).padStart(2, '0');
        if (segundosElement) segundosElement.textContent = String(seconds).padStart(2, '0');
        
        remainingSeconds--;
    }
    
    // Actualizar inmediatamente (EXACTO COMO DashboardCliente)
    updateReservationTimer();
    
    // Iniciar intervalo que se actualiza cada segundo (EXACTO COMO DashboardCliente)
    const intervalId = setInterval(updateReservationTimer, 1000);
    activeCountdownIntervals.set('reservation-timer', intervalId);
}

// Función para inicializar todas las funcionalidades de la página de pago
function initPaymentPageFunctionality() {
    // Upload de comprobante
    initComprobanteUpload();
    
    // Botón finalizar compra
    initFinalizarCompra();
    
    // Copiar información bancaria
    initCopyBankInfo();
}

// Función para manejar el upload de comprobante
function initComprobanteUpload() {
    const dropzone = document.querySelector('.group.cursor-pointer');
    const fileInput = document.getElementById('dropzone-file');
    
    if (!dropzone || !fileInput) return;
    
    // Remover listeners anteriores si existen para evitar duplicados
    const newDropzone = dropzone.cloneNode(true);
    dropzone.parentNode.replaceChild(newDropzone, dropzone);
    const updatedDropzone = newDropzone;
    
    // Click en dropzone (solo si no tiene el botón eliminar)
    updatedDropzone.addEventListener('click', function(e) {
        // Si el click es en el botón eliminar, no hacer nada aquí
        if (e.target && e.target.id === 'remove-file-btn') {
            return;
        }
        const currentFileInput = document.getElementById('dropzone-file');
        if (currentFileInput) {
            currentFileInput.click();
        }
    });
    
    // Cambio de archivo
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            handleFileUpload(file);
        }
    });
    
    // Drag and drop
    updatedDropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('border-primary', 'bg-primary/5');
    });
    
    updatedDropzone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('border-primary', 'bg-primary/5');
    });
    
    updatedDropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('border-primary', 'bg-primary/5');
        
        const file = e.dataTransfer.files[0];
        if (file) {
            // Actualizar el fileInput también cuando se arrastra
            const currentFileInput = document.getElementById('dropzone-file');
            if (currentFileInput) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                currentFileInput.files = dataTransfer.files;
            }
            handleFileUpload(file);
        }
    });
}

// Variable global para guardar el archivo seleccionado
let selectedComprobanteFile = null;

// Función auxiliar para escapar HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Función para manejar el archivo subido
function handleFileUpload(file) {
    // Validar tipo de archivo
    const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'application/pdf'];
    if (!allowedTypes.includes(file.type)) {
        customAlert('Por favor sube un archivo PNG, JPG o PDF.', 'Tipo de Archivo Inválido', 'error');
        return;
    }
    
    // Validar tamaño (2MB)
    const maxSize = 2 * 1024 * 1024; // 2MB
    if (file.size > maxSize) {
        customAlert('El archivo no puede ser mayor a 2MB.', 'Archivo Demasiado Grande', 'error');
        return;
    }
    
    // Guardar referencia global al archivo
    selectedComprobanteFile = file;
    
    // Mostrar preview o mensaje de éxito
    const dropzone = document.querySelector('.group.cursor-pointer');
    const fileInput = document.getElementById('dropzone-file');
    
    if (dropzone) {
        // Guardar referencia al HTML original antes de cambiarlo
        const originalHTML = dropzone.innerHTML;
        
        // Actualizar el fileInput para que mantenga el archivo
        if (fileInput && (!fileInput.files || fileInput.files.length === 0)) {
            // Si el input no tiene el archivo, intentar actualizarlo
            try {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;
            } catch (e) {
                console.warn('No se pudo actualizar fileInput directamente:', e);
                // Continuar de todas formas, usaremos la variable global
            }
        }
        
        dropzone.innerHTML = `
            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                <span class="material-symbols-outlined text-4xl text-green-500 mb-3">check_circle</span>
                <p class="mb-2 text-sm text-white font-semibold">${escapeHtml(file.name)}</p>
                <p class="text-xs text-text-secondary">${(file.size / 1024).toFixed(2)} KB</p>
                <button id="remove-file-btn" type="button" class="mt-3 text-xs text-red-400 hover:text-red-300 underline cursor-pointer" onclick="resetComprobanteUpload(); return false;">Eliminar archivo</button>
            </div>
        `;
        
        // Guardar información del archivo en localStorage (backup)
        localStorage.setItem('comprobanteFileName', file.name);
        localStorage.setItem('comprobanteFileSize', file.size);
        localStorage.setItem('comprobanteFileType', file.type);
    }
}

// Función para resetear el upload de comprobante (disponible globalmente)
function resetComprobanteUpload() {
    const dropzone = document.querySelector('.group.cursor-pointer');
    const fileInput = document.getElementById('dropzone-file');
    
    // Limpiar referencia global al archivo
    selectedComprobanteFile = null;
    
    if (dropzone && fileInput) {
        dropzone.innerHTML = `
            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                <span class="material-symbols-outlined text-4xl text-text-secondary mb-3 group-hover:text-primary transition-colors">cloud_upload</span>
                <p class="mb-2 text-sm text-text-secondary"><span class="font-semibold text-primary">Haz clic para subir</span> o arrastra y suelta</p>
                <p class="text-xs text-gray-500">PNG, JPG o PDF (MAX. 2MB)</p>
            </div>
        `;
        
        // Limpiar el input file
        fileInput.value = '';
        
        // Limpiar localStorage
        localStorage.removeItem('comprobanteFileName');
        localStorage.removeItem('comprobanteFileSize');
        localStorage.removeItem('comprobanteFileType');
        
        // Re-inicializar los listeners del dropzone
        initComprobanteUpload();
    }
}

// Hacer la función disponible globalmente para onclick inline
window.resetComprobanteUpload = resetComprobanteUpload;

// Función para inicializar el botón "Finalizar Compra"
function initFinalizarCompra() {
    const finalizarBtn = Array.from(document.querySelectorAll('button')).find(
        btn => btn.textContent.includes('Finalizar Compra')
    );
    
    if (finalizarBtn) {
        finalizarBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            await handleFinalizarCompra();
        });
    }
}

// Función para manejar la finalización de la compra (IMPLEMENTACIÓN REAL)
async function handleFinalizarCompra() {
    try {
        // Validar información de contacto
        const nombreInput = document.getElementById('input-nombre');
        const emailInput = document.getElementById('input-email');
        const telefonoInput = document.getElementById('input-telefono');
        
        const nombre = nombreInput?.value.trim();
        const email = emailInput?.value.trim();
        const telefono = telefonoInput?.value.trim();
        
        if (!nombre || !email || !telefono) {
            customAlert('Por favor completa todos los campos de información de contacto.', 'Campos Incompletos', 'warning');
            return;
        }
        
        // Validar formato de email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            customAlert('Por favor ingresa un correo electrónico válido.', 'Email Inválido', 'error');
            return;
        }
        
        // Validar comprobante - verificar tanto el fileInput como la variable global
        const fileInput = document.getElementById('dropzone-file');
        let comprobanteFile = null;
        
        // Intentar obtener el archivo del input
        if (fileInput && fileInput.files && fileInput.files.length > 0) {
            comprobanteFile = fileInput.files[0];
        }
        
        // Si no está en el input, usar la variable global (archivo seleccionado previamente)
        if (!comprobanteFile && selectedComprobanteFile) {
            comprobanteFile = selectedComprobanteFile;
        }
        
        // Si aún no hay archivo, verificar localStorage (backup)
        if (!comprobanteFile) {
            const fileName = localStorage.getItem('comprobanteFileName');
            if (!fileName) {
                customAlert('Por favor sube el comprobante de pago antes de finalizar la compra.', 'Comprobante Requerido', 'warning');
                return;
            }
            // Si hay nombre en localStorage pero no archivo, el archivo se perdió
            customAlert('El archivo del comprobante se perdió. Por favor vuelve a subirlo.', 'Archivo Perdido', 'warning').then(() => {
                resetComprobanteUpload();
            });
            return;
        }
        
        // Validar boletos
        const selectedTickets = JSON.parse(localStorage.getItem('selectedTickets') || '[]');
        if (selectedTickets.length === 0) {
            customAlert('No hay boletos seleccionados. Por favor vuelve a la página anterior.', 'Boletos Requeridos', 'warning').then(() => {
                window.location.href = 'SeleccionBoletos.php';
            });
            return;
        }
        
        // Validar datos del sorteo
        const sorteoData = window.currentSorteoData || JSON.parse(localStorage.getItem('selectedSorteo') || '{}');
        if (!sorteoData.id_sorteo && !sorteoData.id) {
            customAlert('No hay sorteo seleccionado. Serás redirigido.', 'Sorteo No Encontrado', 'warning').then(() => {
                window.location.href = 'ListadoSorteosActivos.php';
            });
            return;
        }
        
        const idSorteo = sorteoData.id_sorteo || sorteoData.id;
        const total = document.getElementById('total-amount')?.textContent || '$0.00';
        const montoTotal = parseFloat(total.replace('$', '').replace(',', ''));
        
        // Confirmar compra
        const confirmMessage = `¿Deseas finalizar la compra?\n\n` +
                              `Boletos: ${selectedTickets.length}\n` +
                              `Total: ${total}\n\n` +
                              `Asegúrate de haber realizado la transferencia bancaria y subido el comprobante.`;
        
        const confirmed = await customConfirm(confirmMessage, 'Finalizar Compra', 'help');
        if (!confirmed) return;
        
        // Deshabilitar botón durante el proceso
        const finalizarBtn = Array.from(document.querySelectorAll('button')).find(
            btn => btn.textContent.includes('Finalizar Compra')
        );
        const originalBtnText = finalizarBtn?.innerHTML;
        if (finalizarBtn) {
            finalizarBtn.disabled = true;
            finalizarBtn.innerHTML = '<span class="material-symbols-outlined animate-spin">hourglass_empty</span> Procesando...';
        }
        
        try {
            // 1. SUBIR COMPROBANTE
            const formData = new FormData();
            formData.append('comprobante', comprobanteFile);
            
            const uploadResponse = await fetch('api_upload.php?action=upload_comprobante', {
                method: 'POST',
                body: formData
            });
            
            if (!uploadResponse.ok) {
                throw new Error(`Error HTTP al subir comprobante: ${uploadResponse.status}`);
            }
            
            const uploadText = await uploadResponse.text();
            let uploadData;
            
            try {
                uploadData = JSON.parse(uploadText);
            } catch (parseError) {
                console.error('Error al parsear JSON del upload:', parseError);
                console.error('Respuesta recibida:', uploadText.substring(0, 200));
                throw new Error('Error en la respuesta del servidor (formato inválido)');
            }
            
            if (!uploadData.success || !uploadData.data) {
                throw new Error(uploadData.error || 'Error al subir el comprobante');
            }
            
            const comprobanteUrl = uploadData.data.file_path || uploadData.data.file_name;
            
            // 2. CREAR TRANSACCIÓN
            const transactionData = {
                id_sorteo: idSorteo,
                numeros_boletos: selectedTickets, // Array de números de boletos (enteros)
                metodo_pago: 'Transferencia',
                referencia_pago: null,
                comprobante_url: comprobanteUrl,
                monto_total: montoTotal
            };
            
            const transactionResponse = await fetch('api_transacciones.php?action=create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(transactionData)
            });
            
            if (!transactionResponse.ok) {
                throw new Error(`Error HTTP al crear transacción: ${transactionResponse.status}`);
            }
            
            const transactionText = await transactionResponse.text();
            let transactionResult;
            
            try {
                transactionResult = JSON.parse(transactionText);
            } catch (parseError) {
                console.error('Error al parsear JSON de transacción:', parseError);
                console.error('Respuesta recibida:', transactionText.substring(0, 200));
                throw new Error('Error en la respuesta del servidor (formato inválido)');
            }
            
            if (!transactionResult.success || !transactionResult.data) {
                throw new Error(transactionResult.error || 'Error al crear la transacción');
            }
            
            // 3. ÉXITO - Limpiar localStorage y redirigir
            localStorage.removeItem('comprobanteFileName');
            localStorage.removeItem('comprobanteFileSize');
            
            // Mostrar mensaje de éxito
            await customAlert(
                '¡Tu compra ha sido registrada exitosamente!\n\n' +
                'Tu transacción está siendo revisada por el administrador.\n' +
                'Recibirás un correo de confirmación cuando tu pago sea aprobado.\n\n' +
                'Puedes seguir el estado de tus boletos en la sección "Mis Boletos".',
                '¡Compra Exitosa!',
                'success'
            );
            
            // Redirigir a Mis Boletos
            window.location.href = 'MisBoletosCliente.php';
            
        } catch (error) {
            console.error('Error al procesar la compra:', error);
            customAlert(
                'Error al procesar la compra: ' + error.message + '\n\n' +
                'Por favor, verifica tus datos e intenta nuevamente. Si el problema persiste, contacta al soporte.',
                'Error en la Compra',
                'error'
            );
        } finally {
            // Restaurar botón
            if (finalizarBtn && originalBtnText) {
                finalizarBtn.disabled = false;
                finalizarBtn.innerHTML = originalBtnText;
            }
        }
        
    } catch (error) {
        console.error('Error general en handleFinalizarCompra:', error);
        customAlert('Error inesperado: ' + error.message, 'Error', 'error');
    }
}

// Función para copiar información bancaria
function initCopyBankInfo() {
    const bankInfoElements = document.querySelectorAll('.text-white.font-medium.tracking-wide, .text-white.font-medium');
    
    bankInfoElements.forEach(element => {
        if (element.textContent.includes('0191') || element.textContent.includes('J-')) {
            element.style.cursor = 'pointer';
            element.title = 'Click para copiar';
            element.addEventListener('click', function() {
                const text = this.textContent.trim();
                navigator.clipboard.writeText(text).then(() => {
                    // Mostrar feedback visual
                    const originalText = this.textContent;
                    this.textContent = '¡Copiado!';
                    this.classList.add('text-green-400');
                    setTimeout(() => {
                        this.textContent = originalText;
                        this.classList.remove('text-green-400');
                    }, 2000);
                }).catch(err => {
                    console.error('Error al copiar:', err);
                });
            });
        }
    });
}
</script>

</div>
</div>
</div>
</div>
</main>

</body></html>

<!-- Estos son los pasos para finalizar el pago de los boletos como cliente después de seleccionar
     los boletos y seleccionar el método de pago y subir el comprobante de pago con el tiempo límite para que se liberen
     los boletos y que se pueda ver el resumen del pedido y el total a pagar. -->
