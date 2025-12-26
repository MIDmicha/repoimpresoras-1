<?php
// Cargar configuración antes de session_start
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/../../includes/functions.php';

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación y rol de administrador
if (!isLoggedIn()) {
    redirect('controllers/auth.php');
}

if (!hasRole(ROL_ADMIN)) {
    setFlashMessage('danger', 'No tiene permisos para acceder a esta sección');
    redirect('views/dashboard.php');
}

$page_title = 'Gestión de Usuarios';

$database = new Database();
$db = $database->getConnection();
$usuarioModel = new Usuario($db);

// Obtener todos los usuarios
$usuarios = $usuarioModel->getAll();

include __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-users"></i> Gestión de Usuarios</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUsuario">
        <i class="fas fa-plus"></i> Nuevo Usuario
    </button>
</div>

<!-- Filtros -->
<div class="content-card mb-4">
    <div class="row">
        <div class="col-md-4">
            <label class="form-label">Buscar</label>
            <input type="text" class="form-control" id="searchInput" placeholder="Buscar por nombre o usuario...">
        </div>
        <div class="col-md-3">
            <label class="form-label">Estado</label>
            <select class="form-select" id="filterEstado">
                <option value="">Todos</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Rol</label>
            <select class="form-select" id="filterRol">
                <option value="">Todos</option>
                <option value="1">Administrador</option>
                <option value="2">Encargado</option>
                <option value="3">Usuario</option>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-secondary w-100" onclick="limpiarFiltros()">
                <i class="fas fa-times"></i> Limpiar
            </button>
        </div>
    </div>
</div>

