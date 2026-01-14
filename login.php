<?php
/**
 * Procesamiento del login de usuarios
 */
require_once 'administrador/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$conn = getDBConnection();

// Obtener datos del formulario
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$rol = $_POST['rol'] ?? 'cliente';

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email y contraseña son requeridos']);
    exit;
}

// Asegurar que el usuario admin existe (debe hacerse antes de buscar)
if ($rol === 'admin' && $email === 'admin@sorteos.com') {
    $adminCreado = asegurarAdminExiste($conn);
    // Si se creó el admin, esperar un momento para que se complete la transacción
    if ($adminCreado) {
        // Pequeña pausa para asegurar que la transacción se complete
        usleep(100000); // 0.1 segundos
    }
}

// Asegurar que el usuario cliente de prueba existe
if ($rol === 'cliente' && $email === 'cliente@test.com') {
    $clienteCreado = asegurarClientePruebaExiste($conn);
    // Si se creó el cliente, esperar un momento para que se complete la transacción
    if ($clienteCreado) {
        // Pequeña pausa para asegurar que la transacción se complete
        usleep(100000); // 0.1 segundos
    }
}

// Buscar usuario por email y rol
$query = "SELECT u.id_usuario, u.email, u.password_hash, u.estado, u.primer_nombre, u.apellido_paterno, r.nombre_rol
          FROM usuarios u
          INNER JOIN roles r ON u.id_rol = r.id_rol
          WHERE u.email = ? AND r.nombre_rol = ?";
          
$stmt = $conn->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en la consulta: ' . $conn->error]);
    exit;
}

$rolNombre = $rol === 'admin' ? 'Administrador' : 'Cliente';
$stmt->bind_param('ss', $email, $rolNombre);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    
    // Registrar login fallido en auditoría (usuario no encontrado)
    registrarAuditoria(
        $conn,
        null, // Usuario desconocido
        'login_fallido',
        'Login Fallido - Usuario no encontrado',
        "Auth Module - Email: $email",
        'error',
        true // Es una alerta
    );
    
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas o usuario no encontrado']);
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Verificar contraseña
if (!isset($user['password_hash']) || !password_verify($password, $user['password_hash'])) {
    // Registrar login fallido en auditoría
    registrarAuditoria(
        $conn,
        null, // Usuario desconocido
        'login_fallido',
        'Login Fallido',
        "Auth Module - Email: $email",
        'error',
        true // Es una alerta
    );
    
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas']);
    exit;
}

// Verificar estado del usuario
if ($user['estado'] !== 'Activo') {
    // Registrar intento de login con cuenta inactiva
    registrarAuditoria(
        $conn,
        $user['id_usuario'],
        'login_fallido',
        'Login Fallido - Cuenta ' . $user['estado'],
        "Auth Module - Email: $email",
        'error',
        true // Es una alerta
    );
    
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Tu cuenta está ' . strtolower($user['estado'])]);
    exit;
}

// Crear sesión
$_SESSION['id_usuario'] = $user['id_usuario'];
$_SESSION['email'] = $user['email'];
$_SESSION['nombre'] = $user['primer_nombre'] . ' ' . $user['apellido_paterno'];
$_SESSION['rol'] = $user['nombre_rol'];

// Registrar login exitoso en auditoría
error_log("Registrando login exitoso para usuario: id={$user['id_usuario']}, email=$email");
$auditoria_result = registrarAuditoria(
    $conn,
    $user['id_usuario'],
    'login_exitoso',
    'Login Exitoso',
    "Auth Module - Email: $email",
    'success',
    false
);

if (!$auditoria_result) {
    error_log("ADVERTENCIA: No se pudo registrar en auditoría el login exitoso del usuario {$user['id_usuario']}");
} else {
    error_log("SUCCESS: Login exitoso registrado en auditoría para usuario {$user['id_usuario']}");
}

// Asegurar que la sesión se guarde
session_write_close();

$redirectUrl = $rol === 'admin' ? 'administrador/DashboardAdmnistrador.php' : 'cliente/DashboardCliente.html';

echo json_encode([
    'success' => true,
    'message' => 'Login exitoso',
    'rol' => $rol,
    'redirect' => $redirectUrl
], JSON_UNESCAPED_SLASHES);

/**
 * Asegura que el usuario cliente de prueba existe con las credenciales especificadas
 */
