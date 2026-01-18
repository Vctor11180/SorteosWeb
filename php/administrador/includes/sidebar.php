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
    <div class="p-4 border-t border-gray-200 dark:border-border-dark relative">
        <div id="admin-user-menu-trigger" class="flex items-center gap-3 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg p-2 transition-colors">
            <div class="w-10 h-10 rounded-full bg-cover bg-center" data-alt="User profile picture" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAfIzDdUJZk0e1bBHKOe7BG0HPanJ3nx8d9vtsJZZMiXM6ZJw9-oPch2DQWyWWrowTikKHJBUkhOyI6hUEiy_TgTGdRmm-4uDyO3KjasL500lcWogtry5HOXaJxBgDxpuT_8QBEVTnbuI4727c7c5qtPNid2CyQr0SnpyEcv2R9UEoiXiOVUH_g0RdYwYfb9u5EU5DkqEZl2oL9UW9s45D-zD3htPmEHk69TrCVPL50vnE6cDfTlcz9AJEZo7Hb8gpAhxwAxDP4SCs');"></div>
            <div class="flex flex-col flex-1">
                <span class="text-sm font-medium text-slate-900 dark:text-white">Admin User</span>
                <span class="text-xs text-gray-500">admin@sorteos.web</span>
            </div>
            <span class="material-symbols-outlined text-gray-500 text-lg">arrow_drop_down</span>
        </div>
        <!-- Dropdown Menu -->
        <div id="admin-user-menu" class="hidden absolute bottom-full left-0 right-0 mb-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 overflow-hidden">
            <button id="admin-logout-btn" onclick="handleLogoutAdmin()" class="w-full flex items-center gap-3 px-4 py-3 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <span class="material-symbols-outlined text-[20px]">logout</span>
                <span>Cerrar Sesión</span>
            </button>
        </div>
    </div>
</aside>

<script>
// Logic shared by the sidebar
document.addEventListener('DOMContentLoaded', function() {
    const menuTrigger = document.getElementById('admin-user-menu-trigger');
    const menu = document.getElementById('admin-user-menu');
    
    if (menuTrigger && menu) {
        menuTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            menu.classList.toggle('hidden');
        });
        
        document.addEventListener('click', function(e) {
            if (!menuTrigger.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') menu.classList.add('hidden');
        });
    }

    // Set Active State based on URL
    const currentPage = window.location.pathname.split('/').pop() || window.location.href.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const linkPage = link.getAttribute('data-page') || link.getAttribute('href');
        if (linkPage === currentPage || link.getAttribute('href') === currentPage) {
            link.className = 'nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary/10 text-primary font-medium';
            const icon = link.querySelector('.material-symbols-outlined');
            if (icon) icon.classList.remove('group-hover:text-primary', 'transition-colors');
        } else {
            link.className = 'nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors group';
            const icon = link.querySelector('.material-symbols-outlined');
            if (icon && !icon.classList.contains('group-hover:text-primary')) {
                icon.classList.add('group-hover:text-primary', 'transition-colors');
            }
        }
    });
});

function handleLogoutAdmin() {
    if (typeof customConfirm === 'function') {
        customConfirm('¿Estás seguro de que deseas cerrar sesión?', 'Cerrar Sesión', 'warning').then(confirmed => {
            if (confirmed) window.location.href = 'logout.php';
        });
    } else {
        if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
            window.location.href = 'logout.php';
        }
    }
}
</script>
