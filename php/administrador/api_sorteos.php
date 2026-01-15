<?php
/**
 * API para gestión de sorteos
 * Maneja operaciones CRUD de sorteos
 */

header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Obtener ID de usuario administrador
// Primero intentar desde la sesión, luego buscar un admin en la BD, o crear uno por defecto
$id_admin = null;

// Intentar obtener de la sesión
if (isset($_SESSION['id_usuario'])) {
    $id_admin = intval($_SESSION['id_usuario']);
} else {
    // Buscar un usuario administrador en la base de datos
    $query = "SELECT u.id_usuario 
              FROM usuarios u 
              INNER JOIN roles r ON u.id_rol = r.id_rol 
              WHERE r.nombre_rol = 'Administrador' 
              LIMIT 1";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id_admin = intval($row['id_usuario']);
    } else {
        // Si no hay admin, buscar cualquier usuario o crear uno
        $query = "SELECT id_usuario FROM usuarios LIMIT 1";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $id_admin = intval($row['id_usuario']);
        } else {
            // Si no hay usuarios, crear un administrador por defecto
            $id_admin = crearAdminPorDefecto($conn);
        }
    }
}

// Si aún no tenemos un admin válido, intentar crear uno
if ($id_admin === null) {
    $id_admin = crearAdminPorDefecto($conn);
}

// Si después de todo no tenemos un admin, mostrar error
if ($id_admin === null || $id_admin <= 0) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error: No se pudo identificar o crear un usuario administrador. Por favor, asegúrate de tener usuarios en la base de datos o contacta al administrador del sistema.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    switch ($method) {
        case 'GET':
            if ($action === 'list') {
                getSorteos($conn);
            } elseif ($action === 'get') {
                $id = $_GET['id'] ?? null;
                if ($id) {
                    getSorteo($conn, $id);
                } else {
                    sendError('ID de sorteo requerido', 400);
                }
            } else {
                getSorteos($conn);
            }
            break;
            
        case 'POST':
            if ($action === 'create') {
                createSorteo($conn, $id_admin);
            } elseif ($action === 'update') {
                updateSorteo($conn, $id_admin);
            } elseif ($action === 'delete') {
                deleteSorteo($conn, $id_admin);
            } else {
                sendError('Acción no válida', 400);
            }
            break;
            
        default:
            sendError('Método no permitido', 405);
            break;
    }
} catch (Exception $e) {
    sendError('Error del servidor: ' . $e->getMessage(), 500);
}

/**
 * Obtiene todos los sorteos
 */
function getSorteos($conn) {
    $query = "SELECT 
                s.id_sorteo,
                s.titulo,
                s.descripcion,
                s.precio_boleto,
                s.total_boletos_crear,
                s.fecha_inicio,
                s.fecha_fin,
                s.imagen_url,
                s.estado,
                s.id_creador,
                u.primer_nombre,
                u.apellido_paterno,
                COUNT(b.id_boleto) as boletos_vendidos
              FROM sorteos s
              LEFT JOIN usuarios u ON s.id_creador = u.id_usuario
              LEFT JOIN boletos b ON s.id_sorteo = b.id_sorteo AND b.estado = 'Vendido'
              GROUP BY s.id_sorteo
              ORDER BY s.fecha_inicio DESC";
    
    $result = $conn->query($query);
    
    if (!$result) {
        sendError('Error al obtener sorteos: ' . $conn->error, 500);
        return;
    }
    
    $sorteos = [];
    while ($row = $result->fetch_assoc()) {
        $sorteos[] = formatSorteo($row);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $sorteos
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Obtiene un sorteo específico
 */
function getSorteo($conn, $id) {
    $stmt = $conn->prepare("SELECT 
                s.id_sorteo,
                s.titulo,
                s.descripcion,
                s.precio_boleto,
                s.total_boletos_crear,
                s.fecha_inicio,
                s.fecha_fin,
                s.imagen_url,
                s.estado,
                s.id_creador,
                u.primer_nombre,
                u.apellido_paterno,
                COUNT(b.id_boleto) as boletos_vendidos
              FROM sorteos s
              LEFT JOIN usuarios u ON s.id_creador = u.id_usuario
              LEFT JOIN boletos b ON s.id_sorteo = b.id_sorteo AND b.estado = 'Vendido'
              WHERE s.id_sorteo = ?
              GROUP BY s.id_sorteo");
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'data' => formatSorteo($row)
        ], JSON_UNESCAPED_UNICODE);
    } else {
        sendError('Sorteo no encontrado', 404);
    }
    
    $stmt->close();
}

