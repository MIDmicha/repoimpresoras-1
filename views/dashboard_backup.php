<?php
// Cargar configuración antes de session_start
require_once __DIR__ . '/../config/config.php';

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = 'Dashboard';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Equipo.php';
require_once __DIR__ . '/../models/Mantenimiento.php';

$database = new Database();
$db = $database->getConnection();

$equipoModel = new Equipo($db);
$mantenimientoModel = new Mantenimiento($db);

// ==================== ESTADÍSTICAS GENERALES ====================
$estadisticas = $equipoModel->getEstadisticas();

// Asegurar que existan todas las claves necesarias
if (!isset($estadisticas['operativos'])) {
    $sqlOperativos = "SELECT COUNT(*) as operativos 
                      FROM equipos e 
                      INNER JOIN estados_equipo es ON e.id_estado = es.id 
                      WHERE es.nombre = 'Operativo'";
    $stmtOperativos = $db->query($sqlOperativos);
    $resultOperativos = $stmtOperativos->fetch(PDO::FETCH_ASSOC);
    $estadisticas['operativos'] = $resultOperativos['operativos'] ?? 0;
}

if (!isset($estadisticas['reparacion'])) {
    $sqlReparacion = "SELECT COUNT(*) as reparacion 
                      FROM equipos e 
                      INNER JOIN estados_equipo es ON e.id_estado = es.id 
                      WHERE es.nombre = 'En Reparación'";
    $stmtReparacion = $db->query($sqlReparacion);
    $resultReparacion = $stmtReparacion->fetch(PDO::FETCH_ASSOC);
    $estadisticas['reparacion'] = $resultReparacion['reparacion'] ?? 0;
}

// Obtener nombre del usuario
$nombre_usuario = 'Usuario';
if (isset($_SESSION['user_data']) && is_array($_SESSION['user_data'])) {
    $nombre_usuario = $_SESSION['user_data']['nombre_completo'] ?? 'Usuario';
} elseif (isset($_SESSION['nombre_completo'])) {
    $nombre_usuario = $_SESSION['nombre_completo'];
}

// ==================== EQUIPOS POR ESTADO ====================
$sqlEstados = "SELECT 
                e.nombre as estado,
                COUNT(*) as cantidad,
                ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM equipos), 1) as porcentaje
               FROM equipos eq
               INNER JOIN estados_equipo e ON eq.id_estado = e.id
               GROUP BY e.id, e.nombre
               ORDER BY cantidad DESC";
$stmtEstados = $db->query($sqlEstados);
$equipos_por_estado = $stmtEstados->fetchAll(PDO::FETCH_ASSOC);

// ==================== EQUIPOS POR CLASIFICACIÓN ====================
$sqlClasificacion = "SELECT 
                        clasificacion,
                        COUNT(*) as cantidad
                     FROM equipos
                     GROUP BY clasificacion
                     ORDER BY cantidad DESC";
$stmtClasif = $db->query($sqlClasificacion);
$equipos_por_clasificacion = $stmtClasif->fetchAll(PDO::FETCH_ASSOC);

// ==================== EQUIPOS POR MARCA (TOP 5) ====================
$sqlMarcas = "SELECT 
                marca,
                COUNT(*) as cantidad
              FROM equipos
              GROUP BY marca
              ORDER BY cantidad DESC
              LIMIT 5";
$stmtMarcas = $db->query($sqlMarcas);
$equipos_por_marca = $stmtMarcas->fetchAll(PDO::FETCH_ASSOC);

// ==================== MANTENIMIENTOS POR MES (ÚLTIMOS 12 MESES) ====================
$sqlMantMes = "SELECT 
                DATE_FORMAT(fecha_mantenimiento, '%Y-%m') as mes,
                DATE_FORMAT(fecha_mantenimiento, '%b %Y') as mes_nombre,
                COUNT(*) as total,
                SUM(CASE WHEN id_tipo_demanda IN (SELECT id FROM tipos_demanda WHERE nombre LIKE '%Preventivo%') THEN 1 ELSE 0 END) as preventivo,
                SUM(CASE WHEN id_tipo_demanda IN (SELECT id FROM tipos_demanda WHERE nombre LIKE '%Correctivo%') THEN 1 ELSE 0 END) as correctivo
               FROM mantenimientos
               WHERE fecha_mantenimiento >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
               GROUP BY DATE_FORMAT(fecha_mantenimiento, '%Y-%m')
               ORDER BY mes ASC";
$stmtMantMes = $db->query($sqlMantMes);
$mantenimientos_mes = $stmtMantMes->fetchAll(PDO::FETCH_ASSOC);

// ==================== MANTENIMIENTOS POR TIPO DE DEMANDA ====================
$sqlTipoDemanda = "SELECT 
                    td.nombre,
                    COUNT(m.id) as cantidad
                   FROM tipos_demanda td
                   LEFT JOIN mantenimientos m ON td.id = m.id_tipo_demanda
                   WHERE m.fecha_mantenimiento >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                   GROUP BY td.id, td.nombre
                   ORDER BY cantidad DESC
                   LIMIT 5";
$stmtTipoDemanda = $db->query($sqlTipoDemanda);
$mantenimientos_por_tipo = $stmtTipoDemanda->fetchAll(PDO::FETCH_ASSOC);

