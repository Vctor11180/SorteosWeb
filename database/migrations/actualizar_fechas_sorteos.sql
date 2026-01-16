-- Script para actualizar las fechas de los sorteos activos a fechas futuras
-- Ejecuta este script en phpMyAdmin para que los sorteos se muestren correctamente

-- Actualizar fecha_fin de sorteos activos a 30 días desde hoy
UPDATE sorteos 
SET fecha_fin = DATE_ADD(NOW(), INTERVAL 30 DAY)
WHERE estado = 'Activo' AND fecha_fin <= NOW();

-- También actualizar fecha_inicio si es necesario (7 días antes de fecha_fin)
UPDATE sorteos 
SET fecha_inicio = DATE_SUB(fecha_fin, INTERVAL 7 DAY)
WHERE estado = 'Activo' AND fecha_inicio >= fecha_fin;

-- Verificar los cambios
SELECT 
    id_sorteo,
    titulo,
    estado,
    fecha_inicio,
    fecha_fin,
    CASE 
        WHEN fecha_fin > NOW() THEN '✅ Válido' 
        ELSE '❌ Fecha pasada' 
    END as estado_fecha
FROM sorteos 
WHERE estado = 'Activo'
ORDER BY id_sorteo;
