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

$page_title = 'Reportes';

$database = new Database();
$db = $database->getConnection();

// Obtener datos para filtros
$stmt = $db->query("SELECT * FROM estados_equipo WHERE activo = 1 ORDER BY nombre");
$estados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->query("SELECT * FROM sedes WHERE activo = 1 ORDER BY nombre");
$sedes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->query("SELECT * FROM distritos_fiscales WHERE activo = 1 ORDER BY nombre");
$distritos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$extra_js = <<<'EOD'
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
$(document).ready(function() {
    // Cargar todos los reportes al inicio
    cargarReporteEstados();
    cargarReporteMantenimientos();
    cargarReporteSedes();
    cargarReporteSinMantenimiento();
});

function cargarReporteEstados() {
    $.ajax({
        url: BASE_URL + '/controllers/reportes.php',
        method: 'GET',
        data: { action: 'equiposPorEstado' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Tabla
                let html = '<table style="width: 100%; border-collapse: collapse;">';
                html += '<thead><tr style="border-bottom: 2px solid #000;">';
                html += '<th style="padding: 8px; text-align: left;">Estado</th>';
                html += '<th style="padding: 8px; text-align: right;">Cantidad</th>';
                html += '<th style="padding: 8px; text-align: right;">Porcentaje</th>';
                html += '</tr></thead><tbody>';
                
                let total = response.data.reduce((sum, item) => sum + parseInt(item.cantidad), 0);
                
                response.data.forEach(function(item) {
                    let porcentaje = ((item.cantidad / total) * 100).toFixed(1);
                    html += '<tr style="border-bottom: 1px solid #ccc;">';
                    html += `<td style="padding: 8px;">${item.estado}</td>`;
                    html += `<td style="padding: 8px; text-align: right;">${item.cantidad}</td>`;
                    html += `<td style="padding: 8px; text-align: right;">${porcentaje}%</td>`;
                    html += '</tr>';
                });
                
                html += '</tbody></table>';
                $('#tablaEstados').html(html);
                
                // Gráfico
                const ctx = document.getElementById('chartEstados').getContext('2d');
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: response.data.map(item => item.estado),
                        datasets: [{
                            data: response.data.map(item => item.cantidad),
                            backgroundColor: ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            }
        }
    });
}

function cargarReporteMantenimientos() {
    const fechaInicio = $('#filtroFechaInicio').val();
    const fechaFin = $('#filtroFechaFin').val();
    
    $.ajax({
        url: BASE_URL + '/controllers/reportes.php',
        method: 'GET',
        data: { 
            action: 'mantenimientosPorPeriodo',
            fecha_inicio: fechaInicio,
            fecha_fin: fechaFin
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Tabla
                let html = '<table style="width: 100%; border-collapse: collapse;">';
                html += '<thead><tr style="border-bottom: 2px solid #000;">';
                html += '<th style="padding: 8px; text-align: left;">Fecha</th>';
                html += '<th style="padding: 8px; text-align: left;">Equipo</th>';
                html += '<th style="padding: 8px; text-align: left;">Tipo Demanda</th>';
                html += '<th style="padding: 8px; text-align: left;">Técnico</th>';
                html += '</tr></thead><tbody>';
                
                response.data.forEach(function(item) {
                    html += '<tr style="border-bottom: 1px solid #ccc;">';
                    html += `<td style="padding: 8px;">${new Date(item.fecha_mantenimiento).toLocaleDateString('es-ES')}</td>`;
                    html += `<td style="padding: 8px;">${item.codigo_patrimonial}</td>`;
                    html += `<td style="padding: 8px;">${item.tipo_demanda}</td>`;
                    html += `<td style="padding: 8px;">${item.tecnico_responsable || '-'}</td>`;
                    html += '</tr>';
                });
                
                html += '</tbody></table>';
                $('#tablaMantenimientos').html(html);
                
                // Contar por tipo de demanda
                const conteo = {};
                response.data.forEach(item => {
                    conteo[item.tipo_demanda] = (conteo[item.tipo_demanda] || 0) + 1;
                });
                
                // Gráfico
                const ctx = document.getElementById('chartMantenimientos').getContext('2d');
                if (window.chartMantenimientos) {
                    window.chartMantenimientos.destroy();
                }
                window.chartMantenimientos = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(conteo),
                        datasets: [{
                            label: 'Cantidad',
                            data: Object.values(conteo),
                            backgroundColor: '#ff0000'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }
        }
    });
}

function cargarReporteSedes() {
    const sedeId = $('#filtroSede').val();
    
    $.ajax({
        url: BASE_URL + '/controllers/reportes.php',
        method: 'GET',
        data: { 
            action: 'equiposPorSede',
            id_sede: sedeId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Tabla
                let html = '<table style="width: 100%; border-collapse: collapse;">';
                html += '<thead><tr style="border-bottom: 2px solid #000;">';
                html += '<th style="padding: 8px; text-align: left;">Sede</th>';
                html += '<th style="padding: 8px; text-align: right;">Cantidad</th>';
                html += '</tr></thead><tbody>';
                
                response.data.forEach(function(item) {
                    html += '<tr style="border-bottom: 1px solid #ccc;">';
                    html += `<td style="padding: 8px;">${item.sede || 'Sin sede'}</td>`;
                    html += `<td style="padding: 8px; text-align: right;">${item.cantidad}</td>`;
                    html += '</tr>';
                });
                
                html += '</tbody></table>';
                $('#tablaSedes').html(html);
                
                // Gráfico ApexCharts
                if (window.chartSedesApex) {
                    window.chartSedesApex.destroy();
                }
                
                const options = {
                    series: [{
                        name: 'Equipos',
                        data: response.data.map(item => item.cantidad)
                    }],
                    chart: {
                        type: 'bar',
                        height: 400,
                        toolbar: {
                            show: true,
                            tools: {
                                download: true,
                                selection: false,
                                zoom: false,
                                zoomin: false,
                                zoomout: false,
                                pan: false,
                                reset: false
                            }
                        }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 8,
                            horizontal: true,
                            distributed: true,
                            barHeight: '70%',
                            dataLabels: {
                                position: 'top'
                            }
                        }
                    },
                    colors: ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#546E7A', '#26a69a', '#D10CE8', '#00D9E9', '#FD6A6A'],
                    dataLabels: {
                        enabled: true,
                        style: {
                            fontSize: '12px',
                            colors: ['#fff']
                        },
                        offsetX: 30
                    },
                    xaxis: {
                        categories: response.data.map(item => item.sede || 'Sin sede'),
                        title: {
                            text: 'Cantidad de Equipos'
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Sedes'
                        }
                    },
                    legend: {
                        show: false
                    },
                    tooltip: {
                        theme: 'dark',
                        y: {
                            formatter: function(val) {
                                return val + ' equipos';
                            }
                        }
                    },
                    grid: {
                        borderColor: '#f1f1f1'
                    }
                };
                
                window.chartSedesApex = new ApexCharts(document.querySelector("#chartSedes"), options);
                window.chartSedesApex.render();
            }
        }
    });
}