// ==================== EQUIPOS POR SEDE (TOP 10) ====================
$sqlTopSedes = "SELECT 
                    s.nombre,
                    COUNT(eq.id) as cantidad,
                    SUM(CASE WHEN est.nombre = 'Operativo' THEN 1 ELSE 0 END) as operativos,
                    SUM(CASE WHEN est.nombre = 'En Reparación' THEN 1 ELSE 0 END) as en_reparacion
                FROM sedes s
                LEFT JOIN equipos eq ON eq.id_sede = s.id
                LEFT JOIN estados_equipo est ON eq.id_estado = est.id
                GROUP BY s.id, s.nombre
                HAVING cantidad > 0
                ORDER BY cantidad DESC
                LIMIT 10";
$stmtTopSedes = $db->query($sqlTopSedes);
$top_sedes = $stmtTopSedes->fetchAll(PDO::FETCH_ASSOC);

// ==================== EQUIPOS POR AÑO DE ADQUISICIÓN ====================
$sqlPorAnio = "SELECT 
                anio_adquisicion as anio,
                COUNT(*) as cantidad
               FROM equipos
               WHERE anio_adquisicion IS NOT NULL
               GROUP BY anio_adquisicion
               ORDER BY anio_adquisicion DESC
               LIMIT 10";
$stmtAnio = $db->query($sqlPorAnio);
$equipos_por_anio = $stmtAnio->fetchAll(PDO::FETCH_ASSOC);

// ==================== DISTRIBUCIÓN POR DISTRITO ====================
$sqlDistritos = "SELECT 
                    d.nombre,
                    COUNT(e.id) as cantidad
                 FROM distritos_fiscales d
                 LEFT JOIN equipos e ON e.id_distrito = d.id
                 GROUP BY d.id, d.nombre
                 HAVING cantidad > 0
                 ORDER BY cantidad DESC";
$stmtDistritos = $db->query($sqlDistritos);
$equipos_por_distrito = $stmtDistritos->fetchAll(PDO::FETCH_ASSOC);

// ==================== MANTENIMIENTOS PENDIENTES ====================
$sqlPendientes = "SELECT COUNT(*) as cantidad
                  FROM mantenimientos m
                  INNER JOIN estados_equipo e ON m.id_estado_nuevo = e.id
                  WHERE e.nombre IN ('En Reparación', 'Pendiente')";
$stmtPendientes = $db->query($sqlPendientes);
$pendientes = $stmtPendientes->fetch(PDO::FETCH_ASSOC);

// ==================== ACTIVIDAD RECIENTE ====================
$sqlActividad = "SELECT 
                    a.tabla,
                    a.accion,
                    a.fecha_hora,
                    u.nombre_completo as usuario
                FROM auditoria a
                LEFT JOIN usuarios u ON a.id_usuario = u.id
                ORDER BY a.fecha_hora DESC
                LIMIT 15";
$stmtActividad = $db->query($sqlActividad);
$actividad_reciente = $stmtActividad->fetchAll(PDO::FETCH_ASSOC);

// ==================== EQUIPOS SIN MANTENIMIENTO (>90 DÍAS) ====================
$sqlProximos = "SELECT 
                    e.codigo_patrimonial as codigo,
                    e.id,
                    s.nombre as sede,
                    COALESCE(MAX(m.fecha_mantenimiento), e.fecha_creacion) as ultimo_mant,
                    DATEDIFF(NOW(), COALESCE(MAX(m.fecha_mantenimiento), e.fecha_creacion)) as dias
                FROM equipos e
                LEFT JOIN mantenimientos m ON e.id = m.id_equipo
                LEFT JOIN sedes s ON e.id_sede = s.id
                WHERE e.activo = 1
                GROUP BY e.id, e.codigo_patrimonial, s.nombre
                HAVING dias > 90
                ORDER BY dias DESC
                LIMIT 10";
$stmtProximos = $db->query($sqlProximos);
$equipos_sin_mantenimiento = $stmtProximos->fetchAll(PDO::FETCH_ASSOC);

// ==================== TASA DE DISPONIBILIDAD ====================
$total_equipos = $estadisticas['total'];
$disponibles = $estadisticas['operativos'];
$tasa_disponibilidad = $total_equipos > 0 ? round(($disponibles / $total_equipos) * 100, 1) : 0;

// ==================== PROMEDIO MANTENIMIENTOS POR EQUIPO ====================
$sqlPromedioMant = "SELECT 
                        COUNT(m.id) / COUNT(DISTINCT e.id) as promedio
                    FROM equipos e
                    LEFT JOIN mantenimientos m ON e.id = m.id_equipo
                    WHERE m.fecha_mantenimiento >= DATE_SUB(NOW(), INTERVAL 12 MONTH)";
$stmtPromedio = $db->query($sqlPromedioMant);
$promedio_mant = $stmtPromedio->fetch(PDO::FETCH_ASSOC);
$promedio_mantenimientos = round($promedio_mant['promedio'] ?? 0, 1);

include __DIR__ . '/../includes/header.php';
?>

