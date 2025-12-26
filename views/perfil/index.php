<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isLoggedIn()) {
    redirect('controllers/auth.php');
}

$page_title = 'Mi Perfil';

$database = new Database();
$db = $database->getConnection();

// Obtener datos del usuario actual
$stmt = $db->prepare("SELECT u.*, r.nombre as rol_nombre FROM usuarios u 
                      LEFT JOIN roles r ON u.id_rol = r.id 
                      WHERE u.id = :id");
$stmt->execute([':id' => $_SESSION['user_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener estadísticas del usuario
$sqlStats = "SELECT 
                COUNT(*) as total_acciones,
                SUM(CASE WHEN accion = 'INSERT' THEN 1 ELSE 0 END) as creaciones,
                SUM(CASE WHEN accion = 'UPDATE' THEN 1 ELSE 0 END) as modificaciones,
                SUM(CASE WHEN accion = 'DELETE' THEN 1 ELSE 0 END) as eliminaciones,
                MIN(fecha_hora) as primera_accion,
                MAX(fecha_hora) as ultima_accion
             FROM auditoria 
             WHERE id_usuario = :id_usuario";
$stmtStats = $db->prepare($sqlStats);
$stmtStats->execute([':id_usuario' => $_SESSION['user_id']]);
$stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

// Obtener actividad reciente del usuario
$sqlActividad = "SELECT 
                    tabla,
                    accion,
                    fecha_hora,
                    id_registro
                 FROM auditoria 
                 WHERE id_usuario = :id_usuario
                 ORDER BY fecha_hora DESC
                 LIMIT 20";
$stmtActividad = $db->prepare($sqlActividad);
$stmtActividad->execute([':id_usuario' => $_SESSION['user_id']]);
$actividad = $stmtActividad->fetchAll(PDO::FETCH_ASSOC);

$extra_js = <<<'EOD'
<script>
function actualizarPerfil() {
    const form = document.getElementById('formPerfil');
    const formData = new FormData(form);
    
    $.ajax({
        url: BASE_URL + '/controllers/perfil.php?action=actualizar',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.message,
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'No se pudo actualizar el perfil', 'error');
        }
    });
}

function cambiarPassword() {
    const passwordActual = $('#password_actual').val();
    const passwordNuevo = $('#password_nuevo').val();
    const passwordConfirmar = $('#password_confirmar').val();
    
    if (!passwordActual || !passwordNuevo || !passwordConfirmar) {
        Swal.fire('Error', 'Todos los campos son obligatorios', 'error');
        return;
    }
    
    if (passwordNuevo !== passwordConfirmar) {
        Swal.fire('Error', 'Las contraseñas nuevas no coinciden', 'error');
        return;
    }
    
    if (passwordNuevo.length < 6) {
        Swal.fire('Error', 'La contraseña debe tener al menos 6 caracteres', 'error');
        return;
    }
    
    $.ajax({
        url: BASE_URL + '/controllers/perfil.php?action=cambiarPassword',
        method: 'POST',
        data: {
            password_actual: passwordActual,
            password_nuevo: passwordNuevo
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.message,
                    confirmButtonText: 'OK'
                }).then(() => {
                    $('#modalPassword').modal('hide');
                    $('#formPassword')[0].reset();
                });
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'No se pudo cambiar la contraseña', 'error');
        }
    });
}
</script>
EOD;

include __DIR__ . '/../../includes/header.php';
?>
<style>
/* Solo para el cuadro de Información Personal */
.profile-form {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  padding: 20px;
  margin-bottom: 20px;
}

.profile-form h6 {
  color: var(--text-primary);
  margin-bottom: 20px;
}

/* Inputs dentro del formulario */
.profile-form .form-control {
  background: var(--bg-input);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius-sm);
}

/* Input disabled */
.profile-form .form-control:disabled {
  background: var(--bg-hover);
  color: var(--text-secondary);
}