<!-- Tabla de usuarios -->
<div class="content-card">
    <div class="table-responsive">
        <table class="table table-hover data-table" id="tablaUsuarios">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Nombre Completo</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Último Acceso</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?php echo $usuario['id']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($usuario['username']); ?></strong>
                    </td>
                    <td><?php echo htmlspecialchars($usuario['nombre_completo']); ?></td>
                    <td>
                        <?php if ($usuario['email']): ?>
                            <a href="mailto:<?php echo $usuario['email']; ?>">
                                <?php echo htmlspecialchars($usuario['email']); ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $usuario['telefono'] ? htmlspecialchars($usuario['telefono']) : '-'; ?></td>
                    <td>
                        <?php
                        $rolClass = '';
                        switch($usuario['id_rol']) {
                            case ROL_ADMIN:
                                $rolClass = 'bg-danger';
                                break;
                            case ROL_ENCARGADO:
                                $rolClass = 'bg-warning';
                                break;
                            default:
                                $rolClass = 'bg-info';
                        }
                        ?>
                        <span class="badge <?php echo $rolClass; ?>">
                            <?php echo htmlspecialchars($usuario['rol_nombre']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($usuario['activo']): ?>
                            <span class="badge bg-success">
                                <i class="fas fa-check-circle"></i> Activo
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary">
                                <i class="fas fa-times-circle"></i> Inactivo
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php 
                        if ($usuario['ultimo_acceso']) {
                            echo formatDateTime($usuario['ultimo_acceso']);
                        } else {
                            echo '<span class="text-muted">Nunca</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-info" 
                                    onclick="verUsuario(<?php echo $usuario['id']; ?>)"
                                    title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-warning" 
                                    onclick="editarUsuario(<?php echo $usuario['id']; ?>)"
                                    title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php if ($usuario['id'] != $_SESSION['user_id']): ?>
                            <button type="button" class="btn btn-sm btn-<?php echo $usuario['activo'] ? 'secondary' : 'success'; ?>" 
                                    onclick="toggleEstado(<?php echo $usuario['id']; ?>, <?php echo $usuario['activo'] ? 0 : 1; ?>)"
                                    title="<?php echo $usuario['activo'] ? 'Desactivar' : 'Activar'; ?>">
                                <i class="fas fa-<?php echo $usuario['activo'] ? 'ban' : 'check'; ?>"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" 
                                    onclick="eliminarUsuario(<?php echo $usuario['id']; ?>)"
                                    title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Crear/Editar Usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUsuarioLabel">
                    <i class="fas fa-user-plus"></i> Nuevo Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formUsuario" method="POST" action="<?php echo BASE_URL; ?>/controllers/usuarios.php" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="create" id="formAction">
                <input type="hidden" name="id" id="usuarioId">
                
                <div class="modal-body">
                    <div class="row">
                        <!-- Usuario -->
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Usuario *</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                            <div class="invalid-feedback">Por favor ingrese un usuario</div>
                        </div>

                        <!-- Nombre Completo -->
                        <div class="col-md-6 mb-3">
                            <label for="nombre_completo" class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" required>
                            <div class="invalid-feedback">Por favor ingrese el nombre completo</div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>

                        <!-- Teléfono -->
                        <div class="col-md-6 mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono">
                        </div>

                        <!-- Rol -->
                        <div class="col-md-6 mb-3">
                            <label for="id_rol" class="form-label">Rol *</label>
                            <select class="form-select" id="id_rol" name="id_rol" required>
                                <option value="">Seleccione...</option>
                                <option value="<?php echo ROL_ADMIN; ?>">Administrador</option>
                                <option value="<?php echo ROL_ENCARGADO; ?>">Encargado</option>
                                <option value="<?php echo ROL_USUARIO; ?>">Usuario</option>
                            </select>
                            <div class="invalid-feedback">Por favor seleccione un rol</div>
                        </div>

                        <!-- Estado -->
                        <div class="col-md-6 mb-3">
                            <label for="activo" class="form-label">Estado *</label>
                            <select class="form-select" id="activo" name="activo" required>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>

                        <!-- Contraseña -->
                        <div class="col-md-6 mb-3" id="passwordGroup">
                            <label for="password" class="form-label">
                                Contraseña <span id="passwordRequired">*</span>
                            </label>
                            <input type="password" class="form-control" id="password" name="password">
                            <div class="invalid-feedback">Por favor ingrese una contraseña</div>
                            <small class="text-muted">Mínimo 6 caracteres</small>
                        </div>

                        <!-- Confirmar Contraseña -->
                        <div class="col-md-6 mb-3" id="confirmPasswordGroup">
                            <label for="password_confirm" class="form-label">
                                Confirmar Contraseña <span id="confirmRequired">*</span>
                            </label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm">
                            <div class="invalid-feedback">Las contraseñas no coinciden</div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver Detalles -->
<div class="modal fade" id="modalVerUsuario" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-circle"></i> Perfil del Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalVerUsuarioContent">
                <!-- Se cargará dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
const BASE_URL_JS = '<?php echo BASE_URL; ?>';
let dataTable;

$(document).ready(function() {
    // Inicializar DataTable
    dataTable = DataTableManager.init('#tablaUsuarios', {
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: [8] }
        ]
    });
    
    // Filtro de búsqueda
    $('#searchInput').on('keyup', function() {
        dataTable.search(this.value).draw();
    });
    
    // Filtro por estado
    $('#filterEstado').on('change', function() {
        const value = this.value;
        if (value === '') {
            dataTable.column(6).search('').draw();
        } else {
            const text = value === '1' ? 'Activo' : 'Inactivo';
            dataTable.column(6).search(text).draw();
        }
    });
    
    // Filtro por rol
    $('#filterRol').on('change', function() {
        const value = this.value;
        if (value === '') {
            dataTable.column(5).search('').draw();
        } else {
            const roles = {
                '1': 'Administrador',
                '2': 'Encargado',
                '3': 'Usuario'
            };
            dataTable.column(5).search(roles[value]).draw();
        }
    });
    
    // Validación de contraseñas
    $('#password_confirm').on('keyup', function() {
        const password = $('#password').val();
        const confirm = $(this).val();
        
        if (password !== confirm) {
            this.setCustomValidity('Las contraseñas no coinciden');
        } else {
            this.setCustomValidity('');
        }
    });
    
    // Reset form al cerrar modal
    $('#modalUsuario').on('hidden.bs.modal', function() {
        $('#formUsuario')[0].reset();
        $('#formUsuario').removeClass('was-validated');
        $('#formAction').val('create');
        $('#modalUsuarioLabel').html('<i class="fas fa-user-plus"></i> Nuevo Usuario');
        $('#password').attr('required', true);
        $('#password_confirm').attr('required', true);
        $('#passwordRequired, #confirmRequired').show();
    });
});