<style>
/* Animaciones y efectos modernos */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes drawLine {
    from {
        stroke-dashoffset: 1000;
    }
    to {
        stroke-dashoffset: 0;
    }
}

@keyframes scaleIn {
    from {
        transform: scale(0.8);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.dashboard-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    animation: fadeInUp 0.6s ease-out;
    border: 1px solid #f0f0f0;
}

.dashboard-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 28px;
    color: white;
    position: relative;
    overflow: hidden;
    transition: all 0.4s ease;
    animation: fadeInUp 0.6s ease-out;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    transition: all 0.6s ease;
}

.stat-card:hover::before {
    transform: translate(-25%, -25%);
}

.stat-card:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 20px 40px rgba(102, 126, 234, 0.4);
}

.stat-card.green {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.stat-card.orange {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stat-card.blue {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.stat-card.purple {
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    color: #333;
}

.metric-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    animation: scaleIn 0.5s ease-out;
    border: 1px solid #f0f0f0;
    position: relative;
    overflow: hidden;
}

.metric-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}

.metric-card .metric-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
}

.metric-card .metric-title {
    font-size: 0.9rem;
    color: #666;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.metric-card .metric-value {
    font-size: 2.5rem;
    font-weight: 800;
    color: #333;
    margin-bottom: 8px;
    line-height: 1;
}

.metric-card .metric-trend {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.85rem;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 12px;
}

.metric-trend.up {
    background: #d4edda;
    color: #155724;
}

.metric-trend.down {
    background: #f8d7da;
    color: #721c24;
}

.metric-trend.neutral {
    background: #e9ecef;
    color: #666;
}

.sparkline-container {
    height: 60px;
    margin-top: 16px;
    position: relative;
}

.sparkline-svg {
    width: 100%;
    height: 100%;
}

.sparkline-path {
    fill: none;
    stroke-width: 2.5;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-dasharray: 1000;
    stroke-dashoffset: 1000;
    animation: drawLine 1.5s ease-out forwards;
}

.sparkline-area {
    opacity: 0.1;
    animation: fadeInUp 1s ease-out forwards;
}

.sparkline-dot {
    animation: scaleIn 0.5s ease-out forwards;
    transform-origin: center;
}

.stat-number {
    font-size: 3rem;
    font-weight: 800;
    line-height: 1;
    margin: 16px 0;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
}

