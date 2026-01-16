-- Inserts para la tabla sorteos
-- Sistema de Sorteos Web
-- 
-- IMPORTANTE: Asegúrate de tener al menos un usuario con id_creador válido
-- Ejemplo: INSERT INTO usuarios (...) VALUES (...); -- Crear usuario admin primero
--
-- Si ya tienes usuarios, ajusta los id_creador según corresponda
-- Por defecto, estos inserts usan id_creador = 1 (asume que existe)

-- ============================================
-- SORTEOS ACTIVOS (Para mostrar en el listado)
-- ============================================

-- 1. iPhone 15 Pro Max
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
    'Participa para ganar el último iPhone 15 Pro Max con todas las características premium. Pantalla Super Retina XDR de 6.7 pulgadas, chip A17 Pro, sistema de cámaras Pro avanzado y hasta 1TB de almacenamiento.',
    10.00,
    100,
    NOW(),
    DATE_ADD(NOW(), INTERVAL 30 DAY),
    '',
    JSON_OBJECT(
        'almacenamiento', '256 GB',
        'pantalla', '6.7 pulgadas Super Retina XDR',
        'camara', '48 MP Triple con Zoom Óptico 5x',
        'procesador', 'A17 Pro',
        'bateria', 'Hasta 29 horas de video',
        'garantia', '1 año Apple Care+',
        'color', 'Titanio Natural'
    ),
    'Activo',
    1
);

-- 2. Automóvil Deportivo
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
    'BMW M4 Competition 2024',
    'Gana este impresionante BMW M4 Competition. Potencia extrema, diseño agresivo y tecnología de vanguardia. El auto deportivo que siempre soñaste.',
    25.00,
    200,
    NOW(),
    DATE_ADD(NOW(), INTERVAL 45 DAY),
    '',
    JSON_OBJECT(
        'velocidad_maxima', '290 km/h',
        'motorizacion', '3.0L Twin-Turbo Inline-6',
        'potencia', '510 HP',
        'transmision', 'Automática de 8 velocidades',
        'modelo', 'M4 Competition 2024',
        'garantia', '3 años extendida',
        'color', 'Azul Marina'
    ),
    'Activo',
    1
);

-- 3. Smart TV 4K
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
    'Samsung QLED 55" 4K Ultra HD',
    'Televisor inteligente Samsung QLED de 55 pulgadas con resolución 4K Ultra HD. Tecnología Quantum Dot para colores increíbles y Android TV integrado.',
    5.00,
    150,
    NOW(),
    DATE_ADD(NOW(), INTERVAL 25 DAY),
    '',
    JSON_OBJECT(
        'pantalla', '55 pulgadas',
        'resolucion', '4K Ultra HD (3840 x 2160)',
        'smart', 'Android TV',
        'hdr', 'HDR10+',
        'sonido', 'Dolby Atmos',
        'garantia', '2 años',
        'conectividad', 'WiFi, Bluetooth, HDMI x4'
    ),
    'Activo',
    1
);

-- 4. Laptop Gaming
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
    'ASUS ROG Strix G15 Gaming',
    'Laptop gaming de alto rendimiento. Perfecta para gaming, streaming y trabajo profesional. Equipada con las últimas tecnologías.',
    15.00,
    120,
    NOW(),
    DATE_ADD(NOW(), INTERVAL 35 DAY),
    '',
    JSON_OBJECT(
        'procesador', 'AMD Ryzen 9 5900HX',
        'ram', '16 GB DDR4',
        'almacenamiento', '512 GB SSD NVMe',
        'pantalla', '15.6 pulgadas Full HD 144Hz',
        'tarjeta_grafica', 'NVIDIA RTX 3060 6GB',
        'garantia', '2 años',
        'sistema_operativo', 'Windows 11'
    ),
    'Activo',
    1
);

-- 5. PlayStation 5
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
    'PlayStation 5 + 2 Juegos',
    'Consola PlayStation 5 con disco incluida. Incluye 2 juegos exclusivos: Spider-Man 2 y God of War Ragnarök. La experiencia de gaming definitiva.',
    8.00,
    80,
    NOW(),
    DATE_ADD(NOW(), INTERVAL 20 DAY),
    '',
    JSON_OBJECT(
        'almacenamiento', '825 GB SSD',
        'resolucion', '4K 120Hz',
        'color', 'Blanco',
        'juegos_incluidos', 'Spider-Man 2, God of War Ragnarök',
        'garantia', '1 año',
        'conectividad', 'WiFi 6, Bluetooth 5.1'
    ),
    'Activo',
    1
);

