<!DOCTYPE html>
<?php
// Conexión a la base de datos
require_once 'config.php';
$conn = getDBConnection();
?>


<html class="dark" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Generación de Ganadores - Admin</title>
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<!-- Tailwind Config -->
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
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white antialiased overflow-hidden">
<div class="flex h-screen w-full">
<!-- Sidebar -->
<aside class="w-64 flex-shrink-0 flex flex-col border-r border-gray-200 dark:border-border-dark bg-white dark:bg-[#151a25]">
<div class="h-16 flex items-center px-6 border-b border-gray-200 dark:border-border-dark">
<div class="flex items-center gap-2 text-primary">
<span class="material-symbols-outlined text-3xl">confirmation_number</span>
<span class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">Sorteos<span class="text-primary">Admin</span></span>
</div>
</div>
<div class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
<p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-2">Principal</p>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="DashboardAdmnistrador.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">dashboard</span>
                    Dashboard
                </a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="CrudGestionSorteo.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">confirmation_number</span>
                    Gestión de Sorteos
                </a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="ValidacionPagosAdministrador.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">payments</span>
                    Validación de Pagos
                    <span class="ml-auto bg-yellow-500/20 text-yellow-500 text-xs font-bold px-2 py-0.5 rounded-full">3</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary/10 text-primary font-medium" href="GeneradorGanadoresAdminstradores.php">
<span class="material-symbols-outlined">emoji_events</span>
                    Generación de Ganadores
                </a>
<p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-6">Administración</p>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="GestionUsuariosAdministrador.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">group</span>
                    Usuarios
                </a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="AuditoriaAccionesAdmin.php">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">settings</span>
                    Auditoría
                </a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group" href="InformesEstadisticasAdmin.php">
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
<button class="lg:hidden text-gray-500">
<span class="material-symbols-outlined">menu</span>
</button>
<h1 class="text-xl font-bold text-slate-900 dark:text-white hidden sm:block">Generación de Ganadores</h1>
</div>
<div class="flex items-center gap-4">
<div class="relative hidden md:block w-64">
<span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
<span class="material-symbols-outlined text-[20px]">search</span>
</span>
<input class="w-full bg-gray-100 dark:bg-[#1e2433] border-none rounded-lg py-2 pl-10 pr-4 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary placeholder-gray-500" placeholder="Buscar sorteo, usuario..." type="text"/>
</div>
<button class="relative p-2 text-gray-500 hover:text-primary transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-white/5">
<span class="material-symbols-outlined">notifications</span>
<span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
</button>
</div>
</header>
<!-- Scrollable Content -->
<div class="flex-1 overflow-y-auto p-6 space-y-6">
<div class="max-w-[1200px] mx-auto w-full">
<!-- Breadcrumbs -->
<div class="flex flex-wrap items-center gap-2 px-4 pt-4">
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
<span class="text-white text-sm font-medium leading-normal">Generación de Ganadores</span>
</div>
<!-- Page Heading -->
<div class="flex flex-wrap justify-between gap-3 px-4">
<div class="flex min-w-72 flex-col gap-2">
<h1 class="text-white text-3xl md:text-4xl font-black leading-tight tracking-[-0.033em]">Generación de Ganadores</h1>
<p class="text-[#9d9db9] text-base font-normal leading-normal">Selecciona y gestiona los ganadores de los sorteos finalizados.</p>
</div>
<div class="flex items-center gap-2">
<button onclick="exportarHistorialGanadoresCSV()" class="flex items-center gap-2 bg-surface-dark hover:bg-[#34344a] text-white px-4 py-2 rounded-lg transition-colors border border-gray-700/50">
<span class="material-symbols-outlined text-[20px]">download</span>
<span class="text-sm font-medium">Exportar Reporte</span>
</button>
</div>
</div>
<!-- Filters & Search Toolbar -->
<div class="px-4 py-2 flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
<!-- Search -->
<label class="flex flex-col min-w-40 h-10 w-full md:max-w-md">
<div class="flex w-full flex-1 items-stretch rounded-lg h-full border border-[#3e3e52] focus-within:border-primary transition-colors bg-[#1e1e2d]">
<div class="text-[#9d9db9] flex bg-[#1e1e2d] items-center justify-center pl-3 rounded-l-lg border-r-0">
<span class="material-symbols-outlined !text-lg">search</span>
</div>
<input id="searchInput" class="flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-white focus:outline-0 focus:ring-0 border-none bg-[#1e1e2d] focus:border-none h-full placeholder:text-[#9d9db9] px-3 rounded-l-none border-l-0 pl-2 text-sm font-normal leading-normal" placeholder="Buscar por nombre del sorteo..." value="" style="color: rgb(255 255 255) !important; background-color: rgb(30 30 45) !important;"/>
</div>
</label>
<!-- Status Chips -->
<div class="flex gap-2 flex-wrap">
<button class="filter-btn flex h-9 items-center justify-center gap-x-2 rounded-full bg-primary/20 text-primary border border-primary/30 pl-4 pr-4 transition-all" data-filter="all" onclick="aplicarFiltro('all')">
<span class="text-sm font-medium leading-normal">Todos</span>
</button>
<button class="filter-btn flex h-9 items-center justify-center gap-x-2 rounded-full bg-surface-dark hover:bg-[#34344a] text-[#9d9db9] hover:text-white border border-[#3e3e52] pl-4 pr-4 transition-all" data-filter="pending" onclick="aplicarFiltro('pending')">
<span class="text-sm font-medium leading-normal">Pendientes</span>
</button>
<button class="filter-btn flex h-9 items-center justify-center gap-x-2 rounded-full bg-surface-dark hover:bg-[#34344a] text-[#9d9db9] hover:text-white border border-[#3e3e52] pl-4 pr-4 transition-all" data-filter="completed" onclick="aplicarFiltro('completed')">
<span class="text-sm font-medium leading-normal">Completados</span>
</button>
</div>
</div>
<!-- Raffle Cards Grid -->
<div id="rafflesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 px-4">
    <!-- Carga dinámica -->
    <div class="col-span-full text-center py-10 text-gray-500">
        <span class="material-symbols-outlined animate-spin text-3xl mb-2">autorenew</span>
        <p>Cargando sorteos...</p>
    </div>
