-- Script para agregar campo de características dinámicas a la tabla sorteos
-- Sistema de Sorteos Web

-- Agregar columna caracteristicas como JSON
ALTER TABLE sorteos 
ADD COLUMN caracteristicas JSON NULL 
AFTER imagen_url;

-- Ejemplo de uso:
-- Para un auto:
-- UPDATE sorteos SET caracteristicas = '{"velocidad_maxima": "320 km/h", "motorizacion": "V8 Biturbo 4.0L", "modelo": "Edición Especial 2024", "garantia": "3 años extendida"}' WHERE id_sorteo = 1;
--
-- Para un iPhone:
-- UPDATE sorteos SET caracteristicas = '{"almacenamiento": "256 GB", "pantalla": "6.7 pulgadas", "camara": "48 MP Triple", "garantia": "1 año Apple Care"}' WHERE id_sorteo = 2;
--
-- Para un electrodoméstico:
-- UPDATE sorteos SET caracteristicas = '{"capacidad": "25 litros", "potencia": "800W", "modelo": "Ultra Pro 2024", "garantia": "2 años"}' WHERE id_sorteo = 3;
