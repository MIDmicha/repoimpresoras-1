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

$page_title = 'Auditoría del Sistema';

$database = new Database();
$db = $database->getConnection();

// Obtener usuarios para filtro
$stmt = $db->query("SELECT id, nombre_completo, username FROM usuarios WHERE activo = 1 ORDER BY nombre_completo");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener tablas únicas
$stmt = $db->query("SELECT DISTINCT tabla FROM auditoria ORDER BY tabla");
$tablas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$extra_js = <<<'EOD'
<script>
let tablaAuditoria;

$(document).ready(function() {
    cargarAuditoria();
    
    // Event listeners para filtros
    $('#filtroUsuario, #filtroTabla, #filtroAccion, #filtroFechaInicio, #filtroFechaFin').on('change', function() {
        cargarAuditoria();
    });
    
    $('#btnBuscar').on('click', function() {
        cargarAuditoria();
    });
    
    $('#btnLimpiarFiltros').on('click', function() {
        $('#filtroUsuario').val('');
        $('#filtroTabla').val('');
        $('#filtroAccion').val('');
        $('#filtroFechaInicio').val('');
        $('#filtroFechaFin').val('');
        $('#filtroTexto').val('');
        cargarAuditoria();
    });
});

function cargarAuditoria() {
    const filtros = {
        action: 'listar',
        usuario: $('#filtroUsuario').val(),
        tabla: $('#filtroTabla').val(),
        accion: $('#filtroAccion').val(),
        fecha_inicio: $('#filtroFechaInicio').val(),
        fecha_fin: $('#filtroFechaFin').val(),
        texto: $('#filtroTexto').val()
    };
    
    if (tablaAuditoria) {
        tablaAuditoria.destroy();
    }
    
    tablaAuditoria = $('#tablaAuditoria').DataTable({
        ajax: {
            url: BASE_URL + '/controllers/auditoria.php',
            data: filtros,
            dataSrc: function(json) {
                if (!json.success) {
                    Swal.fire('Error', json.message, 'error');
                    return [];
                }
                return json.data;
            }
        },
        columns: [
            {
                data: null,
                render: function(data, type, row) {
                    return `<div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: ${getColorAccion(row.accion)}; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                            ${getIconoAccion(row.accion)}
                        </div>
                        <div>
                            <div style="font-weight: bold;">${row.usuario}</div>
                            <div style="font-size: 0.85em; color: #666;">${formatearFecha(row.fecha_hora)}</div>
                        </div>
                    </div>`;
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    let badge = '';
                    if (row.accion === 'INSERT') badge = '<span style="background: #28a745; color: white; padding: 3px 8px; border-radius: 3px; font-size: 0.85em;">CREAR</span>';
                    else if (row.accion === 'UPDATE') badge = '<span style="background: #ffc107; color: black; padding: 3px 8px; border-radius: 3px; font-size: 0.85em;">MODIFICAR</span>';
                    else if (row.accion === 'DELETE') badge = '<span style="background: #dc3545; color: white; padding: 3px 8px; border-radius: 3px; font-size: 0.85em;">ELIMINAR</span>';
                    
                    return `<div>
                        ${badge}
                        <div style="margin-top: 5px; font-weight: bold;">${row.tabla}</div>
                        <div style="font-size: 0.85em; color: #666;">ID: ${row.id_registro}</div>
                    </div>`;
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    let cambios = [];
                    
                    if (row.accion === 'INSERT' && row.datos_nuevos) {
                        const nuevos = JSON.parse(row.datos_nuevos);
                        for (let campo in nuevos) {
                            if (nuevos[campo] !== null && nuevos[campo] !== '') {
                                cambios.push(`<div style="margin-bottom: 5px;">
                                    <strong>${campo}:</strong> 
                                    <span style="color: green;">${nuevos[campo]}</span>
                                </div>`);
                            }
                        }
                    } else if (row.accion === 'UPDATE' && row.datos_anteriores && row.datos_nuevos) {
                        const anteriores = JSON.parse(row.datos_anteriores);
                        const nuevos = JSON.parse(row.datos_nuevos);
                        
                        for (let campo in nuevos) {
                            if (anteriores[campo] !== nuevos[campo]) {
                                cambios.push(`<div style="margin-bottom: 5px; padding: 5px; background: #f8f9fa; border-left: 3px solid #ffc107;">
                                    <strong>${campo}:</strong><br>
                                    <span style="color: red; text-decoration: line-through;">${anteriores[campo] || '(vacío)'}</span>
                                    <span style="margin: 0 5px;">→</span>
                                    <span style="color: green;">${nuevos[campo] || '(vacío)'}</span>
                                </div>`);
                            }
                        }
                    } else if (row.accion === 'DELETE' && row.datos_anteriores) {
                        const anteriores = JSON.parse(row.datos_anteriores);
                        for (let campo in anteriores) {
                            if (anteriores[campo] !== null && anteriores[campo] !== '') {
                                cambios.push(`<div style="margin-bottom: 5px;">
                                    <strong>${campo}:</strong> 
                                    <span style="color: red; text-decoration: line-through;">${anteriores[campo]}</span>
                                </div>`);
                            }
                        }
                    }
                    
                    if (cambios.length === 0) {
                        return '<em style="color: #999;">Sin cambios registrados</em>';
                    }
                    
                    if (cambios.length > 3) {
                        return cambios.slice(0, 3).join('') + 
                            `<div style="margin-top: 5px;">
                                <a href="#" onclick="verDetalle('${row.id}'); return false;" style="color: #007bff; text-decoration: none;">
                                    <i class="fas fa-eye"></i> Ver ${cambios.length - 3} cambios más...
                                </a>
                            </div>`;
                    }
                    
                    return cambios.join('');
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    return `<div style="text-align: center;">
                        <div style="font-size: 0.85em; color: #666; margin-bottom: 5px;">
                            <i class="fas fa-network-wired"></i> ${row.ip_usuario || 'N/A'}
                        </div>
                        <button onclick="verDetalle('${row.id}')" style="padding: 5px 10px;">
                            <i class="fas fa-search-plus"></i> Detalle
                        </button>
                    </div>`;
                }
            }
        ],
        order: [[0, 'desc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        pageLength: 25,
        responsive: true,
        dom: '<"top"lf>rt<"bottom"ip><"clear">'
    });
}

function getColorAccion(accion) {
    if (accion === 'INSERT') return '#28a745';
    if (accion === 'UPDATE') return '#ffc107';
    if (accion === 'DELETE') return '#dc3545';
    return '#6c757d';
}

function getIconoAccion(accion) {
    if (accion === 'INSERT') return '+';
    if (accion === 'UPDATE') return '✎';
    if (accion === 'DELETE') return '✕';
    return '?';
}

function formatearFecha(fecha) {
    const d = new Date(fecha);
    const opciones = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    };
    return d.toLocaleDateString('es-ES', opciones);
}

function verDetalle(idAuditoria) {
    $.ajax({
        url: BASE_URL + '/controllers/auditoria.php',
        method: 'GET',
        data: { action: 'detalle', id: idAuditoria },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                
                let html = `
                    <div style="text-align: left;">
                        <h5 style="border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px;">
                            <i class="fas fa-info-circle"></i> Información General
                        </h5>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                            <div>
                                <strong>Usuario:</strong><br>
                                <span style="font-size: 1.1em;">${data.usuario}</span>
                            </div>
                            <div>
                                <strong>Fecha y Hora:</strong><br>
                                <span style="font-size: 1.1em;">${formatearFecha(data.fecha_hora)}</span>
                            </div>
                            <div>
                                <strong>Acción:</strong><br>
                                ${getAccionBadge(data.accion)}
                            </div>
                            <div>
                                <strong>Tabla:</strong><br>
                                <span style="font-size: 1.1em;">${data.tabla} (ID: ${data.id_registro})</span>
                            </div>
                            <div>
                                <strong>IP:</strong><br>
                                <span style="font-size: 1.1em;">${data.ip_usuario || 'N/A'}</span>
                            </div>
                            <div>
                                <strong>Navegador:</strong><br>
                                <span style="font-size: 0.9em;">${data.user_agent ? data.user_agent.substring(0, 50) + '...' : 'N/A'}</span>
                            </div>
                        </div>
                        
                        <h5 style="border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px;">
                            <i class="fas fa-exchange-alt"></i> Detalle de Cambios
                        </h5>
                        
                        <div id="detallesCambios"></div>
                    </div>
                `;
                
                $('#modalDetalleContent').html(html);
                
                // Mostrar cambios detallados
                mostrarCambiosDetallados(data);
                
                $('#modalDetalle').modal('show');
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', 'No se pudo cargar el detalle', 'error');
        }
    });
}

