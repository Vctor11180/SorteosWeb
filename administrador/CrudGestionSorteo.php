<!DOCTYPE html>
<?php
// Conexión a la base de datos
require_once 'config.php';
$conn = getDBConnection();
?>


<html class="dark" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Sorteos Admin Dashboard</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
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
                        "background-dark": "#111621",
                        "card-dark": "#1e2433", // Slightly lighter than bg-dark for contrast
                        "border-dark": "#2a3241",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" },
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
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-full bg-cover bg-center" data-alt="User profile picture" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAfIzDdUJZk0e1bBHKOe7BG0HPanJ3nx8d9vtsJZZMiXM6ZJw9-oPch2DQWyWWrowTikKHJBUkhOyI6hUEiy_TgTGdRmm-4uDyO3KjasL500lcWogtry5HOXaJxBgDxpuT_8QBEVTnbuI4727c7c5qtPNid2CyQr0SnpyEcv2R9UEoiXiOVUH_g0RdYwYfb9u5EU5DkqEZl2oL9UW9s45D-zD3htPmEHk69TrCVPL50vnE6cDfTlcz9AJEZo7Hb8gpAhxwAxDP4SCs');"></div>
<div class="flex flex-col">
<span class="text-sm font-medium text-slate-900 dark:text-white">Admin User</span>
<span class="text-xs text-gray-500">admin@sorteos.web</span>
</div>
</div>
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
<h1 class="text-xl font-bold text-slate-900 dark:text-white hidden sm:block">Gestión de Sorteos</h1>
</div>
<div class="flex items-center gap-4">
<div class="relative hidden md:block w-64">
<span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
<span class="material-symbols-outlined text-[20px]">search</span>
</span>
<input id="headerSearchInput" class="w-full bg-gray-100 dark:bg-[#1e2433] border-none rounded-lg py-2 pl-10 pr-4 text-sm focus:ring-2 focus:ring-primary placeholder-gray-500 dark:placeholder-gray-400" placeholder="Buscar sorteo, usuario..." type="text" style="color: rgb(15 23 42) !important;"/>
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
<div class="flex flex-wrap items-center gap-2 px-4 py-2 mb-4">
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
<span class="text-white text-sm font-medium leading-normal">Gestión de Sorteos</span>
</div>
<!-- Page Header & Actions -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-8">
<div class="flex flex-col gap-2">
<h1 class="text-3xl md:text-4xl font-black tracking-tight text-slate-900 dark:text-white">Gestión de Sorteos</h1>
<p class="text-gray-500 dark:text-gray-400 text-base">Administra, crea y monitorea todos tus eventos activos y pasados.</p>
</div>
<div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
<button onclick="exportarSorteosCSV()" class="flex items-center justify-center gap-2 h-11 px-5 bg-primary/10 hover:bg-primary/20 text-primary border border-primary/20 text-sm font-bold rounded-lg transition-all active:scale-95 w-full sm:w-auto">
<span class="material-symbols-outlined text-[20px]">download</span>
<span>Exportar CSV</span>
</button>
<button id="createRaffleButton" onclick="showRaffleModal('create')" class="flex items-center justify-center gap-2 h-11 px-5 bg-primary hover:bg-primary-hover text-white text-sm font-bold rounded-lg shadow-lg shadow-blue-900/20 transition-all active:scale-95 w-full sm:w-auto">
<span class="material-symbols-outlined text-[20px]">add_circle</span>
<span>Crear Nuevo Sorteo</span>
</button>
</div>
</div>
<!-- Filters & Search -->
<div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6">
<div class="md:col-span-5 lg:col-span-4 relative group">
<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-primary transition-colors">
<span class="material-symbols-outlined">search</span>
</div>
<input id="searchRaffleInput" class="block w-full pl-10 pr-3 py-3 border border-gray-200 dark:border-border-dark rounded-lg leading-5 bg-white dark:bg-[#1e2433] placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary sm:text-sm transition-shadow" placeholder="Buscar por nombre, ID o premio..." type="text" style="color: rgb(15 23 42) !important;"/>
</div>
<div class="md:col-span-3 lg:col-span-2">
<div class="relative">
<select id="statusFilterSelect" onchange="applyFilters()" class="block w-full pl-3 pr-10 py-3 text-base border-gray-200 dark:border-border-dark focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary sm:text-sm rounded-lg bg-white dark:bg-[#1e2433] appearance-none cursor-pointer" style="color: rgb(15 23 42) !important;">
<option value="all" style="color: rgb(15 23 42); background-color: rgb(255 255 255);">Todos los Estados</option>
<option value="Borrador" style="color: rgb(15 23 42); background-color: rgb(255 255 255);">Borrador</option>
<option value="Activo" style="color: rgb(15 23 42); background-color: rgb(255 255 255);">Activo</option>
<option value="Pausado" style="color: rgb(15 23 42); background-color: rgb(255 255 255);">Pausado</option>
<option value="Finalizado" style="color: rgb(15 23 42); background-color: rgb(255 255 255);">Finalizado</option>
</select>
<div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
<span class="material-symbols-outlined">expand_more</span>
</div>
</div>
</div>
<div class="md:col-span-4 lg:col-span-6 flex justify-end items-center gap-2">
<span id="paginationInfo" class="text-sm text-gray-500 dark:text-gray-400 hidden lg:inline-block">Mostrando 1-5 de 24 resultados</span>
</div>
</div>
<!-- Data Table Card -->
<div class="flex-1 bg-white dark:bg-[#151a23] rounded-xl border border-gray-200 dark:border-border-dark overflow-hidden flex flex-col shadow-sm">
<div class="overflow-x-auto custom-scrollbar flex-1">
<table class="min-w-full divide-y divide-gray-200 dark:divide-border-dark">
<thead class="bg-gray-50 dark:bg-[#111621]">
<tr>
<th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24" scope="col">ID</th>
<th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider" scope="col">Nombre del Sorteo</th>
<th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider" scope="col">Periodo</th>
<th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider" scope="col">Premio Principal</th>
<th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32" scope="col">Estado</th>
<th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-48" scope="col">Boletos</th>
<th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32" scope="col">Acciones</th>
</tr>
</thead>
<tbody id="rafflesTableBody" class="divide-y divide-gray-200 dark:divide-border-dark bg-white dark:bg-[#151a23]">
<!-- Los sorteos se cargarán dinámicamente desde la base de datos -->
<tr>
<td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
<span class="material-symbols-outlined animate-spin inline-block mr-2">autorenew</span>
Cargando sorteos...
</td>
</tr>
</tbody>
</table>
</div>
<!-- Pagination -->
<div class="bg-white dark:bg-[#151a23] px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-border-dark sm:px-6">
<div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
<div>
<p class="text-sm text-gray-700 dark:text-gray-400">
                            Mostrando <span id="paginationStart" class="font-medium">1</span> a <span id="paginationEnd" class="font-medium">5</span> de <span id="paginationTotal" class="font-medium">24</span> resultados
                        </p>
