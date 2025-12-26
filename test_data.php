<?php
require_once 'config/database.php';

$db = getDB();

echo "=== VERIFICACIÓN DE DATOS REALES ===\n\n";

// Contar equipos
$total_equipos = $db->query('SELECT COUNT(*) FROM equipos')->fetchColumn();
echo "✓ Total Equipos: $total_equipos\n";

// Contar mantenimientos
$total_mantenimientos = $db->query('SELECT COUNT(*) FROM mantenimientos')->fetchColumn();
echo "✓ Total Mantenimientos: $total_mantenimientos\n";

// Contar sedes
$total_sedes = $db->query('SELECT COUNT(*) FROM sedes')->fetchColumn();
echo "✓ Total Sedes: $total_sedes\n";

// Estados de equipos
$estados = $db->query('SELECT e.nombre, COUNT(eq.id) as cantidad FROM equipos eq LEFT JOIN estados_equipo e ON eq.id_estado = e.id GROUP BY e.nombre')->fetchAll(PDO::FETCH_ASSOC);
echo "\n--- Equipos por Estado ---\n";
foreach ($estados as $estado) {
    echo "  - {$estado['nombre']}: {$estado['cantidad']}\n";
}

// Mantenimientos recientes
$mantenimientos = $db->query("SELECT DATE_FORMAT(fecha_mantenimiento, '%Y-%m') as mes, COUNT(*) as total FROM mantenimientos WHERE fecha_mantenimiento >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY mes ORDER BY mes DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
echo "\n--- Mantenimientos Recientes (últimos 5 meses) ---\n";
foreach ($mantenimientos as $mant) {
    echo "  - {$mant['mes']}: {$mant['total']} mantenimientos\n";
}

// Top sedes
$top_sedes = $db->query('SELECT s.nombre, COUNT(e.id) as cantidad FROM equipos e INNER JOIN sedes s ON e.id_sede = s.id GROUP BY s.nombre ORDER BY cantidad DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
echo "\n--- Top 5 Sedes con más Equipos ---\n";
foreach ($top_sedes as $sede) {
    echo "  - {$sede['nombre']}: {$sede['cantidad']} equipos\n";
}

// Marcas
$marcas = $db->query('SELECT marca, COUNT(*) as cantidad FROM equipos GROUP BY marca ORDER BY cantidad DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
echo "\n--- Top 5 Marcas ---\n";
foreach ($marcas as $marca) {
    echo "  - {$marca['marca']}: {$marca['cantidad']} equipos\n";
}

echo "\n=== FIN DE VERIFICACIÓN ===\n";
