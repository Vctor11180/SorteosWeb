<?php
/**
 * Script para crear boletos faltantes en sorteos existentes
 * Este script verifica todos los sorteos y crea los boletos que faltan
 * 
 * Uso: Ejecutar desde el navegador o línea de comandos
 * http://localhost/SorteosWeb/crear_boletos_faltantes.php
 */

header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/php/cliente/config/database.php';

try {
    $db = getDB();
    
    echo "<h1>Creación de Boletos Faltantes</h1>";
    echo "<p>Verificando sorteos y creando boletos faltantes...</p>";
    echo "<hr>";
    
    // Obtener todos los sorteos
    $stmt = $db->query("SELECT id_sorteo, titulo, total_boletos_crear FROM sorteos");
    $sorteos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalSorteos = count($sorteos);
    $sorteosProcesados = 0;
    $boletosCreados = 0;
    
    foreach ($sorteos as $sorteo) {
        $idSorteo = $sorteo['id_sorteo'];
        $titulo = $sorteo['titulo'];
        $totalBoletos = intval($sorteo['total_boletos_crear']);
        
        // Contar boletos existentes
        $stmtCount = $db->prepare("SELECT COUNT(*) as total FROM boletos WHERE id_sorteo = :id_sorteo");
        $stmtCount->execute([':id_sorteo' => $idSorteo]);
        $result = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $boletosExistentes = intval($result['total']);
        
        if ($boletosExistentes < $totalBoletos) {
            echo "<p><strong>Sorteo #$idSorteo: $titulo</strong><br>";
            echo "Boletos existentes: $boletosExistentes / $totalBoletos<br>";
            
            // Obtener números de boletos existentes
            $stmtBoletos = $db->prepare("SELECT numero_boleto FROM boletos WHERE id_sorteo = :id_sorteo");
            $stmtBoletos->execute([':id_sorteo' => $idSorteo]);
            $boletosExistentesList = $stmtBoletos->fetchAll(PDO::FETCH_COLUMN);
            
            $boletosMap = [];
            foreach ($boletosExistentesList as $numero) {
                $boletosMap[intval($numero)] = true;
            }
            
            // Crear boletos faltantes
            $db->beginTransaction();
            $creados = 0;
            try {
                for ($i = 1; $i <= $totalBoletos; $i++) {
                    if (!isset($boletosMap[$i])) {
                        $numeroBoleto = str_pad($i, 4, '0', STR_PAD_LEFT);
                        $stmtInsert = $db->prepare("
                            INSERT INTO boletos (id_sorteo, numero_boleto, estado)
                            VALUES (:id_sorteo, :numero_boleto, 'Disponible')
                        ");
                        $stmtInsert->execute([
                            ':id_sorteo' => $idSorteo,
                            ':numero_boleto' => $numeroBoleto
                        ]);
                        $creados++;
                    }
                }
                $db->commit();
                echo "✅ <span style='color: green;'>$creados boletos creados exitosamente</span></p>";
                $boletosCreados += $creados;
            } catch (Exception $e) {
                $db->rollBack();
                echo "❌ <span style='color: red;'>Error al crear boletos: " . htmlspecialchars($e->getMessage()) . "</span></p>";
            }
        } else {
            echo "<p>✅ Sorteo #$idSorteo: $titulo - Todos los boletos ya existen ($boletosExistentes/$totalBoletos)</p>";
        }
        
        $sorteosProcesados++;
    }
    
    echo "<hr>";
    echo "<h2>Resumen</h2>";
    echo "<p><strong>Sorteos procesados:</strong> $sorteosProcesados / $totalSorteos</p>";
    echo "<p><strong>Boletos creados:</strong> $boletosCreados</p>";
    echo "<p style='color: green;'><strong>✅ Proceso completado</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