function cargarReporteSinMantenimiento() {
    const dias = $('#filtroDias').val() || 90;
    
    $.ajax({
        url: BASE_URL + '/controllers/reportes.php',
        method: 'GET',
        data: { 
            action: 'equiposSinMantenimiento',
            dias: dias
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let html = '<table style="width: 100%; border-collapse: collapse;">';
                html += '<thead><tr style="border-bottom: 2px solid #000;">';
                html += '<th style="padding: 8px; text-align: left;">Código</th>';
                html += '<th style="padding: 8px; text-align: left;">Equipo</th>';
                html += '<th style="padding: 8px; text-align: left;">Estado</th>';
                html += '<th style="padding: 8px; text-align: left;">Último Mantenimiento</th>';
                html += '</tr></thead><tbody>';
                
                response.data.forEach(function(item) {
                    html += '<tr style="border-bottom: 1px solid #ccc;">';
                    html += `<td style="padding: 8px;">${item.codigo_patrimonial}</td>`;
                    html += `<td style="padding: 8px;">${item.marca} ${item.modelo}</td>`;
                    html += `<td style="padding: 8px;">${item.estado}</td>`;
                    html += `<td style="padding: 8px;">${item.ultimo_mantenimiento ? new Date(item.ultimo_mantenimiento).toLocaleDateString('es-ES') : 'Nunca'}</td>`;
                    html += '</tr>';
                });
                
                html += '</tbody></table>';
                $('#tablaSinMantenimiento').html(html);
            }
        }
    });
}

