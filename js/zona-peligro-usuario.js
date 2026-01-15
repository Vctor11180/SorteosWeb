(function() {
    // Esperar a que el DOM esté completamente cargado
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciarZonaPeligro);
    } else {
        iniciarZonaPeligro();
    }

    function iniciarZonaPeligro() {
        // Referencias a elementos
        const btnBloquearTemporal = document.getElementById('btnBloquearTemporal');
        const btnBanearPermanente = document.getElementById('btnBanearPermanente');
        const duracionTemporal = document.getElementById('duracionTemporal');
        const razonTemporal = document.getElementById('razonTemporal');
        const razonPermanente = document.getElementById('razonPermanente');
        const confirmarPermanente = document.getElementById('confirmarPermanente');
        const estadoUsuario = document.getElementById('estadoUsuario');
        const userId = document.getElementById('userId')?.textContent || '#928374';

        // Obtener nombre del usuario desde el título
        const nombreUsuario = document.querySelector('h1.text-3xl')?.textContent || 'Juan Pérez';

        // Función para actualizar estado visual del usuario
        function actualizarEstadoUsuario(tipo, duracion = null) {
            if (!estadoUsuario) return;

            // Limpiar clases anteriores
            estadoUsuario.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium';

            if (tipo === 'bloqueado_temporal') {
                const duraciones = {
                    '24h': '24 Horas',
                    '3d': '3 Días',
                    '1w': '1 Semana',
                    '1m': '1 Mes'
                };
                estadoUsuario.textContent = `Bloqueado (${duraciones[duracion] || 'Temporal'})`;
                estadoUsuario.classList.add('bg-orange-500/10', 'text-orange-400', 'border', 'border-orange-500/20');
            } else if (tipo === 'baneado') {
                estadoUsuario.textContent = 'Baneado Permanente';
                estadoUsuario.classList.add('bg-red-500/10', 'text-red-400', 'border', 'border-red-500/20');
            } else {
                estadoUsuario.textContent = 'Activo';
                estadoUsuario.classList.add('bg-green-500/10', 'text-green-500', 'border', 'border-green-500/20');
            }
        }

        // Función para mostrar modal de confirmación
        function mostrarModalConfirmacion(tipo, datos = {}) {
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

            let contenidoModal = '';
            let tituloModal = '';
            let iconoColor = '';
            let botonColor = '';
            let botonTexto = '';

            if (tipo === 'temporal') {
                tituloModal = 'Confirmar Bloqueo Temporal';
                iconoColor = 'text-orange-400';
                botonColor = 'bg-orange-500 hover:bg-orange-600 border-orange-500/30';
                botonTexto = 'Confirmar Bloqueo';
                
                const duraciones = {
                    '24h': '24 Horas',
                    '3d': '3 Días',
                    '1w': '1 Semana',
                    '1m': '1 Mes'
                };

                contenidoModal = `
                    <div class="flex items-start gap-4 mb-6">
                        <div class="p-3 bg-orange-500/10 rounded-lg ${iconoColor}">
                            <span class="material-symbols-outlined text-3xl">block</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-white mb-2">${tituloModal}</h3>
                            <p class="text-[#b99d9d] text-sm mb-4">
                                Estás a punto de bloquear temporalmente a <span class="text-white font-bold">${nombreUsuario}</span> por un período de <span class="text-white font-semibold">${duraciones[datos.duracion] || datos.duracion}</span>.
                            </p>
                            <div class="bg-surface-dark rounded-lg p-4 border border-surface-dark-lighter">
                                <p class="text-xs font-medium text-[#b99d9d] uppercase mb-2">Detalles del Bloqueo</p>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-[#b99d9d]">Duración:</span>
                                        <span class="text-white font-medium">${duraciones[datos.duracion] || datos.duracion}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-[#b99d9d]">Usuario:</span>
                                        <span class="text-white font-medium">${nombreUsuario} (${userId})</span>
                                    </div>
                                    ${datos.razon ? `
                                    <div class="flex flex-col gap-1 pt-2 border-t border-surface-dark-lighter">
                                        <span class="text-[#b99d9d]">Razón:</span>
                                        <span class="text-white">"${datos.razon}"</span>
                                    </div>
                                    ` : '<p class="text-xs text-[#b99d9d] pt-2 border-t border-surface-dark-lighter">Sin razón especificada</p>'}
                                </div>
                            </div>
                            <p class="text-xs text-orange-400 mt-4 flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">info</span>
                                El usuario podrá acceder nuevamente después del período de bloqueo.
                            </p>
                        </div>
                    </div>
                `;
            } else if (tipo === 'permanente') {
                tituloModal = 'Confirmar Baneo Permanente';
                iconoColor = 'text-red-400';
                botonColor = 'bg-primary hover:bg-red-600 border-primary/30';
                botonTexto = 'Confirmar Baneo';

                contenidoModal = `
                    <div class="flex items-start gap-4 mb-6">
                        <div class="p-3 bg-primary/10 rounded-lg ${iconoColor}">
                            <span class="material-symbols-outlined text-3xl">delete_forever</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-white mb-2">${tituloModal}</h3>
                            <p class="text-[#b99d9d] text-sm mb-4">
                                Estás a punto de <span class="text-red-400 font-bold">banear permanentemente</span> a <span class="text-white font-bold">${nombreUsuario}</span>. Esta acción es <span class="text-red-400 font-bold">IRREVERSIBLE</span>.
                            </p>
                            <div class="bg-red-500/5 border-2 border-red-500/20 rounded-lg p-4 mb-4">
                                <p class="text-xs font-medium text-red-400 uppercase mb-2">⚠️ Advertencia</p>
                                <ul class="text-sm text-[#b99d9d] space-y-1 list-disc list-inside">
                                    <li>El usuario perderá acceso permanente a la plataforma</li>
                                    <li>Se revocarán todos sus boletos activos</li>
                                    <li>No podrá crear nuevas cuentas con el mismo email</li>
                                    <li>Esta acción no se puede deshacer</li>
                                </ul>
                            </div>
                            <div class="bg-surface-dark rounded-lg p-4 border border-surface-dark-lighter">
                                <p class="text-xs font-medium text-[#b99d9d] uppercase mb-2">Motivo del Baneo</p>
                                <p class="text-white text-sm">"${datos.razon}"</p>
                            </div>
                            <p class="text-xs text-red-400 mt-4 flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">warning</span>
                                Por favor, verifica que esta es la acción que deseas realizar.
                            </p>
                        </div>
                    </div>
                `;
            }

            // Crear modal
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 z-50 overflow-y-auto';
            modal.setAttribute('id', 'modal-confirmacion-ban');
            modal.innerHTML = `
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity bg-black/80 backdrop-blur-sm cursor-pointer" id="modal-backdrop-confirm"></div>
                    <div class="inline-block align-bottom bg-surface-dark border border-surface-dark-lighter rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full" id="modal-content-confirm">
                        <div class="p-6 sm:p-8">
                            ${contenidoModal}
                        </div>
                        <div class="bg-surface-dark-lighter/50 px-6 py-4 sm:px-8 sm:flex sm:flex-row-reverse gap-3 border-t border-surface-dark-lighter">
                            <button type="button" id="btn-confirmar-ban" class="w-full inline-flex justify-center items-center gap-2 rounded-lg border ${botonColor} shadow-sm px-6 py-3 text-base font-bold text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm transition-all">
                                <span class="material-symbols-outlined text-[20px]">${tipo === 'temporal' ? 'block' : 'delete_forever'}</span>
                                ${botonTexto}
                            </button>
                            <button type="button" id="btn-cancelar-ban" class="mt-3 w-full inline-flex justify-center rounded-lg border border-surface-dark-lighter shadow-sm px-6 py-3 bg-surface-dark text-base font-medium text-white hover:bg-surface-dark-lighter sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            // Agregar event listeners
            const backdrop = modal.querySelector('#modal-backdrop-confirm');
            const btnCancelar = modal.querySelector('#btn-cancelar-ban');
            const btnConfirmar = modal.querySelector('#btn-confirmar-ban');
            const modalContent = modal.querySelector('#modal-content-confirm');

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

            // Confirmar acción
            if (btnConfirmar) {
                btnConfirmar.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    cerrarModal();
                    
                    // Ejecutar la acción correspondiente después de cerrar el modal
                    setTimeout(() => {
                        if (tipo === 'temporal') {
                            ejecutarBloqueoTemporal(datos);
                        } else if (tipo === 'permanente') {
                            ejecutarBaneoPermanente(datos);
                        }
                    }, 100);
                });
            }
            
            return modal;
        }

        // Función para ejecutar bloqueo temporal
        function ejecutarBloqueoTemporal(datos) {
            // ============================================================
            // REFERENCIA PARA INTEGRACIÓN CON BASE DE DATOS:
            // ============================================================
            // Cuando se integre con la base de datos, hacer la llamada al backend:
            // 
            // Ejemplo:
            //   const response = await fetch('/api/usuarios/banear-temporal', {
            //       method: 'POST',
            //       headers: { 'Content-Type': 'application/json' },
            //       body: JSON.stringify({
            //           userId: userId.replace('#', ''),
            //           duracion: datos.duracion, // '24h', '3d', '1w', '1m'
            //           razon: datos.razon || null,
            //           fecha_inicio: new Date().toISOString(),
            //           fecha_fin: calcularFechaFin(datos.duracion)
            //       })
            //   });
            //   
            //   // También registrar en tabla de auditoría
            //   await fetch('/api/auditoria/registrar', {
            //       method: 'POST',
            //       body: JSON.stringify({
            //           accion: 'BLOQUEO_TEMPORAL',
            //           usuario_id: userId,
            //           detalles: { duracion: datos.duracion, razon: datos.razon },
            //           admin_id: getCurrentAdminId()
            //       })
            //   });
            // ============================================================

            // Actualizar estado visual
            actualizarEstadoUsuario('bloqueado_temporal', datos.duracion);

            // Limpiar formulario
            if (duracionTemporal) duracionTemporal.value = '';
            if (razonTemporal) razonTemporal.value = '';

            // Mostrar mensaje de éxito
            const duraciones = {
                '24h': '24 horas',
                '3d': '3 días',
                '1w': '1 semana',
                '1m': '1 mes'
            };
            const mensaje = `Usuario ${nombreUsuario} ha sido bloqueado temporalmente por ${duraciones[datos.duracion] || datos.duracion}.`;
            alert(`✅ ${mensaje}\n\nRazón: ${datos.razon || 'No especificada'}\n\nNota: Esta acción se registrará en la base de datos.`);
        }

        // Función para ejecutar baneo permanente
        function ejecutarBaneoPermanente(datos) {
            // ============================================================
            // REFERENCIA PARA INTEGRACIÓN CON BASE DE DATOS:
            // ============================================================
            // Cuando se integre con la base de datos, hacer la llamada al backend:
            // 
            // Ejemplo:
            //   const response = await fetch('/api/usuarios/banear-permanente', {
            //       method: 'POST',
            //       headers: { 'Content-Type': 'application/json' },
            //       body: JSON.stringify({
            //           userId: userId.replace('#', ''),
            //           razon: datos.razon,
            //           fecha_baneo: new Date().toISOString(),
            //           admin_id: getCurrentAdminId()
            //       })
            //   });
            //   
            //   // Revocar todos los boletos activos del usuario
            //   await fetch('/api/boletos/revocar', {
            //       method: 'POST',
            //       body: JSON.stringify({ userId: userId.replace('#', '') })
            //   });
            //   
            //   // Registrar en tabla de auditoría
            //   await fetch('/api/auditoria/registrar', {
            //       method: 'POST',
            //       body: JSON.stringify({
            //           accion: 'BANEO_PERMANENTE',
            //           usuario_id: userId,
            //           detalles: { razon: datos.razon },
            //           admin_id: getCurrentAdminId()
            //       })
            //   });
            // ============================================================

            // Actualizar estado visual
            actualizarEstadoUsuario('baneado');

            // Deshabilitar botones de acción
            if (btnBloquearTemporal) {
                btnBloquearTemporal.disabled = true;
                btnBloquearTemporal.classList.add('opacity-50', 'cursor-not-allowed');
            }
            if (btnBanearPermanente) {
                btnBanearPermanente.disabled = true;
                btnBanearPermanente.classList.add('opacity-50', 'cursor-not-allowed');
            }

            // Limpiar formulario
            if (razonPermanente) razonPermanente.value = '';
            if (confirmarPermanente) confirmarPermanente.checked = false;

            // Mostrar mensaje de éxito
            alert(`🚫 Usuario ${nombreUsuario} ha sido baneado permanentemente.\n\nMotivo: "${datos.razon}"\n\nNota: Esta acción es irreversible y se registrará en la base de datos.`);
        }

        // Event listener para bloqueo temporal
        if (btnBloquearTemporal) {
            btnBloquearTemporal.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                const duracion = duracionTemporal?.value;
                const razon = razonTemporal?.value?.trim() || '';

                // Validar que se haya seleccionado una duración
                if (!duracion) {
                    alert('⚠️ Por favor, selecciona una duración para el bloqueo temporal.');
                    duracionTemporal?.focus();
                    return;
                }

                // Mostrar modal de confirmación
                mostrarModalConfirmacion('temporal', {
                    duracion: duracion,
                    razon: razon
                });
            });
        }

        // Event listener para baneo permanente
        if (btnBanearPermanente) {
            btnBanearPermanente.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                const razon = razonPermanente?.value?.trim() || '';
                const confirmado = confirmarPermanente?.checked || false;

                // Validar que se haya proporcionado una razón
                if (!razon) {
                    alert('⚠️ Por favor, proporciona un motivo detallado para el baneo permanente.\n\nEste campo es obligatorio.');
                    razonPermanente?.focus();
                    return;
                }

                // Validar que se haya confirmado la acción
                if (!confirmado) {
                    alert('⚠️ Por favor, confirma que has revisado la actividad del usuario y entiendes que esta acción es irreversible.');
                    confirmarPermanente?.focus();
                    return;
                }

                // Mostrar modal de confirmación
                mostrarModalConfirmacion('permanente', {
                    razon: razon
                });
            });
        }
    }
})();