/* Botón Cambiar Contraseña */
.profile-form .btn-secondary {
  background: var(--bg-hover);
  color: var(--text-primary);
  border: none;
}

/* Botón Guardar Cambios */
.profile-form .btn-primary {
  background: var(--primary-color);
  border-color: var(--primary-color);
}

/* Ajuste en modo oscuro */
[data-theme="dark"] .profile-form {
  background: var(--bg-card);
  border-color: var(--border-color);
}

[data-theme="dark"] .profile-form .form-control {
  background: var(--bg-input);
  color: var(--text-primary);
  border-color: var(--border-color);
}

[data-theme="dark"] .profile-form .form-control:disabled {
  background: #2d3748;
  color: var(--text-secondary);
}

/* Selector super específico para sobrescribir background #f8f9fa en modo oscuro */
[data-theme="dark"] div[style*="background: #f8f9fa"] {
    background: var(--bg-hover) !important;
}

/* Selector para sobrescribir color #666 en modo oscuro */
[data-theme="dark"] div[style*="color: #666"] {
    color: var(--text-secondary) !important;
}

/* Selector para sobrescribir color #999 en modo oscuro */
[data-theme="dark"] div[style*="color: #999"] {
    color: var(--text-muted) !important;
}

/* Contenedor principal del panel */
[data-theme="dark"] div[style*="border: 2px solid #000"] {
    border-color: var(--border-color) !important;
}

[data-theme="dark"] h6[style*="border-bottom: 1px solid #000"] {
    border-bottom-color: var(--border-color) !important;
}

/* Mantener tus colores de icono y borde izquierdo */
[data-theme="dark"] i[style*="color: #28a745"] { color: var(--success-color) !important; }
[data-theme="dark"] i[style*="color: #ffc107"] { color: var(--warning-color) !important; }
[data-theme="dark"] i[style*="color: #dc3545"] { color: var(--danger-color) !important; }

[data-theme="dark"] div[style*="border-left: 3px solid #28a745"] { border-left-color: var(--success-color) !important; }
[data-theme="dark"] div[style*="border-left: 3px solid #ffc107"] { border-left-color: var(--warning-color) !important; }
[data-theme="dark"] div[style*="border-left: 3px solid #dc3545"] { border-left-color: var(--danger-color) !important; }

/* ===== NEUMORFISMO PARA PERFIL ===== */

/* Avatar y Info Principal con neumorfismo */
.profile-avatar-card {
    background: var(--bg-card);
    border-radius: 15px;
    padding: 20px;
    text-align: center;
    margin-bottom: 20px;
    box-shadow: 
        8px 8px 16px rgba(0, 0, 0, 0.1),
        -8px -8px 16px rgba(255, 255, 255, 0.9);
    border: none !important;
    transition: all 0.3s ease;
}

.profile-avatar-card:hover {
    transform: translateY(-5px);
    box-shadow: 
        12px 12px 24px rgba(0, 0, 0, 0.15),
        -12px -12px 24px rgba(255, 255, 255, 1);
}

[data-theme="dark"] .profile-avatar-card {
    box-shadow: 
        8px 8px 16px rgba(0, 0, 0, 0.4),
        -8px -8px 16px rgba(255, 255, 255, 0.05);
}

[data-theme="dark"] .profile-avatar-card:hover {
    box-shadow: 
        12px 12px 24px rgba(0, 0, 0, 0.5),
        -12px -12px 24px rgba(255, 255, 255, 0.08);
}

/* Estadísticas con neumorfismo */
.profile-stats-card {
    background: var(--bg-card);
    border-radius: 15px;
    padding: 20px;
    box-shadow: 
        8px 8px 16px rgba(0, 0, 0, 0.1),
        -8px -8px 16px rgba(255, 255, 255, 0.9);
    border: none !important;
    transition: all 0.3s ease;
}

[data-theme="dark"] .profile-stats-card {
    box-shadow: 
        8px 8px 16px rgba(0, 0, 0, 0.4),
        -8px -8px 16px rgba(255, 255, 255, 0.05);
}