</div>
<!-- Recent History Table -->
<div class="px-4 pb-10">
<h3 class="text-white text-xl font-bold mb-4 mt-8 flex items-center gap-2">
<span class="material-symbols-outlined text-gray-400">history</span>
                    Historial Reciente
                </h3>
<div class="w-full overflow-x-auto rounded-xl border border-[#3e3e52] bg-surface-dark">
<table class="w-full text-left text-sm text-[#9d9db9]">
<thead class="bg-[#1e1e2d] text-xs uppercase text-white font-semibold">
<tr>
<th class="px-6 py-4" scope="col">Sorteo</th>
<th class="px-6 py-4" scope="col">Fecha Sorteo</th>
<th class="px-6 py-4" scope="col">Ganador</th>
<th class="px-6 py-4" scope="col">Boleto</th>
<th class="px-6 py-4 text-right" scope="col">Estado</th>
</tr>
</thead>
<tbody id="historyTableBody" class="divide-y divide-[#3e3e52]">
<!-- Historial cargado dinámicamente -->
</tbody>
</table>
</div>
</div>
</div>
</main>
</div>
<script>
// Estado global
let currentFilter = 'all';
let searchQuery = '';
let allRaffles = [];

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    loadRaffles();
    loadHistory();

    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            searchQuery = e.target.value.toLowerCase().trim();
            renderRaffles(allRaffles);
        });
    }
});

// Cargar sorteos
async function loadRaffles() {
    const container = document.getElementById('rafflesGrid');
    
    try {
        const response = await fetch('api_ganadores.php?action=list_raffles');
        const result = await response.json();

        if (result.success) {
            allRaffles = result.data;
            renderRaffles(allRaffles);
        } else {
            container.innerHTML = `<div class="col-span-full text-center py-10 text-red-500">Error: ${result.error}</div>`;
        }
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = '<div class="col-span-full text-center py-10 text-red-500">Error de conexión</div>';
    }
}

