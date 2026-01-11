(function() {
    // Esperar a que el DOM esté completamente cargado
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciarDetallesUsuario);
    } else {
        iniciarDetallesUsuario();
    }

    function iniciarDetallesUsuario() {
        // Si hay un userId en la URL, actualizar el elemento en la página (opcional, para consistencia)
        const urlParams = new URLSearchParams(window.location.search);
        const userIdFromUrl = urlParams.get('userId');
        if (userIdFromUrl) {
            const userIdDisplay = document.getElementById('user-id-display');
            if (userIdDisplay) {
                userIdDisplay.textContent = `#${userIdFromUrl}`;
            }
        }

        // Referencias a elementos usando IDs
        const btnEditar = document.getElementById('btnEditarUsuario');
        const btnResetPassword = Array.from(document.querySelectorAll('button')).find(b => 
            b.textContent.includes('Resetear Password')
        );
        
        // Tabs de historial
        const tabBoletos = document.getElementById('tabBoletos');
        const tabPagos = document.getElementById('tabPagos');
        
        // Contenedores de contenido
        const tablaBoletos = document.getElementById('tablaBoletos');
        const tablaPagos = document.getElementById('tablaPagos');

        // Función para obtener datos actuales del usuario
        function obtenerDatosUsuario() {
            const nombreHeader = document.getElementById('user-nombre-header');
            const nombreCompleto = nombreHeader ? nombreHeader.textContent.trim() : 'Juan Pérez';

            const emailEl = document.getElementById('user-email');
            const telefonoEl = document.getElementById('user-telefono');
            const direccion1El = document.getElementById('user-direccion1');
            const direccion2El = document.getElementById('user-direccion2');
            const direccion3El = document.getElementById('user-direccion3');

            return {
                nombreCompleto,
                email: emailEl ? emailEl.textContent.trim() : 'juan.perez@email.com',
                telefono: telefonoEl ? telefonoEl.textContent.trim() : '+34 612 345 678',
                direccion1: direccion1El ? direccion1El.textContent.trim() : 'Calle Mayor 123',
                direccion2: direccion2El ? direccion2El.textContent.trim() : '28001, Madrid',
                direccion3: direccion3El ? direccion3El.textContent.trim() : 'España'
            };
        }

        // Función para actualizar datos en la página
        function actualizarDatosUsuario(datos) {
            // Actualizar nombre en el header
            const nombreHeader = document.getElementById('user-nombre-header');
            if (nombreHeader) {
                nombreHeader.textContent = datos.nombreCompleto;
            }

            // Actualizar nombre en la sección de información personal
            const nombreInfo = document.getElementById('user-nombre');
            if (nombreInfo) {
                nombreInfo.textContent = datos.nombreCompleto;
            }

            // Actualizar email
            const emailEl = document.getElementById('user-email');
            if (emailEl) {
                emailEl.textContent = datos.email;
            }

            // Actualizar teléfono
            const telefonoEl = document.getElementById('user-telefono');
            if (telefonoEl) {
                telefonoEl.textContent = datos.telefono;
            }

            // Actualizar dirección
            const direccion1El = document.getElementById('user-direccion1');
            const direccion2El = document.getElementById('user-direccion2');
            const direccion3El = document.getElementById('user-direccion3');

            if (direccion1El) direccion1El.textContent = datos.direccion1;
            if (direccion2El) direccion2El.textContent = datos.direccion2;
            if (direccion3El) direccion3El.textContent = datos.direccion3;
        }

        // Función para mostrar modal de edición
        function mostrarModalEdicion() {
            const datos = obtenerDatosUsuario();

            // Función para cerrar el modal
            const cerrarModal = (e) => {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                if (modal && modal.parentElement) {
                    modal.remove();
                }
                document.removeEventListener('keydown', closeModalHandler);
            };

            // Handler para cerrar con ESC
            const closeModalHandler = (e) => {
                if (e.key === 'Escape') {
                    cerrarModal();
                }
            };

            // Crear modal
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 z-50 overflow-y-auto';
            modal.setAttribute('id', 'modal-editar-usuario');
            modal.innerHTML = `
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity bg-gray-900/75 backdrop-blur-sm cursor-pointer" id="modal-backdrop"></div>
                    <div class="inline-block align-bottom bg-white dark:bg-[#1e232e] rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-200 dark:border-[#282d39]" id="modal-content">
                        <form id="formEditarUsuario">
                            <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-primary/10 sm:mx-0 sm:h-10 sm:w-10">
                                        <span class="material-symbols-outlined text-primary">edit</span>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                        <h3 class="text-lg leading-6 font-medium text-slate-900 dark:text-white mb-4">
                                            Editar Usuario
                                        </h3>
                                        <div class="mt-4 space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre Completo</label>
                                                <input type="text" id="edit-nombre" value="${datos.nombreCompleto}" required
                                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#282d39] shadow-sm focus:ring-primary focus:border-primary sm:text-sm bg-white dark:bg-[#282d39] dark:text-white dark:placeholder-gray-500 px-3 py-2">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Correo Electrónico</label>
                                                <input type="email" id="edit-email" value="${datos.email}" required
                                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#282d39] shadow-sm focus:ring-primary focus:border-primary sm:text-sm bg-white dark:bg-[#282d39] dark:text-white dark:placeholder-gray-500 px-3 py-2">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Teléfono</label>
                                                <input type="tel" id="edit-telefono" value="${datos.telefono}" required
                                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#282d39] shadow-sm focus:ring-primary focus:border-primary sm:text-sm bg-white dark:bg-[#282d39] dark:text-white dark:placeholder-gray-500 px-3 py-2">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dirección Línea 1</label>
                                                <input type="text" id="edit-direccion1" value="${datos.direccion1}" required
                                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#282d39] shadow-sm focus:ring-primary focus:border-primary sm:text-sm bg-white dark:bg-[#282d39] dark:text-white dark:placeholder-gray-500 px-3 py-2">
                                            </div>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ciudad / Código</label>
                                                    <input type="text" id="edit-direccion2" value="${datos.direccion2}" required
                                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#282d39] shadow-sm focus:ring-primary focus:border-primary sm:text-sm bg-white dark:bg-[#282d39] dark:text-white dark:placeholder-gray-500 px-3 py-2">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">País</label>
                                                    <input type="text" id="edit-direccion3" value="${datos.direccion3}" required
                                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#282d39] shadow-sm focus:ring-primary focus:border-primary sm:text-sm bg-white dark:bg-[#282d39] dark:text-white dark:placeholder-gray-500 px-3 py-2">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-[#151a23] px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="submit" id="btn-guardar" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm">
                                    Guardar Cambios
                                </button>
                                <button type="button" id="btn-cancelar" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-[#282d39] shadow-sm px-4 py-2 bg-white dark:bg-[#1e232e] text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-[#282d39] sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            // Agregar event listeners
            const backdrop = modal.querySelector('#modal-backdrop');
            const btnCancelar = modal.querySelector('#btn-cancelar');
            const formEditar = modal.querySelector('#formEditarUsuario');
            const modalContent = modal.querySelector('#modal-content');

            // Cerrar con botón cancelar
            if (btnCancelar) {
                btnCancelar.addEventListener('click', cerrarModal);
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

            // Guardar cambios
            if (formEditar) {
                formEditar.addEventListener('submit', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const nuevoNombre = document.getElementById('edit-nombre').value;
                    const nuevoEmail = document.getElementById('edit-email').value;
                    const nuevoTelefono = document.getElementById('edit-telefono').value;
                    const nuevaDir1 = document.getElementById('edit-direccion1').value;
                    const nuevaDir2 = document.getElementById('edit-direccion2').value;
                    const nuevaDir3 = document.getElementById('edit-direccion3').value;

                    // Actualizar los datos en la página
                    actualizarDatosUsuario({
                        nombreCompleto: nuevoNombre,
                        email: nuevoEmail,
                        telefono: nuevoTelefono,
                        direccion1: nuevaDir1,
                        direccion2: nuevaDir2,
                        direccion3: nuevaDir3
                    });

                    alert(`¡Usuario "${nuevoNombre}" actualizado exitosamente!`);
                    cerrarModal();
                });
            }
        }

        // Función para cambiar entre tabs
        function cambiarTab(tabActivo) {
            if (!tabBoletos || !tabPagos) return;

            if (tabActivo === 'boletos') {
                // Activar tab de boletos
                tabBoletos.classList.remove('text-[#9da6b9]', 'border-transparent');
                tabBoletos.classList.add('text-primary', 'border-primary', 'bg-[#282d39]/50', 'font-bold');
                tabBoletos.classList.remove('hover:text-white', 'font-medium');
                
                tabPagos.classList.remove('text-primary', 'border-primary', 'bg-[#282d39]/50', 'font-bold');
                tabPagos.classList.add('text-[#9da6b9]', 'border-transparent', 'hover:text-white', 'font-medium');

                // Mostrar tabla de boletos, ocultar tabla de pagos
                if (tablaBoletos) tablaBoletos.style.display = '';
                if (tablaPagos) tablaPagos.style.display = 'none';
            } else if (tabActivo === 'pagos') {
                // Activar tab de pagos
                tabPagos.classList.remove('text-[#9da6b9]', 'border-transparent');
                tabPagos.classList.add('text-primary', 'border-primary', 'bg-[#282d39]/50', 'font-bold');
                tabPagos.classList.remove('hover:text-white', 'font-medium');
                
                tabBoletos.classList.remove('text-primary', 'border-primary', 'bg-[#282d39]/50', 'font-bold');
                tabBoletos.classList.add('text-[#9da6b9]', 'border-transparent', 'hover:text-white', 'font-medium');

                // Mostrar tabla de pagos, ocultar tabla de boletos
                if (tablaBoletos) tablaBoletos.style.display = 'none';
                if (tablaPagos) tablaPagos.style.display = '';
            }
        }

        // Event listeners
        if (btnEditar) {
            btnEditar.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                mostrarModalEdicion();
            });
        }

        if (tabBoletos) {
            tabBoletos.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                cambiarTab('boletos');
            });
        }

        if (tabPagos) {
            tabPagos.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                cambiarTab('pagos');
            });
        }

        if (btnResetPassword) {
            btnResetPassword.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (confirm('¿Estás seguro de resetear la contraseña de este usuario? Se enviará un email con la nueva contraseña.')) {
                    alert('Contraseña reseteada exitosamente. Se ha enviado un email al usuario con la nueva contraseña.');
                }
            });
        }

        // Función para mostrar detalles del sorteo
        function mostrarDetallesSorteo(button) {
            const sorteo = button.getAttribute('data-sorteo');
            const id = button.getAttribute('data-id');
            const fecha = button.getAttribute('data-fecha');
            const boletos = button.getAttribute('data-boletos');
            const estado = button.getAttribute('data-estado');
            const imagen = button.getAttribute('data-imagen');
            
            // Obtener el número del boleto ganador desde el atributo data
            // Si no existe, se mostrará un mensaje indicando que está pendiente de base de datos
            const boletoGanador = button.getAttribute('data-boleto-ganador');

            // Función para cerrar el modal
            const cerrarModal = (e) => {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                if (modal && modal.parentElement) {
                    modal.remove();
                }
                document.removeEventListener('keydown', closeModalHandler);
            };

            // Handler para cerrar con ESC
            const closeModalHandler = (e) => {
                if (e.key === 'Escape') {
                    cerrarModal();
                }
            };

            // Determinar color del badge según el estado
            let estadoColor = 'blue';
            let estadoBg = 'bg-blue-400/10';
            let estadoRing = 'ring-blue-400/20';
            let estadoText = 'text-blue-400';

            if (estado === 'Finalizado') {
                estadoColor = 'yellow';
                estadoBg = 'bg-yellow-400/10';
                estadoRing = 'ring-yellow-400/20';
                estadoText = 'text-yellow-400';
            } else if (estado === 'Ganador') {
                estadoColor = 'green';
                estadoBg = 'bg-green-400/10';
                estadoRing = 'ring-green-400/20';
                estadoText = 'text-green-400';
            } else if (estado === 'No Ganador') {
                estadoColor = 'red';
                estadoBg = 'bg-red-400/10';
                estadoRing = 'ring-red-400/20';
                estadoText = 'text-red-400';
            }

            // Crear modal
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 z-50 overflow-y-auto';
            modal.setAttribute('id', 'modal-detalles-sorteo');
            modal.innerHTML = `
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity bg-gray-900/75 backdrop-blur-sm cursor-pointer" id="modal-backdrop-sorteo"></div>
                    <div class="inline-block align-bottom bg-white dark:bg-[#1e232e] rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-gray-200 dark:border-[#282d39]" id="modal-content-sorteo">
                        <div class="bg-white dark:bg-[#1e232e] px-4 pt-5 pb-4 sm:p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-4">
                                    <div class="size-16 rounded-xl bg-[#282d39] bg-cover bg-center border-2 border-[#282d39]" style="background-image: url('${imagen}');"></div>
                                    <div>
                                        <h3 class="text-xl leading-6 font-bold text-slate-900 dark:text-white mb-1">
                                            ${sorteo}
                                        </h3>
                                        <p class="text-sm text-gray-500 dark:text-[#9da6b9]">${id}</p>
                                    </div>
                                </div>
                                <button type="button" id="btn-cerrar-sorteo" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                                <div class="rounded-lg border border-gray-200 dark:border-[#282d39] bg-gray-50 dark:bg-[#151a23] p-4">
                                    <p class="text-xs font-medium text-gray-500 dark:text-[#9da6b9] uppercase tracking-wider mb-1">Fecha del Sorteo</p>
                                    <p class="text-base font-semibold text-slate-900 dark:text-white">${fecha}</p>
                                </div>
                                <div class="rounded-lg border border-gray-200 dark:border-[#282d39] bg-gray-50 dark:bg-[#151a23] p-4">
                                    <p class="text-xs font-medium text-gray-500 dark:text-[#9da6b9] uppercase tracking-wider mb-1">Boletos Comprados</p>
                                    <p class="text-base font-semibold text-slate-900 dark:text-white">${boletos} ${boletos === '1' ? 'Boleto' : 'Boletos'}</p>
                                </div>
                                <div class="rounded-lg border border-gray-200 dark:border-[#282d39] bg-gray-50 dark:bg-[#151a23] p-4">
                                    <p class="text-xs font-medium text-gray-500 dark:text-[#9da6b9] uppercase tracking-wider mb-1">Estado</p>
                                    <span class="inline-flex items-center gap-1 rounded-full ${estadoBg} px-3 py-1 text-sm font-medium ${estadoText} ring-1 ring-inset ${estadoRing}">
                                        ${estado}
                                    </span>
                                </div>
                                <div class="rounded-lg border border-gray-200 dark:border-[#282d39] bg-gray-50 dark:bg-[#151a23] p-4">
                                    <p class="text-xs font-medium text-gray-500 dark:text-[#9da6b9] uppercase tracking-wider mb-1">Usuario</p>
                                    <p class="text-base font-semibold text-slate-900 dark:text-white" id="user-nombre-header-detalle">${document.getElementById('user-nombre-header')?.textContent || 'Juan Pérez'}</p>
                                </div>
                                ${(estado === 'Ganador' || boletoGanador) ? `
                                <div class="rounded-lg border-2 border-green-400/30 dark:border-green-400/30 bg-green-400/5 dark:bg-green-400/5 p-4 md:col-span-2">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="material-symbols-outlined text-green-500 text-lg">emoji_events</span>
                                        <p class="text-xs font-medium text-green-600 dark:text-green-400 uppercase tracking-wider">Boleto Ganador</p>
                                    </div>
                                    <p class="text-2xl font-bold text-green-600 dark:text-green-400 mb-1">${boletoGanador ? `#${boletoGanador}` : 'N/A - Pendiente de base de datos'}</p>
                                    <p class="text-xs text-gray-500 dark:text-[#9da6b9] mt-1">Número del boleto ganador de este sorteo</p>
                                </div>
                                ` : ''}
                            </div>

                            <div class="mt-6 border-t border-gray-200 dark:border-[#282d39] pt-4">
                                <h4 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Detalles del Sorteo</h4>
                                <div class="space-y-2 text-sm text-gray-600 dark:text-[#9da6b9]">
                                    <p>• Sorteo realizado el ${fecha}</p>
                                    <p>• El usuario compró ${boletos} ${boletos === '1' ? 'boleto' : 'boletos'} para este sorteo</p>
                                    <p>• Estado actual: <span class="font-medium ${estadoText}">${estado}</span></p>
                                    ${estado === 'Ganador' ? `
                                        <p class="text-green-500 font-medium">• ¡Felicidades! Este usuario ganó el sorteo</p>
                                        ${boletoGanador ? `<p class="text-green-500 font-medium">• Boleto ganador: <span class="font-bold">#${boletoGanador}</span></p>` : '<p class="text-yellow-500 text-xs font-medium">• Nota: El número del boleto ganador se obtendrá de la base de datos al integrar con el backend</p>'}
                                    ` : ''}
                                    ${estado === 'Finalizado' ? '<p>• Este sorteo ya ha finalizado</p>' : ''}
                                    ${estado === 'En curso' ? '<p>• Este sorteo aún está en curso</p>' : ''}
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-[#151a23] px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" id="btn-entendido-sorteo" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm">
                                Entendido
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            // Agregar event listeners
            const backdrop = modal.querySelector('#modal-backdrop-sorteo');
            const btnCerrar = modal.querySelector('#btn-cerrar-sorteo');
            const btnEntendido = modal.querySelector('#btn-entendido-sorteo');
            const modalContent = modal.querySelector('#modal-content-sorteo');

            // Cerrar con botón cerrar
            if (btnCerrar) {
                btnCerrar.addEventListener('click', cerrarModal);
            }

            // Cerrar con botón entendido
            if (btnEntendido) {
                btnEntendido.addEventListener('click', cerrarModal);
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

        // Event listeners para botones de ver sorteo
        document.addEventListener('click', (e) => {
            const btnVerSorteo = e.target.closest('.btn-ver-sorteo');
            if (btnVerSorteo) {
                e.preventDefault();
                e.stopPropagation();
                mostrarDetallesSorteo(btnVerSorteo);
            }
        });

        // Función para obtener el ID del usuario
        function obtenerUserId() {
            // Primero intentar obtener de la URL
            const urlParams = new URLSearchParams(window.location.search);
            let userId = urlParams.get('userId');
            
            // Si no está en la URL, intentar obtener del elemento en la página
            if (!userId) {
                const userIdDisplay = document.getElementById('user-id-display');
                if (userIdDisplay) {
                    // Extraer solo el número del ID (ej: "#849320" -> "849320")
                    const idText = userIdDisplay.textContent.trim();
                    userId = idText.replace('#', '').trim();
                }
            }
            
            return userId;
        }

        // Botón Zona de Peligro - Redirigir a ZonaPeligroUsuario.html
        const btnZonaPeligro = document.getElementById('btnZonaPeligro');
        if (btnZonaPeligro) {
            btnZonaPeligro.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                const userId = obtenerUserId();
                
                // Redirigir a Zona de Peligro con el userId
                if (userId) {
                    window.location.href = `ZonaPeligroUsuario.html?userId=${userId}`;
                } else {
                    // Si no se puede obtener el ID, mostrar un mensaje de error
                    alert('Error: No se pudo obtener el ID del usuario. Por favor, asegúrate de estar viendo los detalles de un usuario válido.');
                }
            });
        }

        // Función para filtrar por fecha (funciona con ambos tabs)
        function filtrarPorFecha() {
            const fechaDesde = document.getElementById('filtroFechaDesde')?.value;
            const fechaHasta = document.getElementById('filtroFechaHasta')?.value;
            
            // Filtrar tabla de boletos
            if (tablaBoletos) {
                const filasBoletos = tablaBoletos.querySelectorAll('tbody tr');
                filasBoletos.forEach(fila => {
                    const fechaBoleto = fila.getAttribute('data-fecha');
                    if (!fechaBoleto) {
                        fila.style.display = 'none';
                        return;
                    }
                    
                    let mostrar = true;
                    
                    if (fechaDesde) {
                        if (fechaBoleto < fechaDesde) {
                            mostrar = false;
                        }
                    }
                    
                    if (fechaHasta && mostrar) {
                        if (fechaBoleto > fechaHasta) {
                            mostrar = false;
                        }
                    }
                    
                    fila.style.display = mostrar ? '' : 'none';
                });
            }
            
            // Filtrar tabla de pagos
            if (tablaPagos) {
                const filasPagos = tablaPagos.querySelectorAll('tbody tr');
                filasPagos.forEach(fila => {
                    const fechaPago = fila.getAttribute('data-fecha');
                    if (!fechaPago) {
                        fila.style.display = 'none';
                        return;
                    }
                    
                    let mostrar = true;
                    
                    if (fechaDesde) {
                        if (fechaPago < fechaDesde) {
                            mostrar = false;
                        }
                    }
                    
                    if (fechaHasta && mostrar) {
                        if (fechaPago > fechaHasta) {
                            mostrar = false;
                        }
                    }
                    
                    fila.style.display = mostrar ? '' : 'none';
                });
            }
        }
        
        // Función para limpiar filtros y mostrar todas las filas
        function limpiarFiltros() {
            const fechaDesde = document.getElementById('filtroFechaDesde');
            const fechaHasta = document.getElementById('filtroFechaHasta');
            
            if (fechaDesde) fechaDesde.value = '';
            if (fechaHasta) fechaHasta.value = '';
            
            // Mostrar todas las filas de boletos
            if (tablaBoletos) {
                const filasBoletos = tablaBoletos.querySelectorAll('tbody tr');
                filasBoletos.forEach(fila => {
                    fila.style.display = '';
                });
            }
            
            // Mostrar todas las filas de pagos
            if (tablaPagos) {
                const filasPagos = tablaPagos.querySelectorAll('tbody tr');
                filasPagos.forEach(fila => {
                    fila.style.display = '';
                });
            }
        }
        
        // Botón aplicar filtro
        const btnAplicarFiltro = document.getElementById('btnAplicarFiltro');
        if (btnAplicarFiltro) {
            btnAplicarFiltro.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                filtrarPorFecha();
            });
        }
        
        // Botón limpiar filtro
        const btnLimpiarFiltro = document.getElementById('btnLimpiarFiltro');
        if (btnLimpiarFiltro) {
            btnLimpiarFiltro.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                limpiarFiltros();
            });
        }
        
        // Permitir aplicar filtro al cambiar los inputs de fecha
        const filtroFechaDesde = document.getElementById('filtroFechaDesde');
        const filtroFechaHasta = document.getElementById('filtroFechaHasta');
        
        [filtroFechaDesde, filtroFechaHasta].forEach(input => {
            if (input) {
                input.addEventListener('change', filtrarPorFecha);
            }
        });

        // Inicializar: mostrar tab de boletos por defecto, ocultar tab de pagos
        if (tablaPagos) {
            tablaPagos.style.display = 'none';
        }
    }
})();
