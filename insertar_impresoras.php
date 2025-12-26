<?php
require_once 'config/database.php';

$db = getDB();

echo "=== INSERTANDO 20 IMPRESORAS AL SISTEMA ===\n\n";

// Datos realistas para impresoras
$marcas = ['HP', 'Epson', 'Canon', 'Brother', 'Samsung', 'Xerox', 'Ricoh', 'Kyocera'];
$modelos_hp = ['LaserJet Pro M404dn', 'LaserJet Pro MFP M428fdw', 'OfficeJet Pro 9015', 'DeskJet 2720'];
$modelos_epson = ['EcoTank L3250', 'WorkForce Pro WF-C5790', 'Expression Premium XP-7100', 'L4260'];
$modelos_canon = ['PIXMA G3110', 'imageCLASS MF445dw', 'MAXIFY GX7020', 'PIXMA TR8620'];
$modelos_brother = ['HL-L2395DW', 'MFC-L2750DW', 'DCP-L2550DW', 'HL-L8360CDW'];
$modelos_samsung = ['Xpress M2020W', 'ProXpress M4530ND', 'ML-2525', 'SL-M2070FW'];

$clasificaciones = ['impresora', 'multifuncional'];
$estados = [1, 1, 1, 1, 1, 2, 2, 3]; // Mayoría operativos
$anios = [2020, 2021, 2021, 2022, 2022, 2023, 2023, 2024];
$ubicaciones = [
    'Oficina Principal', 'Sala de Reuniones', 'Área de Contabilidad', 
    'Recursos Humanos', 'Gerencia', 'Recepción', 'Área Legal',
    'Despacho Fiscal', 'Secretaría General', 'Archivo Central',
    'Sala de Juntas', 'Oficina Administrativa', 'Centro de Cómputo'
];

try {
    $db->beginTransaction();
    
    $contador_exitosos = 0;
    
    for ($i = 1; $i <= 20; $i++) {
        // Generar datos aleatorios pero realistas
        $codigo = sprintf("IMP-%04d", 100 + $i);
        $marca = $marcas[array_rand($marcas)];
        
        // Seleccionar modelo según marca
        switch ($marca) {
            case 'HP':
                $modelo = $modelos_hp[array_rand($modelos_hp)];
                break;
            case 'Epson':
                $modelo = $modelos_epson[array_rand($modelos_epson)];
                break;
            case 'Canon':
                $modelo = $modelos_canon[array_rand($modelos_canon)];
                break;
            case 'Brother':
                $modelo = $modelos_brother[array_rand($modelos_brother)];
                break;
            case 'Samsung':
                $modelo = $modelos_samsung[array_rand($modelos_samsung)];
                break;
            default:
                $modelo = "Series " . rand(1000, 9999);
        }
        
        $clasificacion = $clasificaciones[array_rand($clasificaciones)];
        $numero_serie = strtoupper(substr(md5(uniqid()), 0, 15));
        $estado = $estados[array_rand($estados)];
        $anio = $anios[array_rand($anios)];
        $tiene_estabilizador = rand(0, 1);
        $ubicacion = $ubicaciones[array_rand($ubicaciones)];
        
        $observaciones_list = [
            'Equipo en buen estado',
            'Requiere revisión periódica',
            'Uso intensivo diario',
            'Equipo compartido entre departamentos',
            'Impresora principal del área',
            'Backup de equipo principal',
            'Uso ocasional para reportes'
        ];
        $observaciones = $observaciones_list[array_rand($observaciones_list)];
        
        $sql = "INSERT INTO equipos (
            codigo_patrimonial, 
            clasificacion, 
            marca, 
            modelo, 
            numero_serie, 
            garantia, 
            id_estado, 
            tiene_estabilizador, 
            anio_adquisicion, 
            id_distrito, 
            id_sede, 
            id_macro_proceso, 
            ubicacion_fisica, 
            id_despacho, 
            id_usuario_final, 
            observaciones, 
            id_usuario_creacion
        ) VALUES (
            :codigo, 
            :clasificacion, 
            :marca, 
            :modelo, 
            :serie, 
            '24 meses', 
            :estado, 
            :estabilizador, 
            :anio, 
            1, 
            1, 
            1, 
            :ubicacion, 
            1, 
            1, 
            :obs, 
            1
        )";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':codigo' => $codigo,
            ':clasificacion' => $clasificacion,
            ':marca' => $marca,
            ':modelo' => $modelo,
            ':serie' => $numero_serie,
            ':estado' => $estado,
            ':estabilizador' => $tiene_estabilizador,
            ':anio' => $anio,
            ':ubicacion' => $ubicacion,
            ':obs' => $observaciones
        ]);
        
        $contador_exitosos++;
        echo "✓ Impresora $i insertada: $codigo - $marca $modelo [$clasificacion]\n";
    }
    
    $db->commit();
    
    echo "\n=== RESUMEN ===\n";
    echo "✓ Total de impresoras insertadas: $contador_exitosos\n";
    
    // Verificar total en la base de datos
    $total = $db->query('SELECT COUNT(*) FROM equipos')->fetchColumn();
    echo "✓ Total de equipos en el sistema: $total\n";
    
    // Mostrar estadísticas
    echo "\n--- Estadísticas por Estado ---\n";
    $stats = $db->query("
        SELECT e.nombre as estado, COUNT(eq.id) as cantidad 
        FROM equipos eq 
        LEFT JOIN estados_equipo e ON eq.id_estado = e.id 
        GROUP BY e.nombre
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($stats as $stat) {
        echo "  - {$stat['estado']}: {$stat['cantidad']} equipos\n";
    }
    
    echo "\n--- Estadísticas por Marca ---\n";
    $marcas_stats = $db->query("
        SELECT marca, COUNT(*) as cantidad 
        FROM equipos 
        GROUP BY marca 
        ORDER BY cantidad DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($marcas_stats as $marca_stat) {
        echo "  - {$marca_stat['marca']}: {$marca_stat['cantidad']} equipos\n";
    }
    
    echo "\n✅ PROCESO COMPLETADO EXITOSAMENTE!\n";
    
} catch (Exception $e) {
    $db->rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
