<?php
/**
 * SorteoClienteDetalles
 * Sistema de Sorteos Web
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación para páginas protegidas
$protectedPages = ['DashboardCliente', 'AjustesPefilCliente', 'MisBoletosCliente', 'MisGanancias', 'SeleccionBoletos', 'SorteoClienteDetalles', 'FinalizarPagoBoletos'];
if (in_array('SorteoClienteDetalles', $protectedPages) && (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true)) {
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
?>
<!DOCTYPE html>

<html class="dark" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Detalles de Sorteo - Sorteos Web</title>
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;family=Noto+Sans:wght@400;500;700&amp;display=swap" rel="stylesheet"/>
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
              "display": ["Inter", "sans-serif"],
              "body": ["Noto Sans", "sans-serif"]
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
        /* Custom scrollbar for dark theme */
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
        
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        
        /* Hide summary marker */
        details > summary {
            list-style: none;
        }
        details > summary::-webkit-details-marker {
            display: none;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-white font-display overflow-hidden h-screen flex">
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
<div class="flex-1 overflow-y-auto overflow-x-hidden p-6 lg:p-10">
<div class="w-full max-w-[1200px] mx-auto">
<!-- Breadcrumbs -->
<div class="mb-6 flex items-center gap-2 text-sm text-text-secondary">
<a class="hover:text-white transition-colors" href="DashboardCliente.php">Inicio</a>
<span class="material-symbols-outlined text-[16px]">chevron_right</span>
<a class="hover:text-white transition-colors" href="ListadoSorteosActivos.php">Sorteos</a>
<span class="material-symbols-outlined text-[16px]">chevron_right</span>
<span class="text-white font-medium" id="breadcrumb-title">Gran Sorteo de Verano</span>
</div>
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
<!-- Left Column: Visuals & Detailed Info (7 cols) -->
<div class="lg:col-span-7 xl:col-span-8 flex flex-col gap-8">
<!-- Hero Image -->
<div class="w-full relative group rounded-2xl overflow-hidden shadow-2xl bg-card-dark aspect-video">
<div id="sorteo-hero-image" class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-105" data-alt="Sorteo image" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBSThToNJgK8_ebirDUoyudrls3AYTANGq9M7Zs9ix3l8WlMm_iwssMcgcBKtbPqip5f7LCFIqfkHZEYAgosO1pXgUgY-odysLX9t_CMGNLHE6DVzjSA616V4V4d5G3EAG4p4beU1iktaix9DpdKy4WkFUzJqAQ7pU_Dj4DGa6m6Yhiys5YpRrcuf2hPWh6-cQ6hHdLRK54xyf5ZwlJx4PzuBLOLqV0yLu6X3Pl-4TYDmjte2U-sf3aAZ3uDIaa7aiEDTZ_xY_bJXA');">
</div>
<!-- Badges on image -->
<div class="absolute top-4 left-4 flex gap-2">
<span class="px-3 py-1 bg-primary/90 text-white text-xs font-bold uppercase tracking-wider rounded-full backdrop-blur-sm shadow-sm">Premium</span>
<span class="px-3 py-1 bg-black/60 text-white text-xs font-bold uppercase tracking-wider rounded-full backdrop-blur-sm border border-white/10">Internacional</span>
</div>
<!-- Mobile only: Title overlay for better space usage -->
<div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 to-transparent p-6 lg:hidden">
<h1 id="sorteo-title-mobile" class="text-2xl font-black text-white leading-tight mb-2">Gran Sorteo de Verano: Auto Deportivo 2024</h1>
<p id="sorteo-desc-mobile" class="text-gray-300 text-sm">Participa y gana el auto de tus sueños con todas las comodidades.</p>
</div>
</div>
<!-- Tabs & Content -->
<div class="flex flex-col gap-6">
<!-- Tab Navigation -->
<div class="flex overflow-x-auto border-b border-[#282d39] pb-1 scrollbar-hide">
<button class="px-6 py-3 text-sm font-semibold text-primary border-b-2 border-primary whitespace-nowrap">Descripción</button>
<button class="px-6 py-3 text-sm font-medium text-text-secondary hover:text-white whitespace-nowrap">Reglas de Juego</button>
<button class="px-6 py-3 text-sm font-medium text-text-secondary hover:text-white whitespace-nowrap">Premios Secundarios</button>
</div>
<!-- Description Content -->
<div class="prose prose-slate dark:prose-invert max-w-none">
<h3 class="text-xl font-bold mb-3 text-white">Sobre este premio</h3>
<p class="text-text-secondary leading-relaxed mb-4">
                            Experimenta la máxima potencia con el nuevo modelo deportivo 2024. Este vehículo no es solo un medio de transporte, es una declaración de estilo y rendimiento. Equipado con un motor V8 biturbo, interiores de cuero italiano cosido a mano y un sistema de sonido envolvente de última generación.
                        </p>
<p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-6">
                            El ganador recibirá el vehículo con todos los gastos de envío e impuestos cubiertos. Además, incluimos un año de seguro completo y mantenimiento preventivo gratuito.
                        </p>
<!-- Features Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
<div class="flex items-start gap-3 p-4 rounded-xl bg-card-dark border border-[#282d39]">
<span class="material-symbols-outlined text-primary mt-1">speed</span>
<div>
<h4 class="font-bold text-white text-sm">Velocidad Máxima</h4>
<p class="text-xs text-text-secondary mt-1">320 km/h en pista</p>
</div>
</div>
<div class="flex items-start gap-3 p-4 rounded-xl bg-card-dark border border-[#282d39]">
<span class="material-symbols-outlined text-primary mt-1">settings</span>
<div>
<h4 class="font-bold text-white text-sm">Motorización</h4>
<p class="text-xs text-text-secondary mt-1">V8 Biturbo 4.0L</p>
</div>
</div>
<div class="flex items-start gap-3 p-4 rounded-xl bg-card-dark border border-[#282d39]">
<span class="material-symbols-outlined text-primary mt-1">calendar_month</span>
<div>
<h4 class="font-bold text-white text-sm">Modelo</h4>
<p class="text-xs text-text-secondary mt-1">Edición Especial 2024</p>
</div>
</div>
<div class="flex items-start gap-3 p-4 rounded-xl bg-card-dark border border-[#282d39]">
<span class="material-symbols-outlined text-primary mt-1">verified_user</span>
<div>
<h4 class="font-bold text-white text-sm">Garantía</h4>
<p class="text-xs text-text-secondary mt-1">3 años extendida</p>
</div>
</div>
</div>
</div>
<!-- FAQ Section -->
<div class="mt-8">
<h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Preguntas Frecuentes</h3>
<div class="flex flex-col gap-3">
<details class="group bg-card-dark rounded-xl border border-[#282d39] overflow-hidden">
<summary class="flex justify-between items-center cursor-pointer p-4 font-medium text-white select-none">
<span>¿Cómo se elige al ganador?</span>
<span class="material-symbols-outlined transition-transform duration-300 group-open:rotate-180 text-gray-400">expand_more</span>
</summary>
<div class="px-4 pb-4 pt-0 text-sm text-text-secondary border-t border-transparent group-open:border-[#282d39] mt-2 pt-2">
                                    El sorteo se realiza en vivo a través de nuestras redes sociales utilizando un sistema certificado de selección aleatoria (RNG).
                                </div>
</details>
<details class="group bg-card-dark rounded-xl border border-[#282d39] overflow-hidden">
<summary class="flex justify-between items-center cursor-pointer p-4 font-medium text-white select-none">
<span>¿Puedo comprar boletos desde otro país?</span>
<span class="material-symbols-outlined transition-transform duration-300 group-open:rotate-180 text-gray-400">expand_more</span>
</summary>
<div class="px-4 pb-4 pt-0 text-sm text-text-secondary border-t border-transparent group-open:border-[#282d39] mt-2 pt-2">
                                    Sí, el sorteo es internacional. Sin embargo, los gastos de aduana adicionales pueden correr por cuenta del ganador si reside fuera de las zonas especificadas.
                                </div>
</details>
<details class="group bg-card-dark rounded-xl border border-[#282d39] overflow-hidden">
<summary class="flex justify-between items-center cursor-pointer p-4 font-medium text-white select-none">
<span>¿Cuándo recibo mis boletos?</span>
<span class="material-symbols-outlined transition-transform duration-300 group-open:rotate-180 text-gray-400">expand_more</span>
</summary>
<div class="px-4 pb-4 pt-0 text-sm text-text-secondary border-t border-transparent group-open:border-[#282d39] mt-2 pt-2">
                                    Inmediatamente después de confirmar tu pago, recibirás un correo electrónico con tus números de boleto digitales y la factura de compra.
                                </div>
</details>
</div>
</div>
</div>
</div>
<!-- Right Column: Action Sidebar (Sticky) (5 cols) -->
<div class="lg:col-span-5 xl:col-span-4">
<div class="sticky top-24 flex flex-col gap-6">
<!-- Main Card -->
<div class="bg-card-dark rounded-2xl p-6 border border-[#282d39] shadow-xl">
<!-- Desktop Title (Hidden on mobile) -->
<div class="hidden lg:block mb-6">
<h1 id="sorteo-title" class="text-3xl font-black text-white leading-tight tracking-tight">Gran Sorteo de Verano</h1>
<p id="sorteo-subtitle" class="text-text-secondary mt-2">Auto Deportivo 2024 - Edición Limitada</p>
</div>
<!-- Timer -->
<div class="mb-8">
<p class="text-xs font-bold uppercase tracking-widest text-red-500 mb-3 flex items-center gap-1">
<span class="material-symbols-outlined text-sm">timer</span> Cierra en:
                            </p>
<div class="flex gap-2 justify-between">
<div class="flex flex-col items-center bg-[#111318] p-3 rounded-lg flex-1 border border-[#282d39]">
<span id="timer-dias" class="text-2xl font-bold text-white tabular-nums">03</span>
<span class="text-[10px] uppercase text-text-secondary">Días</span>
</div>
<div class="text-xl font-bold text-gray-300 self-start mt-2">:</div>
<div class="flex flex-col items-center bg-[#111318] p-3 rounded-lg flex-1 border border-[#282d39]">
<span id="timer-horas" class="text-2xl font-bold text-white tabular-nums">12</span>
<span class="text-[10px] uppercase text-text-secondary">Hrs</span>
</div>
<div class="text-xl font-bold text-gray-300 self-start mt-2">:</div>
<div class="flex flex-col items-center bg-[#111318] p-3 rounded-lg flex-1 border border-[#282d39]">
<span id="timer-minutos" class="text-2xl font-bold text-white tabular-nums">45</span>
<span class="text-[10px] uppercase text-text-secondary">Min</span>
</div>
<div class="text-xl font-bold text-gray-300 self-start mt-2">:</div>
<div class="flex flex-col items-center bg-[#111318] p-3 rounded-lg flex-1 border border-[#282d39]">
<span id="timer-segundos" class="text-2xl font-bold text-white tabular-nums">30</span>
<span class="text-[10px] uppercase text-text-secondary">Seg</span>
</div>
</div>
</div>
<!-- Progress -->
<div class="mb-8">
<div class="flex justify-between items-end mb-2">
<span class="text-sm font-medium text-white">Boletos vendidos</span>
<span id="porcentaje-vendido" class="text-sm font-bold text-primary">75%</span>
</div>
<div class="w-full bg-[#111318] rounded-full h-2.5 overflow-hidden">
<div id="progress-bar" class="bg-primary h-2.5 rounded-full relative" style="width: 75%">
<div class="absolute inset-0 bg-white/20 animate-pulse"></div>
</div>
</div>
<p id="boletos-restantes" class="text-xs text-text-secondary mt-2 text-right">Quedan 250 boletos disponibles</p>
</div>
<!-- Price & Action -->
<div class="pt-6 border-t border-[#282d39]">
<div class="flex items-baseline justify-between mb-4">
<span class="text-text-secondary text-sm">Precio por boleto</span>
<span id="precio-boleto" class="text-3xl font-black text-white">$50.00 <span class="text-sm font-normal text-text-secondary">MXN</span></span>
</div>
<!-- Ticket Selector -->
<div class="flex items-center justify-between bg-[#111318] rounded-lg p-1 mb-4 border border-[#282d39]">
<button class="size-10 flex items-center justify-center rounded-md hover:bg-[#353b4b] text-white transition-colors">
<span class="material-symbols-outlined">remove</span>
</button>
<input class="w-12 bg-transparent text-center border-none focus:ring-0 text-white font-bold" max="100" min="1" type="number" value="1"/>
<button class="size-10 flex items-center justify-center rounded-md hover:bg-[#353b4b] text-white transition-colors">
<span class="material-symbols-outlined">add</span>
</button>
</div>
<a href="SeleccionBoletos.php" id="btn-seleccionar" onclick="saveCurrentSorteo()" class="w-full bg-primary hover:bg-blue-600 text-white font-bold text-lg py-4 px-6 rounded-xl shadow-lg shadow-primary/25 transition-all active:scale-[0.98] flex items-center justify-center gap-2">
<span>Seleccionar Boletos</span>
<span class="material-symbols-outlined">arrow_forward</span>
</a>
<p class="text-center mt-4 text-xs text-text-secondary flex items-center justify-center gap-1">
<span class="material-symbols-outlined text-[14px]">lock</span> Pago 100% Seguro
                            </p>
</div>
</div>
<!-- Trust Badges (Optional) -->
<div class="grid grid-cols-3 gap-3">
<div class="flex flex-col items-center justify-center p-3 rounded-lg bg-card-dark border border-[#282d39] text-center">
<span class="material-symbols-outlined text-primary mb-1 text-2xl">verified</span>
<span class="text-[10px] text-text-secondary leading-tight">Sorteo Verificado</span>
</div>
<div class="flex flex-col items-center justify-center p-3 rounded-lg bg-white dark:bg-surface-dark/50 border border-gray-200 dark:border-gray-800 text-center">
<span class="material-symbols-outlined text-primary mb-1 text-2xl">support_agent</span>
<span class="text-[10px] text-text-secondary leading-tight">Soporte 24/7</span>
</div>
<div class="flex flex-col items-center justify-center p-3 rounded-lg bg-white dark:bg-surface-dark/50 border border-gray-200 dark:border-gray-800 text-center">
<span class="material-symbols-outlined text-primary mb-1 text-2xl">redeem</span>
<span class="text-[10px] text-text-secondary leading-tight">Premio Garantizado</span>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
<!-- Footer Spacing -->
<div class="h-10"></div>
</div>
</div>
</main>

<!-- Custom Alerts Script (debe cargarse antes de client-layout.js) -->
<script src="js/custom-alerts.js"></script>
<!-- Custom Alerts Script (debe cargarse antes de client-layout.js) -->
<script src="js/custom-alerts.js"></script>
<!-- Client Layout Script -->
<script src="js/client-layout.js"></script>
<script>
// Inicializar layout del cliente
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar el layout con 'sorteos' como página activa
    if (window.ClientLayout) {
        ClientLayout.init('sorteos');
    }
    
    // Cargar información del sorteo seleccionado
    loadSorteoDetails();
    
    // Asegurar que los contadores se inicialicen (igual que DashboardCliente)
    setTimeout(function() {
        const sorteoData = JSON.parse(localStorage.getItem('selectedSorteo')) || getDefaultSorteoData();
        if (sorteoData.tiempoRestante) {
            initSorteoCountdown(sorteoData.tiempoRestante);
        } else {
            initSorteoCountdown({ dias: 3, horas: 12, minutos: 45, segundos: 30 });
        }
    }, 100);
    
    // Inicializar funcionalidades de botones
    initSorteoDetailsButtons();
});

