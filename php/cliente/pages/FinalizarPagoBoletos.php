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
<?php include '../includes/sidebar.php'; ?>
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
    <label class="text-white text-sm font-medium mb-3 block">Selecciona un método de pago</label>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <!-- Transferencia -->
        <label class="cursor-pointer">
            <input type="radio" name="metodo_pago" value="Transferencia" class="peer sr-only" checked onchange="updatePaymentDetails('Transferencia')">
            <div class="rounded-lg border border-input-border p-4 hover:bg-[#1f2530] peer-checked:border-primary peer-checked:bg-primary/5 transition-all h-full">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-primary">account_balance</span>
                    <span class="font-bold text-white text-sm">Transferencia</span>
                </div>
                <p class="text-xs text-text-secondary">BNC, Mercantil</p>
            </div>
        </label>
        
        <!-- Pago Móvil -->
        <label class="cursor-pointer">
            <input type="radio" name="metodo_pago" value="Pago Móvil" class="peer sr-only" onchange="updatePaymentDetails('Pago Móvil')">
            <div class="rounded-lg border border-input-border p-4 hover:bg-[#1f2530] peer-checked:border-primary peer-checked:bg-primary/5 transition-all h-full">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-emerald-500">smartphone</span>
                    <span class="font-bold text-white text-sm">Pago Móvil</span>
                </div>
                <p class="text-xs text-text-secondary">Pago instantáneo</p>
            </div>
        </label>
        
        <!-- Zelle -->
        <label class="cursor-pointer">
            <input type="radio" name="metodo_pago" value="Zelle" class="peer sr-only" onchange="updatePaymentDetails('Zelle')">
            <div class="rounded-lg border border-input-border p-4 hover:bg-[#1f2530] peer-checked:border-primary peer-checked:bg-primary/5 transition-all h-full">
                <div class="flex items-center gap-2 mb-2">
                    <span class="font-black text-[#5800d4] text-sm font-sans">Zelle®</span>
                </div>
                <p class="text-xs text-text-secondary">Pagos en USD</p>
            </div>
        </label>
        
        <!-- Binance -->
        <label class="cursor-pointer">
            <input type="radio" name="metodo_pago" value="Binance" class="peer sr-only" onchange="updatePaymentDetails('Binance')">
            <div class="rounded-lg border border-input-border p-4 hover:bg-[#1f2530] peer-checked:border-primary peer-checked:bg-primary/5 transition-all h-full">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-yellow-500">currency_bitcoin</span>
                    <span class="font-bold text-white text-sm">Binance Pay</span>
                </div>
                <p class="text-xs text-text-secondary">USDT (TRC20)</p>
            </div>
        </label>
        <!-- Tarjeta de Crédito/Débito (Pasarela) -->
        <label class="cursor-pointer group">
            <input type="radio" name="metodo_pago" value="Tarjeta" class="peer sr-only" onchange="updatePaymentDetails('Tarjeta')">
            <div class="p-4 rounded-xl bg-[#161b26] border border-[#282d39] hover:border-primary peer-checked:border-primary peer-checked:bg-primary/5 transition-all h-full flex flex-col items-center justify-center gap-3 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-primary/10 to-transparent opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                <div class="relative z-10 flex flex-col items-center gap-2">
                    <span class="material-symbols-outlined text-blue-400">credit_card</span>
                    <span class="font-bold text-white text-sm">Tarjeta (Bolivia)</span>
                </div>
                <p class="text-xs text-text-secondary">Red Enlace / Libélula</p>
            </div>
        </label>
    </div>
</div>

<!-- Detalles del Pago (Dinámico) -->
<div id="payment-details-container" class="bg-[#111318] rounded-lg p-5 border border-input-border mb-6">
    <!-- El contenido se actualizará vía JS -->
    <div class="flex justify-between items-start mb-4">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">account_balance</span>
            <span class="font-bold text-white">Transferencia Bancaria</span>
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
        <div class="col-span-2">
            <p class="mb-1 text-xs uppercase tracking-wider font-semibold text-gray-500">Número de Cuenta</p>
            <div class="flex items-center gap-2">
                <p class="text-white font-medium tracking-wide font-mono">0191-0001-22-1234567890</p>
                <button type="button" class="text-primary hover:text-white" onclick="copyToClipboard('01910001221234567890')">
                    <span class="material-symbols-outlined text-[16px]">content_copy</span>
                </button>
            </div>
        </div>
        <div>
            <p class="mb-1 text-xs uppercase tracking-wider font-semibold text-gray-500">RIF</p>
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
<script src="../assets/assets/js/custom-alerts.js"></script>
<script src="../assets/assets/js/client-layout.js"></script>
<script>
// Funciones de fallback si customAlert/customConfirm no están disponibles
if (typeof customAlert === 'undefined') {
    window.customAlert = function(message, title, icon) {
        return Promise.resolve(alert(message));
    };
}

