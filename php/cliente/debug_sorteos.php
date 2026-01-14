<?php
/**
 * Script de depuración para verificar por qué no se muestran los sorteos
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir configuración de base de datos
require_once __DIR__ . '/config/database.php';

echo "<h1>Debug de Sorteos Activos</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #1a1a1a; color: #fff; }
    .success { color: #0bda62; }
    .error { color: #ff4444; }
    .info { color: #4da6ff; }
    pre { background: #2a2a2a; padding: 15px; border-radius: 5px; overflow-x: auto; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #444; padding: 10px; text-align: left; }
    th { background: #333; }
</style>";

try {
    $db = getDB();
    echo "<p class='success'>✅ Conexión a la base de datos exitosa</p>";
    
    // 1. Verificar si existen sorteos en la tabla
    echo "<h2>1. Verificando sorteos en la base de datos</h2>";
    $stmt = $db->query("SELECT COUNT(*) as total FROM sorteos");
    $total = $stmt->fetch();
    echo "<p class='info'>Total de sorteos en la tabla: <strong>" . $total['total'] . "</strong></p>";
    
    // 2. Ver todos los sorteos con su estado
    echo "<h2>2. Listado de todos los sorteos</h2>";
    $stmt = $db->query("SELECT id_sorteo, titulo, estado, fecha_inicio, fecha_fin, NOW() as ahora FROM sorteos ORDER BY id_sorteo");
    $todosSorteos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($todosSorteos)) {
        echo "<p class='error'>❌ No hay sorteos en la base de datos</p>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>Título</th><th>Estado</th><th>Fecha Inicio</th><th>Fecha Fin</th><th>Ahora</th><th>¿Válido?</th></tr>";
        foreach ($todosSorteos as $sorteo) {
            $fechaFin = new DateTime($sorteo['fecha_fin']);
            $ahora = new DateTime($sorteo['ahora']);
            $esValido = ($sorteo['estado'] === 'Activo' && $fechaFin > $ahora);
            $color = $esValido ? 'success' : 'error';
            echo "<tr>";
            echo "<td>" . $sorteo['id_sorteo'] . "</td>";
            echo "<td>" . htmlspecialchars($sorteo['titulo']) . "</td>";
            echo "<td>" . $sorteo['estado'] . "</td>";
            echo "<td>" . $sorteo['fecha_inicio'] . "</td>";
            echo "<td>" . $sorteo['fecha_fin'] . "</td>";
            echo "<td>" . $sorteo['ahora'] . "</td>";
            echo "<td class='$color'>" . ($esValido ? '✅ SÍ' : '❌ NO') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 3. Probar la consulta exacta que usa la función
    echo "<h2>3. Probando la consulta de obtenerSorteosActivos</h2>";
    $sql = "
        SELECT 
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
            COUNT(CASE WHEN b.estado = 'Vendido' THEN 1 END) as boletos_vendidos,
            COUNT(CASE WHEN b.estado = 'Reservado' THEN 1 END) as boletos_reservados,
            COUNT(CASE WHEN b.estado = 'Disponible' THEN 1 END) as boletos_disponibles
        FROM sorteos s
        LEFT JOIN boletos b ON s.id_sorteo = b.id_sorteo
        WHERE s.estado = 'Activo'
        AND s.fecha_fin > NOW()
        GROUP BY s.id_sorteo
        ORDER BY s.fecha_fin ASC
    ";
    
    echo "<pre>" . htmlspecialchars($sql) . "</pre>";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $sorteos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p class='info'>Sorteos encontrados con la consulta: <strong>" . count($sorteos) . "</strong></p>";
    
    if (empty($sorteos)) {
        echo "<p class='error'>❌ La consulta no devuelve resultados. Posibles causas:</p>";
        echo "<ul>";
        echo "<li>Los sorteos no tienen estado = 'Activo' (verificar mayúsculas/minúsculas)</li>";
        echo "<li>La fecha_fin es anterior a la fecha actual</li>";
        echo "<li>Hay un problema con la zona horaria</li>";
        echo "</ul>";
        
        // Verificar estado exacto
        echo "<h3>Verificando estados exactos:</h3>";
        $stmt = $db->query("SELECT DISTINCT estado FROM sorteos");
        $estados = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>Estados encontrados en la BD: " . implode(', ', $estados) . "</p>";
        
        // Verificar fechas
        echo "<h3>Verificando fechas:</h3>";
        $stmt = $db->query("SELECT id_sorteo, titulo, fecha_fin, NOW() as ahora, 
                           CASE WHEN fecha_fin > NOW() THEN 'Futuro' ELSE 'Pasado' END as comparacion
                           FROM sorteos WHERE estado = 'Activo'");
        $fechas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($fechas)) {
            echo "<p class='error'>No hay sorteos con estado 'Activo'</p>";
        } else {
            echo "<table>";
            echo "<tr><th>ID</th><th>Título</th><th>Fecha Fin</th><th>Ahora</th><th>Comparación</th></tr>";
            foreach ($fechas as $fecha) {
                echo "<tr>";
                echo "<td>" . $fecha['id_sorteo'] . "</td>";
                echo "<td>" . htmlspecialchars($fecha['titulo']) . "</td>";
                echo "<td>" . $fecha['fecha_fin'] . "</td>";
                echo "<td>" . $fecha['ahora'] . "</td>";
                echo "<td>" . $fecha['comparacion'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p class='success'>✅ La consulta funciona correctamente</p>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Título</th><th>Precio</th><th>Total Boletos</th><th>Vendidos</th><th>Disponibles</th></tr>";
        foreach ($sorteos as $sorteo) {
            echo "<tr>";
            echo "<td>" . $sorteo['id_sorteo'] . "</td>";
            echo "<td>" . htmlspecialchars($sorteo['titulo']) . "</td>";
            echo "<td>$" . number_format($sorteo['precio_boleto'], 2) . "</td>";
            echo "<td>" . $sorteo['total_boletos_crear'] . "</td>";
            echo "<td>" . $sorteo['boletos_vendidos'] . "</td>";
            echo "<td>" . $sorteo['boletos_disponibles'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 4. Probar la función directamente
    echo "<h2>4. Probando la función obtenerSorteosActivos()</h2>";
    require_once __DIR__ . '/includes/sorteos-data.php';
    $sorteosFuncion = obtenerSorteosActivos(0);
    echo "<p class='info'>Sorteos devueltos por la función: <strong>" . count($sorteosFuncion) . "</strong></p>";
    
    if (empty($sorteosFuncion)) {
        echo "<p class='error'>❌ La función no devuelve resultados</p>";
        echo "<p>Revisa los logs de error de PHP para ver si hay excepciones capturadas.</p>";
    } else {
        echo "<p class='success'>✅ La función funciona correctamente</p>";
        echo "<pre>" . print_r($sorteosFuncion, true) . "</pre>";
    }
    
    // 5. Verificar errores de PHP
    echo "<h2>5. Errores de PHP</h2>";
    $errors = error_get_last();
    if ($errors) {
        echo "<p class='error'>Último error: " . $errors['message'] . "</p>";
    } else {
        echo "<p class='success'>No hay errores de PHP</p>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Error de PDO: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Error general: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
