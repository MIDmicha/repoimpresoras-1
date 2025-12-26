<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/views/login.php');
    exit;
}

$db = getDB();
$nombre_usuario = $_SESSION['user_data']['nombre'] ?? 'Usuario';

// ==================== ESTADÍSTICAS PRINCIPALES ====================
$sqlEstadisticas = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN e.nombre = 'Operativo' THEN 1 ELSE 0 END) as operativos,
                        SUM(CASE WHEN e.nombre = 'En Reparación' THEN 1 ELSE 0 END) as reparacion
                    FROM equipos eq
                    LEFT JOIN estados_equipo e ON eq.id_estado = e.id";
$stmt = $db->query($sqlEstadisticas);
$estadisticas = $stmt->fetch(PDO::FETCH_ASSOC);

// ==================== EQUIPOS POR ESTADO ====================
$sqlEstados = "SELECT 
                   e.nombre as estado,
                   COUNT(eq.id) as cantidad,
                   ROUND(COUNT(eq.id) * 100.0 / (SELECT COUNT(*) FROM equipos), 2) as porcentaje
               FROM equipos eq
               LEFT JOIN estados_equipo e ON eq.id_estado = e.id
               GROUP BY e.nombre
               ORDER BY cantidad DESC";
$stmtEstados = $db->query($sqlEstados);
$equipos_por_estado = $stmtEstados->fetchAll(PDO::FETCH_ASSOC);

// ==================== MANTENIMIENTOS POR MES (12 MESES) ====================
$sqlMantenimientos = "SELECT 
                          MONTH(m.fecha_mantenimiento) as mes,
                          MONTHNAME(m.fecha_mantenimiento) as mes_nombre,
                          COUNT(*) as total,
                          SUM(CASE WHEN td.nombre = 'Preventivo' THEN 1 ELSE 0 END) as preventivo,
                          SUM(CASE WHEN td.nombre = 'Correctivo' THEN 1 ELSE 0 END) as correctivo
                      FROM mantenimientos m
                      LEFT JOIN tipos_demanda td ON m.id_tipo_demanda = td.id
                      WHERE m.fecha_mantenimiento >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                      GROUP BY MONTH(m.fecha_mantenimiento), MONTHNAME(m.fecha_mantenimiento)
                      ORDER BY mes ASC";
$stmtMant = $db->query($sqlMantenimientos);
$mantenimientos_mes = $stmtMant->fetchAll(PDO::FETCH_ASSOC);

// ==================== EQUIPOS POR MARCA (TOP 5) ====================
$sqlMarcas = "SELECT marca, COUNT(*) as cantidad
              FROM equipos
              GROUP BY marca
              ORDER BY cantidad DESC
              LIMIT 5";
$stmtMarcas = $db->query($sqlMarcas);
$equipos_por_marca = $stmtMarcas->fetchAll(PDO::FETCH_ASSOC);

// ==================== TOP 10 SEDES ====================
$sqlTopSedes = "SELECT 
                    s.nombre,
                    COUNT(e.id) as cantidad,
                    SUM(CASE WHEN est.nombre = 'Operativo' THEN 1 ELSE 0 END) as operativos,
                    SUM(CASE WHEN est.nombre = 'En Reparación' THEN 1 ELSE 0 END) as en_reparacion
                FROM equipos e
                INNER JOIN sedes s ON e.id_sede = s.id
                LEFT JOIN estados_equipo est ON e.id_estado = est.id
                GROUP BY s.nombre
                ORDER BY cantidad DESC
                LIMIT 10";
$stmtSedes = $db->query($sqlTopSedes);
$top_sedes = $stmtSedes->fetchAll(PDO::FETCH_ASSOC);

// ==================== MANTENIMIENTOS POR TIPO DE DEMANDA ====================
$sqlTipoDemanda = "SELECT 
                       td.nombre,
                       COUNT(m.id) as cantidad
                   FROM mantenimientos m
                   INNER JOIN tipos_demanda td ON m.id_tipo_demanda = td.id
                   WHERE m.fecha_mantenimiento >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                   GROUP BY td.nombre
                   ORDER BY cantidad DESC
                   LIMIT 5";
$stmtTipoDemanda = $db->query($sqlTipoDemanda);
$mantenimientos_por_tipo = $stmtTipoDemanda->fetchAll(PDO::FETCH_ASSOC);

// ==================== EQUIPOS POR AÑO DE ADQUISICIÓN ====================
$sqlAnio = "SELECT 
                anio_adquisicion as anio,
                COUNT(*) as cantidad
            FROM equipos
            WHERE anio_adquisicion IS NOT NULL
            GROUP BY anio_adquisicion
            ORDER BY anio ASC";
$stmtAnio = $db->query($sqlAnio);
$equipos_por_anio = $stmtAnio->fetchAll(PDO::FETCH_ASSOC);

// ==================== EQUIPOS SIN MANTENIMIENTO (>90 DÍAS) ====================
$sqlSinMant = "SELECT 
                   e.codigo_patrimonial as codigo,
                   e.id,
                   s.nombre as sede,
                   MAX(m.fecha_mantenimiento) as ultimo_mant,
                   DATEDIFF(NOW(), MAX(m.fecha_mantenimiento)) as dias
               FROM equipos e
               INNER JOIN sedes s ON e.id_sede = s.id
               LEFT JOIN mantenimientos m ON e.id = m.id_equipo
               GROUP BY e.id
               HAVING dias > 90 OR dias IS NULL
               ORDER BY dias DESC
               LIMIT 10";
$stmtSinMant = $db->query($sqlSinMant);
$equipos_sin_mantenimiento = $stmtSinMant->fetchAll(PDO::FETCH_ASSOC);

// ==================== ACTIVIDAD RECIENTE ====================
$sqlActividad = "SELECT 
                     a.tabla, 
                     a.accion, 
                     DATE_FORMAT(a.fecha_hora, '%d/%m/%Y %H:%i') as fecha_hora,
                     COALESCE(u.nombre_completo, 'Sistema') as usuario
                 FROM auditoria a
                 LEFT JOIN usuarios u ON a.id_usuario = u.id
                 ORDER BY a.fecha_hora DESC
                 LIMIT 15";
$stmtActividad = $db->query($sqlActividad);
$actividad_reciente = $stmtActividad->fetchAll(PDO::FETCH_ASSOC);

// ==================== TASA DE DISPONIBILIDAD ====================
$tasa_disponibilidad = $estadisticas['total'] > 0 
    ? round(($estadisticas['operativos'] / $estadisticas['total']) * 100, 1) 
    : 0;

// ==================== SPARKLINES (ÚLTIMOS 7 DÍAS) ====================
$sqlSparklineEquipos = "SELECT DATE(fecha_creacion) as fecha, COUNT(*) as cantidad
                        FROM equipos
                        WHERE fecha_creacion >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                        GROUP BY DATE(fecha_creacion)
                        ORDER BY fecha ASC";
$stmtSparkline = $db->query($sqlSparklineEquipos);
$sparkline_equipos = $stmtSparkline->fetchAll(PDO::FETCH_ASSOC);

