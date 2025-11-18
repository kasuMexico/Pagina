<?php
/**************************************************************************************************
 * ARCHIVO: c_Ventas_Totales.php
 * Qué hace: Genera un DataTable JSON con el TOTAL DE VENTAS (conteo de pólizas) por producto.
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 *
 * Compatibilidad PHP 8.2:
 * - Uso de etiquetas completas <?php
 * - Manejo de errores con mysqli_report y try/catch
 * - Salida JSON UTF-8 con estructura Google DataTable (cols + rows)
 **************************************************************************************************/

declare(strict_types=1);

//Calcula el total de las polizas compradas por los clientes
//indicar que se inicia una sesion
require_once dirname(__DIR__, 3) . '/eia/session.php';
kasu_session_start();
//inlcuir el archivo de funciones
require_once __DIR__ . '/../../../eia/librerias.php';

header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
        throw new RuntimeException('DB no inicializada');
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

    $stmtCount = $mysqli->prepare("SELECT COUNT(*) AS total FROM Venta WHERE Producto = ? AND FechaRegistro BETWEEN ? AND ?");

    //create an array
    $data = [
        'cols' => [
            ['label' => 'Producto',      'type' => 'string'],
            ['label' => 'Total Ventas',  'type' => 'number'],
        ],
        'rows' => [],
    ];

    //Realizamos la busqueda de los status de la venta
    $sql1 = "SELECT Producto FROM Productos";
    //Realiza consulta
    $res1 = $mysqli->query($sql1);

    //Si existe el registro se asocia en un fetch_assoc
    foreach ($res1 as $Reg1) {
        $producto = (string)$Reg1['Producto'];
        $stmtCount->bind_param('sss', $producto, $iniFull, $finFull);
        $stmtCount->execute();
        $resCount = $stmtCount->get_result()->fetch_assoc();
        $unidades_vendidas = (int)($resCount['total'] ?? 0);

        //Insertamos el valor en el array
        $data['rows'][] = [
            'c' => [
                ['v' => $producto],
                ['v' => $unidades_vendidas],
            ],
        ];
    }

    $stmtCount->close();

    echo json_encode($data, JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error'  => 'Error al generar datos.',
        'detail' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