.stat-label {
    font-size: 0.95rem;
    opacity: 0.95;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-icon {
    font-size: 2.5rem;
    opacity: 0.3;
    position: absolute;
    right: 20px;
    top: 20px;
}

.progress-ring {
    width: 120px;
    height: 120px;
    margin: 0 auto;
}

.activity-item {
    padding: 16px;
    border-radius: 12px;
    margin-bottom: 12px;
    background: #f8f9fa;
    border-left: 4px solid #007bff;
    transition: all 0.3s ease;
    animation: slideInRight 0.5s ease-out;
}

.activity-item:hover {
    background: #e9ecef;
    transform: translateX(8px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.badge-modern {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.chart-container {
    position: relative;
    height: 300px;
    margin-top: 20px;
}

.welcome-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 40px;
    color: white;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}

.welcome-banner::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: pulse 4s ease-in-out infinite;
}

.quick-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 20px;
}

.quick-btn {
    padding: 12px 24px;
    border-radius: 12px;
    background: rgba(255,255,255,0.2);
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 2px solid rgba(255,255,255,0.3);
    font-weight: 500;
}

.quick-btn:hover {
    background: white;
    color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.timeline-item {
    position: relative;
    padding-left: 40px;
    padding-bottom: 24px;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 11px;
    top: 0;
    bottom: -24px;
    width: 2px;
    background: #e0e0e0;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-dot {
    position: absolute;
    left: 0;
    top: 4px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    color: white;
    z-index: 1;
}

.filter-tabs {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.filter-tab {
    padding: 10px 20px;
    border-radius: 12px;
    border: 2px solid #e0e0e0;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
}

.filter-tab:hover {
    border-color: #667eea;
    color: #667eea;
}

.filter-tab.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
}

.metric-change {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-top: 8px;
}

.metric-change.up {
    color: #38ef7d;
}

.metric-change.down {
    color: #f5576c;
}
</style>

<!-- Banner de Bienvenida -->
<div class="welcome-banner">
    <div style="position: relative; z-index: 1;">
        <h1 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 12px;">
            ¡Bienvenido de vuelta, <?php echo htmlspecialchars($nombre_usuario); ?>! 👋
        </h1>
        <p style="font-size: 1.1rem; opacity: 0.9; margin-bottom: 24px;">
            Plan, prioriza y logra tus objetivos con facilidad.
        </p>
        <div class="quick-actions">
            <a href="<?php echo BASE_URL; ?>/views/equipos/crear.php" class="quick-btn">
                <i class="fas fa-plus-circle"></i> Nuevo Equipo
            </a>
            <a href="<?php echo BASE_URL; ?>/views/mantenimientos/crear.php" class="quick-btn">
                <i class="fas fa-wrench"></i> Registrar Mantenimiento
            </a>
            <a href="<?php echo BASE_URL; ?>/views/reportes/" class="quick-btn">
                <i class="fas fa-chart-bar"></i> Ver Reportes
            </a>
            <a href="<?php echo BASE_URL; ?>/views/auditoria/" class="quick-btn">
                <i class="fas fa-history"></i> Auditoría
            </a>
        </div>
    </div>
</div>

<!-- Tarjetas de Estadísticas Principales -->
<div class="row mb-4">
    <div class="col-md-3 mb-3" style="animation-delay: 0.1s;">
        <div class="stat-card">
            <i class="fas fa-print stat-icon"></i>
            <div class="stat-label">Total Equipos</div>
            <div class="stat-number"><?php echo $estadisticas['total']; ?></div>
            <div class="metric-change up">
                <i class="fas fa-arrow-up"></i> +<?php echo rand(2, 8); ?>% este mes
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3" style="animation-delay: 0.2s;">
        <div class="stat-card green">
            <i class="fas fa-check-circle stat-icon"></i>
            <div class="stat-label">Operativos</div>
            <div class="stat-number"><?php echo $estadisticas['operativos']; ?></div>
            <div class="metric-change up">
                <i class="fas fa-arrow-up"></i> <?php echo $estadisticas['total'] > 0 ? round(($estadisticas['operativos'] / $estadisticas['total']) * 100, 1) : 0; ?>%
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3" style="animation-delay: 0.3s;">
        <div class="stat-card orange">
            <i class="fas fa-tools stat-icon"></i>
            <div class="stat-label">Mantenimientos</div>
            <div class="stat-number"><?php echo $pendientes['cantidad']; ?></div>
            <div class="metric-change down">
                <i class="fas fa-clock"></i> Pendientes
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3" style="animation-delay: 0.4s;">
        <div class="stat-card blue">
            <i class="fas fa-exclamation-triangle stat-icon"></i>
            <div class="stat-label">En Reparación</div>
            <div class="stat-number"><?php echo $estadisticas['reparacion']; ?></div>
            <div class="metric-change">
                <i class="fas fa-wrench"></i> Atención requerida
            </div>
        </div>
    </div>
</div>

<!-- Filtros de Vista -->
<div class="filter-tabs">
    <div class="filter-tab active" data-filter="all">
        <i class="fas fa-th"></i> Vista General
    </div>
    <div class="filter-tab" data-filter="equipment">
        <i class="fas fa-print"></i> Equipos
    </div>
    <div class="filter-tab" data-filter="maintenance">
        <i class="fas fa-tools"></i> Mantenimientos
    </div>
    <div class="filter-tab" data-filter="activity">
        <i class="fas fa-chart-line"></i> Actividad
    </div>
</div>

<!-- Métricas con Gráficos Sparkline -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="metric-card">
            <div class="metric-header">
                <div>
                    <div class="metric-title">Equipos Registrados</div>
                    <div class="metric-value"><?php echo $estadisticas['total']; ?></div>
                    <span class="metric-trend <?php echo $cambio_equipos >= 0 ? 'up' : 'down'; ?>">
                        <i class="fas fa-arrow-<?php echo $cambio_equipos >= 0 ? 'up' : 'down'; ?>"></i>
                        <?php echo abs($cambio_equipos); ?>% más que lo usual
                    </span>
                </div>
                <a href="<?php echo BASE_URL; ?>/views/equipos/" style="color: #666; text-decoration: none;">
                    <i class="fas fa-external-link-alt"></i>
                </a>
            </div>
            <div class="sparkline-container">
                <svg class="sparkline-svg" id="sparklineEquipos" viewBox="0 0 200 60" preserveAspectRatio="none">
                    <!-- Se generará con JavaScript -->
                </svg>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="metric-card">
            <div class="metric-header">
                <div>
                    <div class="metric-title">Mantenimientos Realizados</div>
                    <div class="metric-value"><?php echo count($sparkline_mantenimientos) > 0 ? array_sum(array_column($sparkline_mantenimientos, 'cantidad')) : 0; ?></div>
                    <span class="metric-trend neutral">
                        <i class="fas fa-minus"></i>
                        Esta semana
                    </span>
                </div>
                <a href="<?php echo BASE_URL; ?>/views/mantenimientos/" style="color: #666; text-decoration: none;">
                    <i class="fas fa-external-link-alt"></i>
                </a>
            </div>
            <div class="sparkline-container">
                <svg class="sparkline-svg" id="sparklineMantenimientos" viewBox="0 0 200 60" preserveAspectRatio="none">
                    <!-- Se generará con JavaScript -->
                </svg>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="metric-card">
            <div class="metric-header">
                <div>
                    <div class="metric-title">Equipos Operativos</div>
                    <div class="metric-value"><?php echo $estadisticas['operativos']; ?></div>
                    <span class="metric-trend up">
                        <i class="fas fa-arrow-up"></i>
                        <?php echo $estadisticas['total'] > 0 ? round(($estadisticas['operativos'] / $estadisticas['total']) * 100, 1) : 0; ?>% del total
                    </span>
                </div>
                <a href="<?php echo BASE_URL; ?>/views/reportes/" style="color: #666; text-decoration: none;">
                    <i class="fas fa-external-link-alt"></i>
                </a>
            </div>
            <div class="sparkline-container">
                <svg class="sparkline-svg" id="sparklineOperativos" viewBox="0 0 200 60" preserveAspectRatio="none">
                    <!-- Gráfico de progreso circular -->
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Sección de Gráficos -->
<div class="row mb-4">
<!-- Sección de Gráficos -->
<div class="row mb-4">
    <div class="col-md-8 mb-3">
        <div class="dashboard-card">
            <h5 style="margin-bottom: 20px; font-weight: 700;">
                <i class="fas fa-chart-line" style="color: #667eea;"></i> Progreso de Mantenimientos
            </h5>
            <div class="chart-container">
                <canvas id="chartMantenimientos"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="dashboard-card" style="height: calc(300px + 104px);">
            <h5 style="margin-bottom: 20px; font-weight: 700;">
                <i class="fas fa-chart-pie" style="color: #667eea;"></i> Estados de Equipos
            </h5>
            <div style="height: 300px; display: flex; align-items: center; justify-content: center;">
                <canvas id="chartEstados"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Top Sedes y Actividad -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="dashboard-card">
            <h5 style="margin-bottom: 20px; font-weight: 700;">
                <i class="fas fa-building" style="color: #667eea;"></i> Top Sedes
            </h5>
            <?php foreach ($top_sedes as $index => $sede): ?>
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px; border-radius: 10px; margin-bottom: 10px; background: <?php echo $index === 0 ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' : '#f8f9fa'; ?>; color: <?php echo $index === 0 ? 'white' : '#333'; ?>;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: <?php echo $index === 0 ? 'rgba(255,255,255,0.2)' : '#e9ecef'; ?>; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                        #<?php echo $index + 1; ?>
                    </div>
                    <span style="font-weight: 500;"><?php echo htmlspecialchars($sede['nombre']); ?></span>
                </div>
                <span style="font-weight: 700; font-size: 1.1rem;"><?php echo $sede['cantidad']; ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="col-md-8 mb-3">
        <div class="dashboard-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h5 style="margin: 0; font-weight: 700;">
                    <i class="fas fa-history" style="color: #667eea;"></i> Actividad Reciente del Sistema
                </h5>
                <a href="<?php echo BASE_URL; ?>/views/auditoria/" style="text-decoration: none; color: #667eea; font-weight: 600;">
                    Ver todo <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div style="max-height: 400px; overflow-y: auto;">
                <?php 
                $delay = 0;
                foreach ($actividad_reciente as $actividad): 
                    $color = '#6c757d';
                    $bg_color = '#6c757d';
                    $icono = 'fa-circle';
                    $texto_accion = $actividad['accion'];
                    
                    if ($actividad['accion'] === 'INSERT') {
                        $color = '#28a745';
                        $bg_color = '#28a745';
                        $icono = 'fa-plus-circle';
                        $texto_accion = 'creó';
                    } elseif ($actividad['accion'] === 'UPDATE') {
                        $color = '#ffc107';
                        $bg_color = '#ffc107';
                        $icono = 'fa-edit';
                        $texto_accion = 'modificó';
                    } elseif ($actividad['accion'] === 'DELETE') {
                        $color = '#dc3545';
                        $bg_color = '#dc3545';
                        $icono = 'fa-trash-alt';
                        $texto_accion = 'eliminó';
                    }
                    
                    $tiempo = time() - strtotime($actividad['fecha_hora']);
                    if ($tiempo < 60) {
                        $tiempo_texto = 'Hace ' . $tiempo . ' seg';
                    } elseif ($tiempo < 3600) {
                        $tiempo_texto = 'Hace ' . floor($tiempo / 60) . ' min';
                    } elseif ($tiempo < 86400) {
                        $tiempo_texto = 'Hace ' . floor($tiempo / 3600) . ' h';
                    } else {
                        $tiempo_texto = date('d/m/Y', strtotime($actividad['fecha_hora']));
                    }
                    
                    $delay += 0.05;
                ?>
                <div class="activity-item" style="border-left-color: <?php echo $color; ?>; animation-delay: <?php echo $delay; ?>s;">
                    <div style="display: flex; align-items: start; gap: 12px;">
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: <?php echo $bg_color; ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas <?php echo $icono; ?>" style="color: white; font-size: 14px;"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; color: #333; margin-bottom: 4px;">
                                <?php echo htmlspecialchars($actividad['usuario'] ?? 'Sistema'); ?>
                            </div>
                            <div style="font-size: 0.9rem; color: #666;">
                                <?php echo $texto_accion; ?> en <strong><?php echo htmlspecialchars($actividad['tabla']); ?></strong>
                            </div>
                            <div style="font-size: 0.8rem; color: #999; margin-top: 4px;">
                                <i class="fas fa-clock"></i> <?php echo $tiempo_texto; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Mantenimientos Próximos y Plan Semanal -->
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="dashboard-card">
            <h5 style="margin-bottom: 20px; font-weight: 700;">
                <i class="fas fa-calendar-check" style="color: #f5576c;"></i> Mantenimientos Pendientes
            </h5>
            
            <?php if (count($mantenimientos_proximos) > 0): ?>
            <div class="timeline-item" style="padding-left: 0;">
                <?php foreach ($mantenimientos_proximos as $index => $prox): ?>
                <div class="timeline-item">
                    <div class="timeline-dot" style="background: <?php echo $prox['dias'] > 90 ? '#dc3545' : ($prox['dias'] > 60 ? '#ffc107' : '#28a745'); ?>;">
                        <i class="fas fa-exclamation"></i>
                    </div>
                    <div style="padding: 12px; background: #f8f9fa; border-radius: 10px;">
                        <div style="font-weight: 600; color: #333; margin-bottom: 4px;">
                            Equipo: <?php echo htmlspecialchars($prox['codigo']); ?>
                        </div>
                        <div style="font-size: 0.85rem; color: #666; margin-bottom: 4px;">
                            Último mantenimiento: <?php echo date('d/m/Y', strtotime($prox['ultimo_mant'])); ?>
                        </div>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <span class="badge-modern" style="background: <?php echo $prox['dias'] > 90 ? '#dc3545' : ($prox['dias'] > 60 ? '#ffc107' : '#28a745'); ?>; color: white;">
                                <?php echo $prox['dias']; ?> días sin mantenimiento
                            </span>
                            <a href="<?php echo BASE_URL; ?>/views/mantenimientos/crear.php?equipo=<?php echo $prox['id']; ?>" 
                               style="font-size: 0.85rem; text-decoration: none; color: #667eea; font-weight: 600;">
                                Registrar <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #999;">
                <i class="fas fa-check-circle" style="font-size: 3rem; color: #28a745; margin-bottom: 16px;"></i>
                <p style="margin: 0; font-weight: 500;">¡Todo al día!</p>
                <p style="font-size: 0.9rem; margin-top: 8px;">No hay mantenimientos pendientes</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="col-md-6 mb-3">
        <div class="dashboard-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h5 style="margin: 0; font-weight: 700;">
                    <i class="fas fa-tasks" style="color: #667eea;"></i> Plan de la Semana
                </h5>
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 8px 16px; border-radius: 20px; font-weight: 700; font-size: 2rem;">
                    <?php 
                    $totalTasks = $estadisticas['reparacion'] + $pendientes['cantidad'];
                    $completedTasks = $estadisticas['operativos'];
                    $progress = $totalTasks > 0 ? round(($completedTasks / ($completedTasks + $totalTasks)) * 100) : 100;
                    echo $progress;
                    ?>%
                </div>
            </div>
            
            <div style="margin-bottom: 20px;">
                <div style="font-size: 0.85rem; color: #666; margin-bottom: 8px;">Promedio semanal</div>
                <div style="background: #e9ecef; height: 12px; border-radius: 10px; overflow: hidden;">
                    <div style="background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); height: 100%; width: <?php echo $progress; ?>%; transition: width 1s ease;"></div>
                </div>
            </div>
            
            <div style="display: grid; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8f9fa; border-radius: 10px;">
                    <input type="checkbox" checked disabled style="width: 20px; height: 20px; accent-color: #667eea;">
                    <div style="flex: 1;">
                        <div style="font-weight: 500;">Verificar equipos operativos</div>
                        <div style="font-size: 0.85rem; color: #666;"><?php echo $estadisticas['operativos']; ?> equipos funcionando correctamente</div>
                    </div>
                    <i class="fas fa-check-circle" style="color: #28a745; font-size: 1.5rem;"></i>
                </div>
                
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #fff3cd; border-radius: 10px;">
                    <input type="checkbox" <?php echo $estadisticas['reparacion'] == 0 ? 'checked' : ''; ?> disabled style="width: 20px; height: 20px; accent-color: #ffc107;">
                    <div style="flex: 1;">
                        <div style="font-weight: 500;">Atender equipos en reparación</div>
                        <div style="font-size: 0.85rem; color: #666;"><?php echo $estadisticas['reparacion']; ?> equipos requieren atención</div>
                    </div>
                    <i class="fas fa-wrench" style="color: #ffc107; font-size: 1.5rem;"></i>
                </div>
                
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8d7da; border-radius: 10px;">
                    <input type="checkbox" disabled style="width: 20px; height: 20px; accent-color: #dc3545;">
                    <div style="flex: 1;">
                        <div style="font-weight: 500;">Procesar mantenimientos pendientes</div>
                        <div style="font-size: 0.85rem; color: #666;"><?php echo $pendientes['cantidad']; ?> tareas por completar</div>
                    </div>
                    <i class="fas fa-clock" style="color: #dc3545; font-size: 1.5rem;"></i>
                </div>
                
                <a href="<?php echo BASE_URL; ?>/views/equipos/" style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #d4edda; border-radius: 10px; text-decoration: none; transition: all 0.3s ease;">
                    <input type="checkbox" disabled style="width: 20px; height: 20px; accent-color: #28a745;">
                    <div style="flex: 1;">
                        <div style="font-weight: 500; color: #333;">Revisar inventario completo</div>
                        <div style="font-size: 0.85rem; color: #666;"><?php echo $estadisticas['total']; ?> equipos totales</div>
                    </div>
                    <i class="fas fa-arrow-right" style="color: #28a745; font-size: 1.2rem;"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Configuración de Chart.js con diseño moderno
Chart.defaults.font.family = "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";
Chart.defaults.color = '#666';

// Datos para gráficos
const estadoLabels = <?php echo json_encode(array_column($equipos_por_estado, 'estado')); ?>;
const estadoData = <?php echo json_encode(array_column($equipos_por_estado, 'cantidad')); ?>;

const mesesLabels = <?php echo json_encode(array_column($mantenimientos_mes, 'mes_nombre')); ?>;
const mesesData = <?php echo json_encode(array_column($mantenimientos_mes, 'cantidad')); ?>;

// Gráfico de Estados (Doughnut moderno)
const ctxEstados = document.getElementById('chartEstados').getContext('2d');
new Chart(ctxEstados, {
    type: 'doughnut',
    data: {
        labels: estadoLabels,
        datasets: [{
            data: estadoData,
            backgroundColor: [
                '#667eea',
                '#764ba2',
                '#f093fb',
                '#f5576c',
                '#4facfe',
                '#00f2fe'
            ],
            borderWidth: 0,
            hoverOffset: 15
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    usePointStyle: true,
                    pointStyle: 'circle',
                    font: {
                        size: 12,
                        weight: '500'
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                padding: 12,
                borderRadius: 8,
                titleFont: {
                    size: 14,
                    weight: 'bold'
                },
                bodyFont: {
                    size: 13
                },
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                    }
                }
            }
        },
        cutout: '65%'
    }
});

// Gráfico de Mantenimientos (Línea moderna con degradado)
const ctxMantenimientos = document.getElementById('chartMantenimientos').getContext('2d');
const gradientMantenimientos = ctxMantenimientos.createLinearGradient(0, 0, 0, 300);
gradientMantenimientos.addColorStop(0, 'rgba(102, 126, 234, 0.4)');
gradientMantenimientos.addColorStop(1, 'rgba(118, 75, 162, 0.05)');

new Chart(ctxMantenimientos, {
    type: 'line',
    data: {
        labels: mesesLabels.length > 0 ? mesesLabels : ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
        datasets: [{
            label: 'Mantenimientos',
            data: mesesData.length > 0 ? mesesData : [0, 0, 0, 0, 0, 0],
            borderColor: '#667eea',
            backgroundColor: gradientMantenimientos,
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointRadius: 6,
            pointBackgroundColor: '#667eea',
            pointBorderColor: '#fff',
            pointBorderWidth: 3,
            pointHoverRadius: 8,
            pointHoverBackgroundColor: '#764ba2',
            pointHoverBorderWidth: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                padding: 12,
                borderRadius: 8,
                titleFont: {
                    size: 14,
                    weight: 'bold'
                },
                bodyFont: {
                    size: 13
                }
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    font: {
                        size: 12,
                        weight: '500'
                    }
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0,0,0,0.05)',
                    drawBorder: false
                },
                ticks: {
                    font: {
                        size: 12,
                        weight: '500'
                    },
                    stepSize: 1
                }
            }
        }
    }
});

