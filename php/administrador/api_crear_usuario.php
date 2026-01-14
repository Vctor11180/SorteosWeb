<?php
/**
 * API para crear nuevos usuarios
 */
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener ID del administrador que crea el usuario
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$id_admin = $_SESSION['id_usuario'] ?? null;

// Obtener datos del formulario
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$estado = $_POST['estado'] ?? 'active';

// Validar datos
if (empty($nombre) || empty($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nombre y email son requeridos']);
    exit;
}

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El formato del email no es válido']);
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
    echo json_encode(['success' => false, 'message' => 'Este email ya está en uso']);
    exit;
}
$checkEmail->close();

// Separar nombre completo en partes
$nombreParts = explode(' ', $nombre, 4);
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

// Obtener ID del rol Cliente
$rolQuery = "SELECT id_rol FROM roles WHERE nombre_rol = 'Cliente' LIMIT 1";
$rolResult = $conn->query($rolQuery);
if ($rolResult->num_rows === 0) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: Rol Cliente no encontrado']);
    exit;
}
$rol = $rolResult->fetch_assoc();
$id_rol = $rol['id_rol'];

// Mapear estado
$estado_db = 'Activo';
if ($estado === 'inactive') $estado_db = 'Inactivo';
if ($estado === 'pending') $estado_db = 'Inactivo';

// Crear contraseña temporal (el usuario deberá cambiarla)
$password_temp = bin2hex(random_bytes(8));
$password_hash = password_hash($password_temp, PASSWORD_DEFAULT);

// Insertar usuario
$stmt = $conn->prepare("INSERT INTO usuarios 
    (primer_nombre, segundo_nombre, apellido_paterno, apellido_materno, fecha_nacimiento, email, password_hash, telefono, id_rol, estado) 
    VALUES (?, ?, ?, ?, '1990-01-01', ?, ?, ?, ?, ?)");

$stmt->bind_param("ssssssis", 
    $primer_nombre,
    $segundo_nombre,
    $apellido_paterno,
    $apellido_materno,
    $email,
    $password_hash,
    $telefono,
    $id_rol,
    $estado_db
);

if ($stmt->execute()) {
    $id_usuario_nuevo = $conn->insert_id;
    $stmt->close();
    
    // Registrar en auditoría
    $nombre_completo = trim($primer_nombre . ' ' . $apellido_paterno);
    registrarAuditoria(
        $conn,
        $id_admin,
        'creacion_usuario',
        'Creación de Usuario',
        "user: $email ($nombre_completo)",
        'success',
        false
    );
    
    echo json_encode([
        'success' => true,
        'message' => 'Usuario creado exitosamente',
        'id_usuario' => $id_usuario_nuevo
    ]);
} else {
    $stmt->close();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al crear usuario: ' . $conn->error]);
}

$conn->close();
?>

