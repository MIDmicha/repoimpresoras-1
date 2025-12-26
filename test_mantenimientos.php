<?php
require_once 'config/database.php';

$db = getDB();

echo "=== VERIFICACIÓN DE MANTENIMIENTOS POR TIPO ===\n\n";

// Ver tipos de demanda en la BD
echo "--- Tipos de Demanda en la Base de Datos ---\n";
$tipos = $db->query("SELECT * FROM tipos_demanda")->fetchAll(PDO::FETCH_ASSOC);
foreach ($tipos as $tipo) {
    echo "  ID: {$tipo['id']} - Nombre: {$tipo['nombre']}\n";
}

// Contar mantenimientos por tipo
echo "\n--- Mantenimientos por Tipo (Consulta Actual) ---\n";
$sqlMantenimientos = "SELECT MONTH(m.fecha_mantenimiento) as mes, 
                      MONTHNAME(m.fecha_mantenimiento) as mes_nombre,
                      COUNT(*) as total, 
                      SUM(CASE WHEN td.nombre LIKE '%Preventivo%' THEN 1 ELSE 0 END) as preventivo,
                      SUM(CASE WHEN td.nombre LIKE '%Correctivo%' THEN 1 ELSE 0 END) as correctivo
                      FROM mantenimientos m 
                      LEFT JOIN tipos_demanda td ON m.id_tipo_demanda = td.id
                      WHERE m.fecha_mantenimiento >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                      GROUP BY MONTH(m.fecha_mantenimiento), MONTHNAME(m.fecha_mantenimiento) 
                      ORDER BY mes ASC";
$mantenimientos = $db->query($sqlMantenimientos)->fetchAll(PDO::FETCH_ASSOC);

if (empty($mantenimientos)) {
    echo "⚠️ No hay datos de mantenimientos en los últimos 12 meses\n";
} else {
    foreach ($mantenimientos as $mant) {
        echo "  Mes: {$mant['mes_nombre']} - Total: {$mant['total']}, ";
        echo "Preventivo: {$mant['preventivo']}, Correctivo: {$mant['correctivo']}\n";
    }
    
    $total_preventivo = array_sum(array_column($mantenimientos, 'preventivo'));
    $total_correctivo = array_sum(array_column($mantenimientos, 'correctivo'));
    
    echo "\n--- Totales ---\n";
    echo "  Total Preventivos: $total_preventivo\n";
    echo "  Total Correctivos: $total_correctivo\n";
    echo "  Gran Total: " . ($total_preventivo + $total_correctivo) . "\n";
}

// Verificar IDs de tipos de demanda usados
echo "\n--- Distribución Real de Mantenimientos ---\n";
$dist = $db->query("
    SELECT td.nombre, COUNT(*) as cantidad 
    FROM mantenimientos m 
    LEFT JOIN tipos_demanda td ON m.id_tipo_demanda = td.id 
    GROUP BY td.nombre
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($dist as $d) {
    echo "  - {$d['nombre']}: {$d['cantidad']} mantenimientos\n";
}
