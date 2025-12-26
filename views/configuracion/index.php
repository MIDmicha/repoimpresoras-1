<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isLoggedIn() || !hasRole(ROL_ADMIN)) {
    setFlashMessage('danger', 'No tiene permisos para acceder a esta sección');
    redirect('views/dashboard.php');
}

$page_title = 'Configuración del Sistema';

$database = new Database();
$db = $database->getConnection();

// Obtener datos para las pestañas
$estados = $db->query("SELECT * FROM estados_equipo ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$marcas = $db->query("SELECT * FROM marcas ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$modelos = $db->query("SELECT m.*, marc.nombre as marca_nombre FROM modelos m LEFT JOIN marcas marc ON m.id_marca = marc.id ORDER BY marc.nombre, m.nombre")->fetchAll(PDO::FETCH_ASSOC);
$distritos = $db->query("SELECT * FROM distritos_fiscales ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$sedes = $db->query("SELECT s.*, d.nombre as distrito_nombre FROM sedes s LEFT JOIN distritos_fiscales d ON s.id_distrito = d.id ORDER BY s.nombre")->fetchAll(PDO::FETCH_ASSOC);
$macroProcesos = $db->query("SELECT * FROM macro_procesos ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$despachos = $db->query("SELECT d.*, s.nombre as sede_nombre FROM despachos d LEFT JOIN sedes s ON d.id_sede = s.id ORDER BY d.nombre")->fetchAll(PDO::FETCH_ASSOC);
$usuariosFinales = $db->query("SELECT * FROM usuarios_finales ORDER BY nombre_completo")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>

<style>
.config-card {
    background: var(--bg-card);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    margin-bottom: 2rem;
}

.nav-tabs .nav-link {
    color: var(--text-secondary);
    border: none;
    border-bottom: 3px solid transparent;
    padding: 1rem 1.5rem;
    font-weight: 500;
}

.nav-tabs .nav-link:hover {
    color: var(--text-primary);
    border-bottom-color: var(--primary-color);
}

.nav-tabs .nav-link.active {
    color: var(--primary-color);
    border-bottom-color: var(--primary-color);
    background: transparent;
}

.color-picker-wrapper {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.color-picker-wrapper input[type="color"] {
    width: 50px;
    height: 38px;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-sm);
    cursor: pointer;
}

.badge-preview {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-cog"></i> Configuración del Sistema</h2>
</div>

<div class="config-card">
    <!-- Nav Tabs -->
    <ul class="nav nav-tabs mb-4" id="configTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="estados-tab" data-bs-toggle="tab" data-bs-target="#estados" type="button">
                <i class="fas fa-circle"></i> Estados
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="marcas-tab" data-bs-toggle="tab" data-bs-target="#marcas" type="button">
                <i class="fas fa-tag"></i> Marcas
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="modelos-tab" data-bs-toggle="tab" data-bs-target="#modelos" type="button">
                <i class="fas fa-tags"></i> Modelos
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="distritos-tab" data-bs-toggle="tab" data-bs-target="#distritos" type="button">
                <i class="fas fa-map-marked-alt"></i> Distritos Fiscales
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="sedes-tab" data-bs-toggle="tab" data-bs-target="#sedes" type="button">
                <i class="fas fa-building"></i> Sedes
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="macro-tab" data-bs-toggle="tab" data-bs-target="#macro" type="button">
                <i class="fas fa-sitemap"></i> Macro Procesos
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="despachos-tab" data-bs-toggle="tab" data-bs-target="#despachos" type="button">
                <i class="fas fa-door-open"></i> Despachos
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="usuarios-finales-tab" data-bs-toggle="tab" data-bs-target="#usuarios-finales" type="button">
                <i class="fas fa-user"></i> Usuarios Finales
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="configTabContent">
        
        <!-- ESTADOS DE EQUIPOS -->
        <div class="tab-pane fade show active" id="estados" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><i class="fas fa-circle"></i> Estados de Equipos</h5>
                <button class="btn btn-primary btn-sm" onclick="abrirModalEstado()">
                    <i class="fas fa-plus"></i> Nuevo Estado
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="tablaEstados">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Color</th>
                            <th>Vista Previa</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estados as $estado): ?>
                        <tr>
                            <td><?php echo $estado['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($estado['nombre']); ?></strong></td>
                            <td><?php echo htmlspecialchars($estado['descripcion'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($estado['color'] ?? '-'); ?></td>
                            <td>
                                <span class="badge badge-preview" style="background-color: <?php echo $estado['color'] ?? '#6c757d'; ?>; color: white;">
                                    <?php echo htmlspecialchars($estado['nombre']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $estado['activo'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $estado['activo'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editarEstado(<?php echo htmlspecialchars(json_encode($estado)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-<?php echo $estado['activo'] ? 'secondary' : 'success'; ?>" 
                                        onclick="toggleEstado('estados_equipo', <?php echo $estado['id']; ?>, <?php echo $estado['activo'] ? 0 : 1; ?>)">
                                    <i class="fas fa-<?php echo $estado['activo'] ? 'ban' : 'check'; ?>"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MARCAS -->
        <div class="tab-pane fade" id="marcas" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><i class="fas fa-tag"></i> Marcas de Equipos</h5>
                <button class="btn btn-primary btn-sm" onclick="abrirModalMarca()">
                    <i class="fas fa-plus"></i> Nueva Marca
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="tablaMarcas">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($marcas as $marca): ?>
                        <tr>
                            <td><?php echo $marca['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($marca['nombre']); ?></strong></td>
                            <td><?php echo htmlspecialchars($marca['descripcion'] ?? '-'); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $marca['activo'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $marca['activo'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editarMarca(<?php echo htmlspecialchars(json_encode($marca)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-<?php echo $marca['activo'] ? 'secondary' : 'success'; ?>" 
                                        onclick="toggleEstado('marcas', <?php echo $marca['id']; ?>, <?php echo $marca['activo'] ? 0 : 1; ?>)">
                                    <i class="fas fa-<?php echo $marca['activo'] ? 'ban' : 'check'; ?>"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODELOS -->
        <div class="tab-pane fade" id="modelos" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><i class="fas fa-tags"></i> Modelos de Equipos</h5>
                <button class="btn btn-primary btn-sm" onclick="abrirModalModelo()">
                    <i class="fas fa-plus"></i> Nuevo Modelo
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="tablaModelos">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($modelos as $modelo): ?>
                        <tr>
                            <td><?php echo $modelo['id']; ?></td>
                            <td><span class="badge bg-primary"><?php echo htmlspecialchars($modelo['marca_nombre']); ?></span></td>
                            <td><strong><?php echo htmlspecialchars($modelo['nombre']); ?></strong></td>
                            <td><?php echo htmlspecialchars($modelo['descripcion'] ?? '-'); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $modelo['activo'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $modelo['activo'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editarModelo(<?php echo htmlspecialchars(json_encode($modelo)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-<?php echo $modelo['activo'] ? 'secondary' : 'success'; ?>" 
                                        onclick="toggleEstado('modelos', <?php echo $modelo['id']; ?>, <?php echo $modelo['activo'] ? 0 : 1; ?>)">
                                    <i class="fas fa-<?php echo $modelo['activo'] ? 'ban' : 'check'; ?>"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- DISTRITOS FISCALES -->
        <div class="tab-pane fade" id="distritos" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><i class="fas fa-map-marked-alt"></i> Distritos Fiscales</h5>
                <button class="btn btn-primary btn-sm" onclick="abrirModalDistrito()">
                    <i class="fas fa-plus"></i> Nuevo Distrito
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="tablaDistritos">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($distritos as $distrito): ?>
                        <tr>
                            <td><?php echo $distrito['id']; ?></td>
                            <td><?php echo htmlspecialchars($distrito['codigo'] ?? '-'); ?></td>
                            <td><strong><?php echo htmlspecialchars($distrito['nombre']); ?></strong></td>
                            <td>
                                <span class="badge bg-<?php echo $distrito['activo'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $distrito['activo'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editarDistrito(<?php echo htmlspecialchars(json_encode($distrito)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-<?php echo $distrito['activo'] ? 'secondary' : 'success'; ?>" 
                                        onclick="toggleEstado('distritos_fiscales', <?php echo $distrito['id']; ?>, <?php echo $distrito['activo'] ? 0 : 1; ?>)">
                                    <i class="fas fa-<?php echo $distrito['activo'] ? 'ban' : 'check'; ?>"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SEDES -->
        <div class="tab-pane fade" id="sedes" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><i class="fas fa-building"></i> Sedes</h5>
                <button class="btn btn-primary btn-sm" onclick="abrirModalSede()">
                    <i class="fas fa-plus"></i> Nueva Sede
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="tablaSedes">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Dirección</th>
                            <th>Distrito</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sedes as $sede): ?>
                        <tr>
                            <td><?php echo $sede['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($sede['nombre']); ?></strong></td>
                            <td><?php echo htmlspecialchars($sede['direccion'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($sede['distrito_nombre'] ?? '-'); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $sede['activo'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $sede['activo'] ? 'Activa' : 'Inactiva'; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editarSede(<?php echo htmlspecialchars(json_encode($sede)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-<?php echo $sede['activo'] ? 'secondary' : 'success'; ?>" 
                                        onclick="toggleEstado('sedes', <?php echo $sede['id']; ?>, <?php echo $sede['activo'] ? 0 : 1; ?>)">
                                    <i class="fas fa-<?php echo $sede['activo'] ? 'ban' : 'check'; ?>"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MACRO PROCESOS -->
        <div class="tab-pane fade" id="macro" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><i class="fas fa-sitemap"></i> Macro Procesos</h5>
                <button class="btn btn-primary btn-sm" onclick="abrirModalMacro()">
                    <i class="fas fa-plus"></i> Nuevo Macro Proceso
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="tablaMacro">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($macroProcesos as $mp): ?>
                        <tr>
                            <td><?php echo $mp['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($mp['nombre']); ?></strong></td>
                            <td><?php echo htmlspecialchars($mp['descripcion'] ?? '-'); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $mp['activo'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $mp['activo'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editarMacro(<?php echo htmlspecialchars(json_encode($mp)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-<?php echo $mp['activo'] ? 'secondary' : 'success'; ?>" 
                                        onclick="toggleEstado('macro_procesos', <?php echo $mp['id']; ?>, <?php echo $mp['activo'] ? 0 : 1; ?>)">
                                    <i class="fas fa-<?php echo $mp['activo'] ? 'ban' : 'check'; ?>"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- DESPACHOS -->
        <div class="tab-pane fade" id="despachos" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><i class="fas fa-door-open"></i> Despachos</h5>
                <button class="btn btn-primary btn-sm" onclick="abrirModalDespacho()">
                    <i class="fas fa-plus"></i> Nuevo Despacho
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="tablaDespachos">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Sede</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($despachos as $desp): ?>
                        <tr>
                            <td><?php echo $desp['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($desp['nombre']); ?></strong></td>
                            <td><?php echo htmlspecialchars($desp['sede_nombre'] ?? '-'); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $desp['activo'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $desp['activo'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editarDespacho(<?php echo htmlspecialchars(json_encode($desp)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-<?php echo $desp['activo'] ? 'secondary' : 'success'; ?>" 
                                        onclick="toggleEstado('despachos', <?php echo $desp['id']; ?>, <?php echo $desp['activo'] ? 0 : 1; ?>)">
                                    <i class="fas fa-<?php echo $desp['activo'] ? 'ban' : 'check'; ?>"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- USUARIOS FINALES -->
        <div class="tab-pane fade" id="usuarios-finales" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><i class="fas fa-user"></i> Usuarios Finales / Usantes</h5>
                <button class="btn btn-primary btn-sm" onclick="abrirModalUsuarioFinal()">
                    <i class="fas fa-plus"></i> Nuevo Usuario Final
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="tablaUsuariosFinales">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>DNI</th>
                            <th>Cargo</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuariosFinales as $uf): ?>
                        <tr>
                            <td><?php echo $uf['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($uf['nombre_completo']); ?></strong></td>
                            <td><?php echo htmlspecialchars($uf['dni'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($uf['cargo'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($uf['telefono'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($uf['email'] ?? '-'); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $uf['activo'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $uf['activo'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editarUsuarioFinal(<?php echo htmlspecialchars(json_encode($uf)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-<?php echo $uf['activo'] ? 'secondary' : 'success'; ?>" 
                                        onclick="toggleEstado('usuarios_finales', <?php echo $uf['id']; ?>, <?php echo $uf['activo'] ? 0 : 1; ?>)">
                                    <i class="fas fa-<?php echo $uf['activo'] ? 'ban' : 'check'; ?>"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Modales se generarán dinámicamente con JavaScript -->
<div id="modalContainer"></div>

<script>
// Definir BASE_URL para JavaScript
const BASE_URL = '<?php echo BASE_URL; ?>';
</script>
<script src="<?php echo BASE_URL; ?>/assets/js/configuracion.js"></script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