function exportarPDF(tipo) {
    let url = BASE_URL + '/controllers/reportes.php?action=exportarPDF&tipo=' + tipo;
    
    // Si es reporte de mantenimientos, agregar filtros de fecha
    if (tipo === 'mantenimientos') {
        const fechaInicio = $('#filtroFechaInicio').val();
        const fechaFin = $('#filtroFechaFin').val();
        if (fechaInicio) url += '&fecha_inicio=' + fechaInicio;
        if (fechaFin) url += '&fecha_fin=' + fechaFin;
    }
    
    window.open(url, '_blank');
}

function exportarExcel(tipo) {
    let url = BASE_URL + '/controllers/reportes.php?action=exportarExcel&tipo=' + tipo;
    
    // Si es reporte de mantenimientos, agregar filtros de fecha
    if (tipo === 'mantenimientos') {
        const fechaInicio = $('#filtroFechaInicio').val();
        const fechaFin = $('#filtroFechaFin').val();
        if (fechaInicio) url += '&fecha_inicio=' + fechaInicio;
        if (fechaFin) url += '&fecha_fin=' + fechaFin;
    }
    
    window.location.href = url;
}
</script>
EOD;

include __DIR__ . '/../../includes/header.php';
?>

<style>
    
/* Estilos para reportes con neumorfismo */
.report-card {
    margin-bottom: 40px;
    border-radius: 15px;
    padding: 25px;
    background: var(--bg-card);
    box-shadow: 
        8px 8px 16px rgba(0, 0, 0, 0.1),
        -8px -8px 16px rgba(255, 255, 255, 0.9);
    border: none !important;
    transition: all 0.3s ease;
}

.report-card:hover {
    transform: translateY(-5px);
    box-shadow: 
        12px 12px 24px rgba(0, 0, 0, 0.15),
        -12px -12px 24px rgba(255, 255, 255, 1);
}

[data-theme="dark"] .report-card {
    box-shadow: 
        8px 8px 16px rgba(0, 0, 0, 0.4),
        -8px -8px 16px rgba(255, 255, 255, 0.05);
}

[data-theme="dark"] .report-card:hover {
    transform: translateY(-5px);
    box-shadow: 
        12px 12px 24px rgba(0, 0, 0, 0.5),
        -12px -12px 24px rgba(255, 255, 255, 0.08);
}

.report-card h5 {
    color: var(--text-primary);
    margin-bottom: 20px;
    font-weight: 600;
}

.report-section {
    background: var(--bg-hover);
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
}

.report-section:hover {
    transform: scale(1.02);
    box-shadow: 
        4px 4px 12px rgba(0, 0, 0, 0.1),
        -4px -4px 12px rgba(255, 255, 255, 0.8);
}

[data-theme="dark"] .report-section:hover {
    box-shadow: 
        4px 4px 12px rgba(0, 0, 0, 0.3),
        -4px -4px 12px rgba(255, 255, 255, 0.05);
}

/* Estilos para gráficos circulares modernos */
.circular-chart-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    background: var(--bg-card);
    border-radius: 12px;
    box-shadow: 
        inset 3px 3px 6px rgba(0, 0, 0, 0.1),
        inset -3px -3px 6px rgba(255, 255, 255, 0.5);
}

[data-theme="dark"] .circular-chart-wrapper {
    box-shadow: 
        inset 3px 3px 6px rgba(0, 0, 0, 0.4),
        inset -3px -3px 6px rgba(255, 255, 255, 0.03);
}

.circular-chart {
    position: relative;
    width: 220px;
    height: 220px;
    margin-bottom: 30px;
}

.chart-svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}

.chart-background {
    fill: none;
    stroke: var(--bg-hover);
    stroke-width: 25;
}

.chart-segment {
    fill: none;
    stroke-width: 25;
    transition: all 0.3s ease;
    cursor: pointer;
    stroke-linecap: round;
}

.chart-segment:hover {
    stroke-width: 30;
    filter: brightness(1.2);
}

