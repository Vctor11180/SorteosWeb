-- Script para corregir las características del sorteo 1 (iPhone)
-- Ejecuta esto en phpMyAdmin en la pestaña SQL

-- Actualizar con las características correctas de iPhone
UPDATE sorteos 
SET caracteristicas = '{
  "almacenamiento": "256 GB",
  "pantalla": "6.7 pulgadas",
  "camara": "48 MP Triple",
  "garantia": "1 año Apple Care"
}' 
WHERE id_sorteo = 1;

-- Verificar que se actualizó correctamente
SELECT id_sorteo, titulo, caracteristicas 
FROM sorteos 
WHERE id_sorteo = 1;
