<?php
/**
 * Archivo de prueba simple
 * Accede a: http://localhost/SorteosWeb-conLoDeJorge/SorteosWeb-main/php/test.php
 */

echo "<h1>✅ PHP está funcionando correctamente</h1>";
echo "<p>Versión de PHP: " . phpversion() . "</p>";
echo "<p>Directorio actual: " . __DIR__ . "</p>";
echo "<p>Archivo actual: " . __FILE__ . "</p>";

// Verificar si las extensiones necesarias están cargadas
echo "<h2>Extensiones PHP:</h2>";
echo "<ul>";
echo "<li>PDO: " . (extension_loaded('pdo') ? "✅ Instalado" : "❌ No instalado") . "</li>";
echo "<li>PDO MySQL: " . (extension_loaded('pdo_mysql') ? "✅ Instalado" : "❌ No instalado") . "</li>";
echo "<li>MySQLi: " . (extension_loaded('mysqli') ? "✅ Instalado" : "❌ No instalado") . "</li>";
echo "<li>Session: " . (extension_loaded('session') ? "✅ Instalado" : "❌ No instalado") . "</li>";
echo "</ul>";

// Probar sesión
echo "<h2>Prueba de Sesión:</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "<p>✅ Sesión iniciada correctamente</p>";
} else {
    echo "<p>✅ Sesión ya estaba iniciada</p>";
}

// Verificar rutas de archivos
echo "<h2>Verificación de Archivos:</h2>";
$archivos = [
    'index.php' => __DIR__ . '/index.php',
    'cliente/config/database.php' => __DIR__ . '/cliente/config/database.php',
    'administrador/config.php' => __DIR__ . '/administrador/config.php',
];

foreach ($archivos as $nombre => $ruta) {
    echo "<p>" . $nombre . ": " . (file_exists($ruta) ? "✅ Existe" : "❌ No existe") . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Ir a index.php</a></p>";
echo "<p><a href='test_conexion.php'>Probar conexión a base de datos</a></p>";