.chart-segment.active {
    stroke-width: 30;
    filter: brightness(1.3) drop-shadow(0 0 10px currentColor);
}

.chart-center-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    pointer-events: none;
}

.chart-center-value {
    font-size: 2.8em;
    font-weight: 900;
    color: var(--text-primary);
    line-height: 1;
}

.chart-center-label {
    font-size: 0.9em;
    color: var(--text-secondary);
    font-weight: 600;
    margin-top: 5px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Leyendas interactivas estilo horizontal */
.chart-legends-horizontal {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 15px;
    width: 100%;
    padding: 15px;
    background: var(--bg-hover);
    border-radius: 10px;
}

.legend-item-horizontal {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 15px;
    border-radius: 8px;
    background: var(--bg-card);
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    box-shadow: 
        3px 3px 6px rgba(0, 0, 0, 0.1),
        -3px -3px 6px rgba(255, 255, 255, 0.7);
}

.legend-item-horizontal:hover {
    transform: translateY(-3px);
    box-shadow: 
        5px 5px 10px rgba(0, 0, 0, 0.15),
        -5px -5px 10px rgba(255, 255, 255, 0.9);
}

.legend-item-horizontal.active {
    border-color: currentColor;
    transform: translateY(-3px) scale(1.05);
    box-shadow: 
        5px 5px 10px rgba(0, 0, 0, 0.2),
        -5px -5px 10px rgba(255, 255, 255, 1);
}

[data-theme="dark"] .legend-item-horizontal {
    box-shadow: 
        3px 3px 6px rgba(0, 0, 0, 0.3),
        -3px -3px 6px rgba(255, 255, 255, 0.03);
}

[data-theme="dark"] .legend-item-horizontal:hover,
[data-theme="dark"] .legend-item-horizontal.active {
    box-shadow: 
        5px 5px 10px rgba(0, 0, 0, 0.4),
        -5px -5px 10px rgba(255, 255, 255, 0.05);
}

.legend-color-box {
    width: 20px;
    height: 20px;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.legend-text {
    color: var(--text-primary);
    font-weight: 600;
    font-size: 0.9em;
}

.legend-percentage {
    color: var(--text-secondary);
    font-weight: 700;
    font-size: 0.85em;
    margin-left: 5px;
}

/* Inputs con neumorfismo */
.filter-input {
    padding: 10px;
    border-radius: 8px;
    background: var(--bg-input);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
    box-shadow: 
        inset 2px 2px 4px rgba(0, 0, 0, 0.1),
        inset -2px -2px 4px rgba(255, 255, 255, 0.5);
    margin-right: 10px;
}

.filter-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 
        inset 2px 2px 4px rgba(0, 0, 0, 0.15),
        inset -2px -2px 4px rgba(255, 255, 255, 0.7),
        0 0 0 3px rgba(102, 126, 234, 0.1);
}

[data-theme="dark"] .filter-input {
    box-shadow: 
        inset 2px 2px 4px rgba(0, 0, 0, 0.4),
        inset -2px -2px 4px rgba(255, 255, 255, 0.03);
}

/* Botones con neumorfismo */
.btn-report {
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    background: var(--bg-card);
    color: var(--text-primary);
    box-shadow: 
        4px 4px 8px rgba(0, 0, 0, 0.15),
        -4px -4px 8px rgba(255, 255, 255, 0.7);
    margin-right: 10px;
    margin-bottom: 10px;
}

.btn-report:hover {
    transform: translateY(-2px);
    box-shadow: 
        6px 6px 12px rgba(0, 0, 0, 0.2),
        -6px -6px 12px rgba(255, 255, 255, 0.9);
}

.btn-report:active {
    transform: translateY(0);
    box-shadow: 
        inset 2px 2px 4px rgba(0, 0, 0, 0.2),
        inset -2px -2px 4px rgba(255, 255, 255, 0.5);
}

[data-theme="dark"] .btn-report {
    box-shadow: 
        4px 4px 8px rgba(0, 0, 0, 0.4),
        -4px -4px 8px rgba(255, 255, 255, 0.05);
}

[data-theme="dark"] .btn-report:hover {
    box-shadow: 
        6px 6px 12px rgba(0, 0, 0, 0.5),
        -6px -6px 12px rgba(255, 255, 255, 0.08);
}

.btn-primary-report {
    background: var(--primary-color);
    color: white;
}

/* Tabla de datos */
.report-table {
    width: 100%;
    background: var(--bg-card);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 
        inset 2px 2px 4px rgba(0, 0, 0, 0.05);
}

.report-table table {
    width: 100%;
    border-collapse: collapse;
}

.report-table th {
    background: var(--bg-hover);
    color: var(--text-primary);
    font-weight: 700;
    padding: 12px;
    text-align: left;
    border-bottom: 2px solid var(--border-color);
}

.report-table td {
    padding: 10px 12px;
    color: var(--text-primary);
    border-bottom: 1px solid var(--border-color);
}

.report-table tbody tr {
    transition: all 0.2s ease;
}

.report-table tr:hover {
    background: var(--bg-hover);
    transform: scale(1.01);
    box-shadow: 
        2px 2px 4px rgba(0, 0, 0, 0.08);
    transition: all 0.2s ease;
}

[data-theme="dark"] .report-table tr:hover {
    box-shadow: 
        2px 2px 4px rgba(0, 0, 0, 0.2);
}

/* Contenedores de filtros con hover */
.filter-container {
    margin-bottom: 20px;
    padding: 15px;
    background: var(--bg-hover);
    border-radius: 10px;
    transition: all 0.3s ease;
}

.filter-container:hover {
    box-shadow: 
        4px 4px 8px rgba(0, 0, 0, 0.1),
        -4px -4px 8px rgba(255, 255, 255, 0.8);
}

[data-theme="dark"] .filter-container:hover {
    box-shadow: 
        4px 4px 8px rgba(0, 0, 0, 0.3),
        -4px -4px 8px rgba(255, 255, 255, 0.05);
}
</style>

<script>
// Colores predefinidos para los gráficos
const CHART_COLORS = [
    '#28a745', '#ffc107', '#dc3545', '#007bff', '#6f42c1', 
    '#fd7e14', '#20c997', '#e83e8c', '#17a2b8', '#6c757d'
];

// Función para crear gráfico circular interactivo
function createCircularChart(containerId, data) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    // Calcular total y porcentajes
    const total = data.reduce((sum, item) => sum + item.value, 0);
    
    // Si el total es 0, no mostrar nada
    if (total === 0) {
        container.innerHTML = '<p style="text-align: center; color: var(--text-muted); padding: 40px;">No hay datos para mostrar</p>';
        return;
    }
    
    const radius = 85;
    const circumference = 2 * Math.PI * radius;
    
    let currentOffset = 0;
    let segments = [];
    
    // Crear SVG
    let svgHTML = `
        <div class="circular-chart">
            <svg class="chart-svg" viewBox="0 0 200 200">
                <circle class="chart-background" cx="100" cy="100" r="${radius}"/>
    `;
    
    // Crear segmentos dinámicamente según porcentajes reales
    data.forEach((item, index) => {
        const percentage = (item.value / total) * 100;
        const segmentLength = (circumference * percentage) / 100;
        const color = item.color || CHART_COLORS[index % CHART_COLORS.length];
        
        segments.push({
            ...item,
            color,
            percentage,
            offset: currentOffset,
            length: segmentLength
        });
        
        svgHTML += `
            <circle 
                class="chart-segment" 
                id="segment-${containerId}-${index}"
                cx="100" 
                cy="100" 
                r="${radius}"
                stroke="${color}"
                stroke-dasharray="${segmentLength} ${circumference - segmentLength}"
                stroke-dashoffset="${-currentOffset}"
                data-index="${index}"
            />
        `;
        
        currentOffset += segmentLength;
    });
    
    svgHTML += `
            </svg>
            <div class="chart-center-content">
                <div class="chart-center-value" id="center-value-${containerId}">${total}</div>
                <div class="chart-center-label" id="center-label-${containerId}">TOTAL</div>
            </div>
        </div>
    `;
    
    // Crear leyendas horizontales
    let legendsHTML = '<div class="chart-legends-horizontal">';
    
    segments.forEach((segment, index) => {
        legendsHTML += `
            <div class="legend-item-horizontal" 
                 id="legend-${containerId}-${index}"
                 data-index="${index}"
                 style="color: ${segment.color}">
                <div class="legend-color-box" style="background: ${segment.color}"></div>
                <span class="legend-text">${segment.label}</span>
                <span class="legend-percentage">${segment.percentage.toFixed(1)}%</span>
            </div>
        `;
    });
    
    legendsHTML += '</div>';
    
    // Insertar en el contenedor
    container.innerHTML = `
        <div class="circular-chart-wrapper">
            ${svgHTML}
            ${legendsHTML}
        </div>
    `;
    
    // Agregar interactividad
    segments.forEach((segment, index) => {
        const segmentEl = document.getElementById(`segment-${containerId}-${index}`);
        const legendEl = document.getElementById(`legend-${containerId}-${index}`);
        const centerValue = document.getElementById(`center-value-${containerId}`);
        const centerLabel = document.getElementById(`center-label-${containerId}`);
        
        const toggleActive = () => {
            // Remover active de todos
            document.querySelectorAll(`#${containerId} .chart-segment`).forEach(el => {
                el.classList.remove('active');
            });
            document.querySelectorAll(`#${containerId} .legend-item-horizontal`).forEach(el => {
                el.classList.remove('active');
            });
            
            // Agregar active al clickeado
            segmentEl.classList.add('active');
            legendEl.classList.add('active');
            
            // Actualizar centro
            centerValue.textContent = segment.value;
            centerLabel.textContent = segment.label.toUpperCase();
        };
        
        const resetChart = () => {
            document.querySelectorAll(`#${containerId} .chart-segment`).forEach(el => {
                el.classList.remove('active');
            });
            document.querySelectorAll(`#${containerId} .legend-item-horizontal`).forEach(el => {
                el.classList.remove('active');
            });
            centerValue.textContent = total;
            centerLabel.textContent = 'TOTAL';
        };
        
        // Click en segmento o leyenda
        segmentEl.addEventListener('click', toggleActive);
        legendEl.addEventListener('click', toggleActive);
        
        // Doble click para resetear
        segmentEl.addEventListener('dblclick', resetChart);
        legendEl.addEventListener('dblclick', resetChart);
    });
    
    return segments;
}

