/**
 * Configuración del Sistema - JavaScript
 * Gestión de entidades maestras
 */

$(document).ready(function() {
    // Inicializar DataTables para todas las tablas
    $('.table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        pageLength: 10,
        order: [[0, 'asc']]
    });
});

// ==============================================
// ESTADOS DE EQUIPOS
// ==============================================

function abrirModalEstado(datos = null) {
    const esEdicion = datos !== null;
    const titulo = esEdicion ? 'Editar Estado' : 'Nuevo Estado';
    
    const modal = `
        <div class="modal fade" id="modalEstado" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${titulo}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="formEstado">
                        <input type="hidden" name="id" value="${datos?.id || ''}">
                        <input type="hidden" name="tabla" value="estados_equipo">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Nombre *</label>
                                <input type="text" class="form-control" name="nombre" value="${datos?.nombre || ''}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="descripcion" rows="2">${datos?.descripcion || ''}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Color</label>
                                <div class="color-picker-wrapper">
                                    <input type="color" name="color" value="${datos?.color || '#6c757d'}">
                                    <input type="text" class="form-control" name="color_hex" value="${datos?.color || '#6c757d'}" pattern="^#[0-9A-Fa-f]{6}$">
                                    <span class="badge badge-preview" id="colorPreview" style="background-color: ${datos?.color || '#6c757d'}; color: white;">Vista Previa</span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    mostrarModal(modal, 'formEstado');
    
    // Sincronizar color picker
    $('input[name="color"]').on('input', function() {
        const color = $(this).val();
        $('input[name="color_hex"]').val(color);
        $('#colorPreview').css('background-color', color);
    });
    
    $('input[name="color_hex"]').on('input', function() {
        const color = $(this).val();
        if (/^#[0-9A-Fa-f]{6}$/.test(color)) {
            $('input[name="color"]').val(color);
            $('#colorPreview').css('background-color', color);
        }
    });
}

function editarEstado(datos) {
    abrirModalEstado(datos);
}

// ==============================================
// DISTRITOS FISCALES
// ==============================================

function abrirModalDistrito(datos = null) {
    const esEdicion = datos !== null;
    const titulo = esEdicion ? 'Editar Distrito Fiscal' : 'Nuevo Distrito Fiscal';
    
    const modal = `
        <div class="modal fade" id="modalDistrito" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${titulo}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="formDistrito">
                        <input type="hidden" name="id" value="${datos?.id || ''}">
                        <input type="hidden" name="tabla" value="distritos_fiscales">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Código</label>
                                <input type="text" class="form-control" name="codigo" value="${datos?.codigo || ''}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nombre *</label>
                                <input type="text" class="form-control" name="nombre" value="${datos?.nombre || ''}" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    mostrarModal(modal, 'formDistrito');
}

function editarDistrito(datos) {
    abrirModalDistrito(datos);
}

// ==============================================
// SEDES
// ==============================================