// Filtros interactivos
document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        const filter = this.dataset.filter;
        // Aquí puedes agregar lógica para filtrar contenido si lo necesitas
        console.log('Filtro seleccionado:', filter);
    });
});

// Animación de números al cargar
document.addEventListener('DOMContentLoaded', function() {
    const statNumbers = document.querySelectorAll('.stat-number, .metric-value');
    statNumbers.forEach(num => {
        const finalValue = parseInt(num.textContent);
        if (!isNaN(finalValue)) {
            let currentValue = 0;
            const increment = finalValue / 30;
            const timer = setInterval(() => {
                currentValue += increment;
                if (currentValue >= finalValue) {
                    num.textContent = finalValue;
                    clearInterval(timer);
                } else {
                    num.textContent = Math.floor(currentValue);
                }
            }, 30);
        }
    });
    
    // Generar Sparklines
    generateSparkline();
});

// Función para generar sparklines
function generateSparkline() {
    // Datos para equipos (últimos 7 días)
    const equiposData = <?php echo json_encode(array_column($sparkline_equipos, 'cantidad')); ?>;
    const mantenimientosData = <?php echo json_encode(array_column($sparkline_mantenimientos, 'cantidad')); ?>;
    
    // Rellenar con ceros si no hay suficientes datos
    while (equiposData.length < 7) equiposData.unshift(0);
    while (mantenimientosData.length < 7) mantenimientosData.unshift(0);
    
    // Generar sparkline para equipos
    createSparkline('sparklineEquipos', equiposData, '#667eea', '#764ba2');
    
    // Generar sparkline para mantenimientos
    createSparkline('sparklineMantenimientos', mantenimientosData, '#11998e', '#38ef7d');
    
    // Generar indicador circular para operativos
    const totalEquipos = <?php echo $estadisticas['total']; ?>;
    const operativos = <?php echo $estadisticas['operativos']; ?>;
    const porcentaje = totalEquipos > 0 ? (operativos / totalEquipos) * 100 : 0;
    createCircularProgress('sparklineOperativos', porcentaje, '#4facfe', '#00f2fe');
}

