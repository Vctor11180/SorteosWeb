-- Script SQL para actualizar las contraseñas de los usuarios
-- Ejecutar este script en phpMyAdmin después de ejecutar el script PHP
-- O usar el script PHP que genera los hashes automáticamente

-- NOTA: Este script muestra cómo deberían ser los hashes, pero es mejor usar el script PHP
-- porque password_hash() genera hashes únicos cada vez

-- Para el administrador (admin@sorteos.com)
-- Contraseña: admin123
UPDATE usuarios 
SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE email = 'admin@sorteos.com';

-- Para lucia.f@email.com
-- Contraseña: lucia123
UPDATE usuarios 
SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE email = 'lucia.f@email.com';

-- Para roberto.p@email.com
-- Contraseña: roberto123
UPDATE usuarios 
SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE email = 'roberto.p@email.com';

-- Para maria.l@email.com
-- Contraseña: maria123
UPDATE usuarios 
SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE email = 'maria.l@email.com';

-- IMPORTANTE: Los hashes de arriba son ejemplos. 
-- Usa el script PHP actualizar_contraseñas_usuarios.php para generar hashes únicos y seguros.
