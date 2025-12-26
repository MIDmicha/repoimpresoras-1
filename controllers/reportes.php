<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'equiposPorEstado':
            equiposPorEstado($db);
            break;
            
        case 'mantenimientosPorPeriodo':
            mantenimientosPorPeriodo($db);
            break;
            
        case 'equiposPorSede':
            equiposPorSede($db);
            break;
            
        case 'equiposSinMantenimiento':
            equiposSinMantenimiento($db);
            break;
            
        case 'equiposPorMarca':
            equiposPorMarca($db);
            break;
            
        case 'equiposPorModelo':
            equiposPorModelo($db);
            break;
            
        case 'mantenimientosPorTipoDemanda':
            mantenimientosPorTipoDemanda($db);
            break;
            
        case 'mantenimientosPorTecnico':
            mantenimientosPorTecnico($db);
            break;
            
        case 'estadisticasGenerales':
            estadisticasGenerales($db);
            break;
            
        case 'tendenciaMantenimientos':
            tendenciaMantenimientos($db);
            break;
            
        case 'equiposPorDistrito':
            equiposPorDistrito($db);
            break;
            
        case 'topEquiposMantenimiento':
            topEquiposMantenimiento($db);
            break;
            
        case 'tiempoPromedioReparacion':
            tiempoPromedioReparacion($db);
            break;
            
        case 'costosMantenimiento':
            costosMantenimiento($db);
            break;
            
        case 'equiposAntiguos':
            equiposAntiguos($db);
            break;
            
        case 'resumenEjecutivo':
            resumenEjecutivo($db);
            break;
            
        case 'exportarPDF':
            exportarPDF($db);
            break;
            
        case 'exportarExcel':
            exportarExcel($db);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function equiposPorEstado($db) {
    $sql = "SELECT 
                e.nombre as estado,
                COUNT(eq.id) as cantidad,
                e.id as id_estado
            FROM estados_equipo e
            LEFT JOIN equipos eq ON e.id = eq.id_estado AND eq.activo = 1
            WHERE e.activo = 1
            GROUP BY e.id, e.nombre
            ORDER BY cantidad DESC";
    
    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $data]);
}

function mantenimientosPorPeriodo($db) {
    $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
    $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
    
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
            ORDER BY m.fecha_mantenimiento DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':fecha_inicio', $fechaInicio);
    $stmt->bindParam(':fecha_fin', $fechaFin);
    $stmt->execute();
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $data]);
}

function equiposPorSede($db) {
    $idSede = $_GET['id_sede'] ?? null;
    
    $sql = "SELECT 
                s.nombre as sede,
                COUNT(e.id) as cantidad
            FROM sedes s
            LEFT JOIN equipos e ON s.id = e.id_sede AND e.activo = 1
            WHERE s.activo = 1";
    
    if ($idSede) {
        $sql .= " AND s.id = :id_sede";
    }
    
    $sql .= " GROUP BY s.id, s.nombre
              ORDER BY cantidad DESC";
    
    $stmt = $db->prepare($sql);
    if ($idSede) {
        $stmt->bindParam(':id_sede', $idSede);
    }
    $stmt->execute();
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $data]);
}

function equiposSinMantenimiento($db) {
    $dias = $_GET['dias'] ?? 180; // Por defecto 6 meses
    
    $sql = "SELECT 
                e.id,
                e.codigo_patrimonial,
                ma.nombre as marca,
                mo.nombre as modelo,
                s.nombre as sede,
                est.nombre as estado,
                COALESCE(MAX(m.fecha_mantenimiento), e.fecha_creacion) as ultimo_mantenimiento,
                DATEDIFF(NOW(), COALESCE(MAX(m.fecha_mantenimiento), e.fecha_creacion)) as dias_sin_mantenimiento
            FROM equipos e
            LEFT JOIN mantenimientos m ON e.id = m.id_equipo
            LEFT JOIN marcas ma ON e.id_marca = ma.id
            LEFT JOIN modelos mo ON e.id_modelo = mo.id
            LEFT JOIN sedes s ON e.id_sede = s.id
            LEFT JOIN estados_equipo est ON e.id_estado = est.id
            WHERE e.activo = 1
            GROUP BY e.id
            HAVING dias_sin_mantenimiento > :dias
            ORDER BY dias_sin_mantenimiento DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':dias', $dias, PDO::PARAM_INT);
    $stmt->execute();
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $data]);
}