</div>
<div>
<nav aria-label="Pagination" class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
<a id="prevPage" onclick="changePage('prev')" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#151a23] text-sm font-medium text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer">
<span class="sr-only">Anterior</span>
<span class="material-symbols-outlined text-sm">chevron_left</span>
</a>
<div id="paginationNumbers"></div>
<a id="nextPage" onclick="changePage('next')" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#151a23] text-sm font-medium text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer">
<span class="sr-only">Siguiente</span>
<span class="material-symbols-outlined text-sm">chevron_right</span>
</a>
</nav>
                                ...
                            </span>
<a class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#151a23] text-sm font-medium text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800" href="#">
<span class="sr-only">Siguiente</span>
<span class="material-symbols-outlined text-sm">chevron_right</span>
</a>
</nav>
</div>
</div>
    </div>
</div>
<!-- Optional: Background Decoration -->
<div class="fixed top-0 left-0 w-full h-full pointer-events-none -z-10 overflow-hidden">
<div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] bg-primary/5 rounded-full blur-3xl"></div>
<div class="absolute bottom-[-10%] left-[-5%] w-[400px] h-[400px] bg-purple-500/5 rounded-full blur-3xl"></div>
</div>
</main>
<!-- Modal para crear/editar sorteo -->
<div class="hidden fixed inset-0 z-[9999] overflow-y-auto" id="raffleModal" onclick="if(event.target === this) closeRaffleModal()">
<div aria-hidden="true" class="fixed inset-0 transition-opacity z-[9998]">
<div class="absolute inset-0 bg-gray-900/75 backdrop-blur-sm"></div>
</div>
<div class="relative bg-white dark:bg-[#1c212c] rounded-lg text-left overflow-hidden shadow-xl transform transition-all w-full max-w-2xl mx-4 my-8 z-[10000]" onclick="event.stopPropagation()">
<div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
<div class="sm:flex sm:items-start">
<div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-primary/10 sm:mx-0 sm:h-10 sm:w-10">
<span class="material-symbols-outlined text-primary">edit_document</span>
</div>
<div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
<h3 class="text-lg leading-6 font-medium text-slate-900 dark:text-white mb-4" id="modalTitle">
                                Crear Nuevo Sorteo
                            </h3>
