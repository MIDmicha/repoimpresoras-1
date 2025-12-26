<?php
/**
 * Página de inicio - Redirige al login o dashboard según autenticación
 */

session_start();

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header('Location: views/dashboard.php');
} else {
    header('Location: controllers/auth.php');
}
exit();
?>