$sqlSparklineMant = "SELECT DATE(fecha_mantenimiento) as fecha, COUNT(*) as cantidad
                     FROM mantenimientos
                     WHERE fecha_mantenimiento >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                     GROUP BY DATE(fecha_mantenimiento)
                     ORDER BY fecha ASC";
$stmtSparklineMant = $db->query($sqlSparklineMant);
$sparkline_mantenimientos = $stmtSparklineMant->fetchAll(PDO::FETCH_ASSOC);

// ==================== CALCULAR TENDENCIAS ====================
$sqlTendencia = "SELECT COUNT(*) as esta_semana
                 FROM equipos
                 WHERE fecha_creacion >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$stmtTendencia = $db->query($sqlTendencia);
$tendencia = $stmtTendencia->fetch(PDO::FETCH_ASSOC);

$sqlTendenciaAnt = "SELECT COUNT(*) as semana_anterior
                    FROM equipos
                    WHERE fecha_creacion >= DATE_SUB(NOW(), INTERVAL 14 DAY)
                    AND fecha_creacion < DATE_SUB(NOW(), INTERVAL 7 DAY)";
$stmtTendenciaAnt = $db->query($sqlTendenciaAnt);
$tendencia_anterior = $stmtTendenciaAnt->fetch(PDO::FETCH_ASSOC);

$cambio_equipos = 0;
if ($tendencia_anterior['semana_anterior'] > 0) {
    $cambio_equipos = round((($tendencia['esta_semana'] - $tendencia_anterior['semana_anterior']) / $tendencia_anterior['semana_anterior']) * 100, 1);
}

// ==================== NUEVOS GRÁFICOS ADICIONALES ====================

// 1. Acciones de Auditoría por Tipo (INSERT, UPDATE, DELETE)
$sqlAuditoriaTipo = "SELECT 
                        accion,
                        COUNT(*) as cantidad
                     FROM auditoria
                     WHERE fecha_hora >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                     GROUP BY accion
                     ORDER BY cantidad DESC";
$stmtAuditoriaTipo = $db->query($sqlAuditoriaTipo);
$auditoria_por_tipo = $stmtAuditoriaTipo->fetchAll(PDO::FETCH_ASSOC);

// 2. Actividad por Tabla (TOP 8)
$sqlActividadTabla = "SELECT 
                          tabla,
                          COUNT(*) as cantidad,
                          MAX(fecha_hora) as ultima_actividad
                      FROM auditoria
                      WHERE fecha_hora >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                      GROUP BY tabla
                      ORDER BY cantidad DESC
                      LIMIT 8";
$stmtActividadTabla = $db->query($sqlActividadTabla);
$actividad_por_tabla = $stmtActividadTabla->fetchAll(PDO::FETCH_ASSOC);

// 3. Timeline de Mantenimientos (últimos 30 días)
$sqlTimelineMant = "SELECT 
                        DATE(m.fecha_mantenimiento) as fecha,
                        COUNT(*) as cantidad,
                        td.nombre as tipo
                    FROM mantenimientos m
                    LEFT JOIN tipos_demanda td ON m.id_tipo_demanda = td.id
                    WHERE m.fecha_mantenimiento >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY DATE(m.fecha_mantenimiento), td.nombre
                    ORDER BY fecha ASC";
$stmtTimelineMant = $db->query($sqlTimelineMant);
$timeline_mantenimientos = $stmtTimelineMant->fetchAll(PDO::FETCH_ASSOC);

// 4. Distribución por Distrito Fiscal
$sqlDistritos = "SELECT 
                     df.nombre as distrito,
                     COUNT(e.id) as cantidad
                 FROM equipos e
                 INNER JOIN distritos_fiscales df ON e.id_distrito = df.id
                 GROUP BY df.nombre
                 ORDER BY cantidad DESC";
$stmtDistritos = $db->query($sqlDistritos);
$equipos_por_distrito = $stmtDistritos->fetchAll(PDO::FETCH_ASSOC);

// 5. Estado de Garantías
$sqlGarantias = "SELECT 
                     CASE 
                         WHEN garantia IS NOT NULL AND garantia != '' THEN 'Con Garantía'
                         ELSE 'Sin Garantía'
                     END as estado,
                     COUNT(*) as cantidad
                 FROM equipos
                 GROUP BY estado";
$stmtGarantias = $db->query($sqlGarantias);
$equipos_garantia = $stmtGarantias->fetchAll(PDO::FETCH_ASSOC);

// 6. Equipos con/sin Estabilizador
$sqlEstabilizador = "SELECT 
                         CASE 
                             WHEN tiene_estabilizador = 1 THEN 'Con Estabilizador'
                             ELSE 'Sin Estabilizador'
                         END as estado,
                         COUNT(*) as cantidad
                     FROM equipos
                     GROUP BY tiene_estabilizador";
$stmtEstabilizador = $db->query($sqlEstabilizador);
$equipos_estabilizador = $stmtEstabilizador->fetchAll(PDO::FETCH_ASSOC);

// 7. Clasificación de Equipos (Impresora vs Multifuncional)
$sqlClasificacion = "SELECT 
                         clasificacion,
                         COUNT(*) as cantidad
                     FROM equipos
                     GROUP BY clasificacion";
$stmtClasificacion = $db->query($sqlClasificacion);
$equipos_clasificacion = $stmtClasificacion->fetchAll(PDO::FETCH_ASSOC);

// 8. Actividad de Auditoría por Usuario (TOP 10)
$sqlAuditoriaUsuarios = "SELECT 
                             COALESCE(u.nombre_completo, 'Sistema') as usuario,
                             COUNT(a.id) as acciones
                         FROM auditoria a
                         LEFT JOIN usuarios u ON a.id_usuario = u.id
                         WHERE a.fecha_hora >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                         GROUP BY u.nombre_completo
                         ORDER BY acciones DESC
                         LIMIT 10";
$stmtAuditoriaUsuarios = $db->query($sqlAuditoriaUsuarios);
$auditoria_por_usuario = $stmtAuditoriaUsuarios->fetchAll(PDO::FETCH_ASSOC);

// 9. Mantenimientos por Técnico Responsable (TOP 5)
$sqlTecnicos = "SELECT 
                    COALESCE(tecnico_responsable, 'Sin asignar') as tecnico,
                    COUNT(*) as cantidad
                FROM mantenimientos
                WHERE fecha_mantenimiento >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY tecnico_responsable
                ORDER BY cantidad DESC
                LIMIT 5";
$stmtTecnicos = $db->query($sqlTecnicos);
$mantenimientos_por_tecnico = $stmtTecnicos->fetchAll(PDO::FETCH_ASSOC);

