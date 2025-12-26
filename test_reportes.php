<?php
require_once __DIR__ . '/config/database.php';

session_start();
$_SESSION['user_id'] = 1; // Simular sesión activa

$database = new Database();
$db = $database->getConnection();

echo "<h2>Test de Reportes</h2>";

// Test 1: Verificar conexión
echo "<h3>1. Conexión a BD:</h3>";
if ($db) {
    echo "✅ Conexión exitosa<br>";
} else {
    echo "❌ Error de conexión<br>";
    exit;
}

// Test 2: Contar mantenimientos
echo "<h3>2. Mantenimientos en BD:</h3>";
$sql = "SELECT COUNT(*) as total FROM mantenimientos WHERE activo = 1";
$stmt = $db->query($sql);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Total de mantenimientos activos: " . $result['total'] . "<br>";

// Test 3: Mantenimientos con detalles
echo "<h3>3. Últimos 5 mantenimientos:</h3>";
$sql = "SELECT 
            m.id,
            m.fecha_mantenimiento,
            m.tecnico_responsable,
            e.codigo_patrimonial,
            td.nombre as tipo_demanda
        FROM mantenimientos m
        INNER JOIN equipos e ON m.id_equipo = e.id
        LEFT JOIN tipos_demanda td ON m.id_tipo_demanda = td.id
        WHERE m.activo = 1
        ORDER BY m.fecha_mantenimiento DESC
        LIMIT 5";
$stmt = $db->query($sql);
$mantenimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($mantenimientos) > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Fecha</th><th>Código Equipo</th><th>Tipo Demanda</th><th>Técnico</th></tr>";
    foreach ($mantenimientos as $m) {
        echo "<tr>";
        echo "<td>" . $m['id'] . "</td>";
        echo "<td>" . $m['fecha_mantenimiento'] . "</td>";
        echo "<td>" . $m['codigo_patrimonial'] . "</td>";
        echo "<td>" . ($m['tipo_demanda'] ?? 'N/A') . "</td>";
        echo "<td>" . ($m['tecnico_responsable'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "⚠️ No hay mantenimientos registrados<br>";
}

// Test 4: Probar consulta del controlador
echo "<h3>4. Test de consulta mantenimientosPorPeriodo:</h3>";
$fechaInicio = date('Y-m-01');
$fechaFin = date('Y-m-d');
echo "Rango: $fechaInicio a $fechaFin<br>";

$sql = "SELECT 
            m.id,
            m.fecha_mantenimiento,
            m.tecnico_responsable,
            e.codigo_patrimonial,
            ma.nombre as marca,
            mo.nombre as modelo,
            td.nombre as tipo_demanda,
            est.nombre as estado_nuevo,
            m.descripcion
        FROM mantenimientos m
        INNER JOIN equipos e ON m.id_equipo = e.id
        LEFT JOIN marcas ma ON e.id_marca = ma.id
        LEFT JOIN modelos mo ON e.id_modelo = mo.id
        LEFT JOIN tipos_demanda td ON m.id_tipo_demanda = td.id
        LEFT JOIN estados_equipo est ON m.id_estado_nuevo = est.id
        WHERE m.fecha_mantenimiento BETWEEN :fecha_inicio AND :fecha_fin
        AND m.activo = 1
        ORDER BY m.fecha_mantenimiento DESC";

$stmt = $db->prepare($sql);
$stmt->bindParam(':fecha_inicio', $fechaInicio);
$stmt->bindParam(':fecha_fin', $fechaFin);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Encontrados en este mes: " . count($data) . "<br>";

if (count($data) > 0) {
    echo "<pre>";
    print_r(array_slice($data, 0, 2)); // Mostrar primeros 2
    echo "</pre>";
}

// Test 5: Verificar que el controlador responde
echo "<h3>5. Test del controlador:</h3>";
echo "<a href='controllers/reportes.php?action=mantenimientosPorPeriodo&fecha_inicio=$fechaInicio&fecha_fin=$fechaFin' target='_blank'>Ver JSON de mantenimientos</a><br>";
echo "<a href='controllers/reportes.php?action=equiposPorEstado' target='_blank'>Ver JSON de equipos por estado</a><br>";
echo "<a href='controllers/reportes.php?action=equiposSinMantenimiento&dias=90' target='_blank'>Ver JSON de equipos sin mantenimiento</a><br>";
?>
