-- Crear tabla de auditoría para equipos
CREATE TABLE IF NOT EXISTS auditoria_equipos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_equipo INT NOT NULL,
    id_usuario INT DEFAULT NULL,
    usuario_nombre VARCHAR(100) DEFAULT NULL,
    accion VARCHAR(50) NOT NULL COMMENT 'CREAR, MODIFICAR, ELIMINAR, CAMBIO_ESTADO, etc',
    descripcion TEXT DEFAULT NULL,
    datos_anteriores JSON DEFAULT NULL,
    datos_nuevos JSON DEFAULT NULL,
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) DEFAULT NULL,
    INDEX idx_equipo (id_equipo),
    INDEX idx_usuario (id_usuario),
    INDEX idx_fecha (fecha_hora),
    FOREIGN KEY (id_equipo) REFERENCES equipos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trigger para INSERT (Crear equipo)
DELIMITER $$
CREATE TRIGGER after_equipo_insert
AFTER INSERT ON equipos
FOR EACH ROW
BEGIN
    DECLARE usuario_nombre_var VARCHAR(100);
    
    -- Obtener nombre del usuario
    SELECT nombre_completo INTO usuario_nombre_var
    FROM usuarios WHERE id = NEW.id_usuario_creacion;
    
    INSERT INTO auditoria_equipos (
        id_equipo,
        id_usuario,
        usuario_nombre,
        accion,
        descripcion,
        datos_nuevos,
        ip_address
    ) VALUES (
        NEW.id,
        NEW.id_usuario_creacion,
        usuario_nombre_var,
        'CREAR',
        CONCAT('Equipo creado: ', NEW.codigo_patrimonial),
        JSON_OBJECT(
            'codigo_patrimonial', NEW.codigo_patrimonial,
            'clasificacion', NEW.clasificacion,
            'marca', NEW.marca,
            'modelo', NEW.modelo,
            'id_estado', NEW.id_estado,
            'numero_serie', NEW.numero_serie
        ),
        NULL
    );
END$$

-- Trigger para UPDATE (Modificar equipo)
DELIMITER $$
CREATE TRIGGER after_equipo_update
AFTER UPDATE ON equipos
FOR EACH ROW
BEGIN
    DECLARE usuario_nombre_var VARCHAR(100);
    DECLARE descripcion_var TEXT;
    DECLARE estado_anterior VARCHAR(50);
    DECLARE estado_nuevo VARCHAR(50);
    
    -- Obtener nombre del usuario
    SELECT nombre_completo INTO usuario_nombre_var
    FROM usuarios WHERE id = NEW.id_usuario_actualizacion;
    
    -- Detectar tipo de cambio
    IF OLD.activo = 1 AND NEW.activo = 0 THEN
        -- Eliminación lógica
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
        -- Cambio de estado
        SELECT nombre INTO estado_anterior FROM estados_equipo WHERE id = OLD.id_estado;
        SELECT nombre INTO estado_nuevo FROM estados_equipo WHERE id = NEW.id_estado;
        
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
        -- Modificación general
        SET descripcion_var = 'Equipo modificado';
        
        -- Detectar cambios específicos
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
