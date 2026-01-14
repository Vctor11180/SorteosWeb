<?php
/**
 * template-helper
 * Sistema de Sorteos Web
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación para páginas protegidas
$protectedPages = ['DashboardCliente', 'AjustesPefilCliente', 'MisBoletosCliente', 'MisGanancias', 'SeleccionBoletos', 'SorteoClienteDetalles', 'FinalizarPagoBoletos'];
if (in_array('template-helper', $protectedPages) && (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true)) {
    header('Location: InicioSesion.php');
    exit;
}
?>
<!-- PLANTILLA DE ESTRUCTURA PARA PÁGINAS DEL CLIENTE -->
<!-- 
  Esta es una plantilla de referencia para aplicar el layout reutilizable.
  Copia esta estructura en cada página del cliente.
-->

<!DOCTYPE html>
<html class="dark" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>[Título de la Página] - Sorteos Web</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet"/>
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
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
<body class="bg-background-light dark:bg-background-dark text-white font-display overflow-hidden h-screen flex">
    <!-- Sidebar Container - Layout reutilizable -->
    <div id="client-sidebar-container"></div>
    
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
            
            <!-- Page Title or Custom Header Content -->
            <h1 class="text-xl font-bold text-white hidden sm:block">[Título de la Página]</h1>
            
            <!-- Right Actions (opcional) -->
            <div class="ml-auto"></div>
        </header>
        
        <!-- Scrollable Content Area -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden p-6 lg:p-10">
            <!-- CONTENIDO ESPECÍFICO DE LA PÁGINA AQUÍ -->
        </div>
    </main>

    <!-- Client Layout Script -->
    <script src="js/client-layout.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.ClientLayout) {
                // Inicializar con el ID de la página actual:
                // 'dashboard', 'boletos', 'ganadores', 'perfil', 'soporte'
                ClientLayout.init('[id-de-pagina]');
            }
        });
    </script>
</body>
</html>


