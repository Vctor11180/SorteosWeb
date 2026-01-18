/**
 * Ajustes de Perfil Cliente
 * Maneja la funcionalidad de subir y eliminar foto de perfil
 * y otras funciones relacionadas con el perfil del cliente
 */

(function() {
    'use strict';

    // Variable para almacenar la URL de la imagen por defecto
    const DEFAULT_AVATAR = 'https://via.placeholder.com/150/282d39/9da6b9?text=Usuario';
    
    // Variable para almacenar la URL original del avatar
    let originalAvatarUrl = null;

    /**
     * Carga los datos del perfil guardados
     * NOTA: Ya no sobrescribe los valores de PHP, solo usa localStorage como fallback
     * si los campos están vacíos (no deberían estarlo porque vienen de PHP)
     */
    function loadSavedProfileData() {
        try {
            // NO cargar desde localStorage si los campos ya tienen valores de PHP
            // Los valores de PHP tienen prioridad absoluta
            
            const nombreInput = document.getElementById('input-nombre');
            const emailInput = document.getElementById('input-email');
            const telefonoInput = document.getElementById('input-telefono');
            const direccionInput = document.getElementById('input-direccion');
            
            // Solo usar localStorage como fallback si los campos están vacíos
            // (esto no debería pasar porque PHP los llena, pero por seguridad)
            
            // Verificar si los campos tienen valores originales de PHP
            const tieneValorNombre = nombreInput && (nombreInput.value || nombreInput.getAttribute('data-original-value'));
            const tieneValorEmail = emailInput && (emailInput.value || emailInput.getAttribute('data-original-value'));
            const tieneValorTelefono = telefonoInput && (telefonoInput.value || telefonoInput.getAttribute('data-original-value'));
            
            // Si los campos YA tienen valores (vienen de PHP), NO sobrescribirlos
            if (tieneValorNombre && tieneValorEmail) {
                console.log('AjustesPefilCliente - Los campos ya tienen valores de PHP, no se sobrescribirán con localStorage');
                return; // Salir temprano, los datos de PHP tienen prioridad
            }
            
            // Solo como fallback (no debería llegar aquí normalmente):
            const profileData = localStorage.getItem('profileData');
            if (profileData && !tieneValorNombre && !tieneValorEmail) {
                const data = JSON.parse(profileData);
                
                // Solo actualizar si el campo está vacío
                if (data.nombre && nombreInput && !nombreInput.value) {
                    nombreInput.value = data.nombre;
                }
                
                if (data.email && emailInput && !emailInput.value) {
                    emailInput.value = data.email;
                }
                
                if (data.telefono && telefonoInput && !telefonoInput.value) {
                    telefonoInput.value = data.telefono;
                }
                
                if (data.direccion && direccionInput && !direccionInput.value) {
                    direccionInput.value = data.direccion;
                }
            }
        } catch (error) {
            console.error('Error al cargar datos del perfil:', error);
        }
    }
    
    /**
     * Inicializa todas las funcionalidades del perfil
     */
    function init() {
        // Guardar la URL original del avatar al cargar la página
        const avatarElement = document.getElementById('avatar-perfil');
        if (avatarElement) {
            const style = avatarElement.getAttribute('style') || '';
            const match = style.match(/url\(["']?([^"')]+)["']?\)/);
            if (match && match[1]) {
                originalAvatarUrl = match[1];
            }
        }

        // Cargar datos guardados del perfil
        loadSavedProfileData();
        
        // Inicializar funcionalidad de avatar
        initAvatarUpload();
        initAvatarDelete();
        
        // Inicializar otras funcionalidades
        initFormValidations();
        initPasswordValidations();
        initSavePersonalInfo();
        initChangePassword();
        initPasswordToggle();
        loadNotificationPreferences();
        initNotificationSwitches();
        initSavePreferences();
        loadPaymentMethods();
        initPaymentMethodForm();
        initSidebarNavigation();
        initHistorialSorteos();
    }

    /**
     * Inicializa la funcionalidad de subir avatar
     */
    function initAvatarUpload() {
        const uploadBtn = document.getElementById('btn-subir-avatar');
        if (!uploadBtn) {
            console.error('No se encontró el botón de subir avatar');
            return;
        }

        // Crear input de archivo oculto
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = 'image/*';
        fileInput.style.display = 'none';
        fileInput.setAttribute('aria-label', 'Seleccionar imagen de perfil');
        document.body.appendChild(fileInput);

        // Event listener para el botón de subir
        uploadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            fileInput.click();
        });

        // Event listener para cuando se selecciona un archivo
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) {
                return;
            }

            // Validar tipo de archivo
            if (!file.type.startsWith('image/')) {
                if (typeof customAlert === 'function') {
                    customAlert('Por favor selecciona un archivo de imagen válido (JPG, PNG, GIF, etc.)', 'Tipo de Archivo Inválido', 'error');
                } else {
                    alert('Por favor selecciona un archivo de imagen válido');
                }
                fileInput.value = '';
                return;
            }

            // Validar tamaño del archivo (máximo 5MB)
            const maxSize = 5 * 1024 * 1024; // 5MB en bytes
            if (file.size > maxSize) {
                if (typeof customAlert === 'function') {
                    customAlert('La imagen no puede ser mayor a 5MB. Por favor selecciona una imagen más pequeña.', 'Archivo Demasiado Grande', 'error');
                } else {
                    alert('La imagen no puede ser mayor a 5MB');
                }
                fileInput.value = '';
                return;
            }

            // Mostrar preview inmediato (mientras se sube)
            const reader = new FileReader();
            reader.onload = function(event) {
                const previewUrl = event.target.result;
                updateAvatar(previewUrl); // Preview inmediato
            };
            reader.readAsDataURL(file);
            
            // SUBIR ARCHIVO AL SERVIDOR (IMPLEMENTACIÓN REAL)
            uploadAvatarToServer(file);
            
            // Limpiar el input para permitir seleccionar el mismo archivo de nuevo
            fileInput.value = '';
        });
    }

    /**
     * Inicializa la funcionalidad de eliminar avatar
     */
    function initAvatarDelete() {
        const deleteBtn = document.getElementById('btn-eliminar-avatar');
        if (!deleteBtn) {
            console.error('No se encontró el botón de eliminar avatar');
            return;
        }

        deleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Mostrar confirmación
            if (typeof customConfirm === 'function') {
                customConfirm(
                    '¿Estás seguro de que deseas eliminar tu foto de perfil? Se restaurará la imagen por defecto.',
                    'Eliminar Foto de Perfil',
                    'warning'
                ).then(function(confirmed) {
                    if (confirmed) {
                        deleteAvatar();
                    }
                });
            } else {
                // Fallback si customConfirm no está disponible
                if (confirm('¿Estás seguro de que deseas eliminar tu foto de perfil?')) {
                    deleteAvatar();
                }
            }
        });
    }

    /**
     * Actualiza el avatar en todos los lugares donde se muestra
     */
    function updateAvatar(imageUrl) {
        // Actualizar el avatar principal en la página de perfil
        const avatarPerfil = document.getElementById('avatar-perfil');
        if (avatarPerfil) {
            avatarPerfil.style.backgroundImage = `url('${imageUrl}')`;
        }

        // Actualizar todos los elementos con data-alt que contengan "avatar"
        const avatarElements = document.querySelectorAll('[data-alt*="avatar"], [data-alt*="User"]');
        avatarElements.forEach(function(element) {
            element.style.backgroundImage = `url('${imageUrl}')`;
        });

        // Actualizar avatares del sidebar si existen
        const sidebarAvatar = document.getElementById('sidebar-user-avatar');
        if (sidebarAvatar) {
            sidebarAvatar.style.backgroundImage = `url('${imageUrl}')`;
        }

        const mobileAvatar = document.getElementById('mobile-user-avatar');
        if (mobileAvatar) {
            mobileAvatar.style.backgroundImage = `url('${imageUrl}')`;
        }
    }

    /**
     * Sube el avatar al servidor
     */
    async function uploadAvatarToServer(file) {
        // Variables para manejo del botón (necesario en finally)
        const uploadBtn = document.getElementById('btn-subir-avatar');
        let originalBtnText = '';
        if (uploadBtn) {
            originalBtnText = uploadBtn.textContent || 'Subir Foto';
        }
        
        try {
            // Deshabilitar botón mientras se sube
            if (uploadBtn) {
                uploadBtn.disabled = true;
                uploadBtn.textContent = 'Subiendo...';
            }
            
            // Preparar FormData para subir el archivo
            const formData = new FormData();
            formData.append('avatar', file);
            
            // Subir archivo a la API
            const response = await fetch('api_upload.php?action=upload_avatar', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status} ${response.statusText}`);
            }
            
            const text = await response.text();
            let result;
            
            try {
                result = JSON.parse(text);
            } catch (parseError) {
                console.error('Error al parsear JSON del upload:', parseError);
                console.error('Respuesta recibida:', text.substring(0, 200));
                throw new Error('Error en la respuesta del servidor (formato inválido)');
            }
            
            if (!result.success || !result.data) {
                throw new Error(result.error || 'Error al subir el avatar');
            }
            
            const avatarUrl = result.data.file_path || result.data.file_url;
            
            // Actualizar avatar_url en el perfil (llamada adicional para actualizar BD)
            // Aunque api_upload.php ya actualiza avatar_url, aseguramos consistencia
            await updateProfileAvatarUrl(avatarUrl);
            
            // Actualizar avatar en el DOM con la URL del servidor
            updateAvatar(avatarUrl);
            
            // Guardar en localStorage
            try {
                const clientData = JSON.parse(localStorage.getItem('clientData') || '{}');
                clientData.fotoPerfil = avatarUrl;
                localStorage.setItem('clientData', JSON.stringify(clientData));
                
                // Actualizar también en el layout si existe
                if (window.ClientLayout) {
                    window.ClientLayout.updateClientData({ fotoPerfil: avatarUrl });
                }
            } catch (error) {
                console.error('Error al guardar en localStorage:', error);
            }
            
            // Mostrar mensaje de éxito
            if (typeof customToast === 'function') {
                customToast('Foto de perfil actualizada exitosamente', 'success', 3000);
            } else if (typeof customAlert === 'function') {
                customAlert('Foto de perfil actualizada exitosamente', 'Éxito', 'success');
            }
            
        } catch (error) {
            console.error('Error al subir avatar:', error);
            
            // Mostrar mensaje de error
            if (typeof customAlert === 'function') {
                customAlert('Error al subir la foto de perfil: ' + error.message + '\n\nEl cambio solo se aplicó localmente.', 'Error al Subir', 'error');
            } else {
                alert('Error al subir la foto de perfil: ' + error.message);
            }
            
            // Restaurar avatar anterior si falla
            // (podríamos obtener el avatar actual desde BD, pero por ahora dejamos el preview)
            
        } finally {
            // Restaurar botón
            const uploadBtn = document.getElementById('btn-subir-avatar');
            if (uploadBtn && originalBtnText) {
                uploadBtn.disabled = false;
                uploadBtn.textContent = originalBtnText;
            }
        }
    }
    
    /**
     * Actualiza solo el avatar_url en el perfil del usuario
     */
    async function updateProfileAvatarUrl(avatarUrl) {
        try {
            const response = await fetch('api_actualizar_perfil.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    nombre: document.querySelector('#input-nombre')?.value || '',
                    email: document.querySelector('#input-email')?.value || '',
                    telefono: document.querySelector('#input-telefono')?.value || '',
                    avatar_url: avatarUrl
                })
            });
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (!result.success) {
                console.warn('Advertencia: No se pudo actualizar avatar_url en el perfil:', result.message);
                // No fallar, el archivo ya se subió
            }
            
        } catch (error) {
            console.error('Error al actualizar avatar_url en perfil:', error);
            // No fallar, el archivo ya se subió
        }
    }

    /**
     * Elimina el avatar y restaura la imagen por defecto (IMPLEMENTACIÓN REAL)
     * NOTA: La confirmación ya se maneja en initAvatarDelete(), esta función solo ejecuta la eliminación
     */
    async function deleteAvatar() {
        try {
            
            // Actualizar en el servidor (usar URL vacía o null para restaurar por defecto)
            const defaultAvatarUrl = 'https://lh3.googleusercontent.com/aida-public/AB6AXuAscTJ1Xcq7edw4JqzzGbgOvjdyQ9_nDg7kkxtlCQw51-EJsv1RJyDd9OAZC89eniVl2ujzIik6wgxd5FTvho_ak6ccsWrWelinVwXj6yQUdpPUXYUTJN0pSvhRh-smWf81cMQz40x4U3setrSFDsyX4KkfxOsHc6PnTND68lGw6JkA9B0ag_4fNu5s0Z9OMbq83llAZUv3xuo3s6VI1no110ozE88mRALnX-rhgavHoJxmYpvBcUxV7BtrJr_9Q0BlgvZQL2BXCFg';
            
            // Actualizar perfil con URL por defecto
            const nombre = document.querySelector('#input-nombre')?.value || '';
            const email = document.querySelector('#input-email')?.value || '';
            const telefono = document.querySelector('#input-telefono')?.value || '';
            
            const response = await fetch('api_actualizar_perfil.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    nombre: nombre,
                    email: email,
                    telefono: telefono,
                    avatar_url: defaultAvatarUrl
                })
            });
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Error al eliminar el avatar');
            }
            
            // Usar la imagen por defecto en el DOM
            updateAvatar(defaultAvatarUrl);

            // Actualizar en localStorage
            try {
                const clientData = JSON.parse(localStorage.getItem('clientData') || '{}');
                clientData.fotoPerfil = defaultAvatarUrl;
                localStorage.setItem('clientData', JSON.stringify(clientData));
                
                // Actualizar también en el layout si existe
                if (window.ClientLayout) {
                    window.ClientLayout.updateClientData({ fotoPerfil: defaultAvatarUrl });
                }
            } catch (error) {
                console.error('Error al actualizar localStorage:', error);
            }

            // Mostrar mensaje de éxito
            if (typeof customToast === 'function') {
                customToast('Foto de perfil eliminada exitosamente', 'success', 3000);
            } else if (typeof customAlert === 'function') {
                customAlert('Foto de perfil eliminada exitosamente', 'Éxito', 'success');
            }
            
        } catch (error) {
            console.error('Error al eliminar avatar:', error);
            
            if (typeof customAlert === 'function') {
                customAlert('Error al eliminar la foto de perfil: ' + error.message, 'Error', 'error');
            } else {
                alert('Error al eliminar la foto de perfil: ' + error.message);
            }
        }
    }

    /**
     * Funciones de validación
     */
    
    /**
     * Valida el formato de correo electrónico
     */
    function validateEmail(email) {
        if (!email) return { valid: false, message: 'El correo electrónico es requerido' };
        
        // Expresión regular más robusta para validar email
        const emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
        
        if (!emailRegex.test(email)) {
            return { valid: false, message: 'El formato del correo electrónico no es válido' };
        }
        
        // Validar longitud
        if (email.length > 254) {
            return { valid: false, message: 'El correo electrónico es demasiado largo' };
        }
        
        // Validar que no tenga espacios
        if (email.includes(' ')) {
            return { valid: false, message: 'El correo electrónico no puede contener espacios' };
        }
        
        return { valid: true, message: '' };
    }
    
    /**
     * Verifica si un correo electrónico existe (simulación con API pública)
     */
    async function verifyEmailExists(email) {
        try {
            // Usar una API pública para verificar el correo (opcional)
            // Nota: En producción, esto debería hacerse en el backend
            // Por ahora, simulamos una verificación básica
            
            // Simulación: verificar dominios comunes inválidos
            const invalidDomains = ['test.com', 'example.com', 'invalid.com'];
            const domain = email.split('@')[1];
            
            if (invalidDomains.includes(domain)) {
                return { exists: false, message: 'El dominio del correo no es válido' };
            }
            
            // Simular verificación asíncrona (en producción sería una llamada real a API)
            await new Promise(resolve => setTimeout(resolve, 500));
            
            return { exists: true, message: '' };
        } catch (error) {
            console.error('Error al verificar correo:', error);
            // Si falla la verificación, permitir continuar
            return { exists: true, message: '' };
        }
    }
    
    /**
     * Valida el nombre completo
     */
    function validateNombre(nombre) {
        if (!nombre || nombre.trim().length === 0) {
            return { valid: false, message: 'El nombre completo es requerido' };
        }
        
        if (nombre.trim().length < 3) {
            return { valid: false, message: 'El nombre debe tener al menos 3 caracteres' };
        }
        
        if (nombre.trim().length > 100) {
            return { valid: false, message: 'El nombre no puede exceder 100 caracteres' };
        }
        
        // Validar que contenga solo letras, espacios y algunos caracteres especiales
        const nombreRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s'-]+$/;
        if (!nombreRegex.test(nombre.trim())) {
            return { valid: false, message: 'El nombre solo puede contener letras, espacios y guiones' };
        }
        
        // Validar que tenga al menos un espacio (nombre y apellido)
        const parts = nombre.trim().split(/\s+/);
        if (parts.length < 2) {
            return { valid: false, message: 'Por favor ingresa nombre y apellido' };
        }
        
        return { valid: true, message: '' };
    }
    
    /**
     * Valida el teléfono (opcional)
     */
    function validateTelefono(telefono) {
        // El teléfono es opcional, pero si se proporciona debe cumplir requisitos
        if (telefono && telefono.trim().length > 0) {
            // Remover espacios, guiones y paréntesis para validación
            const cleanPhone = telefono.replace(/[\s\-\(\)]/g, '');
            
            // Validar formato internacional (debe empezar con +)
            if (!cleanPhone.startsWith('+')) {
                return { valid: false, message: 'El teléfono debe incluir código de país (ej: +34)' };
            }
            
            // Validar que después del + solo haya números
            const phoneRegex = /^\+[0-9]{8,15}$/;
            if (!phoneRegex.test(cleanPhone)) {
                return { valid: false, message: 'El formato del teléfono no es válido. Debe ser: +[código país][número]' };
            }
        }
        
        return { valid: true, message: '' };
    }
    
    /**
     * Valida la dirección (opcional - no se guarda en BD)
     */
    function validateDireccion(direccion) {
        // La dirección es opcional, pero si se proporciona debe cumplir requisitos
        if (direccion && direccion.trim().length > 0) {
            if (direccion.trim().length < 10) {
                return { valid: false, message: 'La dirección debe tener al menos 10 caracteres' };
            }
            
            if (direccion.trim().length > 200) {
                return { valid: false, message: 'La dirección no puede exceder 200 caracteres' };
            }
        }
        
        return { valid: true, message: '' };
    }
    
    /**
     * Muestra un error en un campo
     */
    function showFieldError(fieldId, errorId, message) {
        const field = document.getElementById(fieldId);
        const errorElement = document.getElementById(errorId);
        
        if (field) {
            field.classList.remove('border-[#282d39]');
            field.classList.add('border-red-500', 'border-2');
        }
        
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.remove('hidden');
        }
    }
    
    /**
     * Limpia el error de un campo
     */
    function clearFieldError(fieldId, errorId) {
        const field = document.getElementById(fieldId);
        const errorElement = document.getElementById(errorId);
        
        if (field) {
            field.classList.remove('border-red-500', 'border-2');
            field.classList.add('border-[#282d39]');
        }
        
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.classList.add('hidden');
        }
    }
    
    /**
     * Encripta datos sensibles usando una función simple de hash
     * Nota: En producción, esto debería hacerse en el backend con algoritmos seguros
     */
    function encryptData(data) {
        // Función simple de hash para demostración
        // En producción, usar algoritmos seguros como bcrypt, AES, etc.
        let hash = 0;
        const str = JSON.stringify(data);
        
        for (let i = 0; i < str.length; i++) {
            const char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convertir a 32 bits
        }
        
        // Simular encriptación básica (en producción usar librerías de encriptación)
        return btoa(str + '|' + hash.toString()).replace(/[+/=]/g, function(match) {
            return { '+': '-', '/': '_', '=': '' }[match];
        });
    }
    
    /**
     * Encripta una contraseña usando hash simple
     * Nota: En producción usar bcrypt, argon2, o similar
     */
    function hashPassword(password) {
        // Función simple de hash para demostración
        // En producción, usar librerías como bcrypt.js o similar
        let hash = 0;
        const str = password;
        
        for (let i = 0; i < str.length; i++) {
            const char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convertir a 32 bits
        }
        
        // Simular hash más robusto
        const salt = 'sorteos_web_2024';
        const saltedPassword = salt + password + salt;
        let finalHash = 0;
        
        for (let i = 0; i < saltedPassword.length; i++) {
            const char = saltedPassword.charCodeAt(i);
            finalHash = ((finalHash << 5) - finalHash) + char;
            finalHash = finalHash & finalHash;
        }
        
        return btoa(finalHash.toString() + '|' + password.length).replace(/[+/=]/g, function(match) {
            return { '+': '-', '/': '_', '=': '' }[match];
        });
    }
    
    /**
     * Verifica si una contraseña coincide con la encriptada
     */
    function verifyPassword(password, hashedPassword) {
        // En producción, usar función de verificación de bcrypt
        const newHash = hashPassword(password);
        return newHash === hashedPassword;
    }
    
    /**
     * Valida la contraseña actual
     */
    function validatePasswordActual(password) {
        if (!password || password.trim().length === 0) {
            return { valid: false, message: 'La contraseña actual es requerida' };
        }
        
        if (password.length < 6) {
            return { valid: false, message: 'La contraseña actual debe tener al menos 6 caracteres' };
        }
        
        return { valid: true, message: '' };
    }
    
    /**
     * Valida la nueva contraseña y calcula su fortaleza
     */
    function validatePasswordNueva(password) {
        if (!password || password.trim().length === 0) {
            return { 
                valid: false, 
                message: 'La nueva contraseña es requerida',
                strength: 0,
                strengthText: ''
            };
        }
        
        if (password.length < 8) {
            return { 
                valid: false, 
                message: 'La contraseña debe tener al menos 8 caracteres',
                strength: 0,
                strengthText: 'Muy débil'
            };
        }
        
        if (password.length > 128) {
            return { 
                valid: false, 
                message: 'La contraseña no puede exceder 128 caracteres',
                strength: 0,
                strengthText: ''
            };
        }
        
        // Calcular fortaleza de la contraseña
        let strength = 0;
        let strengthText = '';
        
        // Longitud
        if (password.length >= 8) strength += 1;
        if (password.length >= 12) strength += 1;
        if (password.length >= 16) strength += 1;
        
        // Diferentes tipos de caracteres
        if (/[a-z]/.test(password)) strength += 1; // Minúsculas
        if (/[A-Z]/.test(password)) strength += 1; // Mayúsculas
        if (/[0-9]/.test(password)) strength += 1; // Números
        if (/[^a-zA-Z0-9]/.test(password)) strength += 1; // Caracteres especiales
        
        // Determinar texto de fortaleza
        if (strength <= 2) {
            strengthText = 'Débil';
        } else if (strength <= 4) {
            strengthText = 'Media';
        } else if (strength <= 6) {
            strengthText = 'Fuerte';
        } else {
            strengthText = 'Muy Fuerte';
        }
        
        // Validar requisitos mínimos
        const hasLower = /[a-z]/.test(password);
        const hasUpper = /[A-Z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        const hasSpecial = /[^a-zA-Z0-9]/.test(password);
        
        if (!hasLower || !hasUpper || !hasNumber || !hasSpecial) {
            return { 
                valid: false, 
                message: 'La contraseña debe contener mayúsculas, minúsculas, números y caracteres especiales',
                strength: strength,
                strengthText: strengthText
            };
        }
        
        // Validar que no sea una contraseña común
        const commonPasswords = ['password', '12345678', 'qwerty', 'abc123', 'password123'];
        const lowerPassword = password.toLowerCase();
        if (commonPasswords.some(common => lowerPassword.includes(common))) {
            return { 
                valid: false, 
                message: 'La contraseña es demasiado común. Por favor elige una más segura',
                strength: strength,
                strengthText: strengthText
            };
        }
        
        return { 
            valid: true, 
            message: '',
            strength: strength,
            strengthText: strengthText
        };
    }
    
    /**
     * Valida que las contraseñas coincidan
     */
    function validatePasswordConfirmar(nuevaPassword, confirmarPassword) {
        if (!confirmarPassword || confirmarPassword.trim().length === 0) {
            return { valid: false, message: 'Por favor confirma la nueva contraseña' };
        }
        
        if (nuevaPassword !== confirmarPassword) {
            return { valid: false, message: 'Las contraseñas no coinciden' };
        }
        
        return { valid: true, message: '' };
    }
    
    /**
     * Muestra la fortaleza de la contraseña
     */
    function showPasswordStrength(strength, strengthText) {
        const strengthContainer = document.getElementById('password-strength');
        const strengthBars = [
            document.getElementById('strength-bar-1'),
            document.getElementById('strength-bar-2'),
            document.getElementById('strength-bar-3'),
            document.getElementById('strength-bar-4')
        ];
        const strengthTextElement = document.getElementById('strength-text');
        
        if (!strengthContainer) return;
        
        // Resetear todas las barras
        strengthBars.forEach(function(bar) {
            if (bar) {
                bar.classList.remove('bg-red-500', 'bg-yellow-500', 'bg-green-500', 'bg-green-600');
                bar.classList.add('bg-gray-700');
            }
        });
        
        if (strength === 0) {
            strengthContainer.classList.add('hidden');
            return;
        }
        
        strengthContainer.classList.remove('hidden');
        
        // Calcular número de barras a mostrar y color
        let barsToShow = 0;
        let colorClass = '';
        
        if (strength <= 2) {
            barsToShow = 1;
            colorClass = 'bg-red-500';
        } else if (strength <= 4) {
            barsToShow = 2;
            colorClass = 'bg-yellow-500';
        } else if (strength <= 6) {
            barsToShow = 3;
            colorClass = 'bg-green-500';
        } else {
            barsToShow = 4;
            colorClass = 'bg-green-600';
        }
        
        // Mostrar barras
        for (let i = 0; i < barsToShow; i++) {
            if (strengthBars[i]) {
                strengthBars[i].classList.remove('bg-gray-700');
                strengthBars[i].classList.add(colorClass);
            }
        }
        
        // Actualizar texto
        if (strengthTextElement) {
            strengthTextElement.textContent = 'Fortaleza: ' + strengthText;
        }
    }
    
    /**
     * Verifica la contraseña actual contra la almacenada
     */
    async function verifyCurrentPassword(password) {
        try {
            const response = await fetch('api_verificar_password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    password: password
                })
            });
            
            const result = await response.json();
            
            if (!response.ok || !result.success) {
                return {
                    valid: false,
                    message: result.message || 'Error al verificar la contraseña'
                };
            }
            
            return {
                valid: result.valid || false,
                message: result.message || (result.valid ? 'Contraseña correcta' : 'Contraseña incorrecta')
            };
        } catch (error) {
            console.error('Error al verificar contraseña:', error);
            return {
                valid: false,
                message: 'Error al verificar la contraseña. Inténtalo más tarde.'
            };
        }
    }
    
    
    /**
     * Inicializa las validaciones en tiempo real de los campos del formulario
     */
    function initFormValidations() {
        const nombreInput = document.getElementById('input-nombre');
        const emailInput = document.getElementById('input-email');
        const telefonoInput = document.getElementById('input-telefono');
        const direccionInput = document.getElementById('input-direccion');
        
        // Validación en tiempo real para nombre
        if (nombreInput) {
            let nombreTimeout;
            nombreInput.addEventListener('input', function() {
                clearTimeout(nombreTimeout);
                const value = this.value.trim();
                
                if (value.length === 0) {
                    clearFieldError('input-nombre', 'error-nombre');
                    return;
                }
                
                nombreTimeout = setTimeout(function() {
                    const validation = validateNombre(value);
                    if (!validation.valid) {
                        showFieldError('input-nombre', 'error-nombre', validation.message);
                    } else {
                        clearFieldError('input-nombre', 'error-nombre');
                    }
                }, 500);
            });
            
            nombreInput.addEventListener('blur', function() {
                const validation = validateNombre(this.value.trim());
                if (!validation.valid) {
                    showFieldError('input-nombre', 'error-nombre', validation.message);
                } else {
                    clearFieldError('input-nombre', 'error-nombre');
                }
            });
        }
        
        // Validación en tiempo real para email
        if (emailInput) {
            let emailTimeout;
            emailInput.addEventListener('input', function() {
                clearTimeout(emailTimeout);
                const value = this.value.trim();
                
                if (value.length === 0) {
                    clearFieldError('input-email', 'error-email');
                    return;
                }
                
                emailTimeout = setTimeout(function() {
                    const validation = validateEmail(value);
                    if (!validation.valid) {
                        showFieldError('input-email', 'error-email', validation.message);
                    } else {
                        clearFieldError('input-email', 'error-email');
                    }
                }, 500);
            });
            
            emailInput.addEventListener('blur', async function() {
                const value = this.value.trim();
                if (value.length === 0) {
                    clearFieldError('input-email', 'error-email');
                    return;
                }
                
                const validation = validateEmail(value);
                if (!validation.valid) {
                    showFieldError('input-email', 'error-email', validation.message);
                    return;
                }
                
                // Verificar existencia del correo
                emailInput.disabled = true;
                const verifyResult = await verifyEmailExists(value);
                emailInput.disabled = false;
                
                if (!verifyResult.exists) {
                    showFieldError('input-email', 'error-email', verifyResult.message || 'El correo electrónico no existe o no es válido');
                } else {
                    clearFieldError('input-email', 'error-email');
                }
            });
        }
        
        // Validación en tiempo real para teléfono
        if (telefonoInput) {
            let telefonoTimeout;
            telefonoInput.addEventListener('input', function() {
                clearTimeout(telefonoTimeout);
                const value = this.value.trim();
                
                if (value.length === 0) {
                    clearFieldError('input-telefono', 'error-telefono');
                    return;
                }
                
                telefonoTimeout = setTimeout(function() {
                    const validation = validateTelefono(value);
                    if (!validation.valid) {
                        showFieldError('input-telefono', 'error-telefono', validation.message);
                    } else {
                        clearFieldError('input-telefono', 'error-telefono');
                    }
                }, 500);
            });
            
            telefonoInput.addEventListener('blur', function() {
                const validation = validateTelefono(this.value.trim());
                if (!validation.valid) {
                    showFieldError('input-telefono', 'error-telefono', validation.message);
                } else {
                    clearFieldError('input-telefono', 'error-telefono');
                }
            });
        }
        
        // Validación en tiempo real para dirección
        if (direccionInput) {
            let direccionTimeout;
            direccionInput.addEventListener('input', function() {
                clearTimeout(direccionTimeout);
                const value = this.value.trim();
                
                if (value.length === 0) {
                    clearFieldError('input-direccion', 'error-direccion');
                    return;
                }
                
                direccionTimeout = setTimeout(function() {
                    const validation = validateDireccion(value);
                    if (!validation.valid) {
                        showFieldError('input-direccion', 'error-direccion', validation.message);
                    } else {
                        clearFieldError('input-direccion', 'error-direccion');
                    }
                }, 500);
            });
            
            direccionInput.addEventListener('blur', function() {
                const validation = validateDireccion(this.value.trim());
                if (!validation.valid) {
                    showFieldError('input-direccion', 'error-direccion', validation.message);
                } else {
                    clearFieldError('input-direccion', 'error-direccion');
                }
            });
        }
    }
    
    /**
     * Actualiza el nombre del usuario en todos los lugares de la página
     */
    function updateUserNameInAllPlaces(nombre) {
        // Actualizar en el sidebar principal
        const sidebarUserName = document.getElementById('sidebar-user-name');
        if (sidebarUserName) {
            sidebarUserName.textContent = nombre;
        }
        
        // Actualizar en el sidebar del perfil (interno)
        const profileSidebarName = document.getElementById('profile-sidebar-name');
        if (profileSidebarName) {
            profileSidebarName.textContent = nombre;
        }
        
        // Actualizar en el menú móvil si existe
        const mobileUserName = document.getElementById('mobile-user-name');
        if (mobileUserName) {
            mobileUserName.textContent = nombre;
        }
        
        // Actualizar en el layout del cliente si existe
        if (window.ClientLayout) {
            window.ClientLayout.updateClientData({ nombre: nombre });
        }
    }
    
    /**
     * Muestra el estado de carga en el botón
     */
    function setButtonLoading(loading) {
        const btn = document.getElementById('btn-guardar-informacion');
        const icon = document.getElementById('btn-guardar-icon');
        const text = document.getElementById('btn-guardar-text');
        const spinner = document.getElementById('btn-guardar-spinner');
        
        if (!btn) return;
        
        if (loading) {
            btn.disabled = true;
            if (icon) icon.classList.add('hidden');
            if (spinner) spinner.classList.remove('hidden');
            if (text) text.textContent = 'Guardando...';
        } else {
            btn.disabled = false;
            if (icon) icon.classList.remove('hidden');
            if (spinner) spinner.classList.add('hidden');
            if (text) text.textContent = 'Guardar Información';
        }
    }
    
    /**
     * Inicializa la funcionalidad de guardar información personal
     */
    function initSavePersonalInfo() {
        const saveBtn = document.getElementById('btn-guardar-informacion');
        
        if (!saveBtn) {
            return;
        }
        
        saveBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const nombreInput = document.getElementById('input-nombre');
            const emailInput = document.getElementById('input-email');
            const telefonoInput = document.getElementById('input-telefono');
            const direccionInput = document.getElementById('input-direccion');
            
            if (!nombreInput || !emailInput || !telefonoInput || !direccionInput) {
                if (typeof customToast === 'function') {
                    customToast('Error al acceder a los campos del formulario', 'error');
                }
                return;
            }
            
            const nombre = nombreInput.value.trim();
            const email = emailInput.value.trim();
            const telefono = telefonoInput.value.trim();
            const direccion = direccionInput.value.trim();
            
            // Validar todos los campos
            const nombreValidation = validateNombre(nombre);
            const emailValidation = validateEmail(email);
            const telefonoValidation = validateTelefono(telefono);
            const direccionValidation = validateDireccion(direccion);
            
            let hasErrors = false;
            
            if (!nombreValidation.valid) {
                showFieldError('input-nombre', 'error-nombre', nombreValidation.message);
                hasErrors = true;
            } else {
                clearFieldError('input-nombre', 'error-nombre');
            }
            
            if (!emailValidation.valid) {
                showFieldError('input-email', 'error-email', emailValidation.message);
                hasErrors = true;
            } else {
                // Solo verificar existencia del correo si es diferente al email actual del usuario
                const emailActual = window.userSessionData && window.userSessionData.email ? window.userSessionData.email.toLowerCase() : '';
                const emailNuevo = email.toLowerCase();
                
                if (emailActual !== emailNuevo) {
                    // El email es diferente, verificar que no esté en uso por otro usuario
                    // Esta verificación se hará en el servidor también, pero validamos formato aquí
                    clearFieldError('input-email', 'error-email');
                } else {
                    // Es el mismo email, no necesita validación adicional
                    clearFieldError('input-email', 'error-email');
                }
            }
            
            if (!telefonoValidation.valid) {
                showFieldError('input-telefono', 'error-telefono', telefonoValidation.message);
                hasErrors = true;
            } else {
                clearFieldError('input-telefono', 'error-telefono');
            }
            
            if (!direccionValidation.valid) {
                showFieldError('input-direccion', 'error-direccion', direccionValidation.message);
                hasErrors = true;
            } else {
                clearFieldError('input-direccion', 'error-direccion');
            }
            
            if (hasErrors) {
                if (typeof customToast === 'function') {
                    customToast('Por favor corrige los errores en el formulario', 'error');
                } else if (typeof customAlert === 'function') {
                    customAlert('Por favor corrige los errores en el formulario', 'Error de Validación', 'error');
                }
                return;
            }
            
            // Activar estado de carga
            setButtonLoading(true);
            
            try {
                // Enviar datos al servidor
                const response = await fetch('api_actualizar_perfil.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        nombre: nombre,
                        email: email,
                        telefono: telefono,
                        direccion: direccion
                    })
                });
                
                console.log('Respuesta del servidor - Status:', response.status);
                
                // Leer el texto de la respuesta primero para debugging
                const responseText = await response.text();
                console.log('Respuesta del servidor - Texto crudo:', responseText);
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    console.error('Error al parsear JSON de la respuesta:', e);
                    console.error('Texto de respuesta:', responseText);
                    throw new Error('Error al procesar la respuesta del servidor. Por favor intenta de nuevo.');
                }
                
                console.log('Respuesta del servidor - Resultado parseado:', result);
                
                if (!response.ok || !result.success) {
                    console.error('Error en la respuesta del servidor:', result);
                    throw new Error(result.message || 'Error al actualizar el perfil');
                }
                
                // Preparar datos para guardar en localStorage
                const profileData = {
                    nombre: nombre,
                    email: email,
                    telefono: telefono,
                    direccion: direccion,
                    fechaActualizacion: new Date().toISOString()
                };
                
                localStorage.setItem('profileData', JSON.stringify(profileData));
                
                // Actualizar también en clientData si existe
                const clientData = JSON.parse(localStorage.getItem('clientData') || '{}');
                clientData.nombre = nombre;
                clientData.email = email;
                if (telefono) {
                    clientData.telefono = telefono;
                }
                localStorage.setItem('clientData', JSON.stringify(clientData));
                sessionStorage.setItem('clientData', JSON.stringify(clientData));
                
                // Actualizar userSessionData global si existe
                if (window.userSessionData) {
                    window.userSessionData.nombre = nombre;
                    window.userSessionData.email = email;
                    if (telefono) {
                        window.userSessionData.telefono = telefono;
                    }
                }
                
                // Actualizar el nombre en todos los lugares de la página
                if (typeof updateUserNameInAllPlaces === 'function') {
                    updateUserNameInAllPlaces(nombre);
                }
                
                // Actualizar el ClientLayout si existe
                if (window.ClientLayout && typeof window.ClientLayout.updateClientData === 'function') {
                    window.ClientLayout.updateClientData({
                        nombre: nombre,
                        email: email,
                        telefono: telefono || ''
                    });
                }
                
                // Actualizar también los elementos de la página directamente
                const profileSidebarName = document.getElementById('profile-sidebar-name');
                if (profileSidebarName) {
                    profileSidebarName.textContent = nombre;
                }
                const sidebarUserName = document.getElementById('sidebar-user-name');
                if (sidebarUserName) {
                    sidebarUserName.textContent = nombre;
                }
                
                // Desactivar estado de carga
                setButtonLoading(false);
                
                // Mostrar mensaje de éxito
                if (typeof customToast === 'function') {
                    customToast(result.message || 'Información personal guardada exitosamente', 'success', 3000);
                } else if (typeof customAlert === 'function') {
                    customAlert(result.message || 'Información personal guardada exitosamente', 'Éxito', 'success');
                }
                
            } catch (error) {
                console.error('Error al guardar información:', error);
                console.error('Stack trace:', error.stack);
                setButtonLoading(false);
                
                let errorMessage = 'Error al guardar la información. Por favor intenta de nuevo.';
                
                // Si el error tiene un mensaje más específico, usarlo
                if (error.message) {
                    errorMessage = error.message;
                }
                
                // Si hay una respuesta del servidor con un mensaje, usarlo
                if (error.response) {
                    try {
                        const errorData = await error.response.json();
                        if (errorData.message) {
                            errorMessage = errorData.message;
                        }
                    } catch (e) {
                        // Ignorar si no se puede parsear
                    }
                }
                
                if (typeof customToast === 'function') {
                    customToast(errorMessage, 'error', 5000);
                } else if (typeof customAlert === 'function') {
                    customAlert(errorMessage, 'Error', 'error');
                } else {
                    alert(errorMessage);
                }
            }
        });
    }

    /**
     * Inicializa las validaciones en tiempo real para los campos de contraseña
     */
    function initPasswordValidations() {
        const passwordActualInput = document.getElementById('input-password-actual');
        const passwordNuevaInput = document.getElementById('input-password-nueva');
        const passwordConfirmarInput = document.getElementById('input-password-confirmar');
        
        // Validación en tiempo real para contraseña actual
        if (passwordActualInput) {
            let passwordActualTimeout;
            passwordActualInput.addEventListener('input', function() {
                clearTimeout(passwordActualTimeout);
                const value = this.value.trim();
                
                if (value.length === 0) {
                    clearFieldError('input-password-actual', 'error-password-actual');
                    return;
                }
                
                passwordActualTimeout = setTimeout(function() {
                    const validation = validatePasswordActual(value);
                    if (!validation.valid) {
                        showFieldError('input-password-actual', 'error-password-actual', validation.message);
                    } else {
                        clearFieldError('input-password-actual', 'error-password-actual');
                    }
                }, 500);
            });
            
            passwordActualInput.addEventListener('blur', async function() {
                const value = this.value.trim();
                if (value.length === 0) {
                    clearFieldError('input-password-actual', 'error-password-actual');
                    return;
                }
                
                const validation = validatePasswordActual(value);
                if (!validation.valid) {
                    showFieldError('input-password-actual', 'error-password-actual', validation.message);
                    return;
                }
                
                // Verificar contraseña actual
                passwordActualInput.disabled = true;
                const verifyResult = await verifyCurrentPassword(value);
                passwordActualInput.disabled = false;
                
                if (!verifyResult.valid) {
                    showFieldError('input-password-actual', 'error-password-actual', verifyResult.message);
                } else {
                    clearFieldError('input-password-actual', 'error-password-actual');
                }
            });
        }
        
        // Validación en tiempo real para nueva contraseña
        if (passwordNuevaInput) {
            let passwordNuevaTimeout;
            passwordNuevaInput.addEventListener('input', function() {
                clearTimeout(passwordNuevaTimeout);
                const value = this.value.trim();
                
                if (value.length === 0) {
                    clearFieldError('input-password-nueva', 'error-password-nueva');
                    showPasswordStrength(0, '');
                    return;
                }
                
                passwordNuevaTimeout = setTimeout(function() {
                    const validation = validatePasswordNueva(value);
                    showPasswordStrength(validation.strength, validation.strengthText);
                    
                    if (!validation.valid) {
                        showFieldError('input-password-nueva', 'error-password-nueva', validation.message);
                    } else {
                        clearFieldError('input-password-nueva', 'error-password-nueva');
                    }
                    
                    // Validar coincidencia si ya hay texto en confirmar
                    const confirmarValue = passwordConfirmarInput ? passwordConfirmarInput.value.trim() : '';
                    if (confirmarValue.length > 0) {
                        const confirmValidation = validatePasswordConfirmar(value, confirmarValue);
                        if (!confirmValidation.valid) {
                            showFieldError('input-password-confirmar', 'error-password-confirmar', confirmValidation.message);
                        } else {
                            clearFieldError('input-password-confirmar', 'error-password-confirmar');
                        }
                    }
                }, 500);
            });
            
            passwordNuevaInput.addEventListener('blur', function() {
                const validation = validatePasswordNueva(this.value.trim());
                showPasswordStrength(validation.strength, validation.strengthText);
                
                if (!validation.valid) {
                    showFieldError('input-password-nueva', 'error-password-nueva', validation.message);
                } else {
                    clearFieldError('input-password-nueva', 'error-password-nueva');
                }
            });
        }
        
        // Validación en tiempo real para confirmar contraseña
        if (passwordConfirmarInput) {
            let passwordConfirmarTimeout;
            passwordConfirmarInput.addEventListener('input', function() {
                clearTimeout(passwordConfirmarTimeout);
                const nuevaValue = passwordNuevaInput ? passwordNuevaInput.value.trim() : '';
                const value = this.value.trim();
                
                if (value.length === 0) {
                    clearFieldError('input-password-confirmar', 'error-password-confirmar');
                    return;
                }
                
                passwordConfirmarTimeout = setTimeout(function() {
                    const validation = validatePasswordConfirmar(nuevaValue, value);
                    if (!validation.valid) {
                        showFieldError('input-password-confirmar', 'error-password-confirmar', validation.message);
                    } else {
                        clearFieldError('input-password-confirmar', 'error-password-confirmar');
                    }
                }, 500);
            });
            
            passwordConfirmarInput.addEventListener('blur', function() {
                const nuevaValue = passwordNuevaInput ? passwordNuevaInput.value.trim() : '';
                const validation = validatePasswordConfirmar(nuevaValue, this.value.trim());
                if (!validation.valid) {
                    showFieldError('input-password-confirmar', 'error-password-confirmar', validation.message);
                } else {
                    clearFieldError('input-password-confirmar', 'error-password-confirmar');
                }
            });
        }
    }
    
    /**
     * Muestra el estado de carga en el botón de contraseña
     */
    function setPasswordButtonLoading(loading) {
        const btn = document.getElementById('btn-actualizar-password');
        const icon = document.getElementById('btn-password-icon');
        const text = document.getElementById('btn-password-text');
        const spinner = document.getElementById('btn-password-spinner');
        
        if (!btn) return;
        
        if (loading) {
            btn.disabled = true;
            if (icon) icon.classList.add('hidden');
            if (spinner) spinner.classList.remove('hidden');
            if (text) text.textContent = 'Actualizando...';
        } else {
            btn.disabled = false;
            if (icon) icon.classList.remove('hidden');
            if (spinner) spinner.classList.add('hidden');
            if (text) text.textContent = 'Actualizar Contraseña';
        }
    }
    
    /**
     * Inicializa la funcionalidad de cambiar contraseña
     */
    function initChangePassword() {
        const changePasswordBtn = document.getElementById('btn-actualizar-password');
        
        if (!changePasswordBtn) {
            return;
        }
        
        changePasswordBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const passwordActualInput = document.getElementById('input-password-actual');
            const passwordNuevaInput = document.getElementById('input-password-nueva');
            const passwordConfirmarInput = document.getElementById('input-password-confirmar');
            
            if (!passwordActualInput || !passwordNuevaInput || !passwordConfirmarInput) {
                if (typeof customToast === 'function') {
                    customToast('Error al acceder a los campos del formulario', 'error');
                }
                return;
            }
            
            const actualPassword = passwordActualInput.value.trim();
            const nuevaPassword = passwordNuevaInput.value.trim();
            const confirmarPassword = passwordConfirmarInput.value.trim();
            
            // Validar todos los campos
            const actualValidation = validatePasswordActual(actualPassword);
            const nuevaValidation = validatePasswordNueva(nuevaPassword);
            const confirmarValidation = validatePasswordConfirmar(nuevaPassword, confirmarPassword);
            
            let hasErrors = false;
            
            // Validar contraseña actual
            if (!actualValidation.valid) {
                showFieldError('input-password-actual', 'error-password-actual', actualValidation.message);
                hasErrors = true;
            } else {
                // Verificar contraseña actual
                setPasswordButtonLoading(true);
                const verifyResult = await verifyCurrentPassword(actualPassword);
                setPasswordButtonLoading(false);
                
                if (!verifyResult.valid) {
                    showFieldError('input-password-actual', 'error-password-actual', verifyResult.message);
                    hasErrors = true;
                } else {
                    clearFieldError('input-password-actual', 'error-password-actual');
                }
            }
            
            // Validar nueva contraseña
            if (!nuevaValidation.valid) {
                showFieldError('input-password-nueva', 'error-password-nueva', nuevaValidation.message);
                showPasswordStrength(nuevaValidation.strength, nuevaValidation.strengthText);
                hasErrors = true;
            } else {
                clearFieldError('input-password-nueva', 'error-password-nueva');
                showPasswordStrength(nuevaValidation.strength, nuevaValidation.strengthText);
            }
            
            // Validar confirmación
            if (!confirmarValidation.valid) {
                showFieldError('input-password-confirmar', 'error-password-confirmar', confirmarValidation.message);
                hasErrors = true;
            } else {
                clearFieldError('input-password-confirmar', 'error-password-confirmar');
            }
            
            // Validar que la nueva contraseña sea diferente a la actual
            if (actualPassword === nuevaPassword) {
                showFieldError('input-password-nueva', 'error-password-nueva', 'La nueva contraseña debe ser diferente a la actual');
                hasErrors = true;
            }
            
            if (hasErrors) {
                if (typeof customToast === 'function') {
                    customToast('Por favor corrige los errores en el formulario', 'error');
                } else if (typeof customAlert === 'function') {
                    customAlert('Por favor corrige los errores en el formulario', 'Error de Validación', 'error');
                }
                return;
            }
            
            // Activar estado de carga
            setPasswordButtonLoading(true);
            
            try {
                // Preparar datos para enviar
                const datosEnvio = {
                    password_actual: actualPassword,
                    password_nueva: nuevaPassword
                };
                
                console.log('Enviando datos de contraseña al servidor');
                
                // Enviar datos al servidor
                const response = await fetch('api_actualizar_password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(datosEnvio)
                });
                
                console.log('Respuesta del servidor (password) - Status:', response.status);
                
                // Leer el texto de la respuesta primero para debugging
                const responseText = await response.text();
                console.log('Respuesta del servidor (password) - Texto crudo:', responseText);
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    console.error('Error al parsear JSON de la respuesta (password):', e);
                    console.error('Texto de respuesta:', responseText);
                    throw new Error('Error al procesar la respuesta del servidor. Por favor intenta de nuevo.');
                }
                
                console.log('Respuesta del servidor (password) - Resultado parseado:', result);
                
                if (!response.ok || !result.success) {
                    console.error('Error en la respuesta del servidor (password):', result);
                    throw new Error(result.message || 'Error al actualizar la contraseña');
                }
                
                // Limpiar campos
                passwordActualInput.value = '';
                passwordNuevaInput.value = '';
                passwordConfirmarInput.value = '';
                
                // Ocultar indicador de fortaleza
                showPasswordStrength(0, '');
                
                // Limpiar errores
                clearFieldError('input-password-actual', 'error-password-actual');
                clearFieldError('input-password-nueva', 'error-password-nueva');
                clearFieldError('input-password-confirmar', 'error-password-confirmar');
                
                // Desactivar estado de carga
                setPasswordButtonLoading(false);
                
                // Mostrar mensaje de éxito
                if (typeof customToast === 'function') {
                    customToast(result.message || 'Contraseña actualizada exitosamente', 'success', 3000);
                } else if (typeof customAlert === 'function') {
                    customAlert(result.message || 'Contraseña actualizada exitosamente', 'Éxito', 'success');
                }
                
            } catch (error) {
                console.error('Error al actualizar contraseña:', error);
                setPasswordButtonLoading(false);
                
                const errorMessage = error.message || 'Error al actualizar la contraseña. Por favor intenta de nuevo.';
                
                // Mostrar error específico en el campo correspondiente
                if (errorMessage.includes('actual') || errorMessage.includes('incorrecta')) {
                    showFieldError('input-password-actual', 'error-password-actual', errorMessage);
                } else if (errorMessage.includes('nueva') || errorMessage.includes('diferente')) {
                    showFieldError('input-password-nueva', 'error-password-nueva', errorMessage);
                }
                
                if (typeof customToast === 'function') {
                    customToast(errorMessage, 'error');
                } else if (typeof customAlert === 'function') {
                    customAlert(errorMessage, 'Error', 'error');
                }
            }
        });
    }

    /**
     * Carga las preferencias de notificación guardadas
     */
    function loadNotificationPreferences() {
        try {
            const savedPreferences = localStorage.getItem('notificationPreferences');
            if (!savedPreferences) {
                return; // Usar valores por defecto del HTML
            }
            
            const preferences = JSON.parse(savedPreferences);
            
            // Mapeo de IDs de checkboxes
            const checkboxMap = {
                'nuevosSorteos': {
                    'email': 'notif-nuevos-sorteos-email',
                    'sms': 'notif-nuevos-sorteos-sms'
                },
                'resultados': {
                    'email': 'notif-resultados-email',
                    'whatsapp': 'notif-resultados-whatsapp'
                },
                'promociones': {
                    'email': 'notif-promociones-email'
                }
            };
            
            // Aplicar preferencias guardadas
            if (preferences.nuevosSorteos) {
                const emailCheckbox = document.getElementById(checkboxMap.nuevosSorteos.email);
                const smsCheckbox = document.getElementById(checkboxMap.nuevosSorteos.sms);
                if (emailCheckbox) emailCheckbox.checked = preferences.nuevosSorteos.email || false;
                if (smsCheckbox) smsCheckbox.checked = preferences.nuevosSorteos.sms || false;
            }
            
            if (preferences.resultados) {
                const emailCheckbox = document.getElementById(checkboxMap.resultados.email);
                const whatsappCheckbox = document.getElementById(checkboxMap.resultados.whatsapp);
                if (emailCheckbox) emailCheckbox.checked = preferences.resultados.email || false;
                if (whatsappCheckbox) whatsappCheckbox.checked = preferences.resultados.whatsapp || false;
            }
            
            if (preferences.promociones) {
                const emailCheckbox = document.getElementById(checkboxMap.promociones.email);
                if (emailCheckbox) emailCheckbox.checked = preferences.promociones.email || false;
            }
            
        } catch (error) {
            console.error('Error al cargar preferencias:', error);
        }
    }
    
    /**
     * Inicializa la funcionalidad de los switches de notificación
     */
    function initNotificationSwitches() {
        // Obtener todos los checkboxes de notificación
        const checkboxes = [
            document.getElementById('notif-nuevos-sorteos-email'),
            document.getElementById('notif-nuevos-sorteos-sms'),
            document.getElementById('notif-resultados-email'),
            document.getElementById('notif-resultados-whatsapp'),
            document.getElementById('notif-promociones-email')
        ];
        
        // Agregar event listeners a cada checkbox
        checkboxes.forEach(function(checkbox) {
            if (!checkbox) return;
            
            checkbox.addEventListener('change', function() {
                // Agregar efecto visual al cambiar
                const label = checkbox.closest('label');
                if (label) {
                    if (checkbox.checked) {
                        label.classList.add('opacity-100');
                    } else {
                        label.classList.remove('opacity-100');
                    }
                }
                
                // Opcional: Mostrar feedback visual
                if (typeof customToast === 'function') {
                    const notificationType = getNotificationTypeName(checkbox.id);
                    const channel = getChannelName(checkbox.value);
                    const status = checkbox.checked ? 'activada' : 'desactivada';
                    // No mostrar toast en cada cambio para no ser molesto
                    // customToast(`${notificationType} - ${channel} ${status}`, 'info', 2000);
                }
            });
            
            // Agregar efecto hover
            const label = checkbox.closest('label');
            if (label) {
                label.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.02)';
                    this.style.transition = 'transform 0.2s';
                });
                
                label.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            }
        });
    }
    
    /**
     * Obtiene el nombre del tipo de notificación basado en el ID del checkbox
     */
    function getNotificationTypeName(checkboxId) {
        const typeMap = {
            'notif-nuevos-sorteos-email': 'Nuevos Sorteos',
            'notif-nuevos-sorteos-sms': 'Nuevos Sorteos',
            'notif-resultados-email': 'Resultados de Sorteos',
            'notif-resultados-whatsapp': 'Resultados de Sorteos',
            'notif-promociones-email': 'Promociones y Ofertas'
        };
        return typeMap[checkboxId] || 'Notificación';
    }
    
    /**
     * Obtiene el nombre del canal basado en el valor del checkbox
     */
    function getChannelName(value) {
        const channelMap = {
            'email': 'Email',
            'sms': 'SMS',
            'whatsapp': 'WhatsApp'
        };
        return channelMap[value] || value;
    }
    
    /**
     * Muestra el estado de carga en el botón de preferencias
     */
    function setPreferencesButtonLoading(loading) {
        const btn = document.getElementById('btn-guardar-preferencias');
        const icon = document.getElementById('btn-preferencias-icon');
        const text = document.getElementById('btn-preferencias-text');
        const spinner = document.getElementById('btn-preferencias-spinner');
        
        if (!btn) return;
        
        if (loading) {
            btn.disabled = true;
            if (icon) icon.classList.add('hidden');
            if (spinner) spinner.classList.remove('hidden');
            if (text) text.textContent = 'Guardando...';
        } else {
            btn.disabled = false;
            if (icon) icon.classList.remove('hidden');
            if (spinner) spinner.classList.add('hidden');
            if (text) text.textContent = 'Guardar Preferencias';
        }
    }
    
    /**
     * Valida las preferencias de notificación
     */
    function validateNotificationPreferences() {
        const nuevosSorteosEmail = document.getElementById('notif-nuevos-sorteos-email');
        const nuevosSorteosSms = document.getElementById('notif-nuevos-sorteos-sms');
        const resultadosEmail = document.getElementById('notif-resultados-email');
        const resultadosWhatsapp = document.getElementById('notif-resultados-whatsapp');
        const promocionesEmail = document.getElementById('notif-promociones-email');
        
        // Verificar que al menos un canal esté activado para cada tipo de notificación
        const errors = [];
        
        // Validar que al menos un canal esté activado para nuevos sorteos
        if (nuevosSorteosEmail && nuevosSorteosSms) {
            if (!nuevosSorteosEmail.checked && !nuevosSorteosSms.checked) {
                // No es un error, solo una advertencia opcional
            }
        }
        
        // Validar que al menos un canal esté activado para resultados
        if (resultadosEmail && resultadosWhatsapp) {
            if (!resultadosEmail.checked && !resultadosWhatsapp.checked) {
                // No es un error, solo una advertencia opcional
            }
        }
        
        return { valid: true, errors: errors };
    }
    
    /**
     * Obtiene las preferencias actuales de los checkboxes
     */
    function getCurrentNotificationPreferences() {
        const nuevosSorteosEmail = document.getElementById('notif-nuevos-sorteos-email');
        const nuevosSorteosSms = document.getElementById('notif-nuevos-sorteos-sms');
        const resultadosEmail = document.getElementById('notif-resultados-email');
        const resultadosWhatsapp = document.getElementById('notif-resultados-whatsapp');
        const promocionesEmail = document.getElementById('notif-promociones-email');
        
        return {
            nuevosSorteos: {
                email: nuevosSorteosEmail ? nuevosSorteosEmail.checked : false,
                sms: nuevosSorteosSms ? nuevosSorteosSms.checked : false
            },
            resultados: {
                email: resultadosEmail ? resultadosEmail.checked : false,
                whatsapp: resultadosWhatsapp ? resultadosWhatsapp.checked : false
            },
            promociones: {
                email: promocionesEmail ? promocionesEmail.checked : false
            },
            fechaActualizacion: new Date().toISOString()
        };
    }
    
    /**
     * Inicializa la funcionalidad de guardar preferencias de notificación
     */
    function initSavePreferences() {
        const savePreferencesBtn = document.getElementById('btn-guardar-preferencias');
        
        if (!savePreferencesBtn) {
            return;
        }
        
        savePreferencesBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            
            // Validar preferencias
            const validation = validateNotificationPreferences();
            
            if (!validation.valid) {
                if (typeof customToast === 'function') {
                    customToast(validation.errors.join(', '), 'error');
                } else if (typeof customAlert === 'function') {
                    customAlert(validation.errors.join(', '), 'Error de Validación', 'error');
                }
                return;
            }
            
            // Activar estado de carga
            setPreferencesButtonLoading(true);
            
            try {
                // Simular envío al servidor (en producción sería una llamada real)
                await new Promise(resolve => setTimeout(resolve, 1200));
                
                // Obtener preferencias actuales
                const preferences = getCurrentNotificationPreferences();
                
                // Encriptar preferencias sensibles antes de guardar
                const encryptedData = encryptData(preferences);
                
                // Guardar en localStorage
                const dataToStore = {
                    nuevosSorteos: preferences.nuevosSorteos,
                    resultados: preferences.resultados,
                    promociones: preferences.promociones,
                    fechaActualizacion: preferences.fechaActualizacion,
                    encrypted: encryptedData // Versión encriptada para seguridad
                };
                
                localStorage.setItem('notificationPreferences', JSON.stringify(dataToStore));
                
                // Desactivar estado de carga
                setPreferencesButtonLoading(false);
                
                // Mostrar mensaje de éxito con resumen
                const activeChannels = [];
                if (preferences.nuevosSorteos.email) activeChannels.push('Nuevos Sorteos - Email');
                if (preferences.nuevosSorteos.sms) activeChannels.push('Nuevos Sorteos - SMS');
                if (preferences.resultados.email) activeChannels.push('Resultados - Email');
                if (preferences.resultados.whatsapp) activeChannels.push('Resultados - WhatsApp');
                if (preferences.promociones.email) activeChannels.push('Promociones - Email');
                
                const message = activeChannels.length > 0 
                    ? `Preferencias guardadas. ${activeChannels.length} notificación(es) activa(s).`
                    : 'Preferencias guardadas. No hay notificaciones activas.';
                
                if (typeof customToast === 'function') {
                    customToast(message, 'success', 3000);
                } else if (typeof customAlert === 'function') {
                    customAlert(message, 'Éxito', 'success');
                }
                
            } catch (error) {
                console.error('Error al guardar preferencias:', error);
                setPreferencesButtonLoading(false);
                
                if (typeof customToast === 'function') {
                    customToast('Error al guardar las preferencias. Por favor intenta de nuevo.', 'error');
                } else if (typeof customAlert === 'function') {
                    customAlert('Error al guardar las preferencias. Por favor intenta de nuevo.', 'Error', 'error');
                }
            }
        });
    }

    /**
     * Inicializa la navegación del sidebar interno
     */
    function initSidebarNavigation() {
        const navLinks = {
            'informacion': {
                link: document.getElementById('nav-link-informacion'),
                section: document.getElementById('section-informacion-personal')
            },
            'seguridad': {
                link: document.getElementById('nav-link-seguridad'),
                section: document.getElementById('section-seguridad')
            },
            'notificaciones': {
                link: document.getElementById('nav-link-notificaciones'),
                section: document.getElementById('section-notificaciones')
            },
            'pagos': {
                link: document.getElementById('nav-link-pagos'),
                section: document.getElementById('section-metodos-pago')
            },
            'historial': {
                link: document.getElementById('nav-link-historial'),
                section: document.getElementById('section-historial-sorteos')
            }
        };
        
        // Función para actualizar el enlace activo
        function setActiveLink(activeKey) {
            // Asegurarse de que activeKey sea válido
            if (!activeKey || !navLinks[activeKey]) {
                activeKey = 'informacion'; // Por defecto
            }
            
            Object.keys(navLinks).forEach(function(key) {
                const navItem = navLinks[key];
                if (navItem && navItem.link) {
                    if (key === activeKey) {
                        // Activar - solo para secciones implementadas
                        if (navItem.section || key === 'informacion' || key === 'pagos') {
                            navItem.link.classList.remove('text-text-secondary', 'hover:bg-[#282d39]', 'hover:text-white');
                            navItem.link.classList.add('bg-primary/10', 'text-primary', 'border-l-4', 'border-primary');
                            const span = navItem.link.querySelector('span.text-sm');
                            if (span) {
                                span.classList.remove('font-medium');
                                span.classList.add('font-bold');
                            }
                        }
                    } else {
                        // Desactivar
                        navItem.link.classList.remove('bg-primary/10', 'text-primary', 'border-l-4', 'border-primary');
                        navItem.link.classList.add('text-text-secondary', 'hover:bg-[#282d39]', 'hover:text-white');
                        const span = navItem.link.querySelector('span.text-sm');
                        if (span) {
                            span.classList.remove('font-bold');
                            span.classList.add('font-medium');
                        }
                    }
                }
            });
        }
        
        // Función para hacer scroll suave a una sección
        function scrollToSection(section, offset = 100) {
            if (!section) return;
            
            // Obtener el contenedor con scroll
            const scrollContainer = document.querySelector('.flex-1.overflow-y-auto');
            
            if (scrollContainer) {
                // Calcular posición relativa al contenedor
                const containerRect = scrollContainer.getBoundingClientRect();
                const sectionRect = section.getBoundingClientRect();
                
                // Calcular la posición del scroll dentro del contenedor
                const scrollTop = scrollContainer.scrollTop;
                const sectionTopRelative = sectionRect.top - containerRect.top;
                const targetScroll = scrollTop + sectionTopRelative - offset;
                
                // Hacer scroll en el contenedor
                scrollContainer.scrollTo({
                    top: Math.max(0, targetScroll),
                    behavior: 'smooth'
                });
            } else {
                // Fallback a window si no hay contenedor
                const sectionTop = section.getBoundingClientRect().top + window.pageYOffset;
                const offsetPosition = sectionTop - offset;
                
                window.scrollTo({
                    top: Math.max(0, offsetPosition),
                    behavior: 'smooth'
                });
            }
        }
        
        // Agregar event listeners a los enlaces
        Object.keys(navLinks).forEach(function(key) {
            const navItem = navLinks[key];
            if (navItem.link) {
                navItem.link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Si la sección no existe, mostrar mensaje
                    if (!navItem.section) {
                        if (key === 'pagos') {
                            if (typeof customAlert === 'function') {
                                customAlert('La funcionalidad de Métodos de Pago estará disponible próximamente.', 'Próximamente', 'info');
                            }
                        }
                        return;
                    }
                    
                    // Actualizar enlace activo
                    setActiveLink(key);
                    
                    // Hacer scroll a la sección
                    scrollToSection(navItem.section, 120);
                });
            }
        });
        
        // Función para detectar qué sección está visible
        function detectActiveSection() {
            try {
                // Obtener el contenedor principal con scroll
                const scrollContainer = document.querySelector('.flex-1.overflow-y-auto');
                let scrollTop;
                
                if (scrollContainer) {
                    scrollTop = scrollContainer.scrollTop;
                } else {
                    scrollTop = window.pageYOffset || document.documentElement.scrollTop || 0;
                }
                
                // Verificar cada sección
                const sections = [
                    { key: 'informacion', section: navLinks.informacion.section },
                    { key: 'seguridad', section: navLinks.seguridad.section },
                    { key: 'notificaciones', section: navLinks.notificaciones.section },
                    { key: 'pagos', section: navLinks.pagos.section },
                    { key: 'historial', section: navLinks.historial.section }
                ];
                
                let activeKey = 'informacion'; // Por defecto
                let minDistance = Infinity;
                
                // Si estamos al inicio de la página, activar la primera sección
                if (scrollTop < 150) {
                    setActiveLink('informacion');
                    return;
                }
                
                // Verificar cada sección
                sections.forEach(function(item) {
                    if (item.section) {
                        try {
                            const rect = item.section.getBoundingClientRect();
                            
                            // Calcular distancia desde el top del viewport (considerando header)
                            const distanceFromTop = Math.abs(rect.top - 150);
                            
                            // Si la sección está visible en el viewport (con margen para el header)
                            if (rect.top <= 250 && rect.bottom >= 50) {
                                if (distanceFromTop < minDistance) {
                                    minDistance = distanceFromTop;
                                    activeKey = item.key;
                                }
                            }
                        } catch (e) {
                            console.error('Error al calcular posición de sección:', e);
                        }
                    }
                });
                
                // Asegurarse de que siempre haya un enlace activo
                setActiveLink(activeKey);
            } catch (error) {
                console.error('Error en detectActiveSection:', error);
                // En caso de error, mantener la primera sección activa
                setActiveLink('informacion');
            }
        }
        
        // Detectar sección activa al hacer scroll
        let scrollTimeout;
        let isScrolling = false;
        
        function handleScroll() {
            if (!isScrolling) {
                isScrolling = true;
            }
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(function() {
                detectActiveSection();
                isScrolling = false;
            }, 150);
        }
        
        // Agregar listener al contenedor con scroll o a window
        const scrollContainer = document.querySelector('.flex-1.overflow-y-auto');
        if (scrollContainer) {
            scrollContainer.addEventListener('scroll', handleScroll);
        } else {
            window.addEventListener('scroll', handleScroll);
        }
        
        // Detectar sección activa al cargar la página (con delay para asegurar que el DOM esté listo)
        setTimeout(function() {
            detectActiveSection();
        }, 300);
    }
    
    /**
     * Valida el número de tarjeta usando algoritmo de Luhn
     */
    function validateCardNumber(cardNumber) {
        // Remover espacios y caracteres no numéricos
        const cleanNumber = cardNumber.replace(/\s+/g, '').replace(/[^0-9]/g, '');
        
        if (!cleanNumber || cleanNumber.length < 13 || cleanNumber.length > 19) {
            return { valid: false, message: 'El número de tarjeta debe tener entre 13 y 19 dígitos' };
        }
        
        // Validar usando algoritmo de Luhn
        let sum = 0;
        let isEven = false;
        
        for (let i = cleanNumber.length - 1; i >= 0; i--) {
            let digit = parseInt(cleanNumber.charAt(i), 10);
            
            if (isEven) {
                digit *= 2;
                if (digit > 9) {
                    digit -= 9;
                }
            }
            
            sum += digit;
            isEven = !isEven;
        }
        
        if (sum % 10 !== 0) {
            return { valid: false, message: 'El número de tarjeta no es válido' };
        }
        
        // Detectar tipo de tarjeta
        let cardType = 'unknown';
        if (/^4/.test(cleanNumber)) {
            cardType = 'visa';
        } else if (/^5[1-5]/.test(cleanNumber)) {
            cardType = 'mastercard';
        } else if (/^3[47]/.test(cleanNumber)) {
            cardType = 'amex';
        }
        
        return { valid: true, message: '', type: cardType, cleanNumber: cleanNumber };
    }
    
    /**
     * Formatea el número de tarjeta con espacios
     */
    function formatCardNumber(value) {
        const cleanValue = value.replace(/\s+/g, '').replace(/[^0-9]/g, '');
        const formatted = cleanValue.match(/.{1,4}/g) || [];
        return formatted.join(' ').trim();
    }
    
    /**
     * Valida la fecha de expiración
     */
    function validateExpiryDate(expiry) {
        if (!expiry || expiry.length < 5) {
            return { valid: false, message: 'La fecha de expiración es requerida (MM/AA)' };
        }
        
        const parts = expiry.split('/');
        if (parts.length !== 2) {
            return { valid: false, message: 'Formato inválido. Usa MM/AA' };
        }
        
        const month = parseInt(parts[0], 10);
        const year = parseInt('20' + parts[1], 10);
        
        if (isNaN(month) || isNaN(year)) {
            return { valid: false, message: 'La fecha debe contener solo números' };
        }
        
        if (month < 1 || month > 12) {
            return { valid: false, message: 'El mes debe estar entre 01 y 12' };
        }
        
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear();
        const currentMonth = currentDate.getMonth() + 1;
        
        if (year < currentYear || (year === currentYear && month < currentMonth)) {
            return { valid: false, message: 'La tarjeta ha expirado' };
        }
        
        // Validar que no sea más de 10 años en el futuro
        if (year > currentYear + 10) {
            return { valid: false, message: 'La fecha de expiración no puede ser más de 10 años en el futuro' };
        }
        
        return { valid: true, message: '', month: month, year: year };
    }
    
    /**
     * Formatea la fecha de expiración
     */
    function formatExpiryDate(value) {
        const cleanValue = value.replace(/\D/g, '');
        if (cleanValue.length >= 2) {
            return cleanValue.substring(0, 2) + '/' + cleanValue.substring(2, 4);
        }
        return cleanValue;
    }
    
    /**
     * Valida el CVV
     */
    function validateCVV(cvv, cardType) {
        if (!cvv || cvv.length === 0) {
            return { valid: false, message: 'El CVV es requerido' };
        }
        
        const cleanCVV = cvv.replace(/\D/g, '');
        
        // AMEX tiene 4 dígitos, otras tarjetas tienen 3
        const expectedLength = cardType === 'amex' ? 4 : 3;
        
        if (cleanCVV.length !== expectedLength) {
            return { 
                valid: false, 
                message: cardType === 'amex' 
                    ? 'El CVV de American Express debe tener 4 dígitos' 
                    : 'El CVV debe tener 3 dígitos' 
            };
        }
        
        if (!/^\d+$/.test(cleanCVV)) {
            return { valid: false, message: 'El CVV solo debe contener números' };
        }
        
        return { valid: true, message: '', cleanCVV: cleanCVV };
    }
    
    /**
     * Valida el nombre del titular
     */
    function validateCardName(name) {
        if (!name || name.trim().length === 0) {
            return { valid: false, message: 'El nombre del titular es requerido' };
        }
        
        if (name.trim().length < 3) {
            return { valid: false, message: 'El nombre debe tener al menos 3 caracteres' };
        }
        
        if (name.trim().length > 50) {
            return { valid: false, message: 'El nombre no puede exceder 50 caracteres' };
        }
        
        // Validar que contenga solo letras, espacios y algunos caracteres especiales
        const nameRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s'-]+$/;
        if (!nameRegex.test(name.trim())) {
            return { valid: false, message: 'El nombre solo puede contener letras, espacios y guiones' };
        }
        
        return { valid: true, message: '' };
    }
    
    /**
     * Enmascara el número de tarjeta para mostrar solo los últimos 4 dígitos
     */
    function maskCardNumber(cardNumber) {
        const cleanNumber = cardNumber.replace(/\s+/g, '');
        if (cleanNumber.length <= 4) {
            return cleanNumber;
        }
        const last4 = cleanNumber.slice(-4);
        return '**** **** **** ' + last4;
    }
    
    /**
     * Obtiene el icono de tipo de tarjeta
     */
    function getCardTypeIcon(cardType) {
        const icons = {
            'visa': 'credit_card',
            'mastercard': 'credit_card',
            'amex': 'credit_card'
        };
        return icons[cardType] || 'credit_card';
    }
    
    /**
     * Carga los métodos de pago guardados
     */
    function loadPaymentMethods() {
        try {
            const savedMethods = localStorage.getItem('paymentMethods');
            if (!savedMethods) {
                return;
            }
            
            const methods = JSON.parse(savedMethods);
            displayPaymentMethods(methods);
        } catch (error) {
            console.error('Error al cargar métodos de pago:', error);
        }
    }
    
    /**
     * Muestra los métodos de pago guardados
     */
    function displayPaymentMethods(methods) {
        const container = document.getElementById('payment-methods-list');
        if (!container || !methods || methods.length === 0) {
            if (container) {
                container.innerHTML = '<p class="text-text-secondary text-sm">No tienes métodos de pago guardados aún.</p>';
            }
            return;
        }
        
        container.innerHTML = methods.map(function(method, index) {
            const cardType = method.type || 'unknown';
            const expiry = method.expiry || '**/**';
            const maskedNumber = maskCardNumber(method.number || '');
            
            return `
                <div class="flex items-center justify-between p-4 bg-[#151a23] rounded-lg border border-[#282d39] hover:border-primary/50 transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-8 bg-primary/20 rounded flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary">credit_card</span>
                        </div>
                        <div>
                            <p class="text-white font-medium">${maskedNumber}</p>
                            <p class="text-text-secondary text-xs">Expira ${expiry} • ${method.name || 'Titular'}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        ${method.isDefault ? '<span class="px-2 py-1 bg-primary/20 text-primary text-xs rounded">Predeterminado</span>' : ''}
                        <button class="delete-payment-method p-2 text-red-400 hover:bg-red-900/20 rounded transition-colors" data-index="${index}" aria-label="Eliminar método de pago">
                            <span class="material-symbols-outlined text-[20px]">delete</span>
                        </button>
                    </div>
                </div>
            `;
        }).join('');
        
        // Agregar event listeners a los botones de eliminar
        container.querySelectorAll('.delete-payment-method').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'), 10);
                deletePaymentMethod(index);
            });
        });
    }
    
    /**
     * Elimina un método de pago
     */
    function deletePaymentMethod(index) {
        if (typeof customConfirm === 'function') {
            customConfirm(
                '¿Estás seguro de que deseas eliminar este método de pago?',
                'Eliminar Método de Pago',
                'warning'
            ).then(function(confirmed) {
                if (confirmed) {
                    try {
                        const savedMethods = localStorage.getItem('paymentMethods');
                        if (savedMethods) {
                            const methods = JSON.parse(savedMethods);
                            methods.splice(index, 1);
                            localStorage.setItem('paymentMethods', JSON.stringify(methods));
                            displayPaymentMethods(methods);
                            
                            if (typeof customToast === 'function') {
                                customToast('Método de pago eliminado exitosamente', 'success');
                            }
                        }
                    } catch (error) {
                        console.error('Error al eliminar método de pago:', error);
                        if (typeof customToast === 'function') {
                            customToast('Error al eliminar el método de pago', 'error');
                        }
                    }
                }
            });
        } else {
            if (confirm('¿Estás seguro de que deseas eliminar este método de pago?')) {
                try {
                    const savedMethods = localStorage.getItem('paymentMethods');
                    if (savedMethods) {
                        const methods = JSON.parse(savedMethods);
                        methods.splice(index, 1);
                        localStorage.setItem('paymentMethods', JSON.stringify(methods));
                        displayPaymentMethods(methods);
                    }
                } catch (error) {
                    console.error('Error al eliminar método de pago:', error);
                }
            }
        }
    }
    
    /**
     * Muestra el estado de carga en el botón de guardar pago
     */
    function setPaymentButtonLoading(loading) {
        const btn = document.getElementById('btn-guardar-pago');
        const icon = document.getElementById('btn-pago-icon');
        const text = document.getElementById('btn-pago-text');
        const spinner = document.getElementById('btn-pago-spinner');
        
        if (!btn) return;
        
        if (loading) {
            btn.disabled = true;
            if (icon) icon.classList.add('hidden');
            if (spinner) spinner.classList.remove('hidden');
            if (text) text.textContent = 'Guardando...';
        } else {
            btn.disabled = false;
            if (icon) icon.classList.remove('hidden');
            if (spinner) spinner.classList.add('hidden');
            if (text) text.textContent = 'Guardar Método de Pago';
        }
    }
    
    /**
     * Limpia el formulario de pago
     */
    function clearPaymentForm() {
        const nameInput = document.getElementById('input-card-name');
        const numberInput = document.getElementById('input-card-number');
        const expiryInput = document.getElementById('input-card-expiry');
        const cvvInput = document.getElementById('input-card-cvv');
        const visaRadio = document.getElementById('card-type-visa');
        
        if (nameInput) nameInput.value = '';
        if (numberInput) numberInput.value = '';
        if (expiryInput) expiryInput.value = '';
        if (cvvInput) {
            cvvInput.value = '';
            cvvInput.placeholder = '123'; // Restablecer placeholder por defecto (Visa)
            cvvInput.maxLength = 3;
        }
        if (visaRadio) {
            visaRadio.checked = true;
            // Disparar evento change para actualizar placeholders
            visaRadio.dispatchEvent(new Event('change', { bubbles: true }));
        }
        
        // Limpiar errores
        clearFieldError('input-card-name', 'error-card-name');
        clearFieldError('input-card-number', 'error-card-number');
        clearFieldError('input-card-expiry', 'error-card-expiry');
        clearFieldError('input-card-cvv', 'error-card-cvv');
    }
    
    /**
     * Inicializa el formulario de métodos de pago
     */
    function initPaymentMethodForm() {
        const nameInput = document.getElementById('input-card-name');
        const numberInput = document.getElementById('input-card-number');
        const expiryInput = document.getElementById('input-card-expiry');
        const cvvInput = document.getElementById('input-card-cvv');
        const saveBtn = document.getElementById('btn-guardar-pago');
        const cancelBtn = document.getElementById('btn-cancelar-pago');
        const cardTypeRadios = document.querySelectorAll('input[name="card-type"]');
        
        // Formatear número de tarjeta mientras se escribe
        if (numberInput) {
            numberInput.addEventListener('input', function() {
                const formatted = formatCardNumber(this.value);
                this.value = formatted;
                
                // Validar y detectar tipo de tarjeta
                if (this.value.length >= 13) {
                    const validation = validateCardNumber(this.value);
                    if (validation.valid && validation.type) {
                        // Seleccionar tipo de tarjeta automáticamente
                        const radio = document.getElementById('card-type-' + validation.type);
                        if (radio && !radio.checked) {
                            radio.checked = true;
                            // Disparar evento change para actualizar placeholders y validaciones
                            radio.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }
                }
                
                // Validar en tiempo real
                if (this.value.length > 0) {
                    const validation = validateCardNumber(this.value);
                    if (!validation.valid) {
                        showFieldError('input-card-number', 'error-card-number', validation.message);
                    } else {
                        clearFieldError('input-card-number', 'error-card-number');
                    }
                } else {
                    clearFieldError('input-card-number', 'error-card-number');
                }
            });
            
            numberInput.addEventListener('blur', function() {
                const validation = validateCardNumber(this.value);
                if (!validation.valid) {
                    showFieldError('input-card-number', 'error-card-number', validation.message);
                } else {
                    clearFieldError('input-card-number', 'error-card-number');
                }
            });
        }
        
        // Formatear fecha de expiración mientras se escribe
        if (expiryInput) {
            expiryInput.addEventListener('input', function() {
                const formatted = formatExpiryDate(this.value);
                this.value = formatted;
                
                if (this.value.length >= 5) {
                    const validation = validateExpiryDate(this.value);
                    if (!validation.valid) {
                        showFieldError('input-card-expiry', 'error-card-expiry', validation.message);
                    } else {
                        clearFieldError('input-card-expiry', 'error-card-expiry');
                    }
                } else if (this.value.length === 0) {
                    clearFieldError('input-card-expiry', 'error-card-expiry');
                }
            });
            
            expiryInput.addEventListener('blur', function() {
                if (this.value.length > 0) {
                    const validation = validateExpiryDate(this.value);
                    if (!validation.valid) {
                        showFieldError('input-card-expiry', 'error-card-expiry', validation.message);
                    } else {
                        clearFieldError('input-card-expiry', 'error-card-expiry');
                    }
                }
            });
        }
        
        // Validar CVV mientras se escribe
        if (cvvInput) {
            cvvInput.addEventListener('input', function() {
                // Solo permitir números
                this.value = this.value.replace(/\D/g, '');
                
                // Obtener tipo de tarjeta seleccionado
                const selectedType = document.querySelector('input[name="card-type"]:checked');
                const cardType = selectedType ? selectedType.value : 'visa';
                
                if (this.value.length > 0) {
                    const validation = validateCVV(this.value, cardType);
                    if (!validation.valid) {
                        showFieldError('input-card-cvv', 'error-card-cvv', validation.message);
                    } else {
                        clearFieldError('input-card-cvv', 'error-card-cvv');
                    }
                } else {
                    clearFieldError('input-card-cvv', 'error-card-cvv');
                }
            });
        }
        
        /**
         * Función que se ejecuta cuando cambia el tipo de tarjeta
         */
        function handleCardTypeChange(cardType) {
            // Actualizar placeholder y maxlength del CVV según el tipo
            if (cvvInput) {
                if (cardType === 'amex') {
                    cvvInput.placeholder = '1234';
                    cvvInput.maxLength = 4;
                } else {
                    cvvInput.placeholder = '123';
                    cvvInput.maxLength = 3;
                }
                
                // Si ya hay un CVV ingresado, validarlo con el nuevo tipo
                if (cvvInput.value.length > 0) {
                    const validation = validateCVV(cvvInput.value, cardType);
                    if (!validation.valid) {
                        showFieldError('input-card-cvv', 'error-card-cvv', validation.message);
                    } else {
                        clearFieldError('input-card-cvv', 'error-card-cvv');
                    }
                } else {
                    // Limpiar errores si el campo está vacío
                    clearFieldError('input-card-cvv', 'error-card-cvv');
                }
            }
            
            // Si ya hay un número de tarjeta ingresado, validarlo con el nuevo tipo
            if (numberInput && numberInput.value.length > 0) {
                const validation = validateCardNumber(numberInput.value);
                
                // Verificar si el tipo detectado coincide con el seleccionado
                if (validation.valid && validation.type) {
                    if (validation.type !== cardType) {
                        // El número no coincide con el tipo seleccionado
                        showFieldError('input-card-number', 'error-card-number', 
                            'El número de tarjeta no corresponde a una tarjeta ' + 
                            (cardType === 'visa' ? 'Visa' : cardType === 'mastercard' ? 'Mastercard' : 'American Express'));
                    } else {
                        // Coincide, limpiar error si existe
                        clearFieldError('input-card-number', 'error-card-number');
                    }
                } else if (!validation.valid) {
                    // El número no es válido
                    showFieldError('input-card-number', 'error-card-number', validation.message);
                }
            }
            
            // Feedback visual: agregar clase de animación al contenedor del radio
            const selectedRadio = document.querySelector('input[name="card-type"]:checked');
            if (selectedRadio) {
                const label = selectedRadio.closest('label');
                if (label) {
                    const cardDiv = label.querySelector('div');
                    if (cardDiv) {
                        // Agregar efecto de pulso
                        cardDiv.classList.add('animate-pulse');
                        setTimeout(function() {
                            cardDiv.classList.remove('animate-pulse');
                        }, 500);
                    }
                }
            }
        }
        
        // Inicializar el placeholder del CVV según el tipo seleccionado por defecto
        const defaultCardType = document.querySelector('input[name="card-type"]:checked');
        if (defaultCardType && cvvInput) {
            handleCardTypeChange(defaultCardType.value);
        }
        
        // Actualizar validación y placeholders cuando cambia el tipo de tarjeta
        cardTypeRadios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                handleCardTypeChange(this.value);
            });
            
            // También agregar evento click para mejor respuesta visual
            radio.addEventListener('click', function() {
                // Forzar el evento change si no se dispara automáticamente
                if (!this.checked) {
                    this.checked = true;
                    this.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });
        
        // Validar nombre mientras se escribe
        if (nameInput) {
            let nameTimeout;
            nameInput.addEventListener('input', function() {
                clearTimeout(nameTimeout);
                const value = this.value.trim();
                
                if (value.length === 0) {
                    clearFieldError('input-card-name', 'error-card-name');
                    return;
                }
                
                nameTimeout = setTimeout(function() {
                    const validation = validateCardName(value);
                    if (!validation.valid) {
                        showFieldError('input-card-name', 'error-card-name', validation.message);
                    } else {
                        clearFieldError('input-card-name', 'error-card-name');
                    }
                }, 500);
            });
            
            nameInput.addEventListener('blur', function() {
                const validation = validateCardName(this.value.trim());
                if (!validation.valid) {
                    showFieldError('input-card-name', 'error-card-name', validation.message);
                } else {
                    clearFieldError('input-card-name', 'error-card-name');
                }
            });
        }
        
        // Botón cancelar
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function(e) {
                e.preventDefault();
                clearPaymentForm();
            });
        }
        
        // Botón guardar
        if (saveBtn) {
            saveBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                
                if (!nameInput || !numberInput || !expiryInput || !cvvInput) {
                    return;
                }
                
                const name = nameInput.value.trim();
                const number = numberInput.value.trim();
                const expiry = expiryInput.value.trim();
                const cvv = cvvInput.value.trim();
                const selectedType = document.querySelector('input[name="card-type"]:checked');
                const cardType = selectedType ? selectedType.value : 'visa';
                
                // Validar todos los campos
                const nameValidation = validateCardName(name);
                const numberValidation = validateCardNumber(number);
                const expiryValidation = validateExpiryDate(expiry);
                const cvvValidation = validateCVV(cvv, cardType);
                
                let hasErrors = false;
                
                if (!nameValidation.valid) {
                    showFieldError('input-card-name', 'error-card-name', nameValidation.message);
                    hasErrors = true;
                } else {
                    clearFieldError('input-card-name', 'error-card-name');
                }
                
                if (!numberValidation.valid) {
                    showFieldError('input-card-number', 'error-card-number', numberValidation.message);
                    hasErrors = true;
                } else {
                    clearFieldError('input-card-number', 'error-card-number');
                }
                
                if (!expiryValidation.valid) {
                    showFieldError('input-card-expiry', 'error-card-expiry', expiryValidation.message);
                    hasErrors = true;
                } else {
                    clearFieldError('input-card-expiry', 'error-card-expiry');
                }
                
                if (!cvvValidation.valid) {
                    showFieldError('input-card-cvv', 'error-card-cvv', cvvValidation.message);
                    hasErrors = true;
                } else {
                    clearFieldError('input-card-cvv', 'error-card-cvv');
                }
                
                if (hasErrors) {
                    if (typeof customToast === 'function') {
                        customToast('Por favor corrige los errores en el formulario', 'error');
                    }
                    return;
                }
                
                // Activar estado de carga
                setPaymentButtonLoading(true);
                
                try {
                    // Simular envío al servidor/pasarela (en producción sería una llamada real)
                    await new Promise(resolve => setTimeout(resolve, 2000));
                    
                    // Obtener métodos guardados
                    let savedMethods = [];
                    try {
                        const stored = localStorage.getItem('paymentMethods');
                        if (stored) {
                            savedMethods = JSON.parse(stored);
                        }
                    } catch (e) {
                        console.error('Error al cargar métodos guardados:', e);
                    }
                    
                    // Crear nuevo método de pago (en producción, el número se tokenizaría)
                    const newMethod = {
                        id: Date.now().toString(),
                        name: name,
                        number: numberValidation.cleanNumber, // En producción, usar token
                        expiry: expiry,
                        type: numberValidation.type || cardType,
                        isDefault: savedMethods.length === 0, // Primera tarjeta es predeterminada
                        fechaAgregado: new Date().toISOString()
                    };
                    
                    // Agregar nuevo método
                    savedMethods.push(newMethod);
                    
                    // Guardar en localStorage
                    localStorage.setItem('paymentMethods', JSON.stringify(savedMethods));
                    
                    // Limpiar formulario
                    clearPaymentForm();
                    
                    // Actualizar lista de métodos
                    displayPaymentMethods(savedMethods);
                    
                    // Desactivar estado de carga
                    setPaymentButtonLoading(false);
                    
                    // Mostrar mensaje de éxito
                    if (typeof customToast === 'function') {
                        customToast('Método de pago guardado exitosamente', 'success', 3000);
                    }
                    
                } catch (error) {
                    console.error('Error al guardar método de pago:', error);
                    setPaymentButtonLoading(false);
                    
                    if (typeof customToast === 'function') {
                        customToast('Error al guardar el método de pago. Por favor intenta de nuevo.', 'error');
                    }
                }
            });
        }
    }

    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        // DOM ya está listo
        init();
    }

    /**
     * Inicializa la funcionalidad de Historial de Sorteos
     */
    function initHistorialSorteos() {
        // Estado del historial
        let historialState = {
            sorteos: [],
            filteredSorteos: [],
            currentPage: 1,
            itemsPerPage: 10,
            filters: {
                search: '',
                estado: 'todos',
                fecha: 'todos'
            }
        };
        
        // Elementos del DOM
        const elementos = {
            container: document.getElementById('historial-container'),
            loading: document.getElementById('historial-loading'),
            empty: document.getElementById('historial-empty'),
            list: document.getElementById('historial-list'),
            pagination: document.getElementById('historial-pagination'),
            searchInput: document.getElementById('input-buscar-historial'),
            estadoSelect: document.getElementById('select-filtro-estado'),
            fechaSelect: document.getElementById('select-filtro-fecha'),
            limpiarBtn: document.getElementById('btn-limpiar-filtros'),
            prevBtn: document.getElementById('btn-prev-page'),
            nextBtn: document.getElementById('btn-next-page'),
            stats: {
                total: document.getElementById('stat-total-sorteos'),
                boletos: document.getElementById('stat-total-boletos'),
                ganados: document.getElementById('stat-sorteos-ganados'),
                invertido: document.getElementById('stat-total-invertido')
            }
        };
        
        // Verificar que todos los elementos existan
        if (!elementos.container || !elementos.loading || !elementos.list) {
            console.error('Elementos del historial no encontrados');
            return;
        }
        
        /**
         * Carga el historial de sorteos desde el DOM (datos reales renderizados por PHP)
         */
        function loadHistorialData() {
            try {
                // Extraer datos de los elementos .historial-item que ya están en el DOM
                const historialItems = elementos.list.querySelectorAll('.historial-item');
                
                if (historialItems.length === 0) {
                    // No hay historial, dejar vacío
                    historialState.sorteos = [];
                    applyFilters();
                    return;
                }
                
                historialState.sorteos = Array.from(historialItems).map(item => {
                    // Extraer datos de los atributos data-*
                    const idSorteo = item.getAttribute('data-sorteo-id');
                    const titulo = item.getAttribute('data-titulo') || '';
                    const estado = item.getAttribute('data-estado') || 'finalizado';
                    const estadoPago = item.getAttribute('data-estado-pago') || 'pendiente';
                    const fechaStr = item.getAttribute('data-fecha') || '';
                    
                    // Extraer imagen del background-image
                    const imagenDiv = item.querySelector('div[style*="background-image"]');
                    let imagenUrl = '';
                    if (imagenDiv) {
                        const style = imagenDiv.getAttribute('style') || '';
                        const match = style.match(/url\(["']?([^"']+)["']?\)/);
                        if (match) {
                            imagenUrl = match[1];
                        }
                    }
                    
                    // Extraer boletos comprados del texto
                    const boletosText = item.querySelector('p.text-white.font-semibold')?.textContent || '0';
                    const boletosMatch = boletosText.match(/(\d+)/);
                    const boletosComprados = boletosMatch ? parseInt(boletosMatch[1]) : 0;
                    
                    // Extraer total pagado
                    const totalText = item.querySelectorAll('p.text-white.font-semibold')[1]?.textContent || '$0';
                    const totalMatch = totalText.replace(/[^0-9.]/g, '');
                    const totalPagado = parseFloat(totalMatch) || 0;
                    
                    // Calcular precio por boleto
                    const precioBoleto = boletosComprados > 0 ? totalPagado / boletosComprados : 0;
                    
                    // Verificar si es ganador (buscar badge de ganador)
                    const esGanador = item.querySelector('span:has(span.material-symbols-outlined)')?.textContent.includes('Ganador') || false;
                    
                    // Extraer fecha de compra
                    const fechaCompraText = item.querySelector('p.text-white.text-sm.font-medium')?.textContent || '';
                    let fechaCompra = new Date();
                    if (fechaStr) {
                        fechaCompra = new Date(fechaStr);
                    } else if (fechaCompraText) {
                        // Intentar parsear fecha en formato "d M, Y" (ej: "15 Jan, 2024")
                        const fechaParsed = new Date(fechaCompraText);
                        if (!isNaN(fechaParsed.getTime())) {
                            fechaCompra = fechaParsed;
                        }
                    }
                    
                    return {
                        id: idSorteo,
                        id_sorteo: idSorteo,
                        nombre: titulo,
                        titulo: titulo,
                        imagen_url: imagenUrl,
                        estado: estado.toLowerCase(),
                        estado_sorteo: estado,
                        estado_pago: estadoPago.toLowerCase(),
                        estado_pago_transaccion: estadoPago,
                        boletosComprados: boletosComprados,
                        boletos_comprados: boletosComprados,
                        precioBoleto: precioBoleto,
                        precio_boleto: precioBoleto,
                        totalInvertido: totalPagado,
                        total_pagado: totalPagado,
                        esGanador: esGanador,
                        fechaCompra: fechaCompra.toISOString(),
                        fecha_ultima_compra: fechaCompra.toISOString(),
                        fechaFin: fechaCompra.toISOString() // Para compatibilidad con filtros
                    };
                });
                
                // Ordenar por fecha más reciente primero (ya viene ordenado del servidor, pero por si acaso)
                historialState.sorteos.sort((a, b) => new Date(b.fechaCompra) - new Date(a.fechaCompra));
                
                console.log('Historial cargado desde DOM:', historialState.sorteos.length, 'sorteos');
                
            } catch (error) {
                console.error('Error al cargar historial desde DOM:', error);
                historialState.sorteos = [];
            }
            
            // Aplicar filtros iniciales
            applyFilters();
        }
        
        /**
         * Aplica los filtros al historial
         */
        function applyFilters() {
            let filtered = [...historialState.sorteos];
            
            // Filtro de búsqueda
            if (historialState.filters.search.trim()) {
                const searchTerm = historialState.filters.search.toLowerCase();
                filtered = filtered.filter(sorteo => {
                    const nombre = (sorteo.nombre || sorteo.titulo || '').toLowerCase();
                    return nombre.includes(searchTerm);
                });
            }
            
            // Filtro de estado
            if (historialState.filters.estado !== 'todos') {
                filtered = filtered.filter(sorteo => {
                    const estadoSorteo = (sorteo.estado || sorteo.estado_sorteo || '').toLowerCase();
                    return estadoSorteo === historialState.filters.estado.toLowerCase();
                });
            }
            
            // Filtro de fecha
            if (historialState.filters.fecha !== 'todos') {
                const fechaActual = new Date();
                const fechaLimite = new Date();
                
                switch (historialState.filters.fecha) {
                    case 'ultimo-mes':
                        fechaLimite.setMonth(fechaLimite.getMonth() - 1);
                        break;
                    case 'ultimos-3-meses':
                        fechaLimite.setMonth(fechaLimite.getMonth() - 3);
                        break;
                    case 'ultimo-ano':
                        fechaLimite.setFullYear(fechaLimite.getFullYear() - 1);
                        break;
                }
                
                filtered = filtered.filter(sorteo => {
                    const fechaSorteo = new Date(sorteo.fechaCompra || sorteo.fecha_ultima_compra || sorteo.fechaFin);
                    return fechaSorteo >= fechaLimite;
                });
            }
            
            historialState.filteredSorteos = filtered;
            historialState.currentPage = 1; // Resetear a primera página
            
            renderHistorial();
            updateStats();
            updatePagination();
        }
        
        /**
         * Renderiza el historial de sorteos
         */
        function renderHistorial() {
            // Ocultar loading y empty por defecto
            if (elementos.loading) elementos.loading.classList.add('hidden');
            if (elementos.empty) elementos.empty.classList.add('hidden');
            
            const totalItems = historialState.filteredSorteos.length;
            
            // Verificar si hay filtros activos
            const hayFiltros = historialState.filters.search.trim() || 
                               historialState.filters.estado !== 'todos' || 
                               historialState.filters.fecha !== 'todos';
            
            // Si no hay filtros y hay datos originales, mantener el HTML original
            if (!hayFiltros && historialState.sorteos.length > 0) {
                // Mostrar todos los elementos originales
                const originalItems = elementos.list.querySelectorAll('.historial-item');
                originalItems.forEach(item => {
                    item.style.display = '';
                });
                
                // Ocultar paginación si no es necesaria
                if (historialState.sorteos.length <= historialState.itemsPerPage) {
                    if (elementos.pagination) elementos.pagination.classList.add('hidden');
                } else {
                    if (elementos.pagination) elementos.pagination.classList.remove('hidden');
                    updatePagination();
                }
                return;
            }
            
            // Si hay filtros o no hay datos, renderizar dinámicamente
            elementos.list.innerHTML = '';
            
            if (totalItems === 0) {
                if (elementos.empty) elementos.empty.classList.remove('hidden');
                if (elementos.pagination) elementos.pagination.classList.add('hidden');
                return;
            }
            
            // Calcular paginación
            const startIndex = (historialState.currentPage - 1) * historialState.itemsPerPage;
            const endIndex = startIndex + historialState.itemsPerPage;
            const paginatedSorteos = historialState.filteredSorteos.slice(startIndex, endIndex);
            
            // Renderizar cada sorteo
            paginatedSorteos.forEach(sorteo => {
                const card = createSorteoCard(sorteo);
                elementos.list.appendChild(card);
            });
            
            if (elementos.pagination) elementos.pagination.classList.remove('hidden');
        }
        
        /**
         * Crea una tarjeta de sorteo para el historial (usando datos reales)
         */
        function createSorteoCard(sorteo) {
            const card = document.createElement('div');
            card.className = 'historial-item bg-[#151a23] rounded-lg p-4 border border-[#282d39] hover:border-primary/50 transition-colors';
            card.setAttribute('data-sorteo-id', sorteo.id || sorteo.id_sorteo || '');
            card.setAttribute('data-titulo', sorteo.nombre || sorteo.titulo || '');
            card.setAttribute('data-estado', (sorteo.estado || sorteo.estado_sorteo || '').toLowerCase());
            
            const fechaCompra = new Date(sorteo.fechaCompra || sorteo.fecha_ultima_compra || sorteo.fechaFin);
            const fechaFormateada = fechaCompra.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
            
            const estadoSorteo = (sorteo.estado || sorteo.estado_sorteo || 'finalizado').toLowerCase();
            const estadoConfig = {
                'activo': { color: 'text-green-500', bg: 'bg-green-500/10', label: 'Activo', icon: 'schedule' },
                'finalizado': { color: 'text-blue-500', bg: 'bg-blue-500/10', label: 'Finalizado', icon: 'check_circle' },
                'pausado': { color: 'text-yellow-500', bg: 'bg-yellow-500/10', label: 'Pausado', icon: 'pause_circle' },
                'cancelado': { color: 'text-red-500', bg: 'bg-red-500/10', label: 'Cancelado', icon: 'cancel' }
            };
            
            const estado = estadoConfig[estadoSorteo] || estadoConfig.finalizado;
            
            const boletosComprados = sorteo.boletosComprados || sorteo.boletos_comprados || 0;
            const precioBoleto = sorteo.precioBoleto || sorteo.precio_boleto || 0;
            const totalPagado = sorteo.totalInvertido || sorteo.total_pagado || 0;
            const imagenUrl = sorteo.imagen_url || 'https://via.placeholder.com/300x200?text=Sorteo';
            const esGanador = sorteo.esGanador || false;
            const idSorteo = sorteo.id || sorteo.id_sorteo || '';
            
            card.innerHTML = `
                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Imagen del sorteo -->
                    <div class="flex-shrink-0">
                        <div class="w-full sm:w-24 h-24 rounded-lg bg-gray-700 bg-cover bg-center" style="background-image: url('${imagenUrl}');" onerror="this.style.backgroundImage='url(\\'https://via.placeholder.com/300x200?text=Sorteo\\')'"></div>
                    </div>
                    <!-- Información del sorteo -->
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 mb-2">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-white font-semibold text-base mb-1 truncate">${sorteo.nombre || sorteo.titulo || 'Sorteo'}</h4>
                                <div class="flex flex-wrap items-center gap-2 text-sm">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold ${estado.color} ${estado.bg}">
                                        ${estado.label}
                                    </span>
                                    ${esGanador ? `
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-500/10 text-yellow-500">
                                        <span class="material-symbols-outlined text-xs">emoji_events</span>
                                        Ganador
                                    </span>
                                    ` : ''}
                                </div>
                            </div>
                            <div class="flex-shrink-0 text-left sm:text-right">
                                <p class="text-text-secondary text-xs mb-1">Fecha de compra</p>
                                <p class="text-white text-sm font-medium">${fechaFormateada}</p>
                            </div>
                        </div>
                        <!-- Detalles de boletos y pago -->
                        <div class="grid grid-cols-2 gap-4 pt-2 border-t border-[#282d39]">
                            <div>
                                <p class="text-text-secondary text-xs mb-1">Boletos comprados</p>
                                <p class="text-white font-semibold">${boletosComprados} ${boletosComprados === 1 ? 'boleto' : 'boletos'}</p>
                            </div>
                            <div>
                                <p class="text-text-secondary text-xs mb-1">Total pagado</p>
                                <p class="text-white font-semibold">$${totalPagado.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
                            </div>
                        </div>
                    </div>
                    <!-- Acciones -->
                    <div class="flex-shrink-0 flex items-center gap-2">
                        <a href="SorteoClienteDetalles.php?id=${idSorteo}" class="px-4 py-2 bg-primary hover:bg-blue-600 text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                            Ver Detalles
                        </a>
                    </div>
                </div>
            `;
            
            return card;
        }
        
        /**
         * Actualiza las estadísticas (solo si hay filtros aplicados, sino usa las del servidor)
         */
        function updateStats() {
            const sorteos = historialState.filteredSorteos;
            
            // Solo actualizar si hay filtros activos, sino mantener las estadísticas del servidor
            const hayFiltros = historialState.filters.search.trim() || 
                               historialState.filters.estado !== 'todos' || 
                               historialState.filters.fecha !== 'todos';
            
            if (hayFiltros) {
                const totalSorteos = sorteos.length;
                const totalBoletos = sorteos.reduce((sum, s) => sum + (s.boletosComprados || s.boletos_comprados || 0), 0);
                const sorteosGanados = sorteos.filter(s => s.esGanador).length;
                const totalInvertido = sorteos.reduce((sum, s) => sum + (s.totalInvertido || s.total_pagado || 0), 0);
                
                if (elementos.stats.total) elementos.stats.total.textContent = totalSorteos;
                if (elementos.stats.boletos) elementos.stats.boletos.textContent = totalBoletos;
                if (elementos.stats.ganados) elementos.stats.ganados.textContent = sorteosGanados;
                if (elementos.stats.invertido) {
                    elementos.stats.invertido.textContent = '$' + totalInvertido.toLocaleString('es-ES', { 
                        minimumFractionDigits: 2, 
                        maximumFractionDigits: 2 
                    });
                }
            }
            // Si no hay filtros, las estadísticas del servidor ya están correctas en el HTML
        }
        
        /**
         * Actualiza la paginación
         */
        function updatePagination() {
            const total = historialState.filteredSorteos.length;
            const totalPages = Math.ceil(total / historialState.itemsPerPage);
            const start = (historialState.currentPage - 1) * historialState.itemsPerPage + 1;
            const end = Math.min(historialState.currentPage * historialState.itemsPerPage, total);
            
            // Actualizar texto de paginación
            const fromSpan = document.getElementById('pagination-from');
            const toSpan = document.getElementById('pagination-to');
            const totalSpan = document.getElementById('pagination-total');
            
            if (fromSpan) fromSpan.textContent = total > 0 ? start : 0;
            if (toSpan) toSpan.textContent = end;
            if (totalSpan) totalSpan.textContent = total;
            
            // Actualizar botones
            if (elementos.prevBtn) {
                elementos.prevBtn.disabled = historialState.currentPage === 1;
            }
            if (elementos.nextBtn) {
                elementos.nextBtn.disabled = historialState.currentPage >= totalPages;
            }
        }
        
        // Event Listeners
        if (elementos.searchInput) {
            let searchTimeout;
            elementos.searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    historialState.filters.search = elementos.searchInput.value;
                    applyFilters();
                }, 300);
            });
        }
        
        if (elementos.estadoSelect) {
            elementos.estadoSelect.addEventListener('change', function() {
                historialState.filters.estado = this.value;
                applyFilters();
            });
        }
        
        if (elementos.fechaSelect) {
            elementos.fechaSelect.addEventListener('change', function() {
                historialState.filters.fecha = this.value;
                applyFilters();
            });
        }
        
        if (elementos.limpiarBtn) {
            elementos.limpiarBtn.addEventListener('click', function() {
                historialState.filters = {
                    search: '',
                    estado: 'todos',
                    fecha: 'todos'
                };
                
                if (elementos.searchInput) elementos.searchInput.value = '';
                if (elementos.estadoSelect) elementos.estadoSelect.value = 'todos';
                if (elementos.fechaSelect) elementos.fechaSelect.value = 'todos';
                
                applyFilters();
            });
        }
        
        if (elementos.prevBtn) {
            elementos.prevBtn.addEventListener('click', function() {
                if (historialState.currentPage > 1) {
                    historialState.currentPage--;
                    renderHistorial();
                    updatePagination();
                    
                    // Scroll al inicio de la lista
                    elementos.list.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        }
        
        if (elementos.nextBtn) {
            elementos.nextBtn.addEventListener('click', function() {
                const totalPages = Math.ceil(historialState.filteredSorteos.length / historialState.itemsPerPage);
                if (historialState.currentPage < totalPages) {
                    historialState.currentPage++;
                    renderHistorial();
                    updatePagination();
                    
                    // Scroll al inicio de la lista
                    elementos.list.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        }
        
        // Cargar datos iniciales
        loadHistorialData();
    }

    /**
     * Inicializa los botones de mostrar/ocultar contraseña
     */
    function initPasswordToggle() {
        // Función para toggle de contraseña
        function togglePassword(inputId, buttonId) {
            const input = document.getElementById(inputId);
            const button = document.getElementById(buttonId);
            
            if (!input || !button) return;
            
            button.addEventListener('click', function() {
                const icon = button.querySelector('.material-symbols-outlined');
                if (!icon) return;
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.textContent = 'visibility_off';
                    button.setAttribute('aria-label', 'Ocultar contraseña');
                } else {
                    input.type = 'password';
                    icon.textContent = 'visibility';
                    button.setAttribute('aria-label', 'Mostrar contraseña');
                }
            });
        }
        
        // Inicializar toggles para cada campo de contraseña
        togglePassword('input-password-actual', 'toggle-password-actual');
        togglePassword('input-password-nueva', 'toggle-password-nueva');
        togglePassword('input-password-confirmar', 'toggle-password-confirmar');
    }

    // Exportar funciones principales para uso externo si es necesario
    window.AjustesPerfilCliente = {
        updateAvatar: updateAvatar,
        deleteAvatar: deleteAvatar
    };

})();