function getAccionBadge(accion) {
    if (accion === 'INSERT') return '<span style="background: #28a745; color: white; padding: 5px 15px; border-radius: 5px; font-weight: bold;">CREAR</span>';
    if (accion === 'UPDATE') return '<span style="background: #ffc107; color: black; padding: 5px 15px; border-radius: 5px; font-weight: bold;">MODIFICAR</span>';
    if (accion === 'DELETE') return '<span style="background: #dc3545; color: white; padding: 5px 15px; border-radius: 5px; font-weight: bold;">ELIMINAR</span>';
    return accion;
}

function mostrarCambiosDetallados(data) {
    let html = '';
    
    if (data.accion === 'INSERT' && data.datos_nuevos) {
        const nuevos = JSON.parse(data.datos_nuevos);
        html += '<table style="width: 100%; border-collapse: collapse;">';
        html += '<thead><tr style="background: #28a745; color: white;">';
        html += '<th style="padding: 10px; text-align: left;">Campo</th>';
        html += '<th style="padding: 10px; text-align: left;">Valor Nuevo</th>';
        html += '</tr></thead><tbody>';
        
        for (let campo in nuevos) {
            html += `<tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 10px; font-weight: bold;">${campo}</td>
                <td style="padding: 10px; color: green;">${formatearValor(nuevos[campo])}</td>
            </tr>`;
        }
        html += '</tbody></table>';
        
    } else if (data.accion === 'UPDATE' && data.datos_anteriores && data.datos_nuevos) {
        const anteriores = JSON.parse(data.datos_anteriores);
        const nuevos = JSON.parse(data.datos_nuevos);
        
        html += '<table style="width: 100%; border-collapse: collapse;">';
        html += '<thead><tr style="background: #ffc107;">';
        html += '<th style="padding: 10px; text-align: left;">Campo</th>';
        html += '<th style="padding: 10px; text-align: left;">Valor Anterior</th>';
        html += '<th style="padding: 10px; text-align: center;">→</th>';
        html += '<th style="padding: 10px; text-align: left;">Valor Nuevo</th>';
        html += '</tr></thead><tbody>';
        
        for (let campo in nuevos) {
            if (anteriores[campo] !== nuevos[campo]) {
                html += `<tr style="border-bottom: 1px solid #ddd; background: #fff9e6;">
                    <td style="padding: 10px; font-weight: bold;">${campo}</td>
                    <td style="padding: 10px; color: red; text-decoration: line-through;">${formatearValor(anteriores[campo])}</td>
                    <td style="padding: 10px; text-align: center; font-weight: bold;">→</td>
                    <td style="padding: 10px; color: green; font-weight: bold;">${formatearValor(nuevos[campo])}</td>
                </tr>`;
            }
        }
        html += '</tbody></table>';
        
    } else if (data.accion === 'DELETE' && data.datos_anteriores) {
        const anteriores = JSON.parse(data.datos_anteriores);
        html += '<table style="width: 100%; border-collapse: collapse;">';
        html += '<thead><tr style="background: #dc3545; color: white;">';
        html += '<th style="padding: 10px; text-align: left;">Campo</th>';
        html += '<th style="padding: 10px; text-align: left;">Valor Eliminado</th>';
        html += '</tr></thead><tbody>';
        
        for (let campo in anteriores) {
            html += `<tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 10px; font-weight: bold;">${campo}</td>
                <td style="padding: 10px; color: red; text-decoration: line-through;">${formatearValor(anteriores[campo])}</td>
            </tr>`;
        }
        html += '</tbody></table>';
    }
    
    $('#detallesCambios').html(html);
}