/* Tarjetas pequeñas de estadísticas */
.mini-stat-card {
    text-align: center;
    padding: 10px;
    border-radius: 10px;
    box-shadow: 
        4px 4px 8px rgba(0, 0, 0, 0.1),
        -4px -4px 8px rgba(255, 255, 255, 0.8);
    transition: all 0.2s ease;
}

.mini-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 
        6px 6px 12px rgba(0, 0, 0, 0.15),
        -6px -6px 12px rgba(255, 255, 255, 1);
}

[data-theme="dark"] .mini-stat-card {
    box-shadow: 
        4px 4px 8px rgba(0, 0, 0, 0.3),
        -4px -4px 8px rgba(255, 255, 255, 0.03);
}

[data-theme="dark"] .mini-stat-card:hover {
    box-shadow: 
        6px 6px 12px rgba(0, 0, 0, 0.4),
        -6px -6px 12px rgba(255, 255, 255, 0.05);
}

/* Formulario de Perfil con neumorfismo */
.profile-form-neomorphism {
    background: var(--bg-card);
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 
        8px 8px 16px rgba(0, 0, 0, 0.1),
        -8px -8px 16px rgba(255, 255, 255, 0.9);
    border: none !important;
}

[data-theme="dark"] .profile-form-neomorphism {
    box-shadow: 
        8px 8px 16px rgba(0, 0, 0, 0.4),
        -8px -8px 16px rgba(255, 255, 255, 0.05);
}

/* Actividad Reciente con neumorfismo */
.profile-activity-card {
    background: var(--bg-card);
    border-radius: 15px;
    padding: 20px;
    box-shadow: 
        8px 8px 16px rgba(0, 0, 0, 0.1),
        -8px -8px 16px rgba(255, 255, 255, 0.9);
    border: none !important;
}

[data-theme="dark"] .profile-activity-card {
    box-shadow: 
        8px 8px 16px rgba(0, 0, 0, 0.4),
        -8px -8px 16px rgba(255, 255, 255, 0.05);
}

/* Items de actividad individual */
.activity-item {
    padding: 12px;
    margin-bottom: 10px;
    border-radius: 10px;
    background: var(--bg-hover);
    box-shadow: 
        inset 2px 2px 4px rgba(0, 0, 0, 0.1),
        inset -2px -2px 4px rgba(255, 255, 255, 0.5);
    transition: all 0.2s ease;
}

.activity-item:hover {
    transform: translateX(5px);
    background: var(--bg-card);
    box-shadow: 
        4px 4px 8px rgba(0, 0, 0, 0.1),
        -4px -4px 8px rgba(255, 255, 255, 0.8);
}

[data-theme="dark"] .activity-item {
    box-shadow: 
        inset 2px 2px 4px rgba(0, 0, 0, 0.3),
        inset -2px -2px 4px rgba(255, 255, 255, 0.02);
}

[data-theme="dark"] .activity-item:hover {
    box-shadow: 
        4px 4px 8px rgba(0, 0, 0, 0.3),
        -4px -4px 8px rgba(255, 255, 255, 0.03);
}

/* Avatar circular con sombra */
.profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    color: white;
    font-size: 3em;
    font-weight: bold;
    box-shadow: 
        6px 6px 12px rgba(0, 0, 0, 0.2),
        -6px -6px 12px rgba(255, 255, 255, 0.7);
}

[data-theme="dark"] .profile-avatar {
    box-shadow: 
        6px 6px 12px rgba(0, 0, 0, 0.5),
        -6px -6px 12px rgba(255, 255, 255, 0.05);
}

/* ===== NEUMORFISMO PARA INPUTS DEL MODAL ===== */

/* Inputs de contraseña con neumorfismo */
.password-input-neomorphism {
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    background: var(--bg-input);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
    box-shadow: 
        inset 3px 3px 6px rgba(0, 0, 0, 0.1),
        inset -3px -3px 6px rgba(255, 255, 255, 0.5);
    transition: all 0.3s ease;
}

