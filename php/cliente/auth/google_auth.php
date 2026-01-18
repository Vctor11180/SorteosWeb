<?php
/**
 * google_auth.php - Manejo de autenticación con Google
 */
require_once __DIR__ . '/../config/database.php';

// Configuración
header('Content-Type: application/json');
session_start();

$input = json_decode(file_get_contents('php://input'), true);
$idToken = $input['credential'] ?? '';
$clientId = '776005870524-pthjf47lsf07l1ck5o03660cc9malctd.apps.googleusercontent.com';

if (!$idToken) {
    echo json_encode(['success' => false, 'error' => 'No token provided']);
    exit;
}

try {
    // Verificar el token con Google
    $url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $idToken;
    $response = file_get_contents($url);
    $payload = json_decode($response, true);

    if (!$payload || isset($payload['error_description'])) {
        throw new Exception('Token inválido: ' . ($payload['error_description'] ?? 'Error desconocido'));
    }

    // Verificar aud (Client ID)
    if ($payload['aud'] !== $clientId) {
        throw new Exception('Client ID no coincide');
    }

    $googleId = $payload['sub'];
    $email = $payload['email'];
    $name = $payload['name'];
    $picture = $payload['picture'] ?? '';
    $givenName = $payload['given_name'] ?? '';
    $familyName = $payload['family_name'] ?? '';

    $db = getDB();

    // 1. Buscar usuario por google_id
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE google_id = :google_id");
    $stmt->execute([':google_id' => $googleId]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Si no existe por google_id, buscar por email (para vincular cuentas existentes)
    if (!$usuario) {
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            // Vincular cuenta existente
            $stmt = $db->prepare("UPDATE usuarios SET google_id = :google_id, picture = :picture WHERE id_usuario = :id");
            $stmt->execute([
                ':google_id' => $googleId,
                ':picture' => $picture,
                ':id' => $usuario['id_usuario']
            ]);
            // Recargar usuario
            $usuario['google_id'] = $googleId;
            $usuario['picture'] = $picture;
        }
    }

    // 3. Crear usuario si no existe
    if (!$usuario) {
        // Obtener rol Cliente
        $stmt = $db->prepare("SELECT id_rol FROM roles WHERE nombre_rol = 'Cliente'");
        $stmt->execute();
        $rol = $stmt->fetch(PDO::FETCH_ASSOC);
        $rolId = $rol['id_rol'] ?? 2; // Default 2

        $stmt = $db->prepare("
            INSERT INTO usuarios (
                primer_nombre, 
                apellido_paterno, 
                email, 
                google_id, 
                picture, 
                id_rol, 
                estado, 
                saldo_disponible,
                fecha_nacimiento -- Requerido por la tabla, pondremos default para Google
            ) VALUES (
                :nombre,
                :apellido,
                :email,
                :google_id,
                :picture,
                :id_rol,
                'Activo',
                0.00,
                CURDATE()
            )
        ");
        
        $stmt->execute([
            ':nombre' => $givenName ?: $name,
            ':apellido' => $familyName ?: 'Google User',
            ':email' => $email,
            ':google_id' => $googleId,
            ':picture' => $picture,
            ':id_rol' => $rolId
        ]);
        
        $nuevoId = $db->lastInsertId();
        
        // Obtener el usuario creado
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE id_usuario = :id");
        $stmt->execute([':id' => $nuevoId]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 4. Iniciar sesión
    $_SESSION['usuario_id'] = $usuario['id_usuario']; // Asegurar compatibilidad (algunos scripts usan usuario_id)
    $_SESSION['id_usuario'] = $usuario['id_usuario'];
    $_SESSION['usuario_nombre'] = $usuario['primer_nombre'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_rol'] = 'Cliente'; // Asumimos cliente para Google Login por ahora
    $_SESSION['is_logged_in'] = true;

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log("Error Google Auth: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
