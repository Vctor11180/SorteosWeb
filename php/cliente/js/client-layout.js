/**
 * Cliente Layout Module
 * Layout lateral reutilizable para todas las p?ginas del cliente
 * Incluye sidebar con navegaci?n, perfil de usuario y men? m?vil
 */

const ClientLayout = {
    // Configuraci?n de rutas de navegaci?n
    navigationRoutes: {
        dashboard: {
            path: 'DashboardCliente.php',
            icon: 'dashboard',
            label: 'Dashboard',
            id: 'nav-dashboard'
        },
        sorteos: {
            path: 'ListadoSorteosActivos.php',
            icon: 'local_activity',
            label: 'Sorteos',
            id: 'nav-sorteos'
        },
        boletos: {
            path: 'MisBoletosCliente.php',
            icon: 'confirmation_number',
            label: 'Mis Boletos',
            id: 'nav-boletos'
        },
        ganadores: {
            path: 'MisGanancias.php',
            icon: 'emoji_events',
            label: 'Ganadores',
            id: 'nav-ganadores'
        },
        perfil: {
            path: 'AjustesPefilCliente.php',
            icon: 'person',
            label: 'Perfil',
            id: 'nav-perfil'
        },
        soporte: {
            path: 'ContactoSoporteCliente.php',
            icon: 'support_agent',
            label: 'Soporte',
            id: 'nav-soporte'
        }
    },

    // Estado del layout
    state: {
        mobileMenuOpen: false,
        currentPage: null,
        clientData: null
    },

    /**
     * Inicializa el layout en la p?gina
     * @param {string} currentPageId - ID de la p?gina actual para marcar como activa
     */
    init(currentPageId = null) {
        this.state.currentPage = currentPageId || this.detectCurrentPage();
        this.loadClientData();
        
        // Verificar que renderNavigationLinks existe antes de llamarlo
        if (typeof this.renderNavigationLinks === 'function') {
            this.renderMobileMenu();
        } else {
            console.error('renderNavigationLinks no está definida. El menú móvil no se renderizará.');
        }
        
        this.attachEventListeners();
        this.updateActiveNavigation();
        this.updateUserInfo();
    },

    /**
     * Detecta la p?gina actual bas?ndose en el nombre del archivo
     */
    detectCurrentPage() {
        const currentPath = window.location.pathname.split('/').pop() || '';
        const currentHref = window.location.href;
        
        const pageMap = {
            'DashboardCliente.php': 'dashboard',
            'DashboardCliente.html': 'dashboard',
            'ListadoSorteosActivos.php': 'sorteos',
            'ListadoSorteosActivos.html': 'sorteos',
            'MisBoletosCliente.php': 'boletos',
            'MisBoletosCliente.html': 'boletos',
            'MisGanancias.php': 'ganadores',
            'MisGanancias.html': 'ganadores',
            'AjustesPefilCliente.php': 'perfil',
            'AjustesPefilCliente.html': 'perfil',
            'ContactoSoporteCliente.php': 'soporte',
            'ContactoSoporteCliente.html': 'soporte',
            'SorteoClienteDetalles.php': 'sorteos',
            'SorteoClienteDetalles.html': 'sorteos',
            'SeleccionBoletos.php': 'sorteos',
            'SeleccionBoletos.html': 'sorteos',
            'FinalizarPagoBoletos.php': 'boletos',
            'FinalizarPagoBoletos.html': 'boletos',
            'FAQCliente.php': 'soporte',
            'FAQCliente.html': 'soporte',
            'TerminosCondicionesCliente.php': 'soporte',
            'TerminosCondicionesCliente.html': 'soporte'
        };
        
        // Primero intentar por nombre de archivo
        if (pageMap[currentPath]) {
            return pageMap[currentPath];
        }
        
        // Intentar detectar desde la URL completa
        for (const [file, page] of Object.entries(pageMap)) {
            if (currentHref.includes(file)) {
                return page;
            }
        }
        
        return null;
    },

    /**
     * Carga los datos del cliente desde localStorage/sessionStorage
     */
    loadClientData() {
        // Valores por defecto (solo se usan si no hay datos en localStorage)
        let clientData = {
            nombre: 'Usuario',
            tipoUsuario: 'Usuario Premium',
            fotoPerfil: 'https://lh3.googleusercontent.com/aida-public/AB6AXuAscTJ1Xcq7edw4JqzzGbgOvjdyQ9_nDg7kkxtlCQw51-EJsv1RJyDd9OAZC89eniVl2ujzIik6wgxd5FTvho_ak6ccsWrWelinVwXj6yQUdpPUXYUTJN0pSvhRh-smWf81cMQz40x4U3setrSFDsyX4KkfxOsHc6PnTND68lGw6JkA9B0ag_4fNu5s0Z9OMbq83llAZUv3xuo3s6VI1no110ozE88mRALnX-rhgavHoJxmYpvBcUxV7BtrJr_9Q0BlgvZQL2BXCFg',
            saldo: 0.00
        };

        try {
            // Priorizar datos de localStorage (que vienen de la sesi?n PHP)
            const storedData = localStorage.getItem('clientData');
            if (storedData) {
                const parsed = JSON.parse(storedData);
                // Usar los datos de localStorage, especialmente nombre y tipoUsuario
                clientData = { ...clientData, ...parsed };
            }
        } catch (e) {
            console.error('Error al cargar datos del cliente:', e);
        }

        this.state.clientData = clientData;
    },

    /**
     * Renderiza el sidebar
     */
    renderSidebar() {
        const sidebarContainer = document.getElementById('client-sidebar-container');
        if (!sidebarContainer) {
            console.error('No se encontr? el contenedor del sidebar: #client-sidebar-container');
            return;
        }
        

        // Los estilos se manejan con clases Tailwind en el aside (hidden lg:flex)

        sidebarContainer.innerHTML = `
            <aside class="w-72 hidden lg:flex flex-col border-r border-[#282d39] bg-[#111318] h-full" id="client-sidebar" role="navigation" aria-label="Navegaci?n principal">
                <div class="p-6 pb-2">
                    <!-- Logo Header -->
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
                        <div id="sidebar-user-avatar" 
                             class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10 ring-2 ring-primary/20" 
                             data-alt="Foto de perfil del usuario"
                             style='background-image: url("${this.state.clientData.fotoPerfil}");'
                             role="img"
                             aria-label="Foto de perfil">
                        </div>
                        <div class="flex flex-col overflow-hidden">
                            <h1 id="sidebar-user-name" class="text-white text-sm font-semibold truncate">${this.state.clientData.nombre}</h1>
                            <p id="sidebar-user-type" class="text-text-secondary text-xs truncate">${this.state.clientData.tipoUsuario}</p>
                        </div>
                    </div>
                    
                    <!-- Navigation -->
                    <nav class="flex flex-col gap-1.5">
                        ${this.renderNavigationLinks()}
                    </nav>
                </div>
                
                <!-- Logout Button Footer -->
                <div class="mt-auto p-6">
                    <button id="logout-btn" 
                            class="flex w-full items-center justify-center gap-2 rounded-lg h-10 px-4 bg-card-dark hover:bg-[#3b4254] text-text-secondary hover:text-white text-sm font-bold transition-colors border border-transparent hover:border-[#4b5563]"
                            aria-label="Cerrar sesi?n">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">logout</span>
                        <span>Cerrar Sesi?n</span>
                    </button>
                </div>
            </aside>
        `;

        // El sidebar se muestra autom?ticamente con las clases Tailwind (hidden lg:flex)

        // Actualizar datos del usuario si hay elementos espec?ficos en la p?gina
        this.updateUserInfo();
    },

    /**
     * Renderiza los enlaces de navegaci?n
     * @returns {string} HTML de los enlaces de navegaci?n
     */
    renderNavigationLinks() {
        const currentPage = this.state.currentPage;
        let linksHTML = '';
        
        Object.entries(this.navigationRoutes).forEach(([key, route]) => {
            const isActive = currentPage === key;
            const activeClasses = isActive 
                ? 'bg-primary text-white' 
                : 'text-text-secondary hover:text-white hover:bg-card-dark';
            
            linksHTML += `
                <a id="${route.id}" 
                   href="${route.path}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg ${activeClasses} transition-colors group"
                   ${isActive ? 'aria-current="page"' : ''}>
                    <span class="material-symbols-outlined text-[24px]">${route.icon}</span>
                    <p class="text-sm font-medium">${route.label}</p>
                </a>
            `;
        });
        
        return linksHTML;
    },

    /**
     * Renderiza el men? m?vil
     */
    renderMobileMenu() {
        const mobileMenuContainer = document.getElementById('client-mobile-menu-container');
        if (!mobileMenuContainer) return;
        
        // Verificar que renderNavigationLinks existe
        if (typeof this.renderNavigationLinks !== 'function') {
            console.error('renderNavigationLinks no está definida. No se puede renderizar el menú móvil.');
            return;
        }

        mobileMenuContainer.innerHTML = `
            <!-- Mobile Menu Overlay -->
            <div id="mobile-menu-overlay" 
                 class="fixed inset-0 bg-black/50 z-40 lg:hidden transition-opacity duration-300 opacity-0 pointer-events-none"
                 aria-hidden="true"
                 role="dialog"
                 aria-modal="true"
                 aria-label="Men? de navegaci?n m?vil">
            </div>
            
            <!-- Mobile Menu Sidebar -->
            <aside id="mobile-sidebar" 
                   class="fixed top-0 left-0 h-full w-72 bg-[#111318] border-r border-[#282d39] z-50 lg:hidden transform -translate-x-full transition-transform duration-300 flex flex-col"
                   role="navigation"
                   aria-label="Navegaci?n m?vil">
                <div class="p-6 pb-2">
                    <!-- Mobile Header -->
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-3">
                            <div class="flex size-8 items-center justify-center rounded-lg bg-primary/20 text-primary">
                                <span class="material-symbols-outlined text-2xl" aria-hidden="true">confirmation_number</span>
                            </div>
                            <h2 class="text-white text-lg font-bold leading-tight tracking-tight">Sorteos Web</h2>
                        </div>
                        <button id="close-mobile-menu" 
                                class="text-white hover:text-text-secondary transition-colors"
                                aria-label="Cerrar men?">
                            <span class="material-symbols-outlined text-2xl">close</span>
                        </button>
                    </div>
                    
                    <!-- User Profile Mobile -->
                    <div class="flex items-center gap-3 p-3 rounded-lg bg-card-dark mb-6 border border-[#282d39]">
                        <div id="mobile-user-avatar" 
                             class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10 ring-2 ring-primary/20"
                             style='background-image: url("${this.state.clientData.fotoPerfil}");'
                             role="img"
                             aria-label="Foto de perfil">
                        </div>
                        <div class="flex flex-col overflow-hidden">
                            <h1 id="mobile-user-name" class="text-white text-sm font-semibold truncate">${this.state.clientData.nombre}</h1>
                            <p id="mobile-user-type" class="text-text-secondary text-xs truncate">${this.state.clientData.tipoUsuario}</p>
                        </div>
                    </div>
                    
                    <!-- Mobile Navigation -->
                    <nav class="flex flex-col gap-1.5" aria-label="Men? de navegaci?n m?vil">
                        ${this.renderNavigationLinks()}
                    </nav>
                </div>
                
                <!-- Mobile Logout -->
                <div class="mt-auto p-6">
                    <button id="mobile-logout-btn" 
                            class="flex w-full items-center justify-center gap-2 rounded-lg h-10 px-4 bg-card-dark hover:bg-[#3b4254] text-text-secondary hover:text-white text-sm font-bold transition-colors border border-transparent hover:border-[#4b5563]"
                            aria-label="Cerrar sesi?n">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">logout</span>
                        <span>Cerrar Sesi?n</span>
                    </button>
                </div>
            </aside>
        `;
    },

    /**
     * Adjunta event listeners
     */
    attachEventListeners() {
        // Toggle m?vil
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', () => this.toggleMobileMenu());
        }

        // Cerrar men? m?vil
        const closeMobileMenu = document.getElementById('close-mobile-menu');
        if (closeMobileMenu) {
            closeMobileMenu.addEventListener('click', () => this.closeMobileMenu());
        }

        // Overlay para cerrar men? m?vil
        const overlay = document.getElementById('mobile-menu-overlay');
        if (overlay) {
            overlay.addEventListener('click', () => this.closeMobileMenu());
        }

        // Botones de logout - usar delegaci?n de eventos para asegurar que funcione
        // incluso si los botones se agregan din?micamente
        document.addEventListener('click', (e) => {
            const logoutBtn = e.target.closest('#logout-btn, #mobile-logout-btn');
            if (logoutBtn) {
                e.preventDefault();
                e.stopPropagation();
                this.handleLogout();
            }
        });
        
        // Tambi?n agregar listeners directos como respaldo
        const logoutBtn = document.getElementById('logout-btn');
        const mobileLogoutBtn = document.getElementById('mobile-logout-btn');
        
        [logoutBtn, mobileLogoutBtn].forEach(btn => {
            if (btn) {
                // Remover listeners anteriores si existen para evitar duplicados
                btn.removeEventListener('click', this.handleLogout);
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.handleLogout();
                });
            }
        });

        // Cerrar men? m?vil al hacer clic en un enlace (pero permitir la navegaci?n)
        document.addEventListener('click', (e) => {
            const mobileLink = e.target.closest('#mobile-sidebar a');
            if (mobileLink) {
                // Permitir que el enlace funcione normalmente
                // Solo cerrar el men? despu?s de un peque?o delay para mejorar UX
                setTimeout(() => {
                    this.closeMobileMenu();
                }, 100);
            }
        });

        // Cerrar men? m?vil con tecla Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.state.mobileMenuOpen) {
                this.closeMobileMenu();
            }
        });
    },

    /**
     * Actualiza la navegaci?n activa
     */
    updateActiveNavigation() {
        Object.entries(this.navigationRoutes).forEach(([key, route]) => {
            const link = document.getElementById(route.id);
            if (!link) return;

            const isActive = this.state.currentPage === key;

            if (isActive) {
                link.classList.remove('text-text-secondary', 'hover:text-white', 'hover:bg-card-dark');
                link.classList.add('bg-primary', 'text-white');
                link.setAttribute('aria-current', 'page');
            } else {
                link.classList.remove('bg-primary', 'text-white');
                link.classList.add('text-text-secondary', 'hover:text-white', 'hover:bg-card-dark');
                link.setAttribute('aria-current', 'false');
            }
        });
    },

    /**
     * Abre el men? m?vil
     */
    openMobileMenu() {
        this.state.mobileMenuOpen = true;
        const sidebar = document.getElementById('mobile-sidebar');
        const overlay = document.getElementById('mobile-menu-overlay');
        
        if (sidebar) {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
        }
        
        if (overlay) {
            overlay.classList.remove('opacity-0', 'pointer-events-none');
            overlay.classList.add('opacity-100');
        }

        // Prevenir scroll del body
        document.body.style.overflow = 'hidden';
    },

    /**
     * Cierra el men? m?vil
     */
    closeMobileMenu() {
        this.state.mobileMenuOpen = false;
        const sidebar = document.getElementById('mobile-sidebar');
        const overlay = document.getElementById('mobile-menu-overlay');
        
        if (sidebar) {
            sidebar.classList.remove('translate-x-0');
            sidebar.classList.add('-translate-x-full');
        }
        
        if (overlay) {
            overlay.classList.remove('opacity-100');
            overlay.classList.add('opacity-0', 'pointer-events-none');
        }

        // Restaurar scroll del body
        document.body.style.overflow = '';
    },

    /**
     * Toggle del men? m?vil
     */
    toggleMobileMenu() {
        if (this.state.mobileMenuOpen) {
            this.closeMobileMenu();
        } else {
            this.openMobileMenu();
        }
    },

    /**
     * Maneja el cierre de sesi?n
     */
    handleLogout() {
        // Usar customConfirm para mantener consistencia con el resto de la aplicaci?n
        if (typeof customConfirm === 'function') {
            customConfirm('?Est?s seguro de que deseas cerrar sesi?n?', 'Cerrar Sesi?n', 'warning').then(confirmed => {
                if (confirmed) {
                    // Limpiar datos de sesi?n del cliente (localStorage/sessionStorage)
                    localStorage.removeItem('clientData');
                    sessionStorage.removeItem('clientData');
                    
                    // Redirigir al logout.php que destruye la sesi?n del servidor
                    window.location.href = 'logout.php';
                }
            });
        } else {
            // Si customConfirm no est? disponible, esperar a que se cargue
            // o usar confirm nativo como ?ltimo recurso
            setTimeout(() => {
                if (typeof customConfirm === 'function') {
                    this.handleLogout();
                } else {
                    if (confirm('?Est?s seguro de que deseas cerrar sesi?n?')) {
                        localStorage.removeItem('clientData');
                        sessionStorage.removeItem('clientData');
                        window.location.href = 'logout.php';
                    }
                }
            }, 100);
        }
    },

    /**
     * Actualiza la informaci?n del usuario en el sidebar
     */
    updateUserInfo() {
        const updateElement = (id, value, attribute = 'textContent') => {
            const element = document.getElementById(id);
            if (element && value) {
                if (attribute === 'textContent') {
                    element.textContent = value;
                } else {
                    element.setAttribute(attribute, value);
                }
            }
        };

        if (this.state.clientData) {
            updateElement('sidebar-user-name', this.state.clientData.nombre);
            updateElement('mobile-user-name', this.state.clientData.nombre);
            updateElement('sidebar-user-type', this.state.clientData.tipoUsuario);
            updateElement('mobile-user-type', this.state.clientData.tipoUsuario);
            
            if (this.state.clientData.fotoPerfil) {
                updateElement('sidebar-user-avatar', this.state.clientData.fotoPerfil, 'style');
                updateElement('mobile-user-avatar', this.state.clientData.fotoPerfil, 'style');
                const avatarStyle = `background-image: url("${this.state.clientData.fotoPerfil}");`;
                const sidebarAvatar = document.getElementById('sidebar-user-avatar');
                const mobileAvatar = document.getElementById('mobile-user-avatar');
                if (sidebarAvatar) sidebarAvatar.setAttribute('style', avatarStyle);
                if (mobileAvatar) mobileAvatar.setAttribute('style', avatarStyle);
            }
        }
    },

    /**
     * Actualiza los datos del cliente
     */
    updateClientData(data) {
        this.state.clientData = { ...this.state.clientData, ...data };
        this.updateUserInfo();
        
        // Guardar en localStorage
        try {
            localStorage.setItem('clientData', JSON.stringify(this.state.clientData));
        } catch (e) {
            console.error('Error al guardar datos del cliente:', e);
        }
    }
};