function limpiarFiltros() {
    $('#searchInput').val('');
    $('#filterEstado').val('');
    $('#filterRol').val('');
    dataTable.search('').columns().search('').draw();
}

function editarUsuario(id) {
    $.get(BASE_URL_JS + '/controllers/usuarios.php', {
        action: 'get',
        id: id
    }, function(response) {
        const usuario = JSON.parse(response);
        
        $('#formAction').val('update');
        $('#usuarioId').val(usuario.id);
        $('#username').val(usuario.username);
        $('#nombre_completo').val(usuario.nombre_completo);
        $('#email').val(usuario.email);
        $('#telefono').val(usuario.telefono);
        $('#id_rol').val(usuario.id_rol);
        $('#activo').val(usuario.activo);
        
        // Hacer contraseña opcional en edición
        $('#password').attr('required', false);
        $('#password_confirm').attr('required', false);
        $('#passwordRequired, #confirmRequired').hide();
        
        $('#modalUsuarioLabel').html('<i class="fas fa-user-edit"></i> Editar Usuario');
        $('#modalUsuario').modal('show');
    });
}

function verUsuario(id) {
    console.log('Solicitando perfil para usuario ID:', id);
    console.log('URL:', BASE_URL_JS + '/controllers/usuarios.php?action=getPerfil&id=' + id);
    
    $.get(BASE_URL_JS + '/controllers/usuarios.php', {
        action: 'getPerfil',
        id: id
    }, function(response) {
        console.log('Respuesta recibida:', response);
        console.log('Tipo de respuesta:', typeof response);
        
        try {
            // Si ya es un objeto, no parsear
            const data = typeof response === 'string' ? JSON.parse(response) : response;
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            const usuario = data.usuario;
            const stats = data.estadisticas;
        
        const html = `
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
                <!-- Panel Izquierdo -->
                <div>
                    <!-- Avatar -->
                    <div style="border: 2px solid #000; padding: 20px; text-align: center; margin-bottom: 15px;">
                        <div style="width: 100px; height: 100px; border-radius: 50%; background: #007bff; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; color: white; font-size: 2.5em; font-weight: bold;">
                            ${usuario.nombre_completo.charAt(0).toUpperCase()}
                        </div>
                        <h6 style="margin-bottom: 5px;">${usuario.nombre_completo}</h6>
                        <p style="color: #666; margin-bottom: 5px;">@${usuario.username}</p>
                        <span style="background: ${usuario.id_rol == 1 ? '#dc3545' : (usuario.id_rol == 2 ? '#ffc107' : '#17a2b8')}; color: white; padding: 5px 10px; display: inline-block; border-radius: 3px; font-size: 0.85em;">
                            ${usuario.rol_nombre}
                        </span>
                    </div>
                    
                    <!-- Estado -->
                    <div style="border: 2px solid #000; padding: 15px; margin-bottom: 15px; text-align: center;">
                        <div style="font-size: 0.9em; color: #666; margin-bottom: 5px;">Estado del Usuario</div>
                        <span style="background: ${usuario.activo ? '#28a745' : '#6c757d'}; color: white; padding: 8px 15px; display: inline-block; border-radius: 5px; font-weight: bold;">
                            ${usuario.activo ? 'ACTIVO' : 'INACTIVO'}
                        </span>
                    </div>
                    
                    <!-- Estadísticas -->
                    <div style="border: 2px solid #000; padding: 15px;">
                        <h6 style="margin-bottom: 15px; border-bottom: 1px solid #000; padding-bottom: 10px;">
                            <i class="fas fa-chart-line"></i> Estadísticas
                        </h6>
                        
                        <div style="margin-bottom: 15px; text-align: center;">
                            <div style="font-size: 0.85em; color: #666; margin-bottom: 5px;">Total Acciones</div>
                            <div style="font-size: 1.8em; font-weight: bold; color: #007bff;">
                                ${stats.total_acciones || 0}
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr; gap: 8px;">
                            <div style="text-align: center; padding: 8px; border: 1px solid #28a745; background: #d4edda;">
                                <div style="font-weight: bold; color: #28a745;">${stats.creaciones || 0}</div>
                                <div style="font-size: 0.75em; color: #155724;">Creaciones</div>
                            </div>
                            
                            <div style="text-align: center; padding: 8px; border: 1px solid #ffc107; background: #fff3cd;">
                                <div style="font-weight: bold; color: #856404;">${stats.modificaciones || 0}</div>
                                <div style="font-size: 0.75em; color: #856404;">Modificaciones</div>
                            </div>
                            
                            <div style="text-align: center; padding: 8px; border: 1px solid #dc3545; background: #f8d7da;">
                                <div style="font-weight: bold; color: #dc3545;">${stats.eliminaciones || 0}</div>
                                <div style="font-size: 0.75em; color: #721c24;">Eliminaciones</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Panel Derecho -->
                <div>
                    <!-- Información de Contacto -->
                    <div style="border: 2px solid #000; padding: 20px; margin-bottom: 15px;">
                        <h6 style="margin-bottom: 15px; border-bottom: 1px solid #000; padding-bottom: 10px;">
                            <i class="fas fa-address-card"></i> Información de Contacto
                        </h6>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <div style="font-size: 0.85em; color: #666; margin-bottom: 5px;">
                                    <i class="fas fa-envelope"></i> Email
                                </div>
                                <div style="font-weight: bold;">
                                    ${usuario.email || '<em style="color: #999;">No especificado</em>'}
                                </div>
                            </div>
                            
                            <div>
                                <div style="font-size: 0.85em; color: #666; margin-bottom: 5px;">
                                    <i class="fas fa-phone"></i> Teléfono
                                </div>
                                <div style="font-weight: bold;">
                                    ${usuario.telefono || '<em style="color: #999;">No especificado</em>'}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información del Sistema -->
                    <div style="border: 2px solid #000; padding: 20px; margin-bottom: 15px;">
                        <h6 style="margin-bottom: 15px; border-bottom: 1px solid #000; padding-bottom: 10px;">
                            <i class="fas fa-info-circle"></i> Información del Sistema
                        </h6>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <div style="font-size: 0.85em; color: #666; margin-bottom: 5px;">
                                    <i class="fas fa-calendar-plus"></i> Fecha de Creación
                                </div>
                                <div style="font-weight: bold;">
                                    ${new Date(usuario.fecha_creacion).toLocaleDateString('es-ES', {
                                        day: '2-digit',
                                        month: 'long',
                                        year: 'numeric'
                                    })}
                                </div>
                            </div>
                            
                            <div>
                                <div style="font-size: 0.85em; color: #666; margin-bottom: 5px;">
                                    <i class="fas fa-clock"></i> Último Acceso
                                </div>
                                <div style="font-weight: bold;">
                                    ${usuario.ultimo_acceso ? new Date(usuario.ultimo_acceso).toLocaleString('es-ES') : '<em style="color: #999;">Nunca</em>'}
                                </div>
                            </div>
                            
                            <div>
                                <div style="font-size: 0.85em; color: #666; margin-bottom: 5px;">
                                    <i class="fas fa-edit"></i> Última Actualización
                                </div>
                                <div style="font-weight: bold;">
                                    ${new Date(usuario.fecha_actualizacion).toLocaleString('es-ES')}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actividad Reciente -->
                    <div style="border: 2px solid #000; padding: 20px;">
                        <h6 style="margin-bottom: 15px; border-bottom: 1px solid #000; padding-bottom: 10px;">
                            <i class="fas fa-history"></i> Actividad Reciente
                        </h6>
                        
                        <div style="max-height: 250px; overflow-y: auto;">
                            ${data.actividad.length > 0 ? data.actividad.map(item => {
                                const color = item.accion === 'INSERT' ? '#28a745' : 
                                            item.accion === 'UPDATE' ? '#ffc107' : '#dc3545';
                                const icono = item.accion === 'INSERT' ? 'fa-plus-circle' : 
                                            item.accion === 'UPDATE' ? 'fa-edit' : 'fa-trash-alt';
                                const texto = item.accion === 'INSERT' ? 'Creó' : 
                                            item.accion === 'UPDATE' ? 'Modificó' : 'Eliminó';
                                
                                const tiempo = Math.floor((Date.now() - new Date(item.fecha_hora)) / 1000);
                                let tiempoTexto;
                                if (tiempo < 60) tiempoTexto = `Hace ${tiempo} seg`;
                                else if (tiempo < 3600) tiempoTexto = `Hace ${Math.floor(tiempo / 60)} min`;
                                else if (tiempo < 86400) tiempoTexto = `Hace ${Math.floor(tiempo / 3600)} h`;
                                else tiempoTexto = new Date(item.fecha_hora).toLocaleDateString('es-ES');
                                
                                return `
                                    <div style="padding: 10px; margin-bottom: 8px; border-left: 3px solid ${color}; background: #f8f9fa;">
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 3px;">
                                            <i class="fas ${icono}" style="color: ${color};"></i>
                                            <strong style="font-size: 0.9em;">${texto}</strong>
                                        </div>
                                        <div style="font-size: 0.85em; color: #666; margin-left: 20px;">
                                            en <strong>${item.tabla}</strong> (ID: ${item.id_registro})
                                        </div>
                                        <div style="font-size: 0.75em; color: #999; margin-left: 20px;">
                                            <i class="fas fa-clock"></i> ${tiempoTexto}
                                        </div>
                                    </div>
                                `;
                            }).join('') : '<p style="text-align: center; color: #999;">Sin actividad reciente</p>'}
                        </div>
                        
                        <div style="margin-top: 15px; text-align: center;">
                            <a href="${BASE_URL_JS}/views/auditoria/timeline.php?usuario=${usuario.id}" 
                               style="text-decoration: none; color: #007bff;">
                                <i class="fas fa-stream"></i> Ver timeline completo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#modalVerUsuarioContent').html(html);
        $('#modalVerUsuario').modal('show');
        
        } catch(error) {
            console.error('Error al parsear respuesta:', error);
            console.log('Respuesta recibida:', response);
            alert('Error al cargar el perfil del usuario');
        }
    }).fail(function(xhr, status, error) {
        console.error('Error en la petición:', error);
        console.log('Estado:', status);
        console.log('Respuesta:', xhr.responseText);
        alert('Error al cargar el perfil: ' + error);
    });
}

function toggleEstado(id, nuevoEstado) {
    const mensaje = nuevoEstado == 1 ? 'activar' : 'desactivar';
    
    if (confirm(`¿Está seguro de ${mensaje} este usuario?`)) {
        $.post(BASE_URL_JS + '/controllers/usuarios.php', {
            action: 'toggle_estado',
            id: id,
            activo: nuevoEstado
        }, function(response) {
            location.reload();
        });
    }
}

function eliminarUsuario(id) {
    Swal.fire({
        icon: 'warning',
        title: '¿Está seguro?',
        text: '¿Desea eliminar este usuario? Esta acción no se puede deshacer.',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(BASE_URL_JS + '/controllers/usuarios.php', {
                action: 'delete',
                id: id
            })
            .done(function(response) {
                const data = JSON.parse(response);
                Swal.fire({
                    icon: 'success',
                    title: '¡Eliminado!',
                    text: data.message || 'Usuario eliminado exitosamente',
                    confirmButtonColor: '#198754'
                }).then(() => {
                    location.reload();
                });
            })
            .fail(function(xhr) {
                const error = xhr.responseJSON || {error: 'Error al eliminar el usuario'};
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.error,
                    confirmButtonColor: '#dc3545'
                });
            });
        }
    });
}
</script>

<?php
include __DIR__ . '/../../includes/footer.php';
?>
