-- Eliminar el trigger antiguo
DROP TRIGGER IF EXISTS after_equipo_update;

-- Recrear el trigger con LIMIT 1 en los SELECTs
DELIMITER $$
CREATE TRIGGER after_equipo_update
AFTER UPDATE ON equipos
FOR EACH ROW
BEGIN
    DECLARE usuario_nombre_var VARCHAR(100);
    DECLARE descripcion_var TEXT;
    DECLARE estado_anterior VARCHAR(50);
    DECLARE estado_nuevo VARCHAR(50);
    
    
    SELECT nombre_completo INTO usuario_nombre_var
    FROM usuarios WHERE id = NEW.id_usuario_actualizacion;
    
    
    IF OLD.activo = 1 AND NEW.activo = 0 THEN
        
        SET descripcion_var = CONCAT('Equipo eliminado: ', NEW.codigo_patrimonial);
        
        INSERT INTO auditoria_equipos (
            id_equipo,
            id_usuario,
            usuario_nombre,
            accion,
            descripcion,
            datos_anteriores,
            datos_nuevos
        ) VALUES (
            NEW.id,
            NEW.id_usuario_actualizacion,
            usuario_nombre_var,
            'ELIMINAR',
            descripcion_var,
            JSON_OBJECT('activo', OLD.activo),
            JSON_OBJECT('activo', NEW.activo)
        );
        
    ELSEIF OLD.id_estado != NEW.id_estado THEN
        
        SELECT nombre INTO estado_anterior FROM estados_equipo WHERE id = OLD.id_estado LIMIT 1;
        SELECT nombre INTO estado_nuevo FROM estados_equipo WHERE id = NEW.id_estado LIMIT 1;
        
        SET descripcion_var = CONCAT('Estado cambiado de "', estado_anterior, '" a "', estado_nuevo, '"');
        
        INSERT INTO auditoria_equipos (
            id_equipo,
            id_usuario,
            usuario_nombre,
            accion,
            descripcion,
            datos_anteriores,
            datos_nuevos
        ) VALUES (
            NEW.id,
            NEW.id_usuario_actualizacion,
            usuario_nombre_var,
            'CAMBIO_ESTADO',
            descripcion_var,
            JSON_OBJECT('id_estado', OLD.id_estado, 'estado_nombre', estado_anterior),
            JSON_OBJECT('id_estado', NEW.id_estado, 'estado_nombre', estado_nuevo)
        );
        
    ELSE
        
        SET descripcion_var = 'Equipo modificado';
        
        
        IF OLD.codigo_patrimonial != NEW.codigo_patrimonial THEN
            SET descripcion_var = CONCAT(descripcion_var, ' - Código patrimonial actualizado');
        END IF;
        
        IF OLD.marca != NEW.marca OR OLD.modelo != NEW.modelo THEN
            SET descripcion_var = CONCAT(descripcion_var, ' - Marca/Modelo actualizado');
        END IF;
        
        IF OLD.numero_serie != NEW.numero_serie OR (OLD.numero_serie IS NULL AND NEW.numero_serie IS NOT NULL) THEN
            SET descripcion_var = CONCAT(descripcion_var, ' - Número de serie actualizado');
        END IF;
        
        IF OLD.id_sede != NEW.id_sede OR (OLD.id_sede IS NULL AND NEW.id_sede IS NOT NULL) THEN
            SET descripcion_var = CONCAT(descripcion_var, ' - Sede actualizada');
        END IF;
        
        IF OLD.imagen != NEW.imagen OR (OLD.imagen IS NULL AND NEW.imagen IS NOT NULL) THEN
            SET descripcion_var = CONCAT(descripcion_var, ' - Imagen actualizada');
        END IF;
        
        INSERT INTO auditoria_equipos (
            id_equipo,
            id_usuario,
            usuario_nombre,
            accion,
            descripcion,
            datos_anteriores,
            datos_nuevos
        ) VALUES (
            NEW.id,
            NEW.id_usuario_actualizacion,
            usuario_nombre_var,
            'MODIFICAR',
            descripcion_var,
            JSON_OBJECT(
                'codigo_patrimonial', OLD.codigo_patrimonial,
                'clasificacion', OLD.clasificacion,
                'marca', OLD.marca,
                'modelo', OLD.modelo,
                'numero_serie', OLD.numero_serie,
                'id_estado', OLD.id_estado,
                'id_sede', OLD.id_sede
            ),
            JSON_OBJECT(
                'codigo_patrimonial', NEW.codigo_patrimonial,
                'clasificacion', NEW.clasificacion,
                'marca', NEW.marca,
                'modelo', NEW.modelo,
                'numero_serie', NEW.numero_serie,
                'id_estado', NEW.id_estado,
                'id_sede', NEW.id_sede
            )
        );
    END IF;
END$$
DELIMITER ;

SELECT 'Trigger after_equipo_update recreado exitosamente' as resultado;
