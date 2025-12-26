<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/views/login.php');
    exit;
}

$db = getDB();

// Obtener nombre del usuario logueado
$nombre_usuario = $_SESSION['nombre_completo'] ?? $_SESSION['username'] ?? 'Usuario';

// CONSULTAS SQL
$sqlEstadisticas = "SELECT COUNT(*) as total, 
                    SUM(CASE WHEN e.nombre = 'Operativo' THEN 1 ELSE 0 END) as operativos,
                    SUM(CASE WHEN e.nombre IN ('Averiado', 'En Mantenimiento', 'Fuera de Servicio') THEN 1 ELSE 0 END) as reparacion 
                    FROM equipos eq
                    LEFT JOIN estados_equipo e ON eq.id_estado = e.id";
$estadisticas = $db->query($sqlEstadisticas)->fetch(PDO::FETCH_ASSOC);

$sqlMantenimientos = "SELECT MONTH(m.fecha_mantenimiento) as mes, MONTHNAME(m.fecha_mantenimiento) as mes_nombre,
                      COUNT(*) as total, 
                      SUM(CASE WHEN td.nombre LIKE '%Preventivo%' THEN 1 ELSE 0 END) as preventivo,
                      SUM(CASE WHEN td.nombre LIKE '%Correctivo%' THEN 1 ELSE 0 END) as correctivo
                      FROM mantenimientos m LEFT JOIN tipos_demanda td ON m.id_tipo_demanda = td.id
                      WHERE m.fecha_mantenimiento >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                      GROUP BY MONTH(m.fecha_mantenimiento), MONTHNAME(m.fecha_mantenimiento) ORDER BY mes ASC";
$mantenimientos_mes = $db->query($sqlMantenimientos)->fetchAll(PDO::FETCH_ASSOC);

$sqlTopSedes = "SELECT s.nombre, COUNT(e.id) as cantidad FROM equipos e INNER JOIN sedes s ON e.id_sede = s.id
                GROUP BY s.nombre ORDER BY cantidad DESC LIMIT 10";
$top_sedes = $db->query($sqlTopSedes)->fetchAll(PDO::FETCH_ASSOC);

$tasa_disponibilidad = $estadisticas['total'] > 0 ? round(($estadisticas['operativos'] / $estadisticas['total']) * 100, 1) : 0;

// Obtener equipos por estado para el gráfico
$sqlEstados = "SELECT e.nombre as estado, COUNT(eq.id) as cantidad
               FROM equipos eq LEFT JOIN estados_equipo e ON eq.id_estado = e.id
               GROUP BY e.nombre ORDER BY cantidad DESC";
$equipos_por_estado = $db->query($sqlEstados)->fetchAll(PDO::FETCH_ASSOC);

// Obtener equipos por marca (TOP 5) para el sales report
$sqlMarcas = "SELECT marca, COUNT(*) as cantidad FROM equipos
              GROUP BY marca ORDER BY cantidad DESC LIMIT 5";
$equipos_por_marca = $db->query($sqlMarcas)->fetchAll(PDO::FETCH_ASSOC);

// Obtener equipos por modelo (TOP 8)
$sqlModelos = "SELECT modelo, marca, COUNT(*) as cantidad FROM equipos
               GROUP BY modelo, marca ORDER BY cantidad DESC LIMIT 8";
$equipos_por_modelo = $db->query($sqlModelos)->fetchAll(PDO::FETCH_ASSOC);

// Obtener equipos en mantenimiento actualmente
$sqlEnMantenimiento = "SELECT eq.codigo_patrimonial, eq.marca, eq.modelo, eq.ubicacion_fisica,
                       e.nombre as estado, m.fecha_mantenimiento, m.descripcion, m.tecnico_responsable
                       FROM equipos eq
                       LEFT JOIN estados_equipo e ON eq.id_estado = e.id
                       LEFT JOIN mantenimientos m ON eq.id = m.id_equipo
                       WHERE e.nombre IN ('En Mantenimiento', 'Averiado')
                       ORDER BY m.fecha_mantenimiento DESC LIMIT 10";
$equipos_en_mantenimiento = $db->query($sqlEnMantenimiento)->fetchAll(PDO::FETCH_ASSOC);

// Obtener equipos por clasificación
$sqlClasificacion = "SELECT clasificacion, COUNT(*) as cantidad FROM equipos
                     GROUP BY clasificacion";
$equipos_por_clasificacion = $db->query($sqlClasificacion)->fetchAll(PDO::FETCH_ASSOC);

