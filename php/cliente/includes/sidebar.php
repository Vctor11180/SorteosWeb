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
                <div id="sidebar-user-avatar" class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-12 ring-2 ring-primary/30 ring-offset-2 ring-offset-[#111318] shadow-lg" data-alt="User profile picture" style='background-image: url("<?php echo htmlspecialchars($usuarioAvatar ?? "https://via.placeholder.com/150"); ?>");'>
                </div>
                <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-[#111318] shadow-lg"></div>
            </div>
            <div class="flex flex-col overflow-hidden">
                <h1 id="sidebar-user-name" class="text-white text-sm font-bold truncate tracking-tight"><?php echo htmlspecialchars($usuarioNombre ?? 'Usuario'); ?></h1>
                <p id="sidebar-user-type" class="text-primary/80 text-xs font-medium truncate"><?php echo htmlspecialchars($tipoUsuario ?? 'Cliente'); ?></p>
            </div>
        </div>
        <!-- Navigation -->
        <nav class="flex flex-col gap-2">
            <a id="nav-dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl text-[#9da6b9] hover:bg-gradient-to-r hover:from-[#282d39]/80 hover:to-[#323846]/50 hover:text-white transition-all duration-200 hover:shadow-md" href="DashboardCliente.php" data-page="DashboardCliente.php">
                <span class="material-symbols-outlined text-xl">dashboard</span>
                <p class="text-sm font-medium">Dashboard</p>
            </a>
            <a id="nav-sorteos" class="flex items-center gap-3 px-4 py-3 rounded-xl text-[#9da6b9] hover:bg-gradient-to-r hover:from-[#282d39]/80 hover:to-[#323846]/50 hover:text-white transition-all duration-200 hover:shadow-md" href="ListadoSorteosActivos.php" data-page="ListadoSorteosActivos.php">
                <span class="material-symbols-outlined text-xl">local_activity</span>
                <p class="text-sm font-medium">Sorteos</p>
            </a>
            <a id="nav-boletos" class="flex items-center gap-3 px-4 py-3 rounded-xl text-[#9da6b9] hover:bg-gradient-to-r hover:from-[#282d39]/80 hover:to-[#323846]/50 hover:text-white transition-all duration-200 hover:shadow-md" href="MisBoletosCliente.php" data-page="MisBoletosCliente.php">
                <span class="material-symbols-outlined text-xl">confirmation_number</span>
                <p class="text-sm font-medium">Mis Boletos</p>
            </a>
            <a id="nav-ganadores" class="flex items-center gap-3 px-4 py-3 rounded-xl text-[#9da6b9] hover:bg-gradient-to-r hover:from-[#282d39]/80 hover:to-[#323846]/50 hover:text-white transition-all duration-200 hover:shadow-md" href="MisGanancias.php" data-page="MisGanancias.php">
                <span class="material-symbols-outlined text-xl">emoji_events</span>
                <p class="text-sm font-medium">Ganadores</p>
            </a>
            <a id="nav-perfil" class="flex items-center gap-3 px-4 py-3 rounded-xl text-[#9da6b9] hover:bg-gradient-to-r hover:from-[#282d39]/80 hover:to-[#323846]/50 hover:text-white transition-all duration-200 hover:shadow-md" href="AjustesPefilCliente.php" data-page="AjustesPefilCliente.php">
                <span class="material-symbols-outlined text-xl">person</span>
                <p class="text-sm font-medium">Perfil</p>
            </a>
            <a id="nav-soporte" class="flex items-center gap-3 px-4 py-3 rounded-xl text-[#9da6b9] hover:bg-gradient-to-r hover:from-[#282d39]/80 hover:to-[#323846]/50 hover:text-white transition-all duration-200 hover:shadow-md" href="ContactoSoporteCliente.php" data-page="ContactoSoporteCliente.php">
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

<script>
// Lógica para marcar el enlace activo del sidebar
document.addEventListener('DOMContentLoaded', function() {
    const minPathName = window.location.pathname.split('/').pop() || window.location.href.split('/').pop();
    const navLinks = document.querySelectorAll('nav a');
    
    navLinks.forEach(link => {
        const pageName = link.getAttribute('data-page') || link.getAttribute('href');
        if (pageName === minPathName) {
            // Aplicar estilo activo
            link.className = 'flex items-center gap-3 px-4 py-3 rounded-xl bg-gradient-to-r from-primary to-blue-600 text-white shadow-lg shadow-primary/20 transition-all duration-200 hover:shadow-xl hover:shadow-primary/30';
            const p = link.querySelector('p');
            if(p) p.classList.add('font-bold');
            if(p) p.classList.remove('font-medium');
        } else {
            // Estilo por defecto
            link.className = 'flex items-center gap-3 px-4 py-3 rounded-xl text-[#9da6b9] hover:bg-gradient-to-r hover:from-[#282d39]/80 hover:to-[#323846]/50 hover:text-white transition-all duration-200 hover:shadow-md';
        }
    });

    // Lógica del botón logout
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.replaceWith(logoutBtn.cloneNode(true)); // Limpiar listeners previos
        const newLogoutBtn = document.getElementById('logout-btn');
        
        newLogoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (typeof customConfirm === 'function') {
                customConfirm('¿Estás seguro de que deseas cerrar sesión?', 'Cerrar Sesión', 'warning').then(confirmed => {
                   if (confirmed) processLogout();
                });
            } else {
                if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                    processLogout();
                }
            }
        });
    }

    function processLogout() {
        localStorage.removeItem('clientData');
        sessionStorage.removeItem('clientData');
        
        // Determinar ruta relativa al logout.php dependiendo de la ubicación actual
        // Asumiendo estructura standard php/cliente/pages/
        window.location.href = '../auth/logout.php';
    }
});
</script>
