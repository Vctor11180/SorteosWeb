<?php
/**
 * AjustesPefilCliente
 * Sistema de Sorteos Web
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación para páginas protegidas
$protectedPages = ['DashboardCliente', 'AjustesPefilCliente', 'MisBoletosCliente', 'MisGanancias', 'SeleccionBoletos', 'SorteoClienteDetalles', 'FinalizarPagoBoletos'];
if (in_array('AjustesPefilCliente', $protectedPages) && (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true)) {
    header('Location: InicioSesion.php');
    exit;
}

// Obtener datos del usuario desde la base de datos
require_once __DIR__ . '/includes/user-data.php';

// Verificar que el usuario_id en la sesión esté presente
if (!isset($_SESSION['usuario_id'])) {
    error_log("AjustesPefilCliente - ERROR: No hay usuario_id en la sesión");
    header('Location: InicioSesion.php');
    exit;
}

error_log("AjustesPefilCliente - Usuario ID en sesión: " . $_SESSION['usuario_id']);
error_log("AjustesPefilCliente - Email en sesión: " . ($_SESSION['usuario_email'] ?? 'NO DEFINIDO'));

$datosUsuario = obtenerDatosUsuarioCompletos();
if (!$datosUsuario) {
    error_log("AjustesPefilCliente - ERROR: No se pudieron obtener los datos del usuario. Usuario ID: " . $_SESSION['usuario_id']);
    header('Location: InicioSesion.php');
    exit;
}

error_log("AjustesPefilCliente - Datos obtenidos - ID: " . $datosUsuario['id_usuario'] . ", Nombre: " . $datosUsuario['nombre'] . ", Email: " . $datosUsuario['email']);

// Verificar que los datos obtenidos correspondan al usuario de la sesión
if ($datosUsuario['id_usuario'] != $_SESSION['usuario_id']) {
    error_log("AjustesPefilCliente - ERROR CRÍTICO: Los datos obtenidos no corresponden al usuario de la sesión!");
    error_log("AjustesPefilCliente - Sesión usuario_id: " . $_SESSION['usuario_id'] . ", Datos usuario_id: " . $datosUsuario['id_usuario']);
    // Forzar recarga de datos
    session_regenerate_id(true);
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
<title>Ajustes de Perfil Cliente - Sorteos Web</title>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&amp;family=Noto+Sans:wght@400;500;700;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "primary": "#2463eb",
              "background-light": "#f6f6f8",
              "background-dark": "#111621",
              "card-dark": "#1a202c",
              "input-dark": "#2d3748",
            },
            fontFamily: {
              "display": ["Inter", "Noto Sans", "sans-serif"]
            },
            borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
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
        
        /* Animación de spinner para botón de carga */
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        .animate-spin {
            animation: spin 1s linear infinite;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display overflow-hidden h-screen flex text-slate-900 dark:text-white">
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
<h1 id="sidebar-user-name" class="text-white text-sm font-semibold truncate"><?php echo htmlspecialchars($usuarioNombre); ?></h1>
<p id="sidebar-user-type" class="text-text-secondary text-xs truncate"><?php echo htmlspecialchars($tipoUsuario); ?></p>
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
<a id="nav-boletos" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-text-secondary hover:text-white hover:bg-card-dark transition-colors group" href="MisBoletosCliente.php">
<span class="material-symbols-outlined text-[24px]">confirmation_number</span>
<p class="text-sm font-medium">Mis Boletos</p>
</a>
<a id="nav-ganadores" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-text-secondary hover:text-white hover:bg-card-dark transition-colors group" href="MisGanancias.php">
<span class="material-symbols-outlined text-[24px]">emoji_events</span>
<p class="text-sm font-medium">Ganadores</p>
</a>
<a id="nav-perfil" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary text-white group transition-colors" href="AjustesPefilCliente.php">
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
<!-- Mobile Menu Toggle -->
<button id="mobile-menu-toggle" class="lg:hidden text-white mr-4" aria-label="Abrir menú de navegación">
<span class="material-symbols-outlined">menu</span>
</button>
<!-- Page Title -->
<h1 class="text-xl font-bold text-white hidden sm:block">Ajustes de Perfil</h1>
<div class="ml-auto"></div>
</header>
<!-- Scrollable Content Area -->
<div class="flex-1 overflow-y-auto overflow-x-hidden p-6 lg:p-10">
<!-- Main Content Container -->
<div class="w-full max-w-[1440px] mx-auto flex flex-col lg:flex-row gap-6">
<!-- Sidebar Navigation -->
<aside class="w-full lg:w-72 flex-shrink-0 lg:sticky lg:top-20 lg:self-start">
<div class="bg-card-dark rounded-xl p-6 border border-[#282d39] min-h-[500px] flex flex-col">
<!-- Profile Snippet -->
<div class="flex items-center gap-4 mb-8 pb-6 border-b border-[#282d39]">
<div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-12" data-alt="User avatar small" style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuBBrupAsp5FgxLWvA_4eDdbO6IBv60Wu2kUzWPNIeip67-oe7I9b2nzaS82HH1OLDR3kt3eIpanzRITFMLGrGYsXnYYc9VA7cfYcoXpQAV1ZQ-hf-DJpgeVpZ2V8DWaQFUHaeUoD_hKmPFlDXfF9XUj5aA9UwMFZqIMKCl-VjIMi1AeKlxdFIXwIkUzXtyq30ajvF07xm95jeC5M4OIFYr8wXRjuU9unKPkk0g_KAcx7iySUtEgz0MBnnruUiSrXMXHZKuiIMrFkg4");'></div>
<div class="flex flex-col">
<h1 id="profile-sidebar-name" class="text-white text-base font-bold"><?php echo htmlspecialchars($usuarioNombre); ?></h1>
<p class="text-text-secondary text-xs"><?php echo htmlspecialchars($tipoUsuario); ?></p>
</div>
</div>
<!-- Navigation Links -->
<nav class="flex flex-col gap-2 flex-1">
<a id="nav-link-informacion" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-primary/10 text-primary border-l-4 border-primary" href="#section-informacion-personal">
<span class="material-symbols-outlined">person</span>
<span class="text-sm font-bold">Información Personal</span>
</a>
<a id="nav-link-seguridad" class="flex items-center gap-3 px-4 py-3 rounded-lg text-text-secondary hover:bg-[#282d39] hover:text-white transition-colors" href="#section-seguridad">
<span class="material-symbols-outlined">lock</span>
<span class="text-sm font-medium">Seguridad</span>
</a>
<a id="nav-link-notificaciones" class="flex items-center gap-3 px-4 py-3 rounded-lg text-text-secondary hover:bg-[#282d39] hover:text-white transition-colors" href="#section-notificaciones">
<span class="material-symbols-outlined">notifications</span>
<span class="text-sm font-medium">Notificaciones</span>
</a>
<a id="nav-link-pagos" class="flex items-center gap-3 px-4 py-3 rounded-lg text-text-secondary hover:bg-[#282d39] hover:text-white transition-colors" href="#">
<span class="material-symbols-outlined">credit_card</span>
<span class="text-sm font-medium">Métodos de Pago</span>
</a>
<a id="nav-link-historial" class="flex items-center gap-3 px-4 py-3 rounded-lg text-text-secondary hover:bg-[#282d39] hover:text-white transition-colors" href="#section-historial-sorteos">
<span class="material-symbols-outlined">history</span>
<span class="text-sm font-medium">Historial de Sorteos</span>
</a>
</nav>
</div>
</aside>
<!-- Main Content Area -->
<main class="flex-1 flex flex-col gap-6">
<!-- Page Header -->
<div class="bg-[#111318] dark:bg-card-dark p-6 rounded-xl border border-[#282d39] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
<div>
<h2 class="text-white text-2xl font-bold tracking-tight">Ajustes de Perfil</h2>
<p class="text-text-secondary text-sm mt-1">Gestiona tu información personal y preferencias de la cuenta.</p>
</div>
</div>
<!-- Profile Info Section -->
<section id="section-informacion-personal" class="bg-[#111318] dark:bg-card-dark rounded-xl border border-[#282d39] overflow-hidden">
<div class="p-6 border-b border-[#282d39]">
<h3 class="text-white text-lg font-bold flex items-center gap-2">
<span class="material-symbols-outlined text-primary">badge</span>
                        Información Personal
                    </h3>
