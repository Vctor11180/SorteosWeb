(function() {
    // Esperar a que el DOM esté completamente cargado
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciarAuditoria);
    } else {
        iniciarAuditoria();
    }

    function iniciarAuditoria() {
        // Capturamos los elementos
        const tableSearchInput = document.querySelector('input[placeholder*="Buscar por ID de sorteo"]');
        const filtroFechaDesde = document.getElementById('filtroFechaDesde');
        const filtroFechaHasta = document.getElementById('filtroFechaHasta');
        const filtroTipo = document.getElementById('filtroTipo');
        const filtroEstado = document.getElementById('filtroEstado');
        const btnSoloAlertas = document.getElementById('btnSoloAlertas');
        const btnActualizar = Array.from(document.querySelectorAll('button')).find(b => b.textContent.includes('Actualizar'));
        const btnExportar = Array.from(document.querySelectorAll('button')).find(b => b.textContent.includes('Exportar CSV'));

        // Estado de filtros
        let filtros = {
            fechaDesde: null,
            fechaHasta: null,
            tipo: null,
            estado: null,
            soloAlertas: false,
            buscador: ''
        };

        // Función para obtener todas las filas
        function getTableRows() {
            return Array.from(document.querySelectorAll('table tbody tr'));
        }

        // Función para convertir fecha de texto a formato ISO (YYYY-MM-DD)
        function parseFecha(fechaTexto) {
            // Formato: "24 Oct 2023" -> "2023-10-24"
            const meses = {
                'Ene': '01', 'Feb': '02', 'Mar': '03', 'Abr': '04',
                'May': '05', 'Jun': '06', 'Jul': '07', 'Ago': '08',
                'Sep': '09', 'Oct': '10', 'Nov': '11', 'Dic': '12'
            };
            const partes = fechaTexto.trim().split(' ');
            if (partes.length >= 3) {
                const dia = partes[0].padStart(2, '0');
                const mes = meses[partes[1]] || '01';
                const año = partes[2];
                return `${año}-${mes}-${dia}`;
            }
            return null;
        }

        // Función para aplicar todos los filtros
        function applyFilters() {
            const rows = getTableRows();
            const query = filtros.buscador.toLowerCase().trim();

            rows.forEach(row => {
                const fecha = row.getAttribute('data-fecha');
                const tipo = row.getAttribute('data-tipo');
                const estado = row.getAttribute('data-estado');
                const esAlerta = row.getAttribute('data-alerta') === 'true';
                
                let mostrar = true;

                // Filtro de fecha desde
                if (filtros.fechaDesde && fecha) {
                    if (fecha < filtros.fechaDesde) {
                        mostrar = false;
                    }
                }

                // Filtro de fecha hasta
                if (filtros.fechaHasta && fecha && mostrar) {
                    if (fecha > filtros.fechaHasta) {
                        mostrar = false;
                    }
                }

                // Filtro de tipo
                if (filtros.tipo && mostrar) {
                    if (tipo !== filtros.tipo) {
                        mostrar = false;
                    }
                }

                // Filtro de estado
                if (filtros.estado && mostrar) {
                    if (estado !== filtros.estado) {
                        mostrar = false;
                    }
                }

                // Filtro solo alertas
                if (filtros.soloAlertas && mostrar) {
                    if (!esAlerta) {
                        mostrar = false;
                    }
                }

                // Filtro de búsqueda
                if (query && mostrar) {
                    const textoCompleto = row.textContent.toLowerCase();
                    if (!textoCompleto.includes(query)) {
                        mostrar = false;
                    }
                }

                row.style.display = mostrar ? '' : 'none';
            });
        }

        // Event listeners para los filtros
        if (filtroFechaDesde) {
            filtroFechaDesde.addEventListener('change', (e) => {
                filtros.fechaDesde = e.target.value || null;
                applyFilters();
            });
        }

        if (filtroFechaHasta) {
            filtroFechaHasta.addEventListener('change', (e) => {
                filtros.fechaHasta = e.target.value || null;
                applyFilters();
            });
        }

        if (filtroTipo) {
            filtroTipo.addEventListener('change', (e) => {
                filtros.tipo = e.target.value || null;
                applyFilters();
            });
        }

        if (filtroEstado) {
            filtroEstado.addEventListener('change', (e) => {
                filtros.estado = e.target.value || null;
                applyFilters();
            });
        }

        if (btnSoloAlertas) {
            let alertasActivo = false;
            btnSoloAlertas.addEventListener('click', (e) => {
                e.preventDefault();
                alertasActivo = !alertasActivo;
                filtros.soloAlertas = alertasActivo;
                
                // Actualizar el estilo del botón
                if (alertasActivo) {
                    btnSoloAlertas.classList.add('bg-red-500/30', 'dark:bg-red-500/40');
                    btnSoloAlertas.classList.remove('bg-red-500/10', 'dark:bg-red-500/20');
                } else {
                    btnSoloAlertas.classList.remove('bg-red-500/30', 'dark:bg-red-500/40');
                    btnSoloAlertas.classList.add('bg-red-500/10', 'dark:bg-red-500/20');
                }
                
                applyFilters();
            });
        }

        // Event listeners para búsqueda
        if (tableSearchInput) {
            tableSearchInput.addEventListener('input', (e) => {
                filtros.buscador = e.target.value;
                applyFilters();
            });
        }

        // Botón actualizar (limpiar filtros)
        if (btnActualizar) {
            btnActualizar.addEventListener('click', () => {
                filtros = {
                    fechaDesde: null,
                    fechaHasta: null,
                    tipo: null,
                    estado: null,
                    soloAlertas: false,
                    buscador: ''
                };
                
                if (filtroFechaDesde) filtroFechaDesde.value = '';
                if (filtroFechaHasta) filtroFechaHasta.value = '';
                if (filtroTipo) filtroTipo.value = '';
                if (filtroEstado) filtroEstado.value = '';
                if (tableSearchInput) tableSearchInput.value = '';
                
                if (btnSoloAlertas) {
                    btnSoloAlertas.classList.remove('bg-red-500/30', 'dark:bg-red-500/40');
                    btnSoloAlertas.classList.add('bg-red-500/10', 'dark:bg-red-500/20');
                }
                
                applyFilters();
            });
        }

        // Botón exportar CSV
        if (btnExportar) {
            btnExportar.addEventListener('click', function() {
                const rows = getTableRows();
                const filasVisibles = rows.filter(row => row.style.display !== 'none');
                
                let csv = [["Fecha", "Actor", "Accion", "Recurso", "Estado"].join(',')];
                
                filasVisibles.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    const fecha = cells[0]?.innerText || '';
                    const actor = cells[1]?.innerText || '';
                    const accion = cells[2]?.innerText || '';
                    const recurso = cells[3]?.innerText || '';
                    const estado = cells[4]?.innerText || '';
                    
                    csv.push([fecha, actor, accion, recurso, estado].map(v => '"' + v.replace(/"/g, '""') + '"').join(','));
                });
                
                const blob = new Blob([csv.join('\r\n')], { type: 'text/csv' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'auditoria.csv';
                link.click();
            });
        }
    }
})();
