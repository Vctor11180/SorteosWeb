<?php
/**
 * FAQCliente
 * Sistema de Sorteos Web
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación para páginas protegidas
$protectedPages = ['DashboardCliente', 'AjustesPefilCliente', 'MisBoletosCliente', 'MisGanancias', 'SeleccionBoletos', 'SorteoClienteDetalles', 'FinalizarPagoBoletos'];
if (in_array('FAQCliente', $protectedPages) && (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true)) {
    header('Location: InicioSesion.php');
    exit;
}
?>
<!DOCTYPE html>

<html class="dark" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>FAQ Cliente - Sorteos Web</title>
<!-- Fonts -->
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
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
                        "text-answer": "#9CA3AF",
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
        /* Custom scrollbar for webkit */
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
        
        /* Remove default marker from details/summary */
        details > summary {
            list-style: none;
        }
        details > summary::-webkit-details-marker {
            display: none;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white font-display overflow-x-hidden h-screen flex">
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
<a id="nav-boletos" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-text-secondary hover:text-white hover:bg-card-dark transition-colors group" href="MisBoletosCliente.php">
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
<a id="nav-soporte" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary text-white group transition-colors" href="ContactoSoporteCliente.php">
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
<input class="block w-full pl-10 pr-3 py-2 border-none rounded-lg leading-5 bg-card-dark text-white placeholder-text-secondary focus:outline-none focus:ring-1 focus:ring-primary sm:text-sm" placeholder="Buscar preguntas..." type="text"/>
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
<div class="flex-1 overflow-y-auto overflow-x-hidden">
<!-- Breadcrumbs & Hero Area -->
<div class="relative w-full bg-gradient-to-b from-[#111318] to-[#161b26] pt-8 pb-12 px-6 lg:px-10 -mx-6 lg:-mx-10">
<div class="max-w-4xl mx-auto space-y-6 px-6 lg:px-10">
<!-- Breadcrumbs -->
<nav class="flex items-center text-sm text-text-secondary">
<a class="hover:text-white transition-colors flex items-center gap-1" href="DashboardCliente.php">
<span class="material-symbols-outlined text-[18px]">home</span>
</a>
<span class="mx-2 text-text-secondary">/</span>
<a class="hover:text-white transition-colors" href="ContactoSoporteCliente.php">Ayuda</a>
<span class="mx-2 text-text-secondary">/</span>
<span class="text-white font-medium">Preguntas Frecuentes</span>
</nav>
<!-- Page Heading -->
<div class="text-center md:text-left space-y-4">
<h1 class="text-3xl md:text-5xl font-black tracking-tight text-white">
                        ¿En qué podemos <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-blue-400">ayudarte?</span>
</h1>
<p class="text-base md:text-lg text-text-secondary max-w-2xl">
                        Encuentra respuestas rápidas sobre cómo participar, realizar pagos y reclamar premios. Estamos aquí para asegurar que tu experiencia sea perfecta.
                    </p>
</div>
<!-- Main Search Bar -->
<div class="relative w-full max-w-2xl mt-8">
<div class="relative group">
<div class="absolute -inset-0.5 bg-gradient-to-r from-primary to-blue-600 rounded-xl opacity-30 group-focus-within:opacity-100 transition duration-500 blur"></div>
<div class="relative flex items-center bg-card-dark rounded-xl h-14 px-4 shadow-xl border border-[#282d39]">
<span class="material-symbols-outlined text-text-secondary text-[24px]">search</span>
<input class="w-full bg-transparent border-none focus:ring-0 text-white placeholder-text-secondary text-lg ml-3" placeholder="Busca una pregunta (ej. cómo comprar boletos, pagos...)" type="text"/>
</div>
</div>
</div>
</div>
</div>
<!-- FAQ Content Section -->
<div class="w-full px-6 lg:px-10 pb-20">
<div class="max-w-4xl mx-auto">
<!-- Category Chips -->
<div class="flex flex-wrap gap-3 py-6 mb-6" id="faq-filter-buttons">
<button data-filter="todo" class="faq-filter-btn flex h-9 items-center justify-center px-5 rounded-full bg-primary text-white text-sm font-semibold shadow-md shadow-primary/20 transition-transform hover:scale-105">
                        Todo
                    </button>
<button data-filter="sorteos" class="faq-filter-btn flex h-9 items-center justify-center px-5 rounded-full bg-card-dark hover:bg-[#353b4b] border border-[#282d39] text-white text-sm font-medium transition-all hover:scale-105">
<span class="material-symbols-outlined text-[18px] mr-2">confirmation_number</span>
                        Sobre Sorteos
                    </button>
<button data-filter="pagos" class="faq-filter-btn flex h-9 items-center justify-center px-5 rounded-full bg-card-dark hover:bg-[#353b4b] border border-[#282d39] text-white text-sm font-medium transition-all hover:scale-105">
<span class="material-symbols-outlined text-[18px] mr-2">payments</span>
                        Pagos
                    </button>
<button data-filter="cuenta" class="faq-filter-btn flex h-9 items-center justify-center px-5 rounded-full bg-card-dark hover:bg-[#353b4b] border border-[#282d39] text-white text-sm font-medium transition-all hover:scale-105">
<span class="material-symbols-outlined text-[18px] mr-2">account_circle</span>
                        Mi Cuenta
                    </button>
<button data-filter="ganadores" class="faq-filter-btn flex h-9 items-center justify-center px-5 rounded-full bg-card-dark hover:bg-[#353b4b] border border-[#282d39] text-white text-sm font-medium transition-all hover:scale-105">
<span class="material-symbols-outlined text-[18px] mr-2">emoji_events</span>
                        Ganadores
                    </button>
</div>
<!-- Questions List -->
<div class="space-y-4">
<!-- Section: Sobre Sorteos (Using Details/Summary for Accordion) -->
<div class="mb-8 faq-section" data-category="sorteos">
<h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
<span class="material-symbols-outlined text-primary">confirmation_number</span>
                            Sobre Sorteos
                        </h3>
<div class="space-y-3">
<!-- Question 1 -->
<details class="group bg-card-dark rounded-xl border border-[#282d39] overflow-hidden transition-all duration-300 hover:border-primary/50 open:ring-1 open:ring-primary/50">
<summary class="flex items-center justify-between p-5 cursor-pointer select-none">
<h4 class="text-base font-semibold text-white group-hover:text-primary transition-colors">¿Cómo puedo comprar un boleto para el sorteo?</h4>
<span class="material-symbols-outlined text-gray-400 group-hover:text-primary transition-transform duration-300 group-open:rotate-180">expand_more</span>
</summary>
<div class="px-5 pb-5 pt-0">
<p class="text-text-answer leading-relaxed text-sm">
                                        Para comprar un boleto, primero debes registrarte en nuestra plataforma. Luego, navega a la sección de <a class="text-primary hover:underline" href="ListadoSorteosActivos.php">Sorteos Activos</a>, selecciona el sorteo de tu interés, elige tus números de la suerte y procede al pago mediante nuestros métodos seguros.
                                    </p>
</div>
</details>
<!-- Question 2 -->
<details class="group bg-card-dark rounded-xl border border-[#282d39] overflow-hidden transition-all duration-300 hover:border-primary/50 open:ring-1 open:ring-primary/50">
<summary class="flex items-center justify-between p-5 cursor-pointer select-none">
<h4 class="text-base font-semibold text-white group-hover:text-primary transition-colors">¿Cuándo se anuncian los ganadores?</h4>
<span class="material-symbols-outlined text-gray-400 group-hover:text-primary transition-transform duration-300 group-open:rotate-180">expand_more</span>
</summary>
<div class="px-5 pb-5 pt-0">
<p class="text-text-answer leading-relaxed text-sm">
                                        Los ganadores se anuncian inmediatamente después de que finaliza el sorteo en vivo. Recibirás una notificación por correo electrónico y SMS si resultas ganador. También puedes consultar la lista oficial en la sección de <a class="text-primary hover:underline" href="MisGanancias.php">Ganadores</a>.
                                    </p>
</div>
</details>
<!-- Question 3 -->
<details class="group bg-card-dark rounded-xl border border-[#282d39] overflow-hidden transition-all duration-300 hover:border-primary/50 open:ring-1 open:ring-primary/50">
<summary class="flex items-center justify-between p-5 cursor-pointer select-none">
<h4 class="text-base font-semibold text-white group-hover:text-primary transition-colors">¿Hay un límite de boletos que puedo comprar?</h4>
<span class="material-symbols-outlined text-gray-400 group-hover:text-primary transition-transform duration-300 group-open:rotate-180">expand_more</span>
</summary>
<div class="px-5 pb-5 pt-0">
<p class="text-text-answer leading-relaxed text-sm">
                                        Sí, cada sorteo tiene un límite máximo de boletos por usuario para garantizar la equidad. Este límite varía según el premio y se especifica en los detalles de cada sorteo antes de la compra.
                                    </p>
</div>
</details>
</div>
</div>
<!-- Section: Pagos -->
<div class="mb-8 faq-section" data-category="pagos">
<h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
<span class="material-symbols-outlined text-primary">payments</span>
                            Pagos y Reembolsos
                        </h3>
<div class="space-y-3">
<!-- Question 4 -->
<details class="group bg-card-dark rounded-xl border border-[#282d39] overflow-hidden transition-all duration-300 hover:border-primary/50 open:ring-1 open:ring-primary/50">
<summary class="flex items-center justify-between p-5 cursor-pointer select-none">
<h4 class="text-base font-semibold text-white group-hover:text-primary transition-colors">¿Qué métodos de pago aceptan?</h4>
<span class="material-symbols-outlined text-gray-400 group-hover:text-primary transition-transform duration-300 group-open:rotate-180">expand_more</span>
</summary>
<div class="px-5 pb-5 pt-0">
<p class="text-text-answer leading-relaxed text-sm">
                                        Aceptamos tarjetas de crédito y débito (Visa, Mastercard), transferencias bancarias directas y pagos a través de plataformas digitales como PayPal y MercadoPago. Todas las transacciones están encriptadas para tu seguridad.
                                    </p>
<div class="mt-3 flex gap-2 opacity-50">
<div class="h-6 w-10 bg-gray-600 rounded"></div>
<div class="h-6 w-10 bg-gray-600 rounded"></div>
<div class="h-6 w-10 bg-gray-600 rounded"></div>
</div>
</div>
</details>
<!-- Question 5 -->
<details class="group bg-card-dark rounded-xl border border-[#282d39] overflow-hidden transition-all duration-300 hover:border-primary/50 open:ring-1 open:ring-primary/50">
<summary class="flex items-center justify-between p-5 cursor-pointer select-none">
<h4 class="text-base font-semibold text-white group-hover:text-primary transition-colors">¿Puedo solicitar un reembolso si me equivoco de número?</h4>
<span class="material-symbols-outlined text-gray-400 group-hover:text-primary transition-transform duration-300 group-open:rotate-180">expand_more</span>
</summary>
<div class="px-5 pb-5 pt-0">
<p class="text-text-answer leading-relaxed text-sm">
                                        Lamentablemente, una vez que el boleto ha sido comprado y el número asignado, no podemos ofrecer reembolsos ni cambios debido a la naturaleza del sorteo en tiempo real. Te recomendamos revisar bien tu selección antes de confirmar el pago.
                                    </p>
</div>
</details>
</div>
</div>
<!-- Section: Mi Cuenta -->
<div class="mb-8 faq-section" data-category="cuenta">
<h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
<span class="material-symbols-outlined text-primary">account_circle</span>
                            Mi Cuenta
                        </h3>
<div class="space-y-3">
<!-- Question 6 -->
<details class="group bg-card-dark rounded-xl border border-[#282d39] overflow-hidden transition-all duration-300 hover:border-primary/50 open:ring-1 open:ring-primary/50">
<summary class="flex items-center justify-between p-5 cursor-pointer select-none">
<h4 class="text-base font-semibold text-white group-hover:text-primary transition-colors">Olvidé mi contraseña, ¿cómo la recupero?</h4>
<span class="material-symbols-outlined text-gray-400 group-hover:text-primary transition-transform duration-300 group-open:rotate-180">expand_more</span>
</summary>
<div class="px-5 pb-5 pt-0">
<p class="text-text-answer leading-relaxed text-sm">
                                        Ve a la página de inicio de sesión y haz clic en "¿Olvidaste tu contraseña?". Ingresa el correo electrónico asociado a tu cuenta y te enviaremos un enlace seguro para restablecerla.
                                    </p>
</div>
</details>
</div>
</div>
<!-- Section: Ganadores -->
<div class="mb-8 faq-section" data-category="ganadores">
<h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
<span class="material-symbols-outlined text-primary">emoji_events</span>
                            Ganadores
                        </h3>
<div class="space-y-3">
<!-- Question 7 -->
<details class="group bg-card-dark rounded-xl border border-[#282d39] overflow-hidden transition-all duration-300 hover:border-primary/50 open:ring-1 open:ring-primary/50">
<summary class="flex items-center justify-between p-5 cursor-pointer select-none">
<h4 class="text-base font-semibold text-white group-hover:text-primary transition-colors">¿Cómo sé si gané un sorteo?</h4>
<span class="material-symbols-outlined text-gray-400 group-hover:text-primary transition-transform duration-300 group-open:rotate-180">expand_more</span>
</summary>
<div class="px-5 pb-5 pt-0">
<p class="text-text-answer leading-relaxed text-sm">
                                        Si resultas ganador, recibirás una notificación inmediata por correo electrónico y SMS. También puedes verificar tu estado en la sección de <a class="text-primary hover:underline" href="MisGanancias.php">Mis Ganancias</a> en tu cuenta, donde se mostrará un resumen de todos tus premios y su estado de entrega.
                                    </p>
</div>
</details>
<!-- Question 8 -->
<details class="group bg-card-dark rounded-xl border border-[#282d39] overflow-hidden transition-all duration-300 hover:border-primary/50 open:ring-1 open:ring-primary/50">
<summary class="flex items-center justify-between p-5 cursor-pointer select-none">
<h4 class="text-base font-semibold text-white group-hover:text-primary transition-colors">¿Cómo reclamar mi premio?</h4>
<span class="material-symbols-outlined text-gray-400 group-hover:text-primary transition-transform duration-300 group-open:rotate-180">expand_more</span>
</summary>
<div class="px-5 pb-5 pt-0">
<p class="text-text-answer leading-relaxed text-sm">
                                        Una vez que recibas la notificación de que ganaste, ve a la sección de <a class="text-primary hover:underline" href="MisGanancias.php">Mis Ganancias</a> y haz clic en el botón "Reclamar" del premio correspondiente. Te pediremos que proporciones información de contacto para coordinar la entrega. Nuestro equipo se pondrá en contacto contigo en un plazo de 24-48 horas.
                                    </p>
</div>
</details>
</div>
</div>
</div>
<!-- Still need help? CTA -->
<div class="mt-12 rounded-2xl bg-gradient-to-br from-card-dark to-[#0f1219] border border-[#282d39] p-8 text-center relative overflow-hidden">
<div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-primary/10 blur-3xl"></div>
<div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-64 h-64 rounded-full bg-primary/5 blur-3xl"></div>
<div class="relative z-10 flex flex-col items-center">
<div class="h-12 w-12 rounded-full bg-primary/20 flex items-center justify-center mb-4 text-primary">
<span class="material-symbols-outlined">support_agent</span>
</div>
<h3 class="text-xl font-bold text-white mb-2">¿No encontraste lo que buscabas?</h3>
<p class="text-text-secondary mb-6 max-w-md mx-auto">Nuestro equipo de soporte está disponible 24/7 para ayudarte con cualquier duda o problema técnico.</p>
<div class="flex gap-4 flex-wrap justify-center">
<a href="ContactoSoporteCliente.php" class="flex items-center gap-2 bg-primary hover:bg-blue-600 text-white font-bold py-2.5 px-6 rounded-lg transition-all shadow-lg shadow-primary/20">
<span class="material-symbols-outlined text-[20px]">chat</span>
                                Chat en Vivo
                            </a>
<a href="ContactoSoporteCliente.php" class="flex items-center gap-2 bg-card-dark hover:bg-[#353b4b] border border-[#282d39] text-white font-medium py-2.5 px-6 rounded-lg transition-all">
<span class="material-symbols-outlined text-[20px]">mail</span>
                                Enviar Correo
                            </a>
</div>
</div>
</div>
</div>
<!-- Footer Spacing -->
<div class="h-10"></div>
</div>
</main>

<!-- Client Layout Script -->
<script src="js/client-layout.js"></script>
<script src="js/custom-alerts.js"></script>
<script>
// Inicializar layout del cliente
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar el layout con 'soporte' como página activa (FAQ está bajo soporte)
    if (window.ClientLayout) {
        ClientLayout.init('soporte');
    }
    
    // Inicializar funcionalidad de filtros FAQ
    initFAQFilters();
    
    // Verificar si hay un parámetro de sección en la URL
    const urlParams = new URLSearchParams(window.location.search);
    const section = urlParams.get('section');
    
    if (section) {
        // Esperar un momento para que el DOM esté completamente cargado
        setTimeout(() => {
            activateSection(section);
        }, 100);
    }
});