// 10. Timeline de Actividad del Sistema (últimos 15 días)
$sqlTimelineActividad = "SELECT 
                             DATE(fecha_hora) as fecha,
                             COUNT(*) as total,
                             SUM(CASE WHEN accion = 'INSERT' THEN 1 ELSE 0 END) as inserciones,
                             SUM(CASE WHEN accion = 'UPDATE' THEN 1 ELSE 0 END) as actualizaciones,
                             SUM(CASE WHEN accion = 'DELETE' THEN 1 ELSE 0 END) as eliminaciones
                         FROM auditoria
                         WHERE fecha_hora >= DATE_SUB(NOW(), INTERVAL 15 DAY)
                         GROUP BY DATE(fecha_hora)
                         ORDER BY fecha ASC";
$stmtTimelineActividad = $db->query($sqlTimelineActividad);
$timeline_actividad = $stmtTimelineActividad->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header.php';
?>

<style>
/* Cuba Admin Template Inspired Styles */
.widget-card {
    border-radius: 10px;
    border: none;
    box-shadow: 0 0 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.widget-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 30px rgba(0,0,0,0.15);
}

.widget-round {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 15px;
    position: relative;
}

.widget-round.primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.widget-round.success { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.widget-round.warning { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
.widget-round.info { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); }

.widget-round svg, .widget-round i {
    color: white;
    font-size: 24px;
}

.trend-badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.trend-badge.up {
    background: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

.trend-badge.down {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.chart-container {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 0 20px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    border-bottom: 1px solid #eee;
    padding-bottom: 15px;
}

.chart-header h5 {
    margin: 0;
    font-weight: 600;
    color: #2F2F3B;
}

.table-modern {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 0 20px rgba(0,0,0,0.08);
}

.table-modern thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.table-modern thead th {
    border: none;
    font-weight: 600;
    padding: 15px;
}

.table-modern tbody td {
    padding: 12px 15px;
    vertical-align: middle;
}

.table-modern tbody tr {
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.2s;
}

.table-modern tbody tr:hover {
    background: #f8f9fa;
}

.badge-status {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

.animate-fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ============================================ */
/* MODO OSCURO - Dark Mode Styles */
/* ============================================ */
body.dark-mode {
    background: #1a1d2e;
    color: #e4e4e7;
}

body.dark-mode .content-wrapper {
    background: #1a1d2e;
}

body.dark-mode .widget-card {
    background: #252b3d;
    box-shadow: 0 0 20px rgba(0,0,0,0.3);
    color: #e4e4e7;
}

body.dark-mode .widget-card:hover {
    box-shadow: 0 5px 30px rgba(0,0,0,0.5);
}

body.dark-mode .text-muted {
    color: #9ca3af !important;
}

body.dark-mode h3, 
body.dark-mode h2,
body.dark-mode h5,
body.dark-mode h6 {
    color: #e4e4e7;
}

body.dark-mode .chart-container {
    background: #252b3d;
    box-shadow: 0 0 20px rgba(0,0,0,0.3);
}

body.dark-mode .chart-header {
    border-bottom-color: #3a4158;
}

body.dark-mode .chart-header h5 {
    color: #e4e4e7;
}

body.dark-mode .table-modern {
    background: #252b3d;
    box-shadow: 0 0 20px rgba(0,0,0,0.3);
}

body.dark-mode .table-modern tbody td {
    color: #e4e4e7;
}

body.dark-mode .table-modern tbody tr {
    border-bottom-color: #3a4158;
}

body.dark-mode .table-modern tbody tr:hover {
    background: #2d3548;
}

body.dark-mode .badge.bg-light {
    background: #3a4158 !important;
    color: #e4e4e7 !important;
}

body.dark-mode .trend-badge.up {
    background: rgba(40, 167, 69, 0.2);
}

body.dark-mode .trend-badge.down {
    background: rgba(220, 53, 69, 0.2);
}

/* ApexCharts Dark Mode */
body.dark-mode .apexcharts-canvas {
    background: transparent !important;
}

body.dark-mode .apexcharts-text {
    fill: #9ca3af !important;
}

body.dark-mode .apexcharts-legend-text {
    color: #9ca3af !important;
}

body.dark-mode .apexcharts-gridline {
    stroke: #3a4158 !important;
}

body.dark-mode .apexcharts-tooltip {
    background: #252b3d !important;
    border: 1px solid #3a4158 !important;
    color: #e4e4e7 !important;
}

body.dark-mode .apexcharts-tooltip-title {
    background: #1a1d2e !important;
    border-bottom: 1px solid #3a4158 !important;
    color: #e4e4e7 !important;
}
</style>

<div class="container-fluid py-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card widget-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2" style="font-weight: 700;">¡Bienvenido, <?php echo htmlspecialchars($nombre_usuario); ?>!</h2>
                            <p class="mb-3" style="opacity: 0.9;">Aquí está el resumen de tu sistema de gestión de impresoras</p>
                            <a href="<?php echo BASE_URL; ?>/views/equipos/index.php" class="btn btn-light btn-sm">
                                <i class="fas fa-plus me-2"></i>Agregar Equipo
                            </a>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="clockbox" style="font-size: 48px; font-weight: 700;">
                                <div id="clock-time"><?php echo date('h:i A'); ?></div>
                                <div style="font-size: 16px; opacity: 0.8;"><?php echo date('d/m/Y'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Widget Cards Row -->
    <div class="row mb-4">
        <!-- Total Equipos -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card widget-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2">Total Equipos</h6>
                            <h3 class="mb-0 fw-bold"><?php echo $estadisticas['total']; ?></h3>
                            <?php if ($cambio_equipos != 0): ?>
                            <span class="trend-badge <?php echo $cambio_equipos > 0 ? 'up' : 'down'; ?>">
                                <i class="fas fa-arrow-<?php echo $cambio_equipos > 0 ? 'up' : 'down'; ?>"></i>
                                <?php echo abs($cambio_equipos); ?>%
                            </span>
                            <?php endif; ?>
                        </div>
                        <div class="widget-round primary">
                            <i class="fas fa-print"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Operativos -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card widget-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2">Operativos</h6>
                            <h3 class="mb-0 fw-bold text-success"><?php echo $estadisticas['operativos']; ?></h3>
                            <span class="trend-badge up">
                                <i class="fas fa-check-circle"></i>
                                <?php echo $tasa_disponibilidad; ?>%
                            </span>
                        </div>
                        <div class="widget-round success">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- En Reparación -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card widget-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2">En Reparación</h6>
                            <h3 class="mb-0 fw-bold text-danger"><?php echo $estadisticas['reparacion']; ?></h3>
                            <span class="trend-badge down">
                                <i class="fas fa-tools"></i>
                                Atención requerida
                            </span>
                        </div>
                        <div class="widget-round warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Disponibilidad -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card widget-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2">Disponibilidad</h6>
                            <h3 class="mb-0 fw-bold"><?php echo $tasa_disponibilidad; ?>%</h3>
                            <span class="trend-badge <?php echo $tasa_disponibilidad >= 80 ? 'up' : 'down'; ?>">
                                <i class="fas fa-chart-line"></i>
                                Sistema
                            </span>
                        </div>
                        <div class="widget-round info">
                            <i class="fas fa-percentage"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row mb-4">
        <!-- Chart 1: Equipos por Estado (Donut) -->
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="chart-container">
                <div class="chart-header">
                    <h5><i class="fas fa-chart-pie me-2" style="color: #667eea;"></i>Equipos por Estado</h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">Hoy</a></li>
                            <li><a class="dropdown-item" href="#">Esta Semana</a></li>
                            <li><a class="dropdown-item" href="#">Este Mes</a></li>
                        </ul>
                    </div>
                </div>
                <div id="chartEstados" style="height: 300px;"></div>
            </div>
        </div>

        <!-- Chart 2: Mantenimientos Mensuales (Stacked Bar) -->
        <div class="col-xl-5 col-lg-6 mb-4">
            <div class="chart-container">
                <div class="chart-header">
                    <h5><i class="fas fa-chart-bar me-2" style="color: #667eea;"></i>Mantenimientos Mensuales</h5>
                    <span class="badge bg-light text-dark">Últimos 12 meses</span>
                </div>
                <div id="chartMantenimientos" style="height: 300px;"></div>
            </div>
        </div>

        <!-- Chart 3: Disponibilidad (Radial) -->
        <div class="col-xl-3 col-lg-12 mb-4">
            <div class="chart-container">
                <div class="chart-header">
                    <h5><i class="fas fa-tachometer-alt me-2" style="color: #667eea;"></i>Disponibilidad</h5>
                </div>
                <div id="chartDisponibilidad" style="height: 300px;"></div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row mb-4">
        <!-- Chart 4: Top 5 Marcas (Horizontal Bar) -->
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="chart-container">
                <div class="chart-header">
                    <h5><i class="fas fa-star me-2" style="color: #667eea;"></i>Top 5 Marcas</h5>
                </div>
                <div id="chartMarcas" style="height: 300px;"></div>
            </div>
        </div>

        <!-- Chart 5: Tipo de Demanda (Pie) -->
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="chart-container">
                <div class="chart-header">
                    <h5><i class="fas fa-clipboard-list me-2" style="color: #667eea;"></i>Tipo de Demanda</h5>
                </div>
                <div id="chartTipoDemanda" style="height: 300px;"></div>
            </div>
        </div>

        <!-- Chart 6: Equipos por Año (Area) -->
        <div class="col-xl-4 col-lg-12 mb-4">
            <div class="chart-container">
                <div class="chart-header">
                    <h5><i class="fas fa-calendar me-2" style="color: #667eea;"></i>Adquisiciones por Año</h5>
                </div>
                <div id="chartAnios" style="height: 300px;"></div>
            </div>
        </div>
    </div>

    <!-- Charts Row 3 -->
    <div class="row mb-4">
        <!-- Chart 7: Sparkline Equipos (Line) -->
        <div class="col-xl-6 col-lg-6 mb-4">
            <div class="chart-container">
                <div class="chart-header">
                    <h5><i class="fas fa-chart-line me-2" style="color: #667eea;"></i>Tendencia de Equipos</h5>
                    <span class="badge bg-light text-dark">Últimos 7 días</span>
                </div>
                <div id="chartSparklineEquipos" style="height: 150px;"></div>
            </div>
        </div>

        <!-- Chart 8: Sparkline Mantenimientos (Area) -->
        <div class="col-xl-6 col-lg-6 mb-4">
            <div class="chart-container">
                <div class="chart-header">
                    <h5><i class="fas fa-tools me-2" style="color: #667eea;"></i>Actividad de Mantenimientos</h5>
                    <span class="badge bg-light text-dark">Últimos 7 días</span>
                </div>
                <div id="chartSparklineMant" style="height: 150px;"></div>
            </div>
        </div>
    </div>

    <!-- Charts Row 4: Auditoría y Sistema -->
    <div class="row mb-4">
        <!-- Chart 9: Acciones de Auditoría (Donut) -->
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="chart-container">
                <div class="chart-header">
                    <h5><i class="fas fa-history me-2" style="color: #667eea;"></i>Acciones de Auditoría</h5>
                    <span class="badge bg-light text-dark">Últimos 30 días</span>
                </div>
                <div id="chartAuditoriaTipo" style="height: 300px;"></div>
            </div>
        </div>

        <!-- Chart 10: Actividad por Tabla (Horizontal Bar) -->
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="chart-container">
                <div class="chart-header">
                    <h5><i class="fas fa-database me-2" style="color: #667eea;"></i>Actividad por Tabla</h5>
                    <span class="badge bg-light text-dark">TOP 8</span>
                </div>
                <div id="chartActividadTabla" style="height: 300px;"></div>
            </div>
        </div>

        <!-- Chart 11: Clasificación de Equipos (Donut) -->
        <div class="col-xl-4 col-lg-12 mb-4">
            <div class="chart-container">
                <div class="chart-header">
                    <h5><i class="fas fa-layer-group me-2" style="color: #667eea;"></i>Clasificación</h5>
                </div>
                <div id="chartClasificacion" style="height: 300px;"></div>
            </div>
        </div>
    </div>

    <!-- Charts Row 5: Estadísticas Detalladas -->
    <div class="row mb-4">
        <!-- Chart 12: Distribución por Distrito (Polar Area) -->
        <div class="col-xl-6 col-lg-6 mb-4">
            <div class="chart-container">
                <div class="chart-header">
                    <h5><i class="fas fa-map-marked-alt me-2" style="color: #667eea;"></i>Distritos Fiscales</h5>
                </div>
                <div id="chartDistritos" style="height: 320px;"></div>
            </div>
        </div>

        <!-- Chart 13: Garantías (Donut) -->
        <div class="col-xl-3 col-lg-6 mb-4">
            <div class="chart-container">
                <div class="chart-header">
                    <h5><i class="fas fa-shield-alt me-2" style="color: #667eea;"></i>Garantías</h5>
                </div>
                <div id="chartGarantias" style="height: 320px;"></div>
            </div>
        </div>

        <!-- Chart 14: Estabilizadores (Donut) -->
        <div class="col-xl-3 col-lg-6 mb-4">
            <div class="chart-container">
                <div class="chart-header">
                    <h5><i class="fas fa-plug me-2" style="color: #667eea;"></i>Estabilizadores</h5>
                </div>
                <div id="chartEstabilizador" style="height: 320px;"></div>
            </div>
        </div>
    </div>

    <!-- Charts Row 6: Timeline y Actividad -->
    <div class="row mb-4">
        <!-- Chart 15: Timeline de Actividad del Sistema (Area Stacked) -->
        <div class="col-xl-8 col-lg-12 mb-4">
            <div class="chart-container">
                <div class="chart-header">
                    <h5><i class="fas fa-chart-area me-2" style="color: #667eea;"></i>Timeline de Actividad del Sistema</h5>
                    <span class="badge bg-light text-dark">Últimos 15 días</span>
                </div>
                <div id="chartTimelineActividad" style="height: 300px;"></div>
            </div>
        </div>

        <!-- Chart 16: Actividad por Usuario (Bar) -->
        <div class="col-xl-4 col-lg-12 mb-4">
            <div class="chart-container">
                <div class="chart-header">
                    <h5><i class="fas fa-users me-2" style="color: #667eea;"></i>Usuarios Activos</h5>
                    <span class="badge bg-light text-dark">TOP 10</span>
                </div>
                <div id="chartAuditoriaUsuarios" style="height: 300px;"></div>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="row">
        <!-- Table 1: Top 10 Sedes -->
        <div class="col-xl-6 mb-4">
            <div class="table-modern">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px;">
                    <h5 class="mb-0 text-white"><i class="fas fa-building me-2"></i>Top 10 Sedes</h5>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Sede</th>
                                <th>Total</th>
                                <th>Operativos</th>
                                <th>Reparación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_sedes as $index => $sede): ?>
                            <tr>
                                <td><span class="badge bg-primary"><?php echo $index + 1; ?></span></td>
                                <td><strong><?php echo htmlspecialchars($sede['nombre']); ?></strong></td>
                                <td><?php echo $sede['cantidad']; ?></td>
                                <td><span class="badge badge-status" style="background: rgba(40, 167, 69, 0.1); color: #28a745;">
                                    <?php echo $sede['operativos']; ?>
                                </span></td>
                                <td><span class="badge badge-status" style="background: rgba(220, 53, 69, 0.1); color: #dc3545;">
                                    <?php echo $sede['en_reparacion']; ?>
                                </span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Table 2: Equipos sin Mantenimiento -->
        <div class="col-xl-6 mb-4">
            <div class="table-modern">
                <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); padding: 20px;">
                    <h5 class="mb-0 text-white"><i class="fas fa-exclamation-circle me-2"></i>Equipos sin Mantenimiento</h5>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Sede</th>
                                <th>Último Mant.</th>
                                <th>Días</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($equipos_sin_mantenimiento as $equipo): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($equipo['codigo']); ?></strong></td>
                                <td><?php echo htmlspecialchars($equipo['sede']); ?></td>
                                <td><?php echo $equipo['ultimo_mant'] ? date('d/m/Y', strtotime($equipo['ultimo_mant'])) : 'Sin registros'; ?></td>
                                <td><span class="badge bg-danger"><?php echo $equipo['dias'] ?? 'N/A'; ?> días</span></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/views/equipos/index.php?id=<?php echo $equipo['id']; ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-tools"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ApexCharts Library -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
// ============================================
// DARK MODE DETECTION AND CONFIGURATION
// ============================================

// Función para detectar si está en modo oscuro
function isDarkMode() {
    return document.body.classList.contains('dark-mode');
}

// Colores según el tema
function getThemeColors() {
    if (isDarkMode()) {
        return {
            textColor: '#9ca3af',
            gridColor: '#3a4158',
            tooltipBg: '#252b3d',
            tooltipBorder: '#3a4158'
        };
    } else {
        return {
            textColor: '#52526C',
            gridColor: '#f1f1f1',
            tooltipBg: 'rgba(0,0,0,0.8)',
            tooltipBorder: '#ddd'
        };
    }
}

// Cuba Template Color Configuration
const primaryColor = '#7366FF';
const secondaryColor = '#764ba2';
const successColor = '#28a745';
const dangerColor = '#dc3545';
const warningColor = '#ffc107';
const infoColor = '#17a2b8';

// Almacenar referencias a todos los gráficos
let allCharts = [];

// Función para actualizar todos los gráficos al cambiar el tema
function updateChartsTheme() {
    const theme = getThemeColors();
    
    allCharts.forEach(chart => {
        if (chart && chart.updateOptions) {
            chart.updateOptions({
                chart: {
                    foreColor: theme.textColor
                },
                grid: {
                    borderColor: theme.gridColor
                },
                xaxis: {
                    labels: {
                        style: {
                            colors: theme.textColor
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: theme.textColor
                        }
                    }
                },
                legend: {
                    labels: {
                        colors: theme.textColor
                    }
                },
                tooltip: {
                    theme: isDarkMode() ? 'dark' : 'light'
                }
            }, false, true);
        }
    });
}

// Observador para detectar cambios en el tema
const themeObserver = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        if (mutation.attributeName === 'class') {
            updateChartsTheme();
        }
    });
});

