<!DOCTYPE html>
<?php
// Conexión a la base de datos
require_once 'config.php';
$conn = getDBConnection();
?>


<html class="dark" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Validación de Pagos Admin</title>
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&amp;display=swap" rel="stylesheet"/>
<!-- Material Symbols -->
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#2463eb",
                        "background-light": "#f6f6f8",
                        "background-dark": "#111621",
                        "card-dark": "#1e2433", // Slightly lighter than bg-dark for contrast
                        "border-dark": "#2a3241",
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
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Custom scrollbar for dark theme */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #111621; 
        }
        ::-webkit-scrollbar-thumb {
            background: #2a3241; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #3b4657; 
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white antialiased overflow-hidden">
<div class="flex h-screen w-full">
<!-- Sidebar -->
<aside id="sidebar" class="w-64 flex-shrink-0 flex flex-col border-r border-gray-200 dark:border-border-dark bg-white dark:bg-[#151a25] lg:translate-x-0 -translate-x-full lg:static fixed inset-y-0 left-0 z-30 transition-transform duration-300">
<!-- Mobile overlay -->
<div id="mobileOverlay" onclick="toggleMobileMenu()" class="hidden lg:hidden fixed inset-0 bg-black/50 z-20"></div>
<div class="h-16 flex items-center px-6 border-b border-gray-200 dark:border-border-dark">
<div class="flex items-center gap-2 text-primary">
<span class="material-symbols-outlined text-3xl">confirmation_number</span>
<span class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">Sorteos<span class="text-primary">Admin</span></span>
</div>
</div>
<div class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
<p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-2">Principal</p>
<a class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="DashboardAdmnistrador.php" data-page="DashboardAdmnistrador.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">dashboard</span>
                    Dashboard
                </a>
<a class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="CrudGestionSorteo.php" data-page="CrudGestionSorteo.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">confirmation_number</span>
                    Gestión de Sorteos
                </a>
<a class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="ValidacionPagosAdministrador.php" data-page="ValidacionPagosAdministrador.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">payments</span>
                    Validación de Pagos
                    <span class="ml-auto bg-yellow-500/20 text-yellow-500 text-xs font-bold px-2 py-0.5 rounded-full">3</span>
</a>
<a class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="GeneradorGanadoresAdminstradores.php" data-page="GeneradorGanadoresAdminstradores.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">emoji_events</span>
                    Generación de Ganadores
                </a>
<p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-6">Administración</p>
<a class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="GestionUsuariosAdministrador.php" data-page="GestionUsuariosAdministrador.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">group</span>
                    Usuarios
                </a>
<a class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="AuditoriaAccionesAdmin.php" data-page="AuditoriaAccionesAdmin.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">settings</span>
                    Auditoría
                </a>
<a class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="InformesEstadisticasAdmin.php" data-page="InformesEstadisticasAdmin.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">analytics</span>
                    Informes
                </a>
</div>
<div class="p-4 border-t border-gray-200 dark:border-border-dark">
<div class="flex items-center gap-3 mb-3">
<div class="w-10 h-10 rounded-full bg-cover bg-center" data-alt="User profile picture" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAfIzDdUJZk0e1bBHKOe7BG0HPanJ3nx8d9vtsJZZMiXM6ZJw9-oPch2DQWyWWrowTikKHJBUkhOyI6hUEiy_TgTGdRmm-4uDyO3KjasL500lcWogtry5HOXaJxBgDxpuT_8QBEVTnbuI4727c7c5qtPNid2CyQr0SnpyEcv2R9UEoiXiOVUH_g0RdYwYfb9u5EU5DkqEZl2oL9UW9s45D-zD3htPmEHk69TrCVPL50vnE6cDfTlcz9AJEZo7Hb8gpAhxwAxDP4SCs');"></div>
<div class="flex flex-col">
<span class="text-sm font-medium text-slate-900 dark:text-white">Admin User</span>
<span class="text-xs text-gray-500">admin@sorteos.web</span>
</div>
</div>
<button id="logout-btn-admin" onclick="handleLogoutAdmin()" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors text-sm font-medium">
<span class="material-symbols-outlined text-[20px]">logout</span>
Cerrar Sesión
</button>
</div>
</aside>
<!-- Main Content -->
<main class="flex-1 flex flex-col h-full overflow-hidden bg-background-light dark:bg-background-dark relative">
<!-- Header -->
<header class="h-16 flex items-center justify-between px-6 border-b border-gray-200 dark:border-border-dark bg-white dark:bg-[#151a25]/80 backdrop-blur-md sticky top-0 z-20">
<div class="flex items-center gap-4">
<!-- Mobile menu trigger (hidden on desktop) -->
<button id="mobileMenuToggle" onclick="toggleMobileMenu()" class="lg:hidden text-gray-500 hover:text-primary transition-colors">
<span class="material-symbols-outlined">menu</span>
</button>
<h1 class="text-xl font-bold text-slate-900 dark:text-white hidden sm:block">Validación de Pagos</h1>
</div>
<div class="flex items-center gap-4">
<div class="relative hidden md:block w-64">
<span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
<span class="material-symbols-outlined text-[20px]">search</span>
</span>
<input id="headerSearchInput" class="w-full bg-gray-100 dark:bg-[#1e2433] border-none rounded-lg py-2 pl-10 pr-4 text-sm focus:ring-2 focus:ring-primary placeholder-gray-500 dark:placeholder-gray-400" placeholder="Buscar sorteo, usuario..." type="text" style="color: rgb(15 23 42);"/>
</div>
<button class="relative p-2 text-gray-500 hover:text-primary transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-white/5">
<span class="material-symbols-outlined">notifications</span>
<span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
</button>
</div>
</header>
<!-- Scrollable Content -->
<div class="flex-1 overflow-y-auto p-6 space-y-6">
<div class="max-w-[1400px] mx-auto w-full">
<!-- Breadcrumbs -->
<div class="flex flex-wrap items-center gap-2 px-4 py-2">
<button onclick="navegarAtras()" class="text-[#9da6b9] hover:text-white transition-colors text-sm font-medium leading-normal flex items-center gap-1" title="Volver">
<span class="material-symbols-outlined !text-lg">arrow_back</span>
                            Atrás
                        </button>
<span class="text-[#9da6b9] text-sm font-medium leading-normal">|</span>
<a class="text-[#9da6b9] hover:text-white transition-colors text-sm font-medium leading-normal flex items-center gap-1" href="DashboardAdmnistrador.php">
<span class="material-symbols-outlined !text-lg">dashboard</span>
                            Dashboard
                        </a>
<span class="text-[#9da6b9] text-sm font-medium leading-normal">/</span>
<span class="text-white text-sm font-medium leading-normal">Validación de Pagos</span>
</div>
<!-- Page Heading -->
<div class="flex flex-wrap justify-between items-end gap-4 px-4 py-6">
<div class="flex min-w-72 flex-col gap-2">
<h1 class="text-white text-3xl md:text-4xl font-black leading-tight tracking-[-0.033em]">Validación de Pagos</h1>
<p class="text-[#9da6b9] text-base font-normal leading-normal">Revisa y valida los comprobantes de pago pendientes de los usuarios.</p>
</div>
<div class="flex gap-3">
<button id="aprobarSeleccionadosBtn" onclick="aprobarSeleccionados()" class="flex items-center gap-2 bg-green-500/10 hover:bg-green-500/20 text-green-400 px-4 py-2.5 rounded-lg font-medium text-sm transition-colors border border-green-500/20 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
<span class="material-symbols-outlined !text-xl">check_circle</span>
                                <span>Aprobar Seleccionados</span>
                                <span id="contadorAprobar" class="hidden bg-green-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">0</span>
                            </button>
<button id="rechazarSeleccionadosBtn" onclick="rechazarSeleccionados()" class="flex items-center gap-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 px-4 py-2.5 rounded-lg font-medium text-sm transition-colors border border-red-500/20 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
<span class="material-symbols-outlined !text-xl">cancel</span>
                                <span>Rechazar Seleccionados</span>
                                <span id="contadorRechazar" class="hidden bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">0</span>
                            </button>
<button onclick="exportarPagosCSV()" class="flex items-center gap-2 bg-primary/10 hover:bg-primary/20 text-primary px-4 py-2.5 rounded-lg font-medium text-sm transition-colors border border-primary/20">
<span class="material-symbols-outlined !text-xl">download</span>
                                Exportar CSV
                            </button>
</div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 px-4 mb-6">
<!-- Pendientes -->
<div class="bg-[#1e2433] rounded-xl p-5 border border-border-dark hover:border-yellow-500/30 transition-colors">
<div class="flex items-center justify-between">
<div>
<p class="text-[#9da6b9] text-sm font-medium">Pagos Pendientes</p>
<p id="contadorPendientes" class="text-3xl font-bold text-yellow-400 mt-1">12</p>
</div>
<div class="w-12 h-12 rounded-xl bg-yellow-500/10 flex items-center justify-center">
<span class="material-symbols-outlined text-yellow-400 !text-2xl">hourglass_top</span>
</div>
</div>
<p class="text-xs text-[#9da6b9] mt-3 flex items-center gap-1">
<span class="material-symbols-outlined !text-sm text-yellow-400">schedule</span>
                                Requieren revisión
                            </p>
</div>
<!-- Aprobados Hoy -->
<div class="bg-[#1e2433] rounded-xl p-5 border border-border-dark hover:border-green-500/30 transition-colors">
<div class="flex items-center justify-between">
<div>
<p class="text-[#9da6b9] text-sm font-medium">Aprobados Hoy</p>
<p id="contadorAprobados" class="text-3xl font-bold text-green-400 mt-1">8</p>
</div>
<div class="w-12 h-12 rounded-xl bg-green-500/10 flex items-center justify-center">
<span class="material-symbols-outlined text-green-400 !text-2xl">task_alt</span>
</div>
</div>
<p class="text-xs text-[#9da6b9] mt-3 flex items-center gap-1">
<span class="material-symbols-outlined !text-sm text-green-400">trending_up</span>
                                +25% vs ayer
                            </p>
</div>
<!-- Rechazados -->
<div class="bg-[#1e2433] rounded-xl p-5 border border-border-dark hover:border-red-500/30 transition-colors">
<div class="flex items-center justify-between">
<div>
<p class="text-[#9da6b9] text-sm font-medium">Rechazados Hoy</p>
<p id="contadorRechazados" class="text-3xl font-bold text-red-400 mt-1">2</p>
</div>
<div class="w-12 h-12 rounded-xl bg-red-500/10 flex items-center justify-center">
<span class="material-symbols-outlined text-red-400 !text-2xl">block</span>
</div>
</div>
<p class="text-xs text-[#9da6b9] mt-3 flex items-center gap-1">
<span class="material-symbols-outlined !text-sm text-red-400">info</span>
                                Comprobantes inválidos
                            </p>
</div>
<!-- Monto Total -->
<div class="bg-[#1e2433] rounded-xl p-5 border border-border-dark hover:border-primary/30 transition-colors">
<div class="flex items-center justify-between">
<div>
<p class="text-[#9da6b9] text-sm font-medium">Monto Pendiente</p>
<p id="montoPendiente" class="text-3xl font-bold text-primary mt-1">$2,450</p>
</div>
<div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
<span class="material-symbols-outlined text-primary !text-2xl">payments</span>
</div>
</div>
<p id="transaccionesPendientes" class="text-xs text-[#9da6b9] mt-3 flex items-center gap-1">
<span class="material-symbols-outlined !text-sm text-primary">account_balance_wallet</span>
                                En 12 transacciones
                            </p>
</div>
</div>

<!-- Filters & Search Toolbar -->
<div class="flex flex-col md:flex-row gap-4 px-4 py-4 bg-[#1e2433]/50 rounded-xl mb-4 border border-border-dark mx-4">
<div class="relative md:max-w-md flex-1">
<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
<span class="material-symbols-outlined text-[#9da6b9]">search</span>
</div>
<input id="searchInput" class="w-full bg-background-dark border border-[#3b4354] rounded-lg pl-10 pr-4 py-2.5 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary placeholder-[#9da6b9] text-sm transition-all" placeholder="Buscar por usuario, sorteo o referencia de pago..." style="color: rgb(255 255 255) !important;"/>
</div>
<div class="flex gap-4 w-full md:w-auto flex-wrap">
<div class="relative min-w-[180px] flex-1 md:flex-none">
<select id="statusFilter" onchange="aplicarFiltros()" class="w-full bg-background-dark border border-[#3b4354] rounded-lg pl-4 pr-10 py-2.5 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm appearance-none cursor-pointer" style="color: rgb(255 255 255) !important;">
<option value="all" style="color: rgb(255 255 255); background-color: rgb(17 22 33);">Estado: Todos</option>
<option value="pending" selected style="color: rgb(255 255 255); background-color: rgb(17 22 33);">Pendiente</option>
<option value="approved" style="color: rgb(255 255 255); background-color: rgb(17 22 33);">Aprobado</option>
<option value="rejected" style="color: rgb(255 255 255); background-color: rgb(17 22 33);">Rechazado</option>
</select>
<div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#9da6b9] z-0">
<span class="material-symbols-outlined !text-lg">expand_more</span>
</div>
</div>
<div class="relative min-w-[180px] flex-1 md:flex-none">
<select id="methodFilter" onchange="aplicarFiltros()" class="w-full bg-background-dark border border-[#3b4354] rounded-lg pl-4 pr-10 py-2.5 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm appearance-none cursor-pointer" style="color: rgb(255 255 255) !important;">
<option value="all" style="color: rgb(255 255 255); background-color: rgb(17 22 33);">Método de Pago</option>
<option value="transfer" style="color: rgb(255 255 255); background-color: rgb(17 22 33);">Transferencia</option>
<option value="deposit" style="color: rgb(255 255 255); background-color: rgb(17 22 33);">Depósito</option>
<option value="paypal" style="color: rgb(255 255 255); background-color: rgb(17 22 33);">PayPal</option>
<option value="crypto" style="color: rgb(255 255 255); background-color: rgb(17 22 33);">Criptomoneda</option>
</select>
<div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#9da6b9] z-0">
<span class="material-symbols-outlined !text-lg">account_balance</span>
</div>
</div>
<div class="relative min-w-[180px] flex-1 md:flex-none">
<select id="sortFilter" onchange="aplicarFiltros()" class="w-full bg-background-dark border border-[#3b4354] rounded-lg pl-4 pr-10 py-2.5 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm appearance-none cursor-pointer" style="color: rgb(255 255 255) !important;">
<option value="recent" style="color: rgb(255 255 255); background-color: rgb(17 22 33);">Ordenar: Más recientes</option>
<option value="oldest" style="color: rgb(255 255 255); background-color: rgb(17 22 33);">Más antiguos</option>
<option value="amount_high" style="color: rgb(255 255 255); background-color: rgb(17 22 33);">Mayor monto</option>
<option value="amount_low" style="color: rgb(255 255 255); background-color: rgb(17 22 33);">Menor monto</option>
</select>
<div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#9da6b9] z-0">
<span class="material-symbols-outlined !text-lg">sort</span>
</div>
</div>
</div>
</div>

<!-- Data Table -->
<div class="px-4 py-2">
<div class="overflow-x-auto rounded-xl border border-border-dark bg-background-dark shadow-xl shadow-black/20">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-[#1e2433] border-b border-border-dark">
<th class="p-4 text-xs font-medium uppercase tracking-wider text-[#9da6b9] w-[50px] text-center">
<input id="selectAllCheckbox" onclick="toggleSelectAll()" class="rounded bg-background-dark border-[#3b4354] text-primary focus:ring-offset-background-dark focus:ring-primary" type="checkbox"/>
</th>
<th class="p-4 text-xs font-medium uppercase tracking-wider text-[#9da6b9]">Usuario</th>
<th class="p-4 text-xs font-medium uppercase tracking-wider text-[#9da6b9]">Sorteo</th>
<th class="p-4 text-xs font-medium uppercase tracking-wider text-[#9da6b9]">Monto</th>
<th class="p-4 text-xs font-medium uppercase tracking-wider text-[#9da6b9]">Método</th>
<th class="p-4 text-xs font-medium uppercase tracking-wider text-[#9da6b9]">Comprobante</th>
<th class="p-4 text-xs font-medium uppercase tracking-wider text-[#9da6b9]">Fecha</th>
<th class="p-4 text-xs font-medium uppercase tracking-wider text-[#9da6b9]">Estado</th>
<th class="p-4 text-xs font-medium uppercase tracking-wider text-[#9da6b9] text-center">Acciones</th>
</tr>
</thead>
<tbody id="pagosTableBody" class="divide-y divide-border-dark">
<!-- Los pagos se cargarán dinámicamente -->
</tbody>
</table>
</div>
</div>
<!-- Pagination -->
<div class="px-4 py-4 flex flex-col sm:flex-row items-center justify-between gap-4 mt-2">
<p class="text-sm text-[#9da6b9]">
                            Mostrando <span id="paginationStart" class="font-medium text-white">1</span> a <span id="paginationEnd" class="font-medium text-white">5</span> de <span id="paginationTotal" class="font-medium text-white">22</span> pagos
                        </p>
<nav aria-label="Pagination" class="isolate inline-flex -space-x-px rounded-md shadow-sm">
<a id="prevPageBtn" onclick="cambiarPagina('prev')" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-[#9da6b9] ring-1 ring-inset ring-[#3b4354] hover:bg-[#1e2433] focus:z-20 focus:outline-offset-0 transition-colors cursor-pointer">
<span class="sr-only">Anterior</span>
<span class="material-symbols-outlined !text-sm">chevron_left</span>
</a>
<a id="page1" onclick="cambiarPagina(1)" aria-current="page" class="relative z-10 inline-flex items-center bg-primary px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary cursor-pointer">1</a>
<a id="page2" onclick="cambiarPagina(2)" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-[#9da6b9] ring-1 ring-inset ring-[#3b4354] hover:bg-[#1e2433] focus:z-20 focus:outline-offset-0 transition-colors cursor-pointer">2</a>
<a id="page3" onclick="cambiarPagina(3)" class="relative hidden items-center px-4 py-2 text-sm font-semibold text-[#9da6b9] ring-1 ring-inset ring-[#3b4354] hover:bg-[#1e2433] focus:z-20 focus:outline-offset-0 md:inline-flex transition-colors cursor-pointer">3</a>
<span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-[#9da6b9] ring-1 ring-inset ring-[#3b4354] focus:outline-offset-0">...</span>
<a id="page5" onclick="cambiarPagina(5)" class="relative hidden items-center px-4 py-2 text-sm font-semibold text-[#9da6b9] ring-1 ring-inset ring-[#3b4354] hover:bg-[#1e2433] focus:z-20 focus:outline-offset-0 md:inline-flex transition-colors cursor-pointer">5</a>
<a id="nextPageBtn" onclick="cambiarPagina('next')" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-[#9da6b9] ring-1 ring-inset ring-[#3b4354] hover:bg-[#1e2433] focus:z-20 focus:outline-offset-0 transition-colors cursor-pointer">
<span class="sr-only">Siguiente</span>
<span class="material-symbols-outlined !text-sm">chevron_right</span>
</a>
</nav>
</div>
</div>
</div>
</main>
</div>
<script>
// Estado global
let currentPage = 1;
let itemsPerPage = 10;
let currentStatusFilter = 'pending';
let currentMethodFilter = 'all';
let currentSortFilter = 'recent';
let searchQuery = '';
let allPagos = []; 
let filteredPagos = [];

// ========== API CALLS ==========

async function loadPagos() {
    const tbody = document.getElementById('pagosTableBody');
    tbody.innerHTML = '<tr><td colspan="9" class="text-center py-8 text-gray-500"><span class="material-symbols-outlined animate-spin mr-2">autorenew</span>Cargando pagos...</td></tr>';
    
    try {
        const response = await fetch('api_pagos.php?action=list');
        const result = await response.json();
        
        if (result.success) {
            allPagos = result.data;
            aplicarFiltros();
            actualizarEstadisticas();
        } else {
            console.error('Error cargando pagos:', result.error);
            tbody.innerHTML = '<tr><td colspan="9" class="text-center py-8 text-red-500">Error al cargar pagos: ' + result.error + '</td></tr>';
        }
    } catch (error) {
        console.error('Error de red:', error);
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-8 text-red-500">Error de conexión al cargar pagos</td></tr>';
    }
}

async function actualizarEstadisticas() {
    try {
        const response = await fetch('api_pagos.php?action=stats');
        const result = await response.json();
        
        if (result.success) {
            const stats = result.data;
            // Actualizar contadores del dashboard
            if(document.getElementById('contadorPendientes')) 
                document.getElementById('contadorPendientes').textContent = stats.pendientes;
            if(document.getElementById('contadorAprobados')) 
                document.getElementById('contadorAprobados').textContent = stats.aprobados_hoy;
            if(document.getElementById('contadorRechazados')) 
                document.getElementById('contadorRechazados').textContent = stats.rechazados_hoy;
            if(document.getElementById('montoPendiente')) 
                document.getElementById('montoPendiente').textContent = '$' + parseFloat(stats.monto_pendiente).toLocaleString('en-US', {minimumFractionDigits: 2});
             if(document.getElementById('transaccionesPendientes')) 
                document.getElementById('transaccionesPendientes').innerHTML = `<span class="material-symbols-outlined !text-sm text-primary">account_balance_wallet</span> En ${stats.pendientes} transacciones`;
        }
    } catch (e) {
        console.error('Error cargando estadísticas:', e);
    }
}


function guardarFiltros() {
    try {
        const filtros = {
            status: currentStatusFilter,
            method: currentMethodFilter,
            sort: currentSortFilter,
            search: searchQuery,
            timestamp: Date.now()
        };
        localStorage.setItem('filtrosValidacionPagos', JSON.stringify(filtros));
    } catch (error) {
        console.warn('No se pudieron guardar los filtros:', error);
    }
}

function cargarFiltros() {
    try {
        const filtrosGuardados = localStorage.getItem('filtrosValidacionPagos');
        if (filtrosGuardados) {
            const filtros = JSON.parse(filtrosGuardados);
            if (filtros.status) {
                currentStatusFilter = filtros.status;
                const statusFilter = document.getElementById('statusFilter');
                if (statusFilter) statusFilter.value = filtros.status;
            }
            if (filtros.method) {
                currentMethodFilter = filtros.method;
                const methodFilter = document.getElementById('methodFilter');
                if (methodFilter) methodFilter.value = filtros.method;
            }
            if (filtros.sort) {
                currentSortFilter = filtros.sort;
                const sortFilter = document.getElementById('sortFilter');
                if (sortFilter) sortFilter.value = filtros.sort;
            }
            if (filtros.search) {
                searchQuery = filtros.search;
                const searchInput = document.getElementById('searchInput');
                if (searchInput) searchInput.value = filtros.search;
            }
        }
    } catch (error) {
        console.warn('No se pudieron cargar los filtros:', error);
    }
}

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    initMobileMenu();
    cargarFiltros();
    loadPagos();
    
    const searchInput = document.getElementById('searchInput');
    const headerSearchInput = document.getElementById('headerSearchInput');
    
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            searchQuery = e.target.value.toLowerCase().trim();
            aplicarFiltros();
            guardarFiltros();
        });
    }
    
    if (headerSearchInput) {
        headerSearchInput.addEventListener('input', function(e) {
            searchQuery = e.target.value.toLowerCase().trim();
            if (searchInput) searchInput.value = e.target.value;
            aplicarFiltros();
            guardarFiltros();
        });
    }
});


function renderTabla(pagos) {
    const tbody = document.getElementById('pagosTableBody');
    
    if (pagos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-8 text-gray-500">No se encontraron pagos</td></tr>';
        return;
    }
    
    const start = (currentPage - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const pagePagos = pagos.slice(start, end);
    
    tbody.innerHTML = pagePagos.map(pago => {
        let rowClass = 'hover:bg-[#1e2433]/50 transition-colors group';
        let statusBadge = '';
        
        switch(pago.estado) {
            case 'approved':
                rowClass += ' bg-green-500/5';
                statusBadge = `<span class="inline-flex items-center gap-1.5 rounded-full bg-green-500/10 px-2.5 py-1 text-xs font-medium text-green-400 border border-green-500/20"><span class="h-1.5 w-1.5 rounded-full bg-green-400"></span>Aprobado</span>`;
                break;
            case 'rejected':
                rowClass += ' bg-red-500/5';
                statusBadge = `<span class="inline-flex items-center gap-1.5 rounded-full bg-red-500/10 px-2.5 py-1 text-xs font-medium text-red-400 border border-red-500/20"><span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>Rechazado</span>`;
                break;
            default: // pending
                statusBadge = `<span class="inline-flex items-center gap-1.5 rounded-full bg-yellow-500/10 px-2.5 py-1 text-xs font-medium text-yellow-400 border border-yellow-500/20"><span class="h-1.5 w-1.5 rounded-full bg-yellow-400 animate-pulse"></span>Pendiente</span>`;
                break;
        }

        let metodoIcon = 'payments';
        let metodoColor = 'text-gray-400';
        if (pago.metodo.includes('transfer')) { metodoIcon = 'account_balance'; metodoColor = 'text-primary'; }
        else if (pago.metodo.includes('paypal')) { metodoIcon = 'credit_card'; metodoColor = 'text-blue-400'; }
        else if (pago.metodo.includes('crypto')) { metodoIcon = 'currency_bitcoin'; metodoColor = 'text-orange-400'; }
        else if (pago.metodo.includes('deposit')) { metodoIcon = 'savings'; metodoColor = 'text-green-400'; }

        let acciones = `<div class="flex items-center justify-center gap-1">`;
        if (pago.estado === 'pending') {
            acciones += `
                <button onclick="aprobarPago('${pago.id}', '${pago.usuario}')" class="p-2 text-green-400 hover:bg-green-400/10 rounded-lg transition-colors" title="Aprobar pago"><span class="material-symbols-outlined !text-xl">check_circle</span></button>
                <button onclick="rechazarPago('${pago.id}', '${pago.usuario}')" class="p-2 text-red-400 hover:bg-red-400/10 rounded-lg transition-colors" title="Rechazar pago"><span class="material-symbols-outlined !text-xl">cancel</span></button>
            `;
        }
        acciones += `
            <button onclick="verDetallesPago('${pago.id}')" class="p-2 text-[#9da6b9] hover:text-white hover:bg-[#1e2433] rounded-lg transition-colors" title="Ver detalles"><span class="material-symbols-outlined !text-xl">visibility</span></button>
        </div>`;

        return `
        <tr class="pago-row ${rowClass}" data-id="${pago.id}" data-estado="${pago.estado}">
            <td class="p-4 text-center">
                <input class="pago-checkbox rounded bg-background-dark border-[#3b4354] text-primary focus:ring-offset-background-dark focus:ring-primary" type="checkbox" data-pago-id="${pago.id}" onchange="actualizarSeleccion()"/>
            </td>
            <td class="p-4">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-cover bg-center border border-border-dark" style='background-image: url("${pago.avatar || 'https://via.placeholder.com/40'}");'></div>
                    <div class="flex flex-col">
                        <span class="text-white text-sm font-semibold">${pago.usuario}</span>
                        <span class="text-[#9da6b9] text-xs">${pago.email}</span>
                    </div>
                </div>
            </td>
            <td class="p-4">
                <div class="flex flex-col">
                    <span class="text-white text-sm font-medium">${pago.sorteo}</span>
                    <span class="text-[#9da6b9] text-xs">${pago.cantidad_boletos} boleto(s)</span>
                </div>
            </td>
            <td class="p-4">
                <span class="text-white text-sm font-bold">$${parseFloat(pago.monto).toFixed(2)}</span>
            </td>
            <td class="p-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined ${metodoColor} !text-lg">${metodoIcon}</span>
                    <span class="text-[#9da6b9] text-sm capitalize">${pago.metodo}</span>
                </div>
            </td>
            <td class="p-4">
                ${pago.comprobante_url ? 
                `<button onclick="verComprobante('${pago.id}', '${pago.usuario}')" class="flex items-center gap-2 text-primary hover:text-primary/80 text-sm font-medium transition-colors">
                    <span class="material-symbols-outlined !text-lg">image</span> Ver comprobante
                </button>` : 
                `<span class="text-gray-500 text-xs">Sin comprobante</span>`}
            </td>
            <td class="p-4">
                <div class="flex flex-col">
                    <span class="text-white text-sm">${new Date(pago.fecha).toLocaleDateString()}</span>
                    <span class="text-[#9da6b9] text-xs">${new Date(pago.fecha).toLocaleTimeString().slice(0,5)}</span>
                </div>
            </td>
            <td class="p-4">${statusBadge}</td>
            <td class="p-4">${acciones}</td>
        </tr>
        `;
    }).join('');
    
    actualizarSeleccion(); 
}


function aplicarFiltros() {
    const statusFilter = document.getElementById('statusFilter');
    const methodFilter = document.getElementById('methodFilter');
    const sortFilter = document.getElementById('sortFilter');
    
    if (statusFilter) currentStatusFilter = statusFilter.value;
    if (methodFilter) currentMethodFilter = methodFilter.value;
    if (sortFilter) currentSortFilter = sortFilter.value;
    
    filteredPagos = allPagos.filter(pago => {
        if (currentStatusFilter !== 'all' && pago.estado !== currentStatusFilter) return false;
        if (currentMethodFilter !== 'all') {
             if (!pago.metodo.includes(currentMethodFilter)) return false; 
        }
        if (searchQuery) {
            const searchStr = (pago.usuario + pago.sorteo + pago.referencia).toLowerCase();
            if (!searchStr.includes(searchQuery)) return false;
        }
        return true;
    });
    
    filteredPagos.sort((a, b) => {
        if (currentSortFilter === 'amount_high') return parseFloat(b.monto) - parseFloat(a.monto);
        if (currentSortFilter === 'amount_low') return parseFloat(a.monto) - parseFloat(b.monto);
        if (currentSortFilter === 'oldest') return new Date(a.fecha) - new Date(b.fecha);
        return new Date(b.fecha) - new Date(a.fecha); // recent
    });
    
    currentPage = 1;
    actualizarPaginacion();
    renderTabla(filteredPagos);
    guardarFiltros();
}


function cambiarPagina(direccion) {
    const totalPages = Math.ceil(filteredPagos.length / itemsPerPage);
    if (direccion === 'prev' && currentPage > 1) currentPage--;
    else if (direccion === 'next' && currentPage < totalPages) currentPage++;
    else if (typeof direccion === 'number') currentPage = direccion;
    
    actualizarPaginacion();
    renderTabla(filteredPagos);
}

function actualizarPaginacion() {
    const total = filteredPagos.length;
    const totalPages = Math.ceil(total / itemsPerPage);
    const start = ((currentPage - 1) * itemsPerPage) + 1;
    const end = Math.min(currentPage * itemsPerPage, total);
    
    const paginationStart = document.getElementById('paginationStart');
    const paginationEnd = document.getElementById('paginationEnd');
    const paginationTotal = document.getElementById('paginationTotal');
    
    if (paginationStart) paginationStart.textContent = total > 0 ? start : 0;
    if (paginationEnd) paginationEnd.textContent = end;
    if (paginationTotal) paginationTotal.textContent = total;
    
    const prevBtn = document.getElementById('prevPageBtn');
    const nextBtn = document.getElementById('nextPageBtn');
    
    if (prevBtn) {
        prevBtn.classList.toggle('opacity-50', currentPage <= 1);
        prevBtn.style.pointerEvents = currentPage <= 1 ? 'none' : 'auto';
    }
    if (nextBtn) {
        nextBtn.classList.toggle('opacity-50', currentPage >= totalPages);
        nextBtn.style.pointerEvents = currentPage >= totalPages ? 'none' : 'auto';
    }
}


async function aprobarPago(id, usuario) {
    const confirmado = await mostrarModalConfirmacion(
        `¿Deseas aprobar el pago de ${usuario}?`,
        'Confirmar aprobación',
        'info'
    );
    
    if (!confirmado) return;
    
    try {
        const response = await fetch('api_pagos.php?action=approve', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id_transaccion: id })
        });
        const result = await response.json();
        
        if (result.success) {
            mostrarNotificacion('Pago aprobado exitosamente', 'success');
            loadPagos(); 
        } else {
            mostrarNotificacion(result.error, 'error');
        }
    } catch (error) {
        mostrarNotificacion('Error al aprobar pago', 'error');
    }
}

