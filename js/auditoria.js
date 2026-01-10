(function() {
    // 1. Capturamos los inputs de búsqueda
    const tableSearchInput = document.querySelector('input[placeholder*="Buscar por ID de sorteo"]');
    const headerSearch = document.querySelector('input[placeholder*="Buscar sorteo, usuario"]');

    function getTableRows() {
        const rows = Array.from(document.querySelectorAll('table tbody tr'));
        return rows.map(row => {
            const cells = row.querySelectorAll('td');
            return {
                tr: row,
                fecha: cells[0]?.innerText || '',
                actor: cells[1]?.innerText || '',
                accion: cells[2]?.innerText || '',
                recurso: cells[3]?.innerText || '',
                estado: cells[4]?.innerText || ''
            };
        });
    }

    // Estado de filtros (Añadimos 'buscador')
    let filtros = {fecha:null, tipo:null, estado:null, buscador: ''};

    // 2. Función de filtrado mejorada (Línea clave)
    function applyFilters() {
        const rows = getTableRows();
        const query = filtros.buscador.toLowerCase();

        rows.forEach(obj => {
            let matchesButtons = true;
            let matchesSearch = true;

            // Filtros de botones
            if (filtros.fecha) matchesButtons = matchesButtons && obj.fecha.toLowerCase().includes(filtros.fecha.toLowerCase());
            if (filtros.tipo) matchesButtons = matchesButtons && obj.accion.toLowerCase().includes(filtros.tipo.toLowerCase());
            if (filtros.estado) matchesButtons = matchesButtons && obj.estado.toLowerCase().includes(filtros.estado.toLowerCase());

            // Filtro del buscador
            if (query) {
                matchesSearch = obj.fecha.toLowerCase().includes(query) || 
                                obj.actor.toLowerCase().includes(query) || 
                                obj.accion.toLowerCase().includes(query) || 
                                obj.recurso.toLowerCase().includes(query) || 
                                obj.estado.toLowerCase().includes(query);
            }

            obj.tr.style.display = (matchesButtons && matchesSearch) ? '' : 'none';
        });
    }

    // 3. Listeners para los inputs
    [tableSearchInput, headerSearch].forEach(input => {
        if (input) {
            input.addEventListener('input', (e) => {
                filtros.buscador = e.target.value;
                applyFilters();
            });
        }
    });

    // --- Resto de tus funciones originales (Actualizar, Exportar, etc.) ---
    const btnFecha = Array.from(document.querySelectorAll('button')).find(b=>b.innerText.includes('Últimos 7 días'));
    const btnTipo = Array.from(document.querySelectorAll('button')).find(b=>b.innerText.includes('Tipo: Todos'));
    const btnEstado = Array.from(document.querySelectorAll('button')).find(b=>b.innerText.includes('Estado: Todos'));
    const btnActualizar = Array.from(document.querySelectorAll('button')).find(b=>b.innerText.includes('Actualizar'));
    const btnExportar = Array.from(document.querySelectorAll('button')).find(b=>b.innerText.includes('Exportar CSV'));

    if (btnFecha) btnFecha.addEventListener('click', () => { filtros.fecha = prompt('Fecha:') || null; applyFilters(); });
    if (btnTipo) btnTipo.addEventListener('click', () => { filtros.tipo = prompt('Acción:') || null; applyFilters(); });
    if (btnEstado) btnEstado.addEventListener('click', () => { filtros.estado = prompt('Estado:') || null; applyFilters(); });
    
    if (btnActualizar) btnActualizar.addEventListener('click', () => {
        filtros = {fecha:null, tipo:null, estado:null, buscador: ''};
        if(tableSearchInput) tableSearchInput.value = '';
        if(headerSearch) headerSearch.value = '';
        applyFilters();
    });

    if (btnExportar) btnExportar.addEventListener('click', function() {
        let csv = [["Fecha", "Actor", "Accion", "Recurso", "Estado"].join(',')];
        getTableRows().forEach(obj => {
            if(obj.tr.style.display !== 'none') csv.push([obj.fecha, obj.actor, obj.accion, obj.recurso, obj.estado].map(v => '"'+v.replace(/"/g,'""')+'"').join(','));
        });
        const blob = new Blob([csv.join('\r\n')], {type: 'text/csv'});
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'auditoria.csv';
        link.click();
    });
})();