// Iniciar observación del body
themeObserver.observe(document.body, {
    attributes: true,
    attributeFilter: ['class']
});

// ========================================
// CHART 1: Equipos por Estado (Donut)
// ========================================
var optionsEstados = {
    series: <?php echo json_encode(array_column($equipos_por_estado, 'cantidad')); ?>,
    chart: {
        type: 'donut',
        height: 300,
        fontFamily: 'Rubik, sans-serif',
        toolbar: { show: false }
    },
    labels: <?php echo json_encode(array_column($equipos_por_estado, 'estado')); ?>,
    colors: [successColor, warningColor, dangerColor, infoColor],
    plotOptions: {
        pie: {
            donut: {
                size: '65%',
                labels: {
                    show: true,
                    name: {
                        show: true,
                        fontSize: '14px',
                        fontWeight: 500,
                        color: '#52526C'
                    },
                    value: {
                        show: true,
                        fontSize: '22px',
                        fontWeight: 600,
                        color: '#2F2F3B',
                        formatter: function (val) { return val }
                    },
                    total: {
                        show: true,
                        label: 'Total',
                        fontSize: '14px',
                        fontWeight: 400,
                        color: '#52526C',
                        formatter: function (w) {
                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                        }
                    }
                }
            }
        }
    },
    legend: {
        position: 'bottom',
        fontSize: '13px',
        markers: { width: 10, height: 10, radius: 12 },
        itemMargin: { horizontal: 10, vertical: 5 }
    },
    dataLabels: { enabled: false },
    responsive: [{
        breakpoint: 480,
        options: {
            chart: { height: 280 },
            legend: { position: 'bottom' }
        }
    }],
    chart: {
        type: 'donut',
        height: 300,
        fontFamily: 'Rubik, sans-serif',
        toolbar: { show: false },
        foreColor: getThemeColors().textColor
    },
    tooltip: {
        theme: isDarkMode() ? 'dark' : 'light'
    }
};
var chartEstados = new ApexCharts(document.querySelector("#chartEstados"), optionsEstados);
chartEstados.render();
allCharts.push(chartEstados);

