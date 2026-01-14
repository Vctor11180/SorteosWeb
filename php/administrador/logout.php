<?php
/**
 * Archivo para cerrar sesión (Administrador)
 * Destruye la sesión del servidor y redirige al login
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Limpiar todas las variables de sesión
$_SESSION = array();

// Si se desea destruir la cookie de sesión también
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Redirigir al login del cliente (o al index principal)
header('Location: ../cliente/InicioSesion.php');
exit;
