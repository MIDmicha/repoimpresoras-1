-- Crear tabla temporal sin duplicados
CREATE TEMPORARY TABLE temp_estados AS
SELECT DISTINCT id, nombre, descripcion, color, activo 
FROM estados_equipo;

-- Truncar tabla original
TRUNCATE TABLE estados_equipo;

-- Insertar datos limpios
INSERT INTO estados_equipo (id, nombre, descripcion, color, activo)
SELECT id, nombre, descripcion, color, activo FROM temp_estados;

-- Verificar resultado
SELECT * FROM estados_equipo ORDER BY id;