async function rechazarPago(id, usuario) {
    const motivo = await mostrarModalInput(
        `¿Por qué deseas rechazar el pago de ${usuario}?`,
        'Motivo del rechazo',
        'Ingresa el motivo del rechazo...',
        ''
    );
    
    if (!motivo) return;
    
    try {
        const response = await fetch('api_pagos.php?action=reject', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id_transaccion: id, motivo: motivo })
        });
        const result = await response.json();
        
        if (result.success) {
            mostrarNotificacion('Pago rechazado exitosamente', 'success');
            loadPagos(); 
        } else {
            mostrarNotificacion(result.error, 'error');
        }
    } catch (error) {
        mostrarNotificacion('Error al rechazar pago', 'error');
    }
}

function verDetallesPago(id) {
    const pago = allPagos.find(p => p.id == id);
    if (!pago) return;
    
    const contenido = `
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div><p class="text-sm text-gray-400">Usuario:</p><p class="font-medium text-white">${pago.usuario}</p></div>
                <div><p class="text-sm text-gray-400">Sorteo:</p><p class="font-medium text-white">${pago.sorteo}</p></div>
                <div><p class="text-sm text-gray-400">Monto:</p><p class="font-bold text-white">$${parseFloat(pago.monto).toFixed(2)}</p></div>
                <div><p class="text-sm text-gray-400">Método:</p><p class="text-white capitalize">${pago.metodo}</p></div>
                <div><p class="text-sm text-gray-400">Referencia:</p><p class="font-mono text-white">${pago.referencia}</p></div>
                <div><p class="text-sm text-gray-400">Fecha:</p><p class="text-white">${new Date(pago.fecha).toLocaleString()}</p></div>
                <div><p class="text-sm text-gray-400">Estado:</p><p class="text-white capitalize">${pago.estado}</p></div>
            </div>
            ${pago.numeros_boletos ? `<div><p class="text-sm text-gray-400">Boletos:</p><p class="text-white font-mono text-xs break-all">${pago.numeros_boletos}</p></div>` : ''}
        </div>
    `;
    mostrarModal('Detalles del Pago', contenido);
}