// Función para cargar los detalles del sorteo desde localStorage
function loadSorteoDetails() {
    const sorteoData = JSON.parse(localStorage.getItem('selectedSorteo')) || getDefaultSorteoData();
    
    // Actualizar título
    document.getElementById('sorteo-title').textContent = sorteoData.titulo;
    document.getElementById('sorteo-title-mobile').textContent = sorteoData.titulo;
    document.getElementById('breadcrumb-title').textContent = sorteoData.titulo;
    document.getElementById('sorteo-subtitle').textContent = sorteoData.subtitulo;
    document.getElementById('sorteo-desc-mobile').textContent = sorteoData.descripcion;
    
    // Actualizar imagen
    const heroImage = document.getElementById('sorteo-hero-image');
    if (heroImage && sorteoData.imagen) {
        heroImage.style.backgroundImage = `url('${sorteoData.imagen}')`;
    }
    
    // Actualizar precio
    document.getElementById('precio-boleto').innerHTML = `$${sorteoData.precio.toFixed(2)} <span class="text-sm font-normal text-text-secondary">MXN</span>`;
    
    // Actualizar progreso
    const porcentaje = (sorteoData.boletosVendidos / sorteoData.boletosTotales) * 100;
    document.getElementById('progress-bar').style.width = `${porcentaje}%`;
    document.getElementById('porcentaje-vendido').textContent = `${Math.round(porcentaje)}%`;
    document.getElementById('boletos-restantes').textContent = `Quedan ${sorteoData.boletosTotales - sorteoData.boletosVendidos} boletos disponibles`;
    
    // Inicializar contador regresivo del sorteo (igual que DashboardCliente)
    if (sorteoData.tiempoRestante) {
        initSorteoCountdown(sorteoData.tiempoRestante);
    } else {
        initSorteoCountdown({ dias: 3, horas: 12, minutos: 45, segundos: 30 });
    }
    
    // Actualizar descripción en el contenido
    const descParagraphs = document.querySelectorAll('.prose p');
    if (descParagraphs.length > 1 && sorteoData.descripcionCompleta) {
        // Dividir la descripción completa en dos párrafos si tiene un punto medio
        const partes = sorteoData.descripcionCompleta.split('. ');
        if (partes.length > 1) {
            descParagraphs[0].textContent = partes.slice(0, Math.floor(partes.length / 2)).join('. ') + '.';
            descParagraphs[1].textContent = partes.slice(Math.floor(partes.length / 2)).join('. ');
        } else {
            descParagraphs[0].textContent = sorteoData.descripcionCompleta;
            if (descParagraphs[1]) {
                descParagraphs[1].textContent = 'El ganador recibirá el premio con todos los gastos de envío e impuestos cubiertos. Además, incluimos garantía y soporte completo.';
            }
        }
    } else if (descParagraphs.length > 0 && sorteoData.descripcionCompleta) {
        descParagraphs[0].textContent = sorteoData.descripcionCompleta;
    }
}

