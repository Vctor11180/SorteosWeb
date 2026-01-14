<?php
/**
 * Script para actualizar las fechas de los sorteos activos a fechas futuras
 * Accede a: http://localhost/SorteosWeb-conLoDeJorge/SorteosWeb-main/php/cliente/actualizar_fechas_sorteos.php
 */

require_once __DIR__ . '/config/database.php';

echo "<h1>Actualizar Fechas de Sorteos Activos</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #1a1a1a; color: #fff; }
    .success { color: #0bda62; }
    .error { color: #ff4444; }
    .info { color: #4da6ff; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #444; padding: 10px; text-align: left; }
    th { background: #333; }
    button { background: #0bda62; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
    button:hover { background: #0aa050; }
</style>";

try {
    $db = getDB();
    
    // Ver sorteos actuales
    echo "<h2>1. Sorteos Activos Actuales</h2>";
    $stmt = $db->query("SELECT id_sorteo, titulo, estado, fecha_inicio, fecha_fin, NOW() as ahora FROM sorteos WHERE estado = 'Activo' ORDER BY id_sorteo");
    $sorteos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($sorteos)) {
        echo "<p class='error'>No hay sorteos con estado 'Activo'</p>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>Título</th><th>Fecha Inicio</th><th>Fecha Fin</th><th>Ahora</th><th>Estado</th></tr>";
        foreach ($sorteos as $sorteo) {
            $fechaFin = new DateTime($sorteo['fecha_fin']);
            $ahora = new DateTime($sorteo['ahora']);
            $esFuturo = $fechaFin > $ahora;
            $color = $esFuturo ? 'success' : 'error';
            echo "<tr>";
            echo "<td>" . $sorteo['id_sorteo'] . "</td>";
            echo "<td>" . htmlspecialchars($sorteo['titulo']) . "</td>";
            echo "<td>" . $sorteo['fecha_inicio'] . "</td>";
            echo "<td>" . $sorteo['fecha_fin'] . "</td>";
            echo "<td>" . $sorteo['ahora'] . "</td>";
            echo "<td class='$color'>" . ($esFuturo ? '✅ Futuro' : '❌ Pasado') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Actualizar fechas si se presiona el botón
    if (isset($_GET['actualizar']) && $_GET['actualizar'] === 'si') {
        echo "<h2>2. Actualizando Fechas...</h2>";
        
        // Actualizar fecha_fin a 30 días desde hoy
        $stmt = $db->prepare("
            UPDATE sorteos 
            SET fecha_fin = DATE_ADD(NOW(), INTERVAL 30 DAY),
                fecha_inicio = DATE_SUB(DATE_ADD(NOW(), INTERVAL 30 DAY), INTERVAL 7 DAY)
            WHERE estado = 'Activo' AND fecha_fin <= NOW()
        ");
        $stmt->execute();
        $actualizados = $stmt->rowCount();
        
        echo "<p class='success'>✅ Se actualizaron <strong>$actualizados</strong> sorteos</p>";
        
        // Mostrar sorteos actualizados
        echo "<h2>3. Sorteos Después de la Actualización</h2>";
        $stmt = $db->query("SELECT id_sorteo, titulo, estado, fecha_inicio, fecha_fin, NOW() as ahora FROM sorteos WHERE estado = 'Activo' ORDER BY id_sorteo");
        $sorteosActualizados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<tr><th>ID</th><th>Título</th><th>Fecha Inicio</th><th>Fecha Fin</th><th>Ahora</th><th>Estado</th></tr>";
        foreach ($sorteosActualizados as $sorteo) {
            $fechaFin = new DateTime($sorteo['fecha_fin']);
            $ahora = new DateTime($sorteo['ahora']);
            $esFuturo = $fechaFin > $ahora;
            $color = $esFuturo ? 'success' : 'error';
            echo "<tr>";
            echo "<td>" . $sorteo['id_sorteo'] . "</td>";
            echo "<td>" . htmlspecialchars($sorteo['titulo']) . "</td>";
            echo "<td>" . $sorteo['fecha_inicio'] . "</td>";
            echo "<td>" . $sorteo['fecha_fin'] . "</td>";
            echo "<td>" . $sorteo['ahora'] . "</td>";
            echo "<td class='$color'>" . ($esFuturo ? '✅ Futuro' : '❌ Pasado') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<p class='success'><strong>¡Actualización completada! Ahora recarga la página de sorteos para ver los cambios.</strong></p>";
        echo "<p><a href='ListadoSorteosActivos.php' style='color: #4da6ff;'>← Volver a Listado de Sorteos</a></p>";
    } else {
        echo "<h2>2. Actualizar Fechas</h2>";
        echo "<p class='info'>Este script actualizará las fechas de los sorteos activos a:</p>";
        echo "<ul>";
        echo "<li><strong>fecha_fin:</strong> 30 días desde hoy</li>";
        echo "<li><strong>fecha_inicio:</strong> 7 días antes de fecha_fin</li>";
        echo "</ul>";
        echo "<p><a href='?actualizar=si'><button>Actualizar Fechas de Sorteos Activos</button></a></p>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Error general: " . $e->getMessage() . "</p>";
}
?>