function abrirModalSede(datos = null) {
    const esEdicion = datos !== null;
    const titulo = esEdicion ? 'Editar Sede' : 'Nueva Sede';
    
    // Obtener lista de distritos
    $.get(BASE_URL + '/controllers/configuracion/api.php', { action: 'getDistritos' }, function(distritos) {
        const opcionesDistritos = distritos.map(d => 
            `<option value="${d.id}" ${datos?.id_distrito == d.id ? 'selected' : ''}>${d.nombre}</option>`
        ).join('');
        
        const modal = `
            <div class="modal fade" id="modalSede" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${titulo}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="formSede">
                            <input type="hidden" name="id" value="${datos?.id || ''}">
                            <input type="hidden" name="tabla" value="sedes">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Nombre *</label>
                                    <input type="text" class="form-control" name="nombre" value="${datos?.nombre || ''}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Dirección</label>
                                    <textarea class="form-control" name="direccion" rows="2">${datos?.direccion || ''}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Distrito Fiscal</label>
                                    <select class="form-select" name="id_distrito">
                                        <option value="">Seleccione...</option>
                                        ${opcionesDistritos}
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        
        mostrarModal(modal, 'formSede');
    }, 'json');
}

function editarSede(datos) {
    abrirModalSede(datos);
}

// ==============================================
// MACRO PROCESOS
// ==============================================

function abrirModalMacro(datos = null) {
    const esEdicion = datos !== null;
    const titulo = esEdicion ? 'Editar Macro Proceso' : 'Nuevo Macro Proceso';
    
    const modal = `
        <div class="modal fade" id="modalMacro" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${titulo}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="formMacro">
                        <input type="hidden" name="id" value="${datos?.id || ''}">
                        <input type="hidden" name="tabla" value="macro_procesos">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Nombre *</label>
                                <input type="text" class="form-control" name="nombre" value="${datos?.nombre || ''}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="descripcion" rows="2">${datos?.descripcion || ''}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    mostrarModal(modal, 'formMacro');
}

function editarMacro(datos) {
    abrirModalMacro(datos);
}

// ==============================================
// DESPACHOS
// ==============================================

function abrirModalDespacho(datos = null) {
    const esEdicion = datos !== null;
    const titulo = esEdicion ? 'Editar Despacho' : 'Nuevo Despacho';
    
    // Obtener lista de sedes
    $.get(BASE_URL + '/controllers/configuracion/api.php', { action: 'getSedes' }, function(sedes) {
        const opcionesSedes = sedes.map(s => 
            `<option value="${s.id}" ${datos?.id_sede == s.id ? 'selected' : ''}>${s.nombre}</option>`
        ).join('');
        
        const modal = `
            <div class="modal fade" id="modalDespacho" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${titulo}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="formDespacho">
                            <input type="hidden" name="id" value="${datos?.id || ''}">
                            <input type="hidden" name="tabla" value="despachos">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Nombre *</label>
                                    <input type="text" class="form-control" name="nombre" value="${datos?.nombre || ''}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Sede</label>
                                    <select class="form-select" name="id_sede">
                                        <option value="">Seleccione...</option>
                                        ${opcionesSedes}
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        
        mostrarModal(modal, 'formDespacho');
    }, 'json');
}

function editarDespacho(datos) {
    abrirModalDespacho(datos);
}

// ==============================================
// USUARIOS FINALES
// ==============================================

function abrirModalUsuarioFinal(datos = null) {
    const esEdicion = datos !== null;
    const titulo = esEdicion ? 'Editar Usuario Final' : 'Nuevo Usuario Final';
    
    const modal = `
        <div class="modal fade" id="modalUsuarioFinal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${titulo}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="formUsuarioFinal">
                        <input type="hidden" name="id" value="${datos?.id || ''}">
                        <input type="hidden" name="tabla" value="usuarios_finales">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Nombre Completo *</label>
                                <input type="text" class="form-control" name="nombre_completo" value="${datos?.nombre_completo || ''}" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">DNI</label>
                                    <input type="text" class="form-control" name="dni" value="${datos?.dni || ''}" maxlength="20">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Cargo</label>
                                    <input type="text" class="form-control" name="cargo" value="${datos?.cargo || ''}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Teléfono</label>
                                <input type="text" class="form-control" name="telefono" value="${datos?.telefono || ''}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="${datos?.email || ''}">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    mostrarModal(modal, 'formUsuarioFinal');
}

function editarUsuarioFinal(datos) {
    abrirModalUsuarioFinal(datos);
}

// ==============================================
// MARCAS
// ==============================================

function abrirModalMarca(datos = null) {
    const esEdicion = datos !== null;
    const titulo = esEdicion ? 'Editar Marca' : 'Nueva Marca';
    
    const modal = `
        <div class="modal fade" id="modalMarca" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${titulo}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="formMarca">
                        <input type="hidden" name="id" value="${datos?.id || ''}">
                        <input type="hidden" name="tabla" value="marcas">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Nombre *</label>
                                <input type="text" class="form-control" name="nombre" value="${datos?.nombre || ''}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="descripcion" rows="2">${datos?.descripcion || ''}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    mostrarModal(modal, 'formMarca');
}

function editarMarca(datos) {
    abrirModalMarca(datos);
}

// ==============================================
// MODELOS
// ==============================================

function abrirModalModelo(datos = null) {
    const esEdicion = datos !== null;
    const titulo = esEdicion ? 'Editar Modelo' : 'Nuevo Modelo';
    
    // Obtener lista de marcas
    $.get(BASE_URL + '/controllers/configuracion/api.php', { action: 'getMarcas' }, function(marcas) {
        const opcionesMarcas = marcas.map(m => 
            `<option value="${m.id}" ${datos?.id_marca == m.id ? 'selected' : ''}>${m.nombre}</option>`
        ).join('');
        
        const modal = `
            <div class="modal fade" id="modalModelo" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${titulo}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="formModelo">
                            <input type="hidden" name="id" value="${datos?.id || ''}">
                            <input type="hidden" name="tabla" value="modelos">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Marca *</label>
                                    <select class="form-select" name="id_marca" required>
                                        <option value="">Seleccione...</option>
                                        ${opcionesMarcas}
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Modelo *</label>
                                    <input type="text" class="form-control" name="nombre" value="${datos?.nombre || ''}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Descripción</label>
                                    <textarea class="form-control" name="descripcion" rows="2">${datos?.descripcion || ''}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        
        mostrarModal(modal, 'formModelo');
    }, 'json');
}

function editarModelo(datos) {
    abrirModalModelo(datos);
}

// ==============================================
// FUNCIONES GENERALES
// ==============================================

function mostrarModal(html, formId) {
    // Limpiar modales anteriores
    $('.modal').modal('hide');
    $('.modal-backdrop').remove();
    $('#modalContainer').html(html);
    
    // Mostrar nuevo modal
    const modal = $('#modalContainer .modal');
    modal.modal('show');
    
    // Manejar submit del formulario
    $(`#${formId}`).on('submit', function(e) {
        e.preventDefault();
        guardarEntidad($(this));
    });
}

function guardarEntidad(form) {
    const formData = form.serialize();
    
    $.post(BASE_URL + '/controllers/configuracion/api.php?action=save', formData)
        .done(function(response) {
            try {
                const data = JSON.parse(response);
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: data.message || 'Guardado exitosamente',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.error || 'No se pudo guardar',
                        confirmButtonColor: '#dc3545'
                    });
                }
            } catch(e) {
                console.error('Error parsing response:', response);
                Swal.fire({
                    icon: 'error',
                    title: 'Error del Servidor',
                    text: 'Error al procesar la respuesta del servidor',
                    confirmButtonColor: '#dc3545'
                });
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de Conexión',
                text: 'Error al guardar. Inténtelo nuevamente.',
                confirmButtonColor: '#dc3545'
            });
        });
}

function toggleEstado(tabla, id, nuevoEstado) {
    const mensaje = nuevoEstado == 1 ? 'activar' : 'desactivar';
    
    Swal.fire({
        icon: 'warning',
        title: '¿Está seguro?',
        text: `¿Desea ${mensaje} este registro?`,
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(BASE_URL + '/controllers/configuracion/api.php', {
                action: 'toggle',
                tabla: tabla,
                id: id,
                activo: nuevoEstado
            })
            .done(function(response) {
                try {
                    const data = JSON.parse(response);
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Actualizado!',
                            text: 'Estado cambiado correctamente',
                            confirmButtonColor: '#198754'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.error || 'No se pudo cambiar el estado',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                } catch(e) {
                    console.error('Error parsing response:', response);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error del Servidor',
                        text: 'Error al procesar la respuesta',
                        confirmButtonColor: '#dc3545'
                    });
                }
            })
            .fail(function(xhr, status, error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'Error al cambiar el estado',
                    confirmButtonColor: '#dc3545'
                });
            });
        }
    });
}