// Función para activar una sección específica
function activateSection(sectionName) {
    const filterButtons = document.querySelectorAll('.faq-filter-btn');
    const faqSections = document.querySelectorAll('.faq-section');
    
    // Buscar el botón correspondiente
    const targetButton = Array.from(filterButtons).find(btn => 
        btn.getAttribute('data-filter') === sectionName
    );
    
    if (targetButton) {
        // Remover estilos activos de todos los botones
        filterButtons.forEach(btn => {
            btn.classList.remove('bg-primary', 'text-white', 'font-semibold', 'shadow-md', 'shadow-primary/20');
            btn.classList.add('bg-card-dark', 'hover:bg-[#353b4b]', 'border', 'border-[#282d39]', 'text-white', 'font-medium');
        });
        
        // Agregar estilos activos al botón objetivo
        targetButton.classList.remove('bg-card-dark', 'hover:bg-[#353b4b]', 'border', 'border-[#282d39]', 'text-white', 'font-medium');
        targetButton.classList.add('bg-primary', 'text-white', 'font-semibold', 'shadow-md', 'shadow-primary/20');
        
        // Filtrar secciones
        if (sectionName === 'todo') {
            // Mostrar todas las secciones
            faqSections.forEach(section => {
                section.style.display = '';
            });
        } else {
            // Mostrar solo la sección correspondiente
            faqSections.forEach(section => {
                const category = section.getAttribute('data-category');
                if (category === sectionName) {
                    section.style.display = '';
                } else {
                    section.style.display = 'none';
                }
            });
        }
        
        // Scroll suave hacia la sección
        const targetSection = document.querySelector(`.faq-section[data-category="${sectionName}"]`);
        if (targetSection) {
            setTimeout(() => {
                targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }
    }
}

// Función para inicializar filtros de FAQ
function initFAQFilters() {
    const filterButtons = document.querySelectorAll('.faq-filter-btn');
    const faqSections = document.querySelectorAll('.faq-section');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            // Remover estilos activos de todos los botones
            filterButtons.forEach(btn => {
                btn.classList.remove('bg-primary', 'text-white', 'font-semibold', 'shadow-md', 'shadow-primary/20');
                btn.classList.add('bg-card-dark', 'hover:bg-[#353b4b]', 'border', 'border-[#282d39]', 'text-white', 'font-medium');
            });
            
            // Agregar estilos activos al botón clickeado
            this.classList.remove('bg-card-dark', 'hover:bg-[#353b4b]', 'border', 'border-[#282d39]', 'text-white', 'font-medium');
            this.classList.add('bg-primary', 'text-white', 'font-semibold', 'shadow-md', 'shadow-primary/20');
            
            // Filtrar secciones
            if (filter === 'todo') {
                // Mostrar todas las secciones
                faqSections.forEach(section => {
                    section.style.display = '';
                });
            } else {
                // Mostrar solo la sección correspondiente
                faqSections.forEach(section => {
                    const category = section.getAttribute('data-category');
                    if (category === filter) {
                        section.style.display = '';
                    } else {
                        section.style.display = 'none';
                    }
                });
            }
            
            // Scroll suave hacia arriba de las preguntas
            const questionsContainer = document.querySelector('.space-y-4');
            if (questionsContainer) {
                questionsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
}
</script>

</body></html>

<!-- Página para ver las preguntas frecuentes como cliente después de iniciar sesión
     Se ve esta página para ver las preguntas frecuentes y las respuestas a las mismas. -->