// Cargar historial
async function loadHistory() {
    const tbody = document.getElementById('historyTableBody');
    try {
        const response = await fetch('api_ganadores.php?action=history');
        const result = await response.json();

        if (result.success && result.data.length > 0) {
            renderHistorialTable(result.data);
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-[#9da6b9]">No hay historial de ganadores reciente</td></tr>';
        }
    } catch (error) {
        console.error('Error historial:', error);
    }
}

// Renderizar tarjetas de sorteos
function renderRaffles(raffles) {
    const container = document.getElementById('rafflesGrid');
    
    const filtered = raffles.filter(r => {
        // Filtro texto
        if (searchQuery && !r.titulo.toLowerCase().includes(searchQuery)) return false;
        
        // Filtro estado tab
        if (currentFilter === 'pending') return r.estado_gestion === 'ready';
        if (currentFilter === 'completed') return r.estado_gestion === 'completed';
        return true;
    });

    if (filtered.length === 0) {
        container.innerHTML = '<div class="col-span-full text-center py-10 text-gray-500">No se encontraron sorteos con los filtros actuales.</div>';
        return;
    }

    container.innerHTML = filtered.map(r => createRaffleCard(r)).join('');
}

function createRaffleCard(r) {
    const percent = r.total_boletos > 0 ? Math.min(100, Math.round((r.vendidos / r.total_boletos) * 100)) : 0;
    
    // Status Badge Logic
    let statusBadge = '';
    let cardBorder = 'border-[#3e3e52] hover:border-primary/50';
    let overlay = '';
    
    if (r.estado_gestion === 'completed' && r.ganador) {
        statusBadge = '<div class="absolute top-3 right-3 bg-success text-white text-xs font-bold px-3 py-1 rounded-full shadow-md uppercase tracking-wide flex items-center gap-1"><span class="material-symbols-outlined text-sm">check_circle</span>Seleccionado</div>';
        cardBorder = 'border-success/30 shadow-success/5';
        
        overlay = `
        <div class="absolute bottom-4 left-4 flex items-center gap-3 z-10">
            <div class="size-12 rounded-full border-2 border-success bg-gray-700 bg-cover bg-center" style="background-image: url('${r.ganador.avatar || 'https://via.placeholder.com/150'}');"></div>
            <div>
                <p class="text-white font-bold text-lg leading-tight">${r.ganador.nombre}</p>
                <p class="text-success font-medium text-sm">Ganador</p>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 w-full h-full bg-gradient-to-t from-surface-dark via-surface-dark/60 to-transparent"></div>
        `;
    } else if (r.estado_gestion === 'ready') {
        statusBadge = '<div class="absolute top-3 right-3 bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-md uppercase tracking-wide">Finalizado</div>';
        overlay = '<div class="absolute bottom-0 left-0 w-full h-1/2 bg-gradient-to-t from-surface-dark to-transparent"></div>';
    } else {
        statusBadge = `<div class="absolute top-3 right-3 bg-blue-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-md uppercase tracking-wide">${r.estado_sorteo}</div>`;
        overlay = '<div class="absolute inset-0 bg-black/20"></div>'; // Dimmed
    }

    // Content Body Logic
    let contentBody = '';
    
    if (r.estado_gestion === 'completed' && r.ganador) {
        contentBody = `
            <div class="bg-[#1e1e2d] rounded-lg p-3 border border-[#3e3e52] flex items-center justify-between">
                <div class="flex flex-col">
                    <span class="text-[#9d9db9] text-xs">Boleto Ganador</span>
                    <span class="text-success text-2xl font-mono font-bold tracking-widest">#${r.ganador.boleto}</span>
                </div>
                <div class="size-10 rounded-full bg-success/20 flex items-center justify-center text-success">
                    <span class="material-symbols-outlined">emoji_events</span>
                </div>
            </div>
            <div class="mt-auto flex flex-col gap-2 pt-2">
                 <button onclick="notificarGanador('${r.id}', '${r.ganador.nombre}', this)" class="w-full h-10 bg-transparent hover:bg-white/5 text-primary font-medium rounded-lg flex items-center justify-center gap-2 transition-colors">
                    <span class="material-symbols-outlined text-sm">mail</span>
                    Reenviar Notificación
                </button>
            </div>
        `;
    } else if (r.estado_gestion === 'ready') {
        contentBody = `
            <div class="grid grid-cols-2 gap-3 py-2 border-y border-[#3e3e52]">
                <div class="flex flex-col">
                    <span class="text-[#9d9db9] text-xs uppercase font-semibold">Boletos</span>
                    <span class="text-white font-medium">${r.vendidos} / ${r.total_boletos}</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-[#9d9db9] text-xs uppercase font-semibold">Recaudado</span>
                    <span class="text-white font-medium">$${parseFloat(r.recaudado).toLocaleString('en-US')}</span>
                </div>
            </div>
            <div class="mt-auto pt-2">
                <button onclick="generarGanador('${r.id}', this)" class="w-full h-12 bg-primary hover:bg-blue-600 text-white font-semibold rounded-lg shadow-lg shadow-primary/20 flex items-center justify-center gap-2 transition-all transform active:scale-95 group">
                    <span class="material-symbols-outlined group-hover:animate-spin">autorenew</span>
                    Generar Ganador
                </button>
            </div>
        `;
    } else {
        // En curso o borrador (disable actions)
        contentBody = `
             <div class="w-full bg-[#1e1e2d] rounded-full h-2.5 mb-2">
                <div class="bg-blue-500 h-2.5 rounded-full" style="width: ${percent}%"></div>
            </div>
            <div class="flex justify-between text-xs text-[#9d9db9]">
                <span>${r.vendidos} vendidos</span>
                <span>${r.total_boletos} total</span>
            </div>
            <div class="mt-auto pt-2">
                <button class="w-full h-12 bg-[#34344a] text-gray-500 font-semibold rounded-lg border border-[#3e3e52] cursor-not-allowed flex items-center justify-center gap-2" disabled="">
                    <span class="material-symbols-outlined">schedule</span>
                    ${r.estado_sorteo === 'Activo' ? 'En Curso' : r.estado_sorteo}
                </button>
            </div>
        `;
    }

    return `
    <div class="raffle-card flex flex-col bg-surface-dark rounded-xl overflow-hidden border ${cardBorder} shadow-lg transition-all duration-300" data-status="${r.estado_gestion}">
        <div class="relative h-48 w-full bg-gray-800 ${r.estado_gestion === 'active' ? '' : ''}">
            <div class="absolute inset-0 bg-cover bg-center ${r.estado_gestion === 'active' ? 'opacity-60' : 'opacity-80'}" style='background-image: url("${r.imagen || 'https://via.placeholder.com/400'}");'></div>
            ${statusBadge}
            ${overlay}
        </div>
        <div class="p-5 flex flex-col flex-1 gap-4">
            <div>
                <h3 class="text-white text-xl font-bold mb-1">${r.titulo}</h3>
                <p class="text-[#9d9db9] text-sm">Cierra: ${new Date(r.fecha_fin).toLocaleDateString()}</p>
            </div>
            ${contentBody}
        </div>
    </div>
    `;
}

// Generar Ganador Action
async function generarGanador(id, btn) {
    if (!confirm('¿Estás seguro de que deseas generar un ganador aleatorio para este sorteo? Esta acción es irreversible.')) return;
    
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin">autorenew</span> Generando...';
    
    try {
        const response = await fetch('api_ganadores.php?action=generate', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id_sorteo: id })
        });
        const result = await response.json();
        
        if (result.success) {
            mostrarNotificacion('¡Ganador generado exitosamente!', 'success');
            // Recargar todo para actualizar la vista
            loadRaffles();
            loadHistory();
        } else {
            mostrarNotificacion(result.error || 'Error al generar ganador', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (e) {
        console.error(e);
        mostrarNotificacion('Error de conexión', 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// Notificar Mock
async function notificarGanador(id, nombre, btn) {
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin">autorenew</span> Enviando...';
    
    setTimeout(() => {
        mostrarNotificacion(`Notificación enviada a ${nombre}`, 'success');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }, 1000);
}


function renderHistorialTable(history) {
    const tbody = document.getElementById('historyTableBody');
    
    tbody.innerHTML = history.map(h => `
        <tr class="hover:bg-[#2c2c3e] transition-colors">
            <td class="px-6 py-4 font-medium text-white flex items-center gap-3">
                <div class="size-8 rounded bg-gray-700 bg-cover bg-center" style="background-image: ${h.imagen || "url('https://via.placeholder.com/40')"}"></div>
                ${h.raffleName}
            </td>
            <td class="px-6 py-4">${h.fecha}</td>
            <td class="px-6 py-4 flex items-center gap-2">
                <div class="size-6 rounded-full bg-primary/20 flex items-center justify-center text-primary text-xs font-bold border border-primary/30">
                    ${(h.ganador[0] || '?')}
                </div>
                <span class="text-white">${h.ganador}</span>
            </td>
            <td class="px-6 py-4 font-mono text-success">#${h.boleto}</td>
            <td class="px-6 py-4 text-right">
                <span class="inline-flex items-center rounded-md bg-green-400/10 px-2 py-1 text-xs font-medium text-green-400 ring-1 ring-inset ring-green-400/20">${h.estado}</span>
            </td>
        </tr>
    `).join('');
}


// Filtros visuales
function aplicarFiltro(filter) {
    currentFilter = filter;
    document.querySelectorAll('.filter-btn').forEach(btn => {
        if (btn.dataset.filter === filter) {
            btn.classList.add('bg-primary/20', 'text-primary', 'border-primary/30');
            btn.classList.remove('bg-surface-dark', 'text-[#9d9db9]', 'border-[#3e3e52]');
        } else {
            btn.classList.remove('bg-primary/20', 'text-primary', 'border-primary/30');
            btn.classList.add('bg-surface-dark', 'text-[#9d9db9]', 'border-[#3e3e52]');
        }
    });
    renderRaffles(allRaffles);
}

function navegarAtras() { window.history.back(); }

function mostrarNotificacion(mensaje, tipo = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 ${
        tipo === 'success' ? 'bg-green-500 text-white' : 
        tipo === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    notification.style.opacity = '0';
    notification.style.transform = 'translateX(100%)';
    notification.style.transition = 'all 0.3s ease-in-out';
    
    notification.innerHTML = `
        <span class="material-symbols-outlined">${tipo === 'success' ? 'check_circle' : tipo === 'error' ? 'error' : 'info'}</span>
        <span class="font-medium">${mensaje}</span>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
    }, 10);

    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function exportarHistorialGanadoresCSV() {
    alert("Función disponible próximamente");
}
</script>
</body></html>