function formatearValor(valor) {
    if (valor === null || valor === '') return '<em style="color: #999;">(vacío)</em>';
    if (typeof valor === 'boolean') return valor ? 'Sí' : 'No';
    if (typeof valor === 'object') return JSON.stringify(valor);
    return valor;
}

function verTimelineUsuario(idUsuario) {
    window.location.href = BASE_URL + '/views/auditoria/timeline.php?usuario=' + idUsuario;
}

function exportarAuditoria() {
    const filtros = {
        action: 'exportar',
        usuario: $('#filtroUsuario').val(),
        tabla: $('#filtroTabla').val(),
        accion: $('#filtroAccion').val(),
        fecha_inicio: $('#filtroFechaInicio').val(),
        fecha_fin: $('#filtroFechaFin').val()
    };
    
    const queryString = $.param(filtros);
    window.location.href = BASE_URL + '/controllers/auditoria.php?' + queryString;
}
</script>
EOD;

include __DIR__ . '/../../includes/header.php';
?>

<style>
    
[data-theme="dark"] #tablaAuditoria td,
[data-theme="dark"] #tablaAuditoria th,
[data-theme="dark"] #tablaAuditoria tbody td,
[data-theme="dark"] #tablaAuditoria tbody tr td,
[data-theme="dark"] .table-responsive td {
    color: var(--text-primary) !important; /* #f1f5f9 - gris claro legible */
}

