-- Insertar datos de ejemplo para el módulo de configuración
USE sistema_impresoras;

-- Estados de equipos (si no existen)
INSERT IGNORE INTO estados_equipo (nombre, descripcion, color, activo) VALUES
('Operativo', 'Equipo funcionando correctamente', '#28a745', 1),
('En Mantenimiento', 'Equipo en proceso de mantenimiento', '#ffc107', 1),
('Fuera de Servicio', 'Equipo no operativo', '#dc3545', 1),
('En Reparación', 'Equipo siendo reparado', '#fd7e14', 1),
('Dado de Baja', 'Equipo dado de baja', '#6c757d', 1);

-- Distritos fiscales (ejemplo)
INSERT IGNORE INTO distritos_fiscales (nombre, codigo, activo) VALUES
('Lima Norte', 'LN-001', 1),
('Lima Sur', 'LS-002', 1),
('Lima Este', 'LE-003', 1),
('Lima Centro', 'LC-004', 1),
('Callao', 'CL-005', 1);

-- Macro procesos (ejemplo)
INSERT IGNORE INTO macro_procesos (nombre, descripcion, activo) VALUES
('Administración', 'Procesos administrativos generales', 1),
('Operaciones', 'Procesos operativos', 1),
('Soporte Técnico', 'Servicios de soporte', 1),
('Logística', 'Gestión logística', 1);
