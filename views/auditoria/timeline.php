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

$page_title = 'Timeline de Actividad';

$idUsuario = $_GET['usuario'] ?? null;

if (!$idUsuario) {
    redirect('views/auditoria/index.php');
}

$database = new Database();
$db = $database->getConnection();

// Obtener información del usuario
$stmt = $db->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->bindParam(':id', $idUsuario);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    redirect('views/auditoria/index.php');
}

// Obtener estadísticas
$sqlStats = "SELECT 
                accion,
                COUNT(*) as total
            FROM auditoria
            WHERE id_usuario = :id_usuario
            GROUP BY accion";

$stmtStats = $db->prepare($sqlStats);
$stmtStats->bindParam(':id_usuario', $idUsuario);
$stmtStats->execute();
$estadisticas = $stmtStats->fetchAll(PDO::FETCH_ASSOC);

$totalInsert = 0;
$totalUpdate = 0;
$totalDelete = 0;

foreach ($estadisticas as $stat) {
    if ($stat['accion'] === 'INSERT') $totalInsert = $stat['total'];
    if ($stat['accion'] === 'UPDATE') $totalUpdate = $stat['total'];
    if ($stat['accion'] === 'DELETE') $totalDelete = $stat['total'];
}

// Obtener actividad por tabla
$sqlTablas = "SELECT 
                tabla,
                COUNT(*) as total
            FROM auditoria
            WHERE id_usuario = :id_usuario
            GROUP BY tabla
            ORDER BY total DESC
            LIMIT 10";

$stmtTablas = $db->prepare($sqlTablas);
$stmtTablas->bindParam(':id_usuario', $idUsuario);
$stmtTablas->execute();
$actividadTablas = $stmtTablas->fetchAll(PDO::FETCH_ASSOC);

// Obtener últimas actividades
$sqlActividades = "SELECT 
                    a.id,
                    a.tabla,
                    a.id_registro,
                    a.accion,
                    a.datos_anteriores,
                    a.datos_nuevos,
                    a.fecha_hora,
                    a.ip_usuario
                FROM auditoria a
                WHERE a.id_usuario = :id_usuario
                ORDER BY a.fecha_hora DESC
                LIMIT 100";

$stmtActividades = $db->prepare($sqlActividades);
$stmtActividades->bindParam(':id_usuario', $idUsuario);
$stmtActividades->execute();
$actividades = $stmtActividades->fetchAll(PDO::FETCH_ASSOC);

$extra_css = <<<'EOD'
<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 50px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #ddd;
}

.timeline-item {
    position: relative;
    padding: 15px 0 15px 80px;
    margin-bottom: 10px;
}

.timeline-icon {
    position: absolute;
    left: 35px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    z-index: 1;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-left: 3px solid #ddd;
}
</style>
EOD;
include __DIR__ . '/../../includes/header.php';
?>
<style>
/* Estilos para tarjetas de estadísticas con neumorfismo */
.stat-card-neomorphism {
    border-radius: 15px;
    padding: 20px;
    text-align: center;
    box-shadow: 
        8px 8px 16px rgba(0, 0, 0, 0.1),
        -8px -8px 16px rgba(255, 255, 255, 0.9);
    border: none !important;
    transition: all 0.3s ease;
}

.stat-card-neomorphism:hover {
    transform: translateY(-5px);
    box-shadow: 
        12px 12px 24px rgba(0, 0, 0, 0.15),
        -12px -12px 24px rgba(255, 255, 255, 1);
}

/* Neumorfismo para modo oscuro en tarjetas de estadísticas */
[data-theme="dark"] .stat-card-neomorphism {
    box-shadow: 
        8px 8px 16px rgba(0, 0, 0, 0.4),
        -8px -8px 16px rgba(255, 255, 255, 0.05);
}

[data-theme="dark"] .stat-card-neomorphism:hover {
    box-shadow: 
        12px 12px 24px rgba(0, 0, 0, 0.5),
        -12px -12px 24px rgba(255, 255, 255, 0.08);
}

/* Estilos para el cuadro de Actividad por Tabla con neumorfismo */
.activity-table-neomorphism {
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 30px;
    background: var(--bg-card);
    box-shadow: 
        8px 8px 16px rgba(0, 0, 0, 0.1),
        -8px -8px 16px rgba(255, 255, 255, 0.9);
    border: none !important;
    transition: all 0.3s ease;
}

[data-theme="dark"] .activity-table-neomorphism {
    box-shadow: 
        8px 8px 16px rgba(0, 0, 0, 0.4),
        -8px -8px 16px rgba(255, 255, 255, 0.05);
}