<form id="raffleForm" onsubmit="saveRaffle(event)" class="mt-4 space-y-4">
<input type="hidden" id="raffleIdInput" value="">
<div>
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="raffleNameInput">Nombre del Sorteo</label>
<input class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm bg-white dark:bg-[#111621] dark:text-white px-3 py-2" id="raffleNameInput" name="raffle-name" placeholder="Ej. Gran Rifa Navideña" type="text" required/>
</div>
<div class="grid grid-cols-2 gap-4">
<div>
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="startDateInput">Fecha Inicio</label>
<input class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm bg-white dark:bg-[#111621] dark:text-white px-3 py-2" id="startDateInput" name="start-date" type="date" min="" required/>
</div>
<div>
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="endDateInput">Fecha Fin</label>
<input class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm bg-white dark:bg-[#111621] dark:text-white px-3 py-2" id="endDateInput" name="end-date" type="date" min="" required/>
</div>
</div>
<div>
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="descripcionInput">Descripción del Premio</label>
<div class="mt-1 flex rounded-md shadow-sm">
<span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 sm:text-sm">
<span class="material-symbols-outlined text-sm">emoji_events</span>
</span>
<input class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md focus:ring-primary focus:border-primary sm:text-sm border border-l-0 border-gray-300 dark:border-gray-600 bg-white dark:bg-[#111621] dark:text-white" id="descripcionInput" name="descripcion" placeholder="Ej. Auto 0km Toyota Yaris" type="text" required/>
</div>
</div>
<div>
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="imagenUrlInput">URL de la Imagen</label>
<div class="mt-1 flex rounded-md shadow-sm">
<span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 sm:text-sm">
<span class="material-symbols-outlined text-sm">image</span>
</span>
<input class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md focus:ring-primary focus:border-primary sm:text-sm border border-l-0 border-gray-300 dark:border-gray-600 bg-white dark:bg-[#111621] dark:text-white" id="imagenUrlInput" name="imagen_url" placeholder="https://ejemplo.com/imagen.jpg" type="url"/>
</div>
</div>
<div class="grid grid-cols-2 gap-4">
<div>
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="precioBoletoInput">Precio por Boleto</label>
<div class="mt-1 flex rounded-md shadow-sm">
<span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 sm:text-sm">$</span>
<input class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md focus:ring-primary focus:border-primary sm:text-sm border border-l-0 border-gray-300 dark:border-gray-600 bg-white dark:bg-[#111621] dark:text-white" id="precioBoletoInput" name="precio_boleto" placeholder="0.00" type="number" step="0.01" min="0" required/>
</div>
</div>
<div>
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="ticketsInput">Cantidad Boletos</label>
<input class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary focus:ring-primary sm:text-sm bg-white dark:bg-[#111621] dark:text-white px-3 py-2" id="ticketsInput" name="total_boletos_crear" placeholder="1000" type="number" min="1" required/>
</div>
</div>
<div>
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="statusInput">Estado</label>
<select class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md bg-white dark:bg-[#111621] dark:text-white" id="statusInput" name="estado" required>
<option value="Activo" selected>Activo</option>
<option value="Borrador">Borrador</option>
<option value="Pausado">Pausado</option>
<option value="Finalizado">Finalizado</option>
</select>
</div>
<div class="bg-gray-50 dark:bg-[#151a23] px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3 mt-6">
<button type="submit" id="saveRaffleButton" class="w-full inline-flex justify-center items-center gap-2 rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm transition-colors">
<span class="material-symbols-outlined text-sm">save</span>
                        Guardar Sorteo
                    </button>
<button type="button" id="cancelButton" onclick="closeRaffleModal()" class="w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-[#1c212c] text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                        Cancelar
                    </button>
</div>
</form>
</div>
</div>
</div>
</div>
</div>
<script>
// Datos de sorteos (se cargarán desde la API)
let raffles = [];

let currentPage = 1;
let itemsPerPage = 5;
let currentSearchQuery = '';
let currentStatusFilter = 'all';
let editingRaffleId = null;
let filteredRaffles = [];

// Cargar sorteos desde la API
async function loadRaffles() {
    try {
        const response = await fetch('api_sorteos.php?action=list');
        const result = await response.json();
        
        if (result.success) {
            raffles = result.data;
            applyFilters();
        } else {
            showNotification('Error al cargar sorteos: ' + (result.error || 'Error desconocido'), 'error');
            raffles = [];
            applyFilters();
        }
    } catch (error) {
        console.error('Error al cargar sorteos:', error);
        showNotification('Error de conexión al cargar sorteos', 'error');
        raffles = [];
        applyFilters();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Cargar sorteos al iniciar
    loadRaffles();
    
    // Establecer fecha mínima para los campos de fecha (fecha actual)
    const today = new Date().toISOString().split('T')[0];
    const startDateInput = document.getElementById('startDateInput');
    const endDateInput = document.getElementById('endDateInput');
    
    if (startDateInput) {
        startDateInput.setAttribute('min', today);
        startDateInput.addEventListener('change', function() {
            // Cuando cambia la fecha de inicio, actualizar el min de fecha fin
            if (endDateInput && this.value) {
                const startDate = new Date(this.value);
                startDate.setDate(startDate.getDate() + 1); // Fecha fin debe ser al menos un día después
                endDateInput.setAttribute('min', startDate.toISOString().split('T')[0]);
                
                // Si la fecha fin es anterior a la nueva fecha mínima, limpiarla
                if (endDateInput.value && endDateInput.value <= this.value) {
                    endDateInput.value = '';
                }
            }
        });
    }
    
    if (endDateInput) {
        endDateInput.setAttribute('min', today);
        endDateInput.addEventListener('change', function() {
            // Validar que la fecha fin sea posterior a la fecha inicio
            if (startDateInput && startDateInput.value) {
                if (this.value <= startDateInput.value) {
                    mostrarError('endDateInput', 'La fecha de fin debe ser posterior a la fecha de inicio');
                    this.value = '';
                }
            }
        });
    }
    
    // Asegurar que el modal esté cerrado al cargar la página
    const modal = document.getElementById('raffleModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.style.cssText = 'display: none !important; visibility: hidden !important; opacity: 0 !important;';
    }
    
    // Ajustar colores de texto en inputs y selects según el tema
    function ajustarColoresTexto() {
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? 'rgb(255 255 255)' : 'rgb(15 23 42)';
        const bgColor = isDark ? 'rgb(30 36 51)' : 'rgb(255 255 255)';
        
        // Buscador principal
        const searchInput = document.getElementById('searchRaffleInput');
        if (searchInput) {
            searchInput.style.color = textColor;
            searchInput.style.backgroundColor = bgColor;
        }
        
        // Dropdown de estados
        const statusSelect = document.getElementById('statusFilterSelect');
        if (statusSelect) {
            statusSelect.style.color = textColor;
            statusSelect.style.backgroundColor = bgColor;
            // Ajustar opciones
            Array.from(statusSelect.options).forEach(option => {
                option.style.color = textColor;
                option.style.backgroundColor = bgColor;
            });
        }
        
        // Buscador del header
        const headerSearch = document.getElementById('headerSearchInput');
        if (headerSearch) {
            headerSearch.style.color = textColor;
        }
    }
    
    // Aplicar colores al cargar
    ajustarColoresTexto();
    
    // Observar cambios en el tema
    const observer = new MutationObserver(ajustarColoresTexto);
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });
    
    const searchInput = document.getElementById('searchRaffleInput');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            currentSearchQuery = e.target.value.toLowerCase().trim();
            applyFilters();
        });
    }
    const statusFilter = document.getElementById('statusFilterSelect');
    if (statusFilter) {
        statusFilter.addEventListener('change', function(e) {
            currentStatusFilter = e.target.value;
            applyFilters();
        });
    }
    
    // Búsqueda global del header
    const headerSearchInput = document.getElementById('headerSearchInput');
    if (headerSearchInput) {
        headerSearchInput.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase().trim();
            if (query.length >= 2) {
                currentSearchQuery = query;
                applyFilters();
            } else if (query.length === 0) {
                currentSearchQuery = '';
                applyFilters();
            }
        });
        
        headerSearchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = e.target.value.toLowerCase().trim();
                if (query.length > 0) {
                    currentSearchQuery = query;
                    applyFilters();
                }
            }
        });
    }
    
    // No aplicar filtros aquí, se aplicarán después de cargar los datos
});

function applyFilters() {
    filteredRaffles = raffles.filter(raffle => {
        if (currentSearchQuery) {
            const searchLower = currentSearchQuery.toLowerCase();
            const name = (raffle.name || '').toLowerCase();
            const id = (raffle.id || '').toLowerCase();
            const prize = (raffle.prize || '').toLowerCase();
            if (!name.includes(searchLower) && !id.includes(searchLower) && !prize.includes(searchLower)) {
                return false;
            }
        }
        if (currentStatusFilter !== 'all' && raffle.status !== currentStatusFilter) return false;
        return true;
    });
    renderTable(filteredRaffles);
    renderPagination(filteredRaffles.length);
}

