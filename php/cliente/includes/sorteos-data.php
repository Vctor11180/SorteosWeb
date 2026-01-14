<?php
/**
 * Helper para obtener sorteos desde la base de datos
 * Sistema de Sorteos Web
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Obtiene todos los sorteos activos desde la base de datos
 * @param int $limite Número máximo de sorteos a retornar (0 = sin límite)
 * @return array Array con los sorteos activos
 */
function obtenerSorteosActivos($limite = 0) {
    try {
        // Intentar cargar desde config/database.php primero
        if (file_exists(__DIR__ . '/../config/database.php')) {
            require_once __DIR__ . '/../config/database.php';
        } else {
            // Si no existe, usar la ruta alternativa
            require_once __DIR__ . '/../../config/database.php';
        }
        $db = getDB();
        
        // Construir la consulta - versión simplificada que evita problemas con GROUP BY
        // Primero obtenemos los sorteos, luego contamos los boletos por separado
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
                s.id_creador
            FROM sorteos s
            WHERE s.estado = 'Activo'
            ORDER BY s.fecha_fin ASC
        ";
        
        if ($limite > 0) {
            $sql .= " LIMIT :limite";
        }
        
        $stmt = $db->prepare($sql);
        if ($limite > 0) {
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        }
        $stmt->execute();
        $sorteos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug: Log para verificar qué se está obteniendo
        error_log("DEBUG obtenerSorteosActivos: Se encontraron " . count($sorteos) . " sorteos con estado 'Activo'");
        
        // Debug: Log si no hay resultados pero hay sorteos activos
        if (empty($sorteos)) {
            // Verificar si hay sorteos activos
            $stmtCheck = $db->query("SELECT COUNT(*) as total FROM sorteos WHERE estado = 'Activo'");
            $totalActivos = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            error_log("DEBUG obtenerSorteosActivos: Total de sorteos con estado 'Activo' en BD: " . $totalActivos['total']);
            
            // Verificar estados exactos
            $stmtEstados = $db->query("SELECT DISTINCT estado FROM sorteos");
            $estados = $stmtEstados->fetchAll(PDO::FETCH_COLUMN);
            error_log("DEBUG obtenerSorteosActivos: Estados encontrados en BD: " . implode(', ', $estados));
        }
        
        // Procesar cada sorteo para agregar información calculada
        $sorteosProcesados = [];
        foreach ($sorteos as $sorteo) {
            // Contar boletos por separado para cada sorteo
            $stmtBoletos = $db->prepare("
                SELECT 
                    COUNT(CASE WHEN estado = 'Vendido' THEN 1 END) as boletos_vendidos,
                    COUNT(CASE WHEN estado = 'Reservado' THEN 1 END) as boletos_reservados,
                    COUNT(CASE WHEN estado = 'Disponible' THEN 1 END) as boletos_disponibles
                FROM boletos
                WHERE id_sorteo = :id_sorteo
            ");
            $stmtBoletos->execute([':id_sorteo' => $sorteo['id_sorteo']]);
            $boletosInfo = $stmtBoletos->fetch(PDO::FETCH_ASSOC);
            
            $boletosVendidos = intval($boletosInfo['boletos_vendidos'] ?? 0);
            $boletosReservados = intval($boletosInfo['boletos_reservados'] ?? 0);
            $boletosDisponibles = intval($boletosInfo['boletos_disponibles'] ?? 0);
            $boletosTotales = intval($sorteo['total_boletos_crear'] ?? 0);
            
            // Si no hay boletos creados aún, los disponibles son el total
            if ($boletosDisponibles == 0 && $boletosVendidos == 0 && $boletosReservados == 0 && $boletosTotales > 0) {
                $boletosDisponibles = $boletosTotales;
            }
            
            // Calcular porcentaje vendido
            $porcentajeVendido = $boletosTotales > 0 
                ? round(($boletosVendidos / $boletosTotales) * 100, 1) 
                : 0;
            
            // Calcular tiempo restante
            $fechaFin = new DateTime($sorteo['fecha_fin']);
            $ahora = new DateTime();
            $totalSegundos = $fechaFin->getTimestamp() - $ahora->getTimestamp();
            
            // Si la fecha ya pasó, mostrar tiempo negativo o 0
            if ($totalSegundos < 0) {
                $totalSegundos = 0;
                $diferencia = $ahora->diff($fechaFin);
                $tiempoRestante = [
                    'dias' => 0,
                    'horas' => 0,
                    'minutos' => 0,
                    'segundos' => 0,
                    'total_segundos' => 0
                ];
                $estaPorFinalizar = true;
            } else {
                $diferencia = $ahora->diff($fechaFin);
                $tiempoRestante = [
                    'dias' => intval($diferencia->format('%a')),
                    'horas' => intval($diferencia->format('%h')),
                    'minutos' => intval($diferencia->format('%i')),
                    'segundos' => intval($diferencia->format('%s')),
                    'total_segundos' => $totalSegundos
                ];
                // Determinar si está por finalizar (menos de 24 horas)
                $estaPorFinalizar = $totalSegundos < 86400;
            }
            
            $sorteosProcesados[] = [
                'id_sorteo' => $sorteo['id_sorteo'],
                'titulo' => $sorteo['titulo'],
                'descripcion' => $sorteo['descripcion'] ?? '',
                'precio_boleto' => floatval($sorteo['precio_boleto']),
                'total_boletos' => $boletosTotales,
                'boletos_vendidos' => $boletosVendidos,
                'boletos_reservados' => $boletosReservados,
                'boletos_disponibles' => $boletosDisponibles,
                'porcentaje_vendido' => $porcentajeVendido,
                'fecha_inicio' => $sorteo['fecha_inicio'],
                'fecha_fin' => $sorteo['fecha_fin'],
                'imagen_url' => $sorteo['imagen_url'] ?? '',
                'estado' => $sorteo['estado'],
                'tiempo_restante' => $tiempoRestante,
                'esta_por_finalizar' => $estaPorFinalizar,
                'id_creador' => $sorteo['id_creador']
            ];
        }
        
        return $sorteosProcesados;
        
    } catch (PDOException $e) {
        error_log("Error al obtener sorteos activos (PDO): " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        // En modo desarrollo, puedes descomentar la siguiente línea para ver el error
        // throw $e;
        return [];
    } catch (Exception $e) {
        error_log("Error general al obtener sorteos activos: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return [];
    }
}

/**
 * Obtiene un sorteo específico por ID
 * @param int $idSorteo ID del sorteo
 * @return array|null Array con los datos del sorteo o null si no existe
 */
function obtenerSorteoPorId($idSorteo) {
    try {
        // Intentar cargar desde config/database.php primero
        if (file_exists(__DIR__ . '/../config/database.php')) {
            require_once __DIR__ . '/../config/database.php';
        } else {
            require_once __DIR__ . '/../../config/database.php';
        }
        $db = getDB();
        
        $stmt = $db->prepare("
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
            WHERE s.id_sorteo = :id_sorteo
            GROUP BY s.id_sorteo
        ");
        
        $stmt->execute([':id_sorteo' => $idSorteo]);
        $sorteo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$sorteo) {
            return null;
        }
        
        $boletosVendidos = intval($sorteo['boletos_vendidos']);
        $boletosTotales = intval($sorteo['total_boletos_crear']);
        
        $porcentajeVendido = $boletosTotales > 0 
            ? round(($boletosVendidos / $boletosTotales) * 100, 1) 
            : 0;
        
        $fechaFin = new DateTime($sorteo['fecha_fin']);
        $ahora = new DateTime();
        $diferencia = $ahora->diff($fechaFin);
        
        $tiempoRestante = [
            'dias' => intval($diferencia->format('%a')),
            'horas' => intval($diferencia->format('%h')),
            'minutos' => intval($diferencia->format('%i')),
            'segundos' => intval($diferencia->format('%s')),
            'total_segundos' => ($fechaFin->getTimestamp() - $ahora->getTimestamp())
        ];
        
        return [
            'id_sorteo' => $sorteo['id_sorteo'],
            'titulo' => $sorteo['titulo'],
            'descripcion' => $sorteo['descripcion'] ?? '',
            'precio_boleto' => floatval($sorteo['precio_boleto']),
            'total_boletos' => $boletosTotales,
            'boletos_vendidos' => $boletosVendidos,
            'boletos_reservados' => intval($sorteo['boletos_reservados']),
            'boletos_disponibles' => intval($sorteo['boletos_disponibles']),
            'porcentaje_vendido' => $porcentajeVendido,
            'fecha_inicio' => $sorteo['fecha_inicio'],
            'fecha_fin' => $sorteo['fecha_fin'],
            'imagen_url' => $sorteo['imagen_url'] ?? '',
            'estado' => $sorteo['estado'],
            'tiempo_restante' => $tiempoRestante,
            'id_creador' => $sorteo['id_creador']
        ];
        
    } catch (PDOException $e) {
        error_log("Error al obtener sorteo por ID: " . $e->getMessage());
        return null;
    } catch (Exception $e) {
        error_log("Error general al obtener sorteo por ID: " . $e->getMessage());
        return null;
    }
}
?>
