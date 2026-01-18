<?php
/**
 * Página de Inicio de Sesión Unificada
 * Sistema de Sorteos Web
 */

// Habilitar errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivos necesarios
$configPath = __DIR__ . '/../config/database.php';
if (!file_exists($configPath)) {
    die("ERROR: No se encontró el archivo de configuración en: " . htmlspecialchars($configPath));
}
require_once $configPath;

// Variables
$mensajeError = '';
$mensajeExito = '';

// Permitir forzar logout con parámetro ?logout=1
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-42000, '/');
    }
    session_destroy();
    session_start();
    $mensajeExito = 'Sesión cerrada correctamente';
}

// Mensajes de error por parámetros GET
if (isset($_GET['error']) && $_GET['error'] === 'sesion_invalida') {
    $mensajeError = 'Tu sesión ha expirado. Por favor ingresa nuevamente.';
}
if (isset($_GET['error']) && $_GET['error'] === 'datos_no_encontrados') {
    $mensajeError = 'No se pudieron obtener tus datos. Por favor, intenta iniciar sesión nuevamente.';
}

// Si ya está autenticado, redirigir al dashboard correspondiente
if (!isset($_GET['logout']) && isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
    if (isset($_SESSION['usuario_rol'])) {
        $rolUsuario = $_SESSION['usuario_rol'];
        if ($rolUsuario === 'Administrador') {
            header('Location: ../../administrador/DashboardAdmnistrador.php');
            exit;
        } else {
            header('Location: ../pages/DashboardCliente.php');
            exit;
        }
    }
}

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validaciones básicas
    if (empty($email) || empty($password)) {
        $mensajeError = 'Por favor, completa todos los campos';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensajeError = 'El email proporcionado no es válido';
    } else {
        try {
            $db = getDB();
            
            // Buscar usuario por email y obtener su rol
            $querySQL = "SELECT 
                    u.id_usuario,
                    u.email,
                    u.password_hash,
                    u.primer_nombre,
                    u.apellido_paterno,
                    u.apellido_materno,
                    u.estado,
                    r.id_rol,
                    r.nombre_rol
                FROM usuarios u
                INNER JOIN roles r ON u.id_rol = r.id_rol
                WHERE u.email = :email";
            
            $stmt = $db->prepare($querySQL);
            $stmt->execute([':email' => $email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar si el usuario existe
            if (!$usuario) {
                // Mensaje genérico por seguridad
                $mensajeError = 'Credenciales incorrectas. Verifica tu email y contraseña.';
                error_log("Login fallido: Usuario no encontrado - Email: {$email}");
            }
            // Verificar contraseña
            elseif (!password_verify($password, $usuario['password_hash'])) {
                // Verificar si es un hash antiguo/inválido
                $hash = $usuario['password_hash'];
                $isValidHash = (substr($hash, 0, 4) === '$2y$' || 
                               substr($hash, 0, 4) === '$2a$' || 
                               substr($hash, 0, 4) === '$2b$');
                
                if (!$isValidHash) {
                    $mensajeError = 'Error: Formato de contraseña no válido. Contacta soporte.';
                    error_log("Hash inválido para usuario {$email}");
                } else {
                    $mensajeError = 'Credenciales incorrectas. Verifica tu email y contraseña.';
                    error_log("Login fallido: Contraseña incorrecta - Email: {$email}");
                }
            }
            // Verificar estado del usuario
            elseif ($usuario['estado'] !== 'Activo') {
                $mensajeError = $usuario['estado'] === 'Baneado' 
                    ? 'Tu cuenta ha sido suspendida. Contacta al administrador.' 
                    : 'Tu cuenta está inactiva. Contacta al administrador.';
            }
            else {
                // Login exitoso
                $rolUsuario = $usuario['nombre_rol'];
                
                // Regenerar ID de sesión por seguridad
                session_regenerate_id(true);
                
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_nombre'] = trim($usuario['primer_nombre'] . ' ' . ($usuario['apellido_paterno'] ?? ''));
                $_SESSION['usuario_rol'] = $rolUsuario;
                $_SESSION['usuario_id_rol'] = $usuario['id_rol'];
                $_SESSION['usuario_estado'] = $usuario['estado'];
                $_SESSION['login_time'] = time();
                $_SESSION['is_logged_in'] = true;
                
                // Redirigir según el rol detectado
                error_log("Login exitoso - Rol: {$rolUsuario}, Email: {$email}");
                
                if ($rolUsuario === 'Administrador') {
                    header('Location: ../../administrador/DashboardAdmnistrador.php');
                    exit;
                } else {
                    header('Location: ../pages/DashboardCliente.php');
                    exit;
                }
            }
        } catch (PDOException $e) {
            error_log("Error DB en login: " . $e->getMessage());
            $mensajeError = 'Error de conexión. Inténtalo más tarde.';
        } catch (Exception $e) {
            error_log("Error general en login: " . $e->getMessage());
            $mensajeError = 'Error inesperado. Inténtalo más tarde.';
        }
    }
}
?>
<!DOCTYPE html>
<html class="dark" lang="es">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Iniciar Sesión - Plataforma de Sorteos</title>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#2463eb",
                        "background-light": "#f6f6f8",
                        "background-dark": "#111621",
                        "surface-dark": "#1a202c",
                        "input-dark": "#282d39",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
                },
            },
        }
    </script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-slate-900 dark:text-white antialiased min-h-screen flex flex-col">
