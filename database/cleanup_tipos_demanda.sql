-- Crear tabla temporal sin duplicados
CREATE TEMPORARY TABLE temp_tipos AS
SELECT DISTINCT id, nombre, descripcion, activo 
FROM tipos_demanda;

-- Truncar tabla original
TRUNCATE TABLE tipos_demanda;

-- Insertar datos limpios
INSERT INTO tipos_demanda (id, nombre, descripcion, activo)
SELECT id, nombre, descripcion, activo FROM temp_tipos;

-- Verificar resultado
SELECT * FROM tipos_demanda ORDER BY id;