/**
 * Crea un nuevo sorteo
 */
function createSorteo($conn, $id_admin) {
    // Validar que el id_admin existe en la base de datos
    if ($id_admin === null || $id_admin <= 0) {
        sendError('Error: No se pudo identificar el usuario administrador. Por favor, asegúrate de tener usuarios en la base de datos.', 500);
        return;
    }
    
    // Verificar que el usuario existe
    $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_admin);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        sendError('Error: El usuario administrador no existe en la base de datos. Por favor, crea un usuario administrador primero.', 500);
        return;
    }
    $stmt->close();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validar datos requeridos
    if (!isset($data['titulo']) || !isset($data['fecha_inicio']) || 
        !isset($data['fecha_fin']) || !isset($data['total_boletos_crear'])) {
        sendError('Faltan datos requeridos', 400);
        return;
    }
    
    $titulo = trim($data['titulo']);
    $descripcion = trim($data['descripcion'] ?? '');
    $precio_boleto = floatval($data['precio_boleto'] ?? 0);
    $total_boletos = intval($data['total_boletos_crear']);
    $fecha_inicio = $data['fecha_inicio'];
    $fecha_fin = $data['fecha_fin'];
    // Validar y obtener estado - usar 'Activo' como valor por defecto
    $estado = isset($data['estado']) && !empty($data['estado']) ? trim($data['estado']) : 'Activo';
    // Validar que el estado sea uno de los valores permitidos
    $estados_permitidos = ['Borrador', 'Activo', 'Pausado', 'Finalizado'];
    if (!in_array($estado, $estados_permitidos)) {
        $estado = 'Activo'; // Si no es válido, usar Activo por defecto
    }
    $imagen_url = $data['imagen_url'] ?? null;
    
    // Validar fechas
    $fecha_inicio_dt = new DateTime($fecha_inicio);
    $fecha_fin_dt = new DateTime($fecha_fin);
    
    if ($fecha_fin_dt < $fecha_inicio_dt) {
        sendError('La fecha de fin debe ser posterior a la fecha de inicio', 400);
        return;
    }
    
    // Insertar sorteo
    $stmt = $conn->prepare("INSERT INTO sorteos 
        (titulo, descripcion, precio_boleto, total_boletos_crear, fecha_inicio, fecha_fin, estado, id_creador, imagen_url)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("ssdisssis", 
        $titulo, 
        $descripcion, 
        $precio_boleto, 
        $total_boletos, 
        $fecha_inicio, 
        $fecha_fin, 
        $estado, 
        $id_admin,
        $imagen_url
    );
    
    if ($stmt->execute()) {
        $id_sorteo = $conn->insert_id;
        
        // Crear boletos para el sorteo
        crearBoletos($conn, $id_sorteo, $total_boletos);
        
        // Registrar en auditoría (incluso si falla, no debe detener el proceso)
        error_log("Intentando registrar auditoría: id_admin=$id_admin, id_sorteo=$id_sorteo, titulo=$titulo");
        
        $auditoria_result = registrarAuditoria(
            $conn,
            $id_admin,
            'creacion_sorteo',
            'Creación de Sorteo',
            "Sorteo #$id_sorteo - $titulo",
            'success',
            false
        );
        
        if (!$auditoria_result) {
            // Log del error pero no fallar la creación del sorteo
            error_log("ERROR: No se pudo registrar en auditoría la creación del sorteo #$id_sorteo. id_admin=$id_admin");
        } else {
            error_log("SUCCESS: Auditoría registrada correctamente para sorteo #$id_sorteo");
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Sorteo creado exitosamente',
            'data' => ['id_sorteo' => $id_sorteo]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        sendError('Error al crear sorteo: ' . $conn->error, 500);
    }
    
    $stmt->close();
}

/**
 * Actualiza un sorteo existente
 */
function updateSorteo($conn, $id_admin) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id_sorteo'])) {
        sendError('ID de sorteo requerido', 400);
        return;
    }
    
    $id_sorteo = intval($data['id_sorteo']);
    $titulo = trim($data['titulo']);
    $descripcion = trim($data['descripcion'] ?? '');
    $precio_boleto = floatval($data['precio_boleto'] ?? 0);
    $total_boletos = intval($data['total_boletos_crear']);
    $fecha_inicio = $data['fecha_inicio'];
    $fecha_fin = $data['fecha_fin'];
    // Validar y obtener estado - usar 'Activo' como valor por defecto
    $estado = isset($data['estado']) && !empty($data['estado']) ? trim($data['estado']) : 'Activo';
    // Validar que el estado sea uno de los valores permitidos
    $estados_permitidos = ['Borrador', 'Activo', 'Pausado', 'Finalizado'];
    if (!in_array($estado, $estados_permitidos)) {
        $estado = 'Activo'; // Si no es válido, usar Activo por defecto
    }
    $imagen_url = $data['imagen_url'] ?? null;
    
    // Validar fechas
    $fecha_inicio_dt = new DateTime($fecha_inicio);
    $fecha_fin_dt = new DateTime($fecha_fin);
    
    if ($fecha_fin_dt < $fecha_inicio_dt) {
        sendError('La fecha de fin debe ser posterior a la fecha de inicio', 400);
        return;
    }
    
    // Verificar que el sorteo existe y pertenece al admin
    $stmt = $conn->prepare("SELECT id_sorteo FROM sorteos WHERE id_sorteo = ? AND id_creador = ?");
    $stmt->bind_param("ii", $id_sorteo, $id_admin);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        sendError('Sorteo no encontrado o no tienes permisos para editarlo', 404);
        return;
    }
    $stmt->close();
    
    // Actualizar sorteo
    $stmt = $conn->prepare("UPDATE sorteos SET 
        titulo = ?, 
        descripcion = ?, 
        precio_boleto = ?, 
        total_boletos_crear = ?, 
        fecha_inicio = ?, 
        fecha_fin = ?, 
        estado = ?,
        imagen_url = ?
        WHERE id_sorteo = ?");
    
    $stmt->bind_param("ssdissssi", 
        $titulo, 
        $descripcion, 
        $precio_boleto, 
        $total_boletos, 
        $fecha_inicio, 
        $fecha_fin, 
        $estado,
        $imagen_url,
        $id_sorteo
    );
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Sorteo actualizado exitosamente'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        sendError('Error al actualizar sorteo: ' . $conn->error, 500);
    }
    
    $stmt->close();
}

