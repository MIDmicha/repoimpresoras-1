<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Mantenimiento.php';
require_once __DIR__ . '/../../models/Equipo.php';
require_once __DIR__ . '/../../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isLoggedIn()) {
    redirect('controllers/auth.php');
}

$page_title = 'Gestión de Mantenimientos';

$database = new Database();
$db = $database->getConnection();
$mantenimientoModel = new Mantenimiento($db);
$equipoModel = new Equipo($db);

// Obtener datos
$mantenimientos = $mantenimientoModel->getAll();
$equipos = $equipoModel->getAll();

// Obtener tipos de demanda y estados
$stmt = $db->query("SELECT * FROM tipos_demanda WHERE activo = 1");
$tipos_demanda = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->query("SELECT * FROM estados_equipo WHERE activo = 1");
$estados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Definir JavaScript ANTES de cargar el header
$extra_js = <<<'EOD'
<script>
$(document).ready(function() {
    // Establecer fecha actual
    $('#fecha_mantenimiento').val(new Date().toISOString().split('T')[0]);
    
    // Filtros de equipos
    $('#searchEquipo').on('keyup', function() {
        filtrarEquipos();
    });
    
    $('#filterEstado').on('change', function() {
        filtrarEquipos();
    });
    
    // Aplicar colores a los badges de estado
    aplicarColoresEstados();
    
    // Envío del formulario
    $('#formMantenimiento').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.message,
                        confirmButtonColor: '#0d6efd'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message,
                        confirmButtonColor: '#dc3545'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al procesar la solicitud',
                    confirmButtonColor: '#dc3545'
                });
            }
        });
    });
});

