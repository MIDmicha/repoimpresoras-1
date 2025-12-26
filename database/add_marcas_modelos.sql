-- ============================================
-- AGREGAR TABLAS DE MARCAS Y MODELOS
-- ============================================

USE sistema_impresoras;

-- ============================================
-- TABLA: marcas
-- ============================================
CREATE TABLE IF NOT EXISTS marcas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: modelos
-- ============================================
CREATE TABLE IF NOT EXISTS modelos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    id_marca INT NOT NULL,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_marca) REFERENCES marcas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_modelo_marca (nombre, id_marca)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DATOS INICIALES - MARCAS COMUNES
-- ============================================
INSERT INTO marcas (nombre, descripcion) VALUES
('HP', 'Hewlett-Packard'),
('Canon', 'Canon Inc.'),
('Epson', 'Epson Corporation'),
('Brother', 'Brother Industries'),
('Samsung', 'Samsung Electronics'),
('Xerox', 'Xerox Corporation'),
('Ricoh', 'Ricoh Company'),
('Kyocera', 'Kyocera Corporation'),
('Lexmark', 'Lexmark International'),
('Konica Minolta', 'Konica Minolta Inc.')
ON DUPLICATE KEY UPDATE nombre=nombre;

-- ============================================
-- DATOS INICIALES - MODELOS COMUNES
-- ============================================
-- HP
INSERT INTO modelos (nombre, id_marca, descripcion) VALUES
('LaserJet Pro M404dn', (SELECT id FROM marcas WHERE nombre = 'HP'), 'Impresora láser monocromática'),
('LaserJet Pro MFP M428fdw', (SELECT id FROM marcas WHERE nombre = 'HP'), 'Multifuncional láser'),
('Color LaserJet Pro M454dw', (SELECT id FROM marcas WHERE nombre = 'HP'), 'Impresora láser color'),
('OfficeJet Pro 9015e', (SELECT id FROM marcas WHERE nombre = 'HP'), 'Multifuncional de tinta')
ON DUPLICATE KEY UPDATE nombre=nombre;

-- Canon
INSERT INTO modelos (nombre, id_marca, descripcion) VALUES
('imageCLASS MF445dw', (SELECT id FROM marcas WHERE nombre = 'Canon'), 'Multifuncional láser monocromática'),
('imageCLASS LBP226dw', (SELECT id FROM marcas WHERE nombre = 'Canon'), 'Impresora láser monocromática'),
('PIXMA G7020', (SELECT id FROM marcas WHERE nombre = 'Canon'), 'Multifuncional de tinta con sistema continuo'),
('imageRUNNER ADVANCE C5560i', (SELECT id FROM marcas WHERE nombre = 'Canon'), 'Multifuncional color profesional')
ON DUPLICATE KEY UPDATE nombre=nombre;

-- Epson
INSERT INTO modelos (nombre, id_marca, descripcion) VALUES
('EcoTank L3250', (SELECT id FROM marcas WHERE nombre = 'Epson'), 'Multifuncional con sistema continuo'),
('WorkForce Pro WF-C5790', (SELECT id FROM marcas WHERE nombre = 'Epson'), 'Multifuncional de tinta empresarial'),
('EcoTank L14150', (SELECT id FROM marcas WHERE nombre = 'Epson'), 'Multifuncional formato A3'),
('WorkForce Pro WF-M5299', (SELECT id FROM marcas WHERE nombre = 'Epson'), 'Impresora monocromática')
ON DUPLICATE KEY UPDATE nombre=nombre;

-- Brother
INSERT INTO modelos (nombre, id_marca, descripcion) VALUES
('HL-L2350DW', (SELECT id FROM marcas WHERE nombre = 'Brother'), 'Impresora láser monocromática'),
('MFC-L2750DW', (SELECT id FROM marcas WHERE nombre = 'Brother'), 'Multifuncional láser monocromática'),
('MFC-J6945DW', (SELECT id FROM marcas WHERE nombre = 'Brother'), 'Multifuncional de tinta A3'),
('HL-L8360CDW', (SELECT id FROM marcas WHERE nombre = 'Brother'), 'Impresora láser color')
ON DUPLICATE KEY UPDATE nombre=nombre;

-- ============================================
-- MODIFICAR TABLA EQUIPOS (si es necesario)
-- ============================================
-- Agregar columnas para referenciar marcas y modelos
ALTER TABLE equipos 
ADD COLUMN IF NOT EXISTS id_marca INT NULL AFTER modelo,
ADD COLUMN IF NOT EXISTS id_modelo INT NULL AFTER id_marca;

-- Agregar índices y claves foráneas
ALTER TABLE equipos
ADD INDEX idx_marca (id_marca),
ADD INDEX idx_modelo (id_modelo);

-- Nota: Las columnas marca y modelo como VARCHAR se mantendrán por compatibilidad
-- pero se puede migrar a usar id_marca e id_modelo en el futuro

-- ============================================
-- AUDITORÍA
-- ============================================
CREATE TABLE IF NOT EXISTS auditoria_configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tabla VARCHAR(50) NOT NULL,
    id_registro INT NOT NULL,
    accion ENUM('crear', 'actualizar', 'eliminar', 'activar', 'desactivar') NOT NULL,
    datos_anteriores JSON,
    datos_nuevos JSON,
    id_usuario INT,
    fecha_accion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- VISTAS
-- ============================================
CREATE OR REPLACE VIEW v_modelos_con_marca AS
SELECT 
    m.id,
    m.nombre as modelo,
    m.id_marca,
    marc.nombre as marca,
    m.descripcion,
    m.activo,
    m.fecha_creacion
FROM modelos m
INNER JOIN marcas marc ON m.id_marca = marc.id;

SELECT 'Tablas de marcas y modelos creadas exitosamente' as mensaje;
