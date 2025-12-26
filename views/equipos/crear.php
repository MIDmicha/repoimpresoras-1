<?php
session_start();
$page_title = 'Registrar Nuevo Equipo';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Equipo.php';

// Verificar permisos
if (!hasRole(ROL_ADMIN) && !hasRole(ROL_ENCARGADO)) {
    setFlashMessage('danger', 'No tiene permisos para realizar esta acción');
    redirect('views/equipos/index.php');
}

$database = new Database();
$db = $database->getConnection();
$equipoModel = new Equipo($db);

// Obtener datos para los selects
$estados = $equipoModel->getEstados();
$marcas = $equipoModel->getMarcas();
$modelos = $equipoModel->getModelos();
$distritos = $equipoModel->getDistritos();
$sedes = $equipoModel->getSedes();
$macro_procesos = $equipoModel->getMacroProcesos();
$despachos = $equipoModel->getDespachos();
$usuarios_finales = $equipoModel->getUsuariosFinales();

include __DIR__ . '/../../includes/header.php';
?>

<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="fas fa-plus-circle"></i> Registrar Nuevo Equipo
        </h4>
        <a href="<?php echo BASE_URL; ?>/views/equipos/index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
    
    <form method="POST" action="<?php echo BASE_URL; ?>/controllers/equipos.php?action=create" id="formEquipo">
        <!-- Información del Equipo -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-print"></i> Información del Equipo</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="codigo_patrimonial" class="form-label">
                            Código Patrimonial <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="codigo_patrimonial" 
                               name="codigo_patrimonial" required>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="clasificacion" class="form-label">
                            Clasificación <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="clasificacion" name="clasificacion" required>
                            <option value="">Seleccione...</option>
                            <option value="impresora">Impresora</option>
                            <option value="multifuncional">Multifuncional</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="id_estado" class="form-label">
                            Estado <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="id_estado" name="id_estado" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($estados as $estado): ?>
                            <option value="<?php echo $estado['id']; ?>">
                                <?php echo $estado['nombre']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="id_marca" class="form-label">
                            Marca <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="id_marca" name="id_marca" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($marcas as $marca): ?>
                            <option value="<?php echo $marca['id']; ?>">
                                <?php echo $marca['nombre']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Si no encuentra la marca, créela en <a href="<?php echo BASE_URL; ?>/views/configuracion/index.php" target="_blank">Configuración</a></small>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="id_modelo" class="form-label">
                            Modelo <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="id_modelo" name="id_modelo" required>
                            <option value="">Seleccione primero una marca...</option>
                        </select>
                        <small class="text-muted">Si no encuentra el modelo, créelo en <a href="<?php echo BASE_URL; ?>/views/configuracion/index.php" target="_blank">Configuración</a></small>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="numero_serie" class="form-label">Número de Serie</label>
                        <input type="text" class="form-control" id="numero_serie" name="numero_serie">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="garantia" class="form-label">Garantía</label>
                        <input type="text" class="form-control" id="garantia" name="garantia" 
                               placeholder="Ej: 2 años">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="anio_adquisicion" class="form-label">Año de Adquisición</label>
                        <input type="number" class="form-control" id="anio_adquisicion" 
                               name="anio_adquisicion" min="2000" max="<?php echo date('Y'); ?>" 
                               value="<?php echo date('Y'); ?>">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="tiene_estabilizador" class="form-label">¿Tiene Estabilizador?</label>
                        <select class="form-select" id="tiene_estabilizador" name="tiene_estabilizador">
                            <option value="0">No</option>
                            <option value="1">Sí</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Ubicación del Equipo -->
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Ubicación del Equipo</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_distrito" class="form-label">Distrito Fiscal</label>
                        <select class="form-select select2" id="id_distrito" name="id_distrito">
                            <option value="">Seleccione...</option>
                            <?php foreach ($distritos as $distrito): ?>
                            <option value="<?php echo $distrito['id']; ?>">
                                <?php echo $distrito['nombre']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="id_sede" class="form-label">Sede</label>
                        <select class="form-select select2" id="id_sede" name="id_sede">
                            <option value="">Seleccione...</option>
                            <?php foreach ($sedes as $sede): ?>
                            <option value="<?php echo $sede['id']; ?>">
                                <?php echo $sede['nombre']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="id_macro_proceso" class="form-label">Macro Proceso</label>
                        <select class="form-select select2" id="id_macro_proceso" name="id_macro_proceso">
                            <option value="">Seleccione...</option>
                            <?php foreach ($macro_procesos as $mp): ?>
                            <option value="<?php echo $mp['id']; ?>">
                                <?php echo $mp['nombre']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="id_despacho" class="form-label">Despacho</label>
                        <select class="form-select select2" id="id_despacho" name="id_despacho">
                            <option value="">Seleccione...</option>
                            <?php foreach ($despachos as $despacho): ?>
                            <option value="<?php echo $despacho['id']; ?>">
                                <?php echo $despacho['nombre']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="ubicacion_fisica" class="form-label">Ubicación Física</label>
                        <input type="text" class="form-control" id="ubicacion_fisica" 
                               name="ubicacion_fisica" placeholder="Ej: Piso 2, Oficina 205">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="id_usuario_final" class="form-label">Usuario Final Responsable</label>
                        <select class="form-select select2" id="id_usuario_final" name="id_usuario_final">
                            <option value="">Seleccione...</option>
                            <?php foreach ($usuarios_finales as $uf): ?>
                            <option value="<?php echo $uf['id']; ?>">
                                <?php echo $uf['nombre_completo']; ?>
                                <?php if ($uf['dni']): ?>
                                    (DNI: <?php echo $uf['dni']; ?>)
                                <?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Observaciones -->
        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-comment"></i> Observaciones</h5>
            </div>
            <div class="card-body">
                <textarea class="form-control" id="observaciones" name="observaciones" 
                          rows="3" placeholder="Ingrese observaciones adicionales..."></textarea>
            </div>
        </div>
        
        <!-- Botones -->
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="<?php echo BASE_URL; ?>/views/equipos/index.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Equipo
            </button>
        </div>
    </form>