function verComprobante(id, usuario) {
    const pago = allPagos.find(p => p.id == id);
    if (!pago || !pago.comprobante_url) {
        mostrarNotificacion('No hay comprobante disponible', 'error');
        return;
    }
    
    const contenido = `
        <div class="space-y-4">
            <div class="bg-gray-800 rounded-lg p-2">
                <img src="${pago.comprobante_url}" class="w-full h-auto rounded" alt="Comprobante">
            </div>
             <div class="flex gap-3 mt-6">
                <a href="${pago.comprobante_url}" target="_blank" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-600 transition-colors text-center">
                    Ver Original
                </a>
            </div>
        </div>
    `;
    mostrarModal('Comprobante de Pago', contenido);
}

async function aprobarSeleccionados() {
    const checkboxes = document.querySelectorAll('.pago-checkbox:checked');
    if (checkboxes.length === 0) return;
    
    const confirmado = await mostrarModalConfirmacion(`¿Aprobar ${checkboxes.length} pagos?`, 'Confirmar');
    if (!confirmado) return;
    
    const btn = document.getElementById('aprobarSeleccionadosBtn');
    btn.disabled = true;
    btn.innerHTML = 'Procesando...';

    let errors = 0;
    for (const cb of checkboxes) {
        const id = cb.getAttribute('data-pago-id');
        try {
            await fetch('api_pagos.php?action=approve', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id_transaccion: id })
            });
        } catch (e) { errors++; }
    }
    
    btn.disabled = false;
    btn.innerHTML = '<span class="material-symbols-outlined !text-xl">check_circle</span> <span>Aprobar Seleccionados</span>';
    loadPagos();
    mostrarNotificacion('Proceso finalizado', errors > 0 ? 'warning' : 'success');
}

