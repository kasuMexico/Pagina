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

    $today = date('Y-m-d');
    $minDefault = '2000-01-01';
    $iniGet = filter_input(INPUT_GET, 'ini', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: $minDefault;
    $finGet = filter_input(INPUT_GET, 'fin', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: $today;
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $iniGet)) { $iniGet = $minDefault; }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $finGet)) { $finGet = $today; }
    if ($iniGet > $finGet) { [$iniGet, $finGet] = [$finGet, $iniGet]; }
    $iniFull = $iniGet . ' 00:00:00';
    $finFull = $finGet . ' 23:59:59';

    $stmtSum = $mysqli->prepare(
        "SELECT COALESCE(SUM(CostoVenta),0) AS monto
         FROM Venta
         WHERE Producto = ? AND Status = 'ACTIVO' AND FechaRegistro BETWEEN ? AND ?"
    );

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

        $stmtSum->bind_param('sss', $producto, $iniFull, $finFull);
        $stmtSum->execute();
        $resMonto = $stmtSum->get_result()->fetch_assoc();
        $monto = (float)($resMonto['monto'] ?? 0.0);

        $data['rows'][] = [
            'c' => [
                ['v' => $producto],
                ['v' => $monto],
            ],
        ];
    }

    $stmtSum->close();

    echo json_encode($data, JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error'  => 'Error al generar datos.',
        'detail' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