</div>

<?php
$extra_js = <<<'EOT'
<script>
// Asegurarse de que jQuery esté cargado
if (typeof jQuery === "undefined") {
    console.error("jQuery no está cargado!");
} else {
    console.log("jQuery versión:", jQuery.fn.jquery);
}

$(document).ready(function() {
    console.log("=== INICIO DEBUG CREAR EQUIPO ===");
    console.log("Selector #id_marca existe:", $("#id_marca").length > 0);
    console.log("Selector #id_modelo existe:", $("#id_modelo").length > 0);
    
    if ($("#id_marca").length === 0) {
        console.error("ERROR: No se encontró el selector #id_marca");
        return;
    }
    
    if ($("#id_modelo").length === 0) {
        console.error("ERROR: No se encontró el selector #id_modelo");
        return;
    }
    
    // Cargar modelos cuando se selecciona una marca
    $("#id_marca").on("change", function() {
        const id_marca = $(this).val();
        const $modeloSelect = $("#id_modelo");
        
        console.log("=== MARCA SELECCIONADA ===");
        console.log("ID Marca:", id_marca);
        console.log("Tipo:", typeof id_marca);
        
        if (!id_marca || id_marca === "") {
            console.log("Marca vacía, reseteando modelos");
            $modeloSelect.html("<option value=''>Seleccione primero una marca...</option>");
            return;
        }
        
        $modeloSelect.html("<option value=''>Cargando...</option>");
        
        const url = BASE_URL + "/controllers/equipos.php";
        const params = {
            action: "getModelos",
            id_marca: id_marca
        };
        
        console.log("URL:", url);
        console.log("Parámetros:", params);
        console.log("URL completa:", url + "?" + $.param(params));
        
        $.ajax({
            url: url,
            method: "GET",
            data: params,
            dataType: "json",
            beforeSend: function() {
                console.log("Enviando petición AJAX...");
            },
            success: function(data) {
                console.log("=== RESPUESTA EXITOSA ===");
                console.log("Tipo de respuesta:", typeof data);
                console.log("Es array:", Array.isArray(data));
                console.log("Cantidad de modelos:", Array.isArray(data) ? data.length : "N/A");
                console.log("Datos completos:", data);
                
                let options = "<option value=''>Seleccione un modelo...</option>";
                
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(function(modelo) {
                        console.log("Agregando modelo:", modelo.id, modelo.nombre);
                        options += `<option value="${modelo.id}">${modelo.nombre}</option>`;
                    });
                    console.log("Total opciones generadas:", data.length + 1);
                } else {
                    console.warn("No hay modelos disponibles");
                    options = "<option value=''>No hay modelos disponibles</option>";
                }
                
                $modeloSelect.html(options);
                console.log("Opciones HTML actualizadas");
            },
            error: function(xhr, status, error) {
                console.error("=== ERROR EN PETICIÓN ===");
                console.error("Status:", status);
                console.error("Error:", error);
                console.error("HTTP Status:", xhr.status);
                console.error("Respuesta del servidor:", xhr.responseText);
                console.error("Headers:", xhr.getAllResponseHeaders());
                
                $modeloSelect.html("<option value=''>Error al cargar modelos</option>");
                
                Swal.fire({
                    icon: "error",
                    title: "Error al cargar modelos",
                    html: `<p><strong>Status:</strong> ${status}</p>
                           <p><strong>Error:</strong> ${error}</p>
                           <p><strong>HTTP:</strong> ${xhr.status}</p>
                           <p>Ver consola para más detalles</p>`,
                    confirmButtonColor: "#dc3545"
                });
            },
            complete: function() {
                console.log("=== PETICIÓN COMPLETADA ===");
            }
        });
    });
    
    console.log("Event handler registrado para #id_marca");
    
    // Validación del formulario
    $("#formEquipo").on("submit", function(e) {
        var codigo = $("#codigo_patrimonial").val().trim();
        var marca = $("#id_marca").val();
        var modelo = $("#id_modelo").val();
        
        if (!codigo || !marca || !modelo) {
            e.preventDefault();
            Swal.fire({
                icon: "warning",
                title: "Campos Incompletos",
                text: "Por favor complete todos los campos obligatorios",
                confirmButtonColor: "#0d6efd"
            });
            return false;
        }
    });
});
</script>
EOT;

include __DIR__ . '/../../includes/footer.php';
?>
