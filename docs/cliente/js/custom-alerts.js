/**
 * Sistema de Alertas Personalizadas
 * Reemplaza las alertas predeterminadas del navegador con modales personalizados
 * que coinciden con el diseño de la aplicación.
 */

// Colores y estilos del tema
const theme = {
    primary: '#2463eb',
    backgroundDark: '#111318',
    surfaceDark: '#1c1f27',
    cardDark: '#282d39',
    textSecondary: '#9da6b9',
    success: '#22c55e',
    warning: '#eab308',
    danger: '#ef4444',
    border: '#282d39'
};

// Crear contenedor de modales si no existe
function createModalContainer() {
    if (document.getElementById('custom-modal-container')) return;
    
    const container = document.createElement('div');
    container.id = 'custom-modal-container';
    container.className = 'fixed inset-0 z-[9999] pointer-events-none';
    document.body.appendChild(container);
    
    // Estilos CSS para los modales
    const style = document.createElement('style');
    style.textContent = `
        .custom-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(4px);
            z-index: 9998;
            animation: fadeIn 0.2s ease-out;
            pointer-events: all;
        }
        
        .custom-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: ${theme.surfaceDark};
            border: 1px solid ${theme.border};
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
            max-width: 90%;
            width: 420px;
            max-height: 90vh;
            overflow: hidden;
            z-index: 9999;
            animation: modalSlideIn 0.3s ease-out;
            pointer-events: all;
        }
        
        .custom-modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid ${theme.border};
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        
        .custom-modal-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
        }
        
        .custom-modal-icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .custom-modal-close {
            background: none;
            border: none;
            color: ${theme.textSecondary};
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 0.5rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .custom-modal-close:hover {
            background: ${theme.cardDark};
            color: #ffffff;
        }
        
        .custom-modal-body {
            padding: 1.5rem;
            color: ${theme.textSecondary};
            line-height: 1.6;
            white-space: pre-line;
            max-height: 50vh;
            overflow-y: auto;
        }
        
        .custom-modal-footer {
            padding: 1.5rem;
            border-top: 1px solid ${theme.border};
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
        }
        
        .custom-modal-button {
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .custom-modal-button-primary {
            background: ${theme.primary};
            color: #ffffff;
        }
        
        .custom-modal-button-primary:hover {
            background: #1e4ed8;
        }
        
        .custom-modal-button-secondary {
            background: ${theme.cardDark};
            color: #ffffff;
            border: 1px solid ${theme.border};
        }
        
        .custom-modal-button-secondary:hover {
            background: #323846;
        }
        
        .custom-modal-button-danger {
            background: ${theme.danger};
            color: #ffffff;
        }
        
        .custom-modal-button-danger:hover {
            background: #dc2626;
        }
        
        .custom-toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: ${theme.surfaceDark};
            border: 1px solid ${theme.border};
            border-radius: 0.75rem;
            padding: 1rem 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
            max-width: 400px;
            z-index: 10000;
            animation: toastSlideIn 0.3s ease-out;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .custom-toast-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        
        .custom-toast-message {
            color: #ffffff;
            font-size: 0.875rem;
            line-height: 1.5;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translate(-50%, -45%);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
        }
        
        @keyframes toastSlideIn {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes toastSlideOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(100%);
            }
        }
        
        .custom-toast-removing {
            animation: toastSlideOut 0.3s ease-out forwards;
        }
        
        @media (max-width: 640px) {
            .custom-modal {
                width: calc(100% - 2rem);
                max-width: none;
            }
            
            .custom-toast {
                right: 1rem;
                left: 1rem;
                max-width: none;
            }
        }
    `;
    document.head.appendChild(style);
}