// Obtener equipos por año de adquisición
$sqlAnios = "SELECT anio_adquisicion as anio, COUNT(*) as cantidad FROM equipos
             WHERE anio_adquisicion IS NOT NULL
             GROUP BY anio_adquisicion ORDER BY anio_adquisicion DESC LIMIT 5";
$equipos_por_anio = $db->query($sqlAnios)->fetchAll(PDO::FETCH_ASSOC);

// Mantenimientos recientes (últimos 10)
$sqlMantRecientes = "SELECT m.fecha_mantenimiento, m.descripcion, m.tecnico_responsable,
                     eq.codigo_patrimonial, eq.marca, eq.modelo, td.nombre as tipo
                     FROM mantenimientos m
                     INNER JOIN equipos eq ON m.id_equipo = eq.id
                     LEFT JOIN tipos_demanda td ON m.id_tipo_demanda = td.id
                     ORDER BY m.fecha_mantenimiento DESC LIMIT 10";
$mantenimientos_recientes = $db->query($sqlMantRecientes)->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<!-- Cuba Template Assets -->
<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>/assets/css/cuba-style.css">
<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>/assets/css/cuba-color.css">

<style>
/* Custom Cuba Dashboard Styles */
.default-dashboard .profile-box { 
    background: #7366FF; 
    color: white; 
}
.default-dashboard .profile-box .greeting-user h2 { color: white; font-weight: 700; }
.default-dashboard .profile-box .greeting-user p { color: rgba(255,255,255,0.9); }
.default-dashboard .profile-box .btn-outline-white { border-color: white; color: white; }
.default-dashboard .profile-box .btn-outline-white:hover { background: white; color: #7366FF; }

.default-dashboard .widget-1 { border-radius: 10px; transition: all 0.3s; margin-bottom: 20px; }
.default-dashboard .widget-1:hover { transform: translateY(-5px); box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
.default-dashboard .widget-round { position: relative; width: 65px; height: 65px; display: flex; align-items: center; justify-content: center; }
.default-dashboard .widget-round.secondary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; }
.default-dashboard .widget-round.success { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 15px; }
.default-dashboard .widget-round.warning { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); border-radius: 15px; }
.default-dashboard .widget-round.primary { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); border-radius: 15px; }

/* Espaciado entre cards */
.default-dashboard .card { margin-bottom: 24px; }
.default-dashboard .row.widget-grid > [class*="col-"] { padding-left: 12px; padding-right: 12px; margin-bottom: 0; }
.default-dashboard .row.widget-grid { margin-left: -12px; margin-right: -12px; }

/* Cards mejor espaciado */
.default-dashboard .box-col-3, 
.default-dashboard .box-col-4, 
.default-dashboard .box-col-6, 
.default-dashboard .box-col-8 { 
    margin-bottom: 24px; 
}