function asegurarClientePruebaExiste($conn) {
    $email = 'cliente@test.com';
    $password = 'cliente123';
    
    // Verificar si existe el rol Cliente
    $query = "SELECT id_rol FROM roles WHERE nombre_rol = 'Cliente' LIMIT 1";
    $result = $conn->query($query);
    
    $id_rol_cliente = null;
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id_rol_cliente = intval($row['id_rol']);
    } else {
        // Crear el rol Cliente si no existe
        $stmt = $conn->prepare("INSERT INTO roles (nombre_rol) VALUES ('Cliente')");
        if ($stmt->execute()) {
            $id_rol_cliente = $conn->insert_id;
        }
        $stmt->close();
    }
    
    if ($id_rol_cliente === null) {
        return false;
    }
    
    // Verificar si ya existe el usuario cliente
    $stmt = $conn->prepare("SELECT id_usuario, password_hash FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Actualizar contraseña si es necesario
        $row = $result->fetch_assoc();
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $updateStmt = $conn->prepare("UPDATE usuarios SET password_hash = ?, id_rol = ?, estado = 'Activo' WHERE id_usuario = ?");
        $updateStmt->bind_param("sii", $password_hash, $id_rol_cliente, $row['id_usuario']);
        $updateStmt->execute();
        $updateStmt->close();
        
        $stmt->close();
        return true;
    }
    $stmt->close();
    
    // Crear usuario cliente de prueba
    $primer_nombre = 'Cliente';
    $apellido_paterno = 'Prueba';
    $apellido_materno = 'Test'; // Requerido por la BD
    $fecha_nacimiento = '1995-05-15';
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $telefono = '5551234567';
    
    $stmt = $conn->prepare("INSERT INTO usuarios 
        (primer_nombre, apellido_paterno, apellido_materno, fecha_nacimiento, email, password_hash, telefono, id_rol, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Activo')");
    
    $stmt->bind_param("sssssssi", 
        $primer_nombre,
        $apellido_paterno,
        $apellido_materno,
        $fecha_nacimiento,
        $email,
        $password_hash,
        $telefono,
        $id_rol_cliente
    );
    
    if ($stmt->execute()) {
        $id_usuario_nuevo = $conn->insert_id;
        $stmt->close();
        
        // Registrar en auditoría la creación del usuario cliente de prueba
        registrarAuditoria(
            $conn,
            null, // Sistema crea el usuario
            'creacion_usuario',
            'Creación de Usuario - Cliente de Prueba',
            "user: $email (Cliente Prueba)",
            'success',
            false
        );
        
        return true;
    } else {
        error_log("Error al crear cliente de prueba: " . $stmt->error);
        $stmt->close();
        return false;
    }
}

/**
 * Asegura que el usuario administrador existe con las credenciales especificadas
 */
function asegurarAdminExiste($conn) {
    $email = 'admin@sorteos.com';
    $password = 'password123';
    
    // Verificar si existe el rol Administrador
    $query = "SELECT id_rol FROM roles WHERE nombre_rol = 'Administrador' LIMIT 1";
    $result = $conn->query($query);
    
    $id_rol_admin = null;
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id_rol_admin = intval($row['id_rol']);
    } else {
        // Crear el rol Administrador si no existe
        $stmt = $conn->prepare("INSERT INTO roles (nombre_rol) VALUES ('Administrador')");
        if ($stmt->execute()) {
            $id_rol_admin = $conn->insert_id;
        }
        $stmt->close();
    }
    
    if ($id_rol_admin === null) {
        return false;
    }
    
    // Verificar si ya existe el usuario admin
    $stmt = $conn->prepare("SELECT id_usuario, password_hash FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Actualizar contraseña si es necesario
        $row = $result->fetch_assoc();
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $updateStmt = $conn->prepare("UPDATE usuarios SET password_hash = ?, id_rol = ?, estado = 'Activo' WHERE id_usuario = ?");
        $updateStmt->bind_param("sii", $password_hash, $id_rol_admin, $row['id_usuario']);
        $updateStmt->execute();
        $updateStmt->close();
        
        $stmt->close();
        return true;
    }
    $stmt->close();
    
    // Crear usuario administrador
    $primer_nombre = 'Administrador';
    $apellido_paterno = 'Sistema';
    $apellido_materno = 'Admin'; // Requerido por la BD
    $fecha_nacimiento = '1990-01-01';
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $telefono = '';
    
    $stmt = $conn->prepare("INSERT INTO usuarios 
        (primer_nombre, apellido_paterno, apellido_materno, fecha_nacimiento, email, password_hash, telefono, id_rol, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Activo')");
    
    $stmt->bind_param("sssssssi", 
        $primer_nombre,
        $apellido_paterno,
        $apellido_materno,
        $fecha_nacimiento,
        $email,
        $password_hash,
        $telefono,
        $id_rol_admin
    );
    
    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        error_log("Error al crear admin: " . $stmt->error);
        $stmt->close();
        return false;
    }
}

$conn->close();
?>