// Función para cargar datos desde PHP y crear gráfico
function cargarGraficoEstados(datosEstados) {
    // datosEstados debe venir como array desde PHP
    // Ejemplo: [{ label: 'Operativo', value: 25, color: '#28a745' }, ...]
    
    createCircularChart('chartEstadosContainer', datosEstados);
}

// Datos de ejemplo - ESTOS SE REEMPLAZAN CON DATOS REALES DE PHP
const estadosData = [
    { label: 'Operativo', value: 43, color: '#28a745' },
    { label: 'En Mantenimiento', value: 100, color: '#ffc107' },
    { label: 'Inoperativo', value: 22, color: '#dc3545' },
    { label: 'En Garantía', value: 14, color: '#007bff' },
    { label: 'Fuera de Servicio', value: 12, color: '#6c757d' }
];

// Inicializar cuando el documento esté listo
document.addEventListener('DOMContentLoaded', function() {
    createCircularChart('chartEstadosContainer', estadosData);
});
</script>

<div class="content-card">
    <h4 style="color: var(--text-primary);"><i class="fas fa-chart-bar"></i> Reportes del Sistema</h4>
    
    <!-- Reporte 1: Equipos por Estado -->
    <div class="report-card">
        <h5><i class="fas fa-desktop"></i> 1. Equipos por Estado</h5>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="report-section">
                <h6 style="color: var(--text-primary); margin-bottom: 15px;">Tabla de Datos</h6>
                <div id="tablaEstados" class="report-table"></div>
            </div>
            <div class="report-section">
                <h6 style="color: var(--text-primary); margin-bottom: 15px;">Gráfico Circular Interactivo</h6>
                <div id="chartEstadosContainer"></div>
                <p style="text-align: center; color: var(--text-secondary); font-size: 0.85em; margin-top: 10px;">
                    <i class="fas fa-info-circle"></i> Haz clic en un segmento o leyenda para ver detalles. Doble clic para resetear.
                </p>
            </div>
        </div>
        
        <div style="margin-top: 20px;">
            <button class="btn-report" onclick="exportarPDF('estados')">
                <i class="fas fa-file-pdf"></i> Exportar PDF
            </button>
            <button class="btn-report" onclick="exportarExcel('estados')">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </button>
        </div>
    </div>
    
    <!-- Reporte 2: Mantenimientos por Periodo -->
    <div class="report-card">
        <h5><i class="fas fa-tools"></i> 2. Mantenimientos por Periodo</h5>
        
        <div class="filter-container">
            <label style="color: var(--text-primary); font-weight: 600;">Fecha Inicio:</label>
            <input type="date" id="filtroFechaInicio" class="filter-input">
            <label style="color: var(--text-primary); font-weight: 600;">Fecha Fin:</label>
            <input type="date" id="filtroFechaFin" class="filter-input">
            <button class="btn-report btn-primary-report" onclick="cargarReporteMantenimientos()">
                <i class="fas fa-search"></i> Filtrar
            </button>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr; gap: 20px;">
            <div class="report-section">
                <h6 style="color: var(--text-primary); margin-bottom: 15px;">Detalle de Mantenimientos</h6>
                <div id="tablaMantenimientos" class="report-table" style="max-height: 400px; overflow-y: auto;"></div>
            </div>
        </div>
        
        <div style="margin-top: 20px;">
            <button class="btn-report" onclick="exportarPDF('mantenimientos')">
                <i class="fas fa-file-pdf"></i> Exportar PDF
            </button>
            <button class="btn-report" onclick="exportarExcel('mantenimientos')">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </button>
        </div>
    </div>
    
    <!-- Reporte 3: Equipos por Sede -->
    <div class="report-card">
        <h5><i class="fas fa-building"></i> 3. Equipos por Sede</h5>
        
        <div class="filter-container">
            <label style="color: var(--text-primary); font-weight: 600;">Sede:</label>
            <select id="filtroSede" class="filter-input" style="padding: 10px;">
                <option value="">Todas</option>
                <?php foreach ($sedes as $sede): ?>
                <option value="<?php echo $sede['id']; ?>"><?php echo htmlspecialchars($sede['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn-report btn-primary-report" onclick="cargarReporteSedes()">
                <i class="fas fa-search"></i> Filtrar
            </button>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="report-section">
                <h6 style="color: var(--text-primary); margin-bottom: 15px;">Tabla de Datos</h6>
                <div id="tablaSedes" class="report-table"></div>
            </div>
            
            <div class="report-section">
                <h6 style="color: var(--text-primary); margin-bottom: 15px;">Gráfico Interactivo</h6>
                <div id="chartSedes" style="min-height: 400px;"></div>
            </div>
        </div>
        
        <div style="margin-top: 20px;">
            <button class="btn-report" onclick="exportarPDF('sedes')">
                <i class="fas fa-file-pdf"></i> Exportar PDF
            </button>
            <button class="btn-report" onclick="exportarExcel('sedes')">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </button>
        </div>
    </div>
    
    <!-- Reporte 4: Equipos sin Mantenimiento -->
    <div class="report-card">
        <h5><i class="fas fa-exclamation-triangle"></i> 4. Equipos sin Mantenimiento (Alerta)</h5>
        
        <div class="filter-container">
            <label style="color: var(--text-primary); font-weight: 600;">Días sin mantenimiento:</label>
            <input type="number" id="filtroDias" value="90" class="filter-input" style="width: 100px;">
            <button class="btn-report btn-primary-report" onclick="cargarReporteSinMantenimiento()">
                <i class="fas fa-search"></i> Filtrar
            </button>
        </div>
        
        <div class="report-section">
            <h6 style="color: var(--text-primary); margin-bottom: 15px;">Equipos que requieren atención</h6>
            <div id="tablaSinMantenimiento" class="report-table" style="max-height: 400px; overflow-y: auto;"></div>
        </div>
        
        <div style="margin-top: 20px;">
            <button class="btn-report" onclick="exportarPDF('sinMantenimiento')">
                <i class="fas fa-file-pdf"></i> Exportar PDF
            </button>
            <button class="btn-report" onclick="exportarExcel('sinMantenimiento')">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </button>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../../includes/footer.php';
?>