/**
 * Elimina un sorteo
 */
function deleteSorteo($conn, $id_admin) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id_sorteo'])) {
        sendError('ID de sorteo requerido', 400);
        return;
    }
    
    $id_sorteo = intval($data['id_sorteo']);
    
    // Verificar que el sorteo existe y pertenece al admin
    $stmt = $conn->prepare("SELECT id_sorteo FROM sorteos WHERE id_sorteo = ? AND id_creador = ?");
    $stmt->bind_param("ii", $id_sorteo, $id_admin);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        sendError('Sorteo no encontrado o no tienes permisos para eliminarlo', 404);
        return;
    }
    $stmt->close();
    
    // Eliminar sorteo (los boletos se eliminan en cascada)
    $stmt = $conn->prepare("DELETE FROM sorteos WHERE id_sorteo = ?");
    $stmt->bind_param("i", $id_sorteo);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Sorteo eliminado exitosamente'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        sendError('Error al eliminar sorteo: ' . $conn->error, 500);
    }
    
    $stmt->close();
}

/**
 * Crea los boletos para un sorteo
 * Crea todos los boletos físicamente en la base de datos
 * Usa inserción en lotes para mejor rendimiento
 */
function crearBoletos($conn, $id_sorteo, $total_boletos) {
    if ($total_boletos <= 0) {
        return; // No hay boletos que crear
    }
    
    // Preparar statement una sola vez para mejor rendimiento
    $stmt = $conn->prepare("INSERT INTO boletos (id_sorteo, numero_boleto, estado) VALUES (?, ?, 'Disponible')");
    
    if (!$stmt) {
        error_log("Error al preparar inserción de boletos: " . $conn->error);
        return;
    }
    
    // Insertar todos los boletos
    for ($i = 1; $i <= $total_boletos; $i++) {
        $numero_boleto = str_pad($i, 4, '0', STR_PAD_LEFT);
        $stmt->bind_param("is", $id_sorteo, $numero_boleto);
        
        if (!$stmt->execute()) {
            error_log("Error al insertar boleto #$numero_boleto para sorteo #$id_sorteo: " . $stmt->error);
            // Continuar con el siguiente boleto aunque falle uno
        }
    }
    
    $stmt->close();
}