function renderTable(filteredRaffles) {
    const tbody = document.getElementById('rafflesTableBody');
    const start = (currentPage - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const pageRaffles = filteredRaffles.slice(start, end);
    
    if (pageRaffles.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No se encontraron sorteos</td></tr>';
        return;
    }
    
    tbody.innerHTML = pageRaffles.map(raffle => {
        // Badge de estado
        let statusBadge = '';
        if (raffle.status === 'Activo') {
            statusBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">Activo</span>';
        } else if (raffle.status === 'Finalizado') {
            statusBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">Finalizado</span>';
        } else if (raffle.status === 'Pausado') {
            statusBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800">Pausado</span>';
        } else {
            statusBadge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">Borrador</span>';
        }
        
        const progress = raffle.ticketsTotal > 0 ? (raffle.ticketsSold / raffle.ticketsTotal) * 100 : 0;
        const progressColor = progress >= 90 ? 'bg-amber-500' : 'bg-primary';
        
        // Acciones según el estado
        let actionsHTML = '';
        if (raffle.status === 'Finalizado') {
            actionsHTML = `<button onclick="viewResults(this)" class="text-gray-400 hover:text-primary dark:hover:text-primary transition-colors p-1 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700" title="Ver Resultados"><span class="material-symbols-outlined text-[20px]">visibility</span></button>`;
        } else {
            actionsHTML = `<button onclick="editRaffle(this)" class="text-gray-400 hover:text-primary dark:hover:text-primary transition-colors p-1 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700" title="Editar"><span class="material-symbols-outlined text-[20px]">edit</span></button><button onclick="deleteRaffle(this)" class="text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors p-1 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700" title="Eliminar"><span class="material-symbols-outlined text-[20px]">delete</span></button>`;
        }
        
        // Parsear período
        const periodParts = raffle.period ? raffle.period.split(' ') : [];
        const periodDisplay = periodParts.length >= 6 
            ? `${periodParts[0]} ${periodParts[1]} - ${periodParts[3]} ${periodParts[4]}`
            : raffle.period || 'N/A';
        const periodYear = periodParts.length >= 6 ? periodParts[5] : '';
        
        return `<tr class="raffle-row hover:bg-gray-50 dark:hover:bg-[#1e2433] transition-colors group" data-id="${raffle.id}" data-name="${raffle.name}" data-period="${raffle.period || ''}" data-prize="${raffle.prize || ''}" data-status="${raffle.status}" data-tickets-sold="${raffle.ticketsSold}" data-tickets-total="${raffle.ticketsTotal}" data-created-by="${raffle.createdBy}">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 font-mono">#${raffle.id}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-slate-900 dark:text-white">${raffle.name || 'Sin título'}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">${raffle.createdBy || 'Admin'}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-gray-300">${periodDisplay}</div>
                ${periodYear ? `<div class="text-xs text-gray-500 dark:text-gray-500">${periodYear}</div>` : ''}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <span class="material-symbols-outlined text-yellow-500 mr-2 text-lg">emoji_events</span>
                    <span class="text-sm text-gray-900 dark:text-gray-300">${raffle.prize || 'Premio Principal'}</span>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center">${statusBadge}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="w-full">
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-slate-900 dark:text-white font-medium">${raffle.ticketsSold || 0}</span>
                        <span class="text-gray-500 dark:text-gray-400">de ${raffle.ticketsTotal || 0}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                        <div class="${progressColor} h-1.5 rounded-full" style="width: ${progress}%"></div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end gap-2 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">${actionsHTML}</div>
            </td>
        </tr>`;
    }).join('');
}

function renderPagination(totalItems) {
    const filtered = raffles.filter(r => {
        if (currentSearchQuery) {
            const searchLower = currentSearchQuery.toLowerCase();
            if (!r.name.toLowerCase().includes(searchLower) && !r.id.toLowerCase().includes(searchLower) && !r.prize.toLowerCase().includes(searchLower)) return false;
        }
        if (currentStatusFilter !== 'all' && r.status !== currentStatusFilter) return false;
        return true;
    });
    const totalPages = Math.ceil(filtered.length / itemsPerPage);
    const paginationNumbers = document.getElementById('paginationNumbers');
    let paginationHTML = '';
    const maxPages = Math.min(totalPages, 5);
    for (let i = 1; i <= maxPages; i++) {
        if (i === currentPage) {
            paginationHTML += `<a aria-current="page" class="z-10 bg-primary/10 border-primary text-primary relative inline-flex items-center px-4 py-2 border text-sm font-bold cursor-pointer">${i}</a>`;
        } else {
            paginationHTML += `<a onclick="changePage(${i})" class="bg-white dark:bg-[#151a23] border-gray-300 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 relative inline-flex items-center px-4 py-2 border text-sm font-medium cursor-pointer">${i}</a>`;
        }
    }
    if (totalPages > 5) {
        paginationHTML = `<a onclick="changePage(1)" class="bg-white dark:bg-[#151a23] border-gray-300 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 relative inline-flex items-center px-4 py-2 border text-sm font-medium cursor-pointer">1</a><span class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#151a23] text-sm font-medium text-gray-700 dark:text-gray-400">...</span><a onclick="changePage(${totalPages})" class="bg-white dark:bg-[#151a23] border-gray-300 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 relative inline-flex items-center px-4 py-2 border text-sm font-medium cursor-pointer">${totalPages}</a>`;
    }
    paginationNumbers.innerHTML = paginationHTML;
    const start = (currentPage - 1) * itemsPerPage + 1;
    const end = Math.min(currentPage * itemsPerPage, totalItems);
    document.getElementById('paginationStart').textContent = start;
    document.getElementById('paginationEnd').textContent = end;
    document.getElementById('paginationTotal').textContent = totalItems;
    document.getElementById('paginationInfo').textContent = `Mostrando ${start}-${end} de ${totalItems} resultados`;
    document.getElementById('prevPage').classList.toggle('opacity-50', currentPage === 1);
    document.getElementById('nextPage').classList.toggle('opacity-50', currentPage === totalPages);
}

function changePage(direction) {
    const filtered = raffles.filter(r => {
        if (currentSearchQuery) {
            const searchLower = currentSearchQuery.toLowerCase();
            if (!r.name.toLowerCase().includes(searchLower) && !r.id.toLowerCase().includes(searchLower) && !r.prize.toLowerCase().includes(searchLower)) return false;
        }
        if (currentStatusFilter !== 'all' && r.status !== currentStatusFilter) return false;
        return true;
    });
    const totalPages = Math.ceil(filtered.length / itemsPerPage);
    if (direction === 'prev' && currentPage > 1) {
        currentPage--;
    } else if (direction === 'next' && currentPage < totalPages) {
        currentPage++;
    } else if (typeof direction === 'number') {
        currentPage = direction;
    }
    applyFilters();
}

// Definir función en scope global explícitamente
window.showRaffleModal = function(mode, raffleData = null) {
    try {
        console.log('showRaffleModal ejecutado con mode:', mode);
        const modal = document.getElementById('raffleModal');
        if (!modal) {
            console.error('Modal no encontrado');
            showNotification('Error al abrir el formulario', 'error');
            return;
        }
        
        const title = document.getElementById('modalTitle');
        const form = document.getElementById('raffleForm');
        
        editingRaffleId = mode === 'edit' && raffleData ? raffleData.id : null;
        
        if (mode === 'edit' && raffleData) {
            title.textContent = 'Editar Sorteo';
            document.getElementById('raffleIdInput').value = raffleData.id || '';
            document.getElementById('raffleNameInput').value = raffleData.name || raffleData.titulo || '';
            document.getElementById('descripcionInput').value = raffleData.prize || raffleData.descripcion || '';
            document.getElementById('precioBoletoInput').value = raffleData.precio_boleto || '0.00';
            document.getElementById('ticketsInput').value = raffleData.ticketsTotal || '';
            document.getElementById('statusInput').value = raffleData.status || 'Activo';
            document.getElementById('imagenUrlInput').value = raffleData.imagen_url || '';
            
            // Parsear fechas - usar fecha_inicio y fecha_fin si están disponibles
            if (raffleData.fecha_inicio && raffleData.fecha_fin) {
                // Extraer solo la fecha (sin hora) si viene con hora
                const fechaInicio = raffleData.fecha_inicio.split(' ')[0];
                const fechaFin = raffleData.fecha_fin.split(' ')[0];
                document.getElementById('startDateInput').value = fechaInicio;
                document.getElementById('endDateInput').value = fechaFin;
                
                // Actualizar min de fecha fin
                const startDate = new Date(fechaInicio);
                startDate.setDate(startDate.getDate() + 1);
                document.getElementById('endDateInput').setAttribute('min', startDate.toISOString().split('T')[0]);
            } else if (raffleData.period) {
                // Fallback: parsear fechas del período si existe
                const periodParts = raffleData.period.split(' - ');
                if (periodParts.length === 2) {
                    // Intentar parsear diferentes formatos de fecha
                    const startDate = periodParts[0].trim();
                    const endDate = periodParts[1].trim();
                    document.getElementById('startDateInput').value = startDate;
                    document.getElementById('endDateInput').value = endDate;
                }
            }
        } else {
            title.textContent = 'Crear Nuevo Sorteo';
            if (form) {
                form.reset();
            }
            document.getElementById('raffleIdInput').value = '';
            document.getElementById('startDateInput').value = '';
            document.getElementById('endDateInput').value = '';
            document.getElementById('precioBoletoInput').value = '0.00';
            document.getElementById('imagenUrlInput').value = '';
            document.getElementById('statusInput').value = 'Activo'; // Por defecto Activo
            
            // Establecer fecha mínima al abrir modal
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('startDateInput').setAttribute('min', today);
            document.getElementById('endDateInput').setAttribute('min', today);
        }
        
        // Limpiar errores previos
        limpiarErroresFormulario('raffleForm');
        
        // Mostrar modal - limpiar estilos previos y aplicar nuevos
        modal.classList.remove('hidden');
        modal.style.cssText = 'display: flex !important; align-items: center !important; justify-content: center !important; z-index: 9999 !important; position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; visibility: visible !important; opacity: 1 !important;';
        
        // Prevenir scroll del body
        document.body.style.overflow = 'hidden';
        
        // Focus en el primer campo después de un pequeño delay
        setTimeout(() => {
            const firstInput = document.getElementById('raffleNameInput');
            if (firstInput) {
                firstInput.focus();
            }
        }, 150);
    } catch (error) {
        console.error('Error al mostrar modal:', error);
        manejarError(error, 'Abrir formulario de sorteo');
    }
}

function closeRaffleModal() {
    try {
        // Cancelar el cierre automático si existe
        if (window.autoCloseInterval) {
            clearInterval(window.autoCloseInterval);
            window.autoCloseInterval = null;
        }
        
        const modal = document.getElementById('raffleModal');
        if (modal) {
            // Ocultar modal completamente
            modal.classList.add('hidden');
            modal.style.cssText = 'display: none !important; visibility: hidden !important; opacity: 0 !important;';
        }
        
        // Restaurar scroll del body
        document.body.style.overflow = '';
        
        editingRaffleId = null;
        const form = document.getElementById('raffleForm');
        if (form) {
            form.reset();
            limpiarErroresFormulario('raffleForm');
        }
        
        // Restaurar botón de guardar si estaba en estado de éxito
        const submitBtn = document.getElementById('saveRaffleButton');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('bg-green-500', 'hover:bg-green-600');
            submitBtn.classList.add('bg-primary', 'hover:bg-blue-600');
            submitBtn.innerHTML = '<span class="material-symbols-outlined text-sm">save</span> Guardar Sorteo';
        }
    } catch (error) {
        console.error('Error al cerrar modal:', error);
    }
}