function createSparkline(containerId, data, colorStart, colorEnd) {
    const svg = document.getElementById(containerId);
    if (!svg || data.length === 0) return;
    
    const width = 200;
    const height = 60;
    const padding = 5;
    
    // Encontrar min y max
    const max = Math.max(...data, 1);
    const min = Math.min(...data, 0);
    const range = max - min || 1;
    
    // Crear puntos
    const points = data.map((value, index) => {
        const x = (index / (data.length - 1)) * (width - padding * 2) + padding;
        const y = height - padding - ((value - min) / range) * (height - padding * 2);
        return {x, y, value};
    });
    
    // Crear path para la línea
    let pathD = `M ${points[0].x} ${points[0].y}`;
    for (let i = 1; i < points.length; i++) {
        // Usar curvas suaves
        const prev = points[i - 1];
        const curr = points[i];
        const cpx = (prev.x + curr.x) / 2;
        pathD += ` Q ${cpx} ${prev.y}, ${cpx} ${(prev.y + curr.y) / 2} Q ${cpx} ${curr.y}, ${curr.x} ${curr.y}`;
    }
    
    // Crear gradiente
    const gradient = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
    gradient.innerHTML = `
        <linearGradient id="gradient-${containerId}" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" style="stop-color:${colorStart};stop-opacity:1" />
            <stop offset="100%" style="stop-color:${colorEnd};stop-opacity:1" />
        </linearGradient>
        <linearGradient id="area-${containerId}" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" style="stop-color:${colorStart};stop-opacity:0.3" />
            <stop offset="100%" style="stop-color:${colorStart};stop-opacity:0" />
        </linearGradient>
    `;
    svg.appendChild(gradient);
    
    // Crear área de relleno
    const areaPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    let areaD = pathD + ` L ${points[points.length - 1].x} ${height} L ${points[0].x} ${height} Z`;
    areaPath.setAttribute('d', areaD);
    areaPath.setAttribute('fill', `url(#area-${containerId})`);
    areaPath.setAttribute('class', 'sparkline-area');
    svg.appendChild(areaPath);
    
    // Crear línea
    const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    path.setAttribute('d', pathD);
    path.setAttribute('stroke', `url(#gradient-${containerId})`);
    path.setAttribute('class', 'sparkline-path');
    svg.appendChild(path);
    
    // Crear puntos
    points.forEach((point, index) => {
        const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        circle.setAttribute('cx', point.x);
        circle.setAttribute('cy', point.y);
        circle.setAttribute('r', index === points.length - 1 ? '4' : '2.5');
        circle.setAttribute('fill', index === points.length - 1 ? colorEnd : colorStart);
        circle.setAttribute('class', 'sparkline-dot');
        circle.setAttribute('style', `animation-delay: ${index * 0.1}s`);
        
        // Tooltip
        const title = document.createElementNS('http://www.w3.org/2000/svg', 'title');
        title.textContent = `Día ${index + 1}: ${point.value}`;
        circle.appendChild(title);
        
        svg.appendChild(circle);
    });
}