</div>
<div class="p-6 lg:p-8">
<!-- Avatar Upload -->
<div class="flex flex-col sm:flex-row gap-6 mb-8 items-center sm:items-start">
<div class="relative group cursor-pointer">
<div id="avatar-perfil" class="bg-center bg-no-repeat bg-cover rounded-full w-24 h-24 border-2 border-primary/50 group-hover:border-primary transition-colors" data-alt="User avatar large" style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuDGuIh6aso4QumOT9FeAmtvNPV87AjsM2Mu8MP96AGuwYqp-sR6G0-z1o0dmajilp9nVokPOW8OcZ50OiPdVJVFhGSggxdLroPiSuUVFsm1KGlsQ1pqDNO2OPEmcqfxRfZxgd_jKnCmN8EFW5qEUr_nxBXzcB4-yqkx2E7vgzFgt1cLqtYKt0PVdk9Wtoc7eV28RI8aVzcqwEnGYvEF5hGaqeqEv_b9jRwJIFEXYSwZJmZiojOfS1yuoS6kLExlnIKcWOsvA8nc6Hk");'></div>
<div class="absolute inset-0 bg-black/50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
<span class="material-symbols-outlined text-white">photo_camera</span>
</div>
</div>
<div class="flex-1 text-center sm:text-left">
<h4 class="text-white font-medium mb-1">Tu Foto de Perfil</h4>
<p class="text-text-secondary text-sm mb-4">Esta foto será visible para tu perfil público y comentarios.</p>
<div class="flex gap-3 justify-center sm:justify-start">
<button id="btn-subir-avatar" class="px-4 py-2 bg-[#282d39] hover:bg-[#323846] text-white text-xs font-bold rounded-lg transition-colors border border-[#3e4552]">
                                    Subir Nueva
                                </button>