/* Dark Mode */
body.dark-mode { background: #1a1d2e; color: #e4e4e7; }
body.dark-mode .content-wrapper { background: #1a1d2e; }
body.dark-mode .page-header { background: #1a1d2e; }

/* Cards en dark mode */
body.dark-mode .card { 
    background: #252b3d !important; 
    color: #e4e4e7 !important; 
    box-shadow: 0 0 20px rgba(0,0,0,0.3); 
    border: 1px solid #3a4158; 
}

/* Card Headers en dark mode */
body.dark-mode .card-header { 
    background: #252b3d !important; 
    border-bottom-color: #3a4158 !important; 
}
body.dark-mode .card-header h5, 
body.dark-mode .card-header h6,
body.dark-mode .card-header h4 { 
    color: #e4e4e7 !important; 
}
body.dark-mode .header-top h5,
body.dark-mode .header-top h6 { 
    color: #e4e4e7 !important; 
}
body.dark-mode .card-body h5,
body.dark-mode .card-body h6,
body.dark-mode .card-body h4 { 
    color: #e4e4e7 !important; 
}

/* Textos en dark mode */
body.dark-mode .text-muted, 
body.dark-mode .f-light { 
    color: #9ca3af !important; 
}
body.dark-mode .f-w-500, 
body.dark-mode .f-w-600 { 
    color: #e4e4e7 !important; 
}
body.dark-mode h1, 
body.dark-mode h2, 
body.dark-mode h3, 
body.dark-mode h4, 
body.dark-mode h5, 
body.dark-mode h6 {
    color: #e4e4e7 !important;
}

/* ApexCharts en dark mode */
body.dark-mode .apexcharts-text { fill: #9ca3af !important; }
body.dark-mode .apexcharts-title-text { fill: #e4e4e7 !important; }
body.dark-mode .apexcharts-gridline { stroke: #3a4158 !important; }
body.dark-mode .apexcharts-tooltip { 
    background: #252b3d !important; 
    border-color: #3a4158 !important; 
    color: #e4e4e7 !important; 
}
body.dark-mode .apexcharts-tooltip-title { 
    background: #1a1d2e !important; 
    border-color: #3a4158 !important; 
    color: #e4e4e7 !important; 
}
body.dark-mode table thead th { color: #e4e4e7 !important; border-color: #3a4158 !important; }
body.dark-mode table tbody tr { border-bottom-color: #3a4158; }
body.dark-mode table tbody tr:hover { background: #2d3548; }
body.dark-mode table tbody td { color: #e4e4e7 !important; }
body.dark-mode .profile-box { background: #7366FF !important; }
</style>

<div class="container-fluid default-dashboard">
    <div class="row widget-grid">
        <!-- Profile Box -->
        <div class="col-xxl-4 col-sm-6 box-col-6"> 
            <div class="card profile-box">
                <div class="card-body">
                    <div class="d-flex media-wrapper justify-content-between">
                        <div class="flex-grow-1"> 
                            <div class="greeting-user">
                                <h2 class="f-w-600">¡Bienvenido <?php echo htmlspecialchars($nombre_usuario); ?>!</h2>
                                <p>Aquí está lo que sucede en tu sistema hoy</p>
                                <div class="whatsnew-btn">
                                    <a class="btn btn-outline-primary" href="<?php echo BASE_URL; ?>/views/equipos/index.php">
                                        <i class="fas fa-plus me-2"></i>Agregar Equipo
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="badge f-12 p-2" id="txt"><?php echo date('h:i A'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget Cards Revenue, Customers -->
        <div class="col-xxl-auto col-xl-3 col-sm-6 box-col-3"> 
            <div class="row"> 
                <div class="col-xl-12"> 
                    <div class="card widget-1">
                        <div class="card-body">
                            <div class="widget-content">
                                <div class="widget-round secondary">
                                    <div class="bg-round">
                                        <i class="fas fa-print text-white" style="font-size: 24px;"></i>
                                    </div>
                                </div>
                                <div> 
                                    <h4><span class="counter"><?php echo $estadisticas['total']; ?></span></h4>
                                    <span class="f-light">Total Equipos</span>
                                </div>
                            </div>
                            <div class="font-success f-w-500">
                                <i class="fa fa-check-circle me-1"></i>
                                <span class="txt-success">Sistema activo</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12"> 
                    <div class="card widget-1">
                        <div class="card-body">
                            <div class="widget-content">
                                <div class="widget-round success">
                                    <div class="bg-round">
                                        <i class="fas fa-check text-white" style="font-size: 24px;"></i>
                                    </div>
                                </div>
                                <div> 
                                    <h4><span class="counter"><?php echo $estadisticas['operativos']; ?></span>+</h4>
                                    <span class="f-light">Operativos</span>
                                </div>
                            </div>
                            <div class="font-success f-w-500">
                                <i class="fa fa-trending-up me-1"></i>
                                <span class="txt-success"><?php echo $tasa_disponibilidad; ?>%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget Cards Profit, Invoices -->
        <div class="col-xxl-auto col-xl-3 col-sm-6 box-col-3"> 
            <div class="row"> 
                <div class="col-xl-12"> 
                    <div class="card widget-1">
                        <div class="card-body"> 
                            <div class="widget-content">
                                <div class="widget-round warning">
                                    <div class="bg-round">
                                        <i class="fas fa-tools text-white" style="font-size: 24px;"></i>
                                    </div>
                                </div>
                                <div> 
                                    <h4><span class="counter"><?php echo $estadisticas['reparacion']; ?></span></h4>
                                    <span class="f-light">Requieren Atención</span>
                                </div>
                            </div>
                            <div class="font-danger f-w-500">
                                <i class="fa fa-exclamation-triangle me-1"></i>
                                <span class="txt-danger"><?php echo $estadisticas['reparacion']; ?> equipos</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12"> 
                    <div class="card widget-1">
                        <div class="card-body"> 
                            <div class="widget-content">
                                <div class="widget-round primary">
                                    <div class="bg-round">
                                        <i class="fas fa-calendar text-white" style="font-size: 24px;"></i>
                                    </div>
                                </div>
                                <div> 
                                    <h4 class="counter"><?php echo count($mantenimientos_mes); ?></h4>
                                    <span class="f-light">Meses Activos</span>
                                </div>
                            </div>
                            <div class="font-success f-w-500">
                                <i class="fa fa-chart-line me-1"></i>
                                <span class="txt-success">Últimos 12 meses</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visitor Chart (Earnings Monthly) -->
        <div class="col-xxl-auto col-xl-4 col-sm-6 box-col-4">
            <div class="card">
                <div class="card-header card-no-border pb-2">
                    <div class="header-top"> 
                        <h5>Mantenimientos Mensuales</h5>
                        <div class="card-header-right-icon">
                            <div class="dropdown icon-dropdown">
                                <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="icon-more-alt"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="#">Hoy</a>
                                    <a class="dropdown-item" href="#">Esta Semana</a>
                                    <a class="dropdown-item" href="#">Este Mes</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body visitor-chart pt-0">
                    <div class="common-flex">
                        <h6><span class="counter"><?php echo array_sum(array_column($mantenimientos_mes, 'total')); ?></span></h6>
                        <div class="d-flex"> 
                            <p>(<span class="txt-success f-w-500 me-1">Últimos 12 meses</span>)</p>
                        </div>
                    </div>
                    <div id="visitor_chart"></div>
                </div>
            </div>
        </div>

        <!-- Top Customers Table -->
        <div class="col-xxl-4 col-sm-6 box-col-6">
            <div class="card"> 
                <div class="card-header card-no-border">
                    <div class="header-top"> 
                        <h5>Top 10 Sedes</h5>
                    </div>
                </div>
                <div class="card-body main-customer-table px-0 pt-0">
                    <div class="recent-table table-responsive custom-scrollbar">
                        <table class="table" id="top-customer">
                            <thead> 
                                <tr>
                                    <th>#</th>
                                    <th>Sede</th>
                                    <th>Equipos</th>
                                </tr>
                            </thead>
                            <tbody> 
                                <?php foreach ($top_sedes as $index => $sede): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <div class="d-flex">
                                            <div class="img-content-box">
                                                <span class="f-w-500"><?php echo htmlspecialchars($sede['nombre']); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="f-w-500 txt-success"><?php echo $sede['cantidad']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Statistical Overview -->
        <div class="col-xxl-5 col-lg-6 box-col-6">
            <div class="card">
                <div class="card-header card-no-border">
                    <div class="header-top"> 
                        <h5>Resumen Estadístico de Mantenimientos</h5>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="row m-0 overall-card">
                        <div class="col-12 p-0">
                            <div class="chart-right">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="card-body p-0 statistical-card">
                                            <div class="row mb-3">
                                                <div class="col-6 text-center">
                                                    <h3 class="counter mb-0 txt-success"><?php echo array_sum(array_column($mantenimientos_mes, 'preventivo')); ?></h3>
                                                    <span class="f-light">Preventivos</span>
                                                </div>
                                                <div class="col-6 text-center">
                                                    <h3 class="counter mb-0 txt-warning"><?php echo array_sum(array_column($mantenimientos_mes, 'correctivo')); ?></h3>
                                                    <span class="f-light">Correctivos</span>
                                                </div>
                                            </div>
                                            <div class="current-sale-container" style="min-height: 312px;">
                                                <div id="chart-currently"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Target -->
        <div class="col-xl-3 col-md-6 box-col-3">
            <div class="card monthly-header"> 
                <div class="card-header card-no-border">
                    <div class="header-top"> 
                        <h5>Disponibilidad</h5>
                    </div>
                </div>
                <div class="card-body"> 
                    <div class="monthly-target">
                        <div class="position-relative" id="monthly_target"></div>
                    </div>
                    <div class="target-content">
                        <p>Sistema operando al <?php echo $tasa_disponibilidad; ?>% de su capacidad. 
                        <?php echo $estadisticas['operativos']; ?> equipos operativos de <?php echo $estadisticas['total']; ?> totales.</p>
                        <div class="common-box">
                            <ul class="common-flex">
                                <li>
                                    <h6>Operativos</h6>
                                    <span class="common-space badge badge-light-success">
                                        <i class="me-1 fa fa-check"></i><?php echo $estadisticas['operativos']; ?>
                                    </span>
                                </li>
                                <li>
                                    <h6>En Reparación</h6>
                                    <span class="common-space badge badge-light-danger">
                                        <i class="me-1 fa fa-tools"></i><?php echo $estadisticas['reparacion']; ?>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sale Report Chart (Equipos por Estado y Marca) -->
        <div class="col-xxl-8 col-xl-7 col-md-12 box-col-8">
            <div class="card height-equal">
                <div class="card-header card-no-border">
                    <div class="header-top">
                        <h5>Distribución de Equipos</h5>
                        <div class="dropdown icon-dropdown">
                            <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="icon-more-alt"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#">Por Estado</a>
                                <a class="dropdown-item" href="#">Por Marca</a>
                                <a class="dropdown-item" href="#">Por Sede</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="row sale-report">
                        <div class="col-xl-6">
                            <h6 class="f-w-600 mb-3">Por Estado</h6>
                            <ul class="d-flex flex-column">
                                <?php 
                                $colors = ['txt-primary', 'txt-success', 'txt-danger', 'txt-warning', 'txt-info'];
                                foreach ($equipos_por_estado as $index => $estado): 
                                ?>
                                <li class="mb-2">
                                    <span class="<?php echo $colors[$index % 5]; ?> f-w-500"><?php echo htmlspecialchars($estado['estado']); ?>:</span>
                                    <span class="ms-2 counter"><?php echo $estado['cantidad']; ?></span> equipos
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="col-xl-6">
                            <h6 class="f-w-600 mb-3">Top 5 Marcas</h6>
                            <ul class="d-flex flex-column">
                                <?php foreach ($equipos_por_marca as $index => $marca): ?>
                                <li class="mb-2">
                                    <span class="<?php echo $colors[$index]; ?> f-w-500"><?php echo htmlspecialchars($marca['marca']); ?>:</span>
                                    <span class="ms-2 counter"><?php echo $marca['cantidad']; ?></span> equipos
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <div id="sale_report_chart" class="mt-3"></div>
                </div>
            </div>
        </div>

        <!-- Equipos por Modelo (Donut Chart) -->
        <div class="col-xxl-4 col-xl-5 col-md-6 box-col-4">
            <div class="card height-equal">
                <div class="card-header card-no-border">
                    <div class="header-top">
                        <h5>Equipos por Modelo</h5>
                        <div class="dropdown icon-dropdown">
                            <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="icon-more-alt"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#">Top 8</a>
                                <a class="dropdown-item" href="#">Ver todos</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div id="modelos_chart"></div>
                    <div class="mt-3">
                        <?php foreach (array_slice($equipos_por_modelo, 0, 5) as $index => $modelo): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="f-light"><?php echo htmlspecialchars($modelo['marca'] . ' ' . $modelo['modelo']); ?></span>
                            <span class="badge badge-light-primary"><?php echo $modelo['cantidad']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla: Equipos en Mantenimiento -->
        <div class="col-xxl-8 col-xl-12 box-col-8">
            <div class="card">
                <div class="card-header card-no-border">
                    <div class="header-top">
                        <h5><i class="fa fa-tools me-2 txt-warning"></i>Equipos en Mantenimiento</h5>
                        <a href="<?php echo BASE_URL; ?>/views/equipos/" class="btn btn-sm btn-outline-primary">
                            Ver Todos
                        </a>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive custom-scrollbar">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Equipo</th>
                                    <th>Ubicación</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Técnico</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($equipos_en_mantenimiento)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        <i class="fa fa-check-circle me-2"></i>No hay equipos en mantenimiento
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($equipos_en_mantenimiento as $equipo): ?>
                                    <tr>
                                        <td><span class="badge badge-light-secondary"><?php echo htmlspecialchars($equipo['codigo_patrimonial']); ?></span></td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="f-w-500"><?php echo htmlspecialchars($equipo['marca']); ?></span>
                                                <span class="f-light f-12"><?php echo htmlspecialchars($equipo['modelo']); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($equipo['ubicacion_fisica'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php if ($equipo['estado'] === 'Averiado'): ?>
                                                <span class="badge badge-light-danger">Averiado</span>
                                            <?php else: ?>
                                                <span class="badge badge-light-warning">En Mantenimiento</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $equipo['fecha_mantenimiento'] ? date('d/m/Y', strtotime($equipo['fecha_mantenimiento'])) : 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars($equipo['tecnico_responsable'] ?? 'No asignado'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clasificación y Años (Bar Charts) -->
        <div class="col-xxl-4 col-xl-6 box-col-4">
            <div class="card">
                <div class="card-header card-no-border">
                    <div class="header-top">
                        <h5>Por Clasificación</h5>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div id="clasificacion_chart"></div>
                </div>
            </div>
        </div>

        <!-- Mantenimientos Recientes -->
        <div class="col-xxl-8 col-xl-12 box-col-8">
            <div class="card">
                <div class="card-header card-no-border">
                    <div class="header-top">
                        <h5><i class="fa fa-history me-2 txt-primary"></i>Mantenimientos Recientes</h5>
                        <a href="<?php echo BASE_URL; ?>/views/equipos/" class="btn btn-sm btn-outline-primary">
                            Ver Historial Completo
                        </a>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive custom-scrollbar">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Equipo</th>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Técnico</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mantenimientos_recientes as $mant): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($mant['fecha_mantenimiento'])); ?></td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="f-w-500"><?php echo htmlspecialchars($mant['codigo_patrimonial']); ?></span>
                                            <span class="f-light f-12"><?php echo htmlspecialchars($mant['marca'] . ' ' . $mant['modelo']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (strpos(strtolower($mant['tipo']), 'preventivo') !== false): ?>
                                            <span class="badge badge-light-success">
                                                <i class="fa fa-check-circle me-1"></i>Preventivo
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-light-warning">
                                                <i class="fa fa-wrench me-1"></i>Correctivo
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="f-light"><?php echo htmlspecialchars(substr($mant['descripcion'], 0, 50)) . (strlen($mant['descripcion']) > 50 ? '...' : ''); ?></span></td>
                                    <td><?php echo htmlspecialchars($mant['tecnico_responsable']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Equipos por Año -->
        <div class="col-xxl-4 col-xl-6 box-col-4">
            <div class="card">
                <div class="card-header card-no-border">
                    <div class="header-top">
                        <h5>Equipos por Año de Adquisición</h5>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div id="anios_chart"></div>
                    <div class="mt-3">
                        <?php foreach ($equipos_por_anio as $anio): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="f-light">Año <?php echo $anio['anio']; ?></span>
                            <span class="badge badge-light-info"><?php echo $anio['cantidad']; ?> equipos</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
(function () {
  // Visitor chart (Mantenimientos Mensuales)
  var visitorUser = {
    series: [{
        name: "Mantenimientos",
        data: <?php echo json_encode(array_column($mantenimientos_mes, 'total')); ?>,
      }],
    chart: {
      height: 160,
      type: "line",
      stacked: true,
      offsetY: -10,
      toolbar: { show: false },
    },
    colors: ["#7366FF"],
    stroke: { width: 3, curve: "smooth" },
    xaxis: {
      type: "category",
      categories: <?php echo json_encode(array_column($mantenimientos_mes, 'mes_nombre')); ?>,
      labels: {
        style: { fontFamily: "Rubik, sans-serif", fontWeight: 500, colors: "#8D8D8D" }
      },
      axisTicks: { show: false },
      axisBorder: { show: false },
    },
    grid: {
      show: true,
      borderColor: "var(--chart-dashed-border)",
      strokeDashArray: 3,
      position: "back",
      xaxis: { lines: { show: true } },
      yaxis: { lines: { show: false } },
    },
    fill: {
      type: "gradient",
      gradient: {
        shade: "dark",
        gradientToColors: ["#7366FF"],
        shadeIntensity: 1,
        type: "horizontal",
        opacityFrom: 1,
        opacityTo: 1,
        colorStops: [
          { offset: 0, color: "#48A3D7", opacity: 1 },
          { offset: 100, color: "#7366FF", opacity: 1 },
        ],
      },
    },
    yaxis: { labels: { show: false } },
    responsive: [
      { breakpoint: 1200, options: { chart: { height: 130, offsetY: -20 } } },
      { breakpoint: 576, options: { chart: { height: 150, offsetY: -20 } } },
    ],
  };
  var visitorChart = new ApexCharts(document.querySelector("#visitor_chart"), visitorUser);
  visitorChart.render();

  // Currently sale (Stacked Bar)
  var chartCurrent = {
    series: [
      {
        name: "Preventivo",
        data: <?php echo json_encode(array_column($mantenimientos_mes, 'preventivo')); ?>,
      },
      {
        name: "Correctivo",
        data: <?php echo json_encode(array_column($mantenimientos_mes, 'correctivo')); ?>,
      },
    ],
    chart: {
      type: "bar",
      height: 312,
      stacked: true,
      toolbar: { show: false },
      dropShadow: {
        enabled: true,
        top: 8,
        left: 0,
        blur: 8,
        color: "#7064F5",
        opacity: 0.1,
      },
    },
    plotOptions: {
      bar: { horizontal: false, columnWidth: "20%", borderRadius: 0 },
    },
    grid: {
      borderColor: "var(--chart-border)",
      yaxis: { lines: { show: true } },
    },
    dataLabels: { enabled: false },
    stroke: { width: 2, dashArray: 0, lineCap: "butt", colors: "#fff" },
    fill: { opacity: 1 },
    legend: { show: false },
    colors: ["#7366FF", "#AAAFCB"],
    xaxis: {
      categories: <?php echo json_encode(array_column($mantenimientos_mes, 'mes_nombre')); ?>,
      labels: { style: { fontFamily: "Rubik, sans-serif" } },
      axisBorder: { show: false },
      axisTicks: { show: false },
    },
    yaxis: {
      labels: {
        formatter: function (value) { return value + ""; },
        style: { fontFamily: "Rubik, sans-serif", fontWeight: 400, colors: "#52526C", fontSize: 12 },
      },
    },
  };
  var currentChart = new ApexCharts(document.querySelector("#chart-currently"), chartCurrent);
  currentChart.render();

  // Monthly Target (Radial Bar)
  var monthlyTarget = {
    series: [<?php echo $tasa_disponibilidad; ?>],
    chart: {
      type: "radialBar",
      height: 320,
      offsetY: -20,
      sparkline: { enabled: true },
    },
    plotOptions: {
      radialBar: {
        hollow: { size: "65%" },
        startAngle: -90,
        endAngle: 90,
        track: {
          background: "#d7e2e9",
          strokeWidth: "97%",
          margin: 5,
          dropShadow: {
            enabled: true,
            top: 2,
            left: 0,
            color: "#999",
            opacity: 1,
            blur: 2,
          },
        },
        dataLabels: {
          name: { show: true, offsetY: -10 },
          value: {
            show: true,
            offsetY: -50,
            fontSize: "18px",
            fontWeight: "600",
            color: "#2F2F3B",
          },
          total: {
            show: true,
            label: "Disponibilidad",
            color: "#7366FF",
            fontSize: "14px",
            fontFamily: "Rubik, sans-serif",
            fontWeight: 400,
            formatter: function () { return "<?php echo $tasa_disponibilidad; ?>%"; },
          },
        },
      },
    },
    grid: { padding: { top: -10 } },
    fill: {
      type: "gradient",
      gradient: {
        shade: "dark",
        shadeIntensity: 0.4,
        inverseColors: false,
        opacityFrom: 1,
        opacityTo: 1,
        stops: [100],
        colorStops: [{ offset: 0, color: "#7366FF", opacity: 1 }],
      },
    },
    labels: ["Disponibilidad"],
  };
  var monthlyChart = new ApexCharts(document.querySelector("#monthly_target"), monthlyTarget);
  monthlyChart.render();

  // Sale Report Chart (Mixed Column + Line)
  var saleReportOptions = {
    series: [
      {
        name: "Por Estado",
        type: "column",
        data: <?php echo json_encode(array_column($equipos_por_estado, 'cantidad')); ?>,
      },
      {
        name: "Por Marca (Top 5)",
        type: "line",
        data: <?php echo json_encode(array_column($equipos_por_marca, 'cantidad')); ?>,
      },
    ],
    chart: {
      height: 300,
      type: "line",
      stacked: false,
      toolbar: { show: false },
      fontFamily: "Rubik, sans-serif",
    },
    colors: ["#7366FF", "#FF6C6C"],
    stroke: {
      width: [0, 3],
      curve: "smooth",
    },
    plotOptions: {
      bar: {
        columnWidth: "50%",
        borderRadius: 4,
      },
    },
    fill: {
      opacity: [0.85, 1],
      gradient: {
        inverseColors: false,
        shade: "light",
        type: "vertical",
        opacityFrom: 0.85,
        opacityTo: 0.55,
        stops: [0, 100, 100, 100],
      },
    },
    labels: <?php echo json_encode(array_merge(
      array_column($equipos_por_estado, 'estado'),
      array_pad([], count($equipos_por_marca) - count($equipos_por_estado), '')
    )); ?>,
    markers: {
      size: 5,
      colors: ["#FF6C6C"],
      strokeColors: "#fff",
      strokeWidth: 2,
      hover: { size: 7 },
    },
    xaxis: {
      labels: { style: { fontFamily: "Rubik, sans-serif" } },
      axisBorder: { show: false },
      axisTicks: { show: false },
    },
    yaxis: {
      title: { text: "Cantidad de Equipos" },
      labels: {
        style: { fontFamily: "Rubik, sans-serif", fontWeight: 400, colors: "#52526C", fontSize: 12 },
      },
    },
    tooltip: {
      shared: true,
      intersect: false,
      y: {
        formatter: function (y) {
          if (typeof y !== "undefined") {
            return y.toFixed(0) + " equipos";
          }
          return y;
        },
      },
    },
    legend: {
      position: "top",
      horizontalAlign: "left",
      fontFamily: "Rubik, sans-serif",
    },
  };
  var saleReportChart = new ApexCharts(document.querySelector("#sale_report_chart"), saleReportOptions);
  saleReportChart.render();

  // Equipos por Modelo (Donut Chart)
  var modelosOptions = {
    series: <?php echo json_encode(array_column($equipos_por_modelo, 'cantidad')); ?>,
    chart: {
      type: "donut",
      height: 300,
      fontFamily: "Rubik, sans-serif",
    },
    labels: <?php echo json_encode(array_map(function($m) { 
      return substr($m['marca'] . ' ' . $m['modelo'], 0, 20); 
    }, $equipos_por_modelo)); ?>,
    colors: ["#7366FF", "#FF6C6C", "#FFA941", "#4099FF", "#2DCE89", "#F5365C", "#5E72E4", "#11CDEF"],
    plotOptions: {
      pie: {
        donut: {
          size: "65%",
          labels: {
            show: true,
            name: { show: true, fontSize: "14px", fontWeight: 600 },
            value: { show: true, fontSize: "22px", fontWeight: 700 },
            total: {
              show: true,
              label: "Total Equipos",
              fontSize: "14px",
              fontWeight: 600,
              formatter: function (w) {
                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
              },
            },
          },
        },
      },
    },
    dataLabels: { enabled: false },
    legend: {
      show: true,
      position: "bottom",
      fontFamily: "Rubik, sans-serif",
      fontSize: "12px",
    },
    tooltip: {
      y: {
        formatter: function (val) {
          return val + " equipos";
        },
      },
    },
  };
  var modelosChart = new ApexCharts(document.querySelector("#modelos_chart"), modelosOptions);
  modelosChart.render();

  // Equipos por Clasificación (Bar Chart)
  var clasificacionOptions = {
    series: [{
      name: "Cantidad",
      data: <?php echo json_encode(array_column($equipos_por_clasificacion, 'cantidad')); ?>,
    }],
    chart: {
      type: "bar",
      height: 250,
      toolbar: { show: false },
      fontFamily: "Rubik, sans-serif",
    },
    colors: ["#7366FF"],
    plotOptions: {
      bar: {
        horizontal: true,
        borderRadius: 6,
        dataLabels: { position: "top" },
      },
    },
    dataLabels: {
      enabled: true,
      offsetX: 30,
      style: {
        fontSize: "12px",
        fontWeight: 600,
        colors: ["#7366FF"],
      },
    },
    xaxis: {
      categories: <?php echo json_encode(array_map('ucfirst', array_column($equipos_por_clasificacion, 'clasificacion'))); ?>,
      labels: { style: { fontFamily: "Rubik, sans-serif" } },
    },
    yaxis: {
      labels: {
        style: { fontFamily: "Rubik, sans-serif", colors: "#52526C" },
      },
    },
    grid: {
      borderColor: "rgba(115, 102, 255, 0.1)",
      xaxis: { lines: { show: true } },
    },
    tooltip: {
      y: {
        formatter: function (val) {
          return val + " equipos";
        },
      },
    },
  };
  var clasificacionChart = new ApexCharts(document.querySelector("#clasificacion_chart"), clasificacionOptions);
  clasificacionChart.render();

  // Equipos por Año (Column Chart)
  var aniosOptions = {
    series: [{
      name: "Equipos Adquiridos",
      data: <?php echo json_encode(array_column($equipos_por_anio, 'cantidad')); ?>,
    }],
    chart: {
      type: "bar",
      height: 250,
      toolbar: { show: false },
      fontFamily: "Rubik, sans-serif",
    },
    colors: ["#4099FF"],
    plotOptions: {
      bar: {
        columnWidth: "60%",
        borderRadius: 6,
        distributed: true,
      },
    },
    dataLabels: {
      enabled: true,
      style: {
        fontSize: "12px",
        fontWeight: 600,
        colors: ["#fff"],
      },
    },
    xaxis: {
      categories: <?php echo json_encode(array_column($equipos_por_anio, 'anio')); ?>,
      labels: { 
        style: { 
          fontFamily: "Rubik, sans-serif",
          colors: "#52526C"
        } 
      },
    },
    yaxis: {
      labels: {
        style: { fontFamily: "Rubik, sans-serif", colors: "#52526C" },
      },
    },
    grid: {
      borderColor: "rgba(64, 153, 255, 0.1)",
      yaxis: { lines: { show: true } },
    },
    legend: { show: false },
    tooltip: {
      y: {
        formatter: function (val) {
          return val + " equipos";
        },
      },
    },
  };
  var aniosChart = new ApexCharts(document.querySelector("#anios_chart"), aniosOptions);
  aniosChart.render();
})();

// Clock
function startTime() {
  var today = new Date();
  var h = today.getHours();
  var m = today.getMinutes();
  var ampm = h >= 12 ? "PM" : "AM";
  h = h % 12;
  h = h ? h : 12;
  m = checkTime(m);
  document.getElementById("txt").innerHTML = h + ":" + m + " " + ampm;
  setTimeout(startTime, 500);
}
function checkTime(i) {
  if (i < 10) { i = "0" + i; }
  return i;
}
startTime();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
