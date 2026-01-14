<?php
/**
 * Página de Inicio de Sesión
 * Sistema de Sorteos Web
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivos necesarios
require_once 'config/database.php';

// Variables
$mensajeError = '';
$mensajeExito = '';
$rolActual = isset($_GET['rol']) ? $_GET['rol'] : 'cliente';

// Si ya está autenticado, redirigir al dashboard correspondiente
if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
    $rolUsuario = $_SESSION['usuario_rol'] ?? '';
    if ($rolUsuario === 'Administrador') {
        header('Location: administrador/DashboardAdmnistrador.html');
        exit;
    } else {
        header('Location: cliente/DashboardCliente.html');
        exit;
    }
}

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $rolSolicitado = isset($_POST['rol']) ? trim($_POST['rol']) : 'cliente';
    
    // Validaciones
    if (empty($email) || empty($password)) {
        $mensajeError = 'Por favor, completa todos los campos';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensajeError = 'El email proporcionado no es válido';
    } else {
        try {
            $db = getDB();
            
            // Buscar usuario por email
            $stmt = $db->prepare("
                SELECT 
                    u.id_usuario,
                    u.email,
                    u.password_hash,
                    u.primer_nombre,
                    u.segundo_nombre,
                    u.apellido_paterno,
                    u.apellido_materno,
                    u.estado,
                    u.saldo_disponible,
                    u.avatar_url,
                    r.id_rol,
                    r.nombre_rol
                FROM usuarios u
                INNER JOIN roles r ON u.id_rol = r.id_rol
                WHERE u.email = :email
            ");
            
            $stmt->execute([':email' => $email]);
            $usuario = $stmt->fetch();
            
            // Verificar si el usuario existe
            if (!$usuario) {
                $mensajeError = 'Credenciales incorrectas. Verifica tu email y contraseña.';
            }
            // Verificar contraseña
            elseif (!password_verify($password, $usuario['password_hash'])) {
                $mensajeError = 'Credenciales incorrectas. Verifica tu email y contraseña.';
            }
            // Verificar estado del usuario
            elseif ($usuario['estado'] !== 'Activo') {
                $mensajeError = $usuario['estado'] === 'Baneado' 
                    ? 'Tu cuenta ha sido suspendida. Contacta al administrador.' 
                    : 'Tu cuenta está inactiva. Contacta al administrador.';
            }
            // Verificar rol
            else {
                $rolUsuario = $usuario['nombre_rol'];
                if ($rolSolicitado === 'admin' && $rolUsuario !== 'Administrador') {
                    $mensajeError = 'No tienes permisos de administrador';
                } elseif ($rolSolicitado === 'cliente' && $rolUsuario !== 'Cliente') {
                    $mensajeError = 'Esta cuenta no es de cliente';
                } else {
                    // Login exitoso - Iniciar sesión
                    $_SESSION['usuario_id'] = $usuario['id_usuario'];
                    $_SESSION['usuario_email'] = $usuario['email'];
                    $_SESSION['usuario_nombre'] = trim($usuario['primer_nombre'] . ' ' . ($usuario['segundo_nombre'] ?? '') . ' ' . $usuario['apellido_paterno'] . ' ' . $usuario['apellido_materno']);
                    $_SESSION['usuario_rol'] = $rolUsuario;
                    $_SESSION['usuario_id_rol'] = $usuario['id_rol'];
                    $_SESSION['usuario_estado'] = $usuario['estado'];
                    $_SESSION['usuario_saldo'] = floatval($usuario['saldo_disponible']);
                    $_SESSION['usuario_avatar'] = $usuario['avatar_url'] ?? 'default_avatar.png';
                    $_SESSION['login_time'] = time();
                    $_SESSION['is_logged_in'] = true;
                    
                    // Guardar datos en localStorage/sessionStorage (para JavaScript)
                    $datosUsuario = [
                        'nombre' => $_SESSION['usuario_nombre'],
                        'email' => $usuario['email'],
                        'saldo' => $_SESSION['usuario_saldo'],
                        'avatar' => $_SESSION['usuario_avatar'],
                        'tipoUsuario' => $rolUsuario === 'Administrador' ? 'Administrador' : 'Usuario Premium'
                    ];
                    
                    // Redirigir según el rol
                    if ($rolUsuario === 'Administrador') {
                        header('Location: administrador/DashboardAdmnistrador.html');
                        exit;
                    } else {
                        header('Location: cliente/DashboardCliente.html');
                        exit;
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Error en login: " . $e->getMessage());
            $mensajeError = 'Error al procesar la solicitud. Inténtalo más tarde.';
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
<title>Inicio de Sesión - Plataforma de Sorteos</title>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
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
<a href="index.html" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
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
<p class="text-[#9da6b9] text-base font-normal leading-normal">Ingresa tus credenciales para acceder a tu cuenta.</p>
</div>
<!-- Role Tabs -->
<div class="w-full">
<div class="flex border-b border-[#3b4354] gap-8">
<a id="tab-cliente" class="flex flex-1 flex-col items-center justify-center border-b-[3px] <?php echo $rolActual === 'cliente' ? 'border-primary text-white' : 'border-transparent text-[#9da6b9]'; ?> pb-3 pt-2 cursor-pointer transition-colors hover:bg-white/5" href="?rol=cliente">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-lg">person</span>
<p class="text-sm font-bold leading-normal tracking-[0.015em]">Soy Cliente</p>
</div>
</a>
<a id="tab-admin" class="flex flex-1 flex-col items-center justify-center border-b-[3px] <?php echo $rolActual === 'admin' ? 'border-primary text-white' : 'border-transparent text-[#9da6b9]'; ?> pb-3 pt-2 cursor-pointer transition-colors hover:bg-white/5" href="?rol=admin">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-lg">admin_panel_settings</span>
<p class="text-sm font-bold leading-normal tracking-[0.015em]">Soy Admin</p>
</div>
</a>
</div>
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
<input type="hidden" name="rol" value="<?php echo htmlspecialchars($rolActual); ?>"/>
<!-- Email Input -->
<label class="flex flex-col gap-2">
<p class="text-white text-sm font-medium leading-normal">Correo Electrónico / Usuario</p>
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
<input name="password" class="form-input flex w-full resize-none overflow-hidden rounded-lg text-white placeholder:text-[#9da6b9] bg-input-dark border border-transparent focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none h-12 pl-12 pr-4 text-base font-normal leading-normal transition-all" placeholder="••••••••" type="password" required/>
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
                                    <a class="font-bold text-primary hover:text-blue-400 transition-colors ml-1" href="CrearCuenta.html">Regístrate aquí</a>
</p>
</div>
</form>
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
</body>
</html>
