<?php
/**
 * ZonaPeligroUsuario
 * Sistema de Sorteos Web - Administrador
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación y rol de administrador
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true || ($_SESSION['usuario_rol'] ?? '') !== 'Administrador') {
    header('Location: ../cliente/InicioSesion.php');
    exit;
}

require_once 'config.php';
?>
<!DOCTYPE html>

<html class="dark" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Zona de Peligro Admin: Usuario</title>
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<!-- Material Symbols -->
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<!-- Theme Config -->
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#ef4343",
                        "background-light": "#f8f6f6",
                        "background-dark": "#221010",
                        "surface-dark": "#2f1b1b",
                        "surface-dark-lighter": "#3e2525"
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
        }
        ::-webkit-scrollbar-track {
            background: #221010; 
        }
        ::-webkit-scrollbar-thumb {
            background: #3e2525; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #ef4343; 
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white overflow-x-hidden min-h-screen flex flex-col">
<!-- Main Layout Wrapper -->
<div class="flex flex-1 h-screen overflow-hidden">
<!-- Sidebar -->
<aside class="w-64 flex-shrink-0 border-r border-surface-dark-lighter bg-background-dark hidden md:flex flex-col">
<div class="h-full flex flex-col justify-between p-4">
<div class="flex flex-col gap-6">
<!-- User Profile Snippet in Sidebar -->
<div class="flex gap-3 items-center px-2">
<div class="bg-center bg-no-repeat bg-cover rounded-full size-10 border border-surface-dark-lighter" data-alt="Admin profile picture placeholder" style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuDbB9BWmZJ1F3kxqhX_l1JU4I5hhim5igs6v5p4-3wGHHZtUZ7FjODp2XONXB1cyzpktniZoj4o5RU4v7PT8EZljZxTw2tB0UptMt4AwvT-4U-rCMe7wrma6iyn59EXliwoWubAxyC43oLQRENawqWGZaFVF93ZMfUjWMqvpysVxe28L5frMqBswuuwXewnsL0Pz1cweRn_Um7UviYi1yD8VxPYfDQEkxlzYhQiONW29FvAFqmyiKBJTbLjOk7jmEz8kbv2FXsrsUM");'></div>
<div class="flex flex-col">
<h1 class="text-white text-base font-bold leading-normal">Administrador</h1>
<p class="text-[#b99d9d] text-xs font-normal leading-normal">Gestión Global</p>
</div>
</div>
<!-- Navigation Links -->
<nav class="flex flex-col gap-2">
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#b99d9d] hover:bg-surface-dark hover:text-white transition-colors" href="#">
<span class="material-symbols-outlined text-[20px]">dashboard</span>
<span class="text-sm font-medium">Resumen</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-surface-dark border border-surface-dark-lighter text-white" href="#">
<span class="material-symbols-outlined text-[20px] fill-1">group</span>
<span class="text-sm font-medium">Usuarios</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#b99d9d] hover:bg-surface-dark hover:text-white transition-colors" href="#">
<span class="material-symbols-outlined text-[20px]">confirmation_number</span>
<span class="text-sm font-medium">Boletos</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#b99d9d] hover:bg-surface-dark hover:text-white transition-colors" href="#">
<span class="material-symbols-outlined text-[20px]">emoji_events</span>
<span class="text-sm font-medium">Ganadores</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#b99d9d] hover:bg-surface-dark hover:text-white transition-colors" href="#">
<span class="material-symbols-outlined text-[20px]">settings</span>
<span class="text-sm font-medium">Ajustes</span>
</a>
</nav>
</div>
<div class="px-2">
<button class="flex items-center gap-2 text-[#b99d9d] hover:text-primary transition-colors text-sm font-medium">
<span class="material-symbols-outlined text-[20px]">logout</span>
                        Cerrar Sesión
                   </button>
</div>
</div>
</aside>
<!-- Main Content -->
<main class="flex-1 flex flex-col h-full overflow-hidden bg-background-dark relative">
<!-- Top Navbar -->
<header class="h-16 flex items-center justify-between px-6 border-b border-surface-dark-lighter bg-background-dark/95 backdrop-blur z-20 sticky top-0">
<div class="flex items-center gap-4">
<span class="material-symbols-outlined text-primary text-3xl">local_fire_department</span>
<h2 class="text-white text-lg font-bold tracking-tight">Admin Panel | Sorteos</h2>
</div>
<div class="flex items-center gap-6">
<!-- Search Bar -->
<div class="hidden sm:flex items-center h-10 w-64 bg-surface-dark rounded-lg border border-surface-dark-lighter focus-within:border-primary/50 transition-colors">
<span class="material-symbols-outlined text-[#b99d9d] px-3">search</span>
<input class="bg-transparent border-none text-white placeholder-[#b99d9d] text-sm w-full focus:ring-0 h-full" placeholder="Buscar..." type="text"/>
</div>
<button class="relative p-2 text-[#b99d9d] hover:text-white transition-colors">
<span class="material-symbols-outlined">notifications</span>
<span class="absolute top-1.5 right-1.5 size-2 bg-primary rounded-full"></span>
</button>
</div>
</header>
<!-- Scrollable Content Area -->
<div class="flex-1 overflow-y-auto p-6 md:p-10">
<div class="max-w-5xl mx-auto flex flex-col gap-8">
<!-- Breadcrumbs -->
<nav class="flex items-center text-sm font-medium text-[#b99d9d]">
<a class="hover:text-white transition-colors" href="#">Usuarios</a>
<span class="mx-2">/</span>
<span class="text-white">Detalles de Usuario: Juan Pérez</span>
</nav>
<!-- Page Heading & User Summary -->
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-surface-dark-lighter pb-6">
<div class="flex gap-5 items-start">
<div class="bg-surface-dark rounded-xl size-20 md:size-24 bg-cover bg-center border-2 border-surface-dark-lighter shadow-lg flex-shrink-0" data-alt="User profile picture big" style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuDw-O5oOnF7Q_uwxHo3d9wm2lWDBf0q-VK15cBL-Id4CZbZPA7P2yngfo16dotE76cMQYRQlo7kgYKSe_KQkaEJTypdNpnpuWcDVKSlT1wGeiAzCAyKDN0O3eh8lPDNiqKGui8JNkZvnN7jLgIVTYCIQyjKuvSYkz_L_Ph9UKNJbyfAy-YYVVnbiXzD6BBl1O62v1qU2EgXChmyM_zjjZDGq7FeVIAhLbvfid04RvRUq-8EuRxeuTTFbFLvCX7841cu1uro6wbOWPc");'></div>
<div class="flex flex-col gap-1">
<h1 class="text-3xl font-bold text-white tracking-tight">Juan Pérez</h1>
<p class="text-[#b99d9d]">ID: <span id="userId" class="text-white font-mono">#928374</span> • Miembro desde Ene 2023</p>
<div class="flex gap-2 mt-2">
<span id="estadoUsuario" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/10 text-green-500 border border-green-500/20">
                                        Activo
                                    </span>
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-500/10 text-blue-500 border border-blue-500/20">
                                        Verificado
                                    </span>
</div>
</div>
</div>
<div class="flex gap-3">
<button class="h-9 px-4 rounded-lg bg-surface-dark hover:bg-surface-dark-lighter border border-surface-dark-lighter text-white text-sm font-medium transition-colors">
                                Ver Boletos
                            </button>
<button class="h-9 px-4 rounded-lg bg-white text-background-dark hover:bg-gray-200 text-sm font-bold transition-colors shadow-sm">
                                Editar Perfil
                            </button>
</div>
</div>
<!-- DANGER ZONE SECTION -->
<section class="mt-4 rounded-xl border border-primary/30 bg-primary/5 overflow-hidden">
<!-- Header -->
<div class="px-6 py-5 border-b border-primary/20 flex items-center gap-3 bg-gradient-to-r from-primary/10 to-transparent">
<div class="p-2 bg-primary/20 rounded-lg text-primary">
<span class="material-symbols-outlined">warning</span>
</div>
<div>
<h3 class="text-xl font-bold text-white">Zona de Peligro</h3>
<p class="text-[#b99d9d] text-sm">Acciones críticas que afectan el acceso del usuario a la plataforma.</p>
</div>
</div>
<div class="p-6 md:p-8 flex flex-col gap-8">
<!-- Action 1: Temporary Block -->
<div class="flex flex-col lg:flex-row gap-6 items-start lg:items-center justify-between pb-8 border-b border-surface-dark-lighter">
<div class="flex-1 max-w-xl">
<h4 class="text-white text-lg font-semibold mb-1 flex items-center gap-2">
<span class="material-symbols-outlined text-orange-400">timelapse</span>
                                        Bloqueo Temporal
                                    </h4>
<p class="text-[#b99d9d] text-sm mb-4">Suspende el acceso del usuario a la compra de boletos y participación en sorteos por un tiempo determinado.</p>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div class="flex flex-col gap-1.5">
<label class="text-xs font-medium text-[#b99d9d] uppercase">Duración</label>
<div class="relative">
<select id="duracionTemporal" class="w-full bg-surface-dark border border-surface-dark-lighter text-white text-sm rounded-lg focus:ring-primary focus:border-primary p-2.5 appearance-none">
<option value="">Seleccionar duración</option>
<option value="24h">24 Horas</option>
<option value="3d">3 Días</option>
<option value="1w">1 Semana</option>
<option value="1m">1 Mes</option>
</select>
<span class="material-symbols-outlined absolute right-3 top-2.5 text-[#b99d9d] pointer-events-none">expand_more</span>
</div>
</div>
<div class="flex flex-col gap-1.5">
<label class="text-xs font-medium text-[#b99d9d] uppercase">Razón (Opcional)</label>
<input id="razonTemporal" class="w-full bg-surface-dark border border-surface-dark-lighter text-white text-sm rounded-lg focus:ring-primary focus:border-primary p-2.5 placeholder:text-white/20" placeholder="Ej. Comportamiento sospechoso" type="text"/>
</div>
</div>
</div>
<div class="flex-shrink-0 pt-4 lg:pt-0">
<button id="btnBloquearTemporal" class="flex items-center gap-2 px-5 py-2.5 rounded-lg border border-orange-500/30 text-orange-400 hover:bg-orange-500/10 hover:border-orange-500 transition-all font-medium text-sm">
<span class="material-symbols-outlined text-[20px]">block</span>
                                        Bloquear Temporalmente
                                    </button>
</div>
</div>
<!-- Action 2: Permanent Ban -->
<div class="flex flex-col lg:flex-row gap-6 items-start justify-between">
<div class="flex-1 w-full">
<h4 class="text-white text-lg font-semibold mb-1 flex items-center gap-2">
<span class="material-symbols-outlined text-primary">gavel</span>
                                        Baneo Permanente
                                    </h4>
<p class="text-[#b99d9d] text-sm mb-5">Esta acción eliminará permanentemente el acceso del usuario. Se requiere una justificación obligatoria.</p>
<div class="flex flex-col gap-4 max-w-2xl bg-surface-dark/50 p-4 rounded-xl border border-surface-dark-lighter">
<div class="flex flex-col gap-1.5">
<label class="text-xs font-medium text-primary uppercase flex justify-between">
<span>Motivo de la Sanción <span class="text-primary">*</span></span>
<span class="text-[#b99d9d] font-normal lowercase">obligatorio</span>
</label>
<textarea id="razonPermanente" class="w-full bg-background-dark border border-surface-dark-lighter text-white text-sm rounded-lg focus:ring-primary focus:border-primary p-3 placeholder:text-white/20 resize-none" placeholder="Describe detalladamente el motivo del baneo..." rows="3" required></textarea>
</div>
<label class="flex items-start gap-3 cursor-pointer group">
<input id="confirmarPermanente" class="mt-1 w-4 h-4 rounded bg-background-dark border-surface-dark-lighter text-primary focus:ring-offset-background-dark focus:ring-primary" type="checkbox"/>
<span class="text-sm text-[#b99d9d] group-hover:text-white transition-colors">
                                                Confirmo que he revisado la actividad de este usuario y entiendo que esta acción es <strong>irreversible</strong>.
                                            </span>
</label>
</div>
</div>
<div class="flex-shrink-0 flex items-end lg:h-full pt-4">
<button id="btnBanearPermanente" class="flex items-center justify-center w-full sm:w-auto gap-2 px-6 py-3 rounded-lg bg-primary hover:bg-red-600 text-white shadow-lg shadow-primary/20 hover:shadow-primary/40 transition-all font-bold text-sm transform active:scale-95">
<span class="material-symbols-outlined text-[20px]">delete_forever</span>
                                        Banear Usuario
                                    </button>
</div>
</div>
</div>
</section>
<!-- Activity Log (Context for Admins) -->
<section class="mb-10">
<h3 class="text-white font-bold text-lg mb-4 px-1">Actividad Reciente</h3>
<div class="bg-surface-dark rounded-xl border border-surface-dark-lighter overflow-hidden">
<table class="w-full text-left text-sm text-[#b99d9d]">
<thead class="bg-surface-dark-lighter text-white font-medium uppercase text-xs">
<tr>
<th class="px-6 py-3">Acción</th>
<th class="px-6 py-3">Detalle</th>
<th class="px-6 py-3">Fecha</th>
<th class="px-6 py-3">IP</th>
</tr>
</thead>
<tbody class="divide-y divide-surface-dark-lighter">
<tr class="hover:bg-white/5 transition-colors">
<td class="px-6 py-4 flex items-center gap-2">
<span class="material-symbols-outlined text-green-500 text-[18px]">shopping_cart</span>
                                            Compra
                                        </td>
<td class="px-6 py-4">Boleto #8821 - Sorteo iPhone 15</td>
<td class="px-6 py-4">Hace 2 horas</td>
<td class="px-6 py-4 font-mono text-xs">192.168.1.42</td>
</tr>
<tr class="hover:bg-white/5 transition-colors">
<td class="px-6 py-4 flex items-center gap-2">
<span class="material-symbols-outlined text-blue-500 text-[18px]">login</span>
                                            Login
                                        </td>
<td class="px-6 py-4">Inicio de sesión exitoso</td>
<td class="px-6 py-4">Ayer, 14:30 PM</td>
<td class="px-6 py-4 font-mono text-xs">192.168.1.42</td>
</tr>
<tr class="hover:bg-white/5 transition-colors">
<td class="px-6 py-4 flex items-center gap-2">
<span class="material-symbols-outlined text-orange-400 text-[18px]">warning</span>
                                            Reporte
                                        </td>
<td class="px-6 py-4 text-white">Reportado por Usuario #112 (Spam en chat)</td>
<td class="px-6 py-4">20 Oct, 2023</td>
<td class="px-6 py-4 font-mono text-xs">-</td>
</tr>
</tbody>
</table>
</div>
</section>
</div>
</div>
</main>
</div>
<!-- Simulated Modal (Hidden by default, visualized in code structure) -->
<!-- 
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm hidden">
        <div class="bg-surface-dark border border-surface-dark-lighter rounded-xl p-6 max-w-md w-full shadow-2xl scale-100">
            <div class="flex items-center gap-3 text-primary mb-4">
                <span class="material-symbols-outlined text-3xl">report</span>
                <h3 class="text-xl font-bold text-white">¿Banear permanentemente?</h3>
            </div>
            <p class="text-[#b99d9d] mb-6">Estás a punto de banear a <span class="text-white font-bold">Juan Pérez</span>. Esta acción no se puede deshacer y el usuario perderá todos sus boletos activos.</p>
            <div class="flex justify-end gap-3">
                <button class="px-4 py-2 rounded-lg text-white hover:bg-white/10 transition-colors">Cancelar</button>
                <button class="px-4 py-2 rounded-lg bg-primary hover:bg-red-600 text-white font-bold shadow-lg shadow-primary/20">Confirmar Baneo</button>
            </div>
        </div>
    </div>
    -->
    <script src="../js/zona-peligro-usuario.js"></script>
</body></html>