/* Estilos para las barras de actividad */
.activity-bar-item {
    margin-bottom: 15px;
    transition: transform 0.2s ease;
}

.activity-bar-item:hover {
    transform: translateX(5px);
}

.activity-bar-label {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 0.95em;
}

.activity-bar-label strong {
    color: var(--text-primary);
    font-weight: 600;
}

.activity-bar-label span {
    color: var(--text-secondary);
    font-size: 0.9em;
}

.activity-bar-container {
    background: var(--bg-hover);
    height: 24px;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
    box-shadow: inset 2px 2px 5px rgba(0, 0, 0, 0.1);
}

[data-theme="dark"] .activity-bar-container {
    box-shadow: inset 2px 2px 5px rgba(0, 0, 0, 0.3);
}

.activity-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    border-radius: 12px;
    transition: width 0.6s ease;
    position: relative;
    overflow: hidden;
}

.activity-bar-fill::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.3),
        transparent
    );
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}

.activity-bar-fill:hover {
    filter: brightness(1.1);
}

.timeline {
    position: relative;
    padding-left: 50px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.timeline-icon {
    position: absolute;
    left: -38px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.2em;
    box-shadow: 0 0 0 4px var(--bg-body), 0 0 0 5px currentColor;
    z-index: 1;
}

.timeline-content {
    background: var(--bg-card);
    border-radius: 12px;
    padding: 20px;
    margin-left: 20px;
    box-shadow: 
        8px 8px 16px rgba(0, 0, 0, 0.15),
        -8px -8px 16px rgba(255, 255, 255, 0.7);
    border-left: 4px solid var(--primary-color);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    overflow: hidden;
}

.timeline-content::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(to right, transparent, currentColor, transparent);
    opacity: 0.3;
}

.timeline-content:hover {
    transform: translateX(5px);
    box-shadow: 
        12px 12px 24px rgba(0, 0, 0, 0.2),
        -12px -12px 24px rgba(255, 255, 255, 0.8);
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border-color);
}

.timeline-action {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.timeline-action strong {
    font-size: 1.1em;
    color: var(--text-primary);
}

.timeline-table {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.95em;
}

.timeline-id {
    display: inline-block;
    background: var(--bg-hover);
    padding: 2px 10px;
    border-radius: 4px;
    color: var(--text-secondary);
    font-size: 0.85em;
    font-family: monospace;
}

.timeline-time {
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--text-secondary);
    font-size: 0.9em;
    white-space: nowrap;
}

.timeline-changes {
    background: var(--bg-hover);
    border-radius: 8px;
    padding: 12px;
    margin-top: 12px;
}

.change-item {
    margin-top: 8px;
    padding: 10px;
    background: var(--bg-card);
    border-radius: 6px;
    border-left: 3px solid #ffc107;
    font-size: 0.9em;
    transition: background 0.2s ease;
}

.change-item:hover {
    background: var(--bg-hover);
}

.change-field {
    font-weight: 600;
    color: var(--text-primary);
    margin-right: 8px;
}

.value-old {
    color: #dc3545;
    text-decoration: line-through;
    background: rgba(220, 53, 69, 0.1);
    padding: 2px 6px;
    border-radius: 3px;
}

.value-new {
    color: #28a745;
    background: rgba(40, 167, 69, 0.1);
    padding: 2px 6px;
    border-radius: 3px;
    font-weight: 500;
}

.timeline-footer {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.85em;
    color: var(--text-secondary);
}

.ip-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--bg-hover);
    padding: 4px 12px;
    border-radius: 20px;
    font-family: monospace;
    color: var(--text-secondary);
}

.date-separator {
    padding-left: 80px;
    margin: 30px 0 20px 0;
    font-weight: bold;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 10px;
}

.date-separator::after {
    content: '';
    flex: 1;
    height: 2px;
    background: linear-gradient(to right, var(--primary-color), transparent);
}

.summary-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.9em;
}