function equiposPorMarca($db) {
    $sql = "SELECT 
                COALESCE(m.nombre, 'Sin marca') as marca,
                COUNT(e.id) as cantidad
            FROM equipos e
            LEFT JOIN marcas m ON e.id_marca = m.id
            WHERE e.activo = 1
            GROUP BY e.id_marca, m.nombre
            ORDER BY cantidad DESC";
    
    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $data]);
}

function equiposPorModelo($db) {
    $sql = "SELECT 
                COALESCE(mo.nombre, 'Sin modelo') as modelo,
                m.nombre as marca,
                COUNT(e.id) as cantidad
            FROM equipos e
            LEFT JOIN modelos mo ON e.id_modelo = mo.id
            LEFT JOIN marcas m ON e.id_marca = m.id
            WHERE e.activo = 1
            GROUP BY e.id_modelo, mo.nombre, m.nombre
            ORDER BY cantidad DESC
            LIMIT 20";
    
    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $data]);
}

function mantenimientosPorTipoDemanda($db) {
    $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
    $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
    
    $sql = "SELECT 
                td.nombre as tipo_demanda,
                COUNT(m.id) as cantidad
            FROM tipos_demanda td
            LEFT JOIN mantenimientos m ON td.id = m.id_tipo_demanda 
                AND m.fecha_mantenimiento BETWEEN :fecha_inicio AND :fecha_fin
            WHERE td.activo = 1
            GROUP BY td.id, td.nombre
            ORDER BY cantidad DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':fecha_inicio', $fechaInicio);
    $stmt->bindParam(':fecha_fin', $fechaFin);
    $stmt->execute();
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $data]);
}

function mantenimientosPorTecnico($db) {
    $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
    $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
    
    $sql = "SELECT 
                COALESCE(m.tecnico_responsable, 'Sin asignar') as tecnico,
                COUNT(m.id) as cantidad,
                COUNT(DISTINCT m.id_equipo) as equipos_atendidos
            FROM mantenimientos m
            WHERE m.fecha_mantenimiento BETWEEN :fecha_inicio AND :fecha_fin
            GROUP BY m.tecnico_responsable
            ORDER BY cantidad DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':fecha_inicio', $fechaInicio);
    $stmt->bindParam(':fecha_fin', $fechaFin);
    $stmt->execute();
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $data]);
}

function estadisticasGenerales($db) {
    // Total de equipos
    $sqlTotalEquipos = "SELECT COUNT(*) as total FROM equipos WHERE activo = 1";
    $total_equipos = $db->query($sqlTotalEquipos)->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Equipos operativos
    $sqlOperativos = "SELECT COUNT(*) as total FROM equipos WHERE activo = 1 AND id_estado = 1";
    $equipos_operativos = $db->query($sqlOperativos)->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total mantenimientos este mes
    $sqlMantMes = "SELECT COUNT(*) as total FROM mantenimientos 
                   WHERE MONTH(fecha_mantenimiento) = MONTH(NOW()) 
                   AND YEAR(fecha_mantenimiento) = YEAR(NOW())
                   AND activo = 1";
    $mantenimientos_mes = $db->query($sqlMantMes)->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total mantenimientos este año
    $sqlMantAnio = "SELECT COUNT(*) as total FROM mantenimientos 
                    WHERE YEAR(fecha_mantenimiento) = YEAR(NOW())
                    AND activo = 1";
    $mantenimientos_anio = $db->query($sqlMantAnio)->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Equipos por reparar (inoperativos)
    $sqlPorReparar = "SELECT COUNT(*) as total FROM equipos WHERE activo = 1 AND id_estado = 3";
    $equipos_por_reparar = $db->query($sqlPorReparar)->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Equipos en mantenimiento
    $sqlEnMantenimiento = "SELECT COUNT(*) as total FROM equipos WHERE activo = 1 AND id_estado = 2";
    $equipos_en_mantenimiento = $db->query($sqlEnMantenimiento)->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Promedio mantenimientos por equipo
    $sqlPromedio = "SELECT AVG(total_mant) as promedio FROM (
                        SELECT COUNT(*) as total_mant 
                        FROM mantenimientos 
                        WHERE activo = 1 
                        GROUP BY id_equipo
                    ) as subquery";
    $promedio_mantenimientos = $db->query($sqlPromedio)->fetch(PDO::FETCH_ASSOC)['promedio'] ?? 0;
    
    $data = [
        'total_equipos' => $total_equipos,
        'equipos_operativos' => $equipos_operativos,
        'equipos_por_reparar' => $equipos_por_reparar,
        'equipos_en_mantenimiento' => $equipos_en_mantenimiento,
        'mantenimientos_mes' => $mantenimientos_mes,
        'mantenimientos_anio' => $mantenimientos_anio,
        'promedio_mantenimientos' => round($promedio_mantenimientos, 2),
        'porcentaje_operativos' => $total_equipos > 0 ? round(($equipos_operativos / $total_equipos) * 100, 1) : 0
    ];
    
    echo json_encode(['success' => true, 'data' => $data]);
}

