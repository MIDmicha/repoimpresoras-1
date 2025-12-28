-- Deshabilitar verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 0;

-- Eliminar tabla repuestos
DROP TABLE IF EXISTS repuestos;

-- Crear tabla repuestos correcta
CREATE TABLE repuestos (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    marca VARCHAR(100),
    modelo_compatible VARCHAR(100),
    stock_minimo INT(11) DEFAULT 0,
    stock_actual INT(11) DEFAULT 0,
    precio_unitario DECIMAL(10, 2) DEFAULT 0.00,
    unidad_medida VARCHAR(50) DEFAULT 'unidad',
    activo TINYINT(1) DEFAULT 1,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_usuario_registro INT(11),
    FOREIGN KEY (id_usuario_registro) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Crear índices
CREATE INDEX idx_codigo ON repuestos(codigo);
CREATE INDEX idx_nombre ON repuestos(nombre);
CREATE INDEX idx_stock ON repuestos(stock_actual);
CREATE INDEX idx_activo ON repuestos(activo);

-- Re-habilitar verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Tabla repuestos recreada exitosamente' as resultado;