.badge-success {
    background: rgba(16, 185, 129, 0.15);
    color: var(--success-color);
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.badge-warning {
    background: rgba(245, 158, 11, 0.15);
    color: var(--warning-color);
    border: 1px solid rgba(245, 158, 11, 0.3);
}

.badge-danger {
    background: rgba(239, 68, 68, 0.15);
    color: var(--danger-color);
    border: 1px solid rgba(239, 68, 68, 0.3);
}

/* Estilos específicos para modo oscuro */
[data-theme="dark"] .timeline-icon {
    box-shadow: 0 0 0 4px #0f172a, 0 0 0 5px currentColor;
}

[data-theme="dark"] .timeline-content {
    box-shadow: 
        8px 8px 16px rgba(0, 0, 0, 0.5),
        -8px -8px 16px rgba(255, 255, 255, 0.03);
}

[data-theme="dark"] .timeline-content:hover {
    box-shadow: 
        12px 12px 24px rgba(0, 0, 0, 0.6),
        -12px -12px 24px rgba(255, 255, 255, 0.05);
}

[data-theme="dark"] .value-old {
    background: rgba(239, 68, 68, 0.2);
}

[data-theme="dark"] .value-new {
    background: rgba(16, 185, 129, 0.2);
}
</style>

<div class="content-card">
    <div style="margin-bottom: 20px;">
        <a href="<?php echo BASE_URL; ?>/views/auditoria/index.php" style="text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Volver a Auditoría
        </a>
    </div>
    
    <h4 style="margin-bottom: 30px;">
        <i class="fas fa-user-clock"></i> Timeline de Actividad: 
        <strong><?php echo htmlspecialchars($usuario['nombre_completo']); ?></strong>
    </h4>
    
    <!-- Resumen de Estadísticas -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px;">
        <div class="stat-card-neomorphism" style="background: #d4edda;">
            <div style="font-size: 2em; font-weight: bold; color: #28a745;">
                <?php echo $totalInsert; ?>
            </div>
            <div style="font-size: 0.9em; color: #155724;">
                <i class="fas fa-plus-circle"></i> CREACIONES
            </div>
        </div>
        
        <div class="stat-card-neomorphism" style="background: #fff3cd;">
            <div style="font-size: 2em; font-weight: bold; color: #856404;">
                <?php echo $totalUpdate; ?>
            </div>
            <div style="font-size: 0.9em; color: #856404;">
                <i class="fas fa-edit"></i> MODIFICACIONES
            </div>
        </div>
        
        <div class="stat-card-neomorphism" style="background: #f8d7da;">
            <div style="font-size: 2em; font-weight: bold; color: #dc3545;">
                <?php echo $totalDelete; ?>
            </div>
            <div style="font-size: 0.9em; color: #721c24;">
                <i class="fas fa-trash-alt"></i> ELIMINACIONES
            </div>
        </div>
        
        <div class="stat-card-neomorphism" style="background: #cce5ff;">
            <div style="font-size: 2em; font-weight: bold; color: #007bff;">
                <?php echo $totalInsert + $totalUpdate + $totalDelete; ?>
            </div>
            <div style="font-size: 0.9em; color: #004085;">
                <i class="fas fa-chart-bar"></i> TOTAL ACCIONES
            </div>
        </div>
    </div>
    
    <!-- Actividad por Tabla -->
    <div class="activity-table-neomorphism">
        <h5><i class="fas fa-table"></i> Actividad por Tabla</h5>
        
        <div style="margin-top: 15px;">
            <?php 
            $totalAcciones = $totalInsert + $totalUpdate + $totalDelete;
            if ($totalAcciones > 0): 
                foreach ($actividadTablas as $tabla): 
                    $porcentaje = ($tabla['total'] / $totalAcciones) * 100;
            ?>
                <div class="activity-bar-item">
                    <div class="activity-bar-label">
                        <strong><?php echo htmlspecialchars($tabla['tabla']); ?></strong>
                        <span><?php echo $tabla['total']; ?> acciones (<?php echo number_format($porcentaje, 1); ?>%)</span>
                    </div>
                    <div class="activity-bar-container">
                        <div class="activity-bar-fill" style="width: <?php echo $porcentaje; ?>%;"></div>
                    </div>
                </div>
            <?php 
                endforeach;
            else:
            ?>
                <p style="text-align: center; color: var(--text-muted); padding: 20px;">
                    <i class="fas fa-info-circle"></i> No hay actividad registrada
                </p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Timeline de Actividades -->
    <div style="border: 1px solid var(--border-color); padding: 25px; border-radius: 8px; background: var(--bg-card);">
        <h5 style="margin-bottom: 25px; display: flex; align-items: center; gap: 10px; color: var(--text-primary);">
            <i class="fas fa-stream" style="color: var(--primary-color);"></i> 
            Historial de Actividades 
            <span style="color: var(--text-secondary); font-size: 0.85em; font-weight: normal;">(Últimas 100)</span>
        </h5>
        
        <div class="timeline">
            <?php 
            $fechaActual = '';
            foreach ($actividades as $actividad): 
                $fecha = date('Y-m-d', strtotime($actividad['fecha_hora']));
                
                // Separador de fecha
                if ($fecha !== $fechaActual) {
                    $fechaActual = $fecha;
                    echo '<div class="date-separator">';
                    echo '<i class="fas fa-calendar-day"></i> ' . date('d/m/Y', strtotime($fecha));
                    echo '</div>';
                }
                
                // Color según acción
                $color = '#6c757d';
                $icono = '?';
                
                if ($actividad['accion'] === 'INSERT') {
                    $color = '#28a745';
                    $icono = '+';
                } elseif ($actividad['accion'] === 'UPDATE') {
                    $color = '#ffc107';
                    $icono = '✎';
                } elseif ($actividad['accion'] === 'DELETE') {
                    $color = '#dc3545';
                    $icono = '✕';
                }
            ?>
            
            <div class="timeline-item">
                <div class="timeline-icon" style="background: <?php echo $color; ?>; color: <?php echo $color; ?>;">
                    <?php echo $icono; ?>
                </div>
                
                <div class="timeline-content" style="border-left-color: <?php echo $color; ?>; color: <?php echo $color; ?>;">
                    <div class="timeline-header">
                        <div class="timeline-action">
                            <strong><?php echo $actividad['accion']; ?> en</strong>
                            <span class="timeline-table" style="background: <?php echo $color; ?>20; color: <?php echo $color; ?>;">
                                <?php echo htmlspecialchars($actividad['tabla']); ?>
                            </span>
                            <span class="timeline-id">
                                ID: <?php echo $actividad['id_registro']; ?>
                            </span>
                        </div>
                        <div class="timeline-time">
                            <i class="fas fa-clock"></i>
                            <?php echo date('H:i:s', strtotime($actividad['fecha_hora'])); ?>
                        </div>
                    </div>
                    
                    <div class="timeline-changes">
                        <?php
                        // Mostrar resumen de cambios
                        if ($actividad['accion'] === 'INSERT' && $actividad['datos_nuevos']) {
                            $nuevos = json_decode($actividad['datos_nuevos'], true);
                            echo '<div class="summary-badge badge-success">';
                            echo '<i class="fas fa-plus-circle"></i> Datos creados: ' . count($nuevos) . ' campos registrados';
                            echo '</div>';
                            
                        } elseif ($actividad['accion'] === 'UPDATE' && $actividad['datos_anteriores'] && $actividad['datos_nuevos']) {
                            $anteriores = json_decode($actividad['datos_anteriores'], true);
                            $nuevos = json_decode($actividad['datos_nuevos'], true);
                            $cambios = 0;
                            
                            foreach ($nuevos as $campo => $valor) {
                                if (isset($anteriores[$campo]) && $anteriores[$campo] !== $valor) {
                                    $cambios++;
                                }
                            }
                            
                            echo '<div class="summary-badge badge-warning">';
                            echo '<i class="fas fa-edit"></i> Campos modificados: ' . $cambios;
                            echo '</div>';
                            
                            // Mostrar algunos cambios
                            $contador = 0;
                            foreach ($nuevos as $campo => $valor) {
                                if (isset($anteriores[$campo]) && $anteriores[$campo] !== $valor && $contador < 3) {
                                    echo '<div class="change-item">';
                                    echo '<span class="change-field">' . htmlspecialchars($campo) . ':</span> ';
                                    echo '<span class="value-old">' . htmlspecialchars($anteriores[$campo] ?? '') . '</span>';
                                    echo ' → ';
                                    echo '<span class="value-new">' . htmlspecialchars($valor ?? '') . '</span>';
                                    echo '</div>';
                                    $contador++;
                                }
                            }
                            
                            if ($cambios > 3) {
                                echo '<div style="margin-top: 10px; text-align: center; color: var(--text-secondary); font-size: 0.85em;">';
                                echo '<i class="fas fa-ellipsis-h"></i> y ' . ($cambios - 3) . ' cambios más';
                                echo '</div>';
                            }
                            
                        } elseif ($actividad['accion'] === 'DELETE' && $actividad['datos_anteriores']) {
                            $anteriores = json_decode($actividad['datos_anteriores'], true);
                            echo '<div class="summary-badge badge-danger">';
                            echo '<i class="fas fa-trash-alt"></i> Datos eliminados: ' . count($anteriores) . ' campos';
                            echo '</div>';
                        }
                        ?>
                    </div>
                    
                    <div class="timeline-footer">
                        <span class="ip-badge">
                            <i class="fas fa-network-wired"></i>
                            IP: <?php echo htmlspecialchars($actividad['ip_usuario'] ?? 'N/A'); ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../../includes/footer.php';
?>