function tendenciaMantenimientos($db) {
    $meses = $_GET['meses'] ?? 12;
    
    $sql = "SELECT 
                DATE_FORMAT(fecha_mantenimiento, '%Y-%m') as mes,
                DATE_FORMAT(fecha_mantenimiento, '%b %Y') as mes_nombre,
                COUNT(*) as cantidad
            FROM mantenimientos
            WHERE fecha_mantenimiento >= DATE_SUB(NOW(), INTERVAL :meses MONTH)
            AND activo = 1
            GROUP BY DATE_FORMAT(fecha_mantenimiento, '%Y-%m')
            ORDER BY mes ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':meses', $meses, PDO::PARAM_INT);
    $stmt->execute();
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $data]);
}

function equiposPorDistrito($db) {
    $sql = "SELECT 
                d.nombre as distrito,
                COUNT(e.id) as cantidad
            FROM distritos_fiscales d
            LEFT JOIN sedes s ON d.id = s.id_distrito_fiscal
            LEFT JOIN equipos e ON s.id = e.id_sede AND e.activo = 1
            WHERE d.activo = 1
            GROUP BY d.id, d.nombre
            ORDER BY cantidad DESC";
    
    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $data]);
}

function topEquiposMantenimiento($db) {
    $limite = $_GET['limite'] ?? 10;
    
    $sql = "SELECT 
                e.codigo_patrimonial,
                ma.nombre as marca,
                mo.nombre as modelo,
                s.nombre as sede,
                COUNT(m.id) as total_mantenimientos,
                MAX(m.fecha_mantenimiento) as ultimo_mantenimiento
            FROM equipos e
            LEFT JOIN mantenimientos m ON e.id = m.id_equipo
            LEFT JOIN marcas ma ON e.id_marca = ma.id
            LEFT JOIN modelos mo ON e.id_modelo = mo.id
            LEFT JOIN sedes s ON e.id_sede = s.id
            WHERE e.activo = 1
            GROUP BY e.id
            ORDER BY total_mantenimientos DESC
            LIMIT :limite";
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $data]);
}

function tiempoPromedioReparacion($db) {
    // Calcular tiempo promedio entre mantenimientos por estado
    $sql = "SELECT 
                est.nombre as estado,
                AVG(DATEDIFF(m2.fecha_mantenimiento, m1.fecha_mantenimiento)) as dias_promedio,
                COUNT(*) as total_casos
            FROM mantenimientos m1
            JOIN mantenimientos m2 ON m1.id_equipo = m2.id_equipo 
                AND m2.fecha_mantenimiento > m1.fecha_mantenimiento
            LEFT JOIN estados_equipo est ON m2.id_estado_nuevo = est.id
            WHERE m1.activo = 1 AND m2.activo = 1
            AND m2.id = (
                SELECT MIN(id) FROM mantenimientos 
                WHERE id_equipo = m1.id_equipo 
                AND fecha_mantenimiento > m1.fecha_mantenimiento
                AND activo = 1
            )
            GROUP BY est.id, est.nombre
            HAVING total_casos > 0
            ORDER BY dias_promedio ASC";
    
    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $data]);
}

function costosMantenimiento($db) {
    // Simulación de costos (puedes agregar un campo real en la BD)
    $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
    $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
    
    $sql = "SELECT 
                DATE_FORMAT(m.fecha_mantenimiento, '%Y-%m') as mes,
                td.nombre as tipo_demanda,
                COUNT(*) as cantidad,
                COUNT(*) * 150 as costo_estimado
            FROM mantenimientos m
            LEFT JOIN tipos_demanda td ON m.id_tipo_demanda = td.id
            WHERE m.fecha_mantenimiento BETWEEN :fecha_inicio AND :fecha_fin
            GROUP BY DATE_FORMAT(m.fecha_mantenimiento, '%Y-%m'), td.nombre
            ORDER BY mes DESC, cantidad DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':fecha_inicio', $fechaInicio);
    $stmt->bindParam(':fecha_fin', $fechaFin);
    $stmt->execute();
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $data]);
}