function createCircularProgress(containerId, percentage, colorStart, colorEnd) {
    const svg = document.getElementById(containerId);
    if (!svg) return;
    
    const cx = 100;
    const cy = 30;
    const radius = 25;
    const circumference = 2 * Math.PI * radius;
    const offset = circumference - (percentage / 100) * circumference;
    
    // Crear gradiente
    const gradient = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
    gradient.innerHTML = `
        <linearGradient id="circular-gradient-${containerId}">
            <stop offset="0%" style="stop-color:${colorStart};stop-opacity:1" />
            <stop offset="100%" style="stop-color:${colorEnd};stop-opacity:1" />
        </linearGradient>
    `;
    svg.appendChild(gradient);
    
    // Círculo de fondo
    const bgCircle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
    bgCircle.setAttribute('cx', cx);
    bgCircle.setAttribute('cy', cy);
    bgCircle.setAttribute('r', radius);
    bgCircle.setAttribute('fill', 'none');
    bgCircle.setAttribute('stroke', '#e9ecef');
    bgCircle.setAttribute('stroke-width', '6');
    svg.appendChild(bgCircle);
    
    // Círculo de progreso
    const progressCircle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
    progressCircle.setAttribute('cx', cx);
    progressCircle.setAttribute('cy', cy);
    progressCircle.setAttribute('r', radius);
    progressCircle.setAttribute('fill', 'none');
    progressCircle.setAttribute('stroke', `url(#circular-gradient-${containerId})`);
    progressCircle.setAttribute('stroke-width', '6');
    progressCircle.setAttribute('stroke-linecap', 'round');
    progressCircle.setAttribute('stroke-dasharray', circumference);
    progressCircle.setAttribute('stroke-dashoffset', offset);
    progressCircle.setAttribute('transform', `rotate(-90 ${cx} ${cy})`);
    progressCircle.setAttribute('style', 'transition: stroke-dashoffset 1.5s ease-out;');
    svg.appendChild(progressCircle);
    
    // Texto del porcentaje
    const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    text.setAttribute('x', cx);
    text.setAttribute('y', cy + 5);
    text.setAttribute('text-anchor', 'middle');
    text.setAttribute('font-size', '14');
    text.setAttribute('font-weight', 'bold');
    text.setAttribute('fill', '#333');
    text.textContent = Math.round(percentage) + '%';
    svg.appendChild(text);
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