<div class="relative flex min-h-screen w-full flex-col overflow-x-hidden">
    <!-- Background Decoration -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] rounded-full bg-primary/10 blur-3xl"></div>
        <div class="absolute bottom-[-10%] left-[-5%] w-[500px] h-[500px] rounded-full bg-primary/5 blur-3xl"></div>
    </div>

    <div class="layout-container flex h-full grow flex-col z-10">
        <!-- Header / Logo Area -->
        <header class="flex items-center justify-center py-6 px-4 sm:px-10">
            <div class="flex items-center gap-3 text-white">
                <a href="../../html-prototypes/index.html" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
                    <div class="flex items-center justify-center size-10 rounded-lg bg-primary/20 text-primary">
                        <span class="material-symbols-outlined text-3xl">confirmation_number</span>
                    </div>
                    <h2 class="text-white text-xl font-bold leading-tight tracking-tight">Plataforma de Sorteos</h2>
                </a>
            </div>
        </header>

        <!-- Main Content: Centered Login Card -->
        <main class="flex flex-1 justify-center items-center px-4 py-5">
            <div class="layout-content-container flex flex-col max-w-[480px] w-full flex-1">
                <!-- Login Card -->
                <div class="flex flex-col gap-6 sm:p-8 sm:bg-[#161b26] sm:rounded-xl sm:border sm:border-[#282d39]">
                    
                    <!-- Heading -->
                    <div class="flex flex-col gap-2 text-center sm:text-left">
                        <h1 class="text-white text-3xl font-black leading-tight tracking-[-0.033em]">Bienvenido de nuevo</h1>
                        <p class="text-[#9da6b9] text-base font-normal leading-normal">Ingresa tus credenciales para acceder (Cliente o Administrador).</p>
                    </div>

                    <!-- Mensaje de Error/Éxito -->
                    <?php if (!empty($mensajeError)): ?>
                    <div class="flex items-center gap-2 text-red-500 bg-red-500/10 p-3 rounded-lg text-sm border border-red-500/20">
                        <span class="material-symbols-outlined text-lg">error</span>
                        <p><?php echo htmlspecialchars($mensajeError); ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($mensajeExito)): ?>
                    <div class="flex items-center gap-2 text-green-500 bg-green-500/10 p-3 rounded-lg text-sm border border-green-500/20">
                        <span class="material-symbols-outlined text-lg">check_circle</span>
                        <p><?php echo htmlspecialchars($mensajeExito); ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <form method="POST" action="" class="flex flex-col gap-5 mt-2">
                        <input type="hidden" name="login" value="1"/>
                        
                        <!-- Email Input -->
                        <label class="flex flex-col gap-2">
                            <p class="text-white text-sm font-medium leading-normal">Correo Electrónico</p>
                            <div class="relative">
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-[#9da6b9]">
                                    <span class="material-symbols-outlined">mail</span>
                                </div>
                                <input name="email" class="form-input flex w-full resize-none overflow-hidden rounded-lg text-white placeholder:text-[#9da6b9] bg-input-dark border border-transparent focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none h-12 pl-12 pr-4 text-base font-normal leading-normal transition-all" placeholder="ejemplo@correo.com" type="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required/>
                            </div>
                        </label>

                        <!-- Password Input -->
                        <label class="flex flex-col gap-2">
                            <div class="flex justify-between items-center">
                                <p class="text-white text-sm font-medium leading-normal">Contraseña</p>
                            </div>
                            <div class="relative">
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-[#9da6b9]">
                                    <span class="material-symbols-outlined">lock</span>
                                </div>
                                <input id="input-password-login" name="password" class="form-input flex w-full resize-none overflow-hidden rounded-lg text-white placeholder:text-[#9da6b9] bg-input-dark border border-transparent focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none h-12 pl-12 pr-12 text-base font-normal leading-normal transition-all" placeholder="••••••••" type="password" required/>
                                <button type="button" id="toggle-password-login" class="absolute right-4 top-1/2 -translate-y-1/2 text-[#9da6b9] hover:text-white transition-colors cursor-pointer" aria-label="Mostrar contraseña">
                                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                                </button>
                            </div>
                            <div class="flex justify-end mt-1">
                                <a class="text-sm font-medium text-primary hover:text-blue-400 transition-colors" href="#">¿Olvidaste tu contraseña?</a>
                            </div>
                        </label>

                        <!-- Submit Button -->
                        <button type="submit" class="flex w-full cursor-pointer items-center justify-center overflow-hidden rounded-lg h-12 px-4 bg-primary hover:bg-blue-600 transition-colors text-white text-base font-bold leading-normal tracking-[0.015em] shadow-lg shadow-blue-900/20 mt-2">
                            <span class="truncate">Iniciar Sesión</span>
                        </button>

                        <!-- Register Link -->
                        <div class="text-center pt-2">
                            <p class="text-[#9da6b9] text-sm">
                                ¿No tienes una cuenta? 
                                <a class="font-bold text-primary hover:text-blue-400 transition-colors ml-1" href="CrearCuenta.php">Regístrate aquí</a>
                            </p>
                        </div>
                        <!-- Google Sign In -->
                        <div class="relative my-4">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-[#282d39]"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-2 bg-[#161b26] text-[#9da6b9]">O continúa con</span>
                            </div>
                        </div>

                        <div id="g_id_onload"
                             data-client_id="776005870524-pthjf47lsf07l1ck5o03660cc9malctd.apps.googleusercontent.com"
                             data-context="signin"
                             data-ux_mode="popup"
                             data-callback="handleCredentialResponse"
                             data-auto_prompt="false">
                        </div>

                        <div class="g_id_signin"
                             data-type="standard"
                             data-shape="rectangular"
                             data-theme="filled_black"
                             data-text="signin_with"
                             data-size="large"
                             data-logo_alignment="left"
                             data-width="100%">
                        </div>
                    </form>

    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
        function handleCredentialResponse(response) {
            // Enviar el token al backend
            fetch('google_auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ credential: response.credential })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '../pages/DashboardCliente.php';
                } else {
                    alert('Error al iniciar sesión con Google: ' + (data.error || 'Error desconocido'));
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Error de conexión al procesar Google Login');
            });
        }
    </script>
                </div>

                <!-- Footer Links -->
                <div class="mt-10 flex justify-center gap-6 text-[#9da6b9] text-xs">
                    <a class="hover:text-white transition-colors" href="#">Términos y Condiciones</a>
                    <a class="hover:text-white transition-colors" href="#">Política de Privacidad</a>
                    <a class="hover:text-white transition-colors" href="#">Ayuda</a>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Función para toggle de mostrar/ocultar contraseña
(function() {
    'use strict';
    function initPasswordToggle() {
        const input = document.getElementById('input-password-login');
        const button = document.getElementById('toggle-password-login');
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
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPasswordToggle);
    } else {
        initPasswordToggle();
    }
})();
</script>
</body>
</html>