function equiposAntiguos($db) {
    $sql = "SELECT 
                e.codigo_patrimonial,
                ma.nombre as marca,
                mo.nombre as modelo,
                s.nombre as sede,
                est.nombre as estado,
                e.fecha_registro,
                YEAR(CURDATE()) - YEAR(e.fecha_registro) as antiguedad_anios,
                COUNT(m.id) as total_mantenimientos
            FROM equipos e
            LEFT JOIN marcas ma ON e.id_marca = ma.id
            LEFT JOIN modelos mo ON e.id_modelo = mo.id
            LEFT JOIN sedes s ON e.id_sede = s.id
            LEFT JOIN estados_equipo est ON e.id_estado = est.id
            LEFT JOIN mantenimientos m ON e.id = m.id_equipo
            WHERE e.activo = 1
            AND YEAR(CURDATE()) - YEAR(e.fecha_registro) >= 3
            GROUP BY e.id
            ORDER BY antiguedad_anios DESC, total_mantenimientos DESC
            LIMIT 50";
    
    $stmt = $db->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $data]);
}

function resumenEjecutivo($db) {
    // Estadísticas generales
    $estadisticas = [];
    
    // Total equipos
    $sql = "SELECT COUNT(*) as total FROM equipos WHERE activo = 1";
    $estadisticas['total_equipos'] = $db->query($sql)->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Equipos por estado
    $sql = "SELECT e.nombre as estado, COUNT(eq.id) as cantidad 
            FROM estados_equipo e 
            LEFT JOIN equipos eq ON e.id = eq.id_estado AND eq.activo = 1 
            WHERE e.activo = 1 
            GROUP BY e.id, e.nombre";
    $estadisticas['equipos_por_estado'] = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    
    // Mantenimientos últimos 30 días
    $sql = "SELECT COUNT(*) as total FROM mantenimientos 
            WHERE fecha_mantenimiento >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $estadisticas['mantenimientos_mes'] = $db->query($sql)->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Top 5 sedes con más equipos
    $sql = "SELECT s.nombre as sede, COUNT(e.id) as cantidad 
            FROM sedes s 
            LEFT JOIN equipos e ON s.id = e.id_sede AND e.activo = 1 
            WHERE s.activo = 1 
            GROUP BY s.id, s.nombre 
            ORDER BY cantidad DESC 
            LIMIT 5";
    $estadisticas['top_sedes'] = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    
    // Top 5 marcas
    $sql = "SELECT m.nombre as marca, COUNT(e.id) as cantidad 
            FROM marcas m 
            LEFT JOIN equipos e ON m.id = e.id_marca AND e.activo = 1 
            WHERE m.activo = 1 
            GROUP BY m.id, m.nombre 
            ORDER BY cantidad DESC 
            LIMIT 5";
    $estadisticas['top_marcas'] = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $estadisticas]);
}