// ========================================
// CHART 2: Mantenimientos Mensuales (Stacked Bar)
// ========================================
var optionsMantenimientos = {
    series: [
        {
            name: 'Preventivo',
            data: <?php echo json_encode(array_column($mantenimientos_mes, 'preventivo')); ?>
        },
        {
            name: 'Correctivo',
            data: <?php echo json_encode(array_column($mantenimientos_mes, 'correctivo')); ?>
        }
    ],
    chart: {
        type: 'bar',
        height: 300,
        stacked: true,
        fontFamily: 'Rubik, sans-serif',
        toolbar: { show: false },
        dropShadow: {
            enabled: true,
            top: 8,
            left: 0,
            blur: 8,
            color: primaryColor,
            opacity: 0.1
        }
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '50%',
            borderRadius: 4
        }
    },
    colors: [primaryColor, '#AAAFCB'],
    dataLabels: { enabled: false },
    stroke: {
        width: 2,
        colors: ['#fff']
    },
    grid: {
        borderColor: '#f1f1f1',
        yaxis: { lines: { show: true } }
    },
    xaxis: {
        categories: <?php echo json_encode(array_column($mantenimientos_mes, 'mes_nombre')); ?>,
        labels: {
            style: { fontSize: '12px' }
        },
        axisBorder: { show: false },
        axisTicks: { show: false }
    },
    yaxis: {
        labels: {
            formatter: function (val) { return Math.round(val); },
            style: { fontSize: '12px', colors: '#52526C' }
        }
    },
    legend: {
        position: 'top',
        horizontalAlign: 'left',
        fontSize: '13px'
    },
    responsive: [{
        breakpoint: 480,
        options: {
            plotOptions: { bar: { columnWidth: '60%' } }
        }
    }],
    chart: {
        type: 'bar',
        height: 300,
        stacked: true,
        fontFamily: 'Rubik, sans-serif',
        toolbar: { show: false },
        dropShadow: {
            enabled: true,
            top: 8,
            left: 0,
            blur: 8,
            color: primaryColor,
            opacity: 0.1
        },
        foreColor: getThemeColors().textColor
    },
    grid: {
        borderColor: getThemeColors().gridColor
    },
    tooltip: {
        theme: isDarkMode() ? 'dark' : 'light'
    }
};
var chartMantenimientos = new ApexCharts(document.querySelector("#chartMantenimientos"), optionsMantenimientos);
chartMantenimientos.render();
allCharts.push(chartMantenimientos);

