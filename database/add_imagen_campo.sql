-- Agregar campo imagen a la tabla equipos
USE sistema_impresoras;

ALTER TABLE equipos 
ADD COLUMN imagen VARCHAR(255) NULL AFTER observaciones;
