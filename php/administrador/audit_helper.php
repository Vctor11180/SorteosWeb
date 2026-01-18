<?php
// audit_helper.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Registra una acción en la auditoría
 * @param mysqli $conn Conexión a base de datos
 * @param string $codigo Codigo de la acción (ej: 'LOGIN', 'CREAR_USUARIO')
 * @param string $modulo Modulo donde ocurre (ej: 'Usuarios', 'Sorteos')
 * @param array|null $detalles Array asociativo con detalles extra
 * @param int|null $idAdminExplicit ID del admin opcional (si no se pasa, se busca en sesión)
 * @return bool
 */
function registrarAuditoria($conn, $codigo, $modulo, $detalles = null, $idAdminExplicit = null) {
    // 1. Obtener ID del admin actual
    $idAdmin = 0;
    
    if ($idAdminExplicit !== null && $idAdminExplicit > 0) {
        $idAdmin = intval($idAdminExplicit);
    } elseif (isset($_SESSION['id_usuario'])) {
        $idAdmin = intval($_SESSION['id_usuario']);
    }
    
    // Si no hay usuario en sesión, intentar obtener de detalles si es un LOGIN
    if ($idAdmin === 0 && $codigo === 'LOGIN' && isset($detalles['id_usuario'])) {
        $idAdmin = intval($detalles['id_usuario']);
    }

    // SI idAdmin sigue siendo 0 (sistema/guest), lo pasamos como NULL
    $idAdminToInsert = ($idAdmin > 0) ? $idAdmin : null;

    // 2. Obtener datos de contexto
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    // Intentar obtener IP real si está detrás de proxy
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Puede venir una lista separada por comas, tomamos la primera
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ipList[0]);
    }

    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $jsonDetalles = $detalles ? json_encode($detalles) : null;

    // 3. Obtener id_tipo basado en el código
    $stmtTipo = $conn->prepare("SELECT id_tipo, descripcion FROM auditoria_tipos WHERE codigo = ?");
    $stmtTipo->bind_param("s", $codigo);
    $stmtTipo->execute();
    $resTipo = $stmtTipo->get_result();
    
    if ($resTipo->num_rows === 0) {
        error_log("Auditoria: Codigo '$codigo' no encontrado en auditoria_tipos.");
        return false;
    }
    
    $rowTipo = $resTipo->fetch_assoc();
    $idTipo = $rowTipo['id_tipo'];
    
    // 4. Insertar en auditoria_admin
    // Nota: 'accion' legacy.
    $stmt = $conn->prepare("INSERT INTO auditoria_admin (id_admin, id_tipo, accion, modulo, detalles_json, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssss", $idAdminToInsert, $idTipo, $codigo, $modulo, $jsonDetalles, $ip, $userAgent);
    
    if (!$stmt->execute()) {
        error_log("Error insertando auditoria: " . $stmt->error);
        return false;
    }
    
    return true;
}
?>
