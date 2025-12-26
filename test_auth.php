<?php
/**
 * Script de prueba de autenticación
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Usuario.php';

echo "=== PRUEBA DE AUTENTICACIÓN ===\n\n";

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("✗ Error: No se pudo conectar a la base de datos\n");
}
echo "✓ Conexión a base de datos: OK\n";

$usuarioModel = new Usuario($db);

// Probar autenticación
$username = 'admin';
$password = 'admin123';

echo "\nIntentando autenticar:\n";
echo "  Usuario: $username\n";
echo "  Contraseña: $password\n\n";

$user = $usuarioModel->authenticate($username, $password);

if ($user) {
    echo "✓ AUTENTICACIÓN EXITOSA\n\n";
    echo "Datos del usuario:\n";
    echo "  ID: " . $user['id'] . "\n";
    echo "  Username: " . $user['username'] . "\n";
    echo "  Nombre: " . $user['nombre_completo'] . "\n";
    echo "  Email: " . $user['email'] . "\n";
    echo "  Rol: " . $user['rol_nombre'] . "\n";
    echo "  Activo: " . ($user['activo'] ? 'Sí' : 'No') . "\n";
    echo "\n✓ TODO ESTÁ FUNCIONANDO CORRECTAMENTE\n";
    echo "\nPuedes iniciar sesión en: " . BASE_URL . "\n";
} else {
    echo "✗ AUTENTICACIÓN FALLIDA\n";
    echo "Por favor verifica:\n";
    echo "  1. El usuario existe en la base de datos\n";
    echo "  2. El usuario está activo\n";
    echo "  3. La contraseña es correcta\n";
}

// NO eliminar este archivo para que puedas usarlo después
?>