// Exportar para uso global
window.ClientLayout = ClientLayout;

// Inicializar logout globalmente para que funcione incluso si ClientLayout.init() no se llama
(function() {
    'use strict';
    
    // Función global de logout que puede ser llamada desde cualquier lugar
    window.handleGlobalLogout = function() {
        const executeLogout = () => {
            localStorage.removeItem('clientData');
            sessionStorage.removeItem('clientData');
            window.location.href = 'logout.php';
        };
        
        if (window.ClientLayout && typeof window.ClientLayout.handleLogout === 'function') {
            window.ClientLayout.handleLogout();
        } else {
            // Fallback si ClientLayout no está disponible
            if (typeof customConfirm === 'function') {
                customConfirm('¿Estás seguro de que deseas cerrar sesión?', 'Cerrar Sesión', 'warning').then(confirmed => {
                    if (confirmed) {
                        executeLogout();
                    }
                }).catch(() => {
                    if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                        executeLogout();
                    }
                });
            } else {
                setTimeout(() => {
                    if (typeof customConfirm === 'function') {
                        window.handleGlobalLogout();
                    } else {
                        if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                            executeLogout();
                        }
                    }
                }, 200);
            }
        }
    };
    
    // Agregar listener global para botones de logout (funciona incluso antes de que ClientLayout se inicialice)
    // Usar capture phase para capturar antes que otros listeners
    document.addEventListener('click', function(e) {
        const logoutBtn = e.target.closest('#logout-btn, #mobile-logout-btn');
        if (logoutBtn) {
            e.preventDefault();
            e.stopPropagation();
            window.handleGlobalLogout();
        }
    }, true);
    
    // También intentar adjuntar listeners directos cuando el DOM esté listo
    function attachGlobalLogoutListeners() {
        const logoutBtn = document.getElementById('logout-btn');
        const mobileLogoutBtn = document.getElementById('mobile-logout-btn');
        
        [logoutBtn, mobileLogoutBtn].forEach(btn => {
            if (btn && !btn.hasAttribute('data-global-logout-attached')) {
                btn.setAttribute('data-global-logout-attached', 'true');
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    window.handleGlobalLogout();
                });
            }
        });
    }
    
    // Intentar adjuntar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachGlobalLogoutListeners);
    } else {
        attachGlobalLogoutListeners();
        // También intentar después de un pequeño delay por si los botones se cargan después
        setTimeout(attachGlobalLogoutListeners, 500);
    }
})();

