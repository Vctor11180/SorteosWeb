/**
 * Generador de Ganadores
 * 
 * Este módulo implementa un generador de números aleatorios para seleccionar ganadores
 * de sorteos usando Math.random().
 * 
 * CARACTERÍSTICAS:
 * - Generación de números aleatorios normales
 * - Sistema de "números al agua" (números descartados antes del ganador)
 * - Verificación automática de existencia de usuario con boleto ganador
 * - Regeneración automática si el boleto no tiene usuario asignado
 * 
 * ENDPOINTS DE API REQUERIDOS:
 * 1. GET /api/sorteos/{idSorteo}/boletos-vendidos
 *    Retorna: { boletos: [{ numero_boleto: string, id_usuario: number, ... }] }
 * 
 * 2. GET /api/sorteos/{idSorteo}/boletos/{numeroBoleto}/verificar-usuario
 *    Retorna: { tieneUsuario: boolean, usuario: { id_usuario, primer_nombre, apellido_paterno, email, ... } }
 * 
 * 3. POST /api/ganadores/guardar
 *    Body: { id_sorteo: number, numero_boleto: string, id_usuario: number }
 *    Retorna: { success: boolean, id_ganador: number }
 */
(function() {
    // Generador de números aleatorios normal usando Math.random()
    
    class GeneradorGanadoresSeguro {
        constructor() {
            this.numerosDescarte = [];
            this.numeroGanador = null;
            this.configuracion = {
                numerosAlAgua: 0,
                numerosRestantes: 0,
                idSorteo: null,
                totalBoletos: 0,
                boletosVendidos: []
            };
            this.inicializarEventos();
        }

        // Generar número aleatorio normal
        generarNumeroAleatorio(min, max) {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }

        // Obtener boletos vendidos del sorteo desde la API
        async obtenerBoletosVendidos(idSorteo) {
            try {
                const response = await fetch(`/api/sorteos/${idSorteo}/boletos-vendidos`);
                if (!response.ok) {
                    throw new Error('Error al obtener boletos');
                }
                const data = await response.json();
                return data.boletos || [];
            } catch (error) {
                console.error('Error al obtener boletos vendidos:', error);
                // En caso de error, retornar array vacío
                return [];
            }
        }

        // Verificar si un boleto tiene usuario asignado
        async verificarBoletoConUsuario(numeroBoleto, idSorteo) {
            try {
                const response = await fetch(`/api/sorteos/${idSorteo}/boletos/${numeroBoleto}/verificar-usuario`);
                if (!response.ok) {
                    throw new Error('Error al verificar boleto');
                }
                const data = await response.json();
                return {
                    existe: data.tieneUsuario || false,
                    usuario: data.usuario || null
                };
            } catch (error) {
                console.error('Error al verificar boleto:', error);
                return { existe: false, usuario: null };
            }
        }

        // Generar número de boleto ganador válido (excluyendo números descartados)
        async generarBoletoGanador() {
            const boletosVendidos = this.configuracion.boletosVendidos;
            const numerosDescarte = this.numerosDescarte.map(n => n.toString());
            
            if (boletosVendidos.length === 0) {
                throw new Error('No hay boletos vendidos para este sorteo');
            }

            // Filtrar boletos que no están en los descartes
            const boletosDisponibles = boletosVendidos.filter(boleto => {
                const numBoleto = boleto.numero_boleto.toString();
                return !numerosDescarte.includes(numBoleto);
            });

            if (boletosDisponibles.length === 0) {
                throw new Error('Todos los boletos disponibles ya fueron descartados');
            }

            let intentos = 0;
            const maxIntentos = 100; // Máximo de intentos para evitar loops infinitos
            
            while (intentos < maxIntentos) {
                // Generar índice aleatorio entre los boletos disponibles
                const indiceAleatorio = this.generarNumeroAleatorio(0, boletosDisponibles.length - 1);
                const boletoSeleccionado = boletosDisponibles[indiceAleatorio];
                
                // Verificar que el boleto tenga usuario asignado
                const verificacion = await this.verificarBoletoConUsuario(
                    boletoSeleccionado.numero_boleto,
                    this.configuracion.idSorteo
                );
                
                if (verificacion.existe && verificacion.usuario) {
                    return {
                        numeroBoleto: boletoSeleccionado.numero_boleto,
                        usuario: verificacion.usuario
                    };
                }
                
                // Si no tiene usuario, remover de la lista para no intentar de nuevo
                const indiceEliminar = boletosDisponibles.findIndex(b => 
                    b.numero_boleto === boletoSeleccionado.numero_boleto
                );
                if (indiceEliminar > -1) {
                    boletosDisponibles.splice(indiceEliminar, 1);
                }
                
                if (boletosDisponibles.length === 0) {
                    throw new Error('No hay más boletos válidos con usuario asignado');
                }
                
                intentos++;
            }
            
            throw new Error('No se pudo encontrar un boleto válido con usuario después de múltiples intentos');
        }

        // Mostrar modal de configuración
        mostrarModalConfiguracion(idSorteo, botonGenerar) {
            return new Promise((resolve) => {
                const modal = document.createElement('div');
                modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm';
                modal.id = 'modalConfiguracion';
                
                modal.innerHTML = `
                    <div class="bg-surface-dark rounded-2xl border border-[#3e3e52] shadow-2xl p-6 max-w-md w-full mx-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-white text-xl font-bold">Configurar Generación</h3>
                            <button class="text-[#9d9db9] hover:text-white transition-colors" id="btnCerrarConfig">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>
                        <p class="text-[#9d9db9] text-sm mb-4">
                            ¿Cuántos números al agua deseas generar antes de revelar al ganador?
                        </p>
                        <div class="mb-4">
                            <label class="block text-white text-sm font-medium mb-2">
                                Números al agua (descarte)
                            </label>
                            <input 
                                type="number" 
                                id="inputNumerosAlAgua"
                                min="0" 
                                max="20" 
                                value="4" 
                                class="w-full bg-[#1e1e2d] border border-[#3e3e52] rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                            <p class="text-[#9d9db9] text-xs mt-1">Estos números se mostrarán antes del ganador final</p>
                        </div>
                        <div class="flex gap-3">
                            <button 
                                id="btnIniciarGeneracion"
                                class="flex-1 bg-primary hover:bg-blue-600 text-white font-semibold py-3 rounded-lg transition-colors"
                            >
                                Iniciar Generación
                            </button>
                            <button 
                                id="btnCancelarConfig"
                                class="flex-1 bg-[#34344a] hover:bg-[#3f3f5a] text-white font-medium py-3 rounded-lg transition-colors"
                            >
                                Cancelar
                            </button>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(modal);
                
                const btnIniciar = modal.querySelector('#btnIniciarGeneracion');
                const btnCancelar = modal.querySelector('#btnCancelarConfig');
                const btnCerrar = modal.querySelector('#btnCerrarConfig');
                const inputNumeros = modal.querySelector('#inputNumerosAlAgua');
                
                const cerrarModal = () => {
                    document.body.removeChild(modal);
                    resolve(null);
                };
                
                btnIniciar.addEventListener('click', () => {
                    const numerosAlAgua = parseInt(inputNumeros.value) || 0;
                    document.body.removeChild(modal);
                    resolve(numerosAlAgua);
                });
                
                btnCancelar.addEventListener('click', cerrarModal);
                btnCerrar.addEventListener('click', cerrarModal);
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) cerrarModal();
                });
            });
        }

        // Mostrar número al agua
        mostrarNumeroAlAgua(numero, indice, total) {
            return new Promise((resolve) => {
                const modal = document.createElement('div');
                modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm';
                modal.id = 'modalNumeroAlAgua';
                
                modal.innerHTML = `
                    <div class="bg-surface-dark rounded-2xl border border-[#3e3e52] shadow-2xl p-8 max-w-md w-full mx-4 text-center">
                        <div class="mb-6">
                            <div class="size-20 mx-auto mb-4 rounded-full bg-red-500/20 flex items-center justify-center border-2 border-red-500/50">
                                <span class="material-symbols-outlined text-4xl text-red-400">water_drop</span>
                            </div>
                            <h3 class="text-white text-2xl font-bold mb-2">Número al Agua</h3>
                            <p class="text-[#9d9db9] text-sm">Descarte ${indice} de ${total}</p>
                        </div>
                        <div class="bg-[#1e1e2d] rounded-xl p-6 border border-[#3e3e52] mb-6">
                            <p class="text-[#9d9db9] text-sm mb-2 uppercase">Boleto Descartado</p>
                            <p class="text-red-400 text-4xl font-mono font-bold tracking-widest">#${numero.toString().padStart(4, '0')}</p>
                        </div>
                        <button 
                            id="btnContinuarAlAgua"
                            class="w-full bg-primary hover:bg-blue-600 text-white font-semibold py-3 rounded-lg transition-colors"
                        >
                            ${indice < total ? 'Continuar' : 'Ver Ganador'}
                        </button>
                    </div>
                `;
                
                document.body.appendChild(modal);
                
                const btnContinuar = modal.querySelector('#btnContinuarAlAgua');
                btnContinuar.addEventListener('click', () => {
                    document.body.removeChild(modal);
                    resolve();
                });
            });
        }

        // Mostrar ganador final
        mostrarGanador(boletoGanador, usuario) {
            return new Promise((resolve) => {
                const modal = document.createElement('div');
                modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm';
                modal.id = 'modalGanador';
                
                modal.innerHTML = `
                    <div class="bg-surface-dark rounded-2xl border border-success/30 shadow-2xl p-8 max-w-md w-full mx-4">
                        <div class="text-center mb-6">
                            <div class="size-24 mx-auto mb-4 rounded-full bg-success/20 flex items-center justify-center border-4 border-success/50">
                                <span class="material-symbols-outlined text-5xl text-success">emoji_events</span>
                            </div>
                            <h3 class="text-white text-3xl font-bold mb-2">¡Ganador Encontrado!</h3>
                            <p class="text-[#9d9db9] text-sm">Boleto seleccionado mediante algoritmo seguro</p>
                        </div>
                        <div class="bg-[#1e1e2d] rounded-xl p-6 border border-success/30 mb-6">
                            <div class="mb-4 pb-4 border-b border-[#3e3e52]">
                                <p class="text-[#9d9db9] text-xs uppercase mb-2">Boleto Ganador</p>
                                <p class="text-success text-4xl font-mono font-bold tracking-widest">#${boletoGanador.toString().padStart(4, '0')}</p>
                            </div>
                            <div>
                                <p class="text-[#9d9db9] text-xs uppercase mb-2">Ganador</p>
                                <p class="text-white text-xl font-semibold">${usuario.primer_nombre} ${usuario.apellido_paterno}</p>
                                <p class="text-[#9d9db9] text-sm mt-1">${usuario.email}</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <button 
                                id="btnConfirmarGanador"
                                class="flex-1 bg-success hover:bg-green-600 text-white font-semibold py-3 rounded-lg transition-colors"
                            >
                                Confirmar Ganador
                            </button>
                            <button 
                                id="btnCancelarGanador"
                                class="flex-1 bg-[#34344a] hover:bg-[#3f3f5a] text-white font-medium py-3 rounded-lg transition-colors"
                            >
                                Cancelar
                            </button>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(modal);
                
                const btnConfirmar = modal.querySelector('#btnConfirmarGanador');
                const btnCancelar = modal.querySelector('#btnCancelarGanador');
                
                btnConfirmar.addEventListener('click', async () => {
                    try {
                        // Guardar ganador en la base de datos
                        await this.guardarGanador(boletoGanador, usuario);
                        document.body.removeChild(modal);
                        resolve(true);
                    } catch (error) {
                        alert('Error al guardar el ganador: ' + error.message);
                    }
                });
                
                btnCancelar.addEventListener('click', () => {
                    document.body.removeChild(modal);
                    resolve(false);
                });
            });
        }

        // Guardar ganador en la base de datos
        async guardarGanador(numeroBoleto, usuario) {
            try {
                const response = await fetch('/api/ganadores/guardar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id_sorteo: this.configuracion.idSorteo,
                        numero_boleto: numeroBoleto,
                        id_usuario: usuario.id_usuario
                    })
                });
                
                if (!response.ok) {
                    throw new Error('Error al guardar el ganador');
                }
                
                // Recargar la página o actualizar la UI
                location.reload();
            } catch (error) {
                console.error('Error al guardar ganador:', error);
                throw error;
            }
        }

        // Proceso completo de generación
        async iniciarGeneracion(idSorteo, botonGenerar) {
            try {
                // Deshabilitar botón
                botonGenerar.disabled = true;
                botonGenerar.innerHTML = '<span class="material-symbols-outlined animate-spin">autorenew</span> Cargando...';
                
                // Obtener boletos vendidos
                const boletosVendidos = await this.obtenerBoletosVendidos(idSorteo);
                
                if (boletosVendidos.length === 0) {
                    alert('No hay boletos vendidos para este sorteo');
                    botonGenerar.disabled = false;
                    botonGenerar.innerHTML = '<span class="material-symbols-outlined group-hover:animate-spin">autorenew</span> Generar Ganador';
                    return;
                }
                
                // Configurar
                this.configuracion.idSorteo = idSorteo;
                this.configuracion.boletosVendidos = boletosVendidos;
                this.configuracion.totalBoletos = boletosVendidos.length;
                
                // Pedir configuración de números al agua
                const numerosAlAgua = await this.mostrarModalConfiguracion(idSorteo, botonGenerar);
                
                if (numerosAlAgua === null) {
                    // Usuario canceló
                    botonGenerar.disabled = false;
                    botonGenerar.innerHTML = '<span class="material-symbols-outlined group-hover:animate-spin">autorenew</span> Generar Ganador';
                    return;
                }
                
                this.configuracion.numerosAlAgua = numerosAlAgua;
                this.configuracion.numerosRestantes = numerosAlAgua;
                this.numerosDescarte = [];
                
                // Validar que haya suficientes boletos
                if (numerosAlAgua >= boletosVendidos.length) {
                    alert(`No hay suficientes boletos vendidos. Solo hay ${boletosVendidos.length} boletos disponibles.`);
                    botonGenerar.disabled = false;
                    botonGenerar.innerHTML = '<span class="material-symbols-outlined group-hover:animate-spin">autorenew</span> Generar Ganador';
                    return;
                }
                
                // Generar y mostrar números al agua (sin repeticiones)
                const numerosYaUsados = new Set();
                
                for (let i = 0; i < numerosAlAgua; i++) {
                    let numeroDescarte;
                    let intentos = 0;
                    const maxIntentosDescarte = 50;
                    
                    // Generar número aleatorio que no esté ya en los descartes
                    do {
                        const indiceAleatorio = this.generarNumeroAleatorio(0, boletosVendidos.length - 1);
                        numeroDescarte = boletosVendidos[indiceAleatorio].numero_boleto.toString();
                        intentos++;
                        
                        if (intentos >= maxIntentosDescarte) {
                            throw new Error('No se pudieron generar números al agua únicos después de múltiples intentos');
                        }
                    } while (numerosYaUsados.has(numeroDescarte));
                    
                    numerosYaUsados.add(numeroDescarte);
                    this.numerosDescarte.push(numeroDescarte);
                    
                    await this.mostrarNumeroAlAgua(numeroDescarte, i + 1, numerosAlAgua);
                }
                
                // Generar ganador final
                const resultadoGanador = await this.generarBoletoGanador();
                
                // Mostrar ganador
                const confirmado = await this.mostrarGanador(
                    resultadoGanador.numeroBoleto,
                    resultadoGanador.usuario
                );
                
                if (!confirmado) {
                    botonGenerar.disabled = false;
                    botonGenerar.innerHTML = '<span class="material-symbols-outlined group-hover:animate-spin">autorenew</span> Generar Ganador';
                }
                
            } catch (error) {
                console.error('Error en generación:', error);
                alert('Error al generar ganador: ' + error.message);
                botonGenerar.disabled = false;
                botonGenerar.innerHTML = '<span class="material-symbols-outlined group-hover:animate-spin">autorenew</span> Generar Ganador';
            }
        }

        // Inicializar eventos de los botones
        inicializarEventos() {
            const initFunction = () => {
                const botonesGenerar = document.querySelectorAll('[data-accion="generar-ganador"]');
                
                botonesGenerar.forEach(boton => {
                    boton.addEventListener('click', (e) => {
                        e.preventDefault();
                        const idSorteo = boton.getAttribute('data-id-sorteo');
                        
                        if (!idSorteo) {
                            alert('Error: No se encontró el ID del sorteo');
                            return;
                        }
                        
                        this.iniciarGeneracion(idSorteo, boton);
                    });
                });
                
                // Inicializar filtros y búsqueda
                this.inicializarFiltros();
            };
            
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initFunction);
            } else {
                // DOM ya está cargado, ejecutar inmediatamente
                initFunction();
            }
        }

        // Inicializar filtros y búsqueda
        inicializarFiltros() {
            const inputBusqueda = document.getElementById('inputBusqueda');
            const filtroTodos = document.getElementById('filtroTodos');
            const filtroFinalizado = document.getElementById('filtroFinalizado');
            const filtroEnCurso = document.getElementById('filtroEnCurso');
            const cardsSorteos = document.querySelectorAll('.sorteo-card');
            
            let filtroActual = 'todos';
            let textoBusqueda = '';
            
            // Función para actualizar los estilos de los botones de filtro
            const actualizarEstilosFiltros = (filtroActivo) => {
                const filtros = [filtroTodos, filtroFinalizado, filtroEnCurso];
                
                filtros.forEach(filtro => {
                    if (filtro && filtro.dataset.filtro === filtroActivo) {
                        filtro.classList.remove('bg-surface-dark', 'text-[#9d9db9]', 'border-[#3e3e52]', 'hover:bg-[#34344a]', 'hover:text-white');
                        filtro.classList.add('bg-primary/20', 'text-primary', 'border-primary/30');
                    } else if (filtro) {
                        filtro.classList.remove('bg-primary/20', 'text-primary', 'border-primary/30');
                        filtro.classList.add('bg-surface-dark', 'text-[#9d9db9]', 'border-[#3e3e52]', 'hover:bg-[#34344a]', 'hover:text-white');
                    }
                });
            };
            
            // Función para filtrar y buscar
            const aplicarFiltros = () => {
                cardsSorteos.forEach(card => {
                    const estado = card.dataset.estado || '';
                    const nombre = (card.dataset.nombre || '').toLowerCase();
                    const textoBusquedaLower = textoBusqueda.toLowerCase().trim();
                    
                    // Verificar filtro de estado
                    let pasaFiltroEstado = false;
                    if (filtroActual === 'todos') {
                        pasaFiltroEstado = true;
                    } else if (filtroActual === 'finalizado') {
                        // Finalizado muestra los que tienen estado "pendiente" (sorteos finalizados sin ganador) y "completado" (con ganador)
                        pasaFiltroEstado = estado === 'pendiente' || estado === 'completado';
                    } else if (filtroActual === 'en-curso') {
                        // En Curso muestra los que tienen estado "en-curso"
                        pasaFiltroEstado = estado === 'en-curso';
                    }
                    
                    // Verificar búsqueda por nombre
                    const pasaBusqueda = textoBusquedaLower === '' || nombre.includes(textoBusquedaLower);
                    
                    // Mostrar u ocultar según filtros
                    if (pasaFiltroEstado && pasaBusqueda) {
                        card.style.display = '';
                        card.classList.remove('hidden');
                    } else {
                        card.style.display = 'none';
                        card.classList.add('hidden');
                    }
                });
            };
            
            // Event listeners para filtros
            if (filtroTodos) {
                filtroTodos.addEventListener('click', () => {
                    filtroActual = 'todos';
                    actualizarEstilosFiltros('todos');
                    aplicarFiltros();
                });
            }
            
            if (filtroFinalizado) {
                filtroFinalizado.addEventListener('click', () => {
                    filtroActual = 'finalizado';
                    actualizarEstilosFiltros('finalizado');
                    aplicarFiltros();
                });
            }
            
            if (filtroEnCurso) {
                filtroEnCurso.addEventListener('click', () => {
                    filtroActual = 'en-curso';
                    actualizarEstilosFiltros('en-curso');
                    aplicarFiltros();
                });
            }
            
            // Event listener para búsqueda - usando una variable externa para mantener el valor
            if (inputBusqueda) {
                inputBusqueda.addEventListener('input', (e) => {
                    textoBusqueda = e.target.value || '';
                    aplicarFiltros();
                });
                
                inputBusqueda.addEventListener('keyup', (e) => {
                    textoBusqueda = e.target.value || '';
                    aplicarFiltros();
                });
                
                // También buscar al hacer Enter
                inputBusqueda.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        textoBusqueda = e.target.value || '';
                        aplicarFiltros();
                    }
                });
            }
            
            // Aplicar filtros iniciales
            aplicarFiltros();
        }
    }

    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            new GeneradorGanadoresSeguro();
        });
    } else {
        new GeneradorGanadoresSeguro();
    }
})();