if (typeof customConfirm === 'undefined') {
    window.customConfirm = function(message, title, icon) {
        return Promise.resolve(confirm(message));
    };
}

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
        // Limpiar datos antiguos de localStorage para evitar duplicación
        // Los boletos se cargarán siempre desde la API para asegurar datos actualizados
        localStorage.removeItem('selectedTickets');
        
        // Obtener ID del sorteo desde localStorage
        const sorteoDataFromStorage = JSON.parse(localStorage.getItem('selectedSorteo') || '{}');
        const idSorteo = sorteoDataFromStorage.id_sorteo || sorteoDataFromStorage.id;
        
        if (!idSorteo) {
            const message = 'No hay sorteo seleccionado. Serás redirigido.';
            if (typeof customAlert === 'function') {
                customAlert(message, 'Sorteo No Encontrado', 'warning').then(() => {
                    window.location.href = 'ListadoSorteosActivos.php';
                });
            } else {
                alert(message);
                window.location.href = 'ListadoSorteosActivos.php';
            }
            return;
        }
        
        // 1. Cargar datos del sorteo desde API
        const sorteoResponse = await fetch(`../api/api_sorteos.php?action=get_details&id=${idSorteo}`);
        
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
        const boletosResponse = await fetch(`../api/api_boletos.php?action=get_my_assigned&id_sorteo=${idSorteo}`);
        
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
            const message = 'No tienes boletos asignados. Serás redirigido a la selección de boletos.';
            if (typeof customAlert === 'function') {
                customAlert(message, 'Sin Boletos', 'warning').then(() => {
                    window.location.href = 'SeleccionBoletos.php';
                });
            } else {
                alert(message);
                window.location.href = 'SeleccionBoletos.php';
            }
            return;
        }
        
        // Filtrar solo boletos en estado 'Reservado' para evitar duplicados o boletos ya comprados
        const boletosReservados = assignedBoletos.filter(b => b.estado === 'Reservado');
        
        if (boletosReservados.length === 0) {
            const message = 'No tienes boletos reservados disponibles. Serás redirigido a la selección de boletos.';
            if (typeof customAlert === 'function') {
                customAlert(message, 'Sin Boletos Reservados', 'warning').then(() => {
                    window.location.href = 'SeleccionBoletos.php';
                });
            } else {
                alert(message);
                window.location.href = 'SeleccionBoletos.php';
            }
            return;
        }
        
        // 3. Actualizar UI con datos reales (solo boletos reservados)
        updateUIWithRealData(sorteo, boletosReservados);
        
        // 4. Guardar datos en window para usar en handleFinalizarCompra (usar boletos reservados)
        window.currentSorteoData = sorteo;
        window.currentAssignedBoletos = boletosReservados;
        window.currentTicketPrice = parseFloat(sorteo.precio_boleto || 0);
        window.currentTicketCount = boletosReservados.length;
        
        // 5. Actualizar timer si hay tiempo restante (usar boletos reservados)
        if (boletosReservados.length > 0 && boletosReservados[0].tiempo_restante !== null) {
            const tiempoRestante = boletosReservados[0].tiempo_restante;
            if (tiempoRestante > 0) {
                startReservationTimerWithSeconds(tiempoRestante);
            }
        }
        
    } catch (error) {
        console.error('Error al cargar datos de pago:', error);
        const message = 'Error al cargar los datos: ' + error.message + '\n\nSerás redirigido a la página anterior.';
        if (typeof customAlert === 'function') {
            customAlert(message, 'Error de Carga', 'error').then(() => {
                window.location.href = 'SeleccionBoletos.php';
            });
        } else {
            alert(message);
            window.location.href = 'SeleccionBoletos.php';
        }
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
            const message = 'El tiempo de reserva ha expirado. Los boletos han sido liberados.';
            if (typeof customAlert === 'function') {
                customAlert(message, 'Tiempo Expirado', 'warning').then(() => {
                    window.location.href = 'ListadoSorteosActivos.php';
                });
            } else {
                alert(message);
                window.location.href = 'ListadoSorteosActivos.php';
            }
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

// Función para actualizar detalles según método de pago (definir ANTES de initPaymentPageFunctionality)
function updatePaymentDetails(method) {
    const container = document.getElementById('payment-details-container');
    if (!container) return;
    
    let content = '';
    
    switch(method) {
        case 'Pago Móvil':
            content = `
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-500">smartphone</span>
                        <span class="font-bold text-white">Pago Móvil</span>
                    </div>
                    <span class="bg-green-500/20 text-green-400 text-xs font-bold px-2 py-1 rounded">Activo</span>
                </div>
                <div class="grid md:grid-cols-2 gap-4 text-sm text-text-secondary">
                    <div>
                        <p class="mb-1 text-xs uppercase tracking-wider font-semibold text-gray-500">Banco</p>
                        <p class="text-white font-medium">Banco Nacional de Crédito (BNC)</p>
                    </div>
                    <div>
                        <p class="mb-1 text-xs uppercase tracking-wider font-semibold text-gray-500">Teléfono</p>
                        <div class="flex items-center gap-2">
                            <p class="text-white font-medium font-mono">0414-1234567</p>
                            <button type="button" class="text-primary hover:text-white" onclick="copyToClipboard('04141234567')">
                                <span class="material-symbols-outlined text-[16px]">content_copy</span>
                            </button>
                        </div>
                    </div>
                    <div>
                        <p class="mb-1 text-xs uppercase tracking-wider font-semibold text-gray-500">RIF / C.I.</p>
                        <p class="text-white font-medium">J-12345678-9</p>
                    </div>
                </div>
            `;
            break;
            
        case 'Zelle':
            content = `
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-2">
                        <span class="font-black text-[#5800d4] text-sm font-sans">Zelle®</span>
                        <span class="font-bold text-white">Pago en USD</span>
                    </div>
                    <span class="bg-purple-500/20 text-purple-400 text-xs font-bold px-2 py-1 rounded">Activo</span>
                </div>
                <div class="text-sm text-text-secondary">
                    <div class="mb-4">
                        <p class="mb-1 text-xs uppercase tracking-wider font-semibold text-gray-500">Correo Electrónico</p>
                        <div class="flex items-center gap-2">
                            <p class="text-white font-medium">pagos@sorteospremium.com</p>
                            <button type="button" class="text-primary hover:text-white" onclick="copyToClipboard('pagos@sorteospremium.com')">
                                <span class="material-symbols-outlined text-[16px]">content_copy</span>
                            </button>
                        </div>
                    </div>
                    <div>
                        <p class="mb-1 text-xs uppercase tracking-wider font-semibold text-gray-500">Titular de la cuenta</p>
                        <p class="text-white font-medium">Inversiones Sorteos Premium LLC</p>
                    </div>
                </div>
            `;
            break;
            
        case 'Binance':
            content = `
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-yellow-500">currency_bitcoin</span>
                        <span class="font-bold text-white">Binance Pay</span>
                    </div>
                    <span class="bg-yellow-500/20 text-yellow-500 text-xs font-bold px-2 py-1 rounded">Activo</span>
                </div>
                <div class="text-sm text-text-secondary">
                    <div class="mb-4">
                        <p class="mb-1 text-xs uppercase tracking-wider font-semibold text-gray-500">Binance ID / Pay ID</p>
                        <div class="flex items-center gap-2">
                            <p class="text-white font-medium font-mono">123456789</p>
                            <button type="button" class="text-primary hover:text-white" onclick="copyToClipboard('123456789')">
                                <span class="material-symbols-outlined text-[16px]">content_copy</span>
                            </button>
                        </div>
                    </div>
                    <div>
                        <p class="mb-1 text-xs uppercase tracking-wider font-semibold text-gray-500">Correo (Alternativo)</p>
                        <p class="text-white font-medium">binance@sorteospremium.com</p>
                    </div>
                </div>
            `;
            break;
            
        case 'Transferencia':
        default:
            content = `
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">account_balance</span>
                        <span class="font-bold text-white">Transferencia Bancaria</span>
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
                    <div class="col-span-2">
                        <p class="mb-1 text-xs uppercase tracking-wider font-semibold text-gray-500">Número de Cuenta</p>
                        <div class="flex items-center gap-2">
                            <p class="text-white font-medium tracking-wide font-mono">0191-0001-22-1234567890</p>
                            <button type="button" class="text-primary hover:text-white" onclick="copyToClipboard('01910001221234567890')">
                                <span class="material-symbols-outlined text-[16px]">content_copy</span>
                            </button>
                        </div>
                    </div>
                    <div>
                        <p class="mb-1 text-xs uppercase tracking-wider font-semibold text-gray-500">RIF</p>
                        <p class="text-white font-medium">J-12345678-9</p>
                    </div>
                </div>
            `;
            break;
    }
    
    container.innerHTML = content;
}

// Función auxiliar para copiar al portapapeles
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        if (typeof customAlert === 'function') {
            customAlert('Copiado al portapapeles', 'Copiado', 'success');
        } else {
            alert('Copiado: ' + text);
        }
    }).catch(err => {
        console.error('Error al copiar: ', err);
        // Fallback para navegadores antiguos
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.opacity = '0';
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            alert('Copiado: ' + text);
        } catch (e) {
            console.error('Error al copiar con fallback:', e);
        }
        document.body.removeChild(textArea);
    });
}