async function rechazarSeleccionados() {
    const checkboxes = document.querySelectorAll('.pago-checkbox:checked');
    if (checkboxes.length === 0) return;

    const motivo = await mostrarModalInput('Motivo para rechazar seleccionados', 'Motivo rechazo');
    if (!motivo) return;
    
    const btn = document.getElementById('rechazarSeleccionadosBtn');
    btn.disabled = true;
    btn.innerHTML = 'Procesando...';

    let errors = 0;
    for (const cb of checkboxes) {
        const id = cb.getAttribute('data-pago-id');
        try {
            await fetch('api_pagos.php?action=reject', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id_transaccion: id, motivo: motivo })
            });
        } catch (e) { errors++; }
    }
    
    btn.disabled = false;
    btn.innerHTML = '<span class="material-symbols-outlined !text-xl">cancel</span> <span>Rechazar Seleccionados</span>';
    loadPagos();
    mostrarNotificacion('Proceso finalizado', errors > 0 ? 'warning' : 'success');
}


function toggleSelectAll() {
    const selectAll = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.pago-checkbox');
    checkboxes.forEach(cb => {
        // Solo seleccionar visibles
        const row = document.querySelector(`tr[data-id="${cb.getAttribute('data-pago-id')}"]`);
        if (row && row.style.display !== 'none') {
             cb.checked = selectAll.checked;
        }
    });
    actualizarSeleccion();
}