async function saveRaffle(e) {
    try {
        e.preventDefault();
        
        // Validar formulario
        if (!validarFormularioSorteo()) {
            return;
        }
        
        const submitBtn = e.target.querySelector('button[type="submit"]') || e.target.querySelector('button:last-child');
        const isEditing = !!editingRaffleId;
        
        // Mostrar estado de carga
        if (submitBtn) {
            const originalHTML = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="material-symbols-outlined animate-spin">autorenew</span> Guardando...';
            
            try {
                const titulo = document.getElementById('raffleNameInput').value.trim();
                const descripcion = document.getElementById('descripcionInput').value.trim();
                const precio_boleto = parseFloat(document.getElementById('precioBoletoInput').value) || 0;
                const total_boletos_crear = parseInt(document.getElementById('ticketsInput').value);
                // Obtener estado del select - asegurarse de que siempre tenga un valor
                const statusSelect = document.getElementById('statusInput');
                const estado = statusSelect ? statusSelect.value : 'Activo';
                
                // Validar que el estado no esté vacío
                if (!estado || estado.trim() === '') {
                    throw new Error('El estado del sorteo es requerido');
                }
                const fecha_inicio = document.getElementById('startDateInput').value;
                const fecha_fin = document.getElementById('endDateInput').value;
                const imagen_url = document.getElementById('imagenUrlInput').value.trim() || null;
                
                // Validar campos requeridos
                if (!titulo || !descripcion || !total_boletos_crear || !estado || !fecha_inicio || !fecha_fin) {
                        throw new Error('Faltan datos requeridos en el formulario');
                    }
                    
                if (precio_boleto < 0) {
                    throw new Error('El precio del boleto no puede ser negativo');
                }
                
                if (total_boletos_crear < 1) {
                    throw new Error('La cantidad de boletos debe ser mayor a 0');
                }
                
                // Preparar datos para la API según estructura de la base de datos
                const data = {
                    titulo: titulo,
                    descripcion: descripcion,
                    precio_boleto: precio_boleto,
                    total_boletos_crear: total_boletos_crear,
                    fecha_inicio: fecha_inicio + ' 00:00:00',
                    fecha_fin: fecha_fin + ' 23:59:59',
                    estado: estado, // Asegurar que el estado se envíe
                    imagen_url: imagen_url
                };
                
                // Debug: verificar que el estado se está enviando
                console.log('Estado a enviar:', estado);
                console.log('Datos completos:', data);
                
                let url = 'api_sorteos.php?action=create';
                if (editingRaffleId) {
                    url = 'api_sorteos.php?action=update';
                    // Agregar id_sorteo para la actualización
                    data.id_sorteo = parseInt(editingRaffleId);
                }
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Cambiar el botón a estado de éxito
                    submitBtn.innerHTML = '<span class="material-symbols-outlined">check_circle</span> ¡Guardado exitosamente!';
                    submitBtn.classList.remove('bg-primary', 'hover:bg-blue-600');
                    submitBtn.classList.add('bg-green-500', 'hover:bg-green-600');
                    submitBtn.disabled = true;
                    
                    // Mostrar notificación
                    showNotification(isEditing ? 'Sorteo actualizado exitosamente' : 'Sorteo creado exitosamente', 'success');
                    
                    // Recargar sorteos desde la API
                    await loadRaffles();
                    
                    // Cerrar modal después de 5 segundos
                    let countdown = 5;
                    const countdownInterval = setInterval(() => {
                        countdown--;
                        if (countdown > 0) {
                            submitBtn.innerHTML = `<span class="material-symbols-outlined">check_circle</span> ¡Guardado! Cerrando en ${countdown}s...`;
                        } else {
                            clearInterval(countdownInterval);
                            closeRaffleModal();
                    // Restaurar botón
                    submitBtn.disabled = false;
                            submitBtn.classList.remove('bg-green-500', 'hover:bg-green-600');
                            submitBtn.classList.add('bg-primary', 'hover:bg-blue-600');
                    submitBtn.innerHTML = originalHTML;
                        }
                    }, 1000);
                    
                    // Guardar referencia del intervalo para poder cancelarlo si el usuario cierra manualmente
                    window.autoCloseInterval = countdownInterval;
                } else {
                    throw new Error(result.error || 'Error al guardar sorteo');
                }
                } catch (error) {
                    manejarError(error, 'Guardar sorteo', () => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalHTML;
                    });
                }
        }
    } catch (error) {
        manejarError(error, 'Procesar formulario de sorteo');
    }
}

