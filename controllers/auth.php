<?php
/**
 * Controlador de Autenticación
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../includes/functions.php';

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_GET['action'] ?? 'login';

switch ($action) {
    case 'login':
        showLoginForm();
        break;
        
    case 'authenticate':
        authenticate();
        break;
        
    case 'logout':
        logout();
        break;
        
    default:
        showLoginForm();
}

/**
 * Mostrar formulario de login
 */
function showLoginForm() {
    if (isLoggedIn()) {
        redirect('views/dashboard.php');
    }
    include __DIR__ . '/../views/login.php';
}

/**
 * Autenticar usuario
 */
function authenticate() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('controllers/auth.php');
    }
    
    // Soportar tanto 'username' como 'usuario' para compatibilidad
    $username = sanitize($_POST['usuario'] ?? $_POST['username'] ?? '');
    $password = $_POST['contrasena'] ?? $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        redirect('controllers/auth.php?error=required');
    }
    
    $database = new Database();
    $db = $database->getConnection();
    $usuarioModel = new Usuario($db);
    
    $user = $usuarioModel->authenticate($username, $password);
    
    if ($user) {
        // Verificar si el usuario está activo
        if (!$user['activo']) {
            redirect('controllers/auth.php?error=inactive');
        }
        
        // Regenerar ID de sesión para prevenir session fixation
        session_regenerate_id(true);
        
        // Guardar datos en sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nombre_completo'] = $user['nombre_completo'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['rol_id'] = $user['id_rol'];
        $_SESSION['rol_nombre'] = $user['rol_nombre'];
        
        // Registrar auditoría
        logAudit($db, 'usuarios', $user['id'], 'LOGIN', null, ['username' => $username]);
        
        setFlashMessage('success', '¡Bienvenido ' . $user['nombre_completo'] . '!');
        redirect('views/dashboard.php');
    } else {
        redirect('controllers/auth.php?error=invalid');
    }
}

/**
 * Cerrar sesión
 */
function logout() {
    if (isLoggedIn()) {
        $database = new Database();
        $db = $database->getConnection();
        logAudit($db, 'usuarios', $_SESSION['user_id'], 'LOGOUT', null, ['username' => $_SESSION['username']]);
    }
    
    // Destruir sesión
    session_unset();
    session_destroy();
    
    // Iniciar nueva sesión para el mensaje
    session_start();
    setFlashMessage('info', 'Sesión cerrada correctamente');
    
    redirect('controllers/auth.php');
}
?>
