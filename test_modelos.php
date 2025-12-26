<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Equipo.php';

$database = new Database();
$db = $database->getConnection();
$equipoModel = new Equipo($db);

// Probar el método directamente
$id_marca = 1; // HP
$modelos = $equipoModel->getModelos($id_marca);

header('Content-Type: application/json');
echo json_encode([
    'id_marca' => $id_marca,
    'count' => count($modelos),
    'modelos' => $modelos
], JSON_PRETTY_PRINT);
?>