[data-theme="dark"] #tablaAuditoria td[style*="color: white"],
[data-theme="dark"] #tablaAuditoria td[style*="color:#ffffff"],
[data-theme="dark"] #tablaAuditoria td[style*="color: #fff"] {
    color: var(--text-primary) !important;
}

[data-theme="dark"] #tablaAuditoria tbody td {
    background-color: transparent !important;
}

[data-theme="dark"] #tablaAuditoria tbody tr:hover td {
    background-color: var(--bg-hover) !important;
}
[data-theme="dark"] .table td,
[data-theme="dark"] .table th,
[data-theme="dark"] .dataTables_wrapper .table td,
[data-theme="dark"] .dataTables_wrapper .table th {
    color: var(--text-primary) !important; /* #f1f5f9 - gris claro legible */
}

[data-theme="dark"] .table tbody td {
    background-color: transparent !important;
    border-bottom: 1px solid var(--border-color) !important;
}

[data-theme="dark"] .table tbody tr:hover td {
    background-color: var(--bg-hover) !important;
}


[data-theme="dark"] table.dataTable tbody td {
    color: var(--text-primary) !important;
}

[data-theme="dark"] .dataTables_wrapper td,
[data-theme="dark"] .dataTables_wrapper th {
    color: var(--text-primary) !important;
}
.content-card {
  background: var(--bg-card);
  border-radius: var(--border-radius);
  padding: 24px;
  box-shadow: var(--shadow);
  border: 1px solid var(--border-color);
}

.filters-container {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  padding: 20px;
}

.filters-container h5 {
  color: var(--text-primary);
}

.filters-container .form-label {
  color: var(--text-primary);
  font-weight: 600;
}

.filters-container .form-select,
.filters-container .form-control {
  background: var(--bg-input);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
}

.filters-container .btn-primary {
  background: var(--primary-color);
  border-color: var(--primary-color);
}

.filters-container .btn-secondary {
  background: var(--bg-hover);
  color: var(--text-primary);
}

/* Tabla */
#tablaAuditoria {
  background: var(--bg-card);
  color: var(--text-primary);
}

#tablaAuditoria thead {
  background: var(--bg-hover);
}

#tablaAuditoria th {
  color: var(--text-primary);
  font-weight: 600;
}

#tablaAuditoria tbody td {
  border-bottom: 1px solid var(--border-color);
}

#tablaAuditoria tbody tr:hover {
  background: var(--bg-hover);
}

/* Modal */
#modalDetalle .modal-content {
  background: var(--bg-card);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
}

#modalDetalle .modal-title {
  color: var(--text-primary);
}

#modalDetalle .modal-header,
#modalDetalle .modal-footer {
  border-color: var(--border-color);
}