// ========================================
// CHART 3: Disponibilidad (Radial Bar)
// ========================================
var optionsDisponibilidad = {
    series: [<?php echo $tasa_disponibilidad; ?>],
    chart: {
        type: 'radialBar',
        height: 300,
        fontFamily: 'Rubik, sans-serif',
        offsetY: -10
    },
    plotOptions: {
        radialBar: {
            hollow: { size: '65%' },
            startAngle: -90,
            endAngle: 90,
            track: {
                background: '#d7e2e9',
                strokeWidth: '97%',
                margin: 5,
                dropShadow: {
                    enabled: true,
                    top: 2,
                    left: 0,
                    color: '#999',
                    opacity: 1,
                    blur: 2
                }
            },
            dataLabels: {
                name: {
                    show: true,
                    offsetY: -10,
                    fontSize: '14px',
                    color: '#52526C'
                },
                value: {
                    show: true,
                    offsetY: -50,
                    fontSize: '24px',
                    fontWeight: 600,
                    color: '#2F2F3B',
                    formatter: function(val) {
                        return val + '%';
                    }
                }
            }
        }
    },
    fill: {
        type: 'gradient',
        gradient: {
            shade: 'dark',
            type: 'horizontal',
            shadeIntensity: 0.5,
            gradientToColors: [primaryColor],
            inverseColors: true,
            opacityFrom: 1,
            opacityTo: 1,
            stops: [0, 100]
        }
    },
    labels: ['Disponibilidad']
};
// Renderizado autom�tico

// ========================================
// CHART 4: Top 5 Marcas (Horizontal Bar)
// ========================================
var optionsMarcas = {
    series: [{
        data: <?php echo json_encode(array_column($equipos_por_marca, 'cantidad')); ?>
    }],
    chart: {
        type: 'bar',
        height: 300,
        fontFamily: 'Rubik, sans-serif',
        toolbar: { show: false }
    },
    plotOptions: {
        bar: {
            borderRadius: 6,
            horizontal: true,
            barHeight: '70%',
            distributed: true
        }
    },
    colors: [primaryColor, successColor, warningColor, dangerColor, infoColor],
    dataLabels: {
        enabled: true,
        style: { fontSize: '12px', colors: ['#fff'] }
    },
    xaxis: {
        categories: <?php echo json_encode(array_column($equipos_por_marca, 'marca')); ?>,
        labels: { style: { fontSize: '12px' } }
    },
    legend: { show: false },
    grid: {
        borderColor: '#f1f1f1',
        xaxis: { lines: { show: true } }
    }
};
// Renderizado autom�tico

// ========================================
// CHART 5: Tipo de Demanda (Pie)
// ========================================
var optionsTipoDemanda = {
    series: <?php echo json_encode(array_column($mantenimientos_por_tipo, 'cantidad')); ?>,
    chart: {
        type: 'pie',
        height: 300,
        fontFamily: 'Rubik, sans-serif',
        toolbar: { show: false }
    },
    labels: <?php echo json_encode(array_column($mantenimientos_por_tipo, 'nombre')); ?>,
    colors: [primaryColor, successColor, warningColor, dangerColor, infoColor],
    legend: {
        position: 'bottom',
        fontSize: '13px'
    },
    dataLabels: {
        enabled: true,
        formatter: function (val) {
            return Math.round(val) + '%';
        }
    },
    responsive: [{
        breakpoint: 480,
        options: { chart: { height: 280 } }
    }]
};
// Renderizado autom�tico

// ========================================
// CHART 6: Equipos por Año (Area)
// ========================================
var optionsAnios = {
    series: [{
        name: 'Equipos Adquiridos',
        data: <?php echo json_encode(array_column($equipos_por_anio, 'cantidad')); ?>
    }],
    chart: {
        type: 'area',
        height: 300,
        fontFamily: 'Rubik, sans-serif',
        toolbar: { show: false },
        sparkline: { enabled: false }
    },
    stroke: {
        curve: 'smooth',
        width: 3
    },
    fill: {
        type: 'gradient',
        gradient: {
            shade: 'dark',
            gradientToColors: [primaryColor],
            shadeIntensity: 1,
            type: 'vertical',
            opacityFrom: 0.5,
            opacityTo: 0.1,
            stops: [0, 100]
        }
    },
    colors: [primaryColor],
    xaxis: {
        categories: <?php echo json_encode(array_column($equipos_por_anio, 'anio')); ?>,
        labels: { style: { fontSize: '12px' } }
    },
    yaxis: {
        labels: {
            formatter: function (val) { return Math.round(val); },
            style: { fontSize: '12px' }
        }
    },
    dataLabels: { enabled: false },
    grid: { borderColor: '#f1f1f1' }
};
// Renderizado autom�tico

// ========================================
// CHART 7: Sparkline Equipos (Line)
// ========================================
var optionsSparklineEquipos = {
    series: [{
        name: 'Nuevos Equipos',
        data: <?php echo json_encode(array_column($sparkline_equipos, 'cantidad')); ?>
    }],
    chart: {
        type: 'line',
        height: 150,
        fontFamily: 'Rubik, sans-serif',
        toolbar: { show: false },
        sparkline: { enabled: false }
    },
    stroke: {
        curve: 'smooth',
        width: 3
    },
    colors: [primaryColor],
    xaxis: {
        categories: <?php echo json_encode(array_map(function($d) { 
            return date('d/m', strtotime($d['fecha'])); 
        }, $sparkline_equipos)); ?>,
        labels: { style: { fontSize: '11px' } }
    },
    yaxis: {
        labels: {
            formatter: function (val) { return Math.round(val); },
            style: { fontSize: '11px' }
        }
    },
    grid: {
        borderColor: '#f1f1f1',
        strokeDashArray: 3
    },
    dataLabels: { enabled: false }
};
new ApexCharts(document.querySelector("#chartSparklineEquipos"), optionsSparklineEquipos).render();

