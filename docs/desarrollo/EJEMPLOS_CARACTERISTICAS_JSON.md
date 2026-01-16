# Ejemplos de Uso de Características JSON para Sorteos

Este documento muestra cómo usar el campo `caracteristicas` JSON en la tabla `sorteos` para almacenar características dinámicas de cada sorteo.

## Formato

El campo `caracteristicas` almacena un objeto JSON con pares clave-valor donde:
- **Clave**: Nombre de la característica (ej: `velocidad_maxima`, `almacenamiento`, `capacidad`)
- **Valor**: Valor de la característica como string (ej: `"320 km/h"`, `"256 GB"`)

## Ejemplos por Tipo de Producto

### 1. Automóvil / Vehículo

```sql
UPDATE sorteos 
SET caracteristicas = '{
  "velocidad_maxima": "320 km/h",
  "motorizacion": "V8 Biturbo 4.0L",
  "modelo": "Edición Especial 2024",
  "garantia": "3 años extendida"
}' 
WHERE id_sorteo = 1;
```

### 2. Dispositivo Móvil (iPhone)

```sql
UPDATE sorteos 
SET caracteristicas = '{
  "almacenamiento": "256 GB",
  "pantalla": "6.7 pulgadas",
  "camara": "48 MP Triple",
  "garantia": "1 año Apple Care"
}' 
WHERE id_sorteo = 2;
```

### 3. Electrodoméstico (Microondas)

```sql
UPDATE sorteos 
SET caracteristicas = '{
  "capacidad": "25 litros",
  "potencia": "800W",
  "modelo": "Ultra Pro 2024",
  "garantia": "2 años"
}' 
WHERE id_sorteo = 3;
```

### 4. Smart TV

```sql
UPDATE sorteos 
SET caracteristicas = '{
  "pantalla": "55 pulgadas",
  "resolucion": "4K Ultra HD",
  "smart": "Android TV",
  "garantia": "2 años"
}' 
WHERE id_sorteo = 4;
```

### 5. Consola de Videojuegos

```sql
UPDATE sorteos 
SET caracteristicas = '{
  "almacenamiento": "1 TB SSD",
  "resolucion": "4K 120Hz",
  "color": "Blanco",
  "garantia": "1 año"
}' 
WHERE id_sorteo = 5;
```

### 6. Laptop

```sql
UPDATE sorteos 
SET caracteristicas = '{
  "procesador": "Intel Core i7",
  "ram": "16 GB",
  "almacenamiento": "512 GB SSD",
  "pantalla": "15.6 pulgadas",
  "garantia": "2 años"
}' 
WHERE id_sorteo = 6;
```

### 7. Bicicleta

```sql
UPDATE sorteos 
SET caracteristicas = '{
  "modelo": "Mountain Pro 2024",
  "marca": "Specialized",
  "color": "Negro/Rojo",
  "garantia": "5 años cuadro"
}' 
WHERE id_sorteo = 7;
```

## Mapeo de Iconos

El sistema automáticamente asigna iconos según el nombre de la característica:

| Nombre de Característica | Icono |
|-------------------------|-------|
| `velocidad_maxima`, `velocidad máxima` | `speed` |
| `motorizacion`, `motorización` | `settings` |
| `modelo` | `calendar_month` |
| `garantia`, `garantía` | `verified_user` |
| `almacenamiento`, `capacidad` | `storage` |
| `pantalla` | `phone_iphone` |
| `camara`, `cámara` | `camera_alt` |
| `bateria`, `batería` | `battery_charging_full` |
| `procesador`, `memoria`, `ram` | `memory` |
| `potencia` | `bolt` |
| `tamaño` | `straighten` |
| `peso` | `scale` |
| `color` | `palette` |
| `marca` | `category` |
| Por defecto | `info` |

## Notas Importantes

1. **Formato JSON**: El campo debe contener un JSON válido. Usa comillas dobles para las claves y valores string.

2. **Caracteres especiales**: Si necesitas usar comillas dentro de un valor, escápalas con `\"`.

3. **Características opcionales**: Si un sorteo no tiene características, deja el campo como `NULL` o un objeto JSON vacío `{}`.

4. **Sin límite de características**: Puedes agregar tantas características como necesites por sorteo.

5. **Renderizado dinámico**: El frontend solo mostrará las características que existan en el JSON. Si no hay características, simplemente no se mostrará la sección.

## Ejemplo de Inserción Completa

```sql
INSERT INTO sorteos (
    titulo, 
    descripcion, 
    precio_boleto, 
    total_boletos_crear, 
    fecha_inicio, 
    fecha_fin, 
    imagen_url, 
    caracteristicas, 
    estado, 
    id_creador
) VALUES (
    'iPhone 15 Pro Max',
    'Gana el último iPhone con todas las características premium.',
    25.00,
    1000,
    '2024-01-01 00:00:00',
    '2024-12-31 23:59:59',
    'https://ejemplo.com/iphone.jpg',
    '{
        "almacenamiento": "256 GB",
        "pantalla": "6.7 pulgadas",
        "camara": "48 MP Triple",
        "garantia": "1 año Apple Care"
    }',
    'Activo',
    1
);
```
