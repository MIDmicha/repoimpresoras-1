-- Insertar más marcas si no existen
INSERT IGNORE INTO marcas (id, nombre, activo) VALUES
(4, 'Canon', 1),
(5, 'Ricoh', 1),
(6, 'Xerox', 1),
(7, 'Kyocera', 1),
(8, 'Brother', 1),
(9, 'HP', 1),
(10, 'Lexmark', 1);

-- Insertar más modelos si no existen
INSERT IGNORE INTO modelos (id, nombre, id_marca, activo) VALUES
(13, 'imagePRUNER 2520', 1, 1),
(14, 'MP C3504', 5, 1),
(15, 'WorkCentre 5335', 6, 1),
(16, 'TASKalfa 3510i', 7, 1),
(17, 'HL-L8360CDW', 8, 1),
(18, 'LaserJet Pro M454dn', 9, 1),
(19, 'MS911de', 10, 1);

-- Insertar más sedes si no existen
INSERT IGNORE INTO sedes (id, nombre, descripcion, activo) VALUES
(2, 'Sede Secundaria', 'Oficinas administrativas', 1),
(3, 'Sucursal Lima', 'Oficina regional', 1);

-- Insertar más distritos si no existen
INSERT IGNORE INTO distritos_fiscales (id, nombre, activo) VALUES
(2, 'Lima Este', 1),
(3, 'Lima Norte', 1),
(4, 'Lima Sur', 1);

-- Insertar más despachos si no existen
INSERT IGNORE INTO despachos (id, nombre, id_sede, activo) VALUES
(2, 'Despacho Administrativo', 1, 1),
(3, 'Área de Sistemas', 2, 1),
(4, 'Contabilidad', 2, 1);

-- Insertar más macro procesos si no existen
INSERT IGNORE INTO macro_procesos (id, nombre, descripcion, activo) VALUES
(2, 'Administración', 'Procesos administrativos', 1),
(3, 'Finanzas', 'Procesos financieros', 1),
(4, 'Ventas', 'Procesos de ventas', 1);

-- Insertar más usuarios finales si no existen
INSERT IGNORE INTO usuarios_finales (id, nombre_completo, cargo, email, telefono, id_sede, activo) VALUES
(2, 'María García López', 'Asistente Administrativo', 'maria.garcia@sistema.com', '987654325', 1, 1),
(3, 'Juan Pérez Rodríguez', 'Contador', 'juan.perez@sistema.com', '987654326', 1, 1),
(4, 'Ana Martínez Silva', 'Gerente de Ventas', 'ana.martinez@sistema.com', '987654327', 2, 1);

-- Insertar más equipos si no existen
INSERT IGNORE INTO equipos (
    codigo_patrimonial, clasificacion, marca, modelo, id_marca, id_modelo, 
    numero_serie, garantia, id_estado, id_sede, id_distrito, 
    id_despacho, id_macro_proceso, ubicacion_fisica, id_usuario_final,
    anio_adquisicion, tiene_estabilizador, activo, id_usuario_creacion
) VALUES
(2, 'impresora', 'Canon', 'imagePRUNER 2520', 1, 13, 'SN8854217641', '12 meses', 1, 1, 1, 2, 1, 'Despacho Contabilidad', 2, 2022, 1, 1, 1),
(3, 'fotocopiadora', 'Ricoh', 'MP C3504', 5, 14, 'SN7745821369', '24 meses', 1, 1, 1, 3, 2, 'Área de Sistemas', 3, 2020, 1, 1, 1),
(4, 'fotocopiadora', 'Xerox', 'WorkCentre 5335', 6, 15, 'SN6634921574', '12 meses', 2, 2, 2, 4, 3, 'Despacho Principal', 4, 2023, 1, 1, 1);

-- Insertar más mantenimientos si no existen
INSERT IGNORE INTO mantenimientos (
    id_equipo, id_tipo_demanda, fecha_mantenimiento, descripcion, 
    tecnico_responsable, observaciones, id_estado_anterior, id_estado_nuevo,
    id_usuario_registro, activo
) VALUES
(2, 2, '2025-12-20', 'Limpieza de cabezal', 'Carlos López', 'Mantenimiento correctivo', 1, 1, 1, 1),
(2, 1, '2025-12-15', 'Mantenimiento preventivo', 'Carlos López', 'Cambio de filtro', 1, 1, 1, 1),
(3, 1, '2025-12-22', 'Mantenimiento programado', 'Roberto Sánchez', 'Revisión general', 1, 1, 1, 1),
(4, 3, '2025-12-21', 'Cambio de tóner', 'Roberto Sánchez', 'Tóner agotado', 1, 1, 1, 1);

-- Insertar repuestos de ejemplo
INSERT IGNORE INTO repuestos (
    codigo, nombre, descripcion, marca, modelo_compatible,
    stock_minimo, stock_actual, precio_unitario, unidad_medida,
    activo, id_usuario_registro
) VALUES
('REP001', 'Tóner Negro', 'Cartucho de tóner negro estándar', 'Genérico', 'Compatible múltiples', 5, 15, 45.50, 'unidad', 1, 1),
('REP002', 'Tóner Color', 'Set de tóner color (CMYK)', 'Genérico', 'Compatible múltiples', 3, 8, 120.00, 'set', 1, 1),
('REP003', 'Rodillo Caucho', 'Rodillo de caucho para alimentación', 'Epson', 'WorkForce Pro', 2, 6, 28.75, 'unidad', 1, 1),
('REP004', 'Fusibles', 'Fusibles de reemplazo 250V', 'Genérico', 'Compatible múltiples', 10, 25, 5.00, 'unidad', 1, 1),
('REP005', 'Cable de Alimentación', 'Cable AC estándar 3 metros', 'Genérico', 'Compatible múltiples', 3, 7, 12.50, 'unidad', 1, 1);

-- Insertar movimientos de repuestos si la tabla existe
INSERT IGNORE INTO repuestos_movimientos (
    id_repuesto, tipo_movimiento, cantidad, motivo, id_usuario,
    observaciones, activo
) VALUES
(1, 'ENTRADA', 20, 'Compra a proveedor', 1, 'Factura #001', 1),
(1, 'SALIDA', 5, 'Uso en mantenimiento', 1, 'Mantenimiento equipo 1', 1),
(2, 'ENTRADA', 10, 'Compra a proveedor', 1, 'Factura #002', 1),
(3, 'ENTRADA', 8, 'Compra a proveedor', 1, 'Factura #003', 1);

SELECT 'Base de datos poblada exitosamente' as resultado;
SELECT CONCAT(COUNT(*), ' equipos totales') as equipos_totales FROM equipos;
SELECT CONCAT(COUNT(*), ' mantenimientos totales') as mantenimientos_totales FROM mantenimientos;
SELECT CONCAT(COUNT(*), ' repuestos totales') as repuestos_totales FROM repuestos;
