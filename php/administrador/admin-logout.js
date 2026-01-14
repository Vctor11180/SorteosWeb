/**
 * Script compartido para manejar el logout de administradores
 * Incluir este script DESPUÉS de custom-alerts.js en todas las páginas de administrador
 */

// Función para manejar el logout del administrador
function handleLogoutAdmin() {
    // Usar customConfirm para mantener consistencia con el resto de la aplicación
    if (typeof customConfirm === 'function') {
        customConfirm('¿Estás seguro de que deseas cerrar sesión?', 'Cerrar Sesión', 'warning').then(confirmed => {
            if (confirmed) {
                // Redirigir al logout.php que destruye la sesión del servidor
                window.location.href = 'logout.php';
            }
        });
    } else {
        // Si customConfirm no está disponible, esperar a que se cargue
        setTimeout(() => {
            if (typeof customConfirm === 'function') {
                handleLogoutAdmin();
            } else {
                // Fallback si customConfirm no se carga
                if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                    window.location.href = 'logout.php';
                }
            }
        }, 200);
    }
}

// Agregar funcionalidad a todos los botones de logout de administrador
document.addEventListener('DOMContentLoaded', function() {
    const logoutBtns = document.querySelectorAll('#logout-btn-admin, button[onclick*="handleLogoutAdmin"]');
    
    logoutBtns.forEach(btn => {
        // Solo agregar listener si no tiene onclick ya definido
        if (!btn.hasAttribute('onclick') || btn.getAttribute('onclick').indexOf('handleLogoutAdmin') === -1) {
            btn.addEventListener('click', handleLogoutAdmin);
        }
    });
});
