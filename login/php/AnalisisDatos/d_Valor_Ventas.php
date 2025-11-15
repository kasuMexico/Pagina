<?php
/**************************************************************************************************
 * ARCHIVO: e_Ventas_Pagadas.php
 * Qué hace: Genera un DataTable JSON con el MONTO TOTAL (CostoVenta) de pólizas ACTIVAS por producto.
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 *
 * Compatibilidad PHP 8.2:
 * - Uso de etiquetas completas <?php
 * - Manejo de errores con mysqli_report y try/catch
 * - Salida JSON UTF-8 con estructura Google DataTable (cols + rows)
 **************************************************************************************************/

declare(strict_types=1);

session_start();
date_default_timezone_set('America/Mexico_City');

header('Content-Type: application/json; charset=utf-8');

require_once '../../../eia/librerias.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
        throw new RuntimeException('Conexión no inicializada.');
    }
    $mysqli->set_charset('utf8mb4');

    // Estructura base DataTable
    $data = [
        'cols' => [
            ['label' => 'Producto',       'type' => 'string'],
            ['label' => 'Monto (MXN)',    'type' => 'number'],
        ],
        'rows' => [],
    ];

    // Solo necesitamos la columna Producto
    $res = $mysqli->query("SELECT Producto FROM Productos");
    foreach ($res as $row) {
        $producto = (string)$row['Producto'];

        // Monto total de ventas ACTIVAS por producto
        // Usa helper existente en tu clase Basicas: Sumar2cond($tabla,$campo,$c1,$v1,$c2,$v2)
        $monto = (float)$basicas->Sumar2cond(
            $mysqli,
            'CostoVenta',
            'Venta',
            'Producto', $producto,
            'Status',   'ACTIVO'
        );

        $data['rows'][] = [
            'c' => [
                ['v' => $producto],
                ['v' => $monto],
            ],
        ];
    }

    echo json_encode($data, JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error'  => 'Error al generar datos.',
        'detail' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
