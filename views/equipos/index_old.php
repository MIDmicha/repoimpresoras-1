<?php
session_start();
$page_title = 'Gestión de Equipos';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Equipo.php';

$database = new Database();
$db = $database->getConnection();
$equipoModel = new Equipo($db);

// Obtener filtros
$filters = [];
if (!empty($_GET['codigo'])) $filters['codigo_patrimonial'] = $_GET['codigo'];
if (!empty($_GET['marca'])) $filters['marca'] = $_GET['marca'];
if (!empty($_GET['clasificacion'])) $filters['clasificacion'] = $_GET['clasificacion'];
if (!empty($_GET['estado'])) $filters['id_estado'] = $_GET['estado'];
if (!empty($_GET['sede'])) $filters['id_sede'] = $_GET['sede'];

// Obtener equipos
$equipos = $equipoModel->getAll($filters);
$estados = $equipoModel->getEstados();
$sedes = $equipoModel->getSedes();

include __DIR__ . '/../../includes/header.php';
?>

<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="fas fa-print"></i> Listado de Equipos
        </h4>
        <?php if (hasRole(ROL_ADMIN) || hasRole(ROL_ENCARGADO)): ?>
        <a href="<?php echo BASE_URL; ?>/views/equipos/crear.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Equipo
        </a>
        <?php endif; ?>
    </div>
    
    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Código Patrimonial</label>
                    <input type="text" class="form-control" name="codigo" 
                           value="<?php echo $_GET['codigo'] ?? ''; ?>" 
                           placeholder="Buscar por código">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Marca</label>
                    <input type="text" class="form-control" name="marca" 
                           value="<?php echo $_GET['marca'] ?? ''; ?>" 
                           placeholder="Buscar por marca">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Clasificación</label>
                    <select class="form-select" name="clasificacion">
                        <option value="">Todos</option>
                        <option value="impresora" <?php echo ($_GET['clasificacion'] ?? '') == 'impresora' ? 'selected' : ''; ?>>Impresora</option>
                        <option value="multifuncional" <?php echo ($_GET['clasificacion'] ?? '') == 'multifuncional' ? 'selected' : ''; ?>>Multifuncional</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select class="form-select" name="estado">
                        <option value="">Todos</option>
                        <?php foreach ($estados as $estado): ?>
                        <option value="<?php echo $estado['id']; ?>" 
                                <?php echo ($_GET['estado'] ?? '') == $estado['id'] ? 'selected' : ''; ?>>
                            <?php echo $estado['nombre']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Sede</label>
                    <select class="form-select" name="sede">
                        <option value="">Todas</option>
                        <?php foreach ($sedes as $sede): ?>
                        <option value="<?php echo $sede['id']; ?>" 
                                <?php echo ($_GET['sede'] ?? '') == $sede['id'] ? 'selected' : ''; ?>>
                            <?php echo $sede['nombre']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <a href="<?php echo BASE_URL; ?>/views/equipos/index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Tabla de Equipos -->
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead class="table-light">
                <tr>
                    <th>Código</th>
                    <th>Clasificación</th>
                    <th>Marca/Modelo</th>
                    <th>Serie</th>
                    <th>Estado</th>
                    <th>Sede</th>
                    <th>Usuario Final</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($equipos as $equipo): ?>
                <tr>
                    <td>
                        <strong><?php echo $equipo['codigo_patrimonial']; ?></strong>
                    </td>
                    <td>
                        <span class="badge bg-info">
                            <?php echo ucfirst($equipo['clasificacion']); ?>
                        </span>
                    </td>
                    <td>
                        <strong><?php echo $equipo['marca']; ?></strong><br>
                        <small class="text-muted"><?php echo $equipo['modelo']; ?></small>
                    </td>
                    <td><?php echo $equipo['numero_serie'] ?? '-'; ?></td>
                    <td>
                        <span class="badge badge-estado" style="background-color: <?php echo $equipo['estado_color']; ?>">
                            <?php echo $equipo['estado_nombre']; ?>
                        </span>
                    </td>
                    <td><?php echo $equipo['sede_nombre'] ?? '-'; ?></td>
                    <td><?php echo $equipo['usuario_final_nombre'] ?? '-'; ?></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="<?php echo BASE_URL; ?>/views/equipos/ver.php?id=<?php echo $equipo['id']; ?>" 
                               class="btn btn-info" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if (hasRole(ROL_ADMIN) || hasRole(ROL_ENCARGADO)): ?>
                            <a href="<?php echo BASE_URL; ?>/views/equipos/editar.php?id=<?php echo $equipo['id']; ?>" 
                               class="btn btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (hasRole(ROL_ADMIN)): ?>
                            <a href="<?php echo BASE_URL; ?>/controllers/equipos.php?action=delete&id=<?php echo $equipo['id']; ?>" 
                               class="btn btn-danger" title="Eliminar"
                               onclick="return confirmarEliminacion('¿Está seguro de eliminar este equipo?')">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php if (count($equipos) == 0): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> No se encontraron equipos con los criterios de búsqueda.
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
