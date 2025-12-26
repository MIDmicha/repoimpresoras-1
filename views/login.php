<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/views/dashboard.php');
    exit;
}

$page_title = 'Iniciar Sesión';
$show_navbar = false;
ob_start();
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    body {
        margin: 0;
        padding: 0;
        height: 100vh;
        background: linear-gradient(rgba(17, 115, 185, 0.85), rgba(36, 49, 65, 0.95)),
                    url('<?php echo BASE_URL; ?>/assets/imagenes/2.png') no-repeat center center/cover;
        font-family: 'Inter', sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        color: white;
        overflow: hidden;
    }

    .login-container {
        display: flex;
        width: 1000px;
        height: 620px;
        background: rgb(0, 48, 151);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 30px 80px rgba(106, 154, 243, 0.7);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(6, 82, 246, 0.988);
        transform: translateX(-120%);
        opacity: 0;
        transition: all 1.2s ease;
    }

    .login-container.active {
        transform: translateX(0);
        opacity: 1;
    }

    .left-panel {
        flex: 1;
        padding: 80px 70px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: linear-gradient(135deg, rgba(113, 182, 225, 0.6), rgba(53, 75, 86, 0.8));
    }

    .logo {
        font-size: 52px;
        font-weight: 700;
        margin-bottom: 40px;
        letter-spacing: 2px;
    }

    .left-panel h1 {
        font-size: 44px;
        font-weight: 700;
        margin: 0 0 24px 0;
        line-height: 1.2;
    }

    .left-panel p {
        font-size: 19px;
        opacity: 0.9;
        line-height: 1.7;
    }

    /* inicio sesion */
    .right-panel {
        flex: 1;
        padding: 80px 70px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: rgba(93, 118, 146, 0.95);  
    }

    .right-panel h2 {
        font-size: 34px;
        text-align: left;
        margin-bottom: 50px;
        font-weight: 600;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-group input {
        width: 100%;
        padding: 16px 20px;
        background: rgba(26, 33, 56, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 12px;
        color: white;
        font-size: 16px;
        transition: all 0.3s;
    }

    /* bordes del registro*/
    .form-group input:focus {
        outline: none;
        background: rgba(255, 255, 255, 0.15);
        border-color: #0b1772;
        box-shadow: 0 0 20px rgba(6, 74, 143, 0.3);
    }

    .form-group input::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }

    /* color boton inicio sesion*/
    .submit-btn {
        width: 100%;
        padding: 16px;
        background: #0d2d48;
        border: none;
        border-radius: 12px;
        color: white;
        font-size: 18px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.4s;
        margin-top: 10px;
    }

    /* color boton inicio sesion*/
    .submit-btn:hover {
        background: #2a568b;
        transform: translateY(-4px);
        box-shadow: 0 15px 30px rgb(91, 129, 164); 
    }

    .social-login {
        margin-top: 30px;
        display: flex;
        justify-content: center;
        gap: 15px;
    }

    .social-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 12px 24px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        color: white;
        text-decoration: none;
        font-size: 15px;
        transition: all 0.3s;
        width: 140px;
    }

    .social-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
    }

    .test-credentials {
        margin-top: 30px;
        text-align: center;
        font-size: 14px;
        opacity: 0.8;
    }

    .alert-danger {
        position: absolute;
        top: 30px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(239, 68, 68, 0.95);
        padding: 14px 30px;
        border-radius: 12px;
        font-size: 15px;
        z-index: 10;
    }

    /* Estilo para checkbox Recuérdame */
    .form-check {
        margin-top: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-check-input {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #0d2d48;
    }

    .form-check-label {
        color: rgba(255, 255, 255, 0.9);
        font-size: 15px;
        cursor: pointer;
        user-select: none;
    }
</style>

<div class="login-container" id="loginPanel">
    <?php if (isset($_GET['error'])): ?>
        <div class="alert-danger">
            <?php
            $errors = [
                'invalid' => 'Usuario o contraseña incorrectos',
                'inactive' => 'Usuario inactivo. Contacte al administrador',
                'required' => 'Usuario y contraseña son requeridos'
            ];
            echo htmlspecialchars($errors[$_GET['error']] ?? 'Error en la autenticación');
            ?>
        </div>
    <?php endif; ?>

    <div class="left-panel">
        <h1>Gestión Inteligente<br>de Impresoras</h1>
        <p>Control total de mantenimiento, monitoreo en tiempo real y optimización de equipos tecnológicos.</p>
    </div>

    <div class="right-panel">
        <h2>Iniciar Sesión</h2>

        <form method="POST" action="<?php echo BASE_URL; ?>/controllers/auth.php?action=authenticate">
            <div class="form-group">
                <input type="text" name="usuario" placeholder="Usuario" required autocomplete="username" autofocus>
            </div>

            <div class="form-group">
                <input type="password" name="contrasena" placeholder="Contraseña" required autocomplete="current-password">
            </div>

            <button type="submit" class="submit-btn">Iniciar Sesión</button>

            <!-- Checkbox Recuérdame bien visible y estilizado -->
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="recordar" name="recordar">
                <label class="form-check-label" for="recordar">
                    Recuérdame
                </label>
            </div>
        </form>
    </div>
</div>

<script>
    const panel = document.getElementById('loginPanel');

    // Animación de entrada desde la izquierda
    setTimeout(() => {
        panel.classList.add('active');
    }, 300);
</script>

<?php
$content = ob_get_clean();
include BASE_PATH . '/views/layout.php';
?>