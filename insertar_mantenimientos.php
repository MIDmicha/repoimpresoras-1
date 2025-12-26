<?php
require_once 'config/database.php';

$db = getDB();

echo "=== INSERTANDO MANTENIMIENTOS REALISTAS ===\n\n";

try {
    $db->beginTransaction();
    
    // Obtener todos los equipos
    $equipos = $db->query("SELECT id FROM equipos WHERE activo = 1")->fetchAll(PDO::FETCH_COLUMN);
    
    // Obtener tipos de demanda (1=Preventivo, 2=Correctivo)
    $tipos = [1, 2];
    
    $contador = 0;
    
    // Generar mantenimientos para los últimos 12 meses
    $fecha_inicio = strtotime('-12 months');
    $fecha_fin = time();
    
    foreach ($equipos as $id_equipo) {
        // Cada equipo tendrá entre 2 y 6 mantenimientos en el año
        $num_mantenimientos = rand(2, 6);
        
        for ($i = 0; $i < $num_mantenimientos; $i++) {
            // Fecha aleatoria en los últimos 12 meses
            $fecha_random = rand($fecha_inicio, $fecha_fin);
            $fecha = date('Y-m-d', $fecha_random);
            
            // Alternar entre preventivo y correctivo (80% preventivo)
            $tipo = (rand(1, 100) <= 80) ? 1 : 2;
            
            $descripciones_preventivo = [
                'Limpieza general del equipo',
                'Revisión de rodillos y bandeja',
                'Actualización de firmware',
                'Lubricación de componentes',
                'Limpieza de cabezales de impresión',
                'Verificación de conexiones eléctricas',
                'Calibración del sistema'
            ];
            
            $descripciones_correctivo = [
                'Reemplazo de cartucho defectuoso',
                'Reparación de atasco de papel',
                'Cambio de fusor',
                'Reemplazo de rodillo alimentador',
                'Reparación de sistema de red',
                'Cambio de placa lógica',
                'Reparación de bandeja de papel'
            ];
            
            $descripcion = ($tipo == 1) 
                ? $descripciones_preventivo[array_rand($descripciones_preventivo)]
                : $descripciones_correctivo[array_rand($descripciones_correctivo)];
            
            $tecnicos = [
                'Juan Pérez',
                'María González',
                'Carlos Rodríguez',
                'Ana López',
                'Roberto Martínez'
            ];
            
            $observaciones = [
                'Mantenimiento completado exitosamente',
                'Se recomienda seguimiento en 3 meses',
                'Equipo funcionando correctamente',
                'Se realizaron todas las pruebas',
                'Trabajo realizado satisfactoriamente'
            ];
            
            $sql = "INSERT INTO mantenimientos (
                id_equipo,
                id_tipo_demanda,
                fecha_mantenimiento,
                descripcion,
                tecnico_responsable,
                observaciones,
                id_usuario_registro
            ) VALUES (
                :id_equipo,
                :tipo,
                :fecha,
                :descripcion,
                :tecnico,
                :observaciones,
                1
            )";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':id_equipo' => $id_equipo,
                ':tipo' => $tipo,
                ':fecha' => $fecha,
                ':descripcion' => $descripcion,
                ':tecnico' => $tecnicos[array_rand($tecnicos)],
                ':observaciones' => $observaciones[array_rand($observaciones)]
            ]);
            
            $contador++;
        }
    }
    
    $db->commit();
    
    echo "✓ Total de mantenimientos insertados: $contador\n\n";
    
    // Mostrar estadísticas
    echo "--- Mantenimientos por Tipo ---\n";
    $stats_tipo = $db->query("
        SELECT td.nombre, COUNT(*) as cantidad 
        FROM mantenimientos m 
        LEFT JOIN tipos_demanda td ON m.id_tipo_demanda = td.id 
        GROUP BY td.nombre
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($stats_tipo as $stat) {
        echo "  - {$stat['nombre']}: {$stat['cantidad']} mantenimientos\n";
    }
    
    echo "\n--- Mantenimientos por Mes (últimos 6 meses) ---\n";
    $stats_mes = $db->query("
        SELECT 
            DATE_FORMAT(fecha_mantenimiento, '%Y-%m') as mes,
            DATE_FORMAT(fecha_mantenimiento, '%b %Y') as mes_nombre,
            COUNT(*) as total,
            SUM(CASE WHEN td.nombre = 'Preventivo' THEN 1 ELSE 0 END) as preventivo,
            SUM(CASE WHEN td.nombre = 'Correctivo' THEN 1 ELSE 0 END) as correctivo
        FROM mantenimientos m 
        LEFT JOIN tipos_demanda td ON m.id_tipo_demanda = td.id
        WHERE m.fecha_mantenimiento >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY mes, mes_nombre 
        ORDER BY mes DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($stats_mes as $stat) {
        echo "  - {$stat['mes_nombre']}: {$stat['total']} total (Preventivo: {$stat['preventivo']}, Correctivo: {$stat['correctivo']})\n";
    }
    
    echo "\n✅ PROCESO COMPLETADO EXITOSAMENTE!\n";
    echo "\n🎯 Ahora el dashboard mostrará datos reales e interesantes!\n";
    
} catch (Exception $e) {
    $db->rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