// Función para inicializar todas las funcionalidades de la página de pago
function initPaymentPageFunctionality() {
    // Upload de comprobante
    initComprobanteUpload();
    
    // Botón finalizar compra
    initFinalizarCompra();
    
    // Copiar información bancaria
    initCopyBankInfo();
    
    // Hacer funciones disponibles globalmente para onclick
    window.updatePaymentDetails = updatePaymentDetails;
    window.copyToClipboard = copyToClipboard;
    
    // Inicializar detalles de pago con el método seleccionado por defecto
    const metodoPagoDefault = document.querySelector('input[name="metodo_pago"]:checked');
    if (metodoPagoDefault) {
        updatePaymentDetails(metodoPagoDefault.value);
    } else {
        // Si no hay método seleccionado, usar Transferencia como default
        updatePaymentDetails('Transferencia');
    }
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
        const message = 'Por favor sube un archivo PNG, JPG o PDF.';
        if (typeof customAlert === 'function') {
            customAlert(message, 'Tipo de Archivo Inválido', 'error');
        } else {
            alert(message);
        }
        return;
    }
    
    // Validar tamaño (2MB)
    const maxSize = 2 * 1024 * 1024; // 2MB
    if (file.size > maxSize) {
        const message = 'El archivo no puede ser mayor a 2MB.';
        if (typeof customAlert === 'function') {
            customAlert(message, 'Archivo Demasiado Grande', 'error');
        } else {
            alert(message);
        }
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
            if (typeof customAlert === 'function') {
                customAlert('Por favor completa todos los campos de información de contacto.', 'Campos Incompletos', 'warning');
            } else {
                alert('Por favor completa todos los campos de información de contacto.');
            }
            return;
        }
        
        // Validar formato de email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            if (typeof customAlert === 'function') {
                customAlert('Por favor ingresa un correo electrónico válido.', 'Email Inválido', 'error');
            } else {
                alert('Por favor ingresa un correo electrónico válido.');
            }
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
                const message = 'Por favor sube el comprobante de pago antes de finalizar la compra.';
                if (typeof customAlert === 'function') {
                    customAlert(message, 'Comprobante Requerido', 'warning');
                } else {
                    alert(message);
                }
                return;
            }
            // Si hay nombre en localStorage pero no archivo, el archivo se perdió
            const message2 = 'El archivo del comprobante se perdió. Por favor vuelve a subirlo.';
            if (typeof customAlert === 'function') {
                customAlert(message2, 'Archivo Perdido', 'warning').then(() => {
                    resetComprobanteUpload();
                });
            } else {
                alert(message2);
                resetComprobanteUpload();
            }
            return;
        }
        
        // Validar datos del sorteo primero
        const sorteoData = window.currentSorteoData || JSON.parse(localStorage.getItem('selectedSorteo') || '{}');
        if (!sorteoData.id_sorteo && !sorteoData.id) {
            const message = 'No hay sorteo seleccionado. Serás redirigido.';
            if (typeof customAlert === 'function') {
                customAlert(message, 'Sorteo No Encontrado', 'warning').then(() => {
                    window.location.href = 'ListadoSorteosActivos.php';
                });
            } else {
                alert(message);
                window.location.href = 'ListadoSorteosActivos.php';
            }
            return;
        }
        
        const idSorteo = sorteoData.id_sorteo || sorteoData.id;
        
        // Obtener boletos desde la API en lugar de localStorage para asegurar datos actualizados
        let selectedTickets = [];
        try {
            // Intentar obtener desde window.currentAssignedBoletos (datos actualizados)
            if (window.currentAssignedBoletos && window.currentAssignedBoletos.length > 0) {
                // Filtrar solo boletos reservados
                const boletosReservados = window.currentAssignedBoletos.filter(b => b.estado === 'Reservado');
                selectedTickets = boletosReservados.map(b => b.numero_boleto_int || parseInt(b.numero_boleto));
            }
            
            // Si no hay boletos en window, obtener desde localStorage como fallback
            if (selectedTickets.length === 0) {
                selectedTickets = JSON.parse(localStorage.getItem('selectedTickets') || '[]');
            }
            
            // Si aún no hay boletos, intentar obtener desde la API
            if (selectedTickets.length === 0) {
                const boletosResponse = await fetch(`../api/api_boletos.php?action=get_my_assigned&id_sorteo=${idSorteo}`);
                if (boletosResponse.ok) {
                    const boletosText = await boletosResponse.text();
                    const boletosData = JSON.parse(boletosText);
                    if (boletosData.success && boletosData.data && boletosData.data.boletos) {
                        const boletosReservados = boletosData.data.boletos.filter(b => b.estado === 'Reservado');
                        selectedTickets = boletosReservados.map(b => b.numero_boleto_int || parseInt(b.numero_boleto));
                    }
                }
            }
        } catch (error) {
            console.error('Error al obtener boletos:', error);
            // Fallback a localStorage
            selectedTickets = JSON.parse(localStorage.getItem('selectedTickets') || '[]');
        }
        
        // Validar boletos
        if (selectedTickets.length === 0) {
            const message = 'No hay boletos seleccionados. Por favor vuelve a la página anterior.';
            if (typeof customAlert === 'function') {
                customAlert(message, 'Boletos Requeridos', 'warning').then(() => {
                    window.location.href = 'SeleccionBoletos.php';
                });
            } else {
                alert(message);
                window.location.href = 'SeleccionBoletos.php';
            }
            return;
        }
        const total = document.getElementById('total-amount')?.textContent || '$0.00';
        const montoTotal = parseFloat(total.replace('$', '').replace(',', ''));
        
        // Confirmar compra
        const confirmMessage = `¿Deseas finalizar la compra?\n\n` +
                              `Boletos: ${selectedTickets.length}\n` +
                              `Total: ${total}\n\n` +
                              `Asegúrate de haber realizado la transferencia bancaria y subido el comprobante.`;
        
        let confirmed = false;
        if (typeof customConfirm === 'function') {
            try {
                confirmed = await customConfirm(confirmMessage, 'Finalizar Compra', 'help');
            } catch (e) {
                console.warn('Error al usar customConfirm, usando confirm nativo:', e);
                confirmed = confirm(confirmMessage);
            }
        } else {
            confirmed = confirm(confirmMessage);
        }
        
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
            
            const uploadResponse = await fetch('../api/api_upload.php?action=upload_comprobante', {
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
            // Asegurarse de que selectedTickets contiene solo números enteros
            const numerosBoletos = selectedTickets.map(num => {
                const numInt = typeof num === 'string' ? parseInt(num) : num;
                return isNaN(numInt) ? null : numInt;
            }).filter(num => num !== null);
            
            if (numerosBoletos.length === 0) {
                throw new Error('No se pudieron validar los números de boletos');
            }
            
            // Obtener método de pago seleccionado
            const metodoPagoInput = document.querySelector('input[name="metodo_pago"]:checked');
            const metodoPago = metodoPagoInput ? metodoPagoInput.value : 'Transferencia';
            
            const transactionData = {
                id_sorteo: idSorteo,
                numeros_boletos: numerosBoletos, // Array de números de boletos (enteros)
                metodo_pago: metodoPago,
                referencia_pago: null,
                comprobante_url: comprobanteUrl,
                monto_total: montoTotal
            };
            
            // Si es Pasarela (Tarjeta), iniciar flujo especial
            if (transactionData.metodo_pago === 'Tarjeta') {
                // 1. Crear transacción como Pendiente primero
                const initResponse = await fetch('../api/api_transacciones.php?action=create', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(transactionData)
                });
                
                const initText = await initResponse.text();
                let initResult;
                try {
                    initResult = JSON.parse(initText);
                } catch(e) {
                    throw new Error("Error del servidor: " + initText);
                }

                if (!initResult.success) {
                   throw new Error(initResult.error || "Error al crear transacción inicial");
                }
                
                // 2. Redirigir a Pasarela (Simulada)
                const pasarelaResponse = await fetch('../api/api_pasarela.php?action=initiate_payment', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ id_transaccion: initResult.data.id_transaccion })
                });
                
                const pasarelaResult = await pasarelaResponse.json();
                
                if (pasarelaResult.success) {
                    // Redirección al "Banco"
                    window.location.href = pasarelaResult.redirect_url;
                    return; // Detener flujo aquí
                } else {
                     throw new Error("Error al conectar con pasarela: " + pasarelaResult.error);
                }
            }

            // Flujo normal para otros métodos
            console.log('Enviando transacción:', transactionData);
            
            const transactionResponse = await fetch('../api/api_transacciones.php?action=create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(transactionData)
            });
            
            // IMPORTANTE: Leemos el texto ANTES de verificar transactionResponse.ok
            // Esto es crucial para ver el error real si el servidor devuelve 500
            const transactionText = await transactionResponse.text();
            console.log('Respuesta de transacción (raw):', transactionText);
            
            if (!transactionResponse.ok) {
                // Intentar parsear el error del JSON
                try {
                    const errorJson = JSON.parse(transactionText);
                    throw new Error(errorJson.error || `Error HTTP: ${transactionResponse.status}`);
                } catch (e) {
                    // Si no es JSON válido, usar el texto o un mensaje genérico
                     throw new Error(`Error del servidor (${transactionResponse.status}): ${transactionText.substring(0, 100)}...`);
                }
            }
            
            // La respuesta ya fue leída en transactionText arriba
            let transactionResult;
            
            try {
                transactionResult = JSON.parse(transactionText);
            } catch (parseError) {
                console.error('Error al parsear JSON de transacción:', parseError);
                console.error('Respuesta recibida completa:', transactionText);
                // Si la respuesta no es JSON, puede ser un error de PHP
                if (transactionText.includes('Fatal error') || transactionText.includes('Warning') || transactionText.includes('Parse error')) {
                    throw new Error('Error en el servidor: ' + transactionText.substring(0, 300));
                }
                throw new Error('Error en la respuesta del servidor (formato inválido): ' + transactionText.substring(0, 200));
            }
            
            console.log('Respuesta de transacción (parseada):', transactionResult);
            
            if (!transactionResult.success || !transactionResult.data) {
                const errorMsg = transactionResult.error || transactionResult.message || 'Error al crear la transacción';
                console.error('Error en transacción:', errorMsg);
                console.error('Respuesta completa:', transactionResult);
                // Si el error menciona columna no encontrada, dar mensaje más claro
                if (errorMsg.includes('Columna no encontrada') || errorMsg.includes('42S22') || errorMsg.includes('id_transaccion_reserva')) {
                    throw new Error('Error de base de datos: El campo id_transaccion_reserva no existe en la tabla boletos. Por favor verifica la estructura de la base de datos.');
                }
                throw new Error(errorMsg);
            }
            
            console.log('Transacción creada exitosamente:', transactionResult.data);
            
            // 3. ÉXITO - Limpiar localStorage y redirigir
            localStorage.removeItem('comprobanteFileName');
            localStorage.removeItem('comprobanteFileSize');
            
            // Mostrar mensaje de éxito
            const successMessage = '¡Tu compra ha sido registrada exitosamente!\n\n' +
                'Tu transacción está siendo revisada por el administrador.\n' +
                'Recibirás un correo de confirmación cuando tu pago sea aprobado.\n\n' +
                'Puedes seguir el estado de tus boletos en la sección "Mis Boletos".';
            
            if (typeof customAlert === 'function') {
                await customAlert(successMessage, '¡Compra Exitosa!', 'success');
            } else {
                alert(successMessage);
            }
            
            // Redirigir a Mis Boletos
            window.location.href = 'MisBoletosCliente.php';
            
        } catch (error) {
            console.error('Error al procesar la compra:', error);
            const errorMessage = 'Error al procesar la compra: ' + error.message + '\n\n' +
                'Por favor, verifica tus datos e intenta nuevamente. Si el problema persiste, contacta al soporte.';
            
            if (typeof customAlert === 'function') {
                customAlert(errorMessage, 'Error en la Compra', 'error');
            } else {
                alert(errorMessage);
            }
        } finally {
            // Restaurar botón
            if (finalizarBtn && originalBtnText) {
                finalizarBtn.disabled = false;
                finalizarBtn.innerHTML = originalBtnText;
            }
        }
        
    } catch (error) {
        console.error('Error general en handleFinalizarCompra:', error);
        if (typeof customAlert === 'function') {
            customAlert('Error inesperado: ' + error.message, 'Error', 'error');
        } else {
            alert('Error inesperado: ' + error.message);
        }
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