// Función para obtener datos por defecto si no hay sorteo seleccionado
function getDefaultSorteoData() {
    return {
        id: 'default',
        titulo: 'Gran Sorteo de Verano',
        subtitulo: 'Auto Deportivo 2024 - Edición Limitada',
        descripcion: 'Participa y gana el auto de tus sueños con todas las comodidades.',
        descripcionCompleta: 'Experimenta la máxima potencia con el nuevo modelo deportivo 2024. Este vehículo no es solo un medio de transporte, es una declaración de estilo y rendimiento. Equipado con un motor V8 biturbo, interiores de cuero italiano cosido a mano y un sistema de sonido envolvente de última generación.',
        imagen: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBSThToNJgK8_ebirDUoyudrls3AYTANGq9M7Zs9ix3l8WlMm_iwssMcgcBKtbPqip5f7LCFIqfkHZEYAgosO1pXgUgY-odysLX9t_CMGNLHE6DVzjSA616V4V4d5G3EAG4p4beU1iktaix9DpdKy4WkFUzJqAQ7pU_Dj4DGa6m6Yhiys5YpRrcuf2hPWh6-cQ6hHdLRK54xyf5ZwlJx4PzuBLOLqV0yLu6X3Pl-4TYDmjte2U-sf3aAZ3uDIaa7aiEDTZ_xY_bJXA',
        precio: 50.00,
        boletosVendidos: 375,
        boletosTotales: 500,
        tiempoRestante: { dias: 3, horas: 12, minutos: 45, segundos: 30 }
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
    const segundosElement = document.getElementById('timer-segundos');
    
    if (!diasElement || !horasElement || !minutosElement || !segundosElement) {
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
        tiempo = { dias: 3, horas: 12, minutos: 45, segundos: 30 };
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
            segundosElement.textContent = '00';
            // Limpiar el intervalo cuando llegue a cero
            if (activeCountdownIntervals.has('sorteo-timer')) {
                clearInterval(activeCountdownIntervals.get('sorteo-timer'));
                activeCountdownIntervals.delete('sorteo-timer');
            }
            return;
        }
        
        // Calcular días, horas, minutos y segundos con el tiempo actual
        const dias = Math.floor(remainingSeconds / 86400);
        const horas = Math.floor((remainingSeconds % 86400) / 3600);
        const minutos = Math.floor((remainingSeconds % 3600) / 60);
        const segundos = remainingSeconds % 60;
        
        // Actualizar la visualización
        diasElement.textContent = String(dias).padStart(2, '0');
        horasElement.textContent = String(horas).padStart(2, '0');
        minutosElement.textContent = String(minutos).padStart(2, '0');
        segundosElement.textContent = String(segundos).padStart(2, '0');
        
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

// Guardar datos del sorteo seleccionado (llamado desde ListadoSorteosActivos.php)
window.saveSorteoData = function(sorteoData) {
    localStorage.setItem('selectedSorteo', JSON.stringify(sorteoData));
};

// Función para inicializar funcionalidades de botones en detalles del sorteo
function initSorteoDetailsButtons() {
    // Botones de cantidad (+ y -)
    const quantityInput = document.querySelector('input[type="number"]');
    const decreaseBtn = Array.from(document.querySelectorAll('button')).find(btn => 
        btn.querySelector('.material-symbols-outlined')?.textContent === 'remove'
    );
    const increaseBtn = Array.from(document.querySelectorAll('button')).find(btn => 
        btn.querySelector('.material-symbols-outlined')?.textContent === 'add'
    );
    
    if (quantityInput && decreaseBtn && increaseBtn) {
        // Botón disminuir cantidad
        decreaseBtn.addEventListener('click', function() {
            let currentValue = parseInt(quantityInput.value) || 1;
            if (currentValue > 1) {
                currentValue--;
                quantityInput.value = currentValue;
                updateTotalPrice();
            }
        });
        
        // Botón aumentar cantidad
        increaseBtn.addEventListener('click', function() {
            let currentValue = parseInt(quantityInput.value) || 1;
            const maxValue = parseInt(quantityInput.max) || 100;
            if (currentValue < maxValue) {
                currentValue++;
                quantityInput.value = currentValue;
                updateTotalPrice();
            }
        });
        
        // Validar input manual
        quantityInput.addEventListener('change', function() {
            let value = parseInt(this.value) || 1;
            const min = parseInt(this.min) || 1;
            const max = parseInt(this.max) || 100;
            
            if (value < min) value = min;
            if (value > max) value = max;
            
            this.value = value;
            updateTotalPrice();
        });
        
        // Actualizar precio total cuando cambia la cantidad
        quantityInput.addEventListener('input', function() {
            updateTotalPrice();
        });
    }
    
    // Botón "Seleccionar Boletos"
    const selectBtn = document.getElementById('btn-seleccionar');
    if (selectBtn) {
        selectBtn.addEventListener('click', function(e) {
            e.preventDefault();
            saveCurrentSorteo();
            // Redirigir a SeleccionBoletos.php
            window.location.href = 'SeleccionBoletos.php';
        });
    }
}

// Función para actualizar el precio total en la página de detalles
function updateTotalPrice() {
    const quantityInput = document.querySelector('input[type="number"]');
    const precioBoleto = document.getElementById('precio-boleto');
    
    if (quantityInput && precioBoleto) {
        const quantity = parseInt(quantityInput.value) || 1;
        const sorteoData = JSON.parse(localStorage.getItem('selectedSorteo')) || getDefaultSorteoData();
        const precioUnitario = sorteoData.precio || 50.00;
        const total = precioUnitario * quantity;
        
        // Actualizar el precio en el botón o mostrar en algún lugar si existe
        // Por ahora solo guardamos la cantidad en el sorteoData
        sorteoData.cantidadBoletos = quantity;
        sorteoData.totalPrecio = total;
        localStorage.setItem('selectedSorteo', JSON.stringify(sorteoData));
    }
}

// Función para guardar el sorteo actual antes de ir a SeleccionBoletos
function saveCurrentSorteo() {
    const sorteoData = JSON.parse(localStorage.getItem('selectedSorteo')) || getDefaultSorteoData();
    
    // Guardar cantidad de boletos seleccionada
    const quantityInput = document.querySelector('input[type="number"]');
    if (quantityInput) {
        sorteoData.cantidadBoletos = parseInt(quantityInput.value) || 1;
    }
    
    localStorage.setItem('selectedSorteo', JSON.stringify(sorteoData));
    return true;
}

</script>

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

// Inicializar layout del cliente DESPUÉS de actualizar localStorage
document.addEventListener('DOMContentLoaded', function() {
    if (window.ClientLayout) {
        ClientLayout.init('sorteos');
    }
});
</script>

</body></html>

<!-- Después de seleccionar un sorteo como cliente, se ve esta página para los detalles del sorteo -->