-- 6. Bicicleta Eléctrica
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
    'Bicicleta Eléctrica Specialized Turbo',
    'Bicicleta eléctrica de alta gama. Perfecta para la ciudad y montaña. Autonomía de hasta 80 km y motor de 250W.',
    12.00,
    90,
    NOW(),
    DATE_ADD(NOW(), INTERVAL 28 DAY),
    '',
    JSON_OBJECT(
        'marca', 'Specialized',
        'motor', '250W',
        'bateria', '500Wh (80 km autonomía)',
        'color', 'Negro/Rojo',
        'peso', '22 kg',
        'garantia', '5 años cuadro, 2 años componentes'
    ),
    'Activo',
    1
);

-- ============================================
-- SORTEOS EN BORRADOR (Para editar antes de activar)
-- ============================================

-- 7. MacBook Pro (Borrador)
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
    'MacBook Pro 16" M3 Max',
    'Laptop profesional de Apple con chip M3 Max. Perfecta para diseñadores, desarrolladores y creadores de contenido.',
    20.00,
    150,
    DATE_ADD(NOW(), INTERVAL 5 DAY),
    DATE_ADD(NOW(), INTERVAL 40 DAY),
    '',
    JSON_OBJECT(
        'procesador', 'Apple M3 Max',
        'ram', '32 GB',
        'almacenamiento', '1 TB SSD',
        'pantalla', '16.2 pulgadas Liquid Retina XDR',
        'garantia', '1 año Apple Care+',
        'color', 'Space Gray'
    ),
    'Borrador',
    1
);

-- 8. AirPods Pro (Borrador)
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
    'AirPods Pro 2da Generación',
    'Auriculares inalámbricos con cancelación activa de ruido. Sonido espacial y resistencia al agua.',
    3.00,
    200,
    DATE_ADD(NOW(), INTERVAL 3 DAY),
    DATE_ADD(NOW(), INTERVAL 18 DAY),
    '',
    JSON_OBJECT(
        'cancelacion_ruido', 'Activa',
        'bateria', 'Hasta 6 horas (30 horas con estuche)',
        'resistencia', 'IPX4 (agua y sudor)',
        'conectividad', 'Bluetooth 5.3',
        'garantia', '1 año',
        'color', 'Blanco'
    ),
    'Borrador',
    1
);

-- ============================================
-- SORTEOS FINALIZADOS (Para historial)
-- ============================================

-- 9. iPhone 14 Pro (Finalizado)
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
    'iPhone 14 Pro',
    'iPhone 14 Pro con chip A16 Bionic y sistema de cámaras Pro.',
    10.00,
    100,
    DATE_SUB(NOW(), INTERVAL 60 DAY),
    DATE_SUB(NOW(), INTERVAL 30 DAY),
    '',
    JSON_OBJECT(
        'almacenamiento', '128 GB',
        'pantalla', '6.1 pulgadas Super Retina XDR',
        'camara', '48 MP Triple',
        'procesador', 'A16 Bionic',
        'garantia', '1 año',
        'color', 'Morado Profundo'
    ),
    'Finalizado',
    1
);

-- 10. Nintendo Switch (Finalizado)
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
    'Nintendo Switch OLED',
    'Consola Nintendo Switch con pantalla OLED mejorada. Incluye Mario Kart 8 Deluxe.',
    6.00,
    100,
    DATE_SUB(NOW(), INTERVAL 45 DAY),
    DATE_SUB(NOW(), INTERVAL 15 DAY),
    '',
    JSON_OBJECT(
        'pantalla', '7 pulgadas OLED',
        'almacenamiento', '64 GB (expandible)',
        'juegos_incluidos', 'Mario Kart 8 Deluxe',
        'garantia', '1 año',
        'color', 'Blanco'
    ),
    'Finalizado',
    1
);

-- ============================================
-- SORTEOS PAUSADOS (Temporalmente detenidos)
-- ============================================

-- 11. iPad Pro (Pausado)
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
    'iPad Pro 12.9" M2',
    'Tablet profesional de Apple con chip M2. Perfecta para diseño, edición de video y productividad.',
    18.00,
    120,
    DATE_SUB(NOW(), INTERVAL 10 DAY),
    DATE_ADD(NOW(), INTERVAL 20 DAY),
    '',
    JSON_OBJECT(
        'procesador', 'Apple M2',
        'pantalla', '12.9 pulgadas Liquid Retina XDR',
        'almacenamiento', '256 GB',
        'ram', '8 GB',
        'garantia', '1 año Apple Care+',
        'color', 'Space Gray'
    ),
    'Pausado',
    1
);