function filtrarEquipos() {
    const searchText = $('#searchEquipo').val().toLowerCase();
    const estadoId = $('#filterEstado').val();
    
    $('.equipo-card').each(function() {
        const codigo = $(this).data('codigo').toLowerCase();
        const marca = $(this).data('marca').toLowerCase();
        const modelo = $(this).data('modelo').toLowerCase();
        const equipoEstadoId = $(this).data('estado-id').toString();
        
        const matchSearch = searchText === '' || 
                          codigo.includes(searchText) || 
                          marca.includes(searchText) || 
                          modelo.includes(searchText);
        
        const matchEstado = estadoId === '' || equipoEstadoId === estadoId;
        
        if (matchSearch && matchEstado) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

function limpiarFiltrosEquipos() {
    $('#searchEquipo').val('');
    $('#filterEstado').val('');
    filtrarEquipos();
}

function seleccionarEquipo(id, codigo, descripcion, estadoId) {
    // Obtener el nombre del estado
    const estadoNombre = $(`.equipo-card[data-id="${id}"]`).data('estado');
    
    // Rellenar el modal con la información del equipo
    $('#id_equipo').val(id);
    $('#equipoCodigo').text(codigo);
    $('#equipoDescripcion').text(descripcion);
    $('#equipoEstadoActual').html(`<span class="estado-badge" data-estado-id="${estadoId}">${estadoNombre}</span>`);
    
    // Pre-seleccionar el estado anterior
    $('#id_estado_anterior').val(estadoId);
    
    // Aplicar color al badge en el modal
    aplicarColoresEstados();
    
    // Limpiar los demás campos
    $('#id_tipo_demanda').val('');
    $('#tecnico_responsable').val('');
    $('#id_estado_nuevo').val('');
    $('#descripcion').val('');
    $('#observaciones').val('');
    $('#fecha_mantenimiento').val(new Date().toISOString().split('T')[0]);
    
    // Abrir modal
    $('#modalMantenimiento').modal('show');
}

function aplicarColoresEstados() {
    $('.estado-badge').each(function() {
        const estadoId = $(this).data('estado-id');
        let color, bgColor;
        
        // Detectar si estamos en modo oscuro
        const isDarkMode = document.documentElement.getAttribute('data-theme') === 'dark';
        
        // Asignar colores según el estado y el tema
        switch(estadoId) {
            case 1: // Operativo
                if (isDarkMode) {
                    color = '#6ee7b7';
                    bgColor = 'rgba(16, 185, 129, 0.2)';
                } else {
                    color = '#0f5132';
                    bgColor = '#d1e7dd';
                }
                break;
            case 2: // Mantenimiento
                if (isDarkMode) {
                    color = '#fcd34d';
                    bgColor = 'rgba(245, 158, 11, 0.2)';
                } else {
                    color = '#664d03';
                    bgColor = '#fff3cd';
                }
                break;
            case 3: // Inoperativo
                if (isDarkMode) {
                    color = '#fca5a5';
                    bgColor = 'rgba(239, 68, 68, 0.2)';
                } else {
                    color = '#842029';
                    bgColor = '#f8d7da';
                }
                break;
            case 4: // En Reparación
                if (isDarkMode) {
                    color = '#67e8f9';
                    bgColor = 'rgba(59, 130, 246, 0.2)';
                } else {
                    color = '#055160';
                    bgColor = '#cff4fc';
                }
                break;
            default:
                if (isDarkMode) {
                    color = '#cbd5e1';
                    bgColor = 'rgba(100, 116, 139, 0.2)';
                } else {
                    color = '#41464b';
                    bgColor = '#e2e3e5';
                }
        }
        
        $(this).css({
            'color': color,
            'background-color': bgColor
        });
    });
}

function verHistorial() {
    $('#modalHistorial').modal('show');
    
    if (!$.fn.DataTable.isDataTable('#tablaHistorial')) {
        $.ajax({
            url: BASE_URL + '/controllers/mantenimientos.php',
            method: 'GET',
            data: { action: 'getAll' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const tbody = $('#tablaHistorial tbody');
                    tbody.empty();
                    
                    response.data.forEach(function(mant) {
                        const estadoAnterior = mant.estado_anterior ? 
                            `<span class="estado-badge" data-estado-id="${mant.id_estado_anterior}">${mant.estado_anterior}</span>` : '-';
                        const estadoNuevo = mant.estado_nuevo ? 
                            `<span class="estado-badge" data-estado-id="${mant.id_estado_nuevo}">${mant.estado_nuevo}</span>` : '-';
                        
                        const row = `
                            <tr>
                                <td>${new Date(mant.fecha_mantenimiento).toLocaleDateString('es-ES')}</td>
                                <td>
                                    <strong>${mant.codigo_patrimonial}</strong><br>
                                    <small>${mant.marca} ${mant.modelo}</small>
                                </td>
                                <td>${mant.tipo_demanda}</td>
                                <td>${mant.tecnico_responsable || '-'}</td>
                                <td>${estadoAnterior}</td>
                                <td>${estadoNuevo}</td>
                                <td>
                                    <button onclick="verMantenimiento(${mant.id})" title="Ver" style="padding: 5px 10px; margin: 2px; background-color: #0d6efd; color: white; border: none; border-radius: 4px;">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="eliminarMantenimiento(${mant.id})" title="Eliminar" style="padding: 5px 10px; margin: 2px; background-color: #fd0d0dff; color: white; border: none; border-radius: 4px;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                    
                    $('#tablaHistorial').DataTable({
                        language: {
                            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                        },
                        order: [[0, 'desc']],
                        pageLength: 25
                    });
                    
                    aplicarColoresEstados();
                }
            }
        });
    }
}

function verMantenimiento(id) {
    $.ajax({
        url: BASE_URL + '/controllers/mantenimientos.php',
        method: 'GET',
        data: { action: 'get', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const mant = response.data;
                
                const estadoAnterior = mant.estado_anterior ? 
                    `<span class="estado-badge" data-estado-id="${mant.id_estado_anterior}">${mant.estado_anterior}</span>` : '-';
                const estadoNuevo = mant.estado_nuevo ? 
                    `<span class="estado-badge" data-estado-id="${mant.id_estado_nuevo}">${mant.estado_nuevo}</span>` : '-';
                
                let html = `
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Equipo:</label>
                            <p><strong>${mant.codigo_patrimonial}</strong> - ${mant.marca} ${mant.modelo}</p>
                        </div>
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Tipo de Demanda:</label>
                            <p>${mant.tipo_demanda}</p>
                        </div>
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Fecha:</label>
                            <p>${new Date(mant.fecha_mantenimiento).toLocaleDateString('es-ES')}</p>
                        </div>
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Técnico:</label>
                            <p>${mant.tecnico_responsable || '-'}</p>
                        </div>
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Estado Anterior:</label>
                            <p>${estadoAnterior}</p>
                        </div>
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Estado Nuevo:</label>
                            <p>${estadoNuevo}</p>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Descripción:</label>
                            <p>${mant.descripcion || '-'}</p>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Observaciones:</label>
                            <p>${mant.observaciones || '-'}</p>
                        </div>
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Registrado por:</label>
                            <p>${mant.usuario_registro || '-'}</p>
                        </div>
                        <div>
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Fecha de Registro:</label>
                            <p>${new Date(mant.fecha_registro).toLocaleString('es-ES')}</p>
                        </div>
                    </div>
                `;
                $('#detallesMantenimiento').html(html);
                aplicarColoresEstados();
                $('#modalVerMantenimiento').modal('show');
            }
        }
    });
}

function eliminarMantenimiento(id) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción no se puede revertir",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: BASE_URL + '/controllers/mantenimientos.php',
                method: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado',
                            text: response.message,
                            confirmButtonColor: '#0d6efd'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message,
                            confirmButtonColor: '#dc3545'
                        });
                    }
                }
            });
        }
    });
}

function verDetallesEquipo(equipoId, codigo) {
    $('#equipoCodigoDetalle').text(codigo);
    $('#modalDetallesEquipo').modal('show');
    
    // Cargar historial de mantenimientos del equipo
    $.ajax({
        url: BASE_URL + '/controllers/mantenimientos.php',
        method: 'GET',
        data: { action: 'getByEquipo', id_equipo: equipoId },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data.length > 0) {
                let html = '<table style="width: 100%; border-collapse: collapse;">';
                html += '<thead><tr style="border-bottom: 2px solid #ddd;">';
                html += '<th style="padding: 10px; text-align: left;">Fecha</th>';
                html += '<th style="padding: 10px; text-align: left;">Tipo Demanda</th>';
                html += '<th style="padding: 10px; text-align: left;">Técnico</th>';
                html += '<th style="padding: 10px; text-align: left;">Estado Anterior</th>';
                html += '<th style="padding: 10px; text-align: left;">Estado Nuevo</th>';
                html += '<th style="padding: 10px; text-align: center;">Acciones</th>';
                html += '</tr></thead><tbody>';
                
                response.data.forEach(function(mant) {
                    const estadoAnterior = mant.estado_anterior ? 
                        `<span class="estado-badge" data-estado-id="${mant.id_estado_anterior}">${mant.estado_anterior}</span>` : '-';
                    const estadoNuevo = mant.estado_nuevo ? 
                        `<span class="estado-badge" data-estado-id="${mant.id_estado_nuevo}">${mant.estado_nuevo}</span>` : '-';
                    
                    html += '<tr style="border-bottom: 1px solid #eee;">';
                    html += `<td style="padding: 10px;">${new Date(mant.fecha_mantenimiento).toLocaleDateString('es-ES')}</td>`;
                    html += `<td style="padding: 10px;">${mant.tipo_demanda}</td>`;
                    html += `<td style="padding: 10px;">${mant.tecnico_responsable || '-'}</td>`;
                    html += `<td style="padding: 10px;">${estadoAnterior}</td>`;
                    html += `<td style="padding: 10px;">${estadoNuevo}</td>`;
                    html += `<td style="padding: 10px; text-align: center;">
                        <button onclick="verMantenimiento(${mant.id})" style="padding: 5px 10px; margin: 2px;">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>`;
                    html += '</tr>';
                });
                
                html += '</tbody></table>';
                $('#historialEquipo').html(html);
                aplicarColoresEstados();
            } else {
                $('#historialEquipo').html('<p style="text-align: center; color: #999; padding: 20px;">No hay mantenimientos registrados para este equipo</p>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar historial:', xhr.responseText);
            let mensaje = 'Error al cargar el historial';
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.message) {
                    mensaje += ': ' + response.message;
                }
            } catch(e) {
                mensaje += ': ' + error;
            }
            $('#historialEquipo').html(`<p style="text-align: center; color: #dc3545; padding: 20px;">${mensaje}</p>`);
        }
    });
}

function cambiarEstadoEquipo(equipoId, codigo, estadoActualId) {
    $('#equipoIdEstado').val(equipoId);
    $('#equipoCodigoEstado').text(codigo);
    
    // Obtener nombre del estado actual
    const estadoNombre = $(`.equipo-card[data-id="${equipoId}"]`).data('estado');
    $('#estadoActualBadge').html(`<span class="estado-badge" data-estado-id="${estadoActualId}">${estadoNombre}</span>`);
    aplicarColoresEstados();
    
    $('#nuevoEstado').val('');
    $('#observacionEstado').val('');
    $('#modalCambiarEstado').modal('show');
}

// Enviar cambio de estado
$('#formCambiarEstado').on('submit', function(e) {
    e.preventDefault();
    
    const equipoId = $('#equipoIdEstado').val();
    const nuevoEstadoId = $('#nuevoEstado').val();
    const observacion = $('#observacionEstado').val();
    
    if (!nuevoEstadoId) {
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: 'Debe seleccionar un nuevo estado',
            confirmButtonColor: '#0d6efd'
        });
        return;
    }
    
    $.ajax({
        url: BASE_URL + '/controllers/equipos.php',
        method: 'POST',
        data: {
            action: 'cambiarEstado',
            id: equipoId,
            id_estado: nuevoEstadoId,
            observacion: observacion
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: 'Estado actualizado correctamente',
                    confirmButtonColor: '#0d6efd'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Error al cambiar el estado',
                    confirmButtonColor: '#dc3545'
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al procesar la solicitud',
                confirmButtonColor: '#dc3545'
            });
        }
    });
});
</script>
EOD;

include __DIR__ . '/../../includes/header.php';
?>

<style>
/* Reset básico (no afecta tu global) */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', 'Segoe UI', Roboto, sans-serif;
  transition: background 0.3s, color 0.3s;
}

/* ==================== ESTILO BASE (hereda variables globales) ==================== */
.content-card {
  background: var(--bg-card);
  border-radius: var(--border-radius);
  padding: 24px;
  box-shadow: var(--shadow);
  border: 1px solid var(--border-color);
  margin: 40px auto;
  max-width: 1400px;
}

.content-card > div:first-child {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
}

.content-card h4 {
  font-size: 1.8rem;
  font-weight: 600;
  color: var(--text-primary);
  display: flex;
  align-items: center;
  gap: 12px;
}

#btnHistorial {
  padding: 10px 20px;
  background: var(--bg-hover);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius-sm);
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  transition: var(--transition);
}

#btnHistorial:hover {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

/* Filtros */
.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 24px;
  margin-bottom: 48px;
}

.filter-group {
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
}

.filter-group label {
  font-size: 0.95rem;
  font-weight: 500;
  margin-bottom: 8px;
  color: var(--text-secondary);
}

input[type="text"], select {
  padding: 12px 16px;
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius-sm);
  font-size: 1rem;
  background: var(--bg-input);
  color: var(--text-primary);
  transition: var(--transition);
}

input[type="text"]:focus, select:focus {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
}

.btn-clear {
  padding: 12px;
  background: var(--bg-hover);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius-sm);
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: var(--transition);
}

.btn-clear:hover {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

/* Grid de tarjetas */
#equiposGrid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 28px;
}

/* Tarjeta */
.equipo-card {
  background: var(--bg-card);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow);
  padding: 28px;
  text-align: center;
  cursor: pointer;
  transition: transform 0.25s ease, box-shadow 0.25s ease;
  border: 1px solid var(--border-color);
}

.equipo-card:hover {
  transform: translateY(-8px);
  box-shadow: var(--shadow-lg);
}

.equipo-card img {
  width: 110px;
  height: 110px;
  object-fit: contain;
  border-radius: var(--border-radius-sm);
  margin-bottom: 20px;
}

.equipo-card i.fas.fa-print {
  font-size: 70px;
  color: var(--text-muted);
  margin-bottom: 20px;
}

.equipo-card .codigo {
  font-size: 1.35rem;
  font-weight: 600;
  margin-bottom: 10px;
  color: var(--text-primary);
}

.equipo-card .descripcion {
  font-size: 1rem;
  color: var(--text-secondary);
  margin-bottom: 20px;
}

.estado-badge {
  padding: 8px 20px;
  border-radius: 999px;
  font-size: 0.9rem;
  font-weight: 500;
  margin-bottom: 20px;
  display: inline-block;
  border: 1px solid var(--border-color);
}

/* Colores de estado (adaptados a tema) */
.estado-badge[data-estado-id="1"] { background: var(--success-color); color: white; }
.estado-badge[data-estado-id="2"] { background: var(--warning-color); color: white; }
.estado-badge[data-estado-id="3"] { background: var(--danger-color); color: white; }
.estado-badge[data-estado-id="4"] { background: var(--success-color); color: white; }

.actions {
  display: flex;
  gap: 16px;
  justify-content: center;
}

.btn-action {
  padding: 10px 20px;
  border: none;
  border-radius: var(--border-radius-sm);
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  transition: var(--transition);
}

.btn-details {
  background: var(--primary-color);
  color: white;
}

.btn-details:hover {
  background: var(--secondary-color);
}

.btn-status {
  background: var(--text-muted);
  color: white;
}

.btn-status:hover {
  background: var(--text-secondary);
}

/* Modales */
.modal-content {
  background: var(--bg-card);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-xl);
}

.modal-title {
  color: var(--text-primary);
  font-weight: 600;
}

.modal-header, .modal-footer {
  border-color: var(--border-color);
}

/* Formularios */
.form-control, .form-select {
  background: var(--bg-input);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius-sm);
}

.form-control:focus, .form-select:focus {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.25);
}

/* Tabla */
.table {
  background: var(--bg-card);
  color: var(--text-primary);
}

.table thead {
  background: var(--bg-hover);
}

.table th, .table td {
  border-color: var(--border-color);
}

.table tbody tr:hover {
  background: var(--bg-hover);
}


/* ==================== cambios cristhian ==================== */








.content-card {
    background: var(--bg-card);
    border-radius: var(--border-radius);
    padding: 24px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border-color);
    margin: 0px;
    max-width: 1400px;
}

.content-wrapper {
    padding: 0px;
}


.content-card {
    max-width: 98%;
}
</style>

<div class="content-card">
    <div>
        <h4>
            <i class="fas fa-tools"></i> Mantenimientos - Seleccione un Equipo
        </h4>
        <button onclick="verHistorial()" id="btnHistorial">
            <i class="fas fa-history"></i> Ver Historial
        </button>
    </div>

    <!-- Filtros -->
    <div class="filters-grid">
        <div class="filter-group">
            <label>Buscar</label>
            <input type="text" id="searchEquipo" placeholder="Código, marca, modelo...">
        </div>
        <div class="filter-group">
            <label>Estado</label>
            <select id="filterEstado">
                <option value="">Todos</option>
                <?php foreach ($estados as $estado): ?>
                <option value="<?php echo $estado['id']; ?>"><?php echo htmlspecialchars($estado['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group filter-group-button">
        <label>&nbsp;</label> 
                   <button onclick="limpiarFiltrosEquipos()" class="btn-clear">
                <i class="fas fa-times"></i> Limpiar
            </button>
        </div>
    </div>

    <!-- Grid de tarjetas -->
    <div id="equiposGrid">
        <?php foreach ($equipos as $equipo): ?>
        <?php
            $codigo = $equipo['codigo_patrimonial'] ?? 'Sin código';
            $marca = $equipo['marca'] ?? '';
            $modelo = $equipo['modelo'] ?? '';
            $estado = $equipo['estado'] ?? 'Sin estado';
            $id_estado = $equipo['id_estado'] ?? 0;
            $descripcion = trim($marca . ' ' . $modelo);
            if (empty($descripcion)) $descripcion = 'Sin descripción';
        ?>
        <div class="equipo-card" 
             data-id="<?php echo $equipo['id']; ?>"
             data-codigo="<?php echo htmlspecialchars($codigo); ?>"
             data-marca="<?php echo htmlspecialchars($marca); ?>"
             data-modelo="<?php echo htmlspecialchars($modelo); ?>"
             data-estado-id="<?php echo $id_estado; ?>"
             data-estado="<?php echo htmlspecialchars($estado); ?>"
             onclick="seleccionarEquipo(<?php echo $equipo['id']; ?>, '<?php echo htmlspecialchars($codigo); ?>', '<?php echo htmlspecialchars($descripcion); ?>', <?php echo $id_estado; ?>)">
            
            <div>
                <?php if (!empty($equipo['imagen'])): ?>
                    <?php $imagenUrl = (strpos($equipo['imagen'], 'uploads/') === 0) ? BASE_URL . '/' . $equipo['imagen'] : BASE_URL . '/uploads/' . $equipo['imagen']; ?>
                    <img src="<?php echo $imagenUrl; ?>" 
                         alt="<?php echo htmlspecialchars($codigo); ?>" 
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <i class="fas fa-print" style="display:none;"></i>
                <?php else: ?>
                    <i class="fas fa-print"></i>
                <?php endif; ?>
            </div>
            
            <div class="codigo"><?php echo htmlspecialchars($codigo); ?></div>
            <div class="descripcion"><?php echo htmlspecialchars($descripcion); ?></div>
            <div class="estado-badge" data-estado-id="<?php echo $id_estado; ?>">
                <?php echo htmlspecialchars($estado); ?>
            </div>
            
            <div class="actions">
                <button class="btn-action btn-details" 
                        onclick="event.stopPropagation(); verDetallesEquipo(<?php echo $equipo['id']; ?>, '<?php echo htmlspecialchars($codigo); ?>');">
                    <i class="fas fa-info-circle"></i> Detalles
                </button>
                <button class="btn-action btn-status" 
                        onclick="event.stopPropagation(); cambiarEstadoEquipo(<?php echo $equipo['id']; ?>, '<?php echo htmlspecialchars($codigo); ?>', <?php echo $id_estado; ?>);">
                    <i class="fas fa-exchange-alt"></i> Estado
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal Registrar Mantenimiento -->
<div class="modal fade" id="modalMantenimiento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-tools"></i> Registrar Mantenimiento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formMantenimiento" method="POST" action="<?php echo BASE_URL; ?>/controllers/mantenimientos.php">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="id_equipo" id="id_equipo">
                
                <div class="modal-body">
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 12px; margin-bottom: 24px;">
                        <h6 style="margin-bottom: 12px;">
                            <i class="fas fa-print"></i> Equipo Seleccionado
                        </h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                            <div><strong>Código:</strong> <span id="equipoCodigo"></span></div>
                            <div><strong>Equipo:</strong> <span id="equipoDescripcion"></span></div>
                            <div><strong>Estado Actual:</strong> <span id="equipoEstadoActual"></span></div>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                        <div>
                            <label>Tipo de Demanda <span style="color: #dc3545;">*</span></label>
                            <select class="form-select" name="id_tipo_demanda" id="id_tipo_demanda" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($tipos_demanda as $tipo): ?>
                                <option value="<?php echo $tipo['id']; ?>"><?php echo htmlspecialchars($tipo['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label>Fecha <span style="color: #dc3545;">*</span></label>
                            <input class="form-control" type="date" name="fecha_mantenimiento" id="fecha_mantenimiento" required>
                        </div>
                        
                        <div>
                            <label>Técnico Responsable</label>
                            <input class="form-control" type="text" name="tecnico_responsable" id="tecnico_responsable">
                        </div>
                        
                        <div>
                            <label>Estado Anterior</label>
                            <select class="form-select" name="id_estado_anterior" id="id_estado_anterior">
                                <option value="">Ninguno</option>
                                <?php foreach ($estados as $estado): ?>
                                <option value="<?php echo $estado['id']; ?>"><?php echo htmlspecialchars($estado['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label>Estado Nuevo</label>
                            <select class="form-select" name="id_estado_nuevo" id="id_estado_nuevo">
                                <option value="">Ninguno</option>
                                <?php foreach ($estados as $estado): ?>
                                <option value="<?php echo $estado['id']; ?>"><?php echo htmlspecialchars($estado['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div style="grid-column: 1 / -1;">
                            <label>Descripción del Mantenimiento</label>
                            <textarea class="form-control" name="descripcion" id="descripcion" rows="4" placeholder="Detalle las actividades realizadas..."></textarea>
                        </div>
                        
                        <div style="grid-column: 1 / -1;">
                            <label>Observaciones</label>
                            <textarea class="form-control" name="observaciones" id="observaciones" rows="3" placeholder="Observaciones adicionales..."></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Registrar Mantenimiento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Historial -->
<div class="modal fade" id="modalHistorial" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-history"></i> Historial de Mantenimientos
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div style="overflow-x: auto;">
                    <table class="table" id="tablaHistorial" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Equipo</th>
                                <th>Tipo Demanda</th>
                                <th>Técnico</th>
                                <th>Estado Anterior</th>
                                <th>Estado Nuevo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Detalles -->
<div class="modal fade" id="modalVerMantenimiento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Detalles del Mantenimiento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detallesMantenimiento"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalles del Equipo -->
<div class="modal fade" id="modalDetallesEquipo" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-print"></i> Detalles del Equipo: <span id="equipoCodigoDetalle"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6 style="margin-bottom: 16px;">
                    <i class="fas fa-history"></i> Historial de Mantenimientos
                </h6>
                <div id="historialEquipo" style="overflow-x: auto;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cambiar Estado -->
<div class="modal fade" id="modalCambiarEstado" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt"></i> Cambiar Estado del Equipo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCambiarEstado">
                <div class="modal-body">
                    <input type="hidden" id="equipoIdEstado">
                    
                    <div style="margin-bottom: 20px;">
                        <strong>Equipo:</strong> <span id="equipoCodigoEstado"></span>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label>Estado Actual</label>
                        <div id="estadoActualBadge"></div>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label>Nuevo Estado <span style="color: #dc3545;">*</span></label>
                        <select id="nuevoEstado" required class="form-select">
                            <option value="">Seleccione...</option>
                            <?php foreach ($estados as $estado): ?>
                            <option value="<?php echo $estado['id']; ?>"><?php echo htmlspecialchars($estado['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label>Observación</label>
                        <textarea id="observacionEstado" rows="4" class="form-control" placeholder="Motivo del cambio de estado..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambio
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../../includes/footer.php';
?>
