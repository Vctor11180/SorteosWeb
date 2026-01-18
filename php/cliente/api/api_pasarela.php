<?php
/**
 * api_pasarela.php - API para iniciar pagos (Simulación Libélula)
 */
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');
session_start();

$action = $_GET['action'] ?? '';

if ($action === 'initiate_payment') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $idTransaccion = $input['id_transaccion'] ?? 0;
        
        if (!$idTransaccion) {
            throw new Exception('ID de transacción requerido');
        }

        $db = getDB();
        
        // 1. Obtener datos de la transacción
        $stmt = $db->prepare("SELECT * FROM transacciones WHERE id_transaccion = :id");
        $stmt->execute([':id' => $idTransaccion]);
        $transaccion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$transaccion) {
            throw new Exception('Transacción no encontrada');
        }
        
        // 2. "Llamar" a la pasarela (Simulación)
        // En producción, aquí haríamos un CURL a la API de Libélula
        
        // Generar un ID de pago único de la pasarela
        $paymentIdPasarela = 'LIB-' . time() . '-' . $idTransaccion;
        
        // Guardar Log
        $stmt = $db->prepare("INSERT INTO pasarela_logs (id_transaccion, tipo_evento, datos_enviados) VALUES (:id, 'INIT', :datos)");
        $stmt->execute([
            ':id' => $idTransaccion,
            ':datos' => json_encode(['monto' => $transaccion['monto_total'], 'payment_id' => $paymentIdPasarela])
        ]);
        
        // 3. Generar URL de redirección (A nuestra página de simulación local)
        // Pasamos el ID de transacción y el monto para mostrarlo
        $redirectUrl = "../pages/simular_pago.php?token=" . base64_encode(json_encode([
            'id_transaccion' => $idTransaccion,
            'monto' => $transaccion['monto_total'],
            'ref' => $paymentIdPasarela
        ]));
        
        echo json_encode([
            'success' => true,
            'redirect_url' => $redirectUrl
        ]);
        
    } catch (Exception $e) {
        error_log("Error Pasarela Init: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Acción no válida']);
}
