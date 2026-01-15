<?php
/**
 * Prueba Directa de API de Sorteos (Sin sesión)
 * Para diagnóstico de problemas
 */

// Desactivar errores en pantalla
ini_set('display_errors', 0);
error_reporting(E_ALL);

echo "<h1>Diagnóstico de API de Sorteos</h1>";
echo "<pre>";

// Test 1: Verificar archivo de configuración
echo "1. Verificando archivo de configuración...\n";
$configPath = __DIR__ . '/config/database.php';
if (file_exists($configPath)) {
    echo "   ✅ Archivo existe: $configPath\n";
} else {
    echo "   ❌ Archivo NO existe: $configPath\n";
    exit;
}

// Test 2: Cargar configuración
echo "\n2. Cargando configuración...\n";
try {
    require_once $configPath;
    echo "   ✅ Configuración cargada\n";
} catch (Exception $e) {
    echo "   ❌ Error al cargar: " . $e->getMessage() . "\n";
    exit;
}

// Test 3: Verificar función getDB
echo "\n3. Verificando función getDB...\n";
if (function_exists('getDB')) {
    echo "   ✅ Función getDB existe\n";
} else {
    echo "   ❌ Función getDB NO existe\n";
    exit;
}

// Test 4: Intentar conexión
echo "\n4. Intentando conectar a la base de datos...\n";
try {
    $db = getDB();
    if ($db) {
        echo "   ✅ Conexión exitosa\n";
    } else {
        echo "   ❌ Conexión retornó null\n";
        exit;
    }
} catch (Exception $e) {
    echo "   ❌ Error de conexión: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
    exit;
}

// Test 5: Verificar tabla sorteos
echo "\n5. Verificando tabla sorteos...\n";
try {
    $stmt = $db->query("SELECT COUNT(*) as total FROM sorteos");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   ✅ Tabla existe. Total de sorteos: " . $result['total'] . "\n";
} catch (Exception $e) {
    echo "   ❌ Error al consultar tabla: " . $e->getMessage() . "\n";
    exit;
}

// Test 6: Verificar sorteos activos
echo "\n6. Verificando sorteos activos...\n";
try {
    $stmt = $db->query("SELECT COUNT(*) as total FROM sorteos WHERE estado = 'Activo'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   ✅ Sorteos activos: " . $result['total'] . "\n";
    
    if ($result['total'] == 0) {
        echo "   ⚠️  ADVERTENCIA: No hay sorteos activos en la base de datos\n";
        echo "   💡 Crea un sorteo con estado 'Activo' desde el panel de administrador\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Test 7: Probar consulta completa
echo "\n7. Probando consulta completa de sorteos activos...\n";
try {
    $sql = "
        SELECT 
            s.id_sorteo,
            s.titulo,
            s.precio_boleto,
            s.total_boletos_crear,
            s.estado
        FROM sorteos s
        WHERE s.estado = 'Activo'
        LIMIT 5
    ";
    $stmt = $db->query($sql);
    $sorteos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   ✅ Consulta exitosa. Sorteos encontrados: " . count($sorteos) . "\n";
    
    if (count($sorteos) > 0) {
        echo "\n   Primeros sorteos:\n";
        foreach ($sorteos as $sorteo) {
            echo "   - ID: {$sorteo['id_sorteo']}, Título: {$sorteo['titulo']}, Estado: {$sorteo['estado']}\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Error en consulta: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

// Test 8: Verificar sesión
echo "\n8. Verificando sesión...\n";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "   Estado de sesión: " . (session_status() === PHP_SESSION_ACTIVE ? 'Activa' : 'Inactiva') . "\n";
echo "   is_logged_in: " . (isset($_SESSION['is_logged_in']) ? ($_SESSION['is_logged_in'] ? 'true' : 'false') : 'no definido') . "\n";
echo "   id_usuario: " . (isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : 'no definido') . "\n";

echo "\n✅ Diagnóstico completado\n";
echo "</pre>";

echo "<h2>Próximos pasos:</h2>";
echo "<ul>";
echo "<li>Si todos los tests pasaron, el problema puede ser de sesión. Inicia sesión primero.</li>";
echo "<li>Si hay errores de conexión, verifica config/database.php</li>";
echo "<li>Si no hay sorteos activos, créalos desde el panel de administrador</li>";
echo "</ul>";

echo "<p><a href='test_api_sorteos.php'>Volver a la página de pruebas</a></p>";
?>