function actualizarSeleccion() {
    const checkboxes = document.querySelectorAll('.pago-checkbox:checked');
    const count = checkboxes.length;
    
    const btnAprobar = document.getElementById('aprobarSeleccionadosBtn');
    const btnRechazar = document.getElementById('rechazarSeleccionadosBtn');
    const cntAprobar = document.getElementById('contadorAprobar');
    const cntRechazar = document.getElementById('contadorRechazar');
    
    if (count > 0) {
        btnAprobar.disabled = false;
        btnRechazar.disabled = false;
        cntAprobar.classList.remove('hidden');
        cntRechazar.classList.remove('hidden');
        cntAprobar.textContent = count;
        cntRechazar.textContent = count;
    } else {
        btnAprobar.disabled = true;
        btnRechazar.disabled = true;
        cntAprobar.classList.add('hidden');
        cntRechazar.classList.add('hidden');
    }
}

function initMobileMenu() {
    // Mobile menu logic (reused)
     const sidebar = document.getElementById('sidebar');
     const overlay = document.getElementById('mobileOverlay');
     window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) {
            if (sidebar) sidebar.classList.remove('-translate-x-full');
            if (overlay) overlay.classList.add('hidden');
        }
    });
}
function toggleMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('mobileOverlay');
    if (sidebar) sidebar.classList.toggle('-translate-x-full');
    if (overlay) overlay.classList.toggle('hidden');
}
function navegarAtras() { window.history.back(); }


