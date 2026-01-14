<?php
/**
 * MisBoletosCliente
 * Sistema de Sorteos Web
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación para páginas protegidas
$protectedPages = ['DashboardCliente', 'AjustesPefilCliente', 'MisBoletosCliente', 'MisGanancias', 'SeleccionBoletos', 'SorteoClienteDetalles', 'FinalizarPagoBoletos'];
if (in_array('MisBoletosCliente', $protectedPages) && (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true)) {
    header('Location: InicioSesion.php');
    exit;
}
?>
<!DOCTYPE html>

<html class="dark" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Consulta de Boletos Cliente</title>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
<body class="bg-background-light dark:bg-background-dark font-display overflow-hidden h-screen flex text-white">
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
<h1 id="sidebar-user-name" class="text-white text-sm font-bold truncate tracking-tight">Juan Pérez</h1>
<p id="sidebar-user-type" class="text-primary/80 text-xs font-medium truncate">Usuario Premium</p>
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
<a id="nav-boletos" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-gradient-to-r from-primary to-blue-600 text-white shadow-lg shadow-primary/20 transition-all duration-200 hover:shadow-xl hover:shadow-primary/30" href="MisBoletosCliente.php">
<span class="material-symbols-outlined text-xl">confirmation_number</span>
<p class="text-sm font-bold">Mis Boletos</p>
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
<!-- Mobile Menu Toggle -->
<button id="mobile-menu-toggle" class="lg:hidden text-white mr-4" aria-label="Abrir menú de navegación">
<span class="material-symbols-outlined">menu</span>
</button>
<!-- Page Title -->
<h1 class="text-xl font-bold text-white hidden sm:block">Mis Boletos</h1>
<div class="ml-auto"></div>
</header>
<!-- Scrollable Content Area -->
<div class="flex-1 overflow-y-auto overflow-x-hidden p-6 lg:p-10">
<div class="flex flex-col gap-8">
<!-- Breadcrumbs -->
<div class="flex flex-wrap gap-2 items-center text-sm">
<a class="text-text-secondary hover:text-primary transition-colors" href="DashboardCliente.php">Inicio</a>
<span class="material-symbols-outlined text-base text-text-secondary">chevron_right</span>
<span class="text-white font-medium">Mis Boletos</span>
</div>
<!-- Page Heading -->
<div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
<div class="flex flex-col gap-2">
<h1 class="text-3xl md:text-4xl font-black tracking-tight text-white">Mis Boletos</h1>
<p class="text-text-secondary text-base max-w-2xl">Consulta el estado de tus participaciones, descarga tus comprobantes y verifica si eres un ganador.</p>
</div>
</div>
<!-- Filters & Search Toolbar -->
<div class="w-full bg-card-dark border border-[#282d39] rounded-xl p-4 shadow-sm">
<div class="flex flex-col lg:flex-row gap-4">
<!-- Search -->
<div class="flex-1">
<label class="flex flex-col gap-1.5 w-full">
<span class="text-xs font-semibold uppercase text-text-secondary tracking-wider">Buscar</span>
<div class="relative group">
<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-secondary group-focus-within:text-primary transition-colors">
<span class="material-symbols-outlined">search</span>
</div>
<input class="w-full h-11 pl-10 pr-4 bg-[#111318] border border-[#282d39] rounded-lg text-sm text-white placeholder:text-text-secondary focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all" placeholder="Buscar por número de boleto..." type="text"/>
</div>
</label>
</div>
<!-- Filter: Raffle -->
<div class="w-full lg:w-64">
<label class="flex flex-col gap-1.5 w-full">
<span class="text-xs font-semibold uppercase text-text-secondary tracking-wider">Sorteo</span>
<div class="relative">
<select class="w-full h-11 pl-3 pr-10 bg-[#111318] border border-[#282d39] rounded-lg text-sm text-white focus:ring-2 focus:ring-primary/50 focus:border-primary appearance-none cursor-pointer">
<option value="">Todos los sorteos</option>
<option value="iphone">Gran Sorteo iPhone 15</option>
<option value="car">Automóvil 2024</option>
<option value="cash">Bono en Efectivo</option>
</select>
<div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-text-secondary">
<span class="material-symbols-outlined">expand_more</span>
</div>
</div>
</label>
</div>
<!-- Filter: Status -->
<div class="w-full lg:w-48">
<label class="flex flex-col gap-1.5 w-full">
<span class="text-xs font-semibold uppercase text-text-secondary tracking-wider">Estado</span>
<div class="relative">
<select class="w-full h-11 pl-3 pr-10 bg-[#111318] border border-[#282d39] rounded-lg text-sm text-white focus:ring-2 focus:ring-primary/50 focus:border-primary appearance-none cursor-pointer">
<option value="">Todos</option>
<option value="winner">Ganadores</option>
<option value="approved">Aprobados</option>
<option value="pending">Pendientes</option>
<option value="rejected">Rechazados</option>
</select>
<div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-text-secondary">
<span class="material-symbols-outlined">expand_more</span>
</div>
</div>
</label>
</div>
</div>
</div>
<!-- Tickets Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
<!-- Card 1: Winner -->
<div class="group relative bg-card-dark rounded-xl border-2 border-amber-500/50 shadow-lg shadow-amber-500/10 overflow-hidden flex flex-col">
<div class="absolute top-0 right-0 bg-amber-500 text-black text-xs font-bold px-3 py-1 rounded-bl-lg z-10 flex items-center gap-1">
<span class="material-symbols-outlined text-sm">emoji_events</span> GANADOR
                    </div>
<div class="p-5 flex items-start gap-4 border-b border-[#282d39] bg-gradient-to-r from-amber-500/10 to-transparent">
<div class="size-16 rounded-lg bg-gray-800 shrink-0 overflow-hidden">
<img alt="Car Raffle Prize" class="w-full h-full object-cover" data-alt="Red sports car side view" src="https://lh3.googleusercontent.com/aida-public/AB6AXuArzq9r1TXy4_0Fjg6nrYz6ldHJWWYI1FcXyDnkNMYh0wLKkdnVRnaVpv_cFdZah7xLd6PlgicJWix4ju2Vlhduw8wJqZ90LDvApj8VGmQm8EZjKCuW7TBM15SF4CY0hAzri9TAvqGKraStMnOpQwAIzBGUZb-jxSff_7TC9QrP7O6LCjgRzugXhxCBWB5pexXg3NTNBlA5vEzPj6GfH3Oy2Ns38TQJ_tubPya33GYIGfT0ILVgL-2czux_KGShnhuwqYvGzaXzbfU"/>
</div>
<div class="flex flex-col">
<h3 class="font-bold text-white line-clamp-1">Gran Sorteo Automóvil 2024</h3>
<p class="text-xs text-text-secondary mt-1">Sorteado el: 15 Oct, 2023</p>
</div>
</div>
<div class="p-5 flex flex-col gap-4 flex-1">
<div class="flex justify-between items-center">
<span class="text-sm font-medium text-text-secondary">Boleto #</span>
<span class="font-mono text-2xl font-bold tracking-wider text-amber-500">77777</span>
</div>
<div class="mt-auto pt-4 flex gap-3">
<button class="flex-1 bg-amber-500 hover:bg-amber-600 text-black font-bold h-10 rounded-lg flex items-center justify-center gap-2 transition-colors text-sm">
<span class="material-symbols-outlined text-lg">celebration</span>
                                Reclamar Premio
                            </button>
<button class="size-10 rounded-lg border border-[#282d39] flex items-center justify-center hover:bg-[#353b4b] transition-colors text-text-secondary">
<span class="material-symbols-outlined">visibility</span>
</button>
</div>
</div>
</div>
<!-- Card 2: Approved -->
<div class="group bg-card-dark rounded-xl border border-[#282d39] overflow-hidden flex flex-col hover:border-[#3b4254] transition-colors">
<div class="p-5 flex items-start gap-4 border-b border-[#282d39]">
<div class="size-16 rounded-lg bg-gray-800 shrink-0 overflow-hidden">
<img alt="Smartphone Prize" class="w-full h-full object-cover" data-alt="Modern smartphone showing home screen" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDQvXPcM1Ldf-aXSI8gyokTpfgsNhOsI1lg6PqSR4bPs2MRdwnkctnAIqpU3O9VEBM2d8wv9ajMd7i23TplQC2a3M-vSRAA4HjK9q_uqNBE-Y7P4OEiCmkPX9KAlj0sfN_5cc8wrAoVuwsaSVUp2u9VnRkHrYh3JKb3LI1Q_sMGCa3GOC1Y7GPl1kyup_CHieZJSz1R41VNae_LlrQ9qTPW2CjZ2bmffb03ntY0aKv0qrsMO-PqUWslmMXZlGHFYRrL9pOMt5YRgZc"/>
</div>
<div class="flex flex-col">
<h3 class="font-bold text-white line-clamp-1">iPhone 15 Pro Max</h3>
<p class="text-xs text-text-secondary mt-1">Compra: 20 Oct, 2023</p>
<div class="mt-2 inline-flex">
<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/10 text-green-400 border border-green-500/20">
<span class="size-1.5 rounded-full bg-green-500"></span>
                                    Aprobado
                                </span>
</div>
</div>
</div>
<div class="p-5 flex flex-col gap-4 flex-1">
<div class="flex justify-between items-center bg-[#111318] p-3 rounded-lg border border-dashed border-[#282d39]">
<span class="text-sm font-medium text-text-secondary">Número de Boleto</span>
<span class="font-mono text-xl font-bold text-white">19204</span>
</div>
<div class="mt-auto flex gap-3">
<button class="flex-1 bg-[#282d39] border border-[#282d39] hover:border-primary hover:text-primary text-gray-300 font-medium h-10 rounded-lg flex items-center justify-center gap-2 transition-colors text-sm">
<span class="material-symbols-outlined text-lg">download</span>
                                Comprobante
                            </button>
<button class="size-10 rounded-lg border border-[#282d39] flex items-center justify-center hover:bg-[#353b4b] transition-colors text-text-secondary">
<span class="material-symbols-outlined">visibility</span>
</button>
</div>
</div>
</div>
<!-- Card 3: Pending -->
<div class="group bg-card-dark rounded-xl border border-[#282d39] overflow-hidden flex flex-col hover:border-[#3b4254] transition-colors">
<div class="p-5 flex items-start gap-4 border-b border-[#282d39]">
<div class="size-16 rounded-lg bg-gray-800 shrink-0 overflow-hidden">
<img alt="Gift Box Prize" class="w-full h-full object-cover" data-alt="Wrapped gift box with red ribbon" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCluC6AkX5FyVDrd5cANYFHXQZlrIWfaSXoMb73bzb6stNOf-nGoQls_4P_mF6y4yFHJhuCXz2SICFKM6nshdwptyBwLh-uXD3vpNJPC71mEe0Rlg92cOFwxBE0-UmK74Y6-7Dv1ewaEzYkn7sIOqp82pBZcMhqp-hekm5KrJg9YEH0FG8XRnWPBivMxDiop_6gUkARoje3LJv3qK7UkJnJKOOHaCyOnfL7badlxb0dv4D9A8itM4xrjplh8_U02UFQhao9CxqHhsY"/>
</div>
<div class="flex flex-col">
<h3 class="font-bold text-white line-clamp-1">Bono Navideño $50k</h3>
<p class="text-xs text-text-secondary mt-1">Compra: Hoy, 10:30 AM</p>
<div class="mt-2 inline-flex">
<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">
<span class="size-1.5 rounded-full bg-yellow-500 animate-pulse"></span>
                                    Validando Pago
                                </span>
</div>
</div>
</div>
<div class="p-5 flex flex-col gap-4 flex-1">
<div class="flex justify-between items-center bg-[#111318] p-3 rounded-lg border border-dashed border-[#282d39] opacity-75">
<span class="text-sm font-medium text-text-secondary">Número de Boleto</span>
<span class="font-mono text-xl font-bold text-white">---</span>
</div>
<div class="mt-auto flex gap-3">
<div class="flex-1 h-10 flex items-center text-xs text-yellow-400 bg-yellow-900/10 rounded-lg px-3">
                                Tu boleto se asignará al confirmar el pago.
                             </div>
<button class="size-10 rounded-lg border border-[#282d39] flex items-center justify-center hover:bg-[#353b4b] transition-colors text-text-secondary">
<span class="material-symbols-outlined">help</span>
</button>
</div>
</div>
</div>
<!-- Card 4: Rejected -->
<div class="group bg-card-dark rounded-xl border border-[#282d39] overflow-hidden flex flex-col opacity-75 hover:opacity-100 transition-opacity">
<div class="p-5 flex items-start gap-4 border-b border-[#282d39]">
<div class="size-16 rounded-lg bg-gray-800 shrink-0 overflow-hidden grayscale">
<img alt="Headphones Prize" class="w-full h-full object-cover" data-alt="Black wireless headphones" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDn5K32ks87zAiWlsAhufuEbmg-1i1jUa7R32YuclUJsLX0ziWKBNEInzT5y0Uxtbq2RAqOkAHUwh93tce466NqBPzV8sHhFQ_RxpgNrkUr5PExO-mSa4SM53QJGhjxT5Bucr0ujHMSQzCqib1KbRiKsnukzCaXcpmsCDTqS94DI0ad9l7EOVurnb1broKUFAp4a9i0nM9yUnZ7v0mximv6Kyte6WxXnfUhrDQBXRX2Nl70ZXw4mvQQTeH4a0ZJ-vzBE3qJPFruTjc"/>
</div>
<div class="flex flex-col">
<h3 class="font-bold text-white line-clamp-1">Rifa Express: Auriculares</h3>
<p class="text-xs text-text-secondary mt-1">Compra: 10 Oct, 2023</p>
<div class="mt-2 inline-flex">
<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20">
<span class="material-symbols-outlined text-[14px]">cancel</span>
                                    Rechazado
                                </span>
</div>
</div>
</div>
<div class="p-5 flex flex-col gap-4 flex-1">
<div class="bg-red-900/10 p-3 rounded-lg border border-red-900/30">
<p class="text-xs text-red-400 leading-relaxed">
<strong>Motivo:</strong> El comprobante de pago no es legible o no corresponde al monto del sorteo.
                            </p>
</div>
<div class="mt-auto flex gap-3">
<button class="flex-1 bg-[#282d39] border border-[#282d39] hover:border-red-500 hover:text-red-500 text-gray-300 font-medium h-10 rounded-lg flex items-center justify-center gap-2 transition-colors text-sm">
                                Contactar Soporte
                            </button>
</div>
</div>
</div>
<!-- Card 5: Approved -->
<div class="group bg-card-dark rounded-xl border border-[#282d39] overflow-hidden flex flex-col hover:border-[#3b4254] transition-colors">
<div class="p-5 flex items-start gap-4 border-b border-[#282d39]">
<div class="size-16 rounded-lg bg-gray-800 shrink-0 overflow-hidden">
<img alt="Travel Prize" class="w-full h-full object-cover" data-alt="Beach with palm trees" src="https://lh3.googleusercontent.com/aida-public/AB6AXuD8QxsRdmmk4lqOuAeEVnvWi0ePeUjW8ibVEoarA70W6U-ULTS_UZeTDYa626Rr71382c3MflyvCEP08yud_ZhK-3fiCe-p8BKQ99teYOn7KAB-E45V1aO_ijuyXPYc3eTcTfxQi-ef_aV86_aGi5SF3DHVlfMeLamIZp2PI2oTvKqMQMA4TKS_D82zVhm3xj-cM7aeob-JhAsFci8jmnm4S536GlHTZxJC0GprSDa3URxuym-_tPCEkYU1wRJS6grTpS3MyjVBweo"/>
</div>
<div class="flex flex-col">
<h3 class="font-bold text-white line-clamp-1">Viaje Todo Incluido</h3>
<p class="text-xs text-text-secondary mt-1">Compra: 05 Oct, 2023</p>
<div class="mt-2 inline-flex">
<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/10 text-green-400 border border-green-500/20">
<span class="size-1.5 rounded-full bg-green-500"></span>
                                    Aprobado
                                </span>
</div>
</div>
</div>
<div class="p-5 flex flex-col gap-4 flex-1">
<div class="flex justify-between items-center bg-[#111318] p-3 rounded-lg border border-dashed border-[#282d39]">
<span class="text-sm font-medium text-text-secondary">Número de Boleto</span>
<span class="font-mono text-xl font-bold text-white">45100</span>
</div>
<div class="mt-auto flex gap-3">
<button class="flex-1 bg-[#282d39] border border-[#282d39] hover:border-primary hover:text-primary text-gray-300 font-medium h-10 rounded-lg flex items-center justify-center gap-2 transition-colors text-sm">
<span class="material-symbols-outlined text-lg">download</span>
                                Comprobante
                            </button>
<button class="size-10 rounded-lg border border-[#282d39] flex items-center justify-center hover:bg-[#353b4b] transition-colors text-text-secondary">
<span class="material-symbols-outlined">visibility</span>
</button>
</div>
</div>
</div>
<!-- Card 6: Approved -->
<div class="group bg-card-dark rounded-xl border border-[#282d39] overflow-hidden flex flex-col hover:border-[#3b4254] transition-colors">
<div class="p-5 flex items-start gap-4 border-b border-[#282d39]">
<div class="size-16 rounded-lg bg-gray-800 shrink-0 overflow-hidden">
<img alt="Electronics Prize" class="w-full h-full object-cover" data-alt="Circuit board pattern blue light" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAOwQrbXGVjOXIM-wlpfjA4igoTseC8YHhgFPBi77_mbl7YhI1BZUviBx948aMAMJUGRQZNtiAC8Yz_l4guEie4tTyp9hWQ5nBAzFi3c1s5sPNTFvo0IZa4N1Tk37soleogeUmHn6N9IrYJR8yo3LeamYE19Ovnuqkv7pSoUEF3CjBjHzWnek8_LcVad4U2ZfWyyTTlLK2gET4gZcAzpB8U5ZfvNzmsi15Ie--7BN6HIUlgB3ER5OIPfcOSXOrEYttexm3bZUbf9V4"/>
</div>
<div class="flex flex-col">
<h3 class="font-bold text-white line-clamp-1">Kit Gamer Ultimate</h3>
<p class="text-xs text-text-secondary mt-1">Compra: 01 Oct, 2023</p>
<div class="mt-2 inline-flex">
<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/10 text-green-400 border border-green-500/20">
<span class="size-1.5 rounded-full bg-green-500"></span>
                                    Aprobado
                                </span>
</div>
</div>
</div>
<div class="p-5 flex flex-col gap-4 flex-1">
<div class="flex justify-between items-center bg-[#111318] p-3 rounded-lg border border-dashed border-[#282d39]">
<span class="text-sm font-medium text-text-secondary">Número de Boleto</span>
<span class="font-mono text-xl font-bold text-white">12932</span>
</div>
<div class="mt-auto flex gap-3">
<button class="flex-1 bg-[#282d39] border border-[#282d39] hover:border-primary hover:text-primary text-gray-300 font-medium h-10 rounded-lg flex items-center justify-center gap-2 transition-colors text-sm">
<span class="material-symbols-outlined text-lg">download</span>
                                Comprobante
                            </button>
<button class="size-10 rounded-lg border border-[#282d39] flex items-center justify-center hover:bg-[#353b4b] transition-colors text-text-secondary">
<span class="material-symbols-outlined">visibility</span>
</button>
</div>
</div>
</div>
</div>
<!-- Pagination -->
<div class="flex items-center justify-center gap-2 pt-4">
<button class="flex items-center justify-center size-10 rounded-lg border border-[#282d39] text-text-secondary hover:bg-[#353b4b] disabled:opacity-50 transition-colors">
<span class="material-symbols-outlined">chevron_left</span>
</button>
<button class="flex items-center justify-center size-10 rounded-lg bg-primary text-white font-medium hover:bg-blue-600 transition-colors">1</button>
<button class="flex items-center justify-center size-10 rounded-lg border border-[#282d39] text-text-secondary hover:bg-[#353b4b] transition-colors">2</button>
<button class="flex items-center justify-center size-10 rounded-lg border border-[#282d39] text-text-secondary hover:bg-[#353b4b] transition-colors">3</button>
<span class="text-text-secondary px-1">...</span>
<button class="flex items-center justify-center size-10 rounded-lg border border-[#282d39] text-text-secondary hover:bg-[#353b4b] transition-colors">
<span class="material-symbols-outlined">chevron_right</span>
</button>
</div>
</div>
</div>
</main>
<!-- Client Layout Script -->
<script src="js/client-layout.js"></script>
<script src="js/custom-alerts.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.ClientLayout) {
        ClientLayout.init('boletos');
    }
    
    // Inicializar funcionalidades de botones
    initMisBoletosButtons();
});

// Función para inicializar todas las funcionalidades de Mis Boletos
function initMisBoletosButtons() {
    // Búsqueda
    initBoletoSearch();
    
    // Filtros
    initBoletoFilters();
    
    // Botones de acciones
    initBoletoActions();
    
    // Paginación
    initPagination();
}

// Función para búsqueda de boletos
function initBoletoSearch() {
    const searchInput = document.querySelector('input[placeholder*="Buscar por número"]');
    if (!searchInput) return;
    
    let searchTimeout;
    
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim().toLowerCase();
        
        searchTimeout = setTimeout(() => {
            searchBoletos(query);
        }, 300);
    });
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimeout);
            searchBoletos(e.target.value.trim().toLowerCase());
        }
    });
}

// Función para buscar boletos
function searchBoletos(query) {
    const boletoCards = document.querySelectorAll('.grid > div');
    
    if (!query) {
        boletoCards.forEach(card => {
            card.style.display = '';
        });
        return;
    }
    
    boletoCards.forEach(card => {
        const boletoNumber = card.querySelector('.font-mono')?.textContent.toLowerCase() || '';
        const sorteoName = card.querySelector('h3')?.textContent.toLowerCase() || '';
        
        if (boletoNumber.includes(query) || sorteoName.includes(query)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

// Función para inicializar filtros
function initBoletoFilters() {
    const sorteoSelect = document.querySelectorAll('select')[0];
    const estadoSelect = document.querySelectorAll('select')[1];
    
    if (sorteoSelect) {
        sorteoSelect.addEventListener('change', function() {
            filterBoletos();
        });
    }
    
    if (estadoSelect) {
        estadoSelect.addEventListener('change', function() {
            filterBoletos();
        });
    }
}

// Función para filtrar boletos
function filterBoletos() {
    const sorteoFilter = document.querySelectorAll('select')[0]?.value || '';
    const estadoFilter = document.querySelectorAll('select')[1]?.value || '';
    
    const boletoCards = document.querySelectorAll('.grid > div');
    
    boletoCards.forEach(card => {
        let show = true;
        
        // Filtrar por sorteo
        if (sorteoFilter) {
            const sorteoName = card.querySelector('h3')?.textContent.toLowerCase() || '';
            if (sorteoFilter === 'iphone' && !sorteoName.includes('iphone')) show = false;
            if (sorteoFilter === 'car' && !sorteoName.includes('automóvil') && !sorteoName.includes('auto')) show = false;
            if (sorteoFilter === 'cash' && !sorteoName.includes('efectivo') && !sorteoName.includes('bono')) show = false;
        }
        
        // Filtrar por estado
        if (estadoFilter && show) {
            const estadoBadge = card.textContent.toLowerCase();
            if (estadoFilter === 'winner' && !estadoBadge.includes('ganador')) show = false;
            if (estadoFilter === 'approved' && !estadoBadge.includes('aprobado')) show = false;
            if (estadoFilter === 'pending' && !estadoBadge.includes('validando') && !estadoBadge.includes('pendiente')) show = false;
            if (estadoFilter === 'rejected' && !estadoBadge.includes('rechazado')) show = false;
        }
        
        card.style.display = show ? '' : 'none';
    });
}

// Función para inicializar acciones de boletos
function initBoletoActions() {
    // Botón "Reclamar Premio"
    const reclamarButtons = Array.from(document.querySelectorAll('button')).filter(
        btn => btn.textContent.includes('Reclamar Premio')
    );
    
    reclamarButtons.forEach(button => {
        button.addEventListener('click', function() {
            const card = this.closest('.grid > div');
            const sorteoName = card?.querySelector('h3')?.textContent || 'Sorteo';
            const boletoNumber = card?.querySelector('.font-mono')?.textContent || 'N/A';
            
            customConfirm(`¿Deseas reclamar el premio del sorteo "${sorteoName}"?\n\nBoleto #${boletoNumber}\n\nSe te pedirá que proporciones información de contacto para coordinar la entrega.`, 'Reclamar Premio', 'help').then(confirmed => {
                if (confirmed) {
                    customAlert('Solicitud de reclamación enviada. Nuestro equipo se pondrá en contacto contigo pronto para coordinar la entrega del premio.\n\n¡Felicidades por tu victoria!', 'Reclamación Enviada', 'success');
                    // Aquí se podría redirigir a un formulario o actualizar el estado
                }
            });
        });
    });
    
    // Botones "Comprobante"
    const comprobanteButtons = Array.from(document.querySelectorAll('button')).filter(
        btn => btn.textContent.includes('Comprobante')
    );
    
    comprobanteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const card = this.closest('.grid > div');
            const boletoNumber = card?.querySelector('.font-mono')?.textContent || 'N/A';
            const sorteoName = card?.querySelector('h3')?.textContent || 'Sorteo';
            
            // Simular descarga de comprobante
            customAlert(`Descargando comprobante del boleto #${boletoNumber} del sorteo "${sorteoName}".\n\nEn una implementación real, esto descargaría el PDF del comprobante.`, 'Descargar Comprobante', 'info');
            
            // Aquí se podría generar/descargar el PDF real
            // window.open(`/comprobantes/${boletoNumber}.pdf`, '_blank');
        });
    });
    
    // Botones "Ver Detalles" (visibility)
    const verDetallesButtons = Array.from(document.querySelectorAll('button')).filter(
        btn => btn.querySelector('.material-symbols-outlined')?.textContent === 'visibility'
    );
    
    verDetallesButtons.forEach(button => {
        button.addEventListener('click', function() {
            const card = this.closest('.grid > div');
            const boletoNumber = card?.querySelector('.font-mono')?.textContent || 'N/A';
            const sorteoName = card?.querySelector('h3')?.textContent || 'Sorteo';
            const fecha = card?.querySelector('p.text-xs')?.textContent || 'N/A';
            const estadoBadge = card?.querySelector('.inline-flex')?.textContent || 'N/A';
            
            customAlert(`Detalles del Boleto\n\n` +
                  `Número: #${boletoNumber}\n` +
                  `Sorteo: ${sorteoName}\n` +
                  `Fecha: ${fecha}\n` +
                  `Estado: ${estadoBadge}\n\n` +
                  `Aquí se mostrarían más detalles del boleto.`, 'Detalles del Boleto', 'info');
        });
    });
    
    // Botón "Help" (para boletos pendientes)
    const helpButtons = Array.from(document.querySelectorAll('button')).filter(
        btn => btn.querySelector('.material-symbols-outlined')?.textContent === 'help'
    );
    
    helpButtons.forEach(button => {
        button.addEventListener('click', function() {
            const card = this.closest('.grid > div');
            const sorteoName = card?.querySelector('h3')?.textContent || 'Sorteo';
            
            customAlert(`Tu boleto está siendo validado. Este proceso puede tardar entre 24 y 48 horas.\n\n` +
                  `Una vez que se confirme tu pago, recibirás tu número de boleto asignado y un correo de confirmación.\n\n` +
                  `Si tienes alguna pregunta, puedes contactar a nuestro equipo de soporte.`, 'Boleto en Validación', 'warning');
        });
    });
    
    // Botón "Contactar Soporte" (para boletos rechazados)
    const soporteButtons = Array.from(document.querySelectorAll('button')).filter(
        btn => btn.textContent.includes('Contactar Soporte')
    );
    
    soporteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const card = this.closest('.grid > div');
            const sorteoName = card?.querySelector('h3')?.textContent || 'Sorteo';
            const motivo = card?.querySelector('.text-red-400')?.textContent || 'Motivo no especificado';
            
            customConfirm(`¿Deseas contactar a nuestro equipo de soporte sobre tu boleto rechazado?\n\n` +
                  `Sorteo: ${sorteoName}\n` +
                  `Motivo: ${motivo}\n\n` +
                  `Te redirigiremos a la página de contacto.`, 'Contactar Soporte', 'help').then(confirmed => {
                if (confirmed) {
                    window.location.href = 'ContactoSoporteCliente.php';
                }
            });
        });
    });
}

// Función para inicializar paginación
function initPagination() {
    const paginationButtons = document.querySelectorAll('.flex.items-center.justify-center.gap-2 button');
    
    paginationButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (this.disabled) return;
            
            const buttonContent = this.innerHTML.trim();
            const isChevronLeft = buttonContent.includes('chevron_left');
            const isChevronRight = buttonContent.includes('chevron_right');
            const isNumber = !isNaN(parseInt(this.textContent.trim()));
            
            if (isChevronLeft) {
                // Página anterior
                customToast('Cargando página anterior...', 'info', 1500);
                // Aquí se cargaría la página anterior
            } else if (isChevronRight) {
                // Página siguiente
                customToast('Cargando página siguiente...', 'info', 1500);
                // Aquí se cargaría la página siguiente
            } else if (isNumber) {
                // Número de página
                const pageNum = parseInt(this.textContent.trim());
                
                // Remover clase activa de todos los botones numéricos
                paginationButtons.forEach(btn => {
                    if (!isNaN(parseInt(btn.textContent.trim()))) {
                        btn.classList.remove('bg-primary', 'text-white');
                        btn.classList.add('border', 'border-[#282d39]', 'text-text-secondary', 'hover:bg-[#353b4b]');
                    }
                });
                
                // Agregar clase activa al clickeado
                this.classList.remove('border', 'border-[#282d39]', 'text-text-secondary', 'hover:bg-[#353b4b]');
                this.classList.add('bg-primary', 'text-white');
                
                customToast(`Cargando página ${pageNum}...`, 'info', 1500);
            }
        });
    });
}
</script>

</body></html>

<!-- Boletos comprados como cliente después de seleccionar un sorteo y comprar los boletos
     Se ve esta página para ver los boletos comprados y el estado de los mismos y si ya fueron ganadores o no. -->
