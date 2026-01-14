<?php
/**
 * API para registro de nuevos clientes
 */
header('Content-Type: application/json; charset=utf-8');
require_once 'administrador/config.php';

$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos del formulario
$nombre_completo = trim($_POST['nombre_completo'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
$acepta_terminos = isset($_POST['acepta_terminos']) && $_POST['acepta_terminos'] === 'on';

// Validar datos
if (empty($nombre_completo) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nombre, email y contraseña son requeridos']);
    exit;
}

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El formato del email no es válido']);
    exit;
}

// Validar que las contraseñas coincidan
if ($password !== $password_confirm) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']);
    exit;
}

// Validar longitud de contraseña
if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
    exit;
}

// Validar términos y condiciones
if (!$acepta_terminos) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Debes aceptar los términos y condiciones']);
    exit;
}

// Verificar que el email no esté en uso
$checkEmail = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
$checkEmail->bind_param("s", $email);
$checkEmail->execute();
$result = $checkEmail->get_result();

if ($result->num_rows > 0) {
    $checkEmail->close();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Este email ya está registrado']);
    exit;
}
$checkEmail->close();

// Separar nombre completo en partes
$nombreParts = explode(' ', $nombre_completo, 4);
$primer_nombre = $nombreParts[0] ?? '';
$segundo_nombre = $nombreParts[1] ?? null;
$apellido_paterno = $nombreParts[2] ?? '';
$apellido_materno = $nombreParts[3] ?? '';

// Si solo hay 2 partes, asumir que es nombre y apellido
if (count($nombreParts) == 2) {
    $primer_nombre = $nombreParts[0];
    $apellido_paterno = $nombreParts[1];
    $segundo_nombre = null;
    $apellido_materno = '';
}

// Si solo hay 1 parte, usar como primer nombre
if (count($nombreParts) == 1) {
    $primer_nombre = $nombreParts[0];
    $apellido_paterno = 'Usuario';
    $segundo_nombre = null;
    $apellido_materno = '';
}

// Obtener ID del rol Cliente
$rolQuery = "SELECT id_rol FROM roles WHERE nombre_rol = 'Cliente' LIMIT 1";
$rolResult = $conn->query($rolQuery);
if ($rolResult->num_rows === 0) {
    // Crear el rol Cliente si no existe
    $stmt = $conn->prepare("INSERT INTO roles (nombre_rol) VALUES ('Cliente')");
    if ($stmt->execute()) {
        $id_rol = $conn->insert_id;
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al crear rol Cliente']);
        exit;
    }
    $stmt->close();
} else {
    $rol = $rolResult->fetch_assoc();
    $id_rol = $rol['id_rol'];
}

// Hash de la contraseña
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Fecha de nacimiento por defecto (el usuario puede cambiarla después)
$fecha_nacimiento = '1990-01-01';

// Insertar usuario
$stmt = $conn->prepare("INSERT INTO usuarios 
    (primer_nombre, segundo_nombre, apellido_paterno, apellido_materno, fecha_nacimiento, email, password_hash, telefono, id_rol, estado) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Activo')");

$stmt->bind_param("sssssssis", 
    $primer_nombre,
    $segundo_nombre,
    $apellido_paterno,
    $apellido_materno,
    $fecha_nacimiento,
    $email,
    $password_hash,
    $telefono,
    $id_rol
);

if ($stmt->execute()) {
    $id_usuario_nuevo = $conn->insert_id;
    $stmt->close();
    
    // Registrar en auditoría (el usuario se registra a sí mismo)
    $nombre_completo_display = trim($primer_nombre . ' ' . $apellido_paterno);
    
    error_log("Registrando auditoría para nuevo usuario: id=$id_usuario_nuevo, email=$email");
    
    // Registrar con el ID del usuario recién creado
    $auditoria_result = registrarAuditoria(
        $conn,
        $id_usuario_nuevo, // El mismo usuario que se registra
        'creacion_usuario',
        'Creación de Usuario',
        "user: $email ($nombre_completo_display)",
        'success',
        false
    );
    
    if (!$auditoria_result) {
        error_log("ADVERTENCIA: No se pudo registrar en auditoría la creación del usuario $id_usuario_nuevo");
        // Intentar de nuevo sin el ID de usuario (como sistema)
        registrarAuditoria(
            $conn,
            null,
            'creacion_usuario',
            'Creación de Usuario',
            "user: $email ($nombre_completo_display)",
            'success',
            false
        );
    } else {
        error_log("SUCCESS: Auditoría registrada correctamente para usuario $id_usuario_nuevo");
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Cuenta creada exitosamente. Ya puedes iniciar sesión.',
        'id_usuario' => $id_usuario_nuevo,
        'auditoria_registrada' => $auditoria_result
    ]);
} else {
    $stmt->close();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al crear cuenta: ' . $conn->error]);
}

$conn->close();
?>