/**
 * Formatea un sorteo para la respuesta JSON
 */
function formatSorteo($row) {
    $fecha_inicio = new DateTime($row['fecha_inicio']);
    $fecha_fin = new DateTime($row['fecha_fin']);
    
    // Formatear período
    $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    $periodo = sprintf(
        "%02d %s - %02d %s %s",
        $fecha_inicio->format('d'),
        $meses[(int)$fecha_inicio->format('m') - 1],
        $fecha_fin->format('d'),
        $meses[(int)$fecha_fin->format('m') - 1],
        $fecha_fin->format('Y')
    );
    
    $creado_por = trim(($row['primer_nombre'] ?? '') . ' ' . ($row['apellido_paterno'] ?? ''));
    if (empty($creado_por)) {
        $creado_por = 'Admin';
    }
    
    return [
        'id' => (string)$row['id_sorteo'],
        'id_sorteo' => $row['id_sorteo'],
        'name' => $row['titulo'],
        'titulo' => $row['titulo'],
        'descripcion' => $row['descripcion'] ?? '',
        'prize' => $row['descripcion'] ?? 'Premio Principal', // Mantener para compatibilidad con la UI
        'period' => $periodo,
        'fecha_inicio' => $fecha_inicio->format('Y-m-d'),
        'fecha_fin' => $fecha_fin->format('Y-m-d'),
        'status' => $row['estado'],
        'ticketsSold' => (int)($row['boletos_vendidos'] ?? 0),
        'ticketsTotal' => (int)$row['total_boletos_crear'],
        'precio_boleto' => floatval($row['precio_boleto'] ?? 0),
        'createdBy' => $creado_por,
        'imagen_url' => $row['imagen_url'] ?? null
    ];
}

/**
 * Crea un administrador por defecto si no existe ninguno
 */
function crearAdminPorDefecto($conn) {
    // Primero verificar si existe el rol Administrador
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
        return null;
    }
    
    // Verificar si ya existe un usuario con ese email
    $email = 'admin@sorteos.web';
    $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Si ya existe, obtener su ID
        $row = $result->fetch_assoc();
        $id_usuario = intval($row['id_usuario']);
        $stmt->close();
        return $id_usuario;
    }
    $stmt->close();
    
    // Crear usuario administrador por defecto
    $primer_nombre = 'Administrador';
    $apellido_paterno = 'Sistema';
    $apellido_materno = '';
    $fecha_nacimiento = '1990-01-01';
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT); // Contraseña por defecto
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
        $id_usuario = $conn->insert_id;
        $stmt->close();
        return $id_usuario;
    }
    
    $stmt->close();
    return null;
}

/**
 * Envía una respuesta de error
 */
function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$conn->close();
?>