/**
 * Valida el formulario de sorteo
 */
function validarFormularioSorteo() {
    let esValido = true;
    const errores = [];
    
    // Limpiar errores previos
    limpiarErroresFormulario('raffleForm');
    
    // Validar nombre
    const nombre = document.getElementById('raffleNameInput').value.trim();
    if (!nombre || nombre.length < 3) {
        mostrarError('raffleNameInput', 'El nombre del sorteo debe tener al menos 3 caracteres');
        esValido = false;
    }
    
    // Validar fechas
    const fechaInicio = document.getElementById('startDateInput').value;
    const fechaFin = document.getElementById('endDateInput').value;
    const fechaActual = new Date();
    fechaActual.setHours(0, 0, 0, 0); // Resetear horas para comparar solo fechas
    
    if (!fechaInicio) {
        mostrarError('startDateInput', 'La fecha de inicio es requerida');
        esValido = false;
    } else {
        const inicio = new Date(fechaInicio);
        inicio.setHours(0, 0, 0, 0);
        
        // Validar que la fecha de inicio no sea menor a la fecha actual
        if (inicio < fechaActual) {
            mostrarError('startDateInput', 'La fecha de inicio no puede ser anterior a la fecha actual');
        esValido = false;
        }
    }
    
    if (!fechaFin) {
        mostrarError('endDateInput', 'La fecha de fin es requerida');
        esValido = false;
    }
    
    if (fechaInicio && fechaFin) {
        const inicio = new Date(fechaInicio);
        const fin = new Date(fechaFin);
        inicio.setHours(0, 0, 0, 0);
        fin.setHours(0, 0, 0, 0);
        
        // Validar que la fecha de fin sea posterior a la fecha de inicio
        if (fin <= inicio) {
            mostrarError('endDateInput', 'La fecha de fin debe ser posterior a la fecha de inicio');
            esValido = false;
        }
    }
    
    // Validar descripción
    const descripcion = document.getElementById('descripcionInput').value.trim();
    if (!descripcion || descripcion.length < 5) {
        mostrarError('descripcionInput', 'La descripción del premio debe tener al menos 5 caracteres');
        esValido = false;
    }
    
    // Validar precio del boleto
    const precioBoleto = parseFloat(document.getElementById('precioBoletoInput').value);
    if (isNaN(precioBoleto) || precioBoleto < 0) {
        mostrarError('precioBoletoInput', 'El precio del boleto debe ser un número válido mayor o igual a 0');
        esValido = false;
    }
    
    // Validar cantidad de boletos
    const tickets = parseInt(document.getElementById('ticketsInput').value);
    if (!tickets || tickets < 1) {
        mostrarError('ticketsInput', 'La cantidad de boletos debe ser mayor a 0');
        esValido = false;
    } else if (tickets > 100000) {
        mostrarError('ticketsInput', 'La cantidad de boletos no puede exceder 100,000');
        esValido = false;
    }
    
    // Validar estado
    const estado = document.getElementById('statusInput').value;
    if (!estado) {
        mostrarError('statusInput', 'El estado es requerido');
        esValido = false;
    }
    
    if (!esValido) {
        showNotification('Por favor corrige los errores en el formulario', 'error');
    }
    
    return esValido;
}

/**
 * Muestra un error en un campo del formulario
 */
function mostrarError(campoId, mensaje) {
    const campo = document.getElementById(campoId);
    if (!campo) return;
    
    // Agregar clase de error
    campo.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
    campo.classList.remove('border-gray-300', 'dark:border-gray-600', 'focus:border-primary', 'focus:ring-primary');
    
    // Crear o actualizar mensaje de error
    let errorMsg = campo.parentElement.querySelector('.error-message');
    if (!errorMsg) {
        errorMsg = document.createElement('p');
        errorMsg.className = 'error-message text-red-500 text-xs mt-1';
        campo.parentElement.appendChild(errorMsg);
    }
    errorMsg.textContent = mensaje;
}

/**
 * Limpia los errores de un formulario
 */
function limpiarErroresFormulario(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    // Limpiar clases de error de todos los inputs
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
        input.classList.add('border-gray-300', 'dark:border-gray-600', 'focus:border-primary', 'focus:ring-primary');
    });
    
    // Eliminar mensajes de error
    const errorMessages = form.querySelectorAll('.error-message');
    errorMessages.forEach(msg => msg.remove());
}

