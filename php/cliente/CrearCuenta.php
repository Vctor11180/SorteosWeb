<?php
/**
 * CrearCuenta - Registro de Usuarios
 * Sistema de Sorteos Web
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si ya está autenticado, redirigir al dashboard
if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
    $rolUsuario = $_SESSION['usuario_rol'] ?? '';
    if ($rolUsuario === 'Administrador') {
        header('Location: ../administrador/DashboardAdmnistrador.php');
        exit;
    } else {
        header('Location: DashboardCliente.php');
        exit;
    }
}

// Incluir archivos necesarios
require_once __DIR__ . '/config/database.php';

// Variables
$mensajeError = '';
$mensajeExito = '';
$valoresFormulario = [
    'nombre_completo' => '',
    'email' => '',
    'telefono' => ''
];

// Procesar formulario de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registro'])) {
    $nombreCompleto = trim($_POST['nombre_completo'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $aceptaTerminos = isset($_POST['acepta_terminos']);
    
    // Guardar valores para mantenerlos en el formulario
    $valoresFormulario = [
        'nombre_completo' => $nombreCompleto,
        'email' => $email,
        'telefono' => $telefono
    ];
    
    // Validaciones
    if (empty($nombreCompleto) || empty($email) || empty($password) || empty($passwordConfirm)) {
        $mensajeError = 'Por favor, completa todos los campos obligatorios';
    } elseif (strlen($nombreCompleto) < 3) {
        $mensajeError = 'El nombre debe tener al menos 3 caracteres';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensajeError = 'El email proporcionado no es válido';
    } elseif (strlen($password) < 6) {
        $mensajeError = 'La contraseña debe tener al menos 6 caracteres';
    } elseif ($password !== $passwordConfirm) {
        $mensajeError = 'Las contraseñas no coinciden';
    } elseif (!$aceptaTerminos) {
        $mensajeError = 'Debes aceptar los términos y condiciones para registrarte';
    } else {
        try {
            $db = getDB();
            
            // Verificar si el email ya existe
            $stmt = $db->prepare("SELECT id_usuario FROM usuarios WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $usuarioExistente = $stmt->fetch();
            
            if ($usuarioExistente) {
                $mensajeError = 'Este email ya está registrado. Por favor, inicia sesión o usa otro email.';
            } else {
                // Obtener el rol de Cliente
                $stmt = $db->prepare("SELECT id_rol FROM roles WHERE nombre_rol = 'Cliente'");
                $stmt->execute();
                $rolCliente = $stmt->fetch();
                
                if (!$rolCliente) {
                    // Crear roles si no existen
                    $db->exec("INSERT INTO roles (nombre_rol) VALUES ('Administrador'), ('Cliente')");
                    $stmt = $db->prepare("SELECT id_rol FROM roles WHERE nombre_rol = 'Cliente'");
                    $stmt->execute();
                    $rolCliente = $stmt->fetch();
                }
                
                $idRolCliente = $rolCliente['id_rol'];
                
                // Separar nombre completo en partes
                $partesNombre = explode(' ', $nombreCompleto, 4);
                $primerNombre = $partesNombre[0] ?? '';
                $segundoNombre = $partesNombre[1] ?? '';
                $apellidoPaterno = $partesNombre[2] ?? '';
                $apellidoMaterno = $partesNombre[3] ?? '';
                
                // Si solo hay 2 partes, asumir que es nombre y apellido
                if (count($partesNombre) == 2) {
                    $primerNombre = $partesNombre[0];
                    $apellidoPaterno = $partesNombre[1];
                    $segundoNombre = '';
                    $apellidoMaterno = '';
                }
                
                // Hash de la contraseña
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                
                // Calcular fecha de nacimiento (asumir 18 años como mínimo)
                $fechaNacimiento = date('Y-m-d', strtotime('-18 years'));
                
                // Insertar nuevo usuario
                $stmt = $db->prepare("
                    INSERT INTO usuarios (
                        primer_nombre,
                        segundo_nombre,
                        apellido_paterno,
                        apellido_materno,
                        fecha_nacimiento,
                        email,
                        password_hash,
                        telefono,
                        id_rol,
                        estado,
                        saldo_disponible
                    ) VALUES (
                        :primer_nombre,
                        :segundo_nombre,
                        :apellido_paterno,
                        :apellido_materno,
                        :fecha_nacimiento,
                        :email,
                        :password_hash,
                        :telefono,
                        :id_rol,
                        'Activo',
                        0.00
                    )
                ");
                
                $stmt->execute([
                    ':primer_nombre' => $primerNombre,
                    ':segundo_nombre' => $segundoNombre ?: null,
                    ':apellido_paterno' => $apellidoPaterno ?: $primerNombre,
                    ':apellido_materno' => $apellidoMaterno ?: '',
                    ':fecha_nacimiento' => $fechaNacimiento,
                    ':email' => $email,
                    ':password_hash' => $passwordHash,
                    ':telefono' => $telefono ?: null,
                    ':id_rol' => $idRolCliente
                ]);
                
                $mensajeExito = '¡Cuenta creada exitosamente! Redirigiendo al inicio de sesión...';
                
                // Redirigir después de 2 segundos
                header("Refresh: 2; url=InicioSesion.php");
            }
        } catch (PDOException $e) {
            error_log("Error en registro: " . $e->getMessage());
            $mensajeError = 'Error al procesar el registro. Inténtalo más tarde.';
        } catch (Exception $e) {
            error_log("Error general en registro: " . $e->getMessage());
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
<title>Registro de Usuario - Plataforma de Sorteos</title>
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
                        "primary": "#2563eb",
                        "background-light": "#f6f6f8",
                        "background-dark": "#111621",
                        "surface-dark": "#1a202c",
                        "input-dark": "#282d39",
                        "error": "#EF4444",
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
<div class="absolute inset-0 pointer-events-none overflow-hidden">
<div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] rounded-full bg-primary/10 blur-3xl"></div>
<div class="absolute bottom-[-10%] left-[-5%] w-[500px] h-[500px] rounded-full bg-primary/5 blur-3xl"></div>
</div>
<div class="layout-container flex h-full grow flex-col z-10">
<header class="flex items-center justify-center py-6 px-4 sm:px-10">
<div class="flex items-center gap-3 text-white">
<a href="../index.php" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
<div class="flex items-center justify-center size-10 rounded-lg bg-primary/20 text-primary">
<span class="material-symbols-outlined text-3xl">confirmation_number</span>
</div>
<h2 class="text-white text-xl font-bold leading-tight tracking-tight">Plataforma de Sorteos</h2>
</a>
</div>
</header>
<main class="flex flex-1 justify-center items-center px-4 py-5">
<div class="layout-content-container flex flex-col max-w-[520px] w-full flex-1">
<div class="flex flex-col gap-6 sm:p-8 sm:bg-[#161b26] sm:rounded-xl sm:border sm:border-[#282d39]">
<div class="flex flex-col gap-2 text-center sm:text-left">
<h1 class="text-white text-3xl font-black leading-tight tracking-[-0.033em]">Crear cuenta</h1>
<p class="text-[#9da6b9] text-base font-normal leading-normal">Únete a nosotros para participar en los mejores sorteos.</p>
</div>

<?php if ($mensajeError): ?>
<div class="bg-red-500/10 border border-red-500/50 rounded-lg p-4 text-red-400 text-sm">
<span class="material-symbols-outlined align-middle mr-2">error</span>
<?php echo htmlspecialchars($mensajeError); ?>
</div>
<?php endif; ?>

<?php if ($mensajeExito): ?>
<div class="bg-green-500/10 border border-green-500/50 rounded-lg p-4 text-green-400 text-sm">
<span class="material-symbols-outlined align-middle mr-2">check_circle</span>
<?php echo htmlspecialchars($mensajeExito); ?>
</div>
<?php endif; ?>

<form method="POST" action="" class="flex flex-col gap-4 mt-2">
<input type="hidden" name="registro" value="1">
<label class="flex flex-col gap-2">
<p class="text-white text-sm font-medium leading-normal">Nombre completo</p>
<div class="relative">
<div class="absolute left-4 top-1/2 -translate-y-1/2 text-[#9da6b9]">
<span class="material-symbols-outlined">person</span>
</div>
<input name="nombre_completo" class="form-input flex w-full resize-none overflow-hidden rounded-lg text-white placeholder:text-[#9da6b9] bg-input-dark border border-transparent focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none h-11 pl-12 pr-4 text-sm font-normal leading-normal transition-all" placeholder="Juan Pérez" type="text" required value="<?php echo htmlspecialchars($valoresFormulario['nombre_completo']); ?>"/>
</div>
</label>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<label class="flex flex-col gap-2">
<p class="text-white text-sm font-medium leading-normal">Correo Electrónico</p>
<div class="relative">
<div class="absolute left-4 top-1/2 -translate-y-1/2 text-[#9da6b9]">
<span class="material-symbols-outlined">mail</span>
</div>
<input name="email" class="form-input flex w-full resize-none overflow-hidden rounded-lg text-white placeholder:text-[#9da6b9] bg-input-dark border border-transparent focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none h-11 pl-12 pr-4 text-sm font-normal leading-normal transition-all" placeholder="juan@ejemplo.com" type="email" required value="<?php echo htmlspecialchars($valoresFormulario['email']); ?>"/>
</div>
</label>
<label class="flex flex-col gap-2">
<p class="text-white text-sm font-medium leading-normal">Teléfono</p>
<div class="relative">
<div class="absolute left-4 top-1/2 -translate-y-1/2 text-[#9da6b9]">
<span class="material-symbols-outlined">smartphone</span>
</div>
<input name="telefono" class="form-input flex w-full resize-none overflow-hidden rounded-lg text-white placeholder:text-[#9da6b9] bg-input-dark border border-transparent focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none h-11 pl-12 pr-4 text-sm font-normal leading-normal transition-all" placeholder="+52 123 456 7890" type="tel" value="<?php echo htmlspecialchars($valoresFormulario['telefono']); ?>"/>
</div>
</label>
</div>
<label class="flex flex-col gap-2">
<p class="text-white text-sm font-medium leading-normal">Contraseña</p>
<div class="relative">
<div class="absolute left-4 top-1/2 -translate-y-1/2 text-[#9da6b9]">
<span class="material-symbols-outlined">lock</span>
</div>
<input name="password" class="form-input flex w-full resize-none overflow-hidden rounded-lg text-white placeholder:text-[#9da6b9] bg-input-dark border border-transparent focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none h-11 pl-12 pr-4 text-sm font-normal leading-normal transition-all" placeholder="••••••••" type="password" required minlength="6"/>
</div>
</label>
<label class="flex flex-col gap-2">
<p class="text-white text-sm font-medium leading-normal">Confirmar Contraseña</p>
<div class="relative">
<div class="absolute left-4 top-1/2 -translate-y-1/2 text-[#9da6b9]">
<span class="material-symbols-outlined">lock_reset</span>
</div>
<input name="password_confirm" class="form-input flex w-full resize-none overflow-hidden rounded-lg text-white placeholder:text-[#9da6b9] bg-input-dark border border-transparent focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none h-11 pl-12 pr-4 text-sm font-normal leading-normal transition-all" placeholder="••••••••" type="password" required minlength="6"/>
</div>
</label>
<label class="flex items-start gap-3 cursor-pointer mt-1">
<div class="relative flex items-center">
<input name="acepta_terminos" class="peer h-5 w-5 cursor-pointer appearance-none rounded border border-[#3b4354] bg-[#282d39] checked:border-primary checked:bg-primary focus:ring-0 focus:ring-offset-0 transition-all" type="checkbox" required/>
<span class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-white opacity-0 peer-checked:opacity-100">
<svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
<path clip-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" fill-rule="evenodd"></path>
</svg>
</span>
</div>
<span class="text-sm text-[#9da6b9] leading-tight">
He leído y acepto los <a href="TerminosCondicionesCliente.php" class="text-primary hover:text-blue-400 hover:underline" target="_blank">Términos y Condiciones</a> y la <a href="#" class="text-primary hover:text-blue-400 hover:underline">Política de Privacidad</a>.
</span>
</label>
<button type="submit" class="flex w-full cursor-pointer items-center justify-center overflow-hidden rounded-lg h-12 px-4 bg-primary hover:bg-blue-600 transition-colors text-white text-base font-bold leading-normal tracking-[0.015em] shadow-lg shadow-blue-900/20 mt-4">
<span class="truncate">Registrarse</span>
</button>
<div class="text-center pt-2">
<p class="text-[#9da6b9] text-sm">
¿Ya tienes una cuenta? 
<a href="InicioSesion.php" class="font-bold text-primary hover:text-blue-400 transition-colors ml-1">Iniciar sesión</a>
</p>
</div>
</form>
</div>
<div class="mt-10 flex justify-center gap-6 text-[#9da6b9] text-xs">
<a href="TerminosCondicionesCliente.php" class="hover:text-white transition-colors">Términos y Condiciones</a>
<a href="#" class="hover:text-white transition-colors">Política de Privacidad</a>
<a href="ContactoSoporteCliente.php" class="hover:text-white transition-colors">Ayuda</a>
</div>
</div>
</main>
</div>
</div>
</body>
</html>