// Modals helpers
function mostrarModal(titulo, contenido) {
    const overlay = document.createElement('div');
    overlay.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm';
    overlay.innerHTML = `
        <div class="bg-white dark:bg-[#1e2433] rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto border border-gray-200 dark:border-border-dark">
            <div class="p-4 border-b border-gray-200 dark:border-border-dark flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">${titulo}</h3>
                <button onclick="this.closest('.fixed').remove()" class="text-gray-500 hover:text-red-500"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="p-6">
                ${contenido}
            </div>
            <div class="p-4 border-t border-gray-200 dark:border-border-dark bg-gray-50 dark:bg-[#151a25] flex justify-end">
                <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">Cerrar</button>
            </div>
        </div>
    `;
    document.body.appendChild(overlay);
}

function mostrarModalConfirmacion(mensaje, titulo) {
    return new Promise(resolve => {
        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm';
        overlay.innerHTML = `
            <div class="bg-white dark:bg-[#1e2433] rounded-lg shadow-xl max-w-md w-full border border-gray-200 dark:border-border-dark">
                <div class="p-6 text-center">
                    <span class="material-symbols-outlined text-4xl text-yellow-500 mb-4">warning</span>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">${titulo}</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">${mensaje}</p>
                    <div class="flex justify-center gap-4">
                        <button id="btnCancel" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Cancelar</button>
                        <button id="btnConfirm" class="px-4 py-2 bg-primary text-white rounded hover:bg-blue-600">Confirmar</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);
        
        overlay.querySelector('#btnCancel').onclick = () => { overlay.remove(); resolve(false); };
        overlay.querySelector('#btnConfirm').onclick = () => { overlay.remove(); resolve(true); };
    });
}

function mostrarModalInput(mensaje, titulo) {
    return new Promise(resolve => {
        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm';
        overlay.innerHTML = `
            <div class="bg-white dark:bg-[#1e2433] rounded-lg shadow-xl max-w-md w-full border border-gray-200 dark:border-border-dark">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">${titulo}</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">${mensaje}</p>
                    <input type="text" id="inputModal" class="w-full bg-background-dark border border-[#3b4354] rounded p-2 text-white mb-6" placeholder="Escribe aquí...">
                    <div class="flex justify-end gap-4">
                        <button id="btnCancel" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Cancelar</button>
                        <button id="btnConfirm" class="px-4 py-2 bg-primary text-white rounded hover:bg-blue-600">Aceptar</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);
        
        const input = overlay.querySelector('#inputModal');
        input.focus();
        
        overlay.querySelector('#btnCancel').onclick = () => { overlay.remove(); resolve(null); };
        overlay.querySelector('#btnConfirm').onclick = () => { 
            const val = input.value.trim();
            overlay.remove(); 
            resolve(val); 
        };
    });
}