// ========================================
// CHART 8: Sparkline Mantenimientos (Area)
// ========================================
var optionsSparklineMant = {
    series: [{
        name: 'Mantenimientos',
        data: <?php echo json_encode(array_column($sparkline_mantenimientos, 'cantidad')); ?>
    }],
    chart: {
        type: 'area',
        height: 150,
        fontFamily: 'Rubik, sans-serif',
        toolbar: { show: false },
        sparkline: { enabled: false }
    },
    stroke: {
        curve: 'smooth',
        width: 3
    },
    fill: {
        type: 'gradient',
        gradient: {
            shade: 'light',
            type: 'vertical',
            opacityFrom: 0.6,
            opacityTo: 0.1
        }
    },
    colors: [successColor],
    xaxis: {
        categories: <?php echo json_encode(array_map(function($d) { 
            return date('d/m', strtotime($d['fecha'])); 
        }, $sparkline_mantenimientos)); ?>,
        labels: { style: { fontSize: '11px' } }
    },
    yaxis: {
        labels: {
            formatter: function (val) { return Math.round(val); },
            style: { fontSize: '11px' }
        }
    },
    grid: {
        borderColor: '#f1f1f1',
        strokeDashArray: 3
    },
    dataLabels: { enabled: false }
};
new ApexCharts(document.querySelector("#chartSparklineMant"), optionsSparklineMant).render();

// ========================================
// CHART 9: Acciones de Auditoría (Donut)
// ========================================
var optionsAuditoriaTipo = {
    series: <?php echo json_encode(array_column($auditoria_por_tipo, 'cantidad')); ?>,
    chart: {
        type: 'donut',
        height: 300,
        fontFamily: 'Rubik, sans-serif',
        toolbar: { show: false }
    },
    labels: <?php echo json_encode(array_column($auditoria_por_tipo, 'accion')); ?>,
    colors: [successColor, infoColor, dangerColor],
    plotOptions: {
        pie: {
            donut: {
                size: '65%',
                labels: {
                    show: true,
                    name: { show: true, fontSize: '14px', fontWeight: 500, color: '#52526C' },
                    value: { show: true, fontSize: '22px', fontWeight: 600, color: '#2F2F3B' },
                    total: {
                        show: true,
                        label: 'Total',
                        fontSize: '14px',
                        fontWeight: 400,
                        color: '#52526C',
                        formatter: function (w) {
                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                        }
                    }
                }
            }
        }
    },
    legend: { position: 'bottom', fontSize: '13px' },
    dataLabels: { enabled: false }
};
new ApexCharts(document.querySelector("#chartAuditoriaTipo"), optionsAuditoriaTipo).render();

// ========================================
// CHART 10: Actividad por Tabla (Horizontal Bar)
// ========================================
var optionsActividadTabla = {
    series: [{
        data: <?php echo json_encode(array_column($actividad_por_tabla, 'cantidad')); ?>
    }],
    chart: {
        type: 'bar',
        height: 300,
        fontFamily: 'Rubik, sans-serif',
        toolbar: { show: false }
    },
    plotOptions: {
        bar: {
            borderRadius: 6,
            horizontal: true,
            barHeight: '70%',
            distributed: true
        }
    },
    colors: [primaryColor, successColor, warningColor, dangerColor, infoColor, '#f093fb', '#30cfd0', '#fa709a'],
    dataLabels: {
        enabled: true,
        style: { fontSize: '12px', colors: ['#fff'] }
    },
    xaxis: {
        categories: <?php echo json_encode(array_column($actividad_por_tabla, 'tabla')); ?>,
        labels: { style: { fontSize: '12px' } }
    },
    legend: { show: false },
    grid: { borderColor: '#f1f1f1', xaxis: { lines: { show: true } } }
};
new ApexCharts(document.querySelector("#chartActividadTabla"), optionsActividadTabla).render();

// ========================================
// CHART 11: Clasificación de Equipos (Donut)
// ========================================
var optionsClasificacion = {
    series: <?php echo json_encode(array_column($equipos_clasificacion, 'cantidad')); ?>,
    chart: {
        type: 'donut',
        height: 300,
        fontFamily: 'Rubik, sans-serif',
        toolbar: { show: false }
    },
    labels: <?php echo json_encode(array_map(function($c) { return ucfirst($c['clasificacion']); }, $equipos_clasificacion)); ?>,
    colors: [primaryColor, infoColor],
    plotOptions: {
        pie: {
            donut: {
                size: '65%',
                labels: {
                    show: true,
                    name: { show: true, fontSize: '14px', fontWeight: 500, color: '#52526C' },
                    value: { show: true, fontSize: '22px', fontWeight: 600, color: '#2F2F3B' },
                    total: {
                        show: true,
                        label: 'Total',
                        fontSize: '14px',
                        fontWeight: 400,
                        color: '#52526C',
                        formatter: function (w) {
                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                        }
                    }
                }
            }
        }
    },
    legend: { position: 'bottom', fontSize: '13px' },
    dataLabels: { enabled: false }
};
new ApexCharts(document.querySelector("#chartClasificacion"), optionsClasificacion).render();

// ========================================
// CHART 12: Distribución por Distrito (Polar Area)
// ========================================
var optionsDistritos = {
    series: <?php echo json_encode(array_column($equipos_por_distrito, 'cantidad')); ?>,
    chart: {
        type: 'polarArea',
        height: 320,
        fontFamily: 'Rubik, sans-serif',
        toolbar: { show: false }
    },
    labels: <?php echo json_encode(array_column($equipos_por_distrito, 'distrito')); ?>,
    colors: [primaryColor, successColor, warningColor, dangerColor, infoColor, '#f093fb', '#30cfd0', '#fa709a'],
    stroke: { colors: ['#fff'] },
    fill: { opacity: 0.8 },
    legend: { position: 'bottom', fontSize: '12px' },
    dataLabels: { enabled: true }
};
new ApexCharts(document.querySelector("#chartDistritos"), optionsDistritos).render();

// ========================================
// CHART 13: Garantías (Donut)
// ========================================
var optionsGarantias = {
    series: <?php echo json_encode(array_column($equipos_garantia, 'cantidad')); ?>,
    chart: {
        type: 'donut',
        height: 320,
        fontFamily: 'Rubik, sans-serif',
        toolbar: { show: false }
    },
    labels: <?php echo json_encode(array_column($equipos_garantia, 'estado')); ?>,
    colors: [successColor, '#cccccc'],
    plotOptions: {
        pie: {
            donut: {
                size: '70%',
                labels: {
                    show: true,
                    name: { show: true, fontSize: '13px' },
                    value: { show: true, fontSize: '20px', fontWeight: 600 },
                    total: {
                        show: true,
                        label: 'Total',
                        fontSize: '13px',
                        formatter: function (w) {
                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                        }
                    }
                }
            }
        }
    },
    legend: { position: 'bottom', fontSize: '12px' },
    dataLabels: { enabled: false }
};
new ApexCharts(document.querySelector("#chartGarantias"), optionsGarantias).render();

