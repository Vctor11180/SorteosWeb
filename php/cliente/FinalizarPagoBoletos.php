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
<input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-white focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-input-border bg-[#111318] focus:border-primary h-12 placeholder:text-text-secondary px-4 text-base font-normal leading-normal transition-all" placeholder="Ej. Juan Pérez"/>
</label>
<div class="flex flex-col md:flex-row gap-5">
<label class="flex flex-col flex-1">
<p class="text-white text-sm font-medium leading-normal pb-2">Correo Electrónico</p>
<input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-white focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-input-border bg-[#111318] focus:border-primary h-12 placeholder:text-text-secondary px-4 text-base font-normal leading-normal transition-all" placeholder="ejemplo@email.com" type="email"/>
</label>
<label class="flex flex-col flex-1">
<p class="text-white text-sm font-medium leading-normal pb-2">Teléfono</p>
<input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-white focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-input-border bg-[#111318] focus:border-primary h-12 placeholder:text-text-secondary px-4 text-base font-normal leading-normal transition-all" placeholder="+58 412 1234567" type="tel"/>
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
<script src="js/client-layout.js"></script>
<script src="js/custom-alerts.js"></script>
<script>
// Inicializar layout del cliente
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar el layout con 'sorteos' como página activa
    if (window.ClientLayout) {
        ClientLayout.init('sorteos');
    }
    
    // Cargar información del sorteo y boletos seleccionados
    loadPaymentData();
    
    // Asegurar que el timer de reserva se inicialice (igual que DashboardCliente)
    setTimeout(function() {
        startReservationTimer();
    }, 100);
    
    // Inicializar funcionalidades de botones y formularios
    initPaymentPageFunctionality();
});

// Función para cargar los datos del sorteo y boletos desde localStorage
function loadPaymentData() {
    const sorteoData = JSON.parse(localStorage.getItem('selectedSorteo')) || getDefaultSorteoData();
    const selectedTickets = JSON.parse(localStorage.getItem('selectedTickets')) || getDefaultTickets();
    
    // Actualizar nombre del sorteo
    const sorteoNameElement = document.getElementById('sorteo-name-text');
    if (sorteoNameElement) {
        sorteoNameElement.textContent = sorteoData.titulo || 'Gran Sorteo Anual';
    }
    
    // Actualizar nombres del sorteo en los boletos
    const sorteoName1 = document.getElementById('sorteo-name-1');
    const sorteoName2 = document.getElementById('sorteo-name-2');
    if (sorteoName1) sorteoName1.textContent = sorteoData.titulo || 'Sorteo Gran Premio';
    if (sorteoName2) sorteoName2.textContent = sorteoData.titulo || 'Sorteo Gran Premio';
    
    // Actualizar precios de los boletos
    const ticketPrice = sorteoData.precio || 50.00;
    const ticketPrice1 = document.getElementById('ticket-price-1');
    const ticketPrice2 = document.getElementById('ticket-price-2');
    if (ticketPrice1) ticketPrice1.textContent = `$${ticketPrice.toFixed(2)}`;
    if (ticketPrice2) ticketPrice2.textContent = `$${ticketPrice.toFixed(2)}`;
    
    // Actualizar números de boletos si están disponibles
    if (selectedTickets.length >= 1) {
        const ticket1 = document.querySelector('#tickets-container > div:first-child p.text-white');
        if (ticket1) ticket1.textContent = `Boleto #${String(selectedTickets[0]).padStart(3, '0')}`;
    }
    if (selectedTickets.length >= 2) {
        const ticket2 = document.querySelector('#tickets-container > div:last-child p.text-white');
        if (ticket2) ticket2.textContent = `Boleto #${String(selectedTickets[1]).padStart(3, '0')}`;
    }
    
    // Calcular y actualizar totales
    const ticketCount = selectedTickets.length || 2; // Por defecto 2 boletos
    const subtotal = ticketPrice * ticketCount;
    const total = subtotal;
    
    const subtotalElement = document.getElementById('subtotal-amount');
    const totalElement = document.getElementById('total-amount');
    if (subtotalElement) subtotalElement.textContent = `$${subtotal.toFixed(2)}`;
    if (totalElement) totalElement.textContent = `$${total.toFixed(2)}`;
    
    // Guardar el precio unitario para el timer
    window.ticketPrice = ticketPrice;
    window.ticketCount = ticketCount;
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
    const timerElement = document.getElementById('reservation-timer');
    const minutosElement = document.getElementById('timer-minutos');
    const segundosElement = document.getElementById('timer-segundos');
    
    if (!timerElement && !minutosElement && !segundosElement) return;
    
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
    
    // Click en dropzone
    dropzone.addEventListener('click', function() {
        fileInput.click();
    });
    
    // Cambio de archivo
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            handleFileUpload(file);
        }
    });
    
    // Drag and drop
    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('border-primary', 'bg-primary/5');
    });
    
    dropzone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('border-primary', 'bg-primary/5');
    });
    
    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('border-primary', 'bg-primary/5');
        
        const file = e.dataTransfer.files[0];
        if (file) {
            handleFileUpload(file);
        }
    });
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
    
    // Mostrar preview o mensaje de éxito
    const dropzone = document.querySelector('.group.cursor-pointer');
    if (dropzone) {
        dropzone.innerHTML = `
            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                <span class="material-symbols-outlined text-4xl text-green-500 mb-3">check_circle</span>
                <p class="mb-2 text-sm text-white font-semibold">${file.name}</p>
                <p class="text-xs text-text-secondary">${(file.size / 1024).toFixed(2)} KB</p>
                <button id="remove-file-btn" class="mt-3 text-xs text-red-400 hover:text-red-300 underline">Eliminar archivo</button>
            </div>
        `;
        
        // Botón para eliminar archivo
        const removeBtn = document.getElementById('remove-file-btn');
        if (removeBtn) {
            removeBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                resetComprobanteUpload();
            });
        }
        
        // Guardar información del archivo
        localStorage.setItem('comprobanteFileName', file.name);
        localStorage.setItem('comprobanteFileSize', file.size);
    }
}