function editRaffle(button) {
    const row = button.closest('tr');
    const raffleId = row.getAttribute('data-id');
    
    // Buscar el sorteo completo en el array
    const raffle = raffles.find(r => r.id === raffleId);
    
    if (raffle) {
        showRaffleModal('edit', {
            id: raffle.id,
            id_sorteo: raffle.id_sorteo,
            name: raffle.name,
            titulo: raffle.name,
            period: raffle.period,
            prize: raffle.prize,
            descripcion: raffle.prize || raffle.descripcion,
            precio_boleto: raffle.precio_boleto || 0,
            status: raffle.status,
            ticketsTotal: raffle.ticketsTotal,
            fecha_inicio: raffle.fecha_inicio,
            fecha_fin: raffle.fecha_fin,
            imagen_url: raffle.imagen_url || ''
        });
    } else {
        showNotification('Error: No se encontró el sorteo a editar', 'error');
    }
}

async function deleteRaffle(button) {
    const row = button.closest('tr');
    const raffleId = row.getAttribute('data-id');
    const raffleName = row.getAttribute('data-name');
    
    const confirmado = await mostrarModalConfirmacion(
        `¿Estás seguro de que deseas eliminar el sorteo "${raffleName}"? Esta acción no se puede deshacer.`,
        'Confirmar eliminación',
        'danger'
    );
    
    if (!confirmado) return;
    
    // Mostrar estado de carga en el botón
    const originalHTML = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<span class="material-symbols-outlined animate-spin">autorenew</span>';
    
    try {
        const response = await fetch('api_sorteos.php?action=delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id_sorteo: parseInt(raffleId)
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Sorteo eliminado exitosamente', 'success');
            // Recargar sorteos desde la API
            await loadRaffles();
        } else {
            throw new Error(result.error || 'Error al eliminar sorteo');
        }
    } catch (error) {
        manejarError(error, 'Eliminar sorteo');
        button.disabled = false;
        button.innerHTML = originalHTML;
    }
}

function viewResults(button) {
    const row = button.closest('tr');
    const raffleName = row.getAttribute('data-name');
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-50 overflow-y-auto';
    modal.onclick = function(e) { if (e.target === modal) modal.remove(); };
    modal.innerHTML = `<div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"><div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm"></div><div class="inline-block align-bottom bg-white dark:bg-[#1c212c] rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-200 dark:border-border-dark"><div class="px-4 pt-5 pb-4 sm:p-6"><h3 class="text-lg leading-6 font-medium text-slate-900 dark:text-white mb-4">Resultados del Sorteo: ${raffleName}</h3><div class="space-y-4"><div class="bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-200 dark:border-emerald-800 rounded-lg p-4"><p class="text-sm font-medium text-emerald-800 dark:text-emerald-300 mb-2">Ganador:</p><p class="text-lg font-bold text-emerald-900 dark:text-emerald-200">Carlos Ruiz</p><p class="text-sm text-emerald-700 dark:text-emerald-400 mt-1">Boleto Ganador: #0592</p></div><div class="grid grid-cols-2 gap-4"><div><p class="text-sm text-gray-600 dark:text-gray-400">Total de Boletos:</p><p class="text-base font-medium text-slate-900 dark:text-white">500</p></div><div><p class="text-sm text-gray-600 dark:text-gray-400">Fecha del Sorteo:</p><p class="text-base text-slate-900 dark:text-white">30 Nov 2023</p></div></div></div></div><div class="bg-gray-50 dark:bg-[#151a23] px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse"><button onclick="this.closest('.fixed').remove()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm">Cerrar</button></div></div></div>`;
    document.body.appendChild(modal);
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 ${type === 'success' ? 'bg-green-500 text-white' : type === 'error' ? 'bg-red-500 text-white' : 'bg-blue-500 text-white'}`;
    notification.style.opacity = '0';
    notification.style.transform = 'translateX(100%)';
    notification.style.transition = 'all 0.3s ease-in-out';
    notification.innerHTML = `<span class="material-symbols-outlined">${type === 'success' ? 'check_circle' : type === 'error' ? 'error' : 'info'}</span><span class="font-medium">${message}</span>`;
    document.body.appendChild(notification);
    setTimeout(() => { notification.style.opacity = '1'; notification.style.transform = 'translateX(0)'; }, 10);
    setTimeout(() => { notification.style.opacity = '0'; notification.style.transform = 'translateX(100%)'; setTimeout(() => notification.remove(), 300); }, 3000);
}

// ========== NAVEGACIÓN DINÁMICA ==========
/**
 * Inicializa la navegación dinámica
 */
document.addEventListener('DOMContentLoaded', function() {
    setActiveMenuItem();
    initMobileMenu();
});

/**
 * Establece el estado activo del menú según la página actual
 */
function setActiveMenuItem() {
    const currentPage = window.location.pathname.split('/').pop() || window.location.href.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const linkPage = link.getAttribute('data-page') || link.getAttribute('href');
        if (linkPage === currentPage || link.getAttribute('href') === currentPage) {
            link.className = 'nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary/10 text-primary font-medium';
            const icon = link.querySelector('.material-symbols-outlined');
            if (icon) {
                icon.classList.remove('group-hover:text-primary', 'transition-colors');
            }
        } else {
            link.className = 'nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group';
            const icon = link.querySelector('.material-symbols-outlined');
            if (icon && !icon.classList.contains('group-hover:text-primary')) {
                icon.classList.add('group-hover:text-primary', 'transition-colors');
            }
        }
    });
}

/**
 * Inicializa el menú móvil
 */
function initMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('mobileOverlay');
    
    // Cerrar menú al hacer clic en un enlace (móvil)
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 1024) {
                toggleMobileMenu();
            }
        });
    });
    
    // Cerrar menú al redimensionar a desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) {
            if (sidebar) sidebar.classList.remove('-translate-x-full');
            if (overlay) overlay.classList.add('hidden');
        }
    });
}

/**
 * Toggle del menú móvil
 */
function toggleMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('mobileOverlay');
    
    if (sidebar && overlay) {
        const isOpen = !sidebar.classList.contains('-translate-x-full');
        
        if (isOpen) {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        } else {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        }
    }
}

// ========== SISTEMA DE MODALES ==========
/**
 * Muestra un modal de confirmación (reemplazo de confirm())
 */
function mostrarModalConfirmacion(mensaje, titulo = 'Confirmar acción', tipo = 'warning') {
    return new Promise((resolve) => {
        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 z-50 overflow-y-auto modal-overlay';
        overlay.style.opacity = '0';
        overlay.style.transition = 'opacity 0.2s ease-in-out';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-labelledby', 'confirm-modal-title');
        overlay.onclick = function(e) {
            if (e.target === overlay) {
                cerrarModal(overlay);
                resolve(false);
            }
        };
        
        const iconos = {
            warning: { icon: 'warning', color: 'text-yellow-400', bg: 'bg-yellow-500/10' },
            danger: { icon: 'error', color: 'text-red-400', bg: 'bg-red-500/10' },
            info: { icon: 'info', color: 'text-blue-400', bg: 'bg-blue-500/10' }
        };
        
        const config = iconos[tipo] || iconos.warning;
        
        const modal = document.createElement('div');
        modal.className = 'flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0';
        
        modal.innerHTML = `
            <div aria-hidden="true" class="fixed inset-0 transition-opacity">
                <div class="absolute inset-0 bg-gray-900/75 backdrop-blur-sm"></div>
            </div>
            <span aria-hidden="true" class="hidden sm:inline-block sm:align-middle sm:h-screen">​</span>
            <div class="inline-block align-bottom bg-white dark:bg-[#1c212c] rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-border-dark">
                <div class="px-4 pt-5 pb-4 sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full ${config.bg} sm:mx-0 sm:h-10 sm:w-10">
                            <span class="material-symbols-outlined ${config.color}">${config.icon}</span>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 id="confirm-modal-title" class="text-lg leading-6 font-medium text-slate-900 dark:text-white">${titulo}</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">${mensaje}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-[#151a23] px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                    <button id="confirmBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Confirmar
                    </button>
                    <button id="cancelBtn" onclick="cerrarModal(this.closest('.modal-overlay')); window.modalConfirmResolve(false);" class="w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-[#1c212c] text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        `;
        
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        
        window.modalConfirmResolve = resolve;
        
        const confirmBtn = overlay.querySelector('#confirmBtn');
        confirmBtn.onclick = () => {
            cerrarModal(overlay);
            resolve(true);
        };
        
        setTimeout(() => {
            overlay.style.opacity = '1';
        }, 10);
        
        const escHandler = (e) => {
            if (e.key === 'Escape') {
                cerrarModal(overlay);
                resolve(false);
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
        
        setTimeout(() => confirmBtn.focus(), 100);
    });
}

