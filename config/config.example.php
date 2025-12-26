<?php
/**
 * Configuración general del sistema - EJEMPLO
 * Copiar este archivo como config.php y ajustar los valores
 */

// Configuración de sesión (DEBE IR ANTES de session_start())
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Cambiar a 1 si usas HTTPS
    session_start(); // Iniciar la sesión
}

// Zona horaria
date_default_timezone_set('America/Lima');

// Configuración de errores (cambiar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Rutas del sistema - AJUSTAR SEGÚN TU ENTORNO
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', 'http://localhost/impresoras'); // Cambiar según tu configuración

// Configuración de la aplicación
define('APP_NAME', 'Sistema de Control de Fotocopiadoras');
define('APP_VERSION', '1.0.0');

// Configuración de uploads
define('UPLOAD_PATH', BASE_PATH . '/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB en bytes

// Tipos de archivo permitidos para imágenes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif']);

// Configuración de paginación
define('ITEMS_PER_PAGE', 10);

// Otras configuraciones
define('SESSION_TIMEOUT', 3600); // 1 hora en segundos
?>