// Función para resetear el upload de comprobante
function resetComprobanteUpload() {
    const dropzone = document.querySelector('.group.cursor-pointer');
    const fileInput = document.getElementById('dropzone-file');
    
    if (dropzone && fileInput) {
        dropzone.innerHTML = `
            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                <span class="material-symbols-outlined text-4xl text-text-secondary mb-3 group-hover:text-primary transition-colors">cloud_upload</span>
                <p class="mb-2 text-sm text-text-secondary"><span class="font-semibold text-primary">Haz clic para subir</span> o arrastra y suelta</p>
                <p class="text-xs text-gray-500">PNG, JPG o PDF (MAX. 2MB)</p>
            </div>
        `;
        fileInput.value = '';
        localStorage.removeItem('comprobanteFileName');
        localStorage.removeItem('comprobanteFileSize');
        
        // Re-inicializar
        initComprobanteUpload();
    }
}

// Función para inicializar el botón "Finalizar Compra"
function initFinalizarCompra() {
    const finalizarBtn = Array.from(document.querySelectorAll('button')).find(
        btn => btn.textContent.includes('Finalizar Compra')
    );
    
    if (finalizarBtn) {
        finalizarBtn.addEventListener('click', function(e) {
            e.preventDefault();
            handleFinalizarCompra();
        });
    }
}

// Función para manejar la finalización de la compra
function handleFinalizarCompra() {
    // Validar información de contacto
    const nombreInput = document.querySelector('input[placeholder*="Juan"]');
    const emailInput = document.querySelector('input[type="email"]');
    const telefonoInput = document.querySelector('input[type="tel"]');
    
    const nombre = nombreInput?.value.trim();
    const email = emailInput?.value.trim();
    const telefono = telefonoInput?.value.trim();
    
    if (!nombre || !email || !telefono) {
        customAlert('Por favor completa todos los campos de información de contacto.', 'Campos Incompletos', 'warning');
        return;
    }
    
    if (!email.includes('@')) {
        customAlert('Por favor ingresa un correo electrónico válido.', 'Email Inválido', 'error');
        return;
    }
    
    // Validar comprobante
    const comprobanteFileName = localStorage.getItem('comprobanteFileName');
    if (!comprobanteFileName) {
        customAlert('Por favor sube el comprobante de pago antes de finalizar la compra.', 'Comprobante Requerido', 'warning');
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
    
    // Confirmar compra
    const sorteoData = JSON.parse(localStorage.getItem('selectedSorteo')) || {};
    const total = document.getElementById('total-amount')?.textContent || '$0.00';
    
    const confirmMessage = `¿Deseas finalizar la compra?\n\n` +
                          `Boletos: ${selectedTickets.length}\n` +
                          `Total: ${total}\n\n` +
                          `Asegúrate de haber realizado la transferencia bancaria y subido el comprobante.`;
    
    customConfirm(confirmMessage, 'Finalizar Compra', 'help').then(confirmed => {
        if (!confirmed) return;
        
        // Simular procesamiento (aquí iría la llamada al servidor)
        console.log('Procesando compra...', {
            nombre,
            email,
            telefono,
            boletos: selectedTickets,
            comprobante: comprobanteFileName,
            total
        });
        
        // Mostrar mensaje de éxito
        customAlert('Tu compra está siendo verificada. Recibirás un correo de confirmación cuando tu pago sea aprobado.\n\nPuedes seguir el estado de tus boletos en la sección "Mis Boletos".', '¡Compra Procesada!', 'success').then(() => {
        });
    });
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
