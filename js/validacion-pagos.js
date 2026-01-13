(function() {
    // Esperar a que el DOM esté completamente cargado
    document.addEventListener('DOMContentLoaded', function() {
        iniciarValidacionPagos();
    });

    // Si el DOM ya está cargado, ejecutar inmediatamente
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciarValidacionPagos);
    } else {
        iniciarValidacionPagos();
    }

    function iniciarValidacionPagos() {
        // Referencias a elementos
        const searchInput = document.getElementById('searchInput');
        const estadoFilter = document.getElementById('estadoFilter');
        const metodoFilter = document.getElementById('metodoFilter');
        const ordenFilter = document.getElementById('ordenFilter');
        const tableBody = document.querySelector('table tbody');
        const btnAprobarTodos = document.getElementById('btnAprobarTodos');
        const btnRechazarTodos = document.getElementById('btnRechazarTodos');

        // Imágenes de comprobantes de ejemplo
        const comprobantes = {
            'juan.perez@email.com': 'https://via.placeholder.com/800x600/4f46e5/ffffff?text=Comprobante+Juan+P%C3%A9rez',
            'maria.garcia@email.com': 'https://via.placeholder.com/800x600/10b981/ffffff?text=Comprobante+Mar%C3%ADa+Garc%C3%ADa',
            'carlos.lopez@email.com': 'https://via.placeholder.com/800x600/f59e0b/ffffff?text=Comprobante+Carlos+L%C3%B3pez',
            'ana.martinez@email.com': 'https://via.placeholder.com/800x600/3b82f6/ffffff?text=Comprobante+Ana+Mart%C3%ADnez',
            'luis.rod@email.com': 'https://via.placeholder.com/800x600/ef4444/ffffff?text=Comprobante+Luis+Rodr%C3%ADguez'
        };

        // Función para obtener todos los checkboxes de filas
        function getCheckboxes() {
            return document.querySelectorAll('tbody input[type="checkbox"]');
        }

        // Función para obtener el email del usuario de una fila
        function getEmailFromRow(row) {
            // Buscar en todas las celdas de la fila
            const cells = row.querySelectorAll('td');
            for (let cell of cells) {
                const spans = cell.querySelectorAll('span');
                for (let span of spans) {
                    const texto = span.textContent.trim();
                    if (texto.includes('@')) {
                        return texto;
                    }
                }
            }
            return null;
        }

        // Función para obtener el nombre del usuario de una fila
        function getNombreFromRow(row) {
            const userCell = row.cells[1]; // Columna de usuario
            if (userCell) {
                const spans = userCell.querySelectorAll('span');
                for (let span of spans) {
                    const texto = span.textContent.trim();
                    // Si no tiene @, probablemente es el nombre
                    if (!texto.includes('@') && texto.length > 1 && !texto.includes('material-symbols')) {
                        return texto;
                    }
                }
            }
            return '';
        }

        // Función para obtener el estado actual de una fila
        function getEstadoFromRow(row) {
            const estadoCell = row.cells[7]; // Columna de estado
            if (estadoCell) {
                const estadoText = estadoCell.textContent.trim().toLowerCase();
                if (estadoText.includes('pendiente')) return 'pending';
                if (estadoText.includes('aprobado')) return 'approved';
                if (estadoText.includes('rechazado')) return 'rejected';
            }
            return 'pending';
        }

        // Función para obtener el método de pago de una fila
        function getMetodoFromRow(row) {
            const metodoCell = row.cells[4]; // Columna de método
            if (metodoCell) {
                const metodoText = metodoCell.textContent.trim().toLowerCase();
                if (metodoText.includes('transferencia')) return 'transfer';
                if (metodoText.includes('paypal')) return 'paypal';
                if (metodoText.includes('depósito')) return 'deposit';
                if (metodoText.includes('criptomoneda')) return 'crypto';
            }
            return '';
        }

        // Función para cambiar el estado de una fila
        function cambiarEstadoFila(row, nuevoEstado) {
            const estadoCell = row.cells[7]; // Columna de estado
            const accionesCell = row.cells[8]; // Columna de acciones
            
            if (!estadoCell) return;

            let badgeHtml = '';

            if (nuevoEstado === 'approved') {
                badgeHtml = `
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-green-500/10 px-2.5 py-1 text-xs font-medium text-green-400 border border-green-500/20">
                        <span class="h-1.5 w-1.5 rounded-full bg-green-400"></span>
                        Aprobado
                    </span>
                `;
                // Actualizar acciones - solo mostrar ver detalles
                accionesCell.innerHTML = `
                    <div class="flex items-center justify-center gap-1">
                        <button class="p-2 text-[#9da6b9] hover:text-white hover:bg-[#1e2433] rounded-lg transition-colors" title="Ver detalles">
                            <span class="material-symbols-outlined !text-xl">visibility</span>
                        </button>
                    </div>
                `;
                row.classList.remove('bg-yellow-500/5', 'bg-red-500/5');
                row.classList.add('bg-green-500/5');
            } else if (nuevoEstado === 'rejected') {
                badgeHtml = `
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-500/10 px-2.5 py-1 text-xs font-medium text-red-400 border border-red-500/20">
                        <span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>
                        Rechazado
                    </span>
                `;
                // Actualizar acciones - mostrar ver motivo y detalles
                accionesCell.innerHTML = `
                    <div class="flex items-center justify-center gap-1">
                        <button class="p-2 text-[#9da6b9] hover:text-white hover:bg-[#1e2433] rounded-lg transition-colors" title="Ver motivo">
                            <span class="material-symbols-outlined !text-xl">info</span>
                        </button>
                        <button class="p-2 text-[#9da6b9] hover:text-white hover:bg-[#1e2433] rounded-lg transition-colors" title="Ver detalles">
                            <span class="material-symbols-outlined !text-xl">visibility</span>
                        </button>
                    </div>
                `;
                row.classList.remove('bg-yellow-500/5', 'bg-green-500/5');
                row.classList.add('bg-red-500/5');
            } else {
                badgeHtml = `
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-yellow-500/10 px-2.5 py-1 text-xs font-medium text-yellow-400 border border-yellow-500/20">
                        <span class="h-1.5 w-1.5 rounded-full bg-yellow-400 animate-pulse"></span>
                        Pendiente
                    </span>
                `;
                row.classList.remove('bg-green-500/5', 'bg-red-500/5');
                // Actualizar acciones - mostrar aprobar, rechazar y ver detalles
                accionesCell.innerHTML = `
                    <div class="flex items-center justify-center gap-1">
                        <button class="btn-aprobar p-2 text-green-400 hover:bg-green-400/10 rounded-lg transition-colors" title="Aprobar pago">
                            <span class="material-symbols-outlined !text-xl">check_circle</span>
                        </button>
                        <button class="btn-rechazar p-2 text-red-400 hover:bg-red-400/10 rounded-lg transition-colors" title="Rechazar pago">
                            <span class="material-symbols-outlined !text-xl">cancel</span>
                        </button>
                        <button class="p-2 text-[#9da6b9] hover:text-white hover:bg-[#1e2433] rounded-lg transition-colors" title="Ver detalles">
                            <span class="material-symbols-outlined !text-xl">visibility</span>
                        </button>
                    </div>
                `;
            }

            estadoCell.innerHTML = badgeHtml;
            actualizarEstadisticas();
            
            // Verificar si hay filtros activos
            const searchValue = (searchInput?.value || '').trim();
            const estadoValue = estadoFilter?.value || '';
            const metodoValue = metodoFilter?.value || '';
            
            // Solo aplicar filtros si hay alguno activo
            if (searchValue || estadoValue || metodoValue) {
                // Hay filtros activos, aplicar los filtros
                aplicarFiltros();
            } else {
                // No hay filtros activos, mantener la fila visible
                row.style.display = '';
            }
        }

        // Función para mostrar modal con comprobante
        function mostrarComprobante(email) {
            const imagenUrl = comprobantes[email] || 'https://via.placeholder.com/800x600/6b7280/ffffff?text=Comprobante+no+disponible';
            
            // Crear modal
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 z-50 overflow-y-auto';
            modal.innerHTML = `
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity bg-gray-900/75 backdrop-blur-sm cursor-pointer" id="comprobante-backdrop"></div>
                    <div class="inline-block align-bottom bg-white dark:bg-[#1e2433] rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-gray-200 dark:border-border-dark" id="comprobante-content">
                        <div class="px-4 pt-5 pb-4 sm:p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg leading-6 font-medium text-slate-900 dark:text-white">Comprobante de Pago</h3>
                                <button id="btn-cerrar-comprobante" class="text-gray-400 hover:text-white">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>
                            <div class="flex justify-center">
                                <img src="${imagenUrl}" alt="Comprobante" class="max-w-full h-auto rounded-lg border border-gray-200 dark:border-border-dark">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            
            // Función para cerrar el modal
            const cerrarModal = (e) => {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                modal.remove();
                document.removeEventListener('keydown', closeModalHandler);
            };
            
            // Handler para cerrar con ESC
            const closeModalHandler = (e) => {
                if (e.key === 'Escape') {
                    cerrarModal();
                }
            };
            
            // Agregar event listeners
            const backdrop = modal.querySelector('#comprobante-backdrop');
            const btnCerrar = modal.querySelector('#btn-cerrar-comprobante');
            const modalContent = modal.querySelector('#comprobante-content');
            
            // Cerrar con botón X
            if (btnCerrar) {
                btnCerrar.addEventListener('click', cerrarModal);
            }
            
            // Cerrar con backdrop
            if (backdrop) {
                backdrop.addEventListener('click', (e) => {
                    if (e.target === backdrop) {
                        cerrarModal(e);
                    }
                });
            }
            
            // Cerrar con ESC
            document.addEventListener('keydown', closeModalHandler);
            
            // Prevenir que el clic dentro del modal cierre el modal
            if (modalContent) {
                modalContent.addEventListener('click', (e) => {
                    e.stopPropagation();
                });
            }
        }

        // Función para mostrar motivo de rechazo
        function mostrarMotivoRechazo(row) {
            const nombreUsuario = getNombreFromRow(row);
            const emailUsuario = getEmailFromRow(row);
            const monto = row.cells[3]?.textContent || '';
            const sorteo = row.cells[2]?.textContent || '';
            
            // Motivos de rechazo comunes (puedes personalizar estos mensajes)
            const motivosRechazo = [
                'El comprobante de pago no coincide con el monto registrado.',
                'El comprobante está ilegible o no se puede verificar la información.',
                'La fecha del comprobante no corresponde con la fecha de la transacción.',
                'El método de pago indicado no coincide con el comprobante enviado.',
                'El comprobante está incompleto o falta información esencial.',
                'No se puede verificar la autenticidad del comprobante de pago.',
                'El comprobante pertenece a otra transacción o usuario diferente.'
            ];
            
            // Seleccionar un motivo aleatorio o usar uno específico
            // Por ahora usaré un motivo genérico basado en el usuario
            const motivoIndex = nombreUsuario.length % motivosRechazo.length;
            const motivo = motivosRechazo[motivoIndex];
            
            // Crear modal
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 z-50 overflow-y-auto';
            modal.innerHTML = `
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity bg-gray-900/75 backdrop-blur-sm cursor-pointer" id="modal-backdrop"></div>
                    <div class="inline-block align-bottom bg-white dark:bg-[#1e2433] rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-border-dark" id="modal-content">
                        <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-500/10 sm:mx-0 sm:h-10 sm:w-10">
                                    <span class="material-symbols-outlined text-red-400 text-2xl">info</span>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-slate-900 dark:text-white mb-2">
                                        Motivo de Rechazo
                                    </h3>
                                    <div class="mt-4 space-y-3">
                                        <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-4">
                                            <p class="text-sm font-medium text-red-400 mb-2">Pago Rechazado</p>
                                            <div class="text-sm text-gray-300 space-y-1">
                                                <p><span class="font-medium">Usuario:</span> ${nombreUsuario}</p>
                                                <p><span class="font-medium">Email:</span> ${emailUsuario}</p>
                                                <p><span class="font-medium">Sorteo:</span> ${sorteo.split('\n')[0] || sorteo}</p>
                                                <p><span class="font-medium">Monto:</span> ${monto}</p>
                                            </div>
                                        </div>
                                        <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-lg p-4">
                                            <p class="text-sm font-semibold text-yellow-400 mb-2 flex items-center gap-2">
                                                <span class="material-symbols-outlined text-lg">warning</span>
                                                Motivo del Rechazo
                                            </p>
                                            <p class="text-sm text-gray-300 leading-relaxed">
                                                ${motivo}
                                            </p>
                                        </div>
                                        <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">
                                            <p class="text-xs text-blue-400">
                                                <span class="material-symbols-outlined text-sm align-middle">info</span>
                                                El usuario puede volver a intentar el pago con un comprobante válido.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-[#151a23] px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button id="btn-cerrar-modal" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm">
                                Entendido
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            
            // Función para cerrar el modal
            const cerrarModal = (e) => {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                modal.remove();
                document.removeEventListener('keydown', closeModalHandler);
            };
            
            // Handler para cerrar con ESC
            const closeModalHandler = (e) => {
                if (e.key === 'Escape') {
                    cerrarModal();
                }
            };
            
            // Agregar event listeners
            const backdrop = modal.querySelector('#modal-backdrop');
            const btnCerrar = modal.querySelector('#btn-cerrar-modal');
            const modalContent = modal.querySelector('#modal-content');
            
            // Cerrar con botón
            if (btnCerrar) {
                btnCerrar.addEventListener('click', cerrarModal);
            }
            
            // Cerrar con backdrop (evitar cerrar al hacer clic dentro del modal)
            if (backdrop) {
                backdrop.addEventListener('click', (e) => {
                    if (e.target === backdrop) {
                        cerrarModal(e);
                    }
                });
            }
            
            // Cerrar con ESC
            document.addEventListener('keydown', closeModalHandler);
            
            // Prevenir que el clic dentro del modal cierre el modal
            if (modalContent) {
                modalContent.addEventListener('click', (e) => {
                    e.stopPropagation();
                });
            }
        }

        // Función para aplicar filtros
        function aplicarFiltros() {
            const searchValue = (searchInput?.value || '').toLowerCase().trim();
            const estadoValue = estadoFilter?.value || '';
            const metodoValue = metodoFilter?.value || '';
            const rows = tableBody?.querySelectorAll('tr') || [];
            let visibleCount = 0;

            rows.forEach(row => {
                const nombreUsuario = getNombreFromRow(row).toLowerCase();
                const emailUsuario = (getEmailFromRow(row) || '').toLowerCase();
                const sorteoText = (row.cells[2]?.textContent || '').toLowerCase();
                const textoFila = row.innerText.toLowerCase();
                
                const estadoFila = getEstadoFromRow(row);
                const metodoFila = getMetodoFromRow(row);

                // Búsqueda por nombre de usuario, email o sorteo
                const matchesSearch = !searchValue || 
                                     nombreUsuario.includes(searchValue) ||
                                     emailUsuario.includes(searchValue) ||
                                     sorteoText.includes(searchValue) ||
                                     textoFila.includes(searchValue);
                
                const matchesEstado = !estadoValue || estadoFila === estadoValue;
                const matchesMetodo = !metodoValue || metodoFila === metodoValue;

                if (matchesSearch && matchesEstado && matchesMetodo) {
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
                // Si no hay orden seleccionado, no hacer nada
                return;
            }

            // Obtener todas las filas (incluidas las ocultas) para mantener el orden completo
            const todasLasFilas = Array.from(tableBody?.querySelectorAll('tr') || []);
            
            if (todasLasFilas.length === 0) return;

            // Separar filas visibles e invisibles para ordenar solo las visibles
            const filasVisibles = todasLasFilas.filter(row => row.style.display !== 'none');
            const filasInvisibles = todasLasFilas.filter(row => row.style.display === 'none');

            if (filasVisibles.length === 0) return;

            filasVisibles.sort((a, b) => {
                if (orden === 'oldest') {
                    // Ordenar por fecha (más antiguos primero)
                    const fechaA = a.cells[6]?.textContent || '';
                    const fechaB = b.cells[6]?.textContent || '';
                    return fechaA.localeCompare(fechaB);
                } else if (orden === 'amount_high') {
                    // Mayor monto primero
                    const montoA = parseFloat((a.cells[3]?.textContent || '').replace('$', '').replace(',', '').trim()) || 0;
                    const montoB = parseFloat((b.cells[3]?.textContent || '').replace('$', '').replace(',', '').trim()) || 0;
                    return montoB - montoA;
                } else if (orden === 'amount_low') {
                    // Menor monto primero
                    const montoA = parseFloat((a.cells[3]?.textContent || '').replace('$', '').replace(',', '').trim()) || 0;
                    const montoB = parseFloat((b.cells[3]?.textContent || '').replace('$', '').replace(',', '').trim()) || 0;
                    return montoA - montoB;
                } else {
                    // Más recientes primero (por defecto o "recientes")
                    const fechaA = a.cells[6]?.textContent || '';
                    const fechaB = b.cells[6]?.textContent || '';
                    // Invertir para más recientes primero
                    return fechaB.localeCompare(fechaA);
                }
            });

            // Limpiar el tbody
            tableBody.innerHTML = '';
            
            // Reinsertar filas ordenadas (primero las visibles ordenadas, luego las invisibles)
            filasVisibles.forEach(row => tableBody.appendChild(row));
            filasInvisibles.forEach(row => tableBody.appendChild(row));
        }

        // Función para actualizar estadísticas
        function actualizarEstadisticas() {
            const rows = Array.from(tableBody?.querySelectorAll('tr') || []);
            let pendientes = 0, aprobados = 0, rechazados = 0, montoPendiente = 0;

            rows.forEach(row => {
                const estado = getEstadoFromRow(row);
                const montoText = row.cells[3]?.textContent || '';
                const monto = parseFloat(montoText.replace('$', '').replace(',', '') || 0);
                
                if (estado === 'pending') {
                    pendientes++;
                    montoPendiente += monto;
                } else if (estado === 'approved') {
                    aprobados++;
                } else if (estado === 'rejected') {
                    rechazados++;
                }
            });

            // Actualizar cards de estadísticas
            const cards = document.querySelectorAll('.bg-\\[\\#1e2433\\]');
            cards.forEach(card => {
                const text = card.textContent;
                if (text.includes('Pagos Pendientes')) {
                    const numEl = card.querySelector('p.text-3xl');
                    if (numEl) numEl.textContent = pendientes;
                } else if (text.includes('Aprobados Hoy')) {
                    const numEl = card.querySelector('p.text-3xl');
                    if (numEl) numEl.textContent = aprobados;
                } else if (text.includes('Rechazados Hoy')) {
                    const numEl = card.querySelector('p.text-3xl');
                    if (numEl) numEl.textContent = rechazados;
                } else if (text.includes('Monto Pendiente')) {
                    const numEl = card.querySelector('p.text-3xl');
                    if (numEl) numEl.textContent = `$${montoPendiente.toLocaleString('es-MX', {minimumFractionDigits: 2})}`;
                }
            });
        }

        // Event listeners para filtros
        if (searchInput) {
            searchInput.addEventListener('input', aplicarFiltros);
        }

        if (estadoFilter) {
            estadoFilter.addEventListener('change', aplicarFiltros);
        }

        if (metodoFilter) {
            metodoFilter.addEventListener('change', aplicarFiltros);
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

                // Aprobar
                if (button && (button.classList.contains('btn-aprobar') || button.title === 'Aprobar pago')) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (confirm('¿Estás seguro de aprobar este pago?')) {
                        cambiarEstadoFila(row, 'approved');
                    }
                    return;
                }

                // Rechazar
                if (button && (button.classList.contains('btn-rechazar') || button.title === 'Rechazar pago')) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (confirm('¿Estás seguro de rechazar este pago?')) {
                        cambiarEstadoFila(row, 'rejected');
                    }
                    return;
                }

                // Ver comprobante
                if (button && (button.classList.contains('btn-ver-comprobante') || 
                              button.textContent.trim().includes('Ver comprobante') || 
                              button.textContent.trim().includes('Ver recibo'))) {
                    e.preventDefault();
                    e.stopPropagation();
                    const email = getEmailFromRow(row);
                    if (email) {
                        mostrarComprobante(email);
                    }
                    return;
                }

                // Ver motivo (solo en rechazados)
                if (button && (button.title === 'Ver motivo' || 
                              button.textContent.trim().includes('Ver motivo'))) {
                    e.preventDefault();
                    e.stopPropagation();
                    mostrarMotivoRechazo(row);
                    return;
                }
            });
        }

        // Event listeners para acciones masivas
        if (btnAprobarTodos) {
            btnAprobarTodos.addEventListener('click', () => {
                const checkboxes = getCheckboxes();
                const selectedRows = Array.from(checkboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.closest('tr'))
                    .filter(row => row); // Filtrar nulls
                
                if (selectedRows.length === 0) {
                    alert('Por favor selecciona al menos un pago para aprobar.');
                    return;
                }

                if (confirm(`¿Estás seguro de aprobar ${selectedRows.length} pago(s)?`)) {
                    selectedRows.forEach(row => {
                        if (row && getEstadoFromRow(row) === 'pending') {
                            cambiarEstadoFila(row, 'approved');
                        }
                    });
                    // Desmarcar todos los checkboxes
                    const checkboxes = getCheckboxes();
                    checkboxes.forEach(cb => cb.checked = false);
                    const selectAllCheckbox = document.querySelector('thead input[type="checkbox"]');
                    if (selectAllCheckbox) selectAllCheckbox.checked = false;
                }
            });
        }

        if (btnRechazarTodos) {
            btnRechazarTodos.addEventListener('click', () => {
                const checkboxes = getCheckboxes();
                const selectedRows = Array.from(checkboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.closest('tr'))
                    .filter(row => row); // Filtrar nulls
                
                if (selectedRows.length === 0) {
                    alert('Por favor selecciona al menos un pago para rechazar.');
                    return;
                }

                if (confirm(`¿Estás seguro de rechazar ${selectedRows.length} pago(s)?`)) {
                    selectedRows.forEach(row => {
                        if (row && getEstadoFromRow(row) === 'pending') {
                            cambiarEstadoFila(row, 'rejected');
                        }
                    });
                    // Desmarcar todos los checkboxes
                    const checkboxes = getCheckboxes();
                    checkboxes.forEach(cb => cb.checked = false);
                    const selectAllCheckbox = document.querySelector('thead input[type="checkbox"]');
                    if (selectAllCheckbox) selectAllCheckbox.checked = false;
                }
            });
        }

        // Seleccionar todos
        const selectAllCheckbox = document.querySelector('thead input[type="checkbox"]');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', (e) => {
                const checkboxes = getCheckboxes();
                checkboxes.forEach(cb => {
                    cb.checked = e.target.checked;
                });
            });
        }

        // Inicializar estadísticas al cargar
        actualizarEstadisticas();
        
        // NO aplicar filtros automáticamente al cargar
        // Todas las filas deben estar visibles por defecto
        // Solo aplicar filtros cuando el usuario los active manualmente
    }
})();