function exportarExcel($db) {
    $tipo = $_GET['tipo'] ?? 'general';
    
    // Headers para Excel
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="reporte_' . $tipo . '_' . date('Y-m-d_His') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Comenzar HTML para Excel
    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    echo "<html xmlns:x='urn:schemas-microsoft-com:office:excel'>";
    echo "<head>";
    echo "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>";
    echo "<style>";
    echo "table { border-collapse: collapse; width: 100%; }";
    echo "th, td { border: 1px solid #000; padding: 8px; text-align: left; }";
    echo "th { background-color: #4472C4; color: white; font-weight: bold; }";
    echo "tr:nth-child(even) { background-color: #D9E2F3; }";
    echo "</style>";
    echo "</head>";
    echo "<body>";
    
    echo "<h2>Reporte de " . ucfirst($tipo) . " - " . date('d/m/Y H:i:s') . "</h2>";
    
    // Generar contenido según el tipo
    switch($tipo) {
        case 'sedes':
            $sql = "SELECT s.nombre as sede, COUNT(e.id) as cantidad
                    FROM sedes s
                    LEFT JOIN equipos e ON s.id = e.id_sede AND e.activo = 1
                    WHERE s.activo = 1
                    GROUP BY s.id, s.nombre
                    ORDER BY cantidad DESC";
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table>";
            echo "<thead><tr><th>Sede</th><th>Cantidad de Equipos</th></tr></thead>";
            echo "<tbody>";
            foreach($data as $row) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['sede']) . "</td>";
                echo "<td style='text-align:right'>" . $row['cantidad'] . "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "<tr><th>TOTAL</th><th style='text-align:right'>" . array_sum(array_column($data, 'cantidad')) . "</th></tr>";
            echo "</table>";
            break;
            
        case 'estados':
            $sql = "SELECT e.nombre as estado, COUNT(eq.id) as cantidad
                    FROM estados_equipo e
                    LEFT JOIN equipos eq ON e.id = eq.id_estado AND eq.activo = 1
                    WHERE e.activo = 1
                    GROUP BY e.id, e.nombre
                    ORDER BY cantidad DESC";
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table>";
            echo "<thead><tr><th>Estado</th><th>Cantidad de Equipos</th><th>Porcentaje</th></tr></thead>";
            echo "<tbody>";
            $total = array_sum(array_column($data, 'cantidad'));
            foreach($data as $row) {
                $porcentaje = $total > 0 ? round(($row['cantidad'] / $total) * 100, 1) : 0;
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['estado']) . "</td>";
                echo "<td style='text-align:right'>" . $row['cantidad'] . "</td>";
                echo "<td style='text-align:right'>" . $porcentaje . "%</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "<tr><th>TOTAL</th><th style='text-align:right'>" . $total . "</th><th>100%</th></tr>";
            echo "</table>";
            break;
            
        case 'mantenimientos':
            $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
            $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
            
            $sql = "SELECT 
                        m.fecha_mantenimiento,
                        e.codigo_patrimonial,
                        ma.nombre as marca,
                        mo.nombre as modelo,
                        m.tecnico_responsable,
                        td.nombre as tipo_demanda,
                        est.nombre as estado_nuevo
                    FROM mantenimientos m
                    INNER JOIN equipos e ON m.id_equipo = e.id
                    LEFT JOIN marcas ma ON e.id_marca = ma.id
                    LEFT JOIN modelos mo ON e.id_modelo = mo.id
                    LEFT JOIN tipos_demanda td ON m.id_tipo_demanda = td.id
                    LEFT JOIN estados_equipo est ON m.id_estado_nuevo = est.id
                    WHERE m.fecha_mantenimiento BETWEEN :fecha_inicio AND :fecha_fin
                    ORDER BY m.fecha_mantenimiento DESC";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':fecha_inicio', $fechaInicio);
            $stmt->bindParam(':fecha_fin', $fechaFin);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<p><strong>Período:</strong> " . date('d/m/Y', strtotime($fechaInicio)) . " - " . date('d/m/Y', strtotime($fechaFin)) . "</p>";
            echo "<table>";
            echo "<thead><tr><th>Fecha</th><th>Código</th><th>Marca</th><th>Modelo</th><th>Técnico</th><th>Tipo Demanda</th><th>Estado</th></tr></thead>";
            echo "<tbody>";
            foreach($data as $row) {
                echo "<tr>";
                echo "<td>" . date('d/m/Y', strtotime($row['fecha_mantenimiento'])) . "</td>";
                echo "<td>" . htmlspecialchars($row['codigo_patrimonial']) . "</td>";
                echo "<td>" . htmlspecialchars($row['marca'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($row['modelo'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($row['tecnico_responsable'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($row['tipo_demanda'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($row['estado_nuevo'] ?? 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            echo "<p><strong>Total de mantenimientos:</strong> " . count($data) . "</p>";
            break;
            
        default:
            // Reporte general - estadísticas
            estadisticasGenerales($db);
            return;
    }
    
    echo "</body></html>";
}

function exportarPDF($db) {
    $tipo = $_GET['tipo'] ?? 'general';
    
    // Simple HTML to PDF conversion
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="reporte_' . $tipo . '_' . date('Y-m-d_His') . '.pdf"');
    
    // For now, return HTML that can be printed as PDF
    // In production, use TCPDF, FPDF or mPDF
    
    echo '%PDF-1.4
%âãÏÓ
1 0 obj
<<
/Type /Catalog
/Pages 2 0 R
>>
endobj
2 0 obj
<<
/Type /Pages
/Kids [3 0 R]
/Count 1
>>
endobj
3 0 obj
<<
/Type /Page
/Parent 2 0 R
/Resources <<
/Font <<
/F1 4 0 R
>>
>>
/MediaBox [0 0 612 792]
/Contents 5 0 R
>>
endobj
4 0 obj
<<
/Type /Font
/Subtype /Type1
/BaseFont /Helvetica
>>
endobj
5 0 obj
<<
/Length 200
>>
stream
BT
/F1 12 Tf
50 750 Td
(Reporte de ' . ucfirst($tipo) . ' - ' . date('d/m/Y H:i:s') . ') Tj
0 -20 Td
(Para una mejor experiencia, use la opcion Imprimir del navegador) Tj
0 -20 Td
(o exporte a Excel para analisis detallado.) Tj
ET
endstream
endobj
xref
0 6
0000000000 65535 f
0000000009 00000 n
0000000058 00000 n
0000000115 00000 n
0000000274 00000 n
0000000361 00000 n
trailer
<<
/Size 6
/Root 1 0 R
>>
startxref
611
%%EOF';
}

?>