/**
 * Cierra un modal
 */
function cerrarModal(overlay) {
    if (overlay) {
        overlay.style.opacity = '0';
        setTimeout(() => {
            overlay.remove();
        }, 200);
    }
}

// ========== NAVEGACIÓN CON HISTORIAL ==========

/**
 * Navega hacia atrás usando el historial del navegador
 */
function navegarAtras() {
    try {
        if (window.history.length > 1) {
            window.history.back();
        } else {
            window.location.href = 'DashboardAdmnistrador.php';
        }
    } catch (error) {
        console.error('Error al navegar atrás:', error);
        window.location.href = 'DashboardAdmnistrador.php';
    }
}

// ========== SISTEMA DE MANEJO DE ERRORES ==========

/**
 * Maneja errores de manera consistente
 */
function manejarError(error, contexto = 'Operación', callback = null) {
    const mensaje = error instanceof Error ? error.message : error;
    const errorCompleto = error instanceof Error ? error : new Error(mensaje);
    
    console.error(`[${contexto}] Error:`, errorCompleto);
    
    let mensajeUsuario = 'Ha ocurrido un error inesperado';
    let tipoError = 'error';
    
    if (mensaje.includes('red') || mensaje.includes('network') || mensaje.includes('fetch')) {
        mensajeUsuario = 'Error de conexión. Por favor verifica tu conexión a internet.';
    } else if (mensaje.includes('timeout') || mensaje.includes('tiempo')) {
        mensajeUsuario = 'La operación está tardando más de lo esperado. Por favor intenta nuevamente.';
    } else if (mensaje.includes('permiso') || mensaje.includes('autorización')) {
        mensajeUsuario = 'No tienes permisos para realizar esta acción.';
    } else if (mensaje) {
        mensajeUsuario = mensaje;
    }
    
    showNotification(mensajeUsuario, tipoError);
    
    if (callback && typeof callback === 'function') {
        try {
            callback(errorCompleto);
        } catch (callbackError) {
            console.error('Error en callback de manejo de errores:', callbackError);
        }
    }
}

// ========== EXPORTACIÓN DE DATOS ==========

/**
 * Exporta los sorteos visibles a CSV
 */
function exportarSorteosCSV() {
    try {
        const sorteosFiltrados = getFilteredRaffles();
        
        if (!sorteosFiltrados || sorteosFiltrados.length === 0) {
            showNotification('No hay sorteos para exportar', 'error');
            return;
        }
        
        // Preparar datos
        const headers = ['ID', 'Nombre', 'Período', 'Premio', 'Estado', 'Boletos Vendidos', 'Total Boletos', 'Progreso %', 'Creado Por'];
        const datos = sorteosFiltrados.map(raffle => {
            try {
                const progreso = raffle.ticketsTotal > 0 
                    ? ((raffle.ticketsSold / raffle.ticketsTotal) * 100).toFixed(1) 
                    : '0';
                
                return [
                    raffle.id || '',
                    raffle.name || '',
                    raffle.period || '',
                    raffle.prize || '',
                    raffle.status || '',
                    raffle.ticketsSold || 0,
                    raffle.ticketsTotal || 0,
                    `${progreso}%`,
                    raffle.createdBy || ''
                ];
            } catch (error) {
                console.warn('Error procesando sorteo:', error);
                return null;
            }
        }).filter(row => row !== null);
        
        if (datos.length === 0) {
            throw new Error('No se pudieron procesar los datos para exportar');
        }
        
        // Crear CSV
        const csv = [headers, ...datos].map(row => 
            row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(',')
        ).join('\n');
        
        // Agregar BOM para Excel
        const BOM = '\uFEFF';
        const blob = new Blob([BOM + csv], { type: 'text/csv;charset=utf-8;' });
        
        // Descargar
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `sorteos_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(link);
        link.click();
        
        setTimeout(() => {
            document.body.removeChild(link);
            URL.revokeObjectURL(link.href);
        }, 100);
        
        showNotification(`${datos.length} sorteo(s) exportado(s) exitosamente`, 'success');
    } catch (error) {
        manejarError(error, 'Exportar sorteos a CSV');
    }
}

/**
 * Obtiene los sorteos filtrados actualmente
 */
function getFilteredRaffles() {
    // Usar los sorteos filtrados si están disponibles, sino usar todos
    return (filteredRaffles && filteredRaffles.length > 0) ? filteredRaffles : raffles;
}

// Las funciones ya están disponibles globalmente a través de window.showRaffleModal
// No es necesario duplicar las asignaciones
</script>
</body>
</html>s