#modalDetalle .btn-secondary {
  background: var(--bg-hover);
  color: var(--text-primary);
}
[data-theme="dark"] #tablaAuditoria tbody td:nth-child(3) {
    background-color: var(--bg-card) !important; 
}

[data-theme="dark"] #tablaAuditoria tbody td:nth-child(3) {
    color: var(--text-primary) !important; /* #f1f5f9 */
}

[data-theme="dark"] #tablaAuditoria tbody td:nth-child(3) span,
[data-theme="dark"] #tablaAuditoria tbody td:nth-child(3) div,
[data-theme="dark"] #tablaAuditoria tbody td:nth-child(3) p {
    color: var(--text-primary) !important;
    background-color: transparent !important;
}

/* Hover en esa celda */
[data-theme="dark"] #tablaAuditoria tbody tr:hover td:nth-child(3) {
    background-color: var(--bg-hover) !important; /* Un poco más claro al hover */
}
[data-theme="dark"] #modalDetalle .modal-body,
[data-theme="dark"] #modalDetalleContent {
    background-color: var(--bg-card) !important; /* #1e293b - fondo oscuro */
}

[data-theme="dark"] #modalDetalleContent table tbody td:first-child,
[data-theme="dark"] #modalDetalleContent table tbody td:first-child span,
[data-theme="dark"] #modalDetalleContent table tbody td:first-child div,
[data-theme="dark"] #modalDetalleContent table tbody td:first-child p {
    color: #000000 !important; 
}



/* Fondo de celdas en oscuro */
[data-theme="dark"] #modalDetalleContent table tbody td {
    background-color: transparent !important;
}
</style>

<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-history"></i> Auditoría del Sistema</h4>
        <button onclick="exportarAuditoria()" class="btn btn-primary">
            <i class="fas fa-download"></i> Exportar Excel
        </button>
    </div>
    
    <!-- Filtros Avanzados -->
    <div class="filters-container mb-4">
        <h5 class="mb-3"><i class="fas fa-filter"></i> Filtros de Búsqueda</h5>
        
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label fw-bold">Usuario</label>
                <select id="filtroUsuario" class="form-select">
                    <option value="">Todos los usuarios</option>
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?php echo $usuario['id']; ?>">
                            <?php echo htmlspecialchars($usuario['nombre_completo']); ?> (<?php echo htmlspecialchars($usuario['username']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-4">
                <label class="form-label fw-bold">Tabla</label>
                <select id="filtroTabla" class="form-select">
                    <option value="">Todas las tablas</option>
                    <?php foreach ($tablas as $tabla): ?>
                        <option value="<?php echo $tabla['tabla']; ?>">
                            <?php echo htmlspecialchars($tabla['tabla']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-4">
                <label class="form-label fw-bold">Acción</label>
                <select id="filtroAccion" class="form-select">
                    <option value="">Todas las acciones</option>
                    <option value="INSERT">CREAR</option>
                    <option value="UPDATE">MODIFICAR</option>
                    <option value="DELETE">ELIMINAR</option>
                </select>
            </div>
        </div>
        
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-bold">Fecha Inicio</label>
                <input type="date" id="filtroFechaInicio" class="form-control">
            </div>
            
            <div class="col-md-4">
                <label class="form-label fw-bold">Fecha Fin</label>
                <input type="date" id="filtroFechaFin" class="form-control">
            </div>
            
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button id="btnBuscar" class="btn btn-primary">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <button id="btnLimpiarFiltros" class="btn btn-secondary">
                    <i class="fas fa-eraser"></i> Limpiar Filtros
                </button>
            </div>
        </div>
    </div>
    
    <!-- Tabla de Auditoría -->
    <div class="table-responsive">
        <table id="tablaAuditoria" class="table table-hover">
            <thead>
                <tr>
                    <th>Usuario & Fecha</th>
                    <th>Acción & Tabla</th>
                    <th>Cambios Realizados</th>
                    <th class="text-center">Detalles</th>
                </tr>
            </thead>
            <tbody>
                <!-- Cargado dinámicamente -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Detalle -->
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt"></i> Detalle Completo de Auditoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modalDetalleContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../../includes/footer.php';
?>