<button id="btn-eliminar-avatar" class="px-4 py-2 text-red-400 hover:text-red-300 hover:bg-red-900/20 text-xs font-bold rounded-lg transition-colors">
                                    Eliminar
                                </button>
</div>
</div>
</div>
<!-- Form Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<div class="flex flex-col gap-2">
<label class="text-white text-sm font-medium">Nombre Completo</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-2.5 text-text-secondary text-[20px]">person</span>
<input id="input-nombre" class="w-full bg-[#111621] border border-[#282d39] text-white text-sm rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent pl-10 py-2.5 placeholder-[#566074]" placeholder="Tu nombre" type="text" value="<?php echo htmlspecialchars($usuarioNombre); ?>"/>
<span id="error-nombre" class="hidden text-red-400 text-xs mt-1"></span>
</div>
</div>
<div class="flex flex-col gap-2">
<label class="text-white text-sm font-medium">Correo Electrónico</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-2.5 text-text-secondary text-[20px]">mail</span>
<input id="input-email" class="w-full bg-[#111621] border border-[#282d39] text-white text-sm rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent pl-10 py-2.5 placeholder-[#566074]" placeholder="email@ejemplo.com" type="email" value="<?php echo htmlspecialchars($usuarioEmail); ?>"/>
<span id="error-email" class="hidden text-red-400 text-xs mt-1"></span>
</div>
</div>
<div class="flex flex-col gap-2">
<label class="text-white text-sm font-medium">Teléfono</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-2.5 text-text-secondary text-[20px]">call</span>
<input id="input-telefono" class="w-full bg-[#111621] border border-[#282d39] text-white text-sm rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent pl-10 py-2.5 placeholder-[#566074]" placeholder="+1 234 567 890" type="tel" value="<?php echo htmlspecialchars($datosUsuario['telefono'] ?? ''); ?>"/>
<span id="error-telefono" class="hidden text-red-400 text-xs mt-1"></span>
</div>
</div>
<div class="flex flex-col gap-2">
<label class="text-white text-sm font-medium">Dirección</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-2.5 text-text-secondary text-[20px]">location_on</span>
<input id="input-direccion" class="w-full bg-[#111621] border border-[#282d39] text-white text-sm rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent pl-10 py-2.5 placeholder-[#566074]" placeholder="Tu dirección completa" type="text" value="Calle Gran Vía 22, Madrid"/>
<span id="error-direccion" class="hidden text-red-400 text-xs mt-1"></span>
</div>
</div>
</div>
</div>
<div class="px-6 py-4 bg-[#151a23] border-t border-[#282d39] flex justify-end">
<button id="btn-guardar-informacion" class="bg-primary hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-bold text-sm shadow-lg shadow-blue-900/20 transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
<span id="btn-guardar-icon" class="material-symbols-outlined text-[18px]">save</span>
<span id="btn-guardar-text">Guardar Información</span>
<span id="btn-guardar-spinner" class="hidden animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></span>
                    </button>
</div>
</section>
<!-- Security Section -->
<section id="section-seguridad" class="bg-[#111318] dark:bg-card-dark rounded-xl border border-[#282d39] overflow-hidden">
<div class="p-6 border-b border-[#282d39]">
<h3 class="text-white text-lg font-bold flex items-center gap-2">
<span class="material-symbols-outlined text-primary">lock_reset</span>
                        Cambiar Contraseña
                    </h3>
