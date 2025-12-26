<?php
require_once 'config/database.php';
$db = getDB();
echo "Estados en la BD:\n";
$estados = $db->query('SELECT * FROM estados_equipo')->fetchAll(PDO::FETCH_ASSOC);
foreach($estados as $e) {
    echo "  - ID: {$e['id']}, Nombre: '{$e['nombre']}'\n";
}

echo "\nConteo por estado:\n";
$conteo = $db->query("SELECT e.nombre, COUNT(eq.id) as cantidad FROM equipos eq LEFT JOIN estados_equipo e ON eq.id_estado = e.id GROUP BY e.nombre")->fetchAll(PDO::FETCH_ASSOC);
foreach($conteo as $c) {
    echo "  - {$c['nombre']}: {$c['cantidad']}\n";
}
