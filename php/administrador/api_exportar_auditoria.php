<?php
/**
 * API para exportar registros de auditoría a CSV
 */

require_once 'config.php';

// Obtener parámetros de filtrado
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$tipoFilter = isset($_GET['tipo']) ? trim($_GET['tipo']) : 'all';
$estadoFilter = isset($_GET['estado']) ? trim($_GET['estado']) : 'all';
$alertsOnly = isset($_GET['alerts']) && $_GET['alerts'] === '1';
$fechaInicio = isset($_GET['fecha_inicio']) ? trim($_GET['fecha_inicio']) : '';
$fechaFin = isset($_GET['fecha_fin']) ? trim($_GET['fecha_fin']) : '';

$conn = getDBConnection();

// Detectar estructura de tabla (igual que en el archivo principal)
// Primero verificar auditoria_acciones (tabla principal)
$query = "SHOW TABLES LIKE 'auditoria_acciones'";
$tableCheck = $conn->query($query);
$tableName = 'auditoria_admin'; // Fallback por defecto
$hasExtendedFields = false;

if ($tableCheck && $tableCheck->num_rows > 0) {
    $tableName = 'auditoria_acciones';
    $hasExtendedFields = true;
} else {
    // Verificar logs_auditoria como segunda opción
    $query = "SHOW TABLES LIKE 'logs_auditoria'";
    $tableCheck = $conn->query($query);
    if ($tableCheck && $tableCheck->num_rows > 0) {
        $tableName = 'logs_auditoria';
        $hasExtendedFields = true;
    } else {
        // Verificar si auditoria_admin tiene campos extendidos
        $checkFields = "SHOW COLUMNS FROM auditoria_admin LIKE 'tipo_accion'";
        $fieldCheck = $conn->query($checkFields);
        if ($fieldCheck && $fieldCheck->num_rows > 0) {
            $hasExtendedFields = true;
        }
    }
}

// Construir consulta
if ($hasExtendedFields) {
    $query = "SELECT 
                a.id_log,
                a.id_usuario,
                a.tipo_accion,
                a.accion,
                a.recurso,
                a.estado,
                a.es_alerta,
                a.ip_address,
                a.fecha_hora,
                u.primer_nombre,
                u.apellido_paterno,
                u.email,
                r.nombre_rol
              FROM {$tableName} a
              LEFT JOIN usuarios u ON a.id_usuario = u.id_usuario
              LEFT JOIN roles r ON u.id_rol = r.id_rol
              WHERE 1=1";
} else {
    $query = "SELECT 
                a.id_log,
                a.id_admin as id_usuario,
                a.accion,
                a.modulo as recurso,
                a.ip_address,
                a.fecha_hora,
                u.primer_nombre,
                u.apellido_paterno,
                u.email,
                r.nombre_rol
              FROM auditoria_admin a
              LEFT JOIN usuarios u ON a.id_admin = u.id_usuario
              LEFT JOIN roles r ON u.id_rol = r.id_rol
              WHERE 1=1";
}

$params = [];
$types = '';

// Aplicar filtros de búsqueda - buscar en todos los campos relevantes
if (!empty($searchTerm)) {
    if ($hasExtendedFields) {
        // Buscar en: accion, recurso, email, nombre completo, id_log, tipo_accion
        $query .= " AND (
            a.accion LIKE ? OR 
            a.recurso LIKE ? OR 
            a.tipo_accion LIKE ? OR
            u.email LIKE ? OR 
            CONCAT(u.primer_nombre, ' ', COALESCE(u.apellido_paterno, '')) LIKE ? OR
            CAST(a.id_log AS CHAR) LIKE ?
        )";
        $searchParam = '%' . $searchTerm . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= 'ssssss';
    } else {
        // Estructura básica - buscar en accion, modulo, email, nombre, id_log
        $query .= " AND (
            a.accion LIKE ? OR 
            a.modulo LIKE ? OR 
            u.email LIKE ? OR
            CONCAT(u.primer_nombre, ' ', COALESCE(u.apellido_paterno, '')) LIKE ? OR
            CAST(a.id_log AS CHAR) LIKE ?
        )";
        $searchParam = '%' . $searchTerm . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= 'sssss';
    }
}