// ========================================
// CHART 14: Estabilizadores (Donut)
// ========================================
var optionsEstabilizador = {
    series: <?php echo json_encode(array_column($equipos_estabilizador, 'cantidad')); ?>,
    chart: {
        type: 'donut',
        height: 320,
        fontFamily: 'Rubik, sans-serif',
        toolbar: { show: false }
    },
    labels: <?php echo json_encode(array_column($equipos_estabilizador, 'estado')); ?>,
    colors: [infoColor, '#cccccc'],
    plotOptions: {
        pie: {
            donut: {
                size: '70%',
                labels: {
                    show: true,
                    name: { show: true, fontSize: '13px' },
                    value: { show: true, fontSize: '20px', fontWeight: 600 },
                    total: {
                        show: true,
                        label: 'Total',
                        fontSize: '13px',
                        formatter: function (w) {
                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                        }
                    }
                }
            }
        }
    },
    legend: { position: 'bottom', fontSize: '12px' },
    dataLabels: { enabled: false }
};
new ApexCharts(document.querySelector("#chartEstabilizador"), optionsEstabilizador).render();

// ========================================
// CHART 15: Timeline de Actividad del Sistema (Stacked Area)
// ========================================
var optionsTimelineActividad = {
    series: [
        {
            name: 'Inserciones',
            data: <?php echo json_encode(array_column($timeline_actividad, 'inserciones')); ?>
        },
        {
            name: 'Actualizaciones',
            data: <?php echo json_encode(array_column($timeline_actividad, 'actualizaciones')); ?>
        },
        {
            name: 'Eliminaciones',
            data: <?php echo json_encode(array_column($timeline_actividad, 'eliminaciones')); ?>
        }
    ],
    chart: {
        type: 'area',
        height: 300,
        stacked: true,
        fontFamily: 'Rubik, sans-serif',
        toolbar: { show: false }
    },
    colors: [successColor, infoColor, dangerColor],
    stroke: {
        curve: 'smooth',
        width: 2
    },
    fill: {
        type: 'gradient',
        gradient: {
            opacityFrom: 0.6,
            opacityTo: 0.1
        }
    },
    xaxis: {
        categories: <?php echo json_encode(array_map(function($d) { 
            return date('d/m', strtotime($d['fecha'])); 
        }, $timeline_actividad)); ?>,
        labels: { style: { fontSize: '11px' } }
    },
    yaxis: {
        labels: {
            formatter: function (val) { return Math.round(val); },
            style: { fontSize: '11px' }
        }
    },
    legend: {
        position: 'top',
        horizontalAlign: 'left',
        fontSize: '12px'
    },
    grid: { borderColor: '#f1f1f1', strokeDashArray: 3 },
    dataLabels: { enabled: false }
};
new ApexCharts(document.querySelector("#chartTimelineActividad"), optionsTimelineActividad).render();

// ========================================
// CHART 16: Actividad por Usuario (Bar)
// ========================================
var optionsAuditoriaUsuarios = {
    series: [{
        name: 'Acciones',
        data: <?php echo json_encode(array_column($auditoria_por_usuario, 'acciones')); ?>
    }],
    chart: {
        type: 'bar',
        height: 300,
        fontFamily: 'Rubik, sans-serif',
        toolbar: { show: false }
    },
    plotOptions: {
        bar: {
            borderRadius: 4,
            horizontal: false,
            columnWidth: '60%',
            distributed: true
        }
    },
    colors: [primaryColor, successColor, warningColor, dangerColor, infoColor, '#f093fb', '#30cfd0', '#fa709a', '#4facfe', '#00f2fe'],
    dataLabels: { enabled: false },
    xaxis: {
        categories: <?php echo json_encode(array_map(function($u) { 
            $nombre = explode(' ', $u['usuario']);
            return $nombre[0]; // Solo primer nombre para ahorrar espacio
        }, $auditoria_por_usuario)); ?>,
        labels: {
            style: { fontSize: '11px' },
            rotate: -45,
            rotateAlways: false
        }
    },
    yaxis: {
        labels: {
            formatter: function (val) { return Math.round(val); },
            style: { fontSize: '11px' }
        }
    },
    legend: { show: false },
    grid: { borderColor: '#f1f1f1' }
};
var chartAuditoriaUsuarios = new ApexCharts(document.querySelector("#chartAuditoriaUsuarios"), optionsAuditoriaUsuarios);
chartAuditoriaUsuarios.render();
allCharts.push(chartAuditoriaUsuarios);

// Registrar todos los demás gráficos en el array
// (Los gráficos 3-16 que faltan)
const chartConfigs = [
    { selector: "#chartDisponibilidad", options: optionsDisponibilidad },
    { selector: "#chartMarcas", options: optionsMarcas },
    { selector: "#chartTipoDemanda", options: optionsTipoDemanda },
    { selector: "#chartAnios", options: optionsAnios },
    { selector: "#chartSparklineEquipos", options: optionsSparklineEquipos },
    { selector: "#chartSparklineMant", options: optionsSparklineMant },
    { selector: "#chartAuditoriaTipo", options: optionsAuditoriaTipo },
    { selector: "#chartActividadTabla", options: optionsActividadTabla },
    { selector: "#chartClasificacion", options: optionsClasificacion },
    { selector: "#chartDistritos", options: optionsDistritos },
    { selector: "#chartGarantias", options: optionsGarantias },
    { selector: "#chartEstabilizador", options: optionsEstabilizador },
    { selector: "#chartTimelineActividad", options: optionsTimelineActividad }
];

// Renderizar y registrar todos los gráficos restantes
chartConfigs.forEach(config => {
    const element = document.querySelector(config.selector);
    if (element && config.options) {
        // Agregar tema al config
        config.options.chart = config.options.chart || {};
        config.options.chart.foreColor = getThemeColors().textColor;
        config.options.grid = config.options.grid || {};
        config.options.grid.borderColor = getThemeColors().gridColor;
        config.options.tooltip = config.options.tooltip || {};
        config.options.tooltip.theme = isDarkMode() ? 'dark' : 'light';
        
        const chart = new ApexCharts(element, config.options);
        chart.render();
        allCharts.push(chart);
    }
});

// Clock update
setInterval(function() {
    const now = new Date();
    const hours = now.getHours();
    const minutes = now.getMinutes();
    const ampm = hours >= 12 ? 'PM' : 'AM';
    const displayHours = hours % 12 || 12;
    const displayMinutes = minutes < 10 ? '0' + minutes : minutes;
    document.getElementById('clock-time').textContent = displayHours + ':' + displayMinutes + ' ' + ampm;
}, 1000);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

