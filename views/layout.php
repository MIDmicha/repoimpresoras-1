<?php
/**
 * Layout principal del sistema
 * Utilizado como plantilla base para todas las vistas
 */

// Incluir configuración si no está incluida
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../config/config.php';
}

// Valores por defecto
$page_title = $page_title ?? APP_NAME;
$show_navbar = $show_navbar ?? true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Estilos personalizados -->
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body>
    <?php if ($show_navbar): ?>
        <?php include BASE_PATH . '/includes/header.php'; ?>
    <?php endif; ?>
    
    <main>
        <?php echo $content ?? ''; ?>
    </main>
    
    <?php if ($show_navbar): ?>
        <?php include BASE_PATH . '/includes/footer.php'; ?>
    <?php endif; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (opcional) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Scripts personalizados -->
    <script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