</div>
<div class="p-6 lg:p-8">
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
<div class="flex flex-col gap-2">
<label class="text-white text-sm font-medium">Contraseña Actual</label>
<div class="relative">
<input id="input-password-actual" class="w-full bg-[#111621] border border-[#282d39] text-white text-sm rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent pl-3 pr-10 py-2.5 placeholder-[#566074]" placeholder="********" type="password"/>
<button type="button" id="toggle-password-actual" class="absolute right-3 top-1/2 -translate-y-1/2 text-text-secondary hover:text-white transition-colors" aria-label="Mostrar contraseña">
<span class="material-symbols-outlined text-[20px]">visibility</span>
</button>
<span id="error-password-actual" class="hidden text-red-400 text-xs mt-1"></span>
</div>
</div>
<div class="flex flex-col gap-2">
<label class="text-white text-sm font-medium">Nueva Contraseña</label>
<div class="relative">
<input id="input-password-nueva" class="w-full bg-[#111621] border border-[#282d39] text-white text-sm rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent pl-3 pr-10 py-2.5 placeholder-[#566074]" placeholder="********" type="password"/>
<button type="button" id="toggle-password-nueva" class="absolute right-3 top-1/2 -translate-y-1/2 text-text-secondary hover:text-white transition-colors" aria-label="Mostrar contraseña">
<span class="material-symbols-outlined text-[20px]">visibility</span>
</button>
<span id="error-password-nueva" class="hidden text-red-400 text-xs mt-1"></span>
<div id="password-strength" class="hidden mt-2">
<div class="flex gap-1 mb-1">
<div id="strength-bar-1" class="h-1 flex-1 bg-gray-700 rounded"></div>
<div id="strength-bar-2" class="h-1 flex-1 bg-gray-700 rounded"></div>
<div id="strength-bar-3" class="h-1 flex-1 bg-gray-700 rounded"></div>
<div id="strength-bar-4" class="h-1 flex-1 bg-gray-700 rounded"></div>
</div>
<p id="strength-text" class="text-xs text-text-secondary"></p>
</div>
</div>
</div>
<div class="flex flex-col gap-2">
<label class="text-white text-sm font-medium">Confirmar Nueva</label>
<div class="relative">
<input id="input-password-confirmar" class="w-full bg-[#111621] border border-[#282d39] text-white text-sm rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent pl-3 pr-10 py-2.5 placeholder-[#566074]" placeholder="********" type="password"/>
<button type="button" id="toggle-password-confirmar" class="absolute right-3 top-1/2 -translate-y-1/2 text-text-secondary hover:text-white transition-colors" aria-label="Mostrar contraseña">
<span class="material-symbols-outlined text-[20px]">visibility</span>
</button>
<span id="error-password-confirmar" class="hidden text-red-400 text-xs mt-1"></span>
</div>
</div>
</div>
</div>
<div class="px-6 py-4 bg-[#151a23] border-t border-[#282d39] flex justify-end">
<button id="btn-actualizar-password" class="bg-[#282d39] hover:bg-[#323846] text-white px-6 py-2 rounded-lg font-bold text-sm transition-all border border-[#3e4552] flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
<span id="btn-password-icon" class="material-symbols-outlined text-[18px]">lock_reset</span>
<span id="btn-password-text">Actualizar Contraseña</span>
<span id="btn-password-spinner" class="hidden animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></span>
                    </button>
</div>
</section>
<!-- Notifications Section -->
<section id="section-notificaciones" class="bg-[#111318] dark:bg-card-dark rounded-xl border border-[#282d39] overflow-hidden">
<div class="p-6 border-b border-[#282d39]">
<h3 class="text-white text-lg font-bold flex items-center gap-2">
<span class="material-symbols-outlined text-primary">notifications_active</span>
                        Preferencias de Notificación
                    </h3>
