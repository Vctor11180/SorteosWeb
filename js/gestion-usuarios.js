(function() {
    // Esperar a que el DOM esté completamente cargado
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciarGestionUsuarios);
    } else {
        iniciarGestionUsuarios();
    }

    function iniciarGestionUsuarios() {
        // Referencias a elementos
        const searchInput = document.getElementById('searchInput');
        const estadoFilter = document.getElementById('estadoFilter');
        const ordenFilter = document.getElementById('ordenFilter');
        const tableBody = document.querySelector('table tbody');
        const selectAllCheckbox = document.querySelector('thead input[type="checkbox"]');

        // Función para obtener todos los checkboxes de filas
        function getCheckboxes() {
            return document.querySelectorAll('tbody input[type="checkbox"]');
        }

        // Función para obtener el nombre del usuario de una fila
        function getNombreFromRow(row) {
            const nombreCell = row.cells[1]; // Columna de usuario
            if (nombreCell) {
                const nombreSpan = nombreCell.querySelector('span.text-white');
                if (nombreSpan) {
                    return nombreSpan.textContent.trim();
                }
            }
            return '';
        }

        // Función para obtener el email del usuario de una fila
        function getEmailFromRow(row) {
            const emailCell = row.cells[2]; // Columna de email
            if (emailCell) {
                const spans = emailCell.querySelectorAll('span');
                for (let span of spans) {
                    const texto = span.textContent.trim();
                    if (texto.includes('@')) {
                        return texto;
                    }
                }
            }
            return '';
        }

        // Función para obtener el ID del usuario de una fila
        function getIdFromRow(row) {
            const nombreCell = row.cells[1]; // Columna de usuario
            if (nombreCell) {
                // Buscar todos los spans y encontrar el que contiene "ID:"
                const spans = nombreCell.querySelectorAll('span');
                for (let span of spans) {
                    if (span.textContent.includes('ID:')) {
                        return span.textContent.trim();
                    }
                }
                // También intentar obtener el data-user-id del row
                const userId = row.getAttribute('data-user-id');
                if (userId) {
                    return `ID: #${userId}`;
                }
            }
            return '';
        }

        // Función para obtener el estado actual de una fila
        function getEstadoFromRow(row) {
            const estadoCell = row.cells[4]; // Columna de estado
            if (estadoCell) {
                const estadoText = estadoCell.textContent.trim().toLowerCase();
                if (estadoText.includes('activo')) return 'active';
                if (estadoText.includes('inactivo')) return 'inactive';
                if (estadoText.includes('pendiente')) return 'pending';
            }
            return 'active';
        }

        // Función para cambiar el estado de una fila
        function cambiarEstadoFila(row, nuevoEstado) {
            const estadoCell = row.cells[4]; // Columna de estado
            const accionesCell = row.cells[5]; // Columna de acciones
            
            if (!estadoCell) return;

            let badgeHtml = '';

            if (nuevoEstado === 'active') {
                badgeHtml = `
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-green-500/10 px-2.5 py-1 text-xs font-medium text-green-400 border border-green-500/20">
                        <span class="h-1.5 w-1.5 rounded-full bg-green-400"></span>
                        Activo
                    </span>
                `;
                // Actualizar acciones - mostrar editar, zona peligro y desactivar
                accionesCell.innerHTML = `
                    <div class="flex items-center justify-end gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity" onclick="event.stopPropagation()">
                        <button class="btn-editar p-2 text-[#9da6b9] hover:text-white hover:bg-surface-dark rounded-lg transition-colors" title="Editar">
                            <span class="material-symbols-outlined !text-[20px]">edit</span>
                        </button>
                        <button class="btn-zona-peligro p-2 text-[#9da6b9] hover:text-orange-400 hover:bg-orange-400/10 rounded-lg transition-colors" title="Zona de Peligro">
                            <span class="material-symbols-outlined !text-[20px]">warning</span>
                        </button>
                        <button class="btn-desactivar p-2 text-[#9da6b9] hover:text-red-400 hover:bg-red-400/10 rounded-lg transition-colors" title="Desactivar cuenta">
                            <span class="material-symbols-outlined !text-[20px]">block</span>
                        </button>
                    </div>
                `;
            } else if (nuevoEstado === 'inactive') {
                badgeHtml = `
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-500/10 px-2.5 py-1 text-xs font-medium text-gray-400 border border-gray-500/20">
                        <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                        Inactivo
                    </span>
                `;
                // Actualizar acciones - mostrar editar, zona peligro y activar
                accionesCell.innerHTML = `
                    <div class="flex items-center justify-end gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity" onclick="event.stopPropagation()">
                        <button class="btn-editar p-2 text-[#9da6b9] hover:text-white hover:bg-surface-dark rounded-lg transition-colors" title="Editar">
                            <span class="material-symbols-outlined !text-[20px]">edit</span>
                        </button>
                        <button class="btn-zona-peligro p-2 text-[#9da6b9] hover:text-orange-400 hover:bg-orange-400/10 rounded-lg transition-colors" title="Zona de Peligro">
                            <span class="material-symbols-outlined !text-[20px]">warning</span>
                        </button>
                        <button class="btn-activar p-2 text-[#9da6b9] hover:text-green-400 hover:bg-green-400/10 rounded-lg transition-colors" title="Activar cuenta">
                            <span class="material-symbols-outlined !text-[20px]">check_circle</span>
                        </button>
                    </div>
                `;
            } else {
                // Pendiente
                badgeHtml = `
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/10 px-2.5 py-1 text-xs font-medium text-amber-400 border border-amber-500/20">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                        Pendiente
                    </span>
                `;
                // Actualizar acciones - mostrar editar, zona peligro y aprobar
                accionesCell.innerHTML = `
                    <div class="flex items-center justify-end gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity" onclick="event.stopPropagation()">
                        <button class="btn-editar p-2 text-[#9da6b9] hover:text-white hover:bg-surface-dark rounded-lg transition-colors" title="Editar">
                            <span class="material-symbols-outlined !text-[20px]">edit</span>
                        </button>
                        <button class="btn-zona-peligro p-2 text-[#9da6b9] hover:text-orange-400 hover:bg-orange-400/10 rounded-lg transition-colors" title="Zona de Peligro">
                            <span class="material-symbols-outlined !text-[20px]">warning</span>
                        </button>
                        <button class="btn-aprobar p-2 text-[#9da6b9] hover:text-green-400 hover:bg-green-400/10 rounded-lg transition-colors" title="Aprobar cuenta">
                            <span class="material-symbols-outlined !text-[20px]">check</span>
                        </button>
                    </div>
                `;
            }

            estadoCell.innerHTML = badgeHtml;
            
            // Verificar si hay filtros activos
            const searchValue = (searchInput?.value || '').trim();
            const estadoValue = estadoFilter?.value || '';
            
            // Solo aplicar filtros si hay alguno activo
            if (searchValue || estadoValue) {
                aplicarFiltros();
            } else {
                // No hay filtros activos, mantener la fila visible
                row.style.display = '';
            }
        }

        // Función para aplicar filtros
        function aplicarFiltros() {
            const searchValue = (searchInput?.value || '').toLowerCase().trim();
            const estadoValue = estadoFilter?.value || '';
            const rows = tableBody?.querySelectorAll('tr') || [];
            let visibleCount = 0;

            rows.forEach(row => {
                const nombreUsuario = getNombreFromRow(row).toLowerCase();
                const emailUsuario = (getEmailFromRow(row) || '').toLowerCase();
                const idUsuario = (getIdFromRow(row) || '').toLowerCase();
                const textoFila = row.innerText.toLowerCase();
                
                const estadoFila = getEstadoFromRow(row);

                // Búsqueda por nombre, email o ID
                const matchesSearch = !searchValue || 
                                     nombreUsuario.includes(searchValue) ||
                                     emailUsuario.includes(searchValue) ||
                                     idUsuario.includes(searchValue) ||
                                     textoFila.includes(searchValue);
                
                const matchesEstado = !estadoValue || estadoFila === estadoValue;

                if (matchesSearch && matchesEstado) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Función para ordenar tabla
        function ordenarTabla(orden) {
            if (!orden || orden === '') {
                return;
            }

            // Obtener todas las filas (incluidas las ocultas)
            const todasLasFilas = Array.from(tableBody?.querySelectorAll('tr') || []);
            
            if (todasLasFilas.length === 0) return;

            // Separar filas visibles e invisibles
            const filasVisibles = todasLasFilas.filter(row => row.style.display !== 'none');
            const filasInvisibles = todasLasFilas.filter(row => row.style.display === 'none');

            if (filasVisibles.length === 0) return;

            filasVisibles.sort((a, b) => {
                if (orden === 'oldest') {
                    // Más antiguos primero (por fecha de registro - columna 3)
                    const fechaA = a.cells[3]?.textContent || '';
                    const fechaB = b.cells[3]?.textContent || '';
                    return fechaA.localeCompare(fechaB);
                } else if (orden === 'name_asc') {
                    // Nombre A-Z
                    const nombreA = getNombreFromRow(a).toLowerCase();
                    const nombreB = getNombreFromRow(b).toLowerCase();
                    return nombreA.localeCompare(nombreB);
                } else if (orden === 'name_desc') {
                    // Nombre Z-A
                    const nombreA = getNombreFromRow(a).toLowerCase();
                    const nombreB = getNombreFromRow(b).toLowerCase();
                    return nombreB.localeCompare(nombreA);
                } else {
                    // Más recientes primero (por defecto)
                    const fechaA = a.cells[3]?.textContent || '';
                    const fechaB = b.cells[3]?.textContent || '';
                    return fechaB.localeCompare(fechaA);
                }
            });

            // Limpiar el tbody
            tableBody.innerHTML = '';
            
            // Reinsertar filas ordenadas
            filasVisibles.forEach(row => tableBody.appendChild(row));
            filasInvisibles.forEach(row => tableBody.appendChild(row));
        }

        // Event listeners para filtros
        if (searchInput) {
            searchInput.addEventListener('input', aplicarFiltros);
        }

        if (estadoFilter) {
            estadoFilter.addEventListener('change', aplicarFiltros);
        }

        if (ordenFilter) {
            ordenFilter.addEventListener('change', (e) => {
                const orden = e.target.value || '';
                if (orden) {
                    ordenarTabla(orden);
                }
            });
        }

        // Event listeners para botones de acción individuales (usando delegación de eventos)
        if (tableBody) {
            tableBody.addEventListener('click', (e) => {
                const row = e.target.closest('tr');
                if (!row) return;

                const target = e.target;
                const button = target.closest('button');

                if (!button) return;

                const title = button.title || '';
                const estadoActual = getEstadoFromRow(row);
                const nombreUsuario = getNombreFromRow(row);

                // Activar cuenta
                if (title === 'Activar cuenta' || button.classList.contains('btn-activar')) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (confirm(`¿Estás seguro de activar la cuenta de ${nombreUsuario}?`)) {
                        cambiarEstadoFila(row, 'active');
                    }
                    return;
                }

                // Desactivar cuenta - Redirige a Zona de Peligro
                if (title === 'Desactivar cuenta' || button.classList.contains('btn-desactivar')) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (confirm(`¿Estás seguro de desactivar la cuenta de ${nombreUsuario}?`)) {
                        cambiarEstadoFila(row, 'inactive');
                        // Obtener el userId de la fila (data-user-id)
                        const userId = row.getAttribute('data-user-id');
                        // Redirigir a la página de Zona de Peligro con el userId
                        if (userId) {
                            window.location.href = `ZonaPeligroUsuario.html?userId=${userId}`;
                        } else {
                            window.location.href = 'ZonaPeligroUsuario.html';
                        }
                    }
                    return;
                }

                // Aprobar cuenta (de pendiente a activo)
                if (title === 'Aprobar cuenta' || button.classList.contains('btn-aprobar')) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (confirm(`¿Estás seguro de aprobar la cuenta de ${nombreUsuario}?`)) {
                        cambiarEstadoFila(row, 'active');
                    }
                    return;
                }

                // Editar (solo mostrar mensaje por ahora)
                if (title === 'Editar' || button.classList.contains('btn-editar')) {
                    e.preventDefault();
                    e.stopPropagation();
                    alert(`Funcionalidad de edición para ${nombreUsuario} - Por implementar`);
                    return;
                }

                // Zona de Peligro - Redirigir a ZonaPeligroUsuario.html
                if (title === 'Zona de Peligro' || button.classList.contains('btn-zona-peligro')) {
                    e.preventDefault();
                    e.stopPropagation();
                    // Obtener el userId de la fila (data-user-id)
                    const userId = row.getAttribute('data-user-id');
                    // Redirigir a la página de Zona de Peligro con el userId
                    if (userId) {
                        window.location.href = `ZonaPeligroUsuario.html?userId=${userId}`;
                    } else {
                        window.location.href = 'ZonaPeligroUsuario.html';
                    }
                    return;
                }
            });
        }

        // Seleccionar todos
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', (e) => {
                const checkboxes = getCheckboxes();
                checkboxes.forEach(cb => {
                    cb.checked = e.target.checked;
                });
            });
        }

        // Botón para crear nuevo usuario
        const btnCrearUsuario = document.getElementById('btnCrearUsuario');
        if (btnCrearUsuario) {
            btnCrearUsuario.addEventListener('click', () => {
                alert('Funcionalidad de crear nuevo usuario - Por implementar');
                // Aquí se puede agregar un modal o redirigir a una página de creación
            });
        }

        // Botón para ir a Zona de Peligro (todos los seleccionados)
        const btnZonaPeligroTodos = document.getElementById('btnZonaPeligroTodos');
        if (btnZonaPeligroTodos) {
            btnZonaPeligroTodos.addEventListener('click', () => {
                const checkboxes = getCheckboxes();
                const selectedRows = Array.from(checkboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.closest('tr'))
                    .filter(row => row);
                
                if (selectedRows.length === 0) {
                    // Si no hay usuarios seleccionados, ir directamente a Zona de Peligro
                    window.location.href = 'ZonaPeligroUsuario.html';
                } else {
                    // Si hay usuarios seleccionados, confirmar y luego ir a Zona de Peligro
                    if (confirm(`¿Deseas enviar ${selectedRows.length} usuario(s) seleccionado(s) a la Zona de Peligro?`)) {
                        // Desmarcar checkboxes antes de redirigir
                        checkboxes.forEach(cb => cb.checked = false);
                        if (selectAllCheckbox) selectAllCheckbox.checked = false;
                        // Redirigir a Zona de Peligro
                        window.location.href = 'ZonaPeligroUsuario.html';
                    }
                }
            });
        }

        // Inicializar - no aplicar filtros por defecto
        // Todas las filas deben estar visibles por defecto
    }
})();

