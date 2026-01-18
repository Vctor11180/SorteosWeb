<?php
// simular_pago.php - Página falsa del banco
$token = $_GET['token'] ?? '';
$data = json_decode(base64_decode($token), true);

if (!$data) {
    die("Token de pago inválido");
}

$monto = number_format($data['monto'], 2);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasarela de Pagos Segura (Simulación)</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-xl max-w-md w-full border-t-4 border-blue-600">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Pasarela de Pagos</h1>
            <p class="text-xs text-gray-500 uppercase tracking-widest mt-1">Ambiente Seguro (Demo)</p>
        </div>
        
        <div class="bg-blue-50 p-4 rounded-md mb-6 border border-blue-100">
            <div class="flex justify-between mb-2">
                <span class="text-gray-600">Comercio:</span>
                <span class="font-semibold">Sorteos Web</span>
            </div>
            <div class="flex justify-between mb-2">
                <span class="text-gray-600">Referencia:</span>
                <span class="font-mono text-xs bg-white px-1 rounded border"><?php echo htmlspecialchars($data['ref']); ?></span>
            </div>
            <div class="flex justify-between items-center pt-2 border-t border-blue-200 mt-2">
                <span class="text-lg font-bold text-blue-800">Total a Pagar:</span>
                <span class="text-2xl font-black text-blue-600">$<?php echo $monto; ?></span>
            </div>
        </div>
        
        <div class="space-y-4">
            <div class="border rounded p-3 bg-gray-50">
                <label class="block text-xs font-bold text-gray-500 uppercase">Número de Tarjeta</label>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-gray-400">💳</span>
                    <input type="text" value="4000 1234 5678 9010" disabled class="bg-transparent font-mono w-full outline-none text-gray-600 cursor-not-allowed">
                </div>
            </div>
            
            <div class="flex gap-4">
                <button onclick="procesarPago(true)" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded transition shadow-lg flex justify-center items-center gap-2">
                    <span>✅ Aprobar Pago</span>
                </button>
                <button onclick="procesarPago(false)" class="flex-1 bg-red-100 hover:bg-red-200 text-red-700 font-bold py-3 px-4 rounded transition border border-red-300">
                    Cancelar
                </button>
            </div>
        </div>
        
        <p class="text-center text-xs text-gray-400 mt-6">
            Esta es una simulación de Libélula / Red Enlace. <br>
            No se está realizando ningún cobro real.
        </p>
    </div>

    <script>
        function procesarPago(exito) {
            const btn = document.querySelector('button');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'Procesando...';
            
            // Llamar al callback de nuestro servidor
            fetch('../api/callback_pasarela.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    id_transaccion: <?php echo $data['id_transaccion']; ?>,
                    resultado: exito ? 'success' : 'failure',
                    ref_pasarela: '<?php echo $data['ref']; ?>'
                })
            })
            .then(res => res.json())
            .then(resp => {
                if (resp.success) {
                    // Redirigir al cliente de vuelta a la tienda
                    alert('Pago Procesado Correctamente. Volviendo a la tienda...');
                    window.location.href = 'DashboardCliente.php?mensaje=pago_exitoso';
                } else {
                    alert('Error: ' + resp.error);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error de conexión');
            });
        }
    </script>
</body>
</html>