// Función para mostrar alerta personalizada
function customAlert(message, title = 'Aviso', icon = 'info') {
    return new Promise((resolve) => {
        createModalContainer();
        
        const overlay = document.createElement('div');
        overlay.className = 'custom-modal-overlay';
        
        const modal = document.createElement('div');
        modal.className = 'custom-modal';
        
        const icons = {
            info: '<span class="material-symbols-outlined" style="color: ' + theme.primary + ';">info</span>',
            success: '<span class="material-symbols-outlined" style="color: ' + theme.success + ';">check_circle</span>',
            warning: '<span class="material-symbols-outlined" style="color: ' + theme.warning + ';">warning</span>',
            error: '<span class="material-symbols-outlined" style="color: ' + theme.danger + ';">error</span>'
        };
        
        modal.innerHTML = `
            <div class="custom-modal-header">
                <div class="custom-modal-title">
                    <div class="custom-modal-icon">${icons[icon] || icons.info}</div>
                    <span>${title}</span>
                </div>
                <button class="custom-modal-close" aria-label="Cerrar">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="custom-modal-body">
                ${message}
            </div>
            <div class="custom-modal-footer">
                <button class="custom-modal-button custom-modal-button-primary" data-action="ok">
                    Aceptar
                </button>
            </div>
        `;
        
        const closeModal = () => {
            overlay.style.animation = 'fadeIn 0.2s ease-out reverse';
            modal.style.animation = 'modalSlideIn 0.3s ease-out reverse';
            setTimeout(() => {
                overlay.remove();
                modal.remove();
                resolve(true);
            }, 200);
        };
        
        modal.querySelector('[data-action="ok"]').addEventListener('click', closeModal);
        modal.querySelector('.custom-modal-close').addEventListener('click', closeModal);
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closeModal();
        });
        
        document.getElementById('custom-modal-container').appendChild(overlay);
        document.getElementById('custom-modal-container').appendChild(modal);
    });
}

// Función para mostrar confirmación personalizada
function customConfirm(message, title = 'Confirmar', icon = 'help') {
    return new Promise((resolve) => {
        createModalContainer();
        
        const overlay = document.createElement('div');
        overlay.className = 'custom-modal-overlay';
        
        const modal = document.createElement('div');
        modal.className = 'custom-modal';
        
        const icons = {
            help: '<span class="material-symbols-outlined" style="color: ' + theme.primary + ';">help</span>',
            warning: '<span class="material-symbols-outlined" style="color: ' + theme.warning + ';">warning</span>',
            danger: '<span class="material-symbols-outlined" style="color: ' + theme.danger + ';">error</span>'
        };
        
        modal.innerHTML = `
            <div class="custom-modal-header">
                <div class="custom-modal-title">
                    <div class="custom-modal-icon">${icons[icon] || icons.help}</div>
                    <span>${title}</span>
                </div>
                <button class="custom-modal-close" aria-label="Cerrar">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="custom-modal-body">
                ${message}
            </div>
            <div class="custom-modal-footer">
                <button class="custom-modal-button custom-modal-button-secondary" data-action="cancel">
                    Cancelar
                </button>
                <button class="custom-modal-button custom-modal-button-primary" data-action="confirm">
                    Confirmar
                </button>
            </div>
        `;
        
        const closeModal = (result) => {
            overlay.style.animation = 'fadeIn 0.2s ease-out reverse';
            modal.style.animation = 'modalSlideIn 0.3s ease-out reverse';
            setTimeout(() => {
                overlay.remove();
                modal.remove();
                resolve(result);
            }, 200);
        };
        
        modal.querySelector('[data-action="confirm"]').addEventListener('click', () => closeModal(true));
        modal.querySelector('[data-action="cancel"]').addEventListener('click', () => closeModal(false));
        modal.querySelector('.custom-modal-close').addEventListener('click', () => closeModal(false));
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closeModal(false);
        });
        
        document.getElementById('custom-modal-container').appendChild(overlay);
        document.getElementById('custom-modal-container').appendChild(modal);
    });
}

// Función para mostrar toast/notificación
function customToast(message, type = 'info', duration = 3000) {
    createModalContainer();
    
    const toast = document.createElement('div');
    toast.className = 'custom-toast';
    
    const icons = {
        info: '<span class="material-symbols-outlined" style="color: ' + theme.primary + ';">info</span>',
        success: '<span class="material-symbols-outlined" style="color: ' + theme.success + ';">check_circle</span>',
        warning: '<span class="material-symbols-outlined" style="color: ' + theme.warning + ';">warning</span>',
        error: '<span class="material-symbols-outlined" style="color: ' + theme.danger + ';">error</span>'
    };
    
    toast.innerHTML = `
        <div class="custom-toast-icon">${icons[type] || icons.info}</div>
        <div class="custom-toast-message">${message}</div>
    `;
    
    document.getElementById('custom-modal-container').appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('custom-toast-removing');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, duration);
}

// Reemplazar funciones globales (opcional, para compatibilidad)
window.customAlert = customAlert;
window.customConfirm = customConfirm;
window.customToast = customToast;