.password-input-neomorphism:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 
        inset 3px 3px 6px rgba(0, 0, 0, 0.15),
        inset -3px -3px 6px rgba(255, 255, 255, 0.7),
        0 0 0 3px rgba(102, 126, 234, 0.1);
}

[data-theme="dark"] .password-input-neomorphism {
    box-shadow: 
        inset 3px 3px 6px rgba(0, 0, 0, 0.4),
        inset -3px -3px 6px rgba(255, 255, 255, 0.03);
}

[data-theme="dark"] .password-input-neomorphism:focus {
    box-shadow: 
        inset 3px 3px 6px rgba(0, 0, 0, 0.5),
        inset -3px -3px 6px rgba(255, 255, 255, 0.05),
        0 0 0 3px rgba(102, 126, 234, 0.2);
}

/* Labels con mejor estilo */
.password-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--text-primary);
    font-size: 0.95em;
}

/* Contenedor de cada grupo de input */
.password-group {
    margin-bottom: 20px;
}

/* Small text para hints */
.password-hint {
    color: var(--text-secondary);
    font-size: 0.85em;
    margin-top: 5px;
    display: block;
}

/* Modal header y footer mejorados */
.modal-header {
    border-bottom: 1px solid var(--border-color);
    background: var(--bg-card);
}

.modal-body {
    background: var(--bg-card);
    padding: 25px;
}

.modal-footer {
    border-top: 1px solid var(--border-color);
    background: var(--bg-card);
}

/* Botones del modal con neumorfismo sutil */
.btn-neomorphism {
    border-radius: 10px;
    padding: 10px 20px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 
        4px 4px 8px rgba(0, 0, 0, 0.15),
        -4px -4px 8px rgba(255, 255, 255, 0.7);
}

.btn-neomorphism:hover {
    transform: translateY(-2px);
    box-shadow: 
        6px 6px 12px rgba(0, 0, 0, 0.2),
        -6px -6px 12px rgba(255, 255, 255, 0.9);
}

.btn-neomorphism:active {
    transform: translateY(0);
    box-shadow: 
        inset 2px 2px 4px rgba(0, 0, 0, 0.2),
        inset -2px -2px 4px rgba(255, 255, 255, 0.5);
}

[data-theme="dark"] .btn-neomorphism {
    box-shadow: 
        4px 4px 8px rgba(0, 0, 0, 0.4),
        -4px -4px 8px rgba(255, 255, 255, 0.05);
}

[data-theme="dark"] .btn-neomorphism:hover {
    box-shadow: 
        6px 6px 12px rgba(0, 0, 0, 0.5),
        -6px -6px 12px rgba(255, 255, 255, 0.08);
}

[data-theme="dark"] .btn-neomorphism:active {
    box-shadow: 
        inset 2px 2px 4px rgba(0, 0, 0, 0.5),
        inset -2px -2px 4px rgba(255, 255, 255, 0.03);
}
</style>

