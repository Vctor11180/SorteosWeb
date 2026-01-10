// Referencias
const searchInput = document.getElementById('searchInput');
const topSearchInput = document.getElementById('topSearchInput');
const statusFilter = document.getElementById('statusFilter');
const tableBody = document.querySelector('#sorteosTable tbody');
const modal = document.getElementById('modal-backdrop');
const openModalBtn = document.getElementById('openModalBtn');
const cancelModalBtn = document.getElementById('cancelModalBtn');
const closeModalBg = document.getElementById('closeModalBg');
const createForm = document.getElementById('createSorteoForm');
const resultsCount = document.getElementById('resultsCount');

// --- FILTRADO ---
function filterTable() {
    const query = searchInput.value.toLowerCase();
    const topQuery = topSearchInput.value.toLowerCase();
    const status = statusFilter.value;
    const rows = tableBody.querySelectorAll('tr');
    let visibleCount = 0;

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        const rowStatus = row.getAttribute('data-status');
        
        const matchesSearch = text.includes(query) && text.includes(topQuery);
        const matchesStatus = (status === 'todos' || rowStatus === status);

        if (matchesSearch && matchesStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    resultsCount.innerText = `Mostrando ${visibleCount} de ${rows.length} resultados`;
}

searchInput.addEventListener('input', filterTable);
topSearchInput.addEventListener('input', filterTable);
statusFilter.addEventListener('change', filterTable);

// --- MODAL ---
const toggleModal = (show) => {
    modal.classList.toggle('hidden', !show);
    if (!show) createForm.reset();
};

openModalBtn.addEventListener('click', () => toggleModal(true));
cancelModalBtn.addEventListener('click', () => toggleModal(false));
closeModalBg.addEventListener('click', () => toggleModal(false));

createForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const name = document.getElementById('sorteo-name').value;
    alert(`¡Sorteo "${name}" guardado exitosamente!`);
    toggleModal(false);
});

// --- ACCIONES DE FILA ---
tableBody.addEventListener('click', (e) => {
    // Eliminar fila
    const deleteBtn = e.target.closest('.btn-delete');
    if (deleteBtn) {
        if (confirm('¿Estás seguro de que deseas eliminar este sorteo?')) {
            deleteBtn.closest('tr').remove();
            filterTable();
        }
    }
});

