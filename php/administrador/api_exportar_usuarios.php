<?php
/**
 * API para exportar usuarios a CSV
 */

require_once 'config.php';

// Obtener parámetros de filtrado
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$estadoFilter = isset($_GET['estado']) ? trim($_GET['estado']) : '';

$conn = getDBConnection();

// Construir la consulta base
$query = "SELECT u.id_usuario, 
                 CONCAT(u.primer_nombre, ' ', COALESCE(u.segundo_nombre, ''), ' ', u.apellido_paterno, ' ', COALESCE(u.apellido_materno, '')) as nombre_completo,
                 u.email, 
                 u.telefono, 
                 u.estado, 
                 u.fecha_registro, 
                 r.nombre_rol
          FROM usuarios u
          INNER JOIN roles r ON u.id_rol = r.id_rol
          WHERE (r.nombre_rol = 'Cliente' OR u.id_rol = 2)";

$params = [];
$types = '';

// Aplicar filtro de búsqueda
if (!empty($searchTerm)) {
    $query .= " AND (CONCAT(u.primer_nombre, ' ', COALESCE(u.segundo_nombre, ''), ' ', u.apellido_paterno, ' ', COALESCE(u.apellido_materno, '')) LIKE ? 
                    OR u.email LIKE ? 
                    OR CAST(u.id_usuario AS CHAR) LIKE ?)";
    $searchParam = '%' . $searchTerm . '%';
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'sss';
}

// Aplicar filtro de estado
if (!empty($estadoFilter)) {
    $estadoMap = [
        'active' => 'Activo',
        'inactive' => 'Inactivo',
        'pending' => 'Pendiente'
    ];
    if (isset($estadoMap[$estadoFilter])) {
        $query .= " AND u.estado = ?";
        $params[] = $estadoMap[$estadoFilter];
        $types .= 's';
    }
}

$query .= " ORDER BY u.fecha_registro DESC";

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
header('Content-Disposition: attachment; filename="usuarios_' . date('Y-m-d') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Crear archivo de salida
$output = fopen('php://output', 'w');

// Agregar BOM para UTF-8 (para Excel)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Escribir encabezados
fputcsv($output, [
    'ID',
    'Nombre Completo',
    'Email',
    'Teléfono',
    'Estado',
    'Fecha de Registro',
    'Rol'
], ';');

// Escribir datos
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id_usuario'],
            $row['nombre_completo'],
            $row['email'],
            $row['telefono'] ?? '',
            $row['estado'],
            $row['fecha_registro'],
            $row['nombre_rol']
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