function mostrarNotificacion(msg, type='info') {
    const div = document.createElement('div');
    div.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded shadow-lg text-white ${type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500'}`;
    div.textContent = msg;
    document.body.appendChild(div);
    setTimeout(() => div.remove(), 3000);
}
function exportarPagosCSV() {
    alert('Función de exportar pendiente de implementar con datos reales');
}

// Función para manejar el logout del administrador
function handleLogoutAdmin() {
    // Usar customConfirm para mantener consistencia con el resto de la aplicación
    if (typeof customConfirm === 'function') {
        customConfirm('¿Estás seguro de que deseas cerrar sesión?', 'Cerrar Sesión', 'warning').then(confirmed => {
            if (confirmed) {
                // Redirigir al logout.php que destruye la sesión del servidor
                window.location.href = 'logout.php';
            }
        });
    } else {
        // Si customConfirm no está disponible, esperar a que se cargue
        setTimeout(() => {
            if (typeof customConfirm === 'function') {
                handleLogoutAdmin();
            } else {
                // Fallback si customConfirm no se carga
                if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                    window.location.href = 'logout.php';
                }
            }
        }, 200);
    }
}
</script>
<!-- Cargar custom-alerts.js para usar alertas personalizadas -->
<script src="custom-alerts.js"></script>
</body>
</html>