-- ============================================
-- SORTEOS VARIADOS (Más ejemplos)
-- ============================================

-- 12. Microondas Premium
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
    'Microondas LG NeoChef 25L',
    'Microondas inteligente con tecnología Inverter. Capacidad de 25 litros y múltiples funciones de cocción.',
    4.00,
    180,
    NOW(),
    DATE_ADD(NOW(), INTERVAL 22 DAY),
    '',
    JSON_OBJECT(
        'capacidad', '25 litros',
        'potencia', '1000W',
        'tecnologia', 'Inverter',
        'funciones', 'Descongelar, Calentar, Cocinar, Grill',
        'garantia', '2 años',
        'color', 'Negro'
    ),
    'Activo',
    1
);

-- 13. Reloj Inteligente
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
    'Apple Watch Series 9',
    'Reloj inteligente con GPS, monitor de salud avanzado y resistencia al agua. Perfecto para deportistas.',
    7.00,
    140,
    NOW(),
    DATE_ADD(NOW(), INTERVAL 32 DAY),
    '',
    JSON_OBJECT(
        'pantalla', '45mm Always-On Retina',
        'bateria', 'Hasta 18 horas',
        'resistencia', '50m (natación)',
        'gps', 'Integrado',
        'garantia', '1 año',
        'color', 'Midnight'
    ),
    'Activo',
    1
);

-- 14. Cámara Profesional
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
    'Canon EOS R6 Mark II',
    'Cámara mirrorless profesional. Perfecta para fotografía y video. Incluye lente 24-105mm.',
    30.00,
    100,
    NOW(),
    DATE_ADD(NOW(), INTERVAL 50 DAY),
    '',
    JSON_OBJECT(
        'sensor', 'Full Frame 24.2 MP',
        'video', '4K 60fps',
        'lente', 'RF 24-105mm f/4L IS',
        'estabilizacion', '5 ejes',
        'garantia', '2 años',
        'color', 'Negro'
    ),
    'Activo',
    1
);

-- 15. Refrigerador Inteligente
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
    'Samsung Family Hub Refrigerador',
    'Refrigerador inteligente con pantalla táctil de 21.5". Controla tu hogar desde la nevera.',
    22.00,
    80,
    NOW(),
    DATE_ADD(NOW(), INTERVAL 55 DAY),
    '',
    JSON_OBJECT(
        'capacidad', '28 pies cúbicos',
        'tecnologia', 'Twin Cooling Plus',
        'pantalla', '21.5 pulgadas táctil',
        'smart', 'WiFi, Bixby, Spotify',
        'garantia', '10 años compresor',
        'color', 'Negro acero'
    ),
    'Activo',
    1
);

-- ============================================
-- NOTAS IMPORTANTES
-- ============================================

-- 1. Asegúrate de tener al menos un usuario con id_creador = 1
--    Si no, ajusta todos los id_creador al ID de un usuario existente
--
-- 2. Después de insertar los sorteos, ejecuta el script crear_boletos_faltantes.php
--    para crear los boletos físicos en la tabla boletos
--
-- 3. Las fechas están configuradas para:
--    - Activos: Inician ahora y terminan en 20-55 días
--    - Borradores: Inician en 3-5 días
--    - Finalizados: Ya terminaron hace 15-30 días
--    - Pausados: Iniciaron hace 10 días pero están pausados
--
-- 4. El campo caracteristicas usa JSON_OBJECT() para MySQL 5.7+
--    Si usas MySQL 5.6 o anterior, usa JSON válido como string:
--    caracteristicas = '{"almacenamiento": "256 GB", "pantalla": "6.7 pulgadas"}'
--
-- 5. Puedes ajustar precios, cantidad de boletos y fechas según tus necesidades

-- ============================================
-- VERIFICACIÓN
-- ============================================

-- Para verificar los sorteos insertados:
-- SELECT id_sorteo, titulo, estado, precio_boleto, total_boletos_crear, fecha_inicio, fecha_fin 
-- FROM sorteos 
-- ORDER BY fecha_inicio DESC;

-- Para ver las características JSON:
-- SELECT id_sorteo, titulo, JSON_PRETTY(caracteristicas) as caracteristicas 
-- FROM sorteos 
-- WHERE caracteristicas IS NOT NULL;
