<?php
/**
 * callback_pasarela.php - Recibe la confirmación del banco (Webhook simulado)
 */
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $idTransaccion = $input['id_transaccion'] ?? 0;
    $resultado = $input['resultado'] ?? 'failure';
    $refPasarela = $input['ref_pasarela'] ?? '';
    
    $db = getDB();
    
    // Log del intento
    $stmt = $db->prepare("INSERT INTO pasarela_logs (id_transaccion, tipo_evento, datos_recibidos) VALUES (:id, 'CALLBACK', :datos)");
    $stmt->execute([
        ':id' => $idTransaccion,
        ':datos' => json_encode($input)
    ]);
    
    if ($resultado === 'success') {
        // 1. Actualizar transacción a Completado
        $stmt = $db->prepare("
            UPDATE transacciones 
            SET estado_pago = 'Completado', 
                gateway_reference = :ref,
                fecha_confirmacion = NOW()
            WHERE id_transaccion = :id
        ");
        $stmt->execute([
            ':ref' => $refPasarela,
            ':id' => $idTransaccion
        ]);
        
        // 2. Actualizar estado de los boletos a 'Vendido'
        // Primero obtenemos los boletos de esta transacción
        $stmt = $db->prepare("SELECT id_boleto FROM detalle_transaccion_boletos WHERE id_transaccion = :id");
        $stmt->execute([':id' => $idTransaccion]);
        $boletos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if ($boletos) {
            $idsBoletos = implode(',', $boletos); 
            // NOTA IMPORTANTE: En producción usar prepared statements para cada ID o una query dinámica segura
            // Aquí por simplicidad en la simulación:
            $db->exec("UPDATE boletos SET estado = 'Vendido', id_usuario = (SELECT id_usuario FROM transacciones WHERE id_transaccion = $idTransaccion) WHERE id_boleto IN ($idsBoletos)");
        }
        
        echo json_encode(['success' => true]);
    } else {
        // Pago fallido
        $stmt = $db->prepare("UPDATE transacciones SET estado_pago = 'Fallido' WHERE id_transaccion = :id");
        $stmt->execute([':id' => $idTransaccion]);
        echo json_encode(['success' => false, 'error' => 'Pago rechazado por el usuario']);
    }
    
} catch (Exception $e) {
    error_log("Error Callback: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
