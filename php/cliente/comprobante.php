<?php
/**
 * Comprobante de Boleto para Impresión
 */
require_once __DIR__ . '/config/database.php';
session_start();

// Validar acceso
if (!isset($_SESSION['is_logged_in']) || !isset($_GET['boleto'])) {
    die("Acceso denegado");
}

$idBoleto = intval($_GET['boleto']);
$idUsuario = $_SESSION['usuario_id'];

// Obtener datos del boleto
try {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT 
            b.numero_boleto,
            b.fecha_compra as fecha_asignacion, -- Usamos fecha_compra o created_at
            s.titulo as sorteo_titulo,
            s.fecha_fin as fecha_sorteo,
            s.precio_boleto,
            u.primer_nombre, u.apellido_paterno, u.email,
            t.referencia_pago, t.metodo_pago
        FROM boletos b
        JOIN sorteos s ON b.id_sorteo = s.id_sorteo
        JOIN usuarios u ON b.id_usuario_actual = u.id_usuario
        LEFT JOIN detalle_transaccion_boletos dtb ON b.id_boleto = dtb.id_boleto
        LEFT JOIN transacciones t ON dtb.id_transaccion = t.id_transaccion
        WHERE b.id_boleto = :id_boleto AND b.id_usuario_actual = :id_usuario
    ");
    $stmt->execute([':id_boleto' => $idBoleto, ':id_usuario' => $idUsuario]);
    $boleto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$boleto) {
        die("Boleto no encontrado o no pertenece al usuario.");
    }

} catch (Exception $e) {
    die("Error al generar comprobante: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Comprobante - Boleto #
        <?php echo htmlspecialchars($boleto['numero_boleto']); ?>
    </title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background: #e0e0e0;
            padding: 20px;
        }

        .ticket {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-top: 5px solid #2463eb;
        }

        .header {
            text-align: center;
            border-bottom: 2px dashed #ccc;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            color: #333;
        }

        .header h2 {
            margin: 5px 0 0;
            color: #666;
            font-size: 18px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .info-label {
            font-weight: bold;
            color: #555;
        }

        .ticket-number {
            text-align: center;
            font-size: 32px;
            font-weight: bold;
            color: #2463eb;
            margin: 20px 0;
            letter-spacing: 2px;
            background: #f0f4ff;
            padding: 10px;
            border-radius: 4px;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #999;
            margin-top: 30px;
            border-top: 1px solid #eee;
            pt: 10px;
        }

        .btn-print {
            display: block;
            width: 100%;
            max-width: 200px;
            margin: 20px auto 0;
            padding: 10px;
            background: #2463eb;
            color: white;
            text-align: center;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
        }

        .btn-print:hover {
            background: #1a4bbd;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .ticket {
                box-shadow: none;
                border: none;
            }

            .btn-print {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="ticket">
        <div class="header">
            <h1>SorteosWeb</h1>
            <h2>Comprobante Electrónico</h2>
        </div>

        <div class="ticket-number">
            #
            <?php echo htmlspecialchars($boleto['numero_boleto']); ?>
        </div>

        <div class="info-row">
            <span class="info-label">Sorteo:</span>
            <span>
                <?php echo htmlspecialchars($boleto['sorteo_titulo']); ?>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha Sorteo:</span>
            <span>
                <?php echo date('d/m/Y', strtotime($boleto['fecha_sorteo'])); ?>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Cliente:</span>
            <span>
                <?php echo htmlspecialchars($boleto['primer_nombre'] . ' ' . $boleto['apellido_paterno']); ?>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Costo:</span>
            <span>$
                <?php echo number_format($boleto['precio_boleto'], 2); ?>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Transacción:</span>
            <span>
                <?php echo htmlspecialchars($boleto['referencia_pago'] ?? 'N/A'); ?>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Método:</span>
            <span>
                <?php echo htmlspecialchars($boleto['metodo_pago'] ?? 'N/A'); ?>
            </span>
        </div>

        <div class="footer">
            <p>Este comprobante es válido para reclamar su premio en caso de resultar ganador.</p>
            <p>Generado el
                <?php echo date('d/m/Y H:i:s'); ?>
            </p>
        </div>

        <a href="#" onclick="window.print(); return false;" class="btn-print">Imprimir / Guardar PDF</a>
    </div>

    <script>
        // Auto-imprimir al cargar si se desea
        // window.onload = function() { window.print(); }
    </script>

</body>

</html>