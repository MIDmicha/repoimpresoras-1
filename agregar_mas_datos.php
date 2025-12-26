<?php
require_once 'config/database.php';

$db = getDB();

echo "=== AGREGANDO MÁS DATOS AL SISTEMA ===\n\n";

try {
    $db->beginTransaction();
    
    // 1. Agregar más sedes
    echo "--- Insertando Sedes ---\n";
    $sedes = [
        ['nombre' => 'Sede Central Lima', 'direccion' => 'Av. Arequipa 1234, Lima'],
        ['nombre' => 'Sede Norte - Los Olivos', 'direccion' => 'Av. Alfredo Mendiola 5678'],
        ['nombre' => 'Sede Sur - San Juan', 'direccion' => 'Av. Los Héroes 9012'],
        ['nombre' => 'Sede Este - Ate', 'direccion' => 'Carretera Central Km 10'],
        ['nombre' => 'Oficina Callao', 'direccion' => 'Av. Colonial 3456, Callao']
    ];
    
    foreach ($sedes as $sede) {
        $sql = "INSERT INTO sedes (nombre, direccion, id_distrito) VALUES (:nombre, :direccion, 1)";
        $stmt = $db->prepare($sql);
        $stmt->execute([':nombre' => $sede['nombre'], ':direccion' => $sede['direccion']]);
        echo "✓ Sede insertada: {$sede['nombre']}\n";
    }
    
    // 2. Agregar más usuarios finales
    echo "\n--- Insertando Usuarios Finales ---\n";
    $usuarios = [
        ['nombre' => 'María González Pérez', 'dni' => '43567890', 'cargo' => 'Contadora', 'telefono' => '987654321', 'email' => 'mgonzalez@sistema.gob.pe'],
        ['nombre' => 'Juan Carlos Ríos', 'dni' => '45678901', 'cargo' => 'Jefe de RRHH', 'telefono' => '987654322', 'email' => 'jrios@sistema.gob.pe'],
        ['nombre' => 'Ana Lucía Torres', 'dni' => '46789012', 'cargo' => 'Secretaria General', 'telefono' => '987654323', 'email' => 'atorres@sistema.gob.pe'],
        ['nombre' => 'Roberto Mendoza', 'dni' => '47890123', 'cargo' => 'Fiscal Adjunto', 'telefono' => '987654324', 'email' => 'rmendoza@sistema.gob.pe'],
        ['nombre' => 'Carmen Sánchez', 'dni' => '48901234', 'cargo' => 'Asistente Legal', 'telefono' => '987654325', 'email' => 'csanchez@sistema.gob.pe'],
        ['nombre' => 'Pedro Ramírez', 'dni' => '49012345', 'cargo' => 'Analista TI', 'telefono' => '987654326', 'email' => 'pramirez@sistema.gob.pe'],
        ['nombre' => 'Sofía Castro', 'dni' => '50123456', 'cargo' => 'Recepcionista', 'telefono' => '987654327', 'email' => 'scastro@sistema.gob.pe']
    ];
    
    foreach ($usuarios as $user) {
        $sql = "INSERT INTO usuarios_finales (nombre_completo, dni, cargo, telefono, email) 
                VALUES (:nombre, :dni, :cargo, :telefono, :email)";
        $stmt = $db->prepare($sql);
        $stmt->execute($user);
        echo "✓ Usuario final insertado: {$user['nombre']}\n";
    }
    
    // 3. Actualizar algunos equipos con las nuevas sedes y usuarios
    echo "\n--- Distribuyendo Equipos en Sedes ---\n";
    
    // Obtener IDs de nuevas sedes
    $sedes_ids = $db->query("SELECT id FROM sedes WHERE id > 1 ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
    
    // Obtener IDs de usuarios finales
    $usuarios_ids = $db->query("SELECT id FROM usuarios_finales ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
    
    // Actualizar equipos aleatoriamente
    $equipos = $db->query("SELECT id FROM equipos WHERE id > 1 LIMIT 15")->fetchAll(PDO::FETCH_COLUMN);
    
    $contador = 0;
    foreach ($equipos as $eq_id) {
        $sede_id = $sedes_ids[array_rand($sedes_ids)];
        $user_id = $usuarios_ids[array_rand($usuarios_ids)];
        
        $sql = "UPDATE equipos SET id_sede = :sede, id_usuario_final = :usuario WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':sede' => $sede_id, ':usuario' => $user_id, ':id' => $eq_id]);
        $contador++;
    }
    
    echo "✓ $contador equipos redistribuidos en nuevas sedes\n";
    
    $db->commit();
    
    echo "\n=== RESUMEN FINAL ===\n";
    
    // Estadísticas actualizadas
    $total_sedes = $db->query("SELECT COUNT(*) FROM sedes")->fetchColumn();
    echo "✓ Total Sedes: $total_sedes\n";
    
    $total_usuarios = $db->query("SELECT COUNT(*) FROM usuarios_finales")->fetchColumn();
    echo "✓ Total Usuarios Finales: $total_usuarios\n";
    
    echo "\n--- Equipos por Sede ---\n";
    $equipos_sede = $db->query("
        SELECT s.nombre, COUNT(e.id) as cantidad 
        FROM equipos e 
        INNER JOIN sedes s ON e.id_sede = s.id 
        GROUP BY s.nombre 
        ORDER BY cantidad DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($equipos_sede as $es) {
        echo "  - {$es['nombre']}: {$es['cantidad']} equipos\n";
    }
    
    echo "\n✅ DATOS ADICIONALES INSERTADOS EXITOSAMENTE!\n";
    
} catch (Exception $e) {
    $db->rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