<div class="content-card">
    <h4 style="margin-bottom: 30px;">
        <i class="fas fa-user-circle"></i> Mi Perfil
    </h4>
    
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
        <!-- Panel Izquierdo - Información Básica -->
        <div>
            <!-- Avatar y Info Principal -->
            <div class="profile-avatar-card">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($usuario['nombre_completo'], 0, 1)); ?>
                </div>
                
                <h5 style="margin-bottom: 5px; color: var(--text-primary);"><?php echo htmlspecialchars($usuario['nombre_completo']); ?></h5>
                <p style="color: var(--text-secondary); margin-bottom: 5px;">@<?php echo htmlspecialchars($usuario['username']); ?></p>
                <p style="background: var(--primary-color); color: white; padding: 5px 10px; display: inline-block; border-radius: 3px; font-size: 0.9em;">
                    <?php echo htmlspecialchars($usuario['rol_nombre']); ?>
                </p>
            </div>
            
            <!-- Estadísticas -->
            <div class="profile-stats-card">
                <h6 style="margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px; color: var(--text-primary);">
                    <i class="fas fa-chart-line"></i> Estadísticas de Actividad
                </h6>
                
                <div style="margin-bottom: 15px;">
                    <div style="font-size: 0.85em; color: var(--text-secondary); margin-bottom: 5px;">Total de Acciones</div>
                    <div style="font-size: 1.5em; font-weight: bold; color: var(--primary-color);">
                        <?php echo number_format($stats['total_acciones']); ?>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                    <div class="mini-stat-card" style="background: rgba(40, 167, 69, 0.1);">
                        <div style="font-weight: 900; color: #28a745; font-size: 1.8em;">
                            <?php echo $stats['creaciones']; ?>
                        </div>
                        <div style="font-size: 0.8em; font-weight: 700; color: #155724;">Creaciones</div>
                    </div>
                    
                    <div class="mini-stat-card" style="background: rgba(255, 193, 7, 0.1);">
                        <div style="font-weight: 900; color: #856404; font-size: 1.8em;">
                            <?php echo $stats['modificaciones']; ?>
                        </div>
                        <div style="font-size: 0.8em; font-weight: 700; color: #856404;">Ediciones</div>
                    </div>
                    
                    <div class="mini-stat-card" style="background: rgba(220, 53, 69, 0.1);">
                        <div style="font-weight: 900; color: #dc3545; font-size: 1.8em;">
                            <?php echo $stats['eliminaciones']; ?>
                        </div>
                        <div style="font-size: 0.8em; font-weight: 700; color: #721c24;">Eliminaciones</div>
                    </div>
                </div>
                
                <div style="font-size: 0.85em; padding-top: 10px; border-top: 1px solid var(--border-color); color: var(--text-primary);">
                    <div style="margin-bottom: 5px;">
                        <i class="fas fa-clock"></i> 
                        <strong>Último acceso:</strong><br>
                        <span style="color: var(--text-secondary);">
                            <?php echo $usuario['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])) : 'N/A'; ?>
                        </span>
                    </div>
                    <div>
                        <i class="fas fa-calendar-plus"></i> 
                        <strong>Miembro desde:</strong><br>
                        <span style="color: var(--text-secondary);">
                            <?php echo date('d/m/Y', strtotime($usuario['fecha_creacion'])); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Panel Derecho - Datos y Actividad -->
        <div>
            <!-- Formulario de Perfil -->
            <div class="profile-form-neomorphism">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="mb-0" style="color: var(--text-primary);">
                        <i class="fas fa-id-card"></i> Información Personal
                    </h6>
                    <button onclick="$('#modalPassword').modal('show')" class="btn btn-secondary">
                        <i class="fas fa-key"></i> Cambiar Contraseña
                    </button>
                </div>
                
                <form id="formPerfil">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="color: var(--text-primary);">Nombre Completo</label>
                            <input type="text" name="nombre_completo" class="form-control" value="<?php echo htmlspecialchars($usuario['nombre_completo']); ?>" style="background: var(--bg-input); color: var(--text-primary); border: 1px solid var(--border-color);">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="color: var(--text-primary);">Usuario</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($usuario['username']); ?>" disabled style="background: var(--bg-hover); color: var(--text-secondary); border: 1px solid var(--border-color);">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="color: var(--text-primary);">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>" style="background: var(--bg-input); color: var(--text-primary); border: 1px solid var(--border-color);">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="color: var(--text-primary);">Teléfono</label>
                            <input type="text" name="telefono" class="form-control" value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>" style="background: var(--bg-input); color: var(--text-primary); border: 1px solid var(--border-color);">
                        </div>
                    </div>
                    
                    <div class="text-end mt-4">
                        <button type="button" onclick="actualizarPerfil()" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Actividad Reciente -->
            <div class="profile-activity-card">
                <h6 style="margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px; color: var(--text-primary);">
                    <i class="fas fa-history"></i> Mi Actividad Reciente
                </h6>
                
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php foreach ($actividad as $item): 
                        $color = '#6c757d';
                        $icono = 'fa-circle';
                        $texto_accion = $item['accion'];
                        
                        if ($item['accion'] === 'INSERT') {
                            $color = '#28a745';
                            $icono = 'fa-plus-circle';
                            $texto_accion = 'Creó';
                        } elseif ($item['accion'] === 'UPDATE') {
                            $color = '#ffc107';
                            $icono = 'fa-edit';
                            $texto_accion = 'Modificó';
                        } elseif ($item['accion'] === 'DELETE') {
                            $color = '#dc3545';
                            $icono = 'fa-trash-alt';
                            $texto_accion = 'Eliminó';
                        }
                        
                        $tiempo = time() - strtotime($item['fecha_hora']);
                        if ($tiempo < 60) {
                            $tiempo_texto = 'Hace ' . $tiempo . ' seg';
                        } elseif ($tiempo < 3600) {
                            $tiempo_texto = 'Hace ' . floor($tiempo / 60) . ' min';
                        } elseif ($tiempo < 86400) {
                            $tiempo_texto = 'Hace ' . floor($tiempo / 3600) . ' h';
                        } else {
                            $tiempo_texto = date('d/m/Y H:i', strtotime($item['fecha_hora']));
                        }
                    ?>
                    <div class="activity-item" style="border-left: 3px solid <?php echo $color; ?>;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                            <i class="fas <?php echo $icono; ?>" style="color: <?php echo $color; ?>;"></i>
                            <strong style="font-size: 0.9em; color: var(--text-primary);"><?php echo $texto_accion; ?></strong>
                        </div>
                        <div style="font-size: 0.85em; color: var(--text-secondary); margin-left: 24px;">
                            en <strong><?php echo htmlspecialchars($item['tabla']); ?></strong> (ID: <?php echo $item['id_registro']; ?>)
                        </div>
                        <div style="font-size: 0.75em; color: var(--text-muted); margin-left: 24px; margin-top: 3px;">
                            <i class="fas fa-clock"></i> <?php echo $tiempo_texto; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (hasRole(ROL_ADMIN)): ?>
                <div style="margin-top: 15px; text-align: center;">
                    <a href="<?php echo BASE_URL; ?>/views/auditoria/timeline.php?usuario=<?php echo $_SESSION['user_id']; ?>" 
                       style="text-decoration: none; color: var(--primary-color);">
                        <i class="fas fa-stream"></i> Ver mi timeline completo
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cambiar Contraseña -->
<div class="modal fade" id="modalPassword" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color: var(--text-primary);"><i class="fas fa-key"></i> Cambiar Contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formPassword">
                    <div class="password-group">
                        <label class="password-label">
                            <i class="fas fa-lock"></i> Contraseña Actual:
                        </label>
                        <input type="password" id="password_actual" class="password-input-neomorphism" placeholder="Ingresa tu contraseña actual">
                    </div>
                    
                    <div class="password-group">
                        <label class="password-label">
                            <i class="fas fa-key"></i> Nueva Contraseña:
                        </label>
                        <input type="password" id="password_nuevo" class="password-input-neomorphism" placeholder="Ingresa tu nueva contraseña">
                        <small class="password-hint">
                            <i class="fas fa-info-circle"></i> Mínimo 6 caracteres
                        </small>
                    </div>
                    
                    <div class="password-group">
                        <label class="password-label">
                            <i class="fas fa-check-circle"></i> Confirmar Nueva Contraseña:
                        </label>
                        <input type="password" id="password_confirmar" class="password-input-neomorphism" placeholder="Confirma tu nueva contraseña">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-neomorphism" data-bs-dismiss="modal" style="background: var(--bg-hover); color: var(--text-primary);">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" onclick="cambiarPassword()" class="btn btn-primary btn-neomorphism" style="background: var(--primary-color); color: white;">
                    <i class="fas fa-save"></i> Cambiar Contraseña
                </button>
            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../../includes/footer.php';
?>