// Mapeo de tipos de acción para el filtro
$tipoAccionMap = [
    'creacion_usuario' => 'creacion_usuario',
    'edicion_usuario' => 'edicion_usuario',
    'login_exitoso' => 'login_exitoso',
    'login_fallido' => 'login_fallido',
    'validacion_pago' => 'validacion_pago',
    'creacion_sorteo' => 'creacion_sorteo',
    'generador_ganador' => 'generador_ganador'
];

if ($tipoFilter !== 'all' && $hasExtendedFields && isset($tipoAccionMap[$tipoFilter])) {
    $query .= " AND a.tipo_accion = ?";
    $params[] = $tipoAccionMap[$tipoFilter];
    $types .= 's';
} elseif ($tipoFilter !== 'all' && $hasExtendedFields) {
    // Si no está en el mapa, buscar por el texto de la acción
    $query .= " AND a.accion LIKE ?";
    $params[] = '%' . $tipoFilter . '%';
    $types .= 's';
}

// Filtro de estado - mapear valores del frontend a valores de BD
if ($estadoFilter !== 'all' && $hasExtendedFields && !$alertsOnly) {
    // Mapear valores del filtro a estados de la BD
    $estadoMap = [
        'success' => 'success',
        'Exitoso' => 'success',
        'error' => 'error',
        'Fallido' => 'error'
    ];
    
    $estadoBD = isset($estadoMap[$estadoFilter]) ? $estadoMap[$estadoFilter] : $estadoFilter;
    $query .= " AND a.estado = ?";
    $params[] = $estadoBD;
    $types .= 's';
}

// Filtro de alertas (tiene prioridad sobre estado si está activo)
if ($alertsOnly && $hasExtendedFields) {
    $query .= " AND a.es_alerta = 1";
}

// Filtro de rango de fechas
if (!empty($fechaInicio)) {
    $query .= " AND DATE(a.fecha_hora) >= ?";
    $params[] = $fechaInicio;
    $types .= 's';
}

if (!empty($fechaFin)) {
    $query .= " AND DATE(a.fecha_hora) <= ?";
    $params[] = $fechaFin;
    $types .= 's';
}

$query .= " ORDER BY a.fecha_hora DESC";

// Ejecutar consulta
$stmt = $conn->prepare($query);
if ($stmt && !empty($params)) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}

// Configurar headers para descarga CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="auditoria_' . date('Y-m-d') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Crear archivo de salida
$output = fopen('php://output', 'w');

// Agregar BOM para UTF-8 (para Excel)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Escribir encabezados
fputcsv($output, [
    'ID',
    'Fecha y Hora',
    'Usuario',
    'Email',
    'Rol',
    'Acción',
    'Recurso',
    'Estado',
    'IP Address',
    'Alerta'
], ';');

// Escribir datos
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Manejar id_usuario NULL - mostrar como Sistema
        $idUsuario = $row['id_usuario'] ?? null;
        if (empty($idUsuario) || is_null($idUsuario)) {
            $usuario = 'Sistema';
            $rol = 'Automático';
        } else {
            $nombreCompleto = trim(($row['primer_nombre'] ?? '') . ' ' . ($row['apellido_paterno'] ?? ''));
            $usuario = !empty($nombreCompleto) ? $nombreCompleto : (!empty($row['email']) ? $row['email'] : 'Usuario Desconocido');
            $rol = $row['nombre_rol'] ?? 'Usuario';
        }
        
        $estado = $hasExtendedFields ? ($row['estado'] ?? 'success') : 'success';
        $estadoTexto = ($estado === 'success' || $estado === 'Exitoso') ? 'Exitoso' : 'Fallido';
        
        fputcsv($output, [
            $row['id_log'],
            $row['fecha_hora'],
            $usuario,
            $row['email'] ?? '',
            $rol ?? '',
            $row['accion'],
            $row['recurso'] ?? '',
            $estadoTexto,
            $row['ip_address'] ?? '',
            $hasExtendedFields ? ($row['es_alerta'] ?? 0 ? 'Sí' : 'No') : 'N/A'
        ], ';');
    }
}

fclose($output);

if ($stmt) {
    $stmt->close();
}
$conn->close();
exit;
?>

