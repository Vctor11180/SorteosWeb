/**
 * Cliente Layout Module
 * Layout lateral reutilizable para todas las páginas del cliente
 * Incluye sidebar con navegación, perfil de usuario y menú móvil
 */

const ClientLayout = {
    // Configuración de rutas de navegación
    navigationRoutes: {
        dashboard: {
            path: 'DashboardCliente.html',
            icon: 'dashboard',
            label: 'Dashboard',
            id: 'nav-dashboard'
        },
        sorteos: {
            path: 'ListadoSorteosActivos.html',
            icon: 'local_activity',
            label: 'Sorteos',
            id: 'nav-sorteos'
        },
        boletos: {
            path: 'MisBoletosCliente.html',
            icon: 'confirmation_number',
            label: 'Mis Boletos',
            id: 'nav-boletos'
        },
        ganadores: {
            path: 'MisGanancias.html',
            icon: 'emoji_events',
            label: 'Ganadores',
            id: 'nav-ganadores'
        },
        perfil: {
            path: 'AjustesPefilCliente.html',
            icon: 'person',
            label: 'Perfil',
            id: 'nav-perfil'
        },
        soporte: {
            path: 'ContactoSoporteCliente.html',
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
     * Inicializa el layout en la página
     * @param {string} currentPageId - ID de la página actual para marcar como activa
     */
    init(currentPageId = null) {
        this.state.currentPage = currentPageId || this.detectCurrentPage();
        this.loadClientData();
        this.renderMobileMenu();
        this.attachEventListeners();
        this.updateActiveNavigation();
        this.updateUserInfo();
    },

    /**
     * Detecta la página actual basándose en el nombre del archivo
     */
    detectCurrentPage() {
        const currentPath = window.location.pathname.split('/').pop() || '';
        const currentHref = window.location.href;
        
        const pageMap = {
            'DashboardCliente.html': 'dashboard',
            'ListadoSorteosActivos.html': 'sorteos',
            'MisBoletosCliente.html': 'boletos',
            'MisGanancias.html': 'ganadores',
            'AjustesPefilCliente.html': 'perfil',
            'ContactoSoporteCliente.html': 'soporte',
            'SorteoClienteDetalles.html': 'sorteos',
            'SeleccionBoletos.html': 'sorteos',
            'FinalizarPagoBoletos.html': 'boletos',
            'FAQCliente.html': 'soporte',
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
        let clientData = {
            nombre: 'Juan Pérez',
            tipoUsuario: 'Usuario Premium',
            fotoPerfil: 'https://lh3.googleusercontent.com/aida-public/AB6AXuAscTJ1Xcq7edw4JqzzGbgOvjdyQ9_nDg7kkxtlCQw51-EJsv1RJyDd9OAZC89eniVl2ujzIik6wgxd5FTvho_ak6ccsWrWelinVwXj6yQUdpPUXYUTJN0pSvhRh-smWf81cMQz40x4U3setrSFDsyX4KkfxOsHc6PnTND68lGw6JkA9B0ag_4fNu5s0Z9OMbq83llAZUv3xuo3s6VI1no110ozE88mRALnX-rhgavHoJxmYpvBcUxV7BtrJr_9Q0BlgvZQL2BXCFg',
            saldo: 1250.00
        };

        try {
            const storedData = localStorage.getItem('clientData');
            if (storedData) {
                const parsed = JSON.parse(storedData);
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
            console.error('No se encontró el contenedor del sidebar: #client-sidebar-container');
            return;
        }
        

        // Los estilos se manejan con clases Tailwind en el aside (hidden lg:flex)

        sidebarContainer.innerHTML = `
            <aside class="w-72 hidden lg:flex flex-col border-r border-[#282d39] bg-[#111318] h-full" id="client-sidebar" role="navigation" aria-label="Navegación principal">
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
                            aria-label="Cerrar sesión">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">logout</span>
                        <span>Cerrar Sesión</span>
                    </button>
                </div>
            </aside>
        `;

        // El sidebar se muestra automáticamente con las clases Tailwind (hidden lg:flex)

        // Actualizar datos del usuario si hay elementos específicos en la página
        this.updateUserInfo();
    },

    /**
     * Los enlaces de navegación ahora están directamente en el HTML
     */

    /**
     * Renderiza el menú móvil
     */
    renderMobileMenu() {
        const mobileMenuContainer = document.getElementById('client-mobile-menu-container');
        if (!mobileMenuContainer) return;

        mobileMenuContainer.innerHTML = `
            <!-- Mobile Menu Overlay -->
            <div id="mobile-menu-overlay" 
                 class="fixed inset-0 bg-black/50 z-40 lg:hidden transition-opacity duration-300 opacity-0 pointer-events-none"
                 aria-hidden="true"
                 role="dialog"
                 aria-modal="true"
                 aria-label="Menú de navegación móvil">
            </div>
            
            <!-- Mobile Menu Sidebar -->
            <aside id="mobile-sidebar" 
                   class="fixed top-0 left-0 h-full w-72 bg-[#111318] border-r border-[#282d39] z-50 lg:hidden transform -translate-x-full transition-transform duration-300 flex flex-col"
                   role="navigation"
                   aria-label="Navegación móvil">
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
                                aria-label="Cerrar menú">
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
                    <nav class="flex flex-col gap-1.5" aria-label="Menú de navegación móvil">
                        ${this.renderNavigationLinks()}
                    </nav>
                </div>
                
                <!-- Mobile Logout -->
                <div class="mt-auto p-6">
                    <button id="mobile-logout-btn" 
                            class="flex w-full items-center justify-center gap-2 rounded-lg h-10 px-4 bg-card-dark hover:bg-[#3b4254] text-text-secondary hover:text-white text-sm font-bold transition-colors border border-transparent hover:border-[#4b5563]"
                            aria-label="Cerrar sesión">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">logout</span>
                        <span>Cerrar Sesión</span>
                    </button>
                </div>
            </aside>
        `;
    },

    /**
     * Adjunta event listeners
     */
    attachEventListeners() {
        // Toggle móvil
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', () => this.toggleMobileMenu());
        }

        // Cerrar menú móvil
        const closeMobileMenu = document.getElementById('close-mobile-menu');
        if (closeMobileMenu) {
            closeMobileMenu.addEventListener('click', () => this.closeMobileMenu());
        }

        // Overlay para cerrar menú móvil
        const overlay = document.getElementById('mobile-menu-overlay');
        if (overlay) {
            overlay.addEventListener('click', () => this.closeMobileMenu());
        }

        // Botones de logout
        const logoutBtn = document.getElementById('logout-btn');
        const mobileLogoutBtn = document.getElementById('mobile-logout-btn');
        
        [logoutBtn, mobileLogoutBtn].forEach(btn => {
            if (btn) {
                btn.addEventListener('click', () => this.handleLogout());
            }
        });

        // Cerrar menú móvil al hacer clic en un enlace (pero permitir la navegación)
        document.addEventListener('click', (e) => {
            const mobileLink = e.target.closest('#mobile-sidebar a');
            if (mobileLink) {
                // Permitir que el enlace funcione normalmente
                // Solo cerrar el menú después de un pequeño delay para mejorar UX
                setTimeout(() => {
                    this.closeMobileMenu();
                }, 100);
            }
        });

        // Cerrar menú móvil con tecla Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.state.mobileMenuOpen) {
                this.closeMobileMenu();
            }
        });
    },

    /**
     * Actualiza la navegación activa
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
     * Abre el menú móvil
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
     * Cierra el menú móvil
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
     * Toggle del menú móvil
     */
    toggleMobileMenu() {
        if (this.state.mobileMenuOpen) {
            this.closeMobileMenu();
        } else {
            this.openMobileMenu();
        }
    },

    /**
     * Maneja el cierre de sesión
     */
    handleLogout() {
        if (typeof customConfirm === 'function') {
            customConfirm('¿Estás seguro de que deseas cerrar sesión?', 'Cerrar Sesión', 'warning').then(confirmed => {
                if (confirmed) {
                    // Limpiar datos de sesión
                    localStorage.removeItem('clientData');
                    sessionStorage.removeItem('clientData');
                    window.location.href = '../index.html';
                }
            });
        } else {
            // Fallback si customConfirm no está disponible
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                // Limpiar datos de sesión
                localStorage.removeItem('clientData');
                sessionStorage.removeItem('clientData');
            
            // Redirigir a la página de inicio de sesión
            window.location.href = '../InicioSesion.php';
        }
    },

    /**
     * Actualiza la información del usuario en el sidebar
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