</div>
<div class="p-6 lg:p-8 flex flex-col gap-6">
<!-- Notification Item -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-[#282d39]">
<div>
<h4 class="text-white font-medium mb-1">Nuevos Sorteos Disponibles</h4>
<p class="text-text-secondary text-sm">Recibe alertas cuando se lancen nuevos sorteos en la plataforma.</p>
</div>
<div class="flex gap-4">
<label class="inline-flex items-center cursor-pointer gap-2 group">
<input id="notif-nuevos-sorteos-email" checked="" class="sr-only peer" type="checkbox" value="email"/>
<div class="relative w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
<span class="text-sm font-medium text-gray-400 group-hover:text-white transition-colors">Email</span>
</label>
<label class="inline-flex items-center cursor-pointer gap-2 group">
<input id="notif-nuevos-sorteos-sms" class="sr-only peer" type="checkbox" value="sms"/>
<div class="relative w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
<span class="text-sm font-medium text-gray-400 group-hover:text-white transition-colors">SMS</span>
</label>
</div>
</div>
<!-- Notification Item -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-[#282d39]">
<div>
<h4 class="text-white font-medium mb-1">Resultados de Sorteos</h4>
<p class="text-text-secondary text-sm">Entérate inmediatamente si has ganado o los resultados generales.</p>
</div>
<div class="flex gap-4">
<label class="inline-flex items-center cursor-pointer gap-2 group">
<input id="notif-resultados-email" checked="" class="sr-only peer" type="checkbox" value="email"/>
<div class="relative w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
<span class="text-sm font-medium text-gray-400 group-hover:text-white transition-colors">Email</span>
</label>
<label class="inline-flex items-center cursor-pointer gap-2 group">
<input id="notif-resultados-whatsapp" checked="" class="sr-only peer" type="checkbox" value="whatsapp"/>
<div class="relative w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
<span class="text-sm font-medium text-gray-400 group-hover:text-white transition-colors">WhatsApp</span>
</label>
</div>
</div>
<!-- Notification Item -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
<div>
<h4 class="text-white font-medium mb-1">Promociones y Ofertas</h4>
<p class="text-text-secondary text-sm">Ofertas especiales en la compra de boletos.</p>
</div>
<div class="flex gap-4">
<label class="inline-flex items-center cursor-pointer gap-2 group">
<input id="notif-promociones-email" class="sr-only peer" type="checkbox" value="email"/>
<div class="relative w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
<span class="text-sm font-medium text-gray-400 group-hover:text-white transition-colors">Email</span>
</label>
</div>
</div>
</div>
<div class="px-6 py-4 bg-[#151a23] border-t border-[#282d39] flex justify-end">
<button id="btn-guardar-preferencias" class="bg-[#282d39] hover:bg-[#323846] text-white px-6 py-2 rounded-lg font-bold text-sm transition-all border border-[#3e4552] flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
<span id="btn-preferencias-icon" class="material-symbols-outlined text-[18px]">save</span>
<span id="btn-preferencias-text">Guardar Preferencias</span>
<span id="btn-preferencias-spinner" class="hidden animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></span>
                    </button>
</div>
</section>
<!-- Payment Methods Section -->
<section id="section-metodos-pago" class="bg-[#111318] dark:bg-card-dark rounded-xl border border-[#282d39] overflow-hidden">
<div class="p-6 border-b border-[#282d39]">
<h3 class="text-white text-lg font-bold flex items-center gap-2">
<span class="material-symbols-outlined text-primary">credit_card</span>
                        Métodos de Pago
                    </h3>
</div>
<div class="p-6 lg:p-8">
<!-- Saved Payment Methods -->
<div id="saved-methods-container" class="mb-6">
<h4 class="text-white font-medium mb-4">Métodos Guardados</h4>
<div id="payment-methods-list" class="space-y-3">
<!-- Los métodos guardados se agregarán aquí dinámicamente -->
<p class="text-text-secondary text-sm">No tienes métodos de pago guardados aún.</p>
</div>
</div>
<!-- Card Type Selection -->
<div class="mb-6 pb-6 border-b border-[#282d39]">
<label class="text-white text-sm font-medium mb-3 block">Tipo de Tarjeta</label>
<div class="flex gap-4">
<label class="flex flex-col items-center gap-2 cursor-pointer group" for="card-type-visa">
<input id="card-type-visa" class="peer sr-only" type="radio" name="card-type" value="visa" checked/>
<div class="w-20 h-14 bg-white rounded-lg border-2 border-[#282d39] flex items-center justify-center peer-checked:border-primary peer-checked:ring-2 peer-checked:ring-primary/50 peer-checked:shadow-lg peer-checked:shadow-primary/20 transition-all hover:border-primary/50 hover:scale-105 overflow-hidden p-2">
<svg viewBox="0 0 200 60" class="h-8 w-auto" xmlns="http://www.w3.org/2000/svg">
<rect width="200" height="60" fill="#F7B600"/>
<text x="100" y="40" font-family="Arial, sans-serif" font-size="32" font-weight="bold" fill="#1A1F71" text-anchor="middle">VISA</text>
</svg>
</div>
<span class="text-text-secondary text-xs group-hover:text-white transition-colors peer-checked:text-primary font-medium">Visa</span>
</label>
<label class="flex flex-col items-center gap-2 cursor-pointer group" for="card-type-mastercard">
<input id="card-type-mastercard" class="peer sr-only" type="radio" name="card-type" value="mastercard"/>
<div class="w-20 h-14 bg-white rounded-lg border-2 border-[#282d39] flex items-center justify-center peer-checked:border-primary peer-checked:ring-2 peer-checked:ring-primary/50 peer-checked:shadow-lg peer-checked:shadow-primary/20 transition-all hover:border-primary/50 hover:scale-105 overflow-hidden p-2">
<svg viewBox="0 0 200 60" class="h-8 w-auto" xmlns="http://www.w3.org/2000/svg">
<circle cx="60" cy="30" r="20" fill="#EB001B"/>
<circle cx="140" cy="30" r="20" fill="#F79E1B"/>
<path d="M100 10c-11 0-20 9-20 20s9 20 20 20c5.5 0 10.5-2.2 14.1-5.8-3.6-3.6-5.8-8.6-5.8-14.2s2.2-10.6 5.8-14.2C110.5 12.2 105.5 10 100 10z" fill="#FF5F00"/>
</svg>
</div>
<span class="text-text-secondary text-xs group-hover:text-white transition-colors peer-checked:text-primary font-medium">Mastercard</span>
</label>
<label class="flex flex-col items-center gap-2 cursor-pointer group" for="card-type-amex">
<input id="card-type-amex" class="peer sr-only" type="radio" name="card-type" value="amex"/>
<div class="w-20 h-14 bg-white rounded-lg border-2 border-[#282d39] flex items-center justify-center peer-checked:border-primary peer-checked:ring-2 peer-checked:ring-primary/50 peer-checked:shadow-lg peer-checked:shadow-primary/20 transition-all hover:border-primary/50 hover:scale-105 overflow-hidden p-2">
<svg viewBox="0 0 200 60" class="h-8 w-auto" xmlns="http://www.w3.org/2000/svg">
<rect width="200" height="60" fill="#006FCF"/>
<text x="100" y="38" font-family="Arial, sans-serif" font-size="18" font-weight="bold" fill="#FFF" text-anchor="middle">AMEX</text>
</svg>
</div>
<span class="text-text-secondary text-xs group-hover:text-white transition-colors peer-checked:text-primary font-medium">Amex</span>
</label>
</div>
</div>
<!-- Add New Payment Method -->
<div>
<h4 class="text-white font-medium mb-4">Agregar Nuevo Método de Pago</h4>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<div class="flex flex-col gap-2">
<label class="text-white text-sm font-medium">Titular de la Tarjeta</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-2.5 text-text-secondary text-[20px]">person</span>
<input id="input-card-name" class="w-full bg-[#111621] border border-[#282d39] text-white text-sm rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent pl-10 py-2.5 placeholder-[#566074]" placeholder="Nombre completo" type="text" maxlength="50"/>
<span id="error-card-name" class="hidden text-red-400 text-xs mt-1"></span>
</div>
</div>
<div class="flex flex-col gap-2">
<label class="text-white text-sm font-medium">Número de Tarjeta</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-2.5 text-text-secondary text-[20px]">credit_card</span>
<input id="input-card-number" class="w-full bg-[#111621] border border-[#282d39] text-white text-sm rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent pl-10 py-2.5 placeholder-[#566074]" placeholder="1234 5678 9012 3456" type="text" maxlength="19"/>
<span id="error-card-number" class="hidden text-red-400 text-xs mt-1"></span>
</div>
</div>
<div class="flex flex-col gap-2">
<label class="text-white text-sm font-medium">Fecha de Expiración</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-2.5 text-text-secondary text-[20px]">calendar_today</span>
<input id="input-card-expiry" class="w-full bg-[#111621] border border-[#282d39] text-white text-sm rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent pl-10 py-2.5 placeholder-[#566074]" placeholder="MM/AA" type="text" maxlength="5"/>
<span id="error-card-expiry" class="hidden text-red-400 text-xs mt-1"></span>
</div>
</div>
<div class="flex flex-col gap-2">
<label class="text-white text-sm font-medium">CVV</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-2.5 text-text-secondary text-[20px]">lock</span>
<input id="input-card-cvv" class="w-full bg-[#111621] border border-[#282d39] text-white text-sm rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent pl-10 py-2.5 placeholder-[#566074]" placeholder="123" type="text" maxlength="4"/>
<span id="error-card-cvv" class="hidden text-red-400 text-xs mt-1"></span>
</div>
</div>
</div>
</div>
</div>
<div class="px-6 py-4 bg-[#151a23] border-t border-[#282d39] flex justify-end gap-3">
<button id="btn-cancelar-pago" class="bg-[#282d39] hover:bg-[#323846] text-white px-6 py-2 rounded-lg font-bold text-sm transition-all border border-[#3e4552]">
                        Cancelar
                    </button>
<button id="btn-guardar-pago" class="bg-primary hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-bold text-sm shadow-lg shadow-blue-900/20 transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
<span id="btn-pago-icon" class="material-symbols-outlined text-[18px]">save</span>
<span id="btn-pago-text">Guardar Método de Pago</span>
<span id="btn-pago-spinner" class="hidden animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></span>
                    </button>
</div>
</section>
<!-- Historial de Sorteos Section -->
<section id="section-historial-sorteos" class="bg-[#111318] dark:bg-card-dark rounded-xl border border-[#282d39] overflow-hidden">
<div class="p-6 border-b border-[#282d39]">
<h3 class="text-white text-lg font-bold flex items-center gap-2">
<span class="material-symbols-outlined text-primary">history</span>
                        Historial de Sorteos
                    </h3>
</div>
<div class="p-6 lg:p-8">
<!-- Filtros y Búsqueda -->
<div class="mb-6 flex flex-col sm:flex-row gap-4">
<div class="flex-1">
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-2.5 text-text-secondary text-[20px]">search</span>
<input id="input-buscar-historial" class="w-full bg-[#111621] border border-[#282d39] text-white text-sm rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent pl-10 py-2.5 placeholder-[#566074]" placeholder="Buscar por nombre de sorteo..." type="text"/>
</div>
</div>
<div class="flex gap-3">
<select id="select-filtro-estado" class="bg-[#111621] border border-[#282d39] text-white text-sm rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent px-4 py-2.5 min-w-[150px]">
<option value="todos">Todos los estados</option>
<option value="activo">Activos</option>
<option value="finalizado">Finalizados</option>
<option value="cancelado">Cancelados</option>
</select>
<select id="select-filtro-fecha" class="bg-[#111621] border border-[#282d39] text-white text-sm rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent px-4 py-2.5 min-w-[150px]">
<option value="todos">Todas las fechas</option>
<option value="ultimo-mes">Último mes</option>
<option value="ultimos-3-meses">Últimos 3 meses</option>
<option value="ultimo-ano">Último año</option>
</select>
<button id="btn-limpiar-filtros" class="bg-[#282d39] hover:bg-[#323846] text-white px-4 py-2.5 rounded-lg font-medium text-sm transition-all border border-[#3e4552] flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">clear_all</span>
Limpiar
</button>
</div>
</div>
<!-- Estadísticas Rápidas -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
<div class="bg-[#151a23] rounded-lg p-4 border border-[#282d39]">
<div class="flex items-center justify-between">
<div>
<p class="text-text-secondary text-xs mb-1">Total Sorteos</p>
<p id="stat-total-sorteos" class="text-white text-2xl font-bold">0</p>
</div>
<span class="material-symbols-outlined text-primary text-3xl">local_activity</span>
</div>
</div>
<div class="bg-[#151a23] rounded-lg p-4 border border-[#282d39]">
<div class="flex items-center justify-between">
<div>
<p class="text-text-secondary text-xs mb-1">Boletos Comprados</p>
<p id="stat-total-boletos" class="text-white text-2xl font-bold">0</p>
</div>
<span class="material-symbols-outlined text-green-500 text-3xl">confirmation_number</span>
</div>
</div>
<div class="bg-[#151a23] rounded-lg p-4 border border-[#282d39]">
<div class="flex items-center justify-between">
<div>
<p class="text-text-secondary text-xs mb-1">Sorteos Ganados</p>
<p id="stat-sorteos-ganados" class="text-white text-2xl font-bold">0</p>
</div>
<span class="material-symbols-outlined text-yellow-500 text-3xl">emoji_events</span>
</div>
</div>
<div class="bg-[#151a23] rounded-lg p-4 border border-[#282d39]">
<div class="flex items-center justify-between">
<div>
<p class="text-text-secondary text-xs mb-1">Total Invertido</p>
<p id="stat-total-invertido" class="text-white text-2xl font-bold">$0</p>
</div>
<span class="material-symbols-outlined text-blue-500 text-3xl">attach_money</span>
</div>
</div>
</div>
<!-- Lista de Historial -->
<div id="historial-container" class="space-y-4">
<!-- Los sorteos se cargarán aquí dinámicamente -->
<div id="historial-loading" class="flex items-center justify-center py-12">
<div class="flex flex-col items-center gap-3">
<div class="animate-spin rounded-full h-12 w-12 border-4 border-primary border-t-transparent"></div>
<p class="text-text-secondary text-sm">Cargando historial...</p>
</div>
</div>
<div id="historial-empty" class="hidden text-center py-12">
<span class="material-symbols-outlined text-text-secondary text-6xl mb-4">history</span>
<p class="text-white font-medium mb-2">No hay historial de sorteos</p>
<p class="text-text-secondary text-sm">Aún no has participado en ningún sorteo.</p>
</div>
<div id="historial-list" class="space-y-3">
<!-- Los elementos del historial se agregarán aquí -->
</div>
</div>
<!-- Paginación -->
<div id="historial-pagination" class="hidden mt-6 flex items-center justify-between border-t border-[#282d39] pt-4">
<div class="text-text-secondary text-sm">
Mostrando <span id="pagination-from">0</span> - <span id="pagination-to">0</span> de <span id="pagination-total">0</span> sorteos
</div>
<div class="flex gap-2">
<button id="btn-prev-page" class="bg-[#282d39] hover:bg-[#323846] text-white px-4 py-2 rounded-lg font-medium text-sm transition-all border border-[#3e4552] disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">chevron_left</span>
Anterior
</button>
<button id="btn-next-page" class="bg-[#282d39] hover:bg-[#323846] text-white px-4 py-2 rounded-lg font-medium text-sm transition-all border border-[#3e4552] disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
Siguiente
<span class="material-symbols-outlined text-[18px]">chevron_right</span>
</button>
</div>
</div>
</div>
</section>
</main>
</div>
<!-- Alert / Toast (Example: Hidden by default, just showing layout) -->
<!-- Remove 'hidden' class to see it -->
<div class="fixed bottom-6 right-6 hidden z-50 animate-bounce">
<div class="bg-card-dark border-l-4 border-green-500 text-white px-6 py-4 rounded shadow-xl flex items-center gap-3">
<span class="material-symbols-outlined text-green-500">check_circle</span>
<div>
<p class="font-bold text-sm">¡Guardado con éxito!</p>
<p class="text-xs text-gray-400">Tus cambios se han actualizado correctamente.</p>
</div>
<span class="material-symbols-outlined text-gray-500 cursor-pointer text-sm ml-4">close</span>
</div>
</div>
</div>
</div>
</main>
<!-- Client Layout Script -->
<script src="js/custom-alerts.js"></script>
<script src="js/client-layout.js"></script>
<script src="js/ajustes-perfil-cliente.js"></script>
<script>
// Datos del usuario desde PHP (sesión) - DEBE estar antes de inicializar ClientLayout
const userSessionData = {
    id: <?php echo intval($datosUsuario['id_usuario']); ?>,
    nombre: '<?php echo addslashes($usuarioNombre); ?>',
    tipoUsuario: '<?php echo addslashes($tipoUsuario); ?>',
    email: '<?php echo addslashes($usuarioEmail); ?>',
    saldo: <?php echo number_format($usuarioSaldo, 2, '.', ''); ?>,
    avatar: '<?php echo addslashes($usuarioAvatar); ?>'
};

// Limpiar localStorage ANTES de actualizar con los nuevos datos (para evitar datos de sesiones anteriores)
console.log('AjustesPefilCliente - Limpiando localStorage y actualizando con datos del usuario:', userSessionData);

// Verificar si hay datos antiguos en localStorage que no correspondan al usuario actual
const oldClientData = localStorage.getItem('clientData');
if (oldClientData) {
    try {
        const parsed = JSON.parse(oldClientData);
        // Si el ID del usuario en localStorage no coincide con el de la sesión, limpiar todo
        if (parsed.id && parsed.id !== userSessionData.id) {
            console.warn('AjustesPefilCliente - Detectados datos de otro usuario en localStorage. Limpiando...');
            localStorage.clear();
            sessionStorage.clear();
        }
    } catch (e) {
        console.warn('AjustesPefilCliente - Error al verificar datos antiguos, limpiando localStorage:', e);
        localStorage.clear();
        sessionStorage.clear();
    }
}

// Actualizar localStorage con los datos de la sesión ANTES de inicializar ClientLayout
if (userSessionData.nombre && userSessionData.tipoUsuario) {
    const sessionClientData = {
        id: userSessionData.id,
        nombre: userSessionData.nombre,
        tipoUsuario: userSessionData.tipoUsuario,
        email: userSessionData.email,
        saldo: userSessionData.saldo,
        fotoPerfil: userSessionData.avatar || 'https://lh3.googleusercontent.com/aida-public/AB6AXuAscTJ1Xcq7edw4JqzzGbgOvjdyQ9_nDg7kkxtlCQw51-EJsv1RJyDd9OAZC89eniVl2ujzIik6wgxd5FTvho_ak6ccsWrWelinVwXj6yQUdpPUXYUTJN0pSvhRh-smWf81cMQz40x4U3setrSFDsyX4KkfxOsHc6PnTND68lGw6JkA9B0ag_4fNu5s0Z9OMbq83llAZUv3xuo3s6VI1no110ozE88mRALnX-rhgavHoJxmYpvBcUxV7BtrJr_9Q0BlgvZQL2BXCFg'
    };
    localStorage.setItem('clientData', JSON.stringify(sessionClientData));
    sessionStorage.setItem('clientData', JSON.stringify(sessionClientData));
    console.log('AjustesPefilCliente - localStorage actualizado con datos del usuario:', sessionClientData);
}

// Inicializar layout del cliente
document.addEventListener('DOMContentLoaded', function() {
    if (window.ClientLayout) {
        ClientLayout.init('perfil');
    }
});
</script>

